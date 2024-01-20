<?php

namespace App\Controller;

use App\Annotation\AuthValidation;
use App\Enums\CacheKeys;
use App\Enums\CacheValidTime;
use App\Enums\StockCacheTags;
use App\Model\Admin\AdminAudiobookDetailsModel;
use App\Model\Admin\AdminCategoriesSuccessModel;
use App\Model\Admin\AdminCategoryModel;
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
use App\Service\AuthorizedUserServiceInterface;
use App\Service\RequestServiceInterface;
use App\Tool\ResponseTool;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Attributes as OA;
use Psr\Cache\InvalidArgumentException;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

#[OA\Response(
    response: 400,
    description: "JSON Data Invalid",
    content: new Model(type: JsonDataInvalidModel::class)
)]
#[OA\Response(
    response: 404,
    description: "Data not found",
    content: new Model(type: DataNotFoundModel::class)
)]
#[OA\Response(
    response: 401,
    description: "User not authorized",
    content: new Model(type: NotAuthorizeModel::class)
)]
#[OA\Response(
    response: 403,
    description: "User have no permission",
    content: new Model(type: PermissionNotGrantedModel::class)
)]
#[OA\Tag(name: "AdminStatistics")]
class AdminStatisticsController extends AbstractController
{
    /**
     * @param Request $request
     * @param RequestServiceInterface $requestService
     * @param AuthorizedUserServiceInterface $authorizedUserService
     * @param LoggerInterface $endpointLogger
     * @param UserRepository $userRepository
     * @param AudiobookCategoryRepository $audiobookCategoryRepository
     * @param AudiobookRepository $audiobookRepository
     * @param AuthenticationTokenRepository $authenticationTokenRepository
     * @param NotificationRepository $notificationRepository
     * @param TechnicalBreakRepository $technicalBreakRepository
     * @param TagAwareCacheInterface $stockCache
     * @return Response
     * @throws InvalidArgumentException
     */
    #[Route("/api/admin/statistic/main", name: "adminStatisticMain", methods: ["GET"])]
    #[AuthValidation(checkAuthToken: true, roles: ["Administrator"])]
    #[OA\Get(
        description: "Endpoint is returning main statistic data",
        requestBody: new OA\RequestBody(),
        responses: [
            new OA\Response(
                response: 200,
                description: "Success",
                content: new Model(type: AdminStatisticMainSuccessModel::class)
            )
        ]
    )]
    public function adminStatisticMain(
        Request                        $request,
        RequestServiceInterface        $requestService,
        AuthorizedUserServiceInterface $authorizedUserService,
        LoggerInterface                $endpointLogger,
        UserRepository                 $userRepository,
        AudiobookCategoryRepository    $audiobookCategoryRepository,
        AudiobookRepository            $audiobookRepository,
        AuthenticationTokenRepository  $authenticationTokenRepository,
        NotificationRepository         $notificationRepository,
        TechnicalBreakRepository       $technicalBreakRepository,
        TagAwareCacheInterface         $stockCache
    ): Response
    {
        [$users, $categories, $audiobooks, $lastWeekRegistered, $lastWeekLogins, $lastWeekNotifications, $lastWeekSystemBreaks] = $stockCache->get(CacheKeys::ADMIN_STATISTICS->value, function (ItemInterface $item) use ($userRepository, $audiobookCategoryRepository, $audiobookRepository, $authenticationTokenRepository, $notificationRepository, $technicalBreakRepository) {
            $item->expiresAfter(CacheValidTime::TEN_MINUTES->value);
            $item->tag(StockCacheTags::ADMIN_STATISTICS->value);

            $users = count($userRepository->findBy([
                "active" => true
            ]));

            $categories = count($audiobookCategoryRepository->findBy([
                "active" => true
            ]));

            $audiobooks = count($audiobookRepository->findBy([
                "active" => true
            ]));

            $lastWeekRegistered = $userRepository->newUsersFromLastWeak();
            $lastWeekLogins = $authenticationTokenRepository->getNumberOfAuthenticationTokensFromLast7Days();
            $lastWeekNotifications = $notificationRepository->getNumberNotificationsFromLastWeak();
            $lastWeekSystemBreaks = $technicalBreakRepository->getNumberTechnicalBreakFromLastWeak();
            return [$users, $categories, $audiobooks, $lastWeekRegistered, $lastWeekLogins, $lastWeekNotifications, $lastWeekSystemBreaks];
        });

        return ResponseTool::getResponse(new AdminStatisticMainSuccessModel($users, $categories, $audiobooks, $lastWeekRegistered, $lastWeekLogins, $lastWeekNotifications, $lastWeekSystemBreaks));
    }

    /**
     * @param Request $request
     * @param RequestServiceInterface $requestService
     * @param AuthorizedUserServiceInterface $authorizedUserService
     * @param LoggerInterface $endpointLogger
     * @param AudiobookRepository $audiobookRepository
     * @param AudiobookCategoryRepository $audiobookCategoryRepository
     * @param TagAwareCacheInterface $stockCache
     * @return Response
     * @throws InvalidArgumentException
     */
    #[Route("/api/admin/statistic/best/audiobooks", name: "adminStatisticBestAudiobooks", methods: ["GET"])]
    #[AuthValidation(checkAuthToken: true, roles: ["Administrator"])]
    #[OA\Get(
        description: "Endpoint  is returning most liked audiobooks statistics",
        requestBody: new OA\RequestBody(),
        responses: [
            new OA\Response(
                response: 200,
                description: "Success",
                content: new Model(type: AdminStatisticBestAudiobooksSuccessModel::class)
            )
        ]
    )]
    public function adminStatisticBestAudiobooks(
        Request                        $request,
        RequestServiceInterface        $requestService,
        AuthorizedUserServiceInterface $authorizedUserService,
        LoggerInterface                $endpointLogger,
        AudiobookRepository            $audiobookRepository,
        AudiobookCategoryRepository    $audiobookCategoryRepository,
        TagAwareCacheInterface         $stockCache
    ): Response
    {
        $topAudiobooks = $audiobookRepository->getBestAudiobooks();

        if (count($topAudiobooks) === 0) {
            return ResponseTool::getResponse();
        }

        $successModel = $stockCache->get(CacheKeys::ADMIN_STATISTICS_AUDIOBOOKS->value, function (ItemInterface $item) use ($topAudiobooks, $audiobookCategoryRepository) {
            $item->expiresAfter(CacheValidTime::TWO_HOURS->value);
            $item->tag(StockCacheTags::ADMIN_STATISTICS->value);

            $successModel = new AdminStatisticBestAudiobooksSuccessModel();

            foreach ($topAudiobooks as $idx => $topAudiobook) {
                $audiobookCategories = [];

                $categories = $audiobookCategoryRepository->getAudiobookCategories($topAudiobook);

                foreach ($categories as $category) {
                    $audiobookCategories[] = new AudiobookDetailCategoryModel(
                        $category->getId(),
                        $category->getName(),
                        $category->getActive(),
                        $category->getCategoryKey()
                    );
                }

                $audiobookModel = new AdminAudiobookDetailsModel(
                    $topAudiobook->getId(),
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
                    $audiobookCategories
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