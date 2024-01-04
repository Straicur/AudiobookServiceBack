<?php

namespace App\Controller;

use App\Annotation\AuthValidation;
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
use App\Repository\UserRepository;
use App\Service\AuthorizedUserServiceInterface;
use App\Service\RequestServiceInterface;
use App\Tool\ResponseTool;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Attributes as OA;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

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
     * @return Response
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
        NotificationRepository         $notificationRepository
    ): Response
    {
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
        $lastWeekNotifications = $notificationRepository->getNotificationsFromLastWeak();

        return ResponseTool::getResponse(new AdminStatisticMainSuccessModel($users, $categories, $audiobooks, $lastWeekRegistered, $lastWeekLogins, $lastWeekNotifications));
    }

    /**
     * @param Request $request
     * @param RequestServiceInterface $requestService
     * @param AuthorizedUserServiceInterface $authorizedUserService
     * @param LoggerInterface $endpointLogger
     * @param AudiobookRepository $audiobookRepository
     * @param AudiobookCategoryRepository $audiobookCategoryRepository
     * @return Response
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
        AudiobookCategoryRepository    $audiobookCategoryRepository
    ): Response
    {
        $topAudiobooks = $audiobookRepository->getBestAudiobooks();

        if (count($topAudiobooks) == 0) {
            return ResponseTool::getResponse();
        }

        $successModel = new AdminStatisticBestAudiobooksSuccessModel();

        if (count($topAudiobooks) >= 1) {
            $firstAudiobook = $topAudiobooks[0];

            $audiobookCategories = [];

            $categories = $audiobookCategoryRepository->getAudiobookCategories($firstAudiobook);

            foreach ($categories as $category) {
                $audiobookCategories[] = new AudiobookDetailCategoryModel(
                    $category->getId(),
                    $category->getName(),
                    $category->getActive(),
                    $category->getCategoryKey()
                );
            }

            $firstAudiobookModel = new AdminAudiobookDetailsModel(
                $firstAudiobook->getId(),
                $firstAudiobook->getTitle(),
                $firstAudiobook->getAuthor(),
                $firstAudiobook->getVersion(),
                $firstAudiobook->getAlbum(),
                $firstAudiobook->getYear(),
                $firstAudiobook->getDuration(),
                $firstAudiobook->getSize(),
                $firstAudiobook->getParts(),
                $firstAudiobook->getDescription(),
                $firstAudiobook->getAge(),
                $firstAudiobook->getActive(),
                $audiobookCategories
            );

            $successModel->setFirstAudiobook($firstAudiobookModel);
        }
        if (count($topAudiobooks) >= 2) {
            $secondAudiobook = $topAudiobooks[1];

            $audiobookCategories = [];

            $categories = $audiobookCategoryRepository->getAudiobookCategories($secondAudiobook);

            foreach ($categories as $category) {
                $audiobookCategories[] = new AudiobookDetailCategoryModel(
                    $category->getId(),
                    $category->getName(),
                    $category->getActive(),
                    $category->getCategoryKey()
                );
            }

            $secondAudiobookModel = new AdminAudiobookDetailsModel(
                $secondAudiobook->getId(),
                $secondAudiobook->getTitle(),
                $secondAudiobook->getAuthor(),
                $secondAudiobook->getVersion(),
                $secondAudiobook->getAlbum(),
                $secondAudiobook->getYear(),
                $secondAudiobook->getDuration(),
                $secondAudiobook->getSize(),
                $secondAudiobook->getParts(),
                $secondAudiobook->getDescription(),
                $secondAudiobook->getAge(),
                $secondAudiobook->getActive(),
                $audiobookCategories);
            $successModel->setSecondAudiobook($secondAudiobookModel);
        }
        if (count($topAudiobooks) >= 3) {
            $thirdAudiobook = $topAudiobooks[2];

            $audiobookCategories = [];

            $categories = $audiobookCategoryRepository->getAudiobookCategories($thirdAudiobook);

            foreach ($categories as $category) {
                $audiobookCategories[] = new AudiobookDetailCategoryModel(
                    $category->getId(),
                    $category->getName(),
                    $category->getActive(),
                    $category->getCategoryKey()
                );
            }

            $thirdAudiobookModel = new AdminAudiobookDetailsModel(
                $thirdAudiobook->getId(),
                $thirdAudiobook->getTitle(),
                $thirdAudiobook->getAuthor(),
                $thirdAudiobook->getVersion(),
                $thirdAudiobook->getAlbum(),
                $thirdAudiobook->getYear(),
                $thirdAudiobook->getDuration(),
                $thirdAudiobook->getSize(),
                $thirdAudiobook->getParts(),
                $thirdAudiobook->getDescription(),
                $thirdAudiobook->getAge(),
                $thirdAudiobook->getActive(),
                $audiobookCategories);
            $successModel->setThirdAudiobook($thirdAudiobookModel);
        }

        return ResponseTool::getResponse($successModel);
    }
}