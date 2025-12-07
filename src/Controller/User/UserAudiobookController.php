<?php

declare(strict_types = 1);

namespace App\Controller\User;

use App\Annotation\AuthValidation;
use App\Entity\AudiobookInfo;
use App\Entity\AudiobookRating;
use App\Enums\Cache\CacheValidTime;
use App\Enums\Cache\UserCacheKeys;
use App\Enums\Cache\UserStockCacheTags;
use App\Enums\UserRolesNames;
use App\Exception\DataNotFoundException;
use App\Exception\InvalidJsonDataException;
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
use App\Model\User\UserCategoriesSuccessModel;
use App\Model\User\UserCategoryModel;
use App\Model\User\UserMyListAudiobooksSuccessModel;
use App\Model\User\UserProposedAudiobooksSuccessModel;
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
use App\Repository\AudiobookUserCommentRepository;
use App\Repository\MyListRepository;
use App\Service\AuthorizedUserServiceInterface;
use App\Service\RequestServiceInterface;
use App\Service\TranslateServiceInterface;
use App\Tool\ResponseTool;
use App\Tool\UserParentalControlTool;
use App\ValueGenerator\BuildUserAudiobookCategoryTreeGenerator;
use DateTime;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

use function count;

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
#[OA\Tag(name: 'UserAudiobook')]
class UserAudiobookController extends AbstractController
{
    public function __construct(private readonly RequestServiceInterface $requestService, private readonly AuthorizedUserServiceInterface $authorizedUserService, private readonly LoggerInterface $endpointLogger, private readonly AudiobookRepository $audiobookRepository, private readonly AudiobookCategoryRepository $audiobookCategoryRepository, private readonly TranslateServiceInterface $translateService, private readonly TagAwareCacheInterface $stockCache, private readonly MyListRepository $listRepository, private readonly AudiobookUserCommentRepository $audiobookUserCommentRepository, private readonly AudiobookInfoRepository $audiobookInfoRepository, private readonly AudiobookRatingRepository $audiobookRatingRepository, private readonly MyListRepository $myListRepository, private readonly AudiobookRatingRepository $ratingRepository) {}

    #[Route('/api/user/audiobooks', name: 'userAudiobooks', methods: ['POST'])]
    #[AuthValidation(checkAuthToken: true, roles: [UserRolesNames::USER])]
    #[OA\Post(
        description: 'Endpoint is returning list of categories with audiobooks',
        requestBody: new OA\RequestBody(
            required: true,
            content : new OA\JsonContent(
                ref : new Model(type: UserAudiobooksQuery::class),
                type: 'object',
            ),
        ),
        responses  : [
            new OA\Response(
                response   : 200,
                description: 'Success',
                content    : new Model(type: UserAudiobooksSuccessModel::class),
            ),
        ]
    )]
    public function userAudiobooks(
        Request $request,
    ): Response {
        $userAudiobooksQuery = $this->requestService->getRequestBodyContent($request, UserAudiobooksQuery::class);

        if ($userAudiobooksQuery instanceof UserAudiobooksQuery) {
            $user = $this->authorizedUserService::getAuthorizedUser();

            $successModel = $this->stockCache->get(
                UserCacheKeys::USER_AUDIOBOOKS->value . $user->getId() . '_' . $userAudiobooksQuery->getPage() . $userAudiobooksQuery->getLimit(),
                function (ItemInterface $item) use (
                    $user,
                    $userAudiobooksQuery
                ): UserAudiobooksSuccessModel {
                    $item->expiresAfter(CacheValidTime::TEN_MINUTES->value);
                    $item->tag(UserStockCacheTags::USER_AUDIOBOOKS->value);

                    $minResult = $userAudiobooksQuery->getPage() * $userAudiobooksQuery->getLimit();
                    $maxResult = $userAudiobooksQuery->getLimit() + $minResult;

                    $allCategories = $this->audiobookCategoryRepository->getCategoriesByCountAudiobooks();

                    $successModel = new UserAudiobooksSuccessModel();

                    $age = null;

                    if ($user->getUserInformation()->getBirthday() !== null) {
                        $userParentalControlTool = new UserParentalControlTool();
                        $age = $userParentalControlTool->getUserAudiobookAgeValue($user);
                    }

                    foreach ($allCategories as $index => $category) {
                        if ($index < $minResult) {
                            continue;
                        }

                        if ($index < $maxResult) {
                            $audiobooks = $this->audiobookRepository->getActiveCategoryAudiobooks($category, $age);

                            if ([] === $audiobooks) {
                                continue;
                            }

                            $categoryModel = new UserCategoryModel($category->getName(), $category->getCategoryKey());

                            foreach ($audiobooks as $audiobook) {
                                $categoryModel->addAudiobook(new UserAudiobookModel(
                                    (string) $audiobook->getId(),
                                    $audiobook->getTitle(),
                                    $audiobook->getAuthor(),
                                    $audiobook->getParts(),
                                    $audiobook->getAge(),
                                    $audiobook->getImgFile(),
                                ));
                            }

                            $successModel->addCategory($categoryModel);
                        } else {
                            break;
                        }
                    }

                    $successModel->setPage($userAudiobooksQuery->getPage());
                    $successModel->setLimit($userAudiobooksQuery->getLimit());

                    $successModel->setMaxPage((int) ceil(count($allCategories) / $userAudiobooksQuery->getLimit()));

                    return $successModel;
                }
            );

            return ResponseTool::getResponse($successModel);
        }

        $this->endpointLogger->error('Invalid given Query');
        $this->translateService->setPreferredLanguage($request);
        throw new InvalidJsonDataException($this->translateService);
    }

    #[Route('/api/user/audiobooks/search', name: 'userAudiobooksSearch', methods: ['POST'])]
    #[AuthValidation(checkAuthToken: true, roles: [UserRolesNames::USER])]
    #[OA\Post(
        description: 'Endpoint is returning list of audiobooks by title',
        requestBody: new OA\RequestBody(
            required: true,
            content : new OA\JsonContent(
                ref : new Model(type: UserAudiobooksSearchQuery::class),
                type: 'object',
            ),
        ),
        responses  : [
            new OA\Response(
                response   : 200,
                description: 'Success',
                content    : new Model(type: UserAudiobooksSearchSuccessModel::class),
            ),
        ]
    )]
    public function userAudiobooksSearch(
        Request $request,
    ): Response {
        $userAudiobooksSearchQuery = $this->requestService->getRequestBodyContent($request, UserAudiobooksSearchQuery::class);

        if ($userAudiobooksSearchQuery instanceof UserAudiobooksSearchQuery) {
            $age = null;
            $user = $this->authorizedUserService::getAuthorizedUser();

            if ($user->getUserInformation()->getBirthday() !== null) {
                $userParentalControlTool = new UserParentalControlTool();
                $age = $userParentalControlTool->getUserAudiobookAgeValue($user);
            }

            $allAudiobooks = $this->audiobookRepository->searchAudiobooksByNameOrKey($userAudiobooksSearchQuery->getTitle(), $userAudiobooksSearchQuery->getCategoryKey(), $age);

            $successModel = new UserAudiobooksSearchSuccessModel();

            foreach ($allAudiobooks as $audiobook) {
                $audiobookModel = new UserAudiobookDetailModel(
                    (string) $audiobook->getId(),
                    $audiobook->getTitle(),
                    $audiobook->getAuthor(),
                    $audiobook->getParts(),
                    $audiobook->getAge(),
                    $audiobook->getImgFile(),
                );

                foreach ($audiobook->getCategories() as $category) {
                    $audiobookModel->addCategory(new UserAudiobookCategoryModel(
                        $category->getName(),
                        $category->getCategoryKey(),
                    ));
                }

                $successModel->addAudiobook($audiobookModel);
            }

            return ResponseTool::getResponse($successModel);
        }

        $this->endpointLogger->error('Invalid given Query');
        $this->translateService->setPreferredLanguage($request);
        throw new InvalidJsonDataException($this->translateService);
    }

    #[Route('/api/user/proposed/audiobooks', name: 'userProposedAudiobooks', methods: ['GET'])]
    #[AuthValidation(checkAuthToken: true, roles: [UserRolesNames::USER])]
    #[OA\Get(
        description: 'Endpoint is returning list of proposed audiobooks',
        requestBody: new OA\RequestBody(),
        responses  : [
            new OA\Response(
                response   : 200,
                description: 'Success',
                content    : new Model(type: UserProposedAudiobooksSuccessModel::class),
            ),
        ]
    )]
    public function userProposedAudiobooks(): Response
    {
        $user = $this->authorizedUserService::getAuthorizedUser();
        $successModel = $this->stockCache->get(
            UserCacheKeys::USER_PROPOSED_AUDIOBOOKS->value . $user->getId(),
            function (ItemInterface $item) use ($user): UserProposedAudiobooksSuccessModel {
                $item->expiresAfter(CacheValidTime::DAY->value);
                $item->tag(UserStockCacheTags::USER_PROPOSED_AUDIOBOOKS->value);

                $audiobooks = $user->getProposedAudiobooks()->getAudiobooks();

                $successModel = new UserProposedAudiobooksSuccessModel();

                foreach ($audiobooks as $audiobook) {
                    if ($audiobook->getActive()) {
                        $audiobookModel = new UserAudiobookDetailModel(
                            (string) $audiobook->getId(),
                            $audiobook->getTitle(),
                            $audiobook->getAuthor(),
                            $audiobook->getParts(),
                            $audiobook->getAge(),
                            $audiobook->getImgFile(),
                        );

                        foreach ($audiobook->getCategories() as $category) {
                            $audiobookModel->addCategory(new UserAudiobookCategoryModel(
                                $category->getName(),
                                $category->getCategoryKey(),
                            ));
                        }

                        $successModel->addAudiobook($audiobookModel);
                    }
                }

                return $successModel;
            }
        );

        return ResponseTool::getResponse($successModel);
    }

    #[Route('/api/user/audiobook/details', name: 'userAudiobookDetails', methods: ['POST'])]
    #[AuthValidation(checkAuthToken: true, roles: [UserRolesNames::USER])]
    #[OA\Post(
        description: 'Endpoint is returning details of given audiobook',
        requestBody: new OA\RequestBody(
            required: true,
            content : new OA\JsonContent(
                ref : new Model(type: UserAudiobookDetailsQuery::class),
                type: 'object',
            ),
        ),
        responses  : [
            new OA\Response(
                response   : 200,
                description: 'Success',
                content    : new Model(type: UserAudiobookDetailsSuccessModel::class),
            ),
        ]
    )]
    public function userAudiobookDetails(
        Request $request,
    ): Response {
        $userAudiobookDetailsQuery = $this->requestService->getRequestBodyContent($request, UserAudiobookDetailsQuery::class);

        if ($userAudiobookDetailsQuery instanceof UserAudiobookDetailsQuery) {
            $audiobook = $this->audiobookRepository->getAudiobookByCategoryKeyAndId(
                $userAudiobookDetailsQuery->getAudiobookId(),
                $userAudiobookDetailsQuery->getCategoryKey()
            );

            if (null === $audiobook) {
                $this->endpointLogger->error('Audiobook dont exist');
                $this->translateService->setPreferredLanguage($request);
                throw new DataNotFoundException([$this->translateService->getTranslation('AudiobookDontExists')]);
            }

            if (!$audiobook->getActive()) {
                $this->endpointLogger->error('Audiobook is not active');
                $this->translateService->setPreferredLanguage($request);
                throw new DataNotFoundException([$this->translateService->getTranslation('AudiobookNotActive')]);
            }

            $user = $this->authorizedUserService::getAuthorizedUser();
            $successModel = $this->stockCache->get(
                UserCacheKeys::USER_AUDIOBOOK->value . $user->getId() . '_' . $audiobook->getId(),
                function (ItemInterface $item) use (
                    $user,
                    $audiobook
                ): UserAudiobookDetailsSuccessModel {
                    $item->expiresAfter(CacheValidTime::HALF_A_DAY->value);
                    $item->tag(UserStockCacheTags::USER_AUDIOBOOK_DETAIL->value);

                    $categories = $this->audiobookCategoryRepository->getAudiobookActiveCategories($audiobook);

                    $audiobookCategories = [];

                    foreach ($categories as $category) {
                        $audiobookCategories[] = new AudiobookDetailCategoryModel(
                            (string) $category->getId(),
                            $category->getName(),
                            $category->getActive(),
                            $category->getCategoryKey(),
                        );
                    }

                    $inList = $this->listRepository->getAudiobookInMyList($user, $audiobook);

                    $audiobookInfos = $this->audiobookInfoRepository->findBy([
                        'audiobook' => $audiobook->getId(),
                        'watched'   => true,
                        'user'      => $user->getId(),
                    ]);

                    $parentAudiobookUserComments = $this->audiobookUserCommentRepository->findBy([
                        'audiobook' => $audiobook->getId(),
                        'deleted'   => false,
                        'parent'    => null,
                    ]);

                    $childrenAudiobookUserComments = $this->audiobookUserCommentRepository->getAllActiveChildrenAudiobookComments($audiobook);

                    $comments = count($parentAudiobookUserComments) + count($childrenAudiobookUserComments);

                    $userRating = $this->audiobookRatingRepository->findOneBy([
                        'audiobook' => $audiobook->getId(),
                        'user'      => $user->getId(),
                    ]);

                    $successModel = new UserAudiobookDetailsSuccessModel(
                        (string) $audiobook->getId(),
                        $audiobook->getTitle(),
                        $audiobook->getAuthor(),
                        $audiobook->getVersion(),
                        $audiobook->getAlbum(),
                        $audiobook->getYear(),
                        (string) $audiobook->getDuration(),
                        $audiobook->getParts(),
                        $audiobook->getDescription(),
                        $audiobook->getAge(),
                        $audiobookCategories,
                        $inList,
                        $comments,
                        $audiobook->getAvgRating(),
                        count($this->audiobookRatingRepository->findBy([
                            'audiobook' => $audiobook->getId(),
                        ])),
                        $audiobook->getImgFile(),
                    );

                    $audiobookInfosAmount = count($audiobookInfos);

                    if (null !== $audiobookInfos && 1 <= $audiobookInfosAmount && $audiobook->getParts() <= $audiobookInfosAmount) {
                        $successModel->setCanRate(true);
                    }

                    if (1 <= $audiobookInfosAmount && (floor($audiobook->getParts() / 2) <= $audiobookInfosAmount || $audiobook->getParts() === $audiobookInfosAmount)) {
                        $successModel->setCanComment(true);
                    }

                    if (null !== $userRating) {
                        $successModel->setRated(true);
                    }

                    return $successModel;
                }
            );

            return ResponseTool::getResponse($successModel);
        }

        $this->endpointLogger->error('Invalid given Query');
        $this->translateService->setPreferredLanguage($request);
        throw new InvalidJsonDataException($this->translateService);
    }

    #[Route('/api/user/audiobook/info', name: 'userAudiobookInfo', methods: ['POST'])]
    #[AuthValidation(checkAuthToken: true, roles: [UserRolesNames::USER])]
    #[OA\Post(
        description: 'Endpoint is returning last information about last played part and time of given audiobook',
        requestBody: new OA\RequestBody(
            required: true,
            content : new OA\JsonContent(
                ref : new Model(type: UserAudiobookInfoQuery::class),
                type: 'object',
            ),
        ),
        responses  : [
            new OA\Response(
                response   : 200,
                description: 'Success',
                content    : new Model(type: UserAudiobookInfoSuccessModel::class),
            ),
        ]
    )]
    public function userAudiobookInfo(
        Request $request,
    ): Response {
        $userAudiobookInfoQuery = $this->requestService->getRequestBodyContent($request, UserAudiobookInfoQuery::class);

        if ($userAudiobookInfoQuery instanceof UserAudiobookInfoQuery) {
            $user = $this->authorizedUserService::getAuthorizedUser();

            $audiobook = $this->audiobookRepository->getAudiobookByCategoryKeyAndId(
                $userAudiobookInfoQuery->getAudiobookId(),
                $userAudiobookInfoQuery->getCategoryKey()
            );

            if (null === $audiobook) {
                $this->endpointLogger->error('Audiobook dont exist');
                $this->translateService->setPreferredLanguage($request);
                throw new DataNotFoundException([$this->translateService->getTranslation('AudiobookDontExists')]);
            }

            if (!$audiobook->getActive()) {
                $this->endpointLogger->error('Audiobook is not active');
                $this->translateService->setPreferredLanguage($request);
                throw new DataNotFoundException([$this->translateService->getTranslation('AudiobookNotActive')]);
            }

            $audiobookInfo = $this->audiobookInfoRepository->findOneBy([
                'audiobook' => $audiobook->getId(),
                'active'    => true,
                'user'      => $user->getId(),
            ]);

            if (null === $audiobookInfo) {
                return ResponseTool::getResponse();
            }

            $successModel = new UserAudiobookInfoSuccessModel(
                $audiobookInfo->getPart(),
                $audiobookInfo->getEndedTime(),
                $audiobookInfo->getWatchingDate(),
            );

            return ResponseTool::getResponse($successModel);
        }

        $this->endpointLogger->error('Invalid given Query');
        $this->translateService->setPreferredLanguage($request);
        throw new InvalidJsonDataException($this->translateService);
    }

    #[Route('/api/user/audiobook/like', name: 'userAudiobookLike', methods: ['PATCH'])]
    #[AuthValidation(checkAuthToken: true, roles: [UserRolesNames::USER])]
    #[OA\Patch(
        description: 'Endpoint is adding/deleting audiobook from my list',
        requestBody: new OA\RequestBody(
            required: true,
            content : new OA\JsonContent(
                ref : new Model(type: UserAudiobookLikeQuery::class),
                type: 'object',
            ),
        ),
        responses  : [
            new OA\Response(
                response   : 200,
                description: 'Success',
            ),
        ]
    )]
    public function userAudiobookLike(
        Request $request,
    ): Response {
        $userAudiobookLikeQuery = $this->requestService->getRequestBodyContent($request, UserAudiobookLikeQuery::class);

        if ($userAudiobookLikeQuery instanceof UserAudiobookLikeQuery) {
            $user = $this->authorizedUserService::getAuthorizedUser();

            $audiobook = $this->audiobookRepository->getAudiobookByCategoryKeyAndId(
                $userAudiobookLikeQuery->getAudiobookId(),
                $userAudiobookLikeQuery->getCategoryKey()
            );

            if (null === $audiobook) {
                $this->endpointLogger->error('Audiobook dont exist');
                $this->translateService->setPreferredLanguage($request);
                throw new DataNotFoundException([$this->translateService->getTranslation('AudiobookDontExists')]);
            }

            if (!$audiobook->getActive()) {
                $this->endpointLogger->error('Audiobook is not active');
                $this->translateService->setPreferredLanguage($request);
                throw new DataNotFoundException([$this->translateService->getTranslation('AudiobookNotActive')]);
            }

            $myList = $user->getMyList();

            if ($this->myListRepository->getAudiobookInMyList($user, $audiobook)) {
                $myList->removeAudiobook($audiobook);
            } else {
                $myList->addAudiobook($audiobook);
            }

            $this->myListRepository->add($myList);

            $this->stockCache->invalidateTags([UserStockCacheTags::USER_AUDIOBOOK_DETAIL->value . $audiobook->getId() . $user->getId()]);

            return ResponseTool::getResponse();
        }

        $this->endpointLogger->error('Invalid given Query');
        $this->translateService->setPreferredLanguage($request);
        throw new InvalidJsonDataException($this->translateService);
    }

    #[Route('/api/user/myList/audiobooks', name: 'userMyListAudiobooks', methods: ['GET'])]
    #[AuthValidation(checkAuthToken: true, roles: [UserRolesNames::USER])]
    #[OA\Get(
        description: 'Endpoint is returning list of audiobooks from my list',
        requestBody: new OA\RequestBody(),
        responses  : [
            new OA\Response(
                response   : 200,
                description: 'Success',
                content    : new Model(type: UserMyListAudiobooksSuccessModel::class),
            ),
        ]
    )]
    public function userMyListAudiobooks(): Response
    {
        $user = $this->authorizedUserService::getAuthorizedUser();
        $audiobooks = $user->getMyList()->getAudiobooks();
        $successModel = new UserMyListAudiobooksSuccessModel();
        foreach ($audiobooks as $audiobook) {
            if ($audiobook->getActive()) {
                $audiobookModel = new UserAudiobookDetailModel(
                    (string) $audiobook->getId(),
                    $audiobook->getTitle(),
                    $audiobook->getAuthor(),
                    $audiobook->getParts(),
                    $audiobook->getAge(),
                    $audiobook->getImgFile(),
                );

                foreach ($audiobook->getCategories() as $category) {
                    $audiobookModel->addCategory(new UserAudiobookCategoryModel(
                        $category->getName(),
                        $category->getCategoryKey(),
                    ));
                }

                $successModel->addAudiobook($audiobookModel);
            }
        }

        return ResponseTool::getResponse($successModel);
    }

    #[Route('/api/user/audiobook/info/add', name: 'userAudiobookInfoAdd', methods: ['PUT'])]
    #[AuthValidation(checkAuthToken: true, roles: [UserRolesNames::USER])]
    #[OA\Put(
        description: 'Endpoint is adding new info about given audiobook',
        requestBody: new OA\RequestBody(
            required: true,
            content : new OA\JsonContent(
                ref : new Model(type: UserAudiobookInfoAddQuery::class),
                type: 'object',
            ),
        ),
        responses  : [
            new OA\Response(
                response   : 201,
                description: 'Success',
            ),
        ]
    )]
    public function userAudiobookInfoAdd(
        Request $request,
    ): Response {
        $userAudiobookInfoAddQuery = $this->requestService->getRequestBodyContent($request, UserAudiobookInfoAddQuery::class);

        if ($userAudiobookInfoAddQuery instanceof UserAudiobookInfoAddQuery) {
            $user = $this->authorizedUserService::getAuthorizedUser();

            $audiobook = $this->audiobookRepository->getAudiobookByCategoryKeyAndId($userAudiobookInfoAddQuery->getAudiobookId(), $userAudiobookInfoAddQuery->getCategoryKey());

            if (null === $audiobook) {
                $this->endpointLogger->error('Audiobook dont exist');
                $this->translateService->setPreferredLanguage($request);
                throw new DataNotFoundException([$this->translateService->getTranslation('AudiobookDontExists')]);
            }

            if (!$audiobook->getActive()) {
                $this->endpointLogger->error('Audiobook is not active');
                $this->translateService->setPreferredLanguage($request);
                throw new DataNotFoundException([$this->translateService->getTranslation('AudiobookNotActive')]);
            }

            $this->audiobookInfoRepository->deActiveAudiobookInfos($user, $audiobook);

            $audiobookInfo = $this->audiobookInfoRepository->findOneBy([
                'audiobook' => $audiobook->getId(),
                'part'      => $userAudiobookInfoAddQuery->getPart(),
                'user'      => $user->getId(),
            ]);

            if (null !== $audiobookInfo) {
                if ($audiobookInfo->getEndedTime() < $userAudiobookInfoAddQuery->getEndedTime()) {
                    $audiobookInfo->setEndedTime($userAudiobookInfoAddQuery->getEndedTime());
                }

                $audiobookInfo->setWatchingDate(new DateTime());

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
                    $userAudiobookInfoAddQuery->getWatched(),
                );
            }

            $this->audiobookInfoRepository->add($audiobookInfo);

            return ResponseTool::getResponse(httpCode: Response::HTTP_CREATED);
        }

        $this->endpointLogger->error('Invalid given Query');
        $this->translateService->setPreferredLanguage($request);
        throw new InvalidJsonDataException($this->translateService);
    }

    #[Route('/api/user/audiobook/rating/add', name: 'userAudiobookRatingAdd', methods: ['PUT'])]
    #[AuthValidation(checkAuthToken: true, roles: [UserRolesNames::USER])]
    #[OA\Put(
        description: 'Endpoint is adding/editing user audiobook rating',
        requestBody: new OA\RequestBody(
            required: true,
            content : new OA\JsonContent(
                ref : new Model(type: UserAudiobookRatingAddQuery::class),
                type: 'object',
            ),
        ),
        responses  : [
            new OA\Response(
                response   : 201,
                description: 'Success',
            ),
        ]
    )]
    public function userAudiobookRatingAdd(
        Request $request,
    ): Response {
        $userAudiobookRatingAddQuery = $this->requestService->getRequestBodyContent($request, UserAudiobookRatingAddQuery::class);

        if ($userAudiobookRatingAddQuery instanceof UserAudiobookRatingAddQuery) {
            $user = $this->authorizedUserService::getAuthorizedUser();

            $audiobook = $this->audiobookRepository->getAudiobookByCategoryKeyAndId($userAudiobookRatingAddQuery->getAudiobookId(), $userAudiobookRatingAddQuery->getCategoryKey());

            if (null === $audiobook) {
                $this->endpointLogger->error('Audiobook dont exist');
                $this->translateService->setPreferredLanguage($request);
                throw new DataNotFoundException([$this->translateService->getTranslation('AudiobookDontExists')]);
            }

            if (!$audiobook->getActive()) {
                $this->endpointLogger->error('Audiobook is not active');
                $this->translateService->setPreferredLanguage($request);
                throw new DataNotFoundException([$this->translateService->getTranslation('AudiobookNotActive')]);
            }

            $rating = $this->ratingRepository->findOneBy([
                'audiobook' => $audiobook->getId(),
                'user'      => $user->getId(),
            ]);

            if (null !== $rating) {
                $rating->setRating($userAudiobookRatingAddQuery->getRating());
            } else {
                $audiobookInfo = $this->audiobookInfoRepository->findBy([
                    'audiobook' => $audiobook->getId(),
                    'watched'   => true,
                    'user'      => $user->getId(),
                ]);

                if (count($audiobookInfo) < $audiobook->getParts()) {
                    $this->endpointLogger->error('Audiobook dont exist');
                    $this->translateService->setPreferredLanguage($request);
                    throw new DataNotFoundException([$this->translateService->getTranslation('AudiobookNotWatched')]);
                }

                $rating = new AudiobookRating($audiobook, $userAudiobookRatingAddQuery->getRating(), $user);
            }

            $this->ratingRepository->add($rating);

            return ResponseTool::getResponse(httpCode: Response::HTTP_CREATED);
        }

        $this->endpointLogger->error('Invalid given Query');
        $this->translateService->setPreferredLanguage($request);
        throw new InvalidJsonDataException($this->translateService);
    }

    #[Route('/api/user/audiobook/rating/get', name: 'userAudiobookRatingGet', methods: ['POST'])]
    #[AuthValidation(checkAuthToken: true, roles: [UserRolesNames::USER])]
    #[OA\Post(
        description: 'Endpoint is getting audiobook overall rating',
        requestBody: new OA\RequestBody(
            required: true,
            content : new OA\JsonContent(
                ref : new Model(type: UserAudiobookRatingGetQuery::class),
                type: 'object',
            ),
        ),
        responses  : [
            new OA\Response(
                response   : 200,
                description: 'Success',
                content    : new Model(type: UserAudiobookRatingGetSuccessModel::class),
            ),
        ]
    )]
    public function userAudiobookRatingGet(
        Request $request,
    ): Response {
        $userAudiobookRatingGetQuery = $this->requestService->getRequestBodyContent($request, UserAudiobookRatingGetQuery::class);

        if ($userAudiobookRatingGetQuery instanceof UserAudiobookRatingGetQuery) {
            $audiobook = $this->audiobookRepository->getAudiobookByCategoryKeyAndId($userAudiobookRatingGetQuery->getAudiobookId(), $userAudiobookRatingGetQuery->getCategoryKey());

            if (null === $audiobook) {
                $this->endpointLogger->error('Audiobook dont exist');
                $this->translateService->setPreferredLanguage($request);
                throw new DataNotFoundException([$this->translateService->getTranslation('AudiobookDontExists')]);
            }

            if (!$audiobook->getActive()) {
                $this->endpointLogger->error('Audiobook is not active');
                $this->translateService->setPreferredLanguage($request);
                throw new DataNotFoundException([$this->translateService->getTranslation('AudiobookNotActive')]);
            }

            return ResponseTool::getResponse(new UserAudiobookRatingGetSuccessModel($audiobook->getAvgRating()));
        }

        $this->endpointLogger->error('Invalid given Query');
        $this->translateService->setPreferredLanguage($request);
        throw new InvalidJsonDataException($this->translateService);
    }

    #[Route('/api/user/categories/tree', name: 'userCategoriesTree', methods: ['GET'])]
    #[AuthValidation(checkAuthToken: true, roles: [UserRolesNames::USER])]
    #[OA\Get(
        description: 'Endpoint is returning all active categories in system as a tree',
        requestBody: new OA\RequestBody(),
        responses  : [
            new OA\Response(
                response   : 200,
                description: 'Success',
                content    : new Model(type: UserCategoriesSuccessModel::class),
            ),
        ]
    )]
    public function userCategoriesTree(): Response
    {
        $successModel = $this->stockCache->get(UserCacheKeys::USER_CATEGORY_TREE->value, function (ItemInterface $item): UserCategoriesSuccessModel {
            $item->expiresAfter(CacheValidTime::DAY->value);
            $item->tag(UserStockCacheTags::USER_CATEGORIES_TREE->value);

            $categories = $this->audiobookCategoryRepository->findBy([
                'parent' => null,
                'active' => true,
            ]);
            $treeGenerator = new BuildUserAudiobookCategoryTreeGenerator($categories, $this->audiobookCategoryRepository);

            return new UserCategoriesSuccessModel($treeGenerator->generate());
        });

        return ResponseTool::getResponse($successModel);
    }
}
