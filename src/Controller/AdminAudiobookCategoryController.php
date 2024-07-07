<?php

declare(strict_types=1);

namespace App\Controller;

use App\Annotation\AuthValidation;
use App\Entity\AudiobookCategory;
use App\Enums\CacheKeys;
use App\Enums\CacheValidTime;
use App\Enums\StockCacheTags;
use App\Exception\DataNotFoundException;
use App\Exception\InvalidJsonDataException;
use App\Model\Admin\AdminCategoriesSuccessModel;
use App\Model\Admin\AdminCategoryAudiobookModel;
use App\Model\Admin\AdminCategoryAudiobooksSuccessModel;
use App\Model\Admin\AdminCategoryModel;
use App\Model\Admin\AdminCategorySuccessModel;
use App\Model\Error\DataNotFoundModel;
use App\Model\Error\JsonDataInvalidModel;
use App\Model\Error\NotAuthorizeModel;
use App\Model\Error\PermissionNotGrantedModel;
use App\Query\Admin\AdminCategoryActiveQuery;
use App\Query\Admin\AdminCategoryAddAudiobookQuery;
use App\Query\Admin\AdminCategoryAddQuery;
use App\Query\Admin\AdminCategoryAudiobooksQuery;
use App\Query\Admin\AdminCategoryDetailQuery;
use App\Query\Admin\AdminCategoryEditQuery;
use App\Query\Admin\AdminCategoryRemoveAudiobookQuery;
use App\Query\Admin\AdminCategoryRemoveQuery;
use App\Repository\AudiobookCategoryRepository;
use App\Repository\AudiobookRepository;
use App\Repository\NotificationRepository;
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
#[OA\Tag(name: 'AdminAudiobookCategory')]
class AdminAudiobookCategoryController extends AbstractController
{
    #[Route('/api/admin/category/add', name: 'adminCategoryAdd', methods: ['PUT'])]
    #[AuthValidation(checkAuthToken: true, roles: ['Administrator'])]
    #[OA\Put(
        description: 'Endpoint is adding new category',
        requestBody: new OA\RequestBody(
            required: true,
            content : new OA\JsonContent(
                ref : new Model(type: AdminCategoryAddQuery::class),
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
    public function adminCategoryAdd(
        Request $request,
        RequestServiceInterface $requestService,
        LoggerInterface $endpointLogger,
        AudiobookCategoryRepository $audiobookCategoryRepository,
        TranslateService $translateService,
        TagAwareCacheInterface $stockCache,
    ): Response {
        $adminCategoryAddQuery = $requestService->getRequestBodyContent($request, AdminCategoryAddQuery::class);

        if ($adminCategoryAddQuery instanceof AdminCategoryAddQuery) {
            $categoryKey = new CategoryKeyGenerator();

            $newCategory = new AudiobookCategory($adminCategoryAddQuery->getName(), $categoryKey);

            $additionalData = $adminCategoryAddQuery->getAdditionalData();

            if (array_key_exists('parentId', $additionalData) && $additionalData['parentId'] !== "") {
                $parentAudiobookCategory = $audiobookCategoryRepository->find($additionalData['parentId']);

                if ($parentAudiobookCategory === null) {
                    $endpointLogger->error('AudiobookCategory dont exist');
                    $translateService->setPreferredLanguage($request);
                    throw new DataNotFoundException([$translateService->getTranslation('ParentCategoryDontExists')]);
                }

                $newCategory->setParent($parentAudiobookCategory);
            }

            $audiobookCategoryRepository->add($newCategory);

            $stockCache->invalidateTags([StockCacheTags::ADMIN_CATEGORY->value]);

            return ResponseTool::getResponse(httpCode: 201);
        }

        $endpointLogger->error('Invalid given Query');
        $translateService->setPreferredLanguage($request);
        throw new InvalidJsonDataException($translateService);
    }

    #[Route('/api/admin/category/edit', name: 'adminCategoryEdit', methods: ['PATCH'])]
    #[AuthValidation(checkAuthToken: true, roles: ['Administrator'])]
    #[OA\Patch(
        description: 'Endpoint is editing given category',
        requestBody: new OA\RequestBody(
            required: true,
            content : new OA\JsonContent(
                ref : new Model(type: AdminCategoryEditQuery::class),
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
    public function adminCategoryEdit(
        Request $request,
        RequestServiceInterface $requestService,
        LoggerInterface $endpointLogger,
        AudiobookCategoryRepository $audiobookCategoryRepository,
        TranslateService $translateService,
        TagAwareCacheInterface $stockCache,
    ): Response {
        $adminCategoryEditQuery = $requestService->getRequestBodyContent($request, AdminCategoryEditQuery::class);

        if ($adminCategoryEditQuery instanceof AdminCategoryEditQuery) {
            $category = $audiobookCategoryRepository->find($adminCategoryEditQuery->getCategoryId());

            if ($category === null) {
                $endpointLogger->error('AudiobookCategory dont exist');
                $translateService->setPreferredLanguage($request);
                throw new DataNotFoundException([$translateService->getTranslation('CategoryDontExists')]);
            }

            $category->setName($adminCategoryEditQuery->getName());

            $audiobookCategoryRepository->add($category);

            $stockCache->invalidateTags([StockCacheTags::ADMIN_CATEGORY->value]);

            return ResponseTool::getResponse();
        }

        $endpointLogger->error('Invalid given Query');
        $translateService->setPreferredLanguage($request);
        throw new InvalidJsonDataException($translateService);
    }

    #[Route('/api/admin/category/remove', name: 'adminCategoryRemove', methods: ['DELETE'])]
    #[AuthValidation(checkAuthToken: true, roles: ['Administrator'])]
    #[OA\Delete(
        description: 'Endpoint is removing given category',
        requestBody: new OA\RequestBody(
            required: true,
            content : new OA\JsonContent(
                ref : new Model(type: AdminCategoryRemoveQuery::class),
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
    public function adminCategoryRemove(
        Request $request,
        RequestServiceInterface $requestService,
        LoggerInterface $endpointLogger,
        AudiobookCategoryRepository $audiobookCategoryRepository,
        TranslateService $translateService,
        NotificationRepository $notificationRepository,
        TagAwareCacheInterface $stockCache,
    ): Response {
        $adminCategoryRemoveQuery = $requestService->getRequestBodyContent($request, AdminCategoryRemoveQuery::class);

        if ($adminCategoryRemoveQuery instanceof AdminCategoryRemoveQuery) {
            $category = $audiobookCategoryRepository->find($adminCategoryRemoveQuery->getCategoryId());

            if ($category === null) {
                $endpointLogger->error('AudiobookCategory dont exist');
                $translateService->setPreferredLanguage($request);
                throw new DataNotFoundException([$translateService->getTranslation('CategoryDontExists')]);
            }

            $notificationRepository->updateDeleteNotificationsByAction($category->getId());
            $audiobookCategoryRepository->removeCategoryAndChildren($category);

            $stockCache->invalidateTags([StockCacheTags::ADMIN_CATEGORY->value]);

            return ResponseTool::getResponse();
        }

        $endpointLogger->error('Invalid given Query');
        $translateService->setPreferredLanguage($request);
        throw new InvalidJsonDataException($translateService);
    }

    #[Route('/api/admin/category/add/audiobook', name: 'adminCategoryAddAudiobook', methods: ['PUT'])]
    #[AuthValidation(checkAuthToken: true, roles: ['Administrator'])]
    #[OA\Put(
        description: 'Endpoint is adding audiobook to given category',
        requestBody: new OA\RequestBody(
            required: true,
            content : new OA\JsonContent(
                ref : new Model(type: AdminCategoryAddAudiobookQuery::class),
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
    public function adminCategoryAddAudiobook(
        Request $request,
        RequestServiceInterface $requestService,
        LoggerInterface $endpointLogger,
        AudiobookCategoryRepository $audiobookCategoryRepository,
        AudiobookRepository $audiobookRepository,
        TranslateService $translateService,
        TagAwareCacheInterface $stockCache,
    ): Response {
        $adminCategoryAddAudiobookQuery = $requestService->getRequestBodyContent($request, AdminCategoryAddAudiobookQuery::class);

        if ($adminCategoryAddAudiobookQuery instanceof AdminCategoryAddAudiobookQuery) {
            $category = $audiobookCategoryRepository->find($adminCategoryAddAudiobookQuery->getCategoryId());

            if ($category === null) {
                $endpointLogger->error('AudiobookCategory dont exist');
                $translateService->setPreferredLanguage($request);
                throw new DataNotFoundException([$translateService->getTranslation('CategoryDontExists')]);
            }

            $audiobook = $audiobookRepository->find($adminCategoryAddAudiobookQuery->getAudiobookId());

            if ($audiobook === null) {
                $endpointLogger->error('Audiobook dont exist');
                $translateService->setPreferredLanguage($request);
                throw new DataNotFoundException([$translateService->getTranslation('AudiobookDontExists')]);
            }

            $audiobook->addCategory($category);

            $audiobookRepository->add($audiobook);

            $stockCache->invalidateTags([StockCacheTags::ADMIN_CATEGORY_AUDIOBOOKS->value,
                StockCacheTags::ADMIN_CATEGORY->value,
                StockCacheTags::ADMIN_AUDIOBOOK->value]);

            return ResponseTool::getResponse(httpCode: 201);
        }

        $endpointLogger->error('Invalid given Query');
        $translateService->setPreferredLanguage($request);
        throw new InvalidJsonDataException($translateService);
    }

    #[Route('/api/admin/category/remove/audiobook', name: 'adminCategoryRemoveAudiobook', methods: ['DELETE'])]
    #[AuthValidation(checkAuthToken: true, roles: ['Administrator'])]
    #[OA\Delete(
        description: 'Endpoint is removing audiobook from given category',
        requestBody: new OA\RequestBody(
            required: true,
            content : new OA\JsonContent(
                ref : new Model(type: AdminCategoryRemoveAudiobookQuery::class),
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
    public function adminCategoryRemoveAudiobook(
        Request $request,
        RequestServiceInterface $requestService,
        LoggerInterface $endpointLogger,
        AudiobookCategoryRepository $audiobookCategoryRepository,
        AudiobookRepository $audiobookRepository,
        TranslateService $translateService,
        TagAwareCacheInterface $stockCache,
    ): Response {
        $adminCategoryRemoveAudiobookQuery = $requestService->getRequestBodyContent($request, AdminCategoryRemoveAudiobookQuery::class);

        if ($adminCategoryRemoveAudiobookQuery instanceof AdminCategoryRemoveAudiobookQuery) {
            $category = $audiobookCategoryRepository->find($adminCategoryRemoveAudiobookQuery->getCategoryId());

            if ($category === null) {
                $endpointLogger->error('AudiobookCategory dont exist');
                $translateService->setPreferredLanguage($request);
                throw new DataNotFoundException([$translateService->getTranslation('CategoryDontExists')]);
            }

            $audiobook = $audiobookRepository->find($adminCategoryRemoveAudiobookQuery->getAudiobookId());

            if ($audiobook === null) {
                $endpointLogger->error('Audiobook dont exist');
                $translateService->setPreferredLanguage($request);
                throw new DataNotFoundException([$translateService->getTranslation('AudiobookDontExists')]);
            }

            $category->removeAudiobook($audiobook);

            $audiobookCategoryRepository->add($category);
            $audiobookRepository->add($audiobook);

            $stockCache->invalidateTags([StockCacheTags::ADMIN_CATEGORY_AUDIOBOOKS->value,
                StockCacheTags::ADMIN_CATEGORY->value,
                StockCacheTags::ADMIN_AUDIOBOOK->value]);

            return ResponseTool::getResponse();
        }

        $endpointLogger->error('Invalid given Query');
        $translateService->setPreferredLanguage($request);
        throw new InvalidJsonDataException($translateService);
    }

    #[Route('/api/admin/category/audiobooks', name: 'adminCategoryAudiobooks', methods: ['POST'])]
    #[AuthValidation(checkAuthToken: true, roles: ['Administrator'])]
    #[OA\Post(
        description: 'Endpoint is returning all audiobooks in given category',
        requestBody: new OA\RequestBody(
            required: true,
            content : new OA\JsonContent(
                ref : new Model(type: AdminCategoryAudiobooksQuery::class),
                type: 'object',
            ),
        ),
        responses  : [
            new OA\Response(
                response   : 200,
                description: 'Success',
                content    : new Model(type: AdminCategoryAudiobooksSuccessModel::class),
            ),
        ]
    )]
    public function adminCategoryAudiobooks(
        Request $request,
        RequestServiceInterface $requestService,
        LoggerInterface $endpointLogger,
        AudiobookCategoryRepository $audiobookCategoryRepository,
        TranslateService $translateService,
        TagAwareCacheInterface $stockCache,
    ): Response {
        $adminCategoryAudiobooksQuery = $requestService->getRequestBodyContent($request, AdminCategoryAudiobooksQuery::class);

        if ($adminCategoryAudiobooksQuery instanceof AdminCategoryAudiobooksQuery) {
            $category = $audiobookCategoryRepository->findOneBy([
                'categoryKey' => $adminCategoryAudiobooksQuery->getCategoryKey(),
            ]);

            if ($category === null) {
                $endpointLogger->error('AudiobookCategory dont exist');
                $translateService->setPreferredLanguage($request);
                throw new DataNotFoundException([$translateService->getTranslation('CategoryDontExists')]);
            }

            $successModel = $stockCache->get(CacheKeys::ADMIN_CATEGORY_AUDIOBOOKS->value . $adminCategoryAudiobooksQuery->getPage() . $adminCategoryAudiobooksQuery->getCategoryKey(), function (ItemInterface $item) use ($category, $adminCategoryAudiobooksQuery) {
                $item->expiresAfter(CacheValidTime::HALF_A_DAY->value);
                $item->tag(StockCacheTags::ADMIN_CATEGORY_AUDIOBOOKS->value);

                $successModel = new AdminCategoryAudiobooksSuccessModel();

                $audiobooks = $category->getAudiobooks();

                $minResult = $adminCategoryAudiobooksQuery->getPage() * $adminCategoryAudiobooksQuery->getLimit();
                $maxResult = $adminCategoryAudiobooksQuery->getLimit() + $minResult;

                foreach ($audiobooks as $index => $audiobook) {
                    if ($index < $minResult) {
                        continue;
                    }

                    if ($index < $maxResult) {
                        $audiobookModel = new AdminCategoryAudiobookModel(
                            (string)$audiobook->getId(),
                            $audiobook->getTitle(),
                            $audiobook->getAuthor(),
                            $audiobook->getYear(),
                            $audiobook->getDuration(),
                            $audiobook->getSize(),
                            $audiobook->getParts(),
                            $audiobook->getAvgRating(),
                            $audiobook->getAge(),
                            $audiobook->getActive(),
                        );

                        $successModel->addAudiobook($audiobookModel);
                    } else {
                        break;
                    }
                }

                $successModel->setPage($adminCategoryAudiobooksQuery->getPage());
                $successModel->setLimit($adminCategoryAudiobooksQuery->getLimit());

                $successModel->setMaxPage((int)ceil(count($audiobooks) / $adminCategoryAudiobooksQuery->getLimit()));

                return $successModel;
            });

            return ResponseTool::getResponse($successModel);
        }

        $endpointLogger->error('Invalid given Query');
        $translateService->setPreferredLanguage($request);
        throw new InvalidJsonDataException($translateService);
    }

    #[Route('/api/admin/categories/tree', name: 'adminCategoriesTree', methods: ['GET'])]
    #[AuthValidation(checkAuthToken: true, roles: ['Administrator'])]
    #[OA\Get(
        description: 'Endpoint is returning all categories in system as a tree',
        requestBody: new OA\RequestBody(),
        responses  : [
            new OA\Response(
                response   : 200,
                description: 'Success',
                content    : new Model(type: AdminCategoriesSuccessModel::class),
            ),
        ]
    )]
    public function adminCategoriesTree(
        AudiobookCategoryRepository $audiobookCategoryRepository,
        AudiobookRepository $audiobookRepository,
        TagAwareCacheInterface $stockCache,
    ): Response {
        $successModel = $stockCache->get(CacheKeys::ADMIN_CATEGORY_TREE->value, function (ItemInterface $item) use ($audiobookCategoryRepository, $audiobookRepository) {
            $item->expiresAfter(CacheValidTime::DAY->value);
            $item->tag(StockCacheTags::ADMIN_CATEGORY->value);

            $categories = $audiobookCategoryRepository->findBy([
                'parent' => null,
            ]);

            $treeGenerator = new BuildAudiobookCategoryTreeGenerator($categories, $audiobookCategoryRepository, $audiobookRepository);

            return new AdminCategoriesSuccessModel($treeGenerator->generate());
        });

        return ResponseTool::getResponse($successModel);
    }

    #[Route('/api/admin/categories', name: 'adminCategories', methods: ['GET'])]
    #[AuthValidation(checkAuthToken: true, roles: ['Administrator'])]
    #[OA\Get(
        description: 'Endpoint is returning all categories in system',
        requestBody: new OA\RequestBody(),
        responses  : [
            new OA\Response(
                response   : 200,
                description: 'Success',
                content    : new Model(type: AdminCategoriesSuccessModel::class),
            ),
        ]
    )]
    public function adminCategories(
        AudiobookCategoryRepository $audiobookCategoryRepository,
        TagAwareCacheInterface $stockCache,
    ): Response {
        $successModel = $stockCache->get(CacheKeys::ADMIN_CATEGORIES->value, function (ItemInterface $item) use ($audiobookCategoryRepository) {
            $item->expiresAfter(CacheValidTime::DAY->value);
            $item->tag(StockCacheTags::ADMIN_CATEGORY->value);

            $categories = $audiobookCategoryRepository->findBy([], orderBy: ['dateAdd' => 'ASC']);

            $successModel = new AdminCategoriesSuccessModel();

            foreach ($categories as $category) {
                $newModel = new AdminCategoryModel((string)$category->getId(), $category->getName(), $category->getActive(), $category->getCategoryKey());
                $successModel->addCategory($newModel);
            }

            return $successModel;
        });

        return ResponseTool::getResponse($successModel);
    }

    #[Route('/api/admin/category/active', name: 'adminCategoryActive', methods: ['PATCH'])]
    #[AuthValidation(checkAuthToken: true, roles: ['Administrator'])]
    #[OA\Patch(
        description: 'Endpoint is activating given category',
        requestBody: new OA\RequestBody(
            required: true,
            content : new OA\JsonContent(
                ref : new Model(type: AdminCategoryActiveQuery::class),
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
    public function adminCategoryActive(
        Request $request,
        RequestServiceInterface $requestService,
        LoggerInterface $endpointLogger,
        AudiobookCategoryRepository $audiobookCategoryRepository,
        TranslateService $translateService,
        TagAwareCacheInterface $stockCache,
    ): Response {
        $adminCategoryActiveQuery = $requestService->getRequestBodyContent($request, AdminCategoryActiveQuery::class);

        if ($adminCategoryActiveQuery instanceof AdminCategoryActiveQuery) {
            $category = $audiobookCategoryRepository->find($adminCategoryActiveQuery->getCategoryId());

            if ($category === null) {
                $endpointLogger->error('AudiobookCategory dont exist');
                $translateService->setPreferredLanguage($request);
                throw new DataNotFoundException([$translateService->getTranslation('CategoryDontExists')]);
            }

            $category->setActive($adminCategoryActiveQuery->isActive());

            $audiobookCategoryRepository->add($category);

            $stockCache->invalidateTags([StockCacheTags::ADMIN_CATEGORY->value]);

            return ResponseTool::getResponse();
        }

        $endpointLogger->error('Invalid given Query');
        $translateService->setPreferredLanguage($request);
        throw new InvalidJsonDataException($translateService);
    }

    #[Route('/api/admin/category/detail', name: 'adminCategoryDetail', methods: ['POST'])]
    #[AuthValidation(checkAuthToken: true, roles: ['Administrator'])]
    #[OA\Post(
        description: 'Endpoint is returning category details',
        requestBody: new OA\RequestBody(
            required: true,
            content : new OA\JsonContent(
                ref : new Model(type: AdminCategoryDetailQuery::class),
                type: 'object',
            ),
        ),
        responses  : [
            new OA\Response(
                response   : 200,
                description: 'Success',
                content    : new Model(type: AdminCategorySuccessModel::class),
            ),
        ]
    )]
    public function adminCategoryDetail(
        Request $request,
        RequestServiceInterface $requestService,
        LoggerInterface $endpointLogger,
        AudiobookCategoryRepository $audiobookCategoryRepository,
        TranslateService $translateService,
        TagAwareCacheInterface $stockCache,
    ): Response {
        $adminCategoryDetailQuery = $requestService->getRequestBodyContent($request, AdminCategoryDetailQuery::class);

        if ($adminCategoryDetailQuery instanceof AdminCategoryDetailQuery) {
            $category = $audiobookCategoryRepository->findOneBy([
                'categoryKey' => $adminCategoryDetailQuery->getCategoryKey(),
            ]);

            if ($category === null) {
                $endpointLogger->error('AudiobookCategory dont exist');
                $translateService->setPreferredLanguage($request);
                throw new DataNotFoundException([$translateService->getTranslation('CategoryDontExists')]);
            }
            $successModel = $stockCache->get(CacheKeys::ADMIN_CATEGORY->value . $category->getId(), function (ItemInterface $item) use ($category) {
                $item->expiresAfter(CacheValidTime::DAY->value);
                $item->tag(StockCacheTags::ADMIN_CATEGORY->value);

                return new AdminCategorySuccessModel((string)$category->getId(), $category->getName(), $category->getActive(), $category->getParent()?->getName(), (string)$category->getParent()?->getId());
            });

            return ResponseTool::getResponse($successModel);
        }

        $endpointLogger->error('Invalid given Query');
        $translateService->setPreferredLanguage($request);
        throw new InvalidJsonDataException($translateService);
    }
}
