<?php

namespace App\Controller;

use App\Annotation\AuthValidation;
use App\Entity\AudiobookInfo;
use App\Exception\DataNotFoundException;
use App\Exception\InvalidJsonDataException;
use App\Model\AdminAudiobookCategoryModel;
use App\Model\DataNotFoundModel;
use App\Model\JsonDataInvalidModel;
use App\Model\NotAuthorizeModel;
use App\Model\PermissionNotGrantedModel;
use App\Model\UserAudiobookDetailsSuccessModel;
use App\Model\UserAudiobookInfoSuccessModel;
use App\Model\UserAudiobookModel;
use App\Model\UserAudiobooksSuccessModel;
use App\Model\UserCategoryModel;
use App\Model\UserMyListAudiobooksSuccessModel;
use App\Model\UserProposedAudiobooksSuccessModel;
use App\Query\UserAudiobookDetailsQuery;
use App\Query\UserAudiobookInfoAddQuery;
use App\Query\UserAudiobookInfoQuery;
use App\Query\UserAudiobookLikeQuery;
use App\Query\UserAudiobooksQuery;
use App\Repository\AudiobookCategoryRepository;
use App\Repository\AudiobookInfoRepository;
use App\Repository\AudiobookRepository;
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
    //1 - Pobranie całej listy kategorii(paginacja)(z aktywnymi audiobookami)(aktywnych kategorii) od tego który ma najwięcej audiobooków
    //2 - Pobranie listy proponowanych audiobooków
    //3 - Pobranie detali audiobooka(wraz z tym czy jest w mojej liscie)(jeśli jest aktywny)
    //4 - Pobranie danych o odtwarzaniu audiobooka
    //5 - Dodanie/Usunięcie z mojej listy
    //6 - Pobranie audiobooków z mojej listy
    //7 - Dodanie danych do AudiobookInformation
    //8 - Pobranie danych z AudiobookInfo po id Audiobooka
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

                    $categoryModel = new UserCategoryModel();

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
     * @throws DataNotFoundException
     * @throws InvalidJsonDataException
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

        $audiobooks = $user->getProposedAudiobooks();

        $successModel = new UserProposedAudiobooksSuccessModel();

        foreach ($audiobooks as $audiobook) {
            $successModel->addAudiobook(new UserAudiobookModel(
                $audiobook->getId(),
                $audiobook->getTitle(),
                $audiobook->getAuthor(),
                $audiobook->getParts(),
                $audiobook->getAge()
            ));
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
                "user"=>$user->getId()
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
        MyListRepository $myListRepository
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

            if($myListRepository->getAudiobookINMyList($user,$audiobook)){
                $myList->removeAudiobook($audiobook);
            }
            else{
                $myList->addAudiobook($audiobook);
            }

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
            $successModel->addAudiobook(new UserAudiobookModel(
                $audiobook->getId(),
                $audiobook->getTitle(),
                $audiobook->getAuthor(),
                $audiobook->getParts(),
                $audiobook->getAge()
            ));
        }

        return ResponseTool::getResponse($successModel);
    }

    /**
     * @param Request $request
     * @param RequestServiceInterface $requestService
     * @param AuthorizedUserServiceInterface $authorizedUserService
     * @param LoggerInterface $endpointLogger
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
        AudiobookRepository $audiobookRepository,
        AudiobookInfoRepository $audiobookInfoRepository
    ): Response
    {
        $userAudiobookInfoAddQuery = $requestService->getRequestBodyContent($request, UserAudiobookInfoAddQuery::class);

        if ($userAudiobookInfoAddQuery instanceof UserAudiobookInfoAddQuery) {

            $user = $authorizedUserService->getAuthorizedUser();

            $audiobook = $audiobookRepository->getAudiobookByCategoryKeyAndId($userAudiobookInfoAddQuery->getAudiobookId(), $userAudiobookInfoAddQuery->getCategoryKey());

            if ($audiobook == null) {
                $endpointLogger->error("Audiobook dont exist");
                throw new DataNotFoundException(["userAudiobook.info.audiobook.not.exist"]);
            }

            $audiobookInfoRepository->deActiveAudiobookInfos($user,$audiobook);

            $newAudiobookInfo = new AudiobookInfo($user,
                $audiobook,
                $userAudiobookInfoAddQuery->getPart(),
                $userAudiobookInfoAddQuery->getEndedTime(),
                $userAudiobookInfoAddQuery->getWatchingDate()
            );

            $audiobookInfoRepository->add($newAudiobookInfo);

        return ResponseTool::getResponse(httpCode: 201);
        } else {
            $endpointLogger->error("Invalid given Query");
            throw new InvalidJsonDataException("userAudiobook.info.add.invalid.query");
        }
    }

}