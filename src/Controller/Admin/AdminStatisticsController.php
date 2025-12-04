<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Annotation\AuthValidation;
use App\Enums\Cache\AdminCacheKeys;
use App\Enums\Cache\AdminStockCacheTags;
use App\Enums\Cache\CacheValidTime;
use App\Enums\UserRolesNames;
use App\Model\Admin\AdminAudiobookDetailsModel;
use App\Model\Admin\AdminStatisticBestAudiobooksSuccessModel;
use App\Model\Admin\AdminStatisticMainSuccessModel;
use App\Model\Common\AudiobookDetailCategoryModel;
use App\Model\Error\DataNotFoundModel;
use App\Model\Error\JsonDataInvalidModel;
use App\Model\Error\NotAuthorizeModel;
use App\Model\Error\PermissionNotGrantedModel;
use App\Repository\AudiobookCategoryRepository;
use App\Repository\AudiobookRepository;
use App\Repository\AuthenticationTokenRepository;
use App\Repository\NotificationRepository;
use App\Repository\TechnicalBreakRepository;
use App\Repository\UserRepository;
use App\Tool\ResponseTool;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

#[OA\Response(
    response   : 400,
    description: 'JSON Data Invalid',
    content    : new Model(type: JsonDataInvalidModel::class)
)]
#[OA\Response(
    response   : 404,
    description: 'Data not found',
    content    : new Model(type: DataNotFoundModel::class)
)]
#[OA\Response(
    response   : 401,
    description: 'User not authorized',
    content    : new Model(type: NotAuthorizeModel::class)
)]
#[OA\Response(
    response   : 403,
    description: 'User have no permission',
    content    : new Model(type: PermissionNotGrantedModel::class)
)]
#[OA\Tag(name: 'AdminStatistics')]
#[Route('/api/admin')]
class AdminStatisticsController extends AbstractController
{
    #[Route('/statistic/main', name: 'adminStatisticMain', methods: ['GET'])]
    #[AuthValidation(checkAuthToken: true, roles: [UserRolesNames::ADMINISTRATOR, UserRolesNames::RECRUITER])]
    #[OA\Get(
        description: 'Endpoint is returning main statistic data',
        requestBody: new OA\RequestBody(),
        responses  : [
            new OA\Response(
                response   : 200,
                description: 'Success',
                content    : new Model(type: AdminStatisticMainSuccessModel::class),
            ),
        ]
    )]
    public function adminStatisticMain(
        UserRepository $userRepository,
        AudiobookCategoryRepository $audiobookCategoryRepository,
        AudiobookRepository $audiobookRepository,
        AuthenticationTokenRepository $authenticationTokenRepository,
        NotificationRepository $notificationRepository,
        TechnicalBreakRepository $technicalBreakRepository,
        TagAwareCacheInterface $stockCache,
    ): Response {
        [
            $users,
            $categories,
            $audiobooks,
            $lastWeekRegistered,
            $lastWeekLogins,
            $lastWeekNotifications,
            $lastWeekSystemBreaks,
        ] = $stockCache->get(AdminCacheKeys::ADMIN_STATISTICS->value, function (ItemInterface $item) use ($userRepository, $audiobookCategoryRepository, $audiobookRepository, $authenticationTokenRepository, $notificationRepository, $technicalBreakRepository) {
                $item->expiresAfter(CacheValidTime::TEN_MINUTES->value);
                $item->tag(AdminStockCacheTags::ADMIN_STATISTICS->value);

                $users = count($userRepository->findBy([
                'active' => true,
                ]));

                $categories = count($audiobookCategoryRepository->findBy([
                'active' => true,
                ]));

                $audiobooks = count($audiobookRepository->findBy([
                'active' => true,
                ]));

                $lastWeekRegistered = $userRepository->newUsersFromLastWeak();
                $lastWeekLogins = $authenticationTokenRepository->getNumberOfAuthenticationTokensFromLast7Days();
                $lastWeekNotifications = $notificationRepository->getNumberNotificationsFromLastWeek();
                $lastWeekSystemBreaks = $technicalBreakRepository->getNumberTechnicalBreakFromLastWeak();
            return [
                $users,
                $categories,
                $audiobooks,
                $lastWeekRegistered,
                $lastWeekLogins,
                $lastWeekNotifications,
                $lastWeekSystemBreaks,
            ];
        });

        return ResponseTool::getResponse(
            new AdminStatisticMainSuccessModel(
                $users,
                $categories,
                $audiobooks,
                $lastWeekRegistered,
                $lastWeekLogins,
                $lastWeekNotifications,
                $lastWeekSystemBreaks
            )
        );
    }

    #[Route('/statistic/best/audiobooks', name: 'adminStatisticBestAudiobooks', methods: ['GET'])]
    #[AuthValidation(checkAuthToken: true, roles: [UserRolesNames::ADMINISTRATOR, UserRolesNames::RECRUITER])]
    #[OA\Get(
        description: 'Endpoint  is returning most liked audiobooks statistics',
        requestBody: new OA\RequestBody(),
        responses  : [
            new OA\Response(
                response   : 200,
                description: 'Success',
                content    : new Model(type: AdminStatisticBestAudiobooksSuccessModel::class),
            ),
        ]
    )]
    public function adminStatisticBestAudiobooks(
        AudiobookRepository $audiobookRepository,
        AudiobookCategoryRepository $audiobookCategoryRepository,
        TagAwareCacheInterface $stockCache,
    ): Response {
        $topAudiobooks = $audiobookRepository->getBestAudiobooks();

        if (count($topAudiobooks) === 0) {
            return ResponseTool::getResponse();
        }

        $successModel = $stockCache->get(AdminCacheKeys::ADMIN_STATISTICS_AUDIOBOOKS->value, function (ItemInterface $item) use ($topAudiobooks, $audiobookCategoryRepository) {
            $item->expiresAfter(CacheValidTime::TWO_HOURS->value);
            $item->tag(AdminStockCacheTags::ADMIN_STATISTICS->value);

            $successModel = new AdminStatisticBestAudiobooksSuccessModel();

            foreach ($topAudiobooks as $idx => $topAudiobook) {
                $audiobookCategories = [];

                $categories = $audiobookCategoryRepository->getAudiobookCategories($topAudiobook);

                foreach ($categories as $category) {
                    $audiobookCategories[] = new AudiobookDetailCategoryModel(
                        (string)$category->getId(),
                        $category->getName(),
                        $category->getActive(),
                        $category->getCategoryKey(),
                    );
                }

                $audiobookModel = new AdminAudiobookDetailsModel(
                    (string)$topAudiobook->getId(),
                    $topAudiobook->getTitle(),
                    $topAudiobook->getAuthor(),
                    $topAudiobook->getVersion(),
                    $topAudiobook->getAlbum(),
                    $topAudiobook->getYear(),
                    $topAudiobook->getDuration(),
                    $topAudiobook->getSize(),
                    $topAudiobook->getParts(),
                    $topAudiobook->getDescription(),
                    $topAudiobook->getAge(),
                    $topAudiobook->getActive(),
                    $audiobookCategories,
                );

                match ($idx) {
                    0 => $successModel->setFirstAudiobook($audiobookModel),
                    1 => $successModel->setSecondAudiobook($audiobookModel),
                    2 => $successModel->setThirdAudiobook($audiobookModel),
                };
            }

            return $successModel;
        });

        return ResponseTool::getResponse($successModel);
    }
}
