<?php

namespace App\Controller;

use App\Annotation\AuthValidation;
use App\Entity\AudiobookCategory;
use App\Exception\DataNotFoundException;
use App\Exception\InvalidJsonDataException;
use App\Model\Admin\AdminCategoriesSuccessModel;
use App\Model\Admin\AdminCategoryAudiobookModel;
use App\Model\Admin\AdminCategoryAudiobooksSuccessModel;
use App\Model\Admin\AdminCategoryModel;
use App\Model\Admin\AdminCategorySuccessModel;
use App\Model\DataNotFoundModel;
use App\Model\JsonDataInvalidModel;
use App\Model\NotAuthorizeModel;
use App\Model\PermissionNotGrantedModel;
use App\Query\AdminCategoryActiveQuery;
use App\Query\AdminCategoryAddAudiobookQuery;
use App\Query\AdminCategoryAddQuery;
use App\Query\AdminCategoryAudiobooksQuery;
use App\Query\AdminCategoryDetailQuery;
use App\Query\AdminCategoryEditQuery;
use App\Query\AdminCategoryRemoveAudiobookQuery;
use App\Query\AdminCategoryRemoveQuery;
use App\Repository\AudiobookCategoryRepository;
use App\Repository\AudiobookRepository;
use App\Repository\NotificationRepository;
use App\Service\AuthorizedUserServiceInterface;
use App\Service\RequestServiceInterface;
use App\Service\TranslateService;
use App\Tool\ResponseTool;
use App\ValueGenerator\BuildAudiobookCategoryTreeGenerator;
use App\ValueGenerator\CategoryKeyGenerator;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Attributes as OA;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * AdminAudiobookCategoryController
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
#[OA\Tag(name: "AdminAudiobookCategory")]
class AdminAudiobookCategoryController extends AbstractController
{
    /**
     * @param Request $request
     * @param RequestServiceInterface $requestService
     * @param AuthorizedUserServiceInterface $authorizedUserService
     * @param LoggerInterface $endpointLogger
     * @param AudiobookCategoryRepository $audiobookCategoryRepository
     * @param TranslateService $translateService
     * @return Response
     * @throws DataNotFoundException
     * @throws InvalidJsonDataException
     */
    #[Route("/api/admin/category/add", name: "adminCategoryAdd", methods: ["PUT"])]
    #[AuthValidation(checkAuthToken: true, roles: ["Administrator"])]
    #[OA\Put(
        description: "Endpoint is adding new category",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                ref: new Model(type: AdminCategoryAddQuery::class),
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
    public function adminCategoryAdd(
        Request                        $request,
        RequestServiceInterface        $requestService,
        AuthorizedUserServiceInterface $authorizedUserService,
        LoggerInterface                $endpointLogger,
        AudiobookCategoryRepository    $audiobookCategoryRepository,
        TranslateService               $translateService
    ): Response
    {
        $adminCategoryAddQuery = $requestService->getRequestBodyContent($request, AdminCategoryAddQuery::class);

        if ($adminCategoryAddQuery instanceof AdminCategoryAddQuery) {

            $categoryKey = new CategoryKeyGenerator();

            $newCategory = new AudiobookCategory($adminCategoryAddQuery->getName(), $categoryKey);

            $additionalData = $adminCategoryAddQuery->getAdditionalData();

            if (array_key_exists("parentId", $additionalData) && $additionalData["parentId"] != "") {

                $parentAudiobookCategory = $audiobookCategoryRepository->findOneBy([
                    "id" => $additionalData["parentId"]
                ]);

                if ($parentAudiobookCategory == null) {
                    $endpointLogger->error("AudiobookCategory dont exist");
                    $translateService->setPreferredLanguage($request);
                    throw new DataNotFoundException([$translateService->getTranslation("ParentCategoryDontExists")]);
                }

                $newCategory->setParent($parentAudiobookCategory);
            }

            $audiobookCategoryRepository->add($newCategory);

            return ResponseTool::getResponse(httpCode: 201);
        } else {
            $endpointLogger->error("Invalid given Query");
            $translateService->setPreferredLanguage($request);
            throw new InvalidJsonDataException($translateService);
        }
    }

    /**
     * @param Request $request
     * @param RequestServiceInterface $requestService
     * @param AuthorizedUserServiceInterface $authorizedUserService
     * @param LoggerInterface $endpointLogger
     * @param AudiobookCategoryRepository $audiobookCategoryRepository
     * @param TranslateService $translateService
     * @return Response
     * @throws DataNotFoundException
     * @throws InvalidJsonDataException
     */
    #[Route("/api/admin/category/edit", name: "adminCategoryEdit", methods: ["PATCH"])]
    #[AuthValidation(checkAuthToken: true, roles: ["Administrator"])]
    #[OA\Patch(
        description: "Endpoint is editing given category",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                ref: new Model(type: AdminCategoryEditQuery::class),
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
    public function adminCategoryEdit(
        Request                        $request,
        RequestServiceInterface        $requestService,
        AuthorizedUserServiceInterface $authorizedUserService,
        LoggerInterface                $endpointLogger,
        AudiobookCategoryRepository    $audiobookCategoryRepository,
        TranslateService               $translateService
    ): Response
    {
        $adminCategoryEditQuery = $requestService->getRequestBodyContent($request, AdminCategoryEditQuery::class);

        if ($adminCategoryEditQuery instanceof AdminCategoryEditQuery) {
            $category = $audiobookCategoryRepository->findOneBy([
                "id" => $adminCategoryEditQuery->getCategoryId()
            ]);

            if ($category == null) {
                $endpointLogger->error("AudiobookCategory dont exist");
                $translateService->setPreferredLanguage($request);
                throw new DataNotFoundException([$translateService->getTranslation("CategoryDontExists")]);
            }

            $category->setName($adminCategoryEditQuery->getName());

            $audiobookCategoryRepository->add($category);

            return ResponseTool::getResponse();
        } else {
            $endpointLogger->error("Invalid given Query");
            $translateService->setPreferredLanguage($request);
            throw new InvalidJsonDataException($translateService);
        }
    }

    /**
     * @param Request $request
     * @param RequestServiceInterface $requestService
     * @param AuthorizedUserServiceInterface $authorizedUserService
     * @param LoggerInterface $endpointLogger
     * @param AudiobookCategoryRepository $audiobookCategoryRepository
     * @param TranslateService $translateService
     * @param NotificationRepository $notificationRepository
     * @return Response
     * @throws DataNotFoundException
     * @throws InvalidJsonDataException
     */
    #[Route("/api/admin/category/remove", name: "adminCategoryRemove", methods: ["DELETE"])]
    #[AuthValidation(checkAuthToken: true, roles: ["Administrator"])]
    #[OA\Delete(
        description: "Endpoint is removing given category",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                ref: new Model(type: AdminCategoryRemoveQuery::class),
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
    public function adminCategoryRemove(
        Request                        $request,
        RequestServiceInterface        $requestService,
        AuthorizedUserServiceInterface $authorizedUserService,
        LoggerInterface                $endpointLogger,
        AudiobookCategoryRepository    $audiobookCategoryRepository,
        TranslateService               $translateService,
        NotificationRepository         $notificationRepository
    ): Response
    {
        $adminCategoryRemoveQuery = $requestService->getRequestBodyContent($request, AdminCategoryRemoveQuery::class);

        if ($adminCategoryRemoveQuery instanceof AdminCategoryRemoveQuery) {

            $category = $audiobookCategoryRepository->findOneBy([
                "id" => $adminCategoryRemoveQuery->getCategoryId()
            ]);

            if ($category == null) {
                $endpointLogger->error("AudiobookCategory dont exist");
                $translateService->setPreferredLanguage($request);
                throw new DataNotFoundException([$translateService->getTranslation("CategoryDontExists")]);
            }

            $notificationRepository->updateDeleteNotificationsByAction($category->getId());
            $audiobookCategoryRepository->removeCategoryAndChildren($category);
//            $audiobookCategoryRepository->remove($category);

            return ResponseTool::getResponse();
        } else {
            $endpointLogger->error("Invalid given Query");
            $translateService->setPreferredLanguage($request);
            throw new InvalidJsonDataException($translateService);
        }
    }

    /**
     * @param Request $request
     * @param RequestServiceInterface $requestService
     * @param AuthorizedUserServiceInterface $authorizedUserService
     * @param LoggerInterface $endpointLogger
     * @param AudiobookCategoryRepository $audiobookCategoryRepository
     * @param AudiobookRepository $audiobookRepository
     * @param TranslateService $translateService
     * @return Response
     * @throws DataNotFoundException
     * @throws InvalidJsonDataException
     */
    #[Route("/api/admin/category/add/audiobook", name: "adminCategoryAddAudiobook", methods: ["PATCH"])]
    #[AuthValidation(checkAuthToken: true, roles: ["Administrator"])]
    #[OA\Patch(
        description: "Endpoint is adding audiobook to given category",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                ref: new Model(type: AdminCategoryAddAudiobookQuery::class),
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
    public function adminCategoryAddAudiobook(
        Request                        $request,
        RequestServiceInterface        $requestService,
        AuthorizedUserServiceInterface $authorizedUserService,
        LoggerInterface                $endpointLogger,
        AudiobookCategoryRepository    $audiobookCategoryRepository,
        AudiobookRepository            $audiobookRepository,
        TranslateService               $translateService
    ): Response
    {
        $adminCategoryAddAudiobookQuery = $requestService->getRequestBodyContent($request, AdminCategoryAddAudiobookQuery::class);

        if ($adminCategoryAddAudiobookQuery instanceof AdminCategoryAddAudiobookQuery) {

            $category = $audiobookCategoryRepository->findOneBy([
                "id" => $adminCategoryAddAudiobookQuery->getCategoryId()
            ]);

            if ($category == null) {
                $endpointLogger->error("AudiobookCategory dont exist");
                $translateService->setPreferredLanguage($request);
                throw new DataNotFoundException([$translateService->getTranslation("CategoryDontExists")]);
            }

            $audiobook = $audiobookRepository->findOneBy([
                "id" => $adminCategoryAddAudiobookQuery->getAudiobookId()
            ]);

            if ($audiobook == null) {
                $endpointLogger->error("Audiobook dont exist");
                $translateService->setPreferredLanguage($request);
                throw new DataNotFoundException([$translateService->getTranslation("AudiobookDontExists")]);
            }

            $audiobook->addCategory($category);

            $audiobookRepository->add($audiobook);

            return ResponseTool::getResponse();
        } else {
            $endpointLogger->error("Invalid given Query");
            $translateService->setPreferredLanguage($request);
            throw new InvalidJsonDataException($translateService);
        }
    }

    /**
     * @param Request $request
     * @param RequestServiceInterface $requestService
     * @param AuthorizedUserServiceInterface $authorizedUserService
     * @param LoggerInterface $endpointLogger
     * @param AudiobookCategoryRepository $audiobookCategoryRepository
     * @param AudiobookRepository $audiobookRepository
     * @param TranslateService $translateService
     * @return Response
     * @throws DataNotFoundException
     * @throws InvalidJsonDataException
     */
    #[Route("/api/admin/category/remove/audiobook", name: "adminCategoryRemoveAudiobook", methods: ["DELETE"])]
    #[AuthValidation(checkAuthToken: true, roles: ["Administrator"])]
    #[OA\Delete(
        description: "Endpoint is removing audiobook from given category",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                ref: new Model(type: AdminCategoryRemoveAudiobookQuery::class),
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
    public function adminCategoryRemoveAudiobook(
        Request                        $request,
        RequestServiceInterface        $requestService,
        AuthorizedUserServiceInterface $authorizedUserService,
        LoggerInterface                $endpointLogger,
        AudiobookCategoryRepository    $audiobookCategoryRepository,
        AudiobookRepository            $audiobookRepository,
        TranslateService               $translateService
    ): Response
    {
        $adminCategoryRemoveAudiobookQuery = $requestService->getRequestBodyContent($request, AdminCategoryRemoveAudiobookQuery::class);

        if ($adminCategoryRemoveAudiobookQuery instanceof AdminCategoryRemoveAudiobookQuery) {

            $category = $audiobookCategoryRepository->findOneBy([
                "id" => $adminCategoryRemoveAudiobookQuery->getCategoryId()
            ]);

            if ($category == null) {
                $endpointLogger->error("AudiobookCategory dont exist");
                $translateService->setPreferredLanguage($request);
                throw new DataNotFoundException([$translateService->getTranslation("CategoryDontExists")]);
            }

            $audiobook = $audiobookRepository->findOneBy([
                "id" => $adminCategoryRemoveAudiobookQuery->getAudiobookId()
            ]);

            if ($audiobook == null) {
                $endpointLogger->error("Audiobook dont exist");
                $translateService->setPreferredLanguage($request);
                throw new DataNotFoundException([$translateService->getTranslation("AudiobookDontExists")]);
            }

            $audiobook->removeCategory($category);

            $audiobookRepository->add($audiobook);

            return ResponseTool::getResponse();
        } else {
            $endpointLogger->error("Invalid given Query");
            $translateService->setPreferredLanguage($request);
            throw new InvalidJsonDataException($translateService);
        }
    }

    /**
     * @param Request $request
     * @param RequestServiceInterface $requestService
     * @param AuthorizedUserServiceInterface $authorizedUserService
     * @param LoggerInterface $endpointLogger
     * @param AudiobookCategoryRepository $audiobookCategoryRepository
     * @param TranslateService $translateService
     * @return Response
     * @throws DataNotFoundException
     * @throws InvalidJsonDataException
     */
    #[Route("/api/admin/category/audiobooks", name: "adminCategoryAudiobooks", methods: ["POST"])]
    #[AuthValidation(checkAuthToken: true, roles: ["Administrator"])]
    #[OA\Post(
        description: "Endpoint is returning all audiobooks in given category",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                ref: new Model(type: AdminCategoryAudiobooksQuery::class),
                type: "object"
            ),
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Success",
                content: new Model(type: AdminCategoryAudiobooksSuccessModel::class),
            )
        ]
    )]
    public function adminCategoryAudiobooks(
        Request                        $request,
        RequestServiceInterface        $requestService,
        AuthorizedUserServiceInterface $authorizedUserService,
        LoggerInterface                $endpointLogger,
        AudiobookCategoryRepository    $audiobookCategoryRepository,
        TranslateService               $translateService
    ): Response
    {
        $adminCategoryAudiobooksQuery = $requestService->getRequestBodyContent($request, AdminCategoryAudiobooksQuery::class);

        if ($adminCategoryAudiobooksQuery instanceof AdminCategoryAudiobooksQuery) {

            $category = $audiobookCategoryRepository->findOneBy([
                "categoryKey" => $adminCategoryAudiobooksQuery->getCategoryKey()
            ]);

            if ($category == null) {
                $endpointLogger->error("AudiobookCategory dont exist");
                $translateService->setPreferredLanguage($request);
                throw new DataNotFoundException([$translateService->getTranslation("CategoryDontExists")]);
            }

            $successModel = new AdminCategoryAudiobooksSuccessModel();

            $audiobooks = $category->getAudiobooks();

            $minResult = $adminCategoryAudiobooksQuery->getPage() * $adminCategoryAudiobooksQuery->getLimit();
            $maxResult = $adminCategoryAudiobooksQuery->getLimit() + $minResult;

            foreach ($audiobooks as $index => $audiobook) {
                if ($index < $minResult) {
                    continue;
                } elseif ($index < $maxResult) {
                    $audiobookModel = new AdminCategoryAudiobookModel(
                        $audiobook->getId(),
                        $audiobook->getTitle(),
                        $audiobook->getAuthor(),
                        $audiobook->getYear(),
                        $audiobook->getDuration(),
                        $audiobook->getSize(),
                        $audiobook->getParts(),
                        $audiobook->getAvgRating(),
                        $audiobook->getAge(),
                        $audiobook->getActive()
                    );

                    $successModel->addAudiobook($audiobookModel);
                } else {
                    break;
                }
            }
            $successModel->setPage($adminCategoryAudiobooksQuery->getPage());
            $successModel->setLimit($adminCategoryAudiobooksQuery->getLimit());

            $successModel->setMaxPage(ceil(count($audiobooks) / $adminCategoryAudiobooksQuery->getLimit()));

            return ResponseTool::getResponse($successModel);
        } else {
            $endpointLogger->error("Invalid given Query");
            $translateService->setPreferredLanguage($request);
            throw new InvalidJsonDataException($translateService);
        }
    }

    /**
     * @param Request $request
     * @param RequestServiceInterface $requestService
     * @param AuthorizedUserServiceInterface $authorizedUserService
     * @param LoggerInterface $endpointLogger
     * @param AudiobookCategoryRepository $audiobookCategoryRepository
     * @param AudiobookRepository $audiobookRepository
     * @return Response
     */
    #[Route("/api/admin/categories/tree", name: "adminCategoriesTree", methods: ["GET"])]
    #[AuthValidation(checkAuthToken: true, roles: ["Administrator"])]
    #[OA\Get(
        description: "Endpoint is returning all categories in system as a tree",
        requestBody: new OA\RequestBody(),
        responses: [
            new OA\Response(
                response: 200,
                description: "Success",
                content: new Model(type: AdminCategoriesSuccessModel::class)
            )
        ]
    )]
    public function adminCategoriesTree(
        Request                        $request,
        RequestServiceInterface        $requestService,
        AuthorizedUserServiceInterface $authorizedUserService,
        LoggerInterface                $endpointLogger,
        AudiobookCategoryRepository    $audiobookCategoryRepository,
        AudiobookRepository            $audiobookRepository
    ): Response
    {
        $categories = $audiobookCategoryRepository->findBy([
            "parent" => null
        ]);

        $treeGenerator = new BuildAudiobookCategoryTreeGenerator($categories, $audiobookCategoryRepository, $audiobookRepository);

        $successModel = new AdminCategoriesSuccessModel($treeGenerator->generate());

        return ResponseTool::getResponse($successModel);

    }

    /**
     * @param Request $request
     * @param RequestServiceInterface $requestService
     * @param AuthorizedUserServiceInterface $authorizedUserService
     * @param LoggerInterface $endpointLogger
     * @param AudiobookCategoryRepository $audiobookCategoryRepository
     * @return Response
     */
    #[Route("/api/admin/categories", name: "adminCategories", methods: ["GET"])]
    #[AuthValidation(checkAuthToken: true, roles: ["Administrator"])]
    #[OA\Get(
        description: "Endpoint is returning all categories in system",
        requestBody: new OA\RequestBody(),
        responses: [
            new OA\Response(
                response: 200,
                description: "Success",
                content: new Model(type: AdminCategoriesSuccessModel::class)
            )
        ]
    )]
    public function adminCategories(
        Request                        $request,
        RequestServiceInterface        $requestService,
        AuthorizedUserServiceInterface $authorizedUserService,
        LoggerInterface                $endpointLogger,
        AudiobookCategoryRepository    $audiobookCategoryRepository,
    ): Response
    {
        $categories = $audiobookCategoryRepository->findBy([], orderBy: ["dateAdd" => "ASC"]);

        $successModel = new AdminCategoriesSuccessModel();

        foreach ($categories as $category) {
            $newModel = new AdminCategoryModel($category->getId(), $category->getName(), $category->getActive(), $category->getCategoryKey());
            $successModel->addCategory($newModel);
        }

        return ResponseTool::getResponse($successModel);
    }

    /**
     * @param Request $request
     * @param RequestServiceInterface $requestService
     * @param AuthorizedUserServiceInterface $authorizedUserService
     * @param LoggerInterface $endpointLogger
     * @param AudiobookCategoryRepository $audiobookCategoryRepository
     * @param TranslateService $translateService
     * @return Response
     * @throws DataNotFoundException
     * @throws InvalidJsonDataException
     */
    #[Route("/api/admin/category/active", name: "adminCategoryActive", methods: ["PATCH"])]
    #[AuthValidation(checkAuthToken: true, roles: ["Administrator"])]
    #[OA\Patch(
        description: "Endpoint is activating given category",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                ref: new Model(type: AdminCategoryActiveQuery::class),
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
    public function adminCategoryActive(
        Request                        $request,
        RequestServiceInterface        $requestService,
        AuthorizedUserServiceInterface $authorizedUserService,
        LoggerInterface                $endpointLogger,
        AudiobookCategoryRepository    $audiobookCategoryRepository,
        TranslateService               $translateService
    ): Response
    {
        $adminCategoryActiveQuery = $requestService->getRequestBodyContent($request, AdminCategoryActiveQuery::class);

        if ($adminCategoryActiveQuery instanceof AdminCategoryActiveQuery) {

            $category = $audiobookCategoryRepository->findOneBy([
                "id" => $adminCategoryActiveQuery->getCategoryId()
            ]);

            if ($category == null) {
                $endpointLogger->error("AudiobookCategory dont exist");
                $translateService->setPreferredLanguage($request);
                throw new DataNotFoundException([$translateService->getTranslation("CategoryDontExists")]);
            }

            $category->setActive($adminCategoryActiveQuery->isActive());

            $audiobookCategoryRepository->add($category);

            return ResponseTool::getResponse();
        } else {
            $endpointLogger->error("Invalid given Query");
            $translateService->setPreferredLanguage($request);
            throw new InvalidJsonDataException($translateService);
        }
    }

    /**
     * @param Request $request
     * @param RequestServiceInterface $requestService
     * @param AuthorizedUserServiceInterface $authorizedUserService
     * @param LoggerInterface $endpointLogger
     * @param AudiobookCategoryRepository $audiobookCategoryRepository
     * @param TranslateService $translateService
     * @return Response
     * @throws DataNotFoundException
     * @throws InvalidJsonDataException
     */
    #[Route("/api/admin/category/detail", name: "adminCategoryDetail", methods: ["POST"])]
    #[AuthValidation(checkAuthToken: true, roles: ["Administrator"])]
    #[OA\Post(
        description: "Endpoint is returning category details",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                ref: new Model(type: AdminCategoryDetailQuery::class),
                type: "object"
            ),
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Success",
                content: new Model(type: AdminCategorySuccessModel::class)
            )
        ]
    )]
    public function adminCategoryDetail(
        Request                        $request,
        RequestServiceInterface        $requestService,
        AuthorizedUserServiceInterface $authorizedUserService,
        LoggerInterface                $endpointLogger,
        AudiobookCategoryRepository    $audiobookCategoryRepository,
        TranslateService               $translateService
    ): Response
    {
        $adminCategoryDetailQuery = $requestService->getRequestBodyContent($request, AdminCategoryDetailQuery::class);

        if ($adminCategoryDetailQuery instanceof AdminCategoryDetailQuery) {

            $category = $audiobookCategoryRepository->findOneBy([
                "categoryKey" => $adminCategoryDetailQuery->getCategoryKey()
            ]);

            if ($category == null) {
                $endpointLogger->error("AudiobookCategory dont exist");
                $translateService->setPreferredLanguage($request);
                throw new DataNotFoundException([$translateService->getTranslation("CategoryDontExists")]);
            }

            $successModel = new AdminCategorySuccessModel($category->getId(), $category->getName(), $category->getActive(), $category->getParent()?->getName(), $category->getParent()?->getId());

            return ResponseTool::getResponse($successModel);

        } else {
            $endpointLogger->error("Invalid given Query");
            $translateService->setPreferredLanguage($request);
            throw new InvalidJsonDataException($translateService);
        }
    }
}