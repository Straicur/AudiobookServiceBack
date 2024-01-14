<?php

namespace App\Controller;

use App\Annotation\AuthValidation;
use App\Entity\AudiobookInfo;
use App\Entity\AudiobookRating;
use App\Entity\AudiobookUserComment;
use App\Entity\AudiobookUserCommentLike;
use App\Exception\DataNotFoundException;
use App\Exception\InvalidJsonDataException;
use App\Model\Common\AudiobookCommentsSuccessModel;
use App\Model\Common\AudiobookDetailCategoryModel;
use App\Model\Error\DataNotFoundModel;
use App\Model\Error\JsonDataInvalidModel;
use App\Model\Error\NotAuthorizeModel;
use App\Model\Error\PermissionNotGrantedModel;
use App\Model\User\UserAudiobookCategoryModel;
use App\Model\User\UserAudiobookDetailModel;
use App\Model\User\UserAudiobookDetailsSuccessModel;
use App\Model\User\UserAudiobookInfoSuccessModel;
use App\Model\User\UserAudiobookModel;
use App\Model\User\UserAudiobookRatingGetSuccessModel;
use App\Model\User\UserAudiobooksSearchSuccessModel;
use App\Model\User\UserAudiobooksSuccessModel;
use App\Model\User\UserCategoryModel;
use App\Model\User\UserMyListAudiobooksSuccessModel;
use App\Model\User\UserProposedAudiobooksSuccessModel;
use App\Query\User\UserAudiobookCommentAddQuery;
use App\Query\User\UserAudiobookCommentEditQuery;
use App\Query\User\UserAudiobookCommentGetQuery;
use App\Query\User\UserAudiobookCommentLikeAddQuery;
use App\Query\User\UserAudiobookCommentLikeDeleteQuery;
use App\Query\User\UserAudiobookDetailsQuery;
use App\Query\User\UserAudiobookInfoAddQuery;
use App\Query\User\UserAudiobookInfoQuery;
use App\Query\User\UserAudiobookLikeQuery;
use App\Query\User\UserAudiobookRatingAddQuery;
use App\Query\User\UserAudiobookRatingGetQuery;
use App\Query\User\UserAudiobooksQuery;
use App\Query\User\UserAudiobooksSearchQuery;
use App\Repository\AudiobookCategoryRepository;
use App\Repository\AudiobookInfoRepository;
use App\Repository\AudiobookRatingRepository;
use App\Repository\AudiobookRepository;
use App\Repository\AudiobookUserCommentLikeRepository;
use App\Repository\AudiobookUserCommentRepository;
use App\Repository\MyListRepository;
use App\Service\AuthorizedUserServiceInterface;
use App\Service\RequestServiceInterface;
use App\Service\TranslateService;
use App\Tool\ResponseTool;
use App\Tool\UserParentalControlTool;
use App\ValueGenerator\BuildAudiobookCommentTreeGenerator;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Attributes as OA;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

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
#[OA\Tag(name: "UserAudiobook")]
class UserAudiobookController extends AbstractController
{
    /**
     * @param Request $request
     * @param RequestServiceInterface $requestService
     * @param AuthorizedUserServiceInterface $authorizedUserService
     * @param LoggerInterface $endpointLogger
     * @param AudiobookRepository $audiobookRepository
     * @param AudiobookCategoryRepository $audiobookCategoryRepository
     * @param TranslateService $translateService
     * @return Response
     * @throws InvalidJsonDataException
     */
    #[Route("/api/user/audiobooks", name: "userAudiobooks", methods: ["POST"])]
    #[AuthValidation(checkAuthToken: true, roles: ["User"])]
    #[OA\Post(
        description: "Endpoint is returning list of categories with audiobooks",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                ref: new Model(type: UserAudiobooksQuery::class),
                type: "object"
            ),
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Success",
                content: new Model(type: UserAudiobooksSuccessModel::class)
            )
        ]
    )]
    public function userAudiobooks(
        Request                        $request,
        RequestServiceInterface        $requestService,
        AuthorizedUserServiceInterface $authorizedUserService,
        LoggerInterface                $endpointLogger,
        AudiobookRepository            $audiobookRepository,
        AudiobookCategoryRepository    $audiobookCategoryRepository,
        TranslateService               $translateService
    ): Response
    {
        $userAudiobooksQuery = $requestService->getRequestBodyContent($request, UserAudiobooksQuery::class);

        if ($userAudiobooksQuery instanceof UserAudiobooksQuery) {

            $minResult = $userAudiobooksQuery->getPage() * $userAudiobooksQuery->getLimit();
            $maxResult = $userAudiobooksQuery->getLimit() + $minResult;

            $allCategories = $audiobookCategoryRepository->getCategoriesByCountAudiobooks();

            $successModel = new UserAudiobooksSuccessModel();

            foreach ($allCategories as $index => $category) {
                if ($index < $minResult) {
                    continue;
                }

                if ($index < $maxResult) {
                    $categoryModel = new UserCategoryModel($category->getName(), $category->getCategoryKey());

                    $age = null;
                    $user = $authorizedUserService->getAuthorizedUser();

                    if ($user->getUserInformation()->getBirthday() !== null) {
                        $userParentalControlTool = new UserParentalControlTool();
                        $age = $userParentalControlTool->getUserAudiobookAgeValue($user);
                    }

                    $audiobooks = $audiobookRepository->getActiveCategoryAudiobooks($category, $age);

                    foreach ($audiobooks as $audiobook) {
                        $categoryModel->addAudiobook(new UserAudiobookModel(
                            $audiobook->getId(),
                            $audiobook->getTitle(),
                            $audiobook->getAuthor(),
                            $audiobook->getParts(),
                            $audiobook->getAge()
                        ));
                    }
                    $successModel->addCategory($categoryModel);
                } else {
                    break;
                }
            }

            $successModel->setPage($userAudiobooksQuery->getPage());
            $successModel->setLimit($userAudiobooksQuery->getLimit());

            $successModel->setMaxPage(ceil(count($allCategories) / $userAudiobooksQuery->getLimit()));

            return ResponseTool::getResponse($successModel);
        }

        $endpointLogger->error("Invalid given Query");
        $translateService->setPreferredLanguage($request);
        throw new InvalidJsonDataException($translateService);
    }

    /**
     * @param Request $request
     * @param RequestServiceInterface $requestService
     * @param AuthorizedUserServiceInterface $authorizedUserService
     * @param LoggerInterface $endpointLogger
     * @param AudiobookRepository $audiobookRepository
     * @param TranslateService $translateService
     * @return Response
     * @throws InvalidJsonDataException
     */
    #[Route("/api/user/audiobooks/search", name: "userAudiobooksSearch", methods: ["POST"])]
    #[AuthValidation(checkAuthToken: true, roles: ["User"])]
    #[OA\Post(
        description: "Endpoint is returning list of audiobooks by title",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                ref: new Model(type: UserAudiobooksSearchQuery::class),
                type: "object"
            ),
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Success",
                content: new Model(type: UserAudiobooksSearchSuccessModel::class)
            )
        ]
    )]
    public function userAudiobooksSearch(
        Request                        $request,
        RequestServiceInterface        $requestService,
        AuthorizedUserServiceInterface $authorizedUserService,
        LoggerInterface                $endpointLogger,
        AudiobookRepository            $audiobookRepository,
        TranslateService               $translateService
    ): Response
    {
        $userAudiobooksSearchQuery = $requestService->getRequestBodyContent($request, UserAudiobooksSearchQuery::class);

        if ($userAudiobooksSearchQuery instanceof UserAudiobooksSearchQuery) {
            $age = null;
            $user = $authorizedUserService->getAuthorizedUser();

            if ($user->getUserInformation()->getBirthday() !== null) {
                $userParentalControlTool = new UserParentalControlTool();
                $age = $userParentalControlTool->getUserAudiobookAgeValue($user);
            }

            $allAudiobooks = $audiobookRepository->searchAudiobooksByName($userAudiobooksSearchQuery->getTitle(), $age);

            $successModel = new UserAudiobooksSearchSuccessModel();

            foreach ($allAudiobooks as $audiobook) {

                $audiobookModel = new UserAudiobookDetailModel(
                    $audiobook->getId(),
                    $audiobook->getTitle(),
                    $audiobook->getAuthor(),
                    $audiobook->getParts(),
                    $audiobook->getAge()
                );

                foreach ($audiobook->getCategories() as $category) {
                    $audiobookModel->addCategory(new UserAudiobookCategoryModel(
                        $category->getName(),
                        $category->getCategoryKey()
                    ));
                }

                $successModel->addAudiobook($audiobookModel);

            }

            return ResponseTool::getResponse($successModel);
        }

        $endpointLogger->error("Invalid given Query");
        $translateService->setPreferredLanguage($request);
        throw new InvalidJsonDataException($translateService);
    }

    /**
     * @param Request $request
     * @param RequestServiceInterface $requestService
     * @param AuthorizedUserServiceInterface $authorizedUserService
     * @param LoggerInterface $endpointLogger
     * @return Response
     */
    #[Route("/api/user/proposed/audiobooks", name: "userProposedAudiobooks", methods: ["GET"])]
    #[AuthValidation(checkAuthToken: true, roles: ["User"])]
    #[OA\Get(
        description: "Endpoint is returning list of proposed audiobooks",
        requestBody: new OA\RequestBody(),
        responses: [
            new OA\Response(
                response: 200,
                description: "Success",
                content: new Model(type: UserProposedAudiobooksSuccessModel::class)
            )
        ]
    )]
    public function userProposedAudiobooks(
        Request                        $request,
        RequestServiceInterface        $requestService,
        AuthorizedUserServiceInterface $authorizedUserService,
        LoggerInterface                $endpointLogger,
    ): Response
    {
        $user = $authorizedUserService->getAuthorizedUser();

        $audiobooks = $user->getProposedAudiobooks()->getAudiobooks();

        $successModel = new UserProposedAudiobooksSuccessModel();

        foreach ($audiobooks as $audiobook) {
            if ($audiobook->getActive()) {

                $audiobookModel = new UserAudiobookDetailModel(
                    $audiobook->getId(),
                    $audiobook->getTitle(),
                    $audiobook->getAuthor(),
                    $audiobook->getParts(),
                    $audiobook->getAge()
                );

                foreach ($audiobook->getCategories() as $category) {
                    $audiobookModel->addCategory(new UserAudiobookCategoryModel(
                        $category->getName(),
                        $category->getCategoryKey()
                    ));
                }

                $successModel->addAudiobook($audiobookModel);
            }
        }

        return ResponseTool::getResponse($successModel);
    }

    /**
     * @param Request $request
     * @param RequestServiceInterface $requestService
     * @param AuthorizedUserServiceInterface $authorizedUserService
     * @param LoggerInterface $endpointLogger
     * @param AudiobookRepository $audiobookRepository
     * @param AudiobookCategoryRepository $audiobookCategoryRepository
     * @param MyListRepository $listRepository
     * @param AudiobookUserCommentRepository $audiobookUserCommentRepository
     * @param AudiobookInfoRepository $audiobookInfoRepository
     * @param AudiobookRatingRepository $audiobookRatingRepository
     * @param TranslateService $translateService
     * @return Response
     * @throws DataNotFoundException
     * @throws InvalidJsonDataException
     */
    #[Route("/api/user/audiobook/details", name: "userAudiobookDetails", methods: ["POST"])]
    #[AuthValidation(checkAuthToken: true, roles: ["User"])]
    #[OA\Post(
        description: "Endpoint is returning details of given audiobook",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                ref: new Model(type: UserAudiobookDetailsQuery::class),
                type: "object"
            ),
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Success",
                content: new Model(type: UserAudiobookDetailsSuccessModel::class)
            )
        ]
    )]
    public function userAudiobookDetails(
        Request                        $request,
        RequestServiceInterface        $requestService,
        AuthorizedUserServiceInterface $authorizedUserService,
        LoggerInterface                $endpointLogger,
        AudiobookRepository            $audiobookRepository,
        AudiobookCategoryRepository    $audiobookCategoryRepository,
        MyListRepository               $listRepository,
        AudiobookUserCommentRepository $audiobookUserCommentRepository,
        AudiobookInfoRepository        $audiobookInfoRepository,
        AudiobookRatingRepository      $audiobookRatingRepository,
        TranslateService               $translateService
    ): Response
    {
        $userAudiobookDetailsQuery = $requestService->getRequestBodyContent($request, UserAudiobookDetailsQuery::class);

        if ($userAudiobookDetailsQuery instanceof UserAudiobookDetailsQuery) {

            $audiobook = $audiobookRepository->getAudiobookByCategoryKeyAndId($userAudiobookDetailsQuery->getAudiobookId(), $userAudiobookDetailsQuery->getCategoryKey());

            if ($audiobook == null) {
                $endpointLogger->error("Audiobook dont exist");
                $translateService->setPreferredLanguage($request);
                throw new DataNotFoundException([$translateService->getTranslation("AudiobookDontExists")]);
            }

            $user = $authorizedUserService->getAuthorizedUser();

            $categories = $audiobookCategoryRepository->getAudiobookActiveCategories($audiobook);

            $audiobookCategories = [];

            foreach ($categories as $category) {
                $audiobookCategories[] = new AudiobookDetailCategoryModel(
                    $category->getId(),
                    $category->getName(),
                    $category->getActive(),
                    $category->getCategoryKey()
                );
            }

            $inList = $listRepository->getAudiobookINMyList($user, $audiobook);

            $audiobookInfo = $audiobookInfoRepository->findBy([
                "audiobook" => $audiobook->getId(),
                "watched" => true,
                "user" => $user->getId()
            ]);

            $audiobookUserComments = $audiobookUserCommentRepository->findBy([
                "parent" => null,
                "audiobook" => $audiobook->getId(),
                "deleted" => false
            ]);

            $successModel = new UserAudiobookDetailsSuccessModel(
                $audiobook->getId(),
                $audiobook->getTitle(),
                $audiobook->getAuthor(),
                $audiobook->getVersion(),
                $audiobook->getAlbum(),
                $audiobook->getYear(),
                $audiobook->getDuration(),
                $audiobook->getParts(),
                $audiobook->getDescription(),
                $audiobook->getAge(),
                $audiobookCategories,
                $inList,
                count($audiobookUserComments),
                $audiobook->getAvgRating(),
                count($audiobookRatingRepository->findBy([
                    "audiobook" => $audiobook->getId()
                ]))
            );

            if ($audiobookInfo != null && count($audiobookInfo) >= $audiobook->getParts()) {
                $successModel->setCanRate(true);
            }

            if (floor($audiobook->getParts() / 2) > $audiobookInfo) {
                $successModel->setCanComment(true);
            }

            return ResponseTool::getResponse($successModel);
        }

        $endpointLogger->error("Invalid given Query");
        $translateService->setPreferredLanguage($request);
        throw new InvalidJsonDataException($translateService);
    }

    /**
     * @param Request $request
     * @param RequestServiceInterface $requestService
     * @param AuthorizedUserServiceInterface $authorizedUserService
     * @param LoggerInterface $endpointLogger
     * @param AudiobookRepository $audiobookRepository
     * @param AudiobookInfoRepository $audiobookInfoRepository
     * @param TranslateService $translateService
     * @return Response
     * @throws DataNotFoundException
     * @throws InvalidJsonDataException
     */
    #[Route("/api/user/audiobook/info", name: "userAudiobookInfo", methods: ["POST"])]
    #[AuthValidation(checkAuthToken: true, roles: ["User"])]
    #[OA\Post(
        description: "Endpoint is returning last information about last played part and time of given audiobook",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                ref: new Model(type: UserAudiobookInfoQuery::class),
                type: "object"
            ),
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Success",
                content: new Model(type: UserAudiobookInfoSuccessModel::class)
            )
        ]
    )]
    public function userAudiobookInfo(
        Request                        $request,
        RequestServiceInterface        $requestService,
        AuthorizedUserServiceInterface $authorizedUserService,
        LoggerInterface                $endpointLogger,
        AudiobookRepository            $audiobookRepository,
        AudiobookInfoRepository        $audiobookInfoRepository,
        TranslateService               $translateService
    ): Response
    {
        $userAudiobookInfoQuery = $requestService->getRequestBodyContent($request, UserAudiobookInfoQuery::class);

        if ($userAudiobookInfoQuery instanceof UserAudiobookInfoQuery) {

            $user = $authorizedUserService->getAuthorizedUser();

            $audiobook = $audiobookRepository->getAudiobookByCategoryKeyAndId($userAudiobookInfoQuery->getAudiobookId(), $userAudiobookInfoQuery->getCategoryKey());

            if ($audiobook == null) {
                $endpointLogger->error("Audiobook dont exist");
                $translateService->setPreferredLanguage($request);
                throw new DataNotFoundException([$translateService->getTranslation("AudiobookDontExists")]);
            }

            $audiobookInfo = $audiobookInfoRepository->findOneBy([
                "audiobook" => $audiobook->getId(),
                "active" => true,
                "user" => $user->getId()
            ]);

            if ($audiobookInfo == null) {
                $endpointLogger->error("AudiobookInfo dont exist");
                $translateService->setPreferredLanguage($request);
                throw new DataNotFoundException([$translateService->getTranslation("AudiobookInfoDontExists")]);
            }

            $successModel = new UserAudiobookInfoSuccessModel(
                $audiobookInfo->getPart(),
                $audiobookInfo->getEndedTime(),
                $audiobookInfo->getWatchingDate()
            );

            return ResponseTool::getResponse($successModel);
        }

        $endpointLogger->error("Invalid given Query");
        $translateService->setPreferredLanguage($request);
        throw new InvalidJsonDataException($translateService);
    }

    /**
     * @param Request $request
     * @param RequestServiceInterface $requestService
     * @param AuthorizedUserServiceInterface $authorizedUserService
     * @param LoggerInterface $endpointLogger
     * @param AudiobookRepository $audiobookRepository
     * @param MyListRepository $myListRepository
     * @param TranslateService $translateService
     * @return Response
     * @throws DataNotFoundException
     * @throws InvalidJsonDataException
     */
    #[Route("/api/user/audiobook/like", name: "userAudiobookLike", methods: ["PATCH"])]
    #[AuthValidation(checkAuthToken: true, roles: ["User"])]
    #[OA\Patch(
        description: "Endpoint is adding/deleting audiobook from my list",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                ref: new Model(type: UserAudiobookLikeQuery::class),
                type: "object"
            ),
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Success",
            )
        ]
    )]
    public function userAudiobookLike(
        Request                        $request,
        RequestServiceInterface        $requestService,
        AuthorizedUserServiceInterface $authorizedUserService,
        LoggerInterface                $endpointLogger,
        AudiobookRepository            $audiobookRepository,
        MyListRepository               $myListRepository,
        TranslateService               $translateService
    ): Response
    {
        $userAudiobookLikeQuery = $requestService->getRequestBodyContent($request, UserAudiobookLikeQuery::class);

        if ($userAudiobookLikeQuery instanceof UserAudiobookLikeQuery) {

            $user = $authorizedUserService->getAuthorizedUser();

            $audiobook = $audiobookRepository->getAudiobookByCategoryKeyAndId($userAudiobookLikeQuery->getAudiobookId(), $userAudiobookLikeQuery->getCategoryKey());

            if ($audiobook == null) {
                $endpointLogger->error("Audiobook dont exist");
                $translateService->setPreferredLanguage($request);
                throw new DataNotFoundException([$translateService->getTranslation("AudiobookDontExists")]);
            }

            $myList = $user->getMyList();

            if ($myListRepository->getAudiobookINMyList($user, $audiobook)) {
                $myList->removeAudiobook($audiobook);
            } else {
                $myList->addAudiobook($audiobook);
            }

            $myListRepository->add($myList);

            return ResponseTool::getResponse();
        }

        $endpointLogger->error("Invalid given Query");
        $translateService->setPreferredLanguage($request);
        throw new InvalidJsonDataException($translateService);
    }

    /**
     * @param Request $request
     * @param RequestServiceInterface $requestService
     * @param AuthorizedUserServiceInterface $authorizedUserService
     * @param LoggerInterface $endpointLogger
     * @return Response
     */
    #[Route("/api/user/myList/audiobooks", name: "userMyListAudiobooks", methods: ["GET"])]
    #[AuthValidation(checkAuthToken: true, roles: ["User"])]
    #[OA\Get(
        description: "Endpoint is returning list of audiobooks from my list",
        requestBody: new OA\RequestBody(),
        responses: [
            new OA\Response(
                response: 200,
                description: "Success",
                content: new Model(type: UserMyListAudiobooksSuccessModel::class)
            )
        ]
    )]
    public function userMyListAudiobooks(
        Request                        $request,
        RequestServiceInterface        $requestService,
        AuthorizedUserServiceInterface $authorizedUserService,
        LoggerInterface                $endpointLogger,
    ): Response
    {
        $user = $authorizedUserService->getAuthorizedUser();

        $audiobooks = $user->getMyList()->getAudiobooks();

        $successModel = new UserMyListAudiobooksSuccessModel();

        foreach ($audiobooks as $audiobook) {
            if ($audiobook->getActive()) {

                $audiobookModel = new UserAudiobookDetailModel(
                    $audiobook->getId(),
                    $audiobook->getTitle(),
                    $audiobook->getAuthor(),
                    $audiobook->getParts(),
                    $audiobook->getAge()
                );

                foreach ($audiobook->getCategories() as $category) {
                    $audiobookModel->addCategory(new UserAudiobookCategoryModel(
                        $category->getName(),
                        $category->getCategoryKey()
                    ));
                }

                $successModel->addAudiobook($audiobookModel);
            }
        }

        return ResponseTool::getResponse($successModel);
    }

    /**
     * @param Request $request
     * @param RequestServiceInterface $requestService
     * @param AuthorizedUserServiceInterface $authorizedUserService
     * @param LoggerInterface $endpointLogger
     * @param AudiobookRepository $audiobookRepository
     * @param AudiobookInfoRepository $audiobookInfoRepository
     * @param TranslateService $translateService
     * @return Response
     * @throws DataNotFoundException
     * @throws InvalidJsonDataException
     */
    #[Route("/api/user/audiobook/info/add", name: "userAudiobookInfoAdd", methods: ["PUT"])]
    #[AuthValidation(checkAuthToken: true, roles: ["User"])]
    #[OA\Put(
        description: "Endpoint is adding new info about given audiobook",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                ref: new Model(type: UserAudiobookInfoAddQuery::class),
                type: "object"
            ),
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: "Success",
            )
        ]
    )]
    public function userAudiobookInfoAdd(
        Request                        $request,
        RequestServiceInterface        $requestService,
        AuthorizedUserServiceInterface $authorizedUserService,
        LoggerInterface                $endpointLogger,
        AudiobookRepository            $audiobookRepository,
        AudiobookInfoRepository        $audiobookInfoRepository,
        TranslateService               $translateService
    ): Response
    {
        $userAudiobookInfoAddQuery = $requestService->getRequestBodyContent($request, UserAudiobookInfoAddQuery::class);

        if ($userAudiobookInfoAddQuery instanceof UserAudiobookInfoAddQuery) {

            $user = $authorizedUserService->getAuthorizedUser();

            $audiobook = $audiobookRepository->getAudiobookByCategoryKeyAndId($userAudiobookInfoAddQuery->getAudiobookId(), $userAudiobookInfoAddQuery->getCategoryKey());

            if ($audiobook == null) {
                $endpointLogger->error("Audiobook dont exist");
                $translateService->setPreferredLanguage($request);
                throw new DataNotFoundException([$translateService->getTranslation("AudiobookDontExists")]);
            }

            $audiobookInfoRepository->deActiveAudiobookInfos($user, $audiobook);

            $audiobookInfo = $audiobookInfoRepository->findOneBy([
                "audiobook" => $audiobook->getId(),
                "part" => $userAudiobookInfoAddQuery->getPart(),
                "user" => $user->getId()
            ]);

            if ($audiobookInfo != null) {
                if ($audiobookInfo->getEndedTime() < $userAudiobookInfoAddQuery->getEndedTime()) {
                    $audiobookInfo->setEndedTime($userAudiobookInfoAddQuery->getEndedTime());
                }

                $audiobookInfo->setWatchingDate(new \DateTime('Now'));

                if (!$audiobookInfo->getWatched()) {
                    $audiobookInfo->setWatched($userAudiobookInfoAddQuery->getWatched());
                }

                $audiobookInfo->setActive(true);
            } else {
                $audiobookInfo = new AudiobookInfo(
                    $user,
                    $audiobook,
                    $userAudiobookInfoAddQuery->getPart(),
                    $userAudiobookInfoAddQuery->getEndedTime(),
                    $userAudiobookInfoAddQuery->getWatched()
                );
            }

            $audiobookInfoRepository->add($audiobookInfo);

            return ResponseTool::getResponse(httpCode: 201);
        }

        $endpointLogger->error("Invalid given Query");
        $translateService->setPreferredLanguage($request);
        throw new InvalidJsonDataException($translateService);
    }

    /**
     * @param Request $request
     * @param RequestServiceInterface $requestService
     * @param AuthorizedUserServiceInterface $authorizedUserService
     * @param LoggerInterface $endpointLogger
     * @param AudiobookRepository $audiobookRepository
     * @param AudiobookInfoRepository $audiobookInfoRepository
     * @param AudiobookRatingRepository $ratingRepository
     * @param TranslateService $translateService
     * @return Response
     * @throws DataNotFoundException
     * @throws InvalidJsonDataException
     */
    #[Route("/api/user/audiobook/rating/add", name: "userAudiobookRatingAdd", methods: ["PUT"])]
    #[AuthValidation(checkAuthToken: true, roles: ["User"])]
    #[OA\Put(
        description: "Endpoint is adding/editing user audiobook rating",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                ref: new Model(type: UserAudiobookRatingAddQuery::class),
                type: "object"
            ),
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: "Success",
            )
        ]
    )]
    public function userAudiobookRatingAdd(
        Request                        $request,
        RequestServiceInterface        $requestService,
        AuthorizedUserServiceInterface $authorizedUserService,
        LoggerInterface                $endpointLogger,
        AudiobookRepository            $audiobookRepository,
        AudiobookInfoRepository        $audiobookInfoRepository,
        AudiobookRatingRepository      $ratingRepository,
        TranslateService               $translateService
    ): Response
    {
        $userAudiobookRatingAddQuery = $requestService->getRequestBodyContent($request, UserAudiobookRatingAddQuery::class);

        if ($userAudiobookRatingAddQuery instanceof UserAudiobookRatingAddQuery) {

            $user = $authorizedUserService->getAuthorizedUser();

            $audiobook = $audiobookRepository->getAudiobookByCategoryKeyAndId($userAudiobookRatingAddQuery->getAudiobookId(), $userAudiobookRatingAddQuery->getCategoryKey());

            if ($audiobook == null) {
                $endpointLogger->error("Audiobook dont exist");
                $translateService->setPreferredLanguage($request);
                throw new DataNotFoundException([$translateService->getTranslation("AudiobookDontExists")]);
            }

            $rating = $ratingRepository->findOneBy([
                "audiobook" => $audiobook->getId(),
                "user" => $user->getId()
            ]);

            if ($rating != null) {
                $rating->setRating($userAudiobookRatingAddQuery->isRating());
            } else {
                $audiobookInfo = $audiobookInfoRepository->findBy([
                    "audiobook" => $audiobook->getId(),
                    "watched" => true,
                    "user" => $user->getId()
                ]);

                if (count($audiobookInfo) < $audiobook->getParts()) {
                    $endpointLogger->error("Audiobook dont exist");
                    $translateService->setPreferredLanguage($request);
                    throw new DataNotFoundException([$translateService->getTranslation("AudiobookNotWatched")]);
                }

                $rating = new AudiobookRating($audiobook, $userAudiobookRatingAddQuery->isRating(), $user);
            }

            $ratingRepository->add($rating);

            return ResponseTool::getResponse(httpCode: 201);

        }

        $endpointLogger->error("Invalid given Query");
        $translateService->setPreferredLanguage($request);
        throw new InvalidJsonDataException($translateService);
    }

    /**
     * @param Request $request
     * @param RequestServiceInterface $requestService
     * @param AuthorizedUserServiceInterface $authorizedUserService
     * @param LoggerInterface $endpointLogger
     * @param AudiobookRepository $audiobookRepository
     * @param TranslateService $translateService
     * @return Response
     * @throws DataNotFoundException
     * @throws InvalidJsonDataException
     */
    #[Route("/api/user/audiobook/rating/get", name: "userAudiobookRatingGet", methods: ["POST"])]
    #[AuthValidation(checkAuthToken: true, roles: ["User"])]
    #[OA\Post(
        description: "Endpoint is getting audiobook overall rating",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                ref: new Model(type: UserAudiobookRatingGetQuery::class),
                type: "object"
            ),
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Success",
                content: new Model(type: UserAudiobookRatingGetSuccessModel::class)
            )
        ]
    )]
    public function userAudiobookRatingGet(
        Request                        $request,
        RequestServiceInterface        $requestService,
        AuthorizedUserServiceInterface $authorizedUserService,
        LoggerInterface                $endpointLogger,
        AudiobookRepository            $audiobookRepository,
        TranslateService               $translateService
    ): Response
    {
        $userAudiobookRatingGetQuery = $requestService->getRequestBodyContent($request, UserAudiobookRatingGetQuery::class);

        if ($userAudiobookRatingGetQuery instanceof UserAudiobookRatingGetQuery) {

            $audiobook = $audiobookRepository->getAudiobookByCategoryKeyAndId($userAudiobookRatingGetQuery->getAudiobookId(), $userAudiobookRatingGetQuery->getCategoryKey());

            if ($audiobook == null) {
                $endpointLogger->error("Audiobook dont exist");
                $translateService->setPreferredLanguage($request);
                throw new DataNotFoundException([$translateService->getTranslation("AudiobookDontExists")]);
            }

            return ResponseTool::getResponse(new UserAudiobookRatingGetSuccessModel($audiobook->getAvgRating()));

        }

        $endpointLogger->error("Invalid given Query");
        $translateService->setPreferredLanguage($request);
        throw new InvalidJsonDataException($translateService);
    }

    /**
     * @param Request $request
     * @param RequestServiceInterface $requestService
     * @param AuthorizedUserServiceInterface $authorizedUserService
     * @param LoggerInterface $endpointLogger
     * @param AudiobookRepository $audiobookRepository
     * @param AudiobookUserCommentRepository $audiobookUserCommentRepository
     * @param AudiobookInfoRepository $audiobookInfoRepository
     * @param TranslateService $translateService
     * @return Response
     * @throws DataNotFoundException
     * @throws InvalidJsonDataException
     */
    #[Route("/api/user/audiobook/comment/add", name: "userAudiobookCommentAdd", methods: ["PUT"])]
    #[AuthValidation(checkAuthToken: true, roles: ["User"])]
    #[OA\Put(
        description: "Endpoint is adding comment for given audiobook",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                ref: new Model(type: UserAudiobookCommentAddQuery::class),
                type: "object"
            ),
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: "Success"
            )
        ]
    )]
    public function userAudiobookCommentAdd(
        Request                        $request,
        RequestServiceInterface        $requestService,
        AuthorizedUserServiceInterface $authorizedUserService,
        LoggerInterface                $endpointLogger,
        AudiobookRepository            $audiobookRepository,
        AudiobookUserCommentRepository $audiobookUserCommentRepository,
        AudiobookInfoRepository        $audiobookInfoRepository,
        TranslateService               $translateService
    ): Response
    {
        $userAudiobookCommentAddQuery = $requestService->getRequestBodyContent($request, UserAudiobookCommentAddQuery::class);

        if ($userAudiobookCommentAddQuery instanceof UserAudiobookCommentAddQuery) {

            $user = $authorizedUserService->getAuthorizedUser();

            $audiobook = $audiobookRepository->getAudiobookByCategoryKeyAndId($userAudiobookCommentAddQuery->getAudiobookId(), $userAudiobookCommentAddQuery->getCategoryKey());

            if ($audiobook == null) {
                $endpointLogger->error("Audiobook dont exist");
                $translateService->setPreferredLanguage($request);
                throw new DataNotFoundException([$translateService->getTranslation("AudiobookDontExists")]);
            }

            $watchedParts = $audiobookInfoRepository->findBy([
                "audiobook" => $audiobook->getId(),
                "user" => $user->getId(),
                "watched" => true
            ]);

            if (floor($audiobook->getParts() / 2) > $watchedParts) {
                $endpointLogger->error("Audiobook dont exist");
                $translateService->setPreferredLanguage($request);
                throw new DataNotFoundException([$translateService->getTranslation("AudiobookNotListened")]);
            }

            $audiobookComment = new AudiobookUserComment($userAudiobookCommentAddQuery->getComment(), $audiobook, $user);

            $additionalData = $userAudiobookCommentAddQuery->getAdditionalData();

            if (array_key_exists("parentId", $additionalData)) {

                $audiobookParentComment = $audiobookUserCommentRepository->findOneBy([
                    "id" => $additionalData["parentId"]
                ]);

                if ($audiobookParentComment == null || $audiobookParentComment->getParent() != null) {
                    $endpointLogger->error("Audiobook Parent Comment dont exist");
                    $translateService->setPreferredLanguage($request);
                    throw new DataNotFoundException([$translateService->getTranslation("AudiobookParentCommentDontExists")]);
                }

                $audiobookComment->setParent($audiobookParentComment);
            }

            $audiobookUserCommentRepository->add($audiobookComment);

            return ResponseTool::getResponse(httpCode: 201);

        }

        $endpointLogger->error("Invalid given Query");
        $translateService->setPreferredLanguage($request);
        throw new InvalidJsonDataException($translateService);
    }

    /**
     * @param Request $request
     * @param RequestServiceInterface $requestService
     * @param AuthorizedUserServiceInterface $authorizedUserService
     * @param LoggerInterface $endpointLogger
     * @param AudiobookRepository $audiobookRepository
     * @param AudiobookUserCommentRepository $audiobookUserCommentRepository
     * @param TranslateService $translateService
     * @return Response
     * @throws DataNotFoundException
     * @throws InvalidJsonDataException
     */
    #[Route("/api/user/audiobook/comment/edit", name: "userAudiobookCommentEdit", methods: ["PATCH"])]
    #[AuthValidation(checkAuthToken: true, roles: ["User"])]
    #[OA\Patch(
        description: "Endpoint is editing given comment",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                ref: new Model(type: UserAudiobookCommentEditQuery::class),
                type: "object"
            ),
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Success",
            )
        ]
    )]
    public function userAudiobookCommentEdit(
        Request                        $request,
        RequestServiceInterface        $requestService,
        AuthorizedUserServiceInterface $authorizedUserService,
        LoggerInterface                $endpointLogger,
        AudiobookRepository            $audiobookRepository,
        AudiobookUserCommentRepository $audiobookUserCommentRepository,
        TranslateService               $translateService
    ): Response
    {
        $userAudiobookCommentEditQuery = $requestService->getRequestBodyContent($request, UserAudiobookCommentEditQuery::class);

        if ($userAudiobookCommentEditQuery instanceof UserAudiobookCommentEditQuery) {

            $user = $authorizedUserService->getAuthorizedUser();

            $audiobook = $audiobookRepository->getAudiobookByCategoryKeyAndId($userAudiobookCommentEditQuery->getAudiobookId(), $userAudiobookCommentEditQuery->getCategoryKey());

            if ($audiobook == null) {
                $endpointLogger->error("Audiobook dont exist");
                $translateService->setPreferredLanguage($request);
                throw new DataNotFoundException([$translateService->getTranslation("AudiobookDontExists")]);
            }

            $audiobookComment = $audiobookUserCommentRepository->findOneBy([
                "id" => $userAudiobookCommentEditQuery->getAudiobookCommentId(),
                "user" => $user->getId()
            ]);

            if ($audiobookComment == null) {
                $endpointLogger->error("Audiobook Comment dont exist");
                $translateService->setPreferredLanguage($request);
                throw new DataNotFoundException([$translateService->getTranslation("AudiobookCommentDontExists")]);
            }

            $audiobookComment->setDeleted($userAudiobookCommentEditQuery->isDeleted());
            $audiobookComment->setComment($userAudiobookCommentEditQuery->getComment());
            $audiobookComment->setEdited(true);

            $additionalData = $userAudiobookCommentEditQuery->getAdditionalData();

            if (array_key_exists("parentId", $additionalData)) {

                $audiobookParentComment = $audiobookUserCommentRepository->findOneBy([
                    "id" => $additionalData["parentId"]
                ]);

                if ($audiobookParentComment == null) {
                    $endpointLogger->error("Audiobook Parent Comment dont exist");
                    $translateService->setPreferredLanguage($request);
                    throw new DataNotFoundException([$translateService->getTranslation("AudiobookParentCommentDontExists")]);
                }

                $audiobookComment->setParent($audiobookParentComment);
            }

            $audiobookUserCommentRepository->add($audiobookComment);

            return ResponseTool::getResponse();

        }

        $endpointLogger->error("Invalid given Query");
        $translateService->setPreferredLanguage($request);
        throw new InvalidJsonDataException($translateService);
    }

    /**
     * @param Request $request
     * @param RequestServiceInterface $requestService
     * @param AuthorizedUserServiceInterface $authorizedUserService
     * @param LoggerInterface $endpointLogger
     * @param AudiobookUserCommentLikeRepository $audiobookUserCommentLikeRepository
     * @param AudiobookUserCommentRepository $audiobookUserCommentRepository
     * @param TranslateService $translateService
     * @return Response
     * @throws DataNotFoundException
     * @throws InvalidJsonDataException
     */
    #[Route("/api/user/audiobook/comment/like/add", name: "userAudiobookCommentLikeAdd", methods: ["PATCH"])]
    #[AuthValidation(checkAuthToken: true, roles: ["User"])]
    #[OA\Patch(
        description: "Endpoint is adding/editing user audiobook comment like",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                ref: new Model(type: UserAudiobookCommentLikeAddQuery::class),
                type: "object"
            ),
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Success",
            )
        ]
    )]
    public function userAudiobookCommentLikeAdd(
        Request                            $request,
        RequestServiceInterface            $requestService,
        AuthorizedUserServiceInterface     $authorizedUserService,
        LoggerInterface                    $endpointLogger,
        AudiobookUserCommentLikeRepository $audiobookUserCommentLikeRepository,
        AudiobookUserCommentRepository     $audiobookUserCommentRepository,
        TranslateService                   $translateService
    ): Response
    {
        $userAudiobookCommentLikeAddQuery = $requestService->getRequestBodyContent($request, UserAudiobookCommentLikeAddQuery::class);

        if ($userAudiobookCommentLikeAddQuery instanceof UserAudiobookCommentLikeAddQuery) {

            $user = $authorizedUserService->getAuthorizedUser();

            $comment = $audiobookUserCommentRepository->findOneBy([
                "id" => $userAudiobookCommentLikeAddQuery->getCommentId()
            ]);

            if ($comment == null) {
                $endpointLogger->error("Audiobook dont exist");
                $translateService->setPreferredLanguage($request);
                throw new DataNotFoundException([$translateService->getTranslation("AudiobookCommentDontExists")]);
            }

            $commentLike = $audiobookUserCommentLikeRepository->findOneBy([
                "audiobookUserComment" => $comment->getId(),
                "user" => $user->getId()
            ]);

            if ($commentLike == null) {
                $commentLike = new AudiobookUserCommentLike($userAudiobookCommentLikeAddQuery->isLike(), $comment, $user);
            } else {
                $commentLike->setLiked($userAudiobookCommentLikeAddQuery->isLike());
                if ($commentLike->getDeleted()) {
                    $commentLike->setDeleted(!$commentLike->getDeleted());
                }
            }

            $audiobookUserCommentLikeRepository->add($commentLike);

            return ResponseTool::getResponse();

        }

        $endpointLogger->error("Invalid given Query");
        $translateService->setPreferredLanguage($request);
        throw new InvalidJsonDataException($translateService);
    }

    /**
     * @param Request $request
     * @param RequestServiceInterface $requestService
     * @param AuthorizedUserServiceInterface $authorizedUserService
     * @param LoggerInterface $endpointLogger
     * @param AudiobookUserCommentRepository $audiobookUserCommentRepository
     * @param AudiobookUserCommentLikeRepository $audiobookUserCommentLikeRepository
     * @param TranslateService $translateService
     * @return Response
     * @throws DataNotFoundException
     * @throws InvalidJsonDataException
     */
    #[Route("/api/user/audiobook/comment/like/delete", name: "userAudiobookCommentLikeDelete", methods: ["DELETE"])]
    #[AuthValidation(checkAuthToken: true, roles: ["User"])]
    #[OA\Delete(
        description: "Endpoint is adding/editing user audiobook comment like",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                ref: new Model(type: UserAudiobookCommentLikeDeleteQuery::class),
                type: "object"
            ),
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Success",
            )
        ]
    )]
    public function userAudiobookCommentLikeDelete(
        Request                            $request,
        RequestServiceInterface            $requestService,
        AuthorizedUserServiceInterface     $authorizedUserService,
        LoggerInterface                    $endpointLogger,
        AudiobookUserCommentRepository     $audiobookUserCommentRepository,
        AudiobookUserCommentLikeRepository $audiobookUserCommentLikeRepository,
        TranslateService                   $translateService
    ): Response
    {
        $userAudiobookCommentLikeDeleteQuery = $requestService->getRequestBodyContent($request, UserAudiobookCommentLikeDeleteQuery::class);

        if ($userAudiobookCommentLikeDeleteQuery instanceof UserAudiobookCommentLikeDeleteQuery) {

            $user = $authorizedUserService->getAuthorizedUser();

            $comment = $audiobookUserCommentRepository->findOneBy([
                "id" => $userAudiobookCommentLikeDeleteQuery->getCommentId()
            ]);

            if ($comment == null) {
                $endpointLogger->error("Audiobook dont exist");
                $translateService->setPreferredLanguage($request);
                throw new DataNotFoundException([$translateService->getTranslation("AudiobookCommentDontExists")]);
            }

            $commentLike = $audiobookUserCommentLikeRepository->findOneBy([
                "audiobookUserComment" => $comment->getId(),
                "user" => $user->getId(),
                "deleted" => false
            ]);

            if ($commentLike == null) {
                $endpointLogger->error("Comment like dont exist");
                $translateService->setPreferredLanguage($request);
                throw new DataNotFoundException([$translateService->getTranslation("AudiobookCommentLikeDontExists")]);
            }

            $commentLike->setDeleted(true);

            $audiobookUserCommentLikeRepository->add($commentLike);

            return ResponseTool::getResponse();

        }

        $endpointLogger->error("Invalid given Query");
        $translateService->setPreferredLanguage($request);
        throw new InvalidJsonDataException($translateService);
    }

    /**
     * @param Request $request
     * @param RequestServiceInterface $requestService
     * @param AuthorizedUserServiceInterface $authorizedUserService
     * @param LoggerInterface $endpointLogger
     * @param AudiobookUserCommentRepository $audiobookUserCommentRepository
     * @param AudiobookUserCommentLikeRepository $audiobookUserCommentLikeRepository
     * @param AudiobookRepository $audiobookRepository
     * @param TranslateService $translateService
     * @return Response
     * @throws DataNotFoundException
     * @throws InvalidJsonDataException
     */
    #[Route("/api/user/audiobook/comment/get", name: "userAudiobookCommentGet", methods: ["POST"])]
    #[AuthValidation(checkAuthToken: true, roles: ["User"])]
    #[OA\Post(
        description: "Endpoint is returning comments for given audiobook for user",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                ref: new Model(type: UserAudiobookCommentGetQuery::class),
                type: "object"
            ),
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Success",
                content: new Model(type: AudiobookCommentsSuccessModel::class)
            )
        ]
    )]
    public function userAudiobookCommentGet(
        Request                            $request,
        RequestServiceInterface            $requestService,
        AuthorizedUserServiceInterface     $authorizedUserService,
        LoggerInterface                    $endpointLogger,
        AudiobookUserCommentRepository     $audiobookUserCommentRepository,
        AudiobookUserCommentLikeRepository $audiobookUserCommentLikeRepository,
        AudiobookRepository                $audiobookRepository,
        TranslateService                   $translateService
    ): Response
    {
        $userAudiobookCommentGetQuery = $requestService->getRequestBodyContent($request, UserAudiobookCommentGetQuery::class);

        if ($userAudiobookCommentGetQuery instanceof UserAudiobookCommentGetQuery) {

            $user = $authorizedUserService->getAuthorizedUser();

            $audiobook = $audiobookRepository->getAudiobookByCategoryKeyAndId($userAudiobookCommentGetQuery->getAudiobookId(), $userAudiobookCommentGetQuery->getCategoryKey());


            if ($audiobook == null) {
                $endpointLogger->error("Audiobook dont exist");
                $translateService->setPreferredLanguage($request);
                throw new DataNotFoundException([$translateService->getTranslation("AudiobookCommentDontExists")]);
            }

            $audiobookUserComments = $audiobookUserCommentRepository->findBy([
                "parent" => null,
                "audiobook" => $audiobook->getId(),
                "deleted" => false
            ]);

            $treeGenerator = new BuildAudiobookCommentTreeGenerator($audiobookUserComments, $audiobookUserCommentRepository, $audiobookUserCommentLikeRepository, $user, false);

            $successModel = new AudiobookCommentsSuccessModel($treeGenerator->generate());

            return ResponseTool::getResponse($successModel);
        }

        $endpointLogger->error("Invalid given Query");
        $translateService->setPreferredLanguage($request);
        throw new InvalidJsonDataException($translateService);
    }
}