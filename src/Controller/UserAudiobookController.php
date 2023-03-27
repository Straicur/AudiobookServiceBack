<?php

namespace App\Controller;

use App\Annotation\AuthValidation;
use App\Entity\AudiobookInfo;
use App\Entity\AudiobookRating;
use App\Entity\AudiobookUserComment;
use App\Entity\AudiobookUserCommentLike;
use App\Exception\DataNotFoundException;
use App\Exception\InvalidJsonDataException;
use App\Model\AdminAudiobookCategoryModel;
use App\Model\DataNotFoundModel;
use App\Model\JsonDataInvalidModel;
use App\Model\NotAuthorizeModel;
use App\Model\PermissionNotGrantedModel;
use App\Model\UserAudiobookCategoryModel;
use App\Model\UserAudiobookDetailModel;
use App\Model\UserAudiobookDetailsSuccessModel;
use App\Model\UserAudiobookInfoSuccessModel;
use App\Model\UserAudiobookModel;
use App\Model\UserAudiobookRatingGetSuccessModel;
use App\Model\UserAudiobooksSuccessModel;
use App\Model\UserCategoryModel;
use App\Model\UserMyListAudiobooksSuccessModel;
use App\Model\UserProposedAudiobooksSuccessModel;
use App\Query\UserAudiobookCommentAddQuery;
use App\Query\UserAudiobookCommentEditQuery;
use App\Query\UserAudiobookCommentLikeAddQuery;
use App\Query\UserAudiobookCommentLikeDeleteQuery;
use App\Query\UserAudiobookDetailsQuery;
use App\Query\UserAudiobookInfoAddQuery;
use App\Query\UserAudiobookInfoQuery;
use App\Query\UserAudiobookLikeQuery;
use App\Query\UserAudiobookRatingAddQuery;
use App\Query\UserAudiobookRatingGetQuery;
use App\Query\UserAudiobooksQuery;
use App\Repository\AudiobookCategoryRepository;
use App\Repository\AudiobookInfoRepository;
use App\Repository\AudiobookRatingRepository;
use App\Repository\AudiobookRepository;
use App\Repository\AudiobookUserCommentLikeRepository;
use App\Repository\AudiobookUserCommentRepository;
use App\Repository\MyListRepository;
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

/**
 * UserAudiobookController
 */
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
        AudiobookCategoryRepository    $audiobookCategoryRepository
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
                } elseif ($index < $maxResult) {

                    $categoryModel = new UserCategoryModel($category->getName(), $category->getCategoryKey());

                    $audiobooks = $audiobookRepository->getActiveCategoryAudiobooks($category);

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

            $successModel->setMaxPage(floor(count($allCategories) / $userAudiobooksQuery->getLimit()));

            return ResponseTool::getResponse($successModel);
        } else {
            $endpointLogger->error("Invalid given Query");
            throw new InvalidJsonDataException("userAudiobooks.invalid.query");
        }
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
        AudiobookCategoryRepository    $audiobookCategoryRepository
    ): Response
    {
        $userAudiobookDetailsQuery = $requestService->getRequestBodyContent($request, UserAudiobookDetailsQuery::class);

        if ($userAudiobookDetailsQuery instanceof UserAudiobookDetailsQuery) {

            $audiobook = $audiobookRepository->getAudiobookByCategoryKeyAndId($userAudiobookDetailsQuery->getAudiobookId(), $userAudiobookDetailsQuery->getCategoryKey());

            if ($audiobook == null) {
                $endpointLogger->error("Audiobook dont exist");
                throw new DataNotFoundException(["userAudiobook.details.audiobook.not.exist"]);
            }

            $categories = $audiobookCategoryRepository->getAudiobookActiveCategories($audiobook);

            $audiobookCategories = [];

            foreach ($categories as $category) {
                $audiobookCategories[] = new AdminAudiobookCategoryModel(
                    $category->getId(),
                    $category->getName(),
                    $category->getActive(),
                    $category->getCategoryKey()
                );
            }

            $successModel = new UserAudiobookDetailsSuccessModel(
                $audiobook->getId(),
                $audiobook->getTitle(),
                $audiobook->getAuthor(),
                $audiobook->getVersion(),
                $audiobook->getAlbum(),
                $audiobook->getYear(),
                $audiobook->getDuration(),
                $audiobook->getSize(),
                $audiobook->getParts(),
                $audiobook->getDescription(),
                $audiobook->getAge(),
                $audiobookCategories
            );

            return ResponseTool::getResponse($successModel);
        } else {
            $endpointLogger->error("Invalid given Query");
            throw new InvalidJsonDataException("userAudiobook.details.invalid.query");
        }
    }

    /**
     * @param Request $request
     * @param RequestServiceInterface $requestService
     * @param AuthorizedUserServiceInterface $authorizedUserService
     * @param LoggerInterface $endpointLogger
     * @param AudiobookRepository $audiobookRepository
     * @param AudiobookInfoRepository $audiobookInfoRepository
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
        AudiobookInfoRepository        $audiobookInfoRepository
    ): Response
    {
        $userAudiobookInfoQuery = $requestService->getRequestBodyContent($request, UserAudiobookInfoQuery::class);

        if ($userAudiobookInfoQuery instanceof UserAudiobookInfoQuery) {

            $user = $authorizedUserService->getAuthorizedUser();

            $audiobook = $audiobookRepository->getAudiobookByCategoryKeyAndId($userAudiobookInfoQuery->getAudiobookId(), $userAudiobookInfoQuery->getCategoryKey());

            if ($audiobook == null) {
                $endpointLogger->error("Audiobook dont exist");
                throw new DataNotFoundException(["userAudiobook.info.audiobook.not.exist"]);
            }

            $audiobookInfo = $audiobookInfoRepository->findOneBy([
                "audiobook" => $audiobook->getId(),
                "active" => true,
                "user" => $user->getId()
            ]);

            if ($audiobookInfo == null) {
                $endpointLogger->error("AudiobookInfo dont exist");
                throw new DataNotFoundException(["userAudiobook.info.audiobookInfo.not.exist"]);
            }

            $successModel = new UserAudiobookInfoSuccessModel(
                $audiobookInfo->getPart(),
                $audiobookInfo->getEndedTime(),
                $audiobookInfo->getWatchingDate()
            );

            return ResponseTool::getResponse($successModel);
        } else {
            $endpointLogger->error("Invalid given Query");
            throw new InvalidJsonDataException("userAudiobook.info.invalid.query");
        }
    }

    /**
     * @param Request $request
     * @param RequestServiceInterface $requestService
     * @param AuthorizedUserServiceInterface $authorizedUserService
     * @param LoggerInterface $endpointLogger
     * @param AudiobookRepository $audiobookRepository
     * @param MyListRepository $myListRepository
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
        MyListRepository               $myListRepository
    ): Response
    {
        $userAudiobookLikeQuery = $requestService->getRequestBodyContent($request, UserAudiobookLikeQuery::class);

        if ($userAudiobookLikeQuery instanceof UserAudiobookLikeQuery) {

            $user = $authorizedUserService->getAuthorizedUser();

            $audiobook = $audiobookRepository->getAudiobookByCategoryKeyAndId($userAudiobookLikeQuery->getAudiobookId(), $userAudiobookLikeQuery->getCategoryKey());

            if ($audiobook == null) {
                $endpointLogger->error("Audiobook dont exist");
                throw new DataNotFoundException(["userAudiobook.like.audiobook.not.exist"]);
            }

            $myList = $user->getMyList();

            if ($myListRepository->getAudiobookINMyList($user, $audiobook)) {
                $myList->removeAudiobook($audiobook);
            } else {
                $myList->addAudiobook($audiobook);
            }

            $myListRepository->add($myList);

            return ResponseTool::getResponse();
        } else {
            $endpointLogger->error("Invalid given Query");
            throw new InvalidJsonDataException("userAudiobook.like.invalid.query");
        }
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
        AudiobookInfoRepository        $audiobookInfoRepository
    ): Response
    {
        $userAudiobookInfoAddQuery = $requestService->getRequestBodyContent($request, UserAudiobookInfoAddQuery::class);

        if ($userAudiobookInfoAddQuery instanceof UserAudiobookInfoAddQuery) {

            $user = $authorizedUserService->getAuthorizedUser();

            $audiobook = $audiobookRepository->getAudiobookByCategoryKeyAndId($userAudiobookInfoAddQuery->getAudiobookId(), $userAudiobookInfoAddQuery->getCategoryKey());

            if ($audiobook == null) {
                $endpointLogger->error("Audiobook dont exist");
                throw new DataNotFoundException(["userAudiobook.add.info.audiobook.not.exist"]);
            }

            $audiobookInfoRepository->deActiveAudiobookInfos($user, $audiobook);

            $audiobookInfo = $audiobookInfoRepository->findOneBy([
                "audiobook" => $audiobook->getId(),
                "part" => $userAudiobookInfoAddQuery->getPart(),
                "user" => $user->getId()
            ]);

            if ($audiobookInfo != null) {
                $audiobookInfo->setEndedTime($userAudiobookInfoAddQuery->getEndedTime());
                $audiobookInfo->setWatchingDate($userAudiobookInfoAddQuery->getWatchingDate());
                $audiobookInfo->setWatched($userAudiobookInfoAddQuery->getWatched());
                $audiobookInfo->setActive(true);
            } else {
                $audiobookInfo = new AudiobookInfo(
                    $user,
                    $audiobook,
                    $userAudiobookInfoAddQuery->getPart(),
                    $userAudiobookInfoAddQuery->getEndedTime(),
                    $userAudiobookInfoAddQuery->getWatchingDate(),
                    $userAudiobookInfoAddQuery->getWatched()
                );
            }

            $audiobookInfoRepository->add($audiobookInfo);

            return ResponseTool::getResponse(httpCode: 201);
        } else {
            $endpointLogger->error("Invalid given Query");
            throw new InvalidJsonDataException("userAudiobook.add.info.invalid.query");
        }
    }

    /**
     * @param Request $request
     * @param RequestServiceInterface $requestService
     * @param AuthorizedUserServiceInterface $authorizedUserService
     * @param LoggerInterface $endpointLogger
     * @param AudiobookRepository $audiobookRepository
     * @param AudiobookInfoRepository $audiobookInfoRepository
     * @param AudiobookRatingRepository $ratingRepository
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
        AudiobookRatingRepository      $ratingRepository
    ): Response
    {
        $userAudiobookRatingAddQuery = $requestService->getRequestBodyContent($request, UserAudiobookRatingAddQuery::class);

        if ($userAudiobookRatingAddQuery instanceof UserAudiobookRatingAddQuery) {

            $user = $authorizedUserService->getAuthorizedUser();

            $audiobook = $audiobookRepository->getAudiobookByCategoryKeyAndId($userAudiobookRatingAddQuery->getAudiobookId(), $userAudiobookRatingAddQuery->getCategoryKey());

            if ($audiobook == null) {
                $endpointLogger->error("Audiobook dont exist");
                throw new DataNotFoundException(["userAudiobook.add.rating.audiobook.not.exist"]);
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
                    throw new DataNotFoundException(["userAudiobook.add.rating.audiobook.not.watched"]);
                }

                $rating = new AudiobookRating($audiobook, $userAudiobookRatingAddQuery->isRating(), $user);
            }

            $ratingRepository->add($rating);

            return ResponseTool::getResponse();

        } else {
            $endpointLogger->error("Invalid given Query");
            throw new InvalidJsonDataException("userAudiobook.add.rating.invalid.query");
        }
    }

    /**
     * @param Request $request
     * @param RequestServiceInterface $requestService
     * @param AuthorizedUserServiceInterface $authorizedUserService
     * @param LoggerInterface $endpointLogger
     * @param AudiobookRepository $audiobookRepository
     * @param AudiobookRatingRepository $ratingRepository
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
                response: 201,
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
        AudiobookRatingRepository      $ratingRepository
    ): Response
    {
        $userAudiobookRatingGetQuery = $requestService->getRequestBodyContent($request, UserAudiobookRatingGetQuery::class);

        if ($userAudiobookRatingGetQuery instanceof UserAudiobookRatingGetQuery) {

            $user = $authorizedUserService->getAuthorizedUser();

            $audiobook = $audiobookRepository->getAudiobookByCategoryKeyAndId($userAudiobookRatingGetQuery->getAudiobookId(), $userAudiobookRatingGetQuery->getCategoryKey());

            if ($audiobook == null) {
                $endpointLogger->error("Audiobook dont exist");
                throw new DataNotFoundException(["userAudiobook.add.rating.audiobook.not.exist"]);
            }

            return ResponseTool::getResponse(new UserAudiobookRatingGetSuccessModel($audiobook->getAvgRating()));

        } else {
            $endpointLogger->error("Invalid given Query");
            throw new InvalidJsonDataException("userAudiobook.add.rating.invalid.query");
        }
    }

    /**
     * @param Request $request
     * @param RequestServiceInterface $requestService
     * @param AuthorizedUserServiceInterface $authorizedUserService
     * @param LoggerInterface $endpointLogger
     * @param AudiobookRepository $audiobookRepository
     * @param AudiobookUserCommentRepository $audiobookUserCommentRepository
     * @param AudiobookInfoRepository $audiobookInfoRepository
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
        AudiobookInfoRepository        $audiobookInfoRepository
    ): Response
    {
        $userAudiobookCommentAddQuery = $requestService->getRequestBodyContent($request, UserAudiobookCommentAddQuery::class);

        if ($userAudiobookCommentAddQuery instanceof UserAudiobookCommentAddQuery) {

            $user = $authorizedUserService->getAuthorizedUser();

            $audiobook = $audiobookRepository->getAudiobookByCategoryKeyAndId($userAudiobookCommentAddQuery->getAudiobookId(), $userAudiobookCommentAddQuery->getCategoryKey());

            if ($audiobook == null) {
                $endpointLogger->error("Audiobook dont exist");
                throw new DataNotFoundException(["userAudiobook.add.comment.audiobook.not.exist"]);
            }

            $watchedParts = $audiobookInfoRepository->findBy([
                "audiobook" => $audiobook->getId(),
                "user" => $user->getId(),
                "watched" => true
            ]);

            if (floor($audiobook->getParts() / 2) > $watchedParts) {
                $endpointLogger->error("Audiobook dont exist");
                throw new DataNotFoundException(["userAudiobook.add.comment.audiobook.not.watched"]);
            }

            $audiobookComment = new AudiobookUserComment($userAudiobookCommentAddQuery->getComment(), $audiobook, $user);

            $additionalData = $userAudiobookCommentAddQuery->getAdditionalData();

            if (array_key_exists("parentId", $additionalData)) {

                $audiobookParentComment = $audiobookUserCommentRepository->findOneBy([
                    "id" => $additionalData["parentId"]
                ]);

                if ($audiobookParentComment == null || $audiobookParentComment->getParent() != null) {
                    $endpointLogger->error("Audiobook Parent Comment dont exist");
                    throw new DataNotFoundException(["userAudiobook.edit.comment.audiobook.parent.comment.not.exist"]);
                }

                $audiobookComment->setParent($audiobookParentComment);
            }

            $audiobookUserCommentRepository->add($audiobookComment);

            return ResponseTool::getResponse();

        } else {
            $endpointLogger->error("Invalid given Query");
            throw new InvalidJsonDataException("userAudiobook.add.comment.invalid.query");
        }
    }

    /**
     * @param Request $request
     * @param RequestServiceInterface $requestService
     * @param AuthorizedUserServiceInterface $authorizedUserService
     * @param LoggerInterface $endpointLogger
     * @param AudiobookRepository $audiobookRepository
     * @param AudiobookUserCommentRepository $audiobookUserCommentRepository
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
        AudiobookUserCommentRepository $audiobookUserCommentRepository
    ): Response
    {
        $userAudiobookCommentEditQuery = $requestService->getRequestBodyContent($request, UserAudiobookCommentEditQuery::class);

        if ($userAudiobookCommentEditQuery instanceof UserAudiobookCommentEditQuery) {

            $user = $authorizedUserService->getAuthorizedUser();

            $audiobook = $audiobookRepository->getAudiobookByCategoryKeyAndId($userAudiobookCommentEditQuery->getAudiobookId(), $userAudiobookCommentEditQuery->getCategoryKey());

            if ($audiobook == null) {
                $endpointLogger->error("Audiobook dont exist");
                throw new DataNotFoundException(["userAudiobook.edit.comment.audiobook.not.exist"]);
            }

            $audiobookComment = $audiobookUserCommentRepository->findOneBy([
                "id" => $userAudiobookCommentEditQuery->getAudiobookCommentId(),
                "user" => $user->getId()
            ]);

            if ($audiobookComment == null) {
                $endpointLogger->error("Audiobook Comment dont exist");
                throw new DataNotFoundException(["userAudiobook.edit.comment.audiobook.comment.not.exist"]);
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
                    throw new DataNotFoundException(["userAudiobook.edit.comment.audiobook.parent.comment.not.exist"]);
                }

                $audiobookComment->setParent($audiobookParentComment);
            }

            $audiobookUserCommentRepository->add($audiobookComment);

            return ResponseTool::getResponse();

        } else {
            $endpointLogger->error("Invalid given Query");
            throw new InvalidJsonDataException("userAudiobook.edit.comment.invalid.query");
        }
    }

    /**
     * @param Request $request
     * @param RequestServiceInterface $requestService
     * @param AuthorizedUserServiceInterface $authorizedUserService
     * @param LoggerInterface $endpointLogger
     * @param AudiobookUserCommentLikeRepository $audiobookUserCommentLikeRepository
     * @param AudiobookUserCommentRepository $audiobookUserCommentRepository
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
        AudiobookUserCommentRepository     $audiobookUserCommentRepository
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
                throw new DataNotFoundException(["userAudiobook.add.comment.like.comment.not.exist"]);
            }

            $commentLike = $audiobookUserCommentLikeRepository->findOneBy([
                "audiobookUserComment" => $comment->getId(),
                "user" => $user->getId(),
                "deleted" => false
            ]);

            if ($commentLike == null) {
                $commentLike = new AudiobookUserCommentLike($userAudiobookCommentLikeAddQuery->isLike(), $comment, $user);
            } else {
                $commentLike->setLiked($userAudiobookCommentLikeAddQuery->isLike());
            }

            $audiobookUserCommentLikeRepository->add($commentLike);

            return ResponseTool::getResponse();

        } else {
            $endpointLogger->error("Invalid given Query");
            throw new InvalidJsonDataException("userAudiobook.add.comment.like.invalid.query");
        }
    }

    /**
     * @param Request $request
     * @param RequestServiceInterface $requestService
     * @param AuthorizedUserServiceInterface $authorizedUserService
     * @param LoggerInterface $endpointLogger
     * @param AudiobookUserCommentLikeRepository $audiobookUserCommentLikeRepository
     * @return Response
     * @throws DataNotFoundException
     * @throws InvalidJsonDataException
     */
    #[Route("/api/user/audiobook/comment/like/delete", name: "userAudiobookCommentLikeDelete", methods: ["DELETE"])]
    #[AuthValidation(checkAuthToken: true, roles: ["User"])]
    #[OA\Put(
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
        Request                        $request,
        RequestServiceInterface        $requestService,
        AuthorizedUserServiceInterface $authorizedUserService,
        LoggerInterface                $endpointLogger,
        AudiobookUserCommentLikeRepository $audiobookUserCommentLikeRepository,
    ): Response
    {
        $userAudiobookCommentLikeDeleteQuery = $requestService->getRequestBodyContent($request, UserAudiobookCommentLikeDeleteQuery::class);

        if ($userAudiobookCommentLikeDeleteQuery instanceof UserAudiobookCommentLikeDeleteQuery) {

            $user = $authorizedUserService->getAuthorizedUser();

            $commentLike = $audiobookUserCommentLikeRepository->findOneBy([
                "id" => $userAudiobookCommentLikeDeleteQuery->getCommentLikeId(),
                "user" => $user->getId(),
                "deleted" => false
            ]);

            if ($commentLike == null) {
                $endpointLogger->error("Audiobook dont exist");
                throw new DataNotFoundException(["userAudiobook.delete.comment.like.comment.like.not.exist"]);
            }

            $commentLike->setDeleted(true);

            $audiobookUserCommentLikeRepository->add($commentLike);

            return ResponseTool::getResponse();

        } else {
            $endpointLogger->error("Invalid given Query");
            throw new InvalidJsonDataException("userAudiobook.delete.comment.like.invalid.query");
        }
    }
}