<?php

declare(strict_types = 1);

namespace App\Controller\Admin;

use App\Annotation\AuthValidation;
use App\Entity\AudiobookCategory;
use App\Enums\Cache\AdminCacheKeys;
use App\Enums\Cache\AdminStockCacheTags;
use App\Enums\Cache\CacheValidTime;
use App\Enums\UserRolesNames;
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
use App\Service\TranslateServiceInterface;
use App\Tool\ResponseTool;
use App\ValueGenerator\BuildAdminAudiobookCategoryTreeGenerator;
use App\ValueGenerator\CategoryKeyGenerator;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

use function array_key_exists;
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
#[OA\Tag(name: 'AdminAudiobookCategory')]
class AdminAudiobookCategoryController extends AbstractController
{
    public function __construct(private readonly RequestServiceInterface $requestService, private readonly LoggerInterface $endpointLogger, private readonly AudiobookCategoryRepository $audiobookCategoryRepository, private readonly TranslateServiceInterface $translateService, private readonly TagAwareCacheInterface $stockCache, private readonly NotificationRepository $notificationRepository, private readonly AudiobookRepository $audiobookRepository) {}

    #[Route('/api/admin/category/add', name: 'adminCategoryAdd', methods: ['PUT'])]
    #[AuthValidation(checkAuthToken: true, roles: [UserRolesNames::ADMINISTRATOR, UserRolesNames::RECRUITER])]
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
    ): Response {
        $adminCategoryAddQuery = $this->requestService->getRequestBodyContent($request, AdminCategoryAddQuery::class);

        if ($adminCategoryAddQuery instanceof AdminCategoryAddQuery) {
            $categoryKey = new CategoryKeyGenerator();

            $newCategory = new AudiobookCategory($adminCategoryAddQuery->getName(), $categoryKey);

            $additionalData = $adminCategoryAddQuery->getAdditionalData();

            if (array_key_exists('parentId', $additionalData) && '' !== $additionalData['parentId']) {
                $parentAudiobookCategory = $this->audiobookCategoryRepository->find($additionalData['parentId']);

                if (null === $parentAudiobookCategory) {
                    $this->endpointLogger->error('AudiobookCategory dont exist');
                    $this->translateService->setPreferredLanguage($request);
                    throw new DataNotFoundException([$this->translateService->getTranslation('ParentCategoryDontExists')]);
                }

                $newCategory->setParent($parentAudiobookCategory);
            }

            $this->audiobookCategoryRepository->add($newCategory);

            $this->stockCache->invalidateTags([AdminStockCacheTags::ADMIN_CATEGORY->value]);

            return ResponseTool::getResponse(httpCode: Response::HTTP_CREATED);
        }

        $this->endpointLogger->error('Invalid given Query');
        $this->translateService->setPreferredLanguage($request);
        throw new InvalidJsonDataException($this->translateService);
    }

    #[Route('/api/admin/category/edit', name: 'adminCategoryEdit', methods: ['PATCH'])]
    #[AuthValidation(checkAuthToken: true, roles: [UserRolesNames::ADMINISTRATOR, UserRolesNames::RECRUITER])]
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
    ): Response {
        $adminCategoryEditQuery = $this->requestService->getRequestBodyContent($request, AdminCategoryEditQuery::class);

        if ($adminCategoryEditQuery instanceof AdminCategoryEditQuery) {
            $category = $this->audiobookCategoryRepository->find($adminCategoryEditQuery->getCategoryId());

            if (null === $category) {
                $this->endpointLogger->error('AudiobookCategory dont exist');
                $this->translateService->setPreferredLanguage($request);
                throw new DataNotFoundException([$this->translateService->getTranslation('CategoryDontExists')]);
            }

            $category->setName($adminCategoryEditQuery->getName());

            $this->audiobookCategoryRepository->add($category);

            $this->stockCache->invalidateTags([AdminStockCacheTags::ADMIN_CATEGORY->value]);

            return ResponseTool::getResponse();
        }

        $this->endpointLogger->error('Invalid given Query');
        $this->translateService->setPreferredLanguage($request);
        throw new InvalidJsonDataException($this->translateService);
    }

    #[Route('/api/admin/category/remove', name: 'adminCategoryRemove', methods: ['DELETE'])]
    #[AuthValidation(checkAuthToken: true, roles: [UserRolesNames::ADMINISTRATOR, UserRolesNames::RECRUITER])]
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
    ): Response {
        $adminCategoryRemoveQuery = $this->requestService->getRequestBodyContent($request, AdminCategoryRemoveQuery::class);

        if ($adminCategoryRemoveQuery instanceof AdminCategoryRemoveQuery) {
            $category = $this->audiobookCategoryRepository->find($adminCategoryRemoveQuery->getCategoryId());

            if (null === $category) {
                $this->endpointLogger->error('AudiobookCategory dont exist');
                $this->translateService->setPreferredLanguage($request);
                throw new DataNotFoundException([$this->translateService->getTranslation('CategoryDontExists')]);
            }

            $this->notificationRepository->updateDeleteNotificationsByAction($category->getId());
            $this->audiobookCategoryRepository->removeCategoryAndChildren($category);

            $this->stockCache->invalidateTags([AdminStockCacheTags::ADMIN_CATEGORY->value]);

            return ResponseTool::getResponse();
        }

        $this->endpointLogger->error('Invalid given Query');
        $this->translateService->setPreferredLanguage($request);
        throw new InvalidJsonDataException($this->translateService);
    }

    #[Route('/api/admin/category/add/audiobook', name: 'adminCategoryAddAudiobook', methods: ['PUT'])]
    #[AuthValidation(checkAuthToken: true, roles: [UserRolesNames::ADMINISTRATOR, UserRolesNames::RECRUITER])]
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
    ): Response {
        $adminCategoryAddAudiobookQuery = $this->requestService->getRequestBodyContent($request, AdminCategoryAddAudiobookQuery::class);

        if ($adminCategoryAddAudiobookQuery instanceof AdminCategoryAddAudiobookQuery) {
            $category = $this->audiobookCategoryRepository->find($adminCategoryAddAudiobookQuery->getCategoryId());

            if (null === $category) {
                $this->endpointLogger->error('AudiobookCategory dont exist');
                $this->translateService->setPreferredLanguage($request);
                throw new DataNotFoundException([$this->translateService->getTranslation('CategoryDontExists')]);
            }

            $audiobook = $this->audiobookRepository->find($adminCategoryAddAudiobookQuery->getAudiobookId());

            if (null === $audiobook) {
                $this->endpointLogger->error('Audiobook dont exist');
                $this->translateService->setPreferredLanguage($request);
                throw new DataNotFoundException([$this->translateService->getTranslation('AudiobookDontExists')]);
            }

            $audiobook->addCategory($category);

            $this->audiobookRepository->add($audiobook);

            $this->stockCache->invalidateTags([AdminStockCacheTags::ADMIN_CATEGORY_AUDIOBOOKS->value,
                AdminStockCacheTags::ADMIN_CATEGORY->value,
                AdminStockCacheTags::ADMIN_AUDIOBOOK->value]);

            return ResponseTool::getResponse(httpCode: Response::HTTP_CREATED);
        }

        $this->endpointLogger->error('Invalid given Query');
        $this->translateService->setPreferredLanguage($request);
        throw new InvalidJsonDataException($this->translateService);
    }

    #[Route('/api/admin/category/remove/audiobook', name: 'adminCategoryRemoveAudiobook', methods: ['DELETE'])]
    #[AuthValidation(checkAuthToken: true, roles: [UserRolesNames::ADMINISTRATOR, UserRolesNames::RECRUITER])]
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
    ): Response {
        $adminCategoryRemoveAudiobookQuery = $this->requestService->getRequestBodyContent($request, AdminCategoryRemoveAudiobookQuery::class);

        if ($adminCategoryRemoveAudiobookQuery instanceof AdminCategoryRemoveAudiobookQuery) {
            $category = $this->audiobookCategoryRepository->find($adminCategoryRemoveAudiobookQuery->getCategoryId());

            if (null === $category) {
                $this->endpointLogger->error('AudiobookCategory dont exist');
                $this->translateService->setPreferredLanguage($request);
                throw new DataNotFoundException([$this->translateService->getTranslation('CategoryDontExists')]);
            }

            $audiobook = $this->audiobookRepository->find($adminCategoryRemoveAudiobookQuery->getAudiobookId());

            if (null === $audiobook) {
                $this->endpointLogger->error('Audiobook dont exist');
                $this->translateService->setPreferredLanguage($request);
                throw new DataNotFoundException([$this->translateService->getTranslation('AudiobookDontExists')]);
            }

            $category->removeAudiobook($audiobook);

            $this->audiobookCategoryRepository->add($category);
            $this->audiobookRepository->add($audiobook);

            $this->stockCache->invalidateTags([AdminStockCacheTags::ADMIN_CATEGORY_AUDIOBOOKS->value,
                AdminStockCacheTags::ADMIN_CATEGORY->value,
                AdminStockCacheTags::ADMIN_AUDIOBOOK->value]);

            return ResponseTool::getResponse();
        }

        $this->endpointLogger->error('Invalid given Query');
        $this->translateService->setPreferredLanguage($request);
        throw new InvalidJsonDataException($this->translateService);
    }

    #[Route('/api/admin/category/audiobooks', name: 'adminCategoryAudiobooks', methods: ['POST'])]
    #[AuthValidation(checkAuthToken: true, roles: [UserRolesNames::ADMINISTRATOR, UserRolesNames::RECRUITER])]
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
    ): Response {
        $adminCategoryAudiobooksQuery = $this->requestService->getRequestBodyContent($request, AdminCategoryAudiobooksQuery::class);

        if ($adminCategoryAudiobooksQuery instanceof AdminCategoryAudiobooksQuery) {
            $category = $this->audiobookCategoryRepository->findOneBy([
                'categoryKey' => $adminCategoryAudiobooksQuery->getCategoryKey(),
            ]);

            if (null === $category) {
                $this->endpointLogger->error('AudiobookCategory dont exist');
                $this->translateService->setPreferredLanguage($request);
                throw new DataNotFoundException([$this->translateService->getTranslation('CategoryDontExists')]);
            }

            $successModel = $this->stockCache->get(AdminCacheKeys::ADMIN_CATEGORY_AUDIOBOOKS->value . $adminCategoryAudiobooksQuery->getPage() . $adminCategoryAudiobooksQuery->getCategoryKey(), function (ItemInterface $item) use ($category, $adminCategoryAudiobooksQuery): AdminCategoryAudiobooksSuccessModel {
                $item->expiresAfter(CacheValidTime::HALF_A_DAY->value);
                $item->tag(AdminStockCacheTags::ADMIN_CATEGORY_AUDIOBOOKS->value);

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
                            (string) $audiobook->getId(),
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

                $successModel->setMaxPage((int) ceil(count($audiobooks) / $adminCategoryAudiobooksQuery->getLimit()));

                return $successModel;
            });

            return ResponseTool::getResponse($successModel);
        }

        $this->endpointLogger->error('Invalid given Query');
        $this->translateService->setPreferredLanguage($request);
        throw new InvalidJsonDataException($this->translateService);
    }

    #[Route('/api/admin/categories/tree', name: 'adminCategoriesTree', methods: ['GET'])]
    #[AuthValidation(checkAuthToken: true, roles: [UserRolesNames::ADMINISTRATOR, UserRolesNames::RECRUITER])]
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
    public function adminCategoriesTree(): Response
    {
        $successModel = $this->stockCache->get(AdminCacheKeys::ADMIN_CATEGORY_TREE->value, function (ItemInterface $item): AdminCategoriesSuccessModel {
            $item->expiresAfter(CacheValidTime::DAY->value);
            $item->tag(AdminStockCacheTags::ADMIN_CATEGORY->value);

            $categories = $this->audiobookCategoryRepository->findBy([
                'parent' => null,
            ]);
            $treeGenerator = new BuildAdminAudiobookCategoryTreeGenerator($categories, $this->audiobookCategoryRepository, $this->audiobookRepository);

            return new AdminCategoriesSuccessModel($treeGenerator->generate());
        });

        return ResponseTool::getResponse($successModel);
    }

    #[Route('/api/admin/categories', name: 'adminCategories', methods: ['GET'])]
    #[AuthValidation(checkAuthToken: true, roles: [UserRolesNames::ADMINISTRATOR, UserRolesNames::RECRUITER])]
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
    public function adminCategories(): Response
    {
        $successModel = $this->stockCache->get(AdminCacheKeys::ADMIN_CATEGORIES->value, function (ItemInterface $item): AdminCategoriesSuccessModel {
            $item->expiresAfter(CacheValidTime::DAY->value);
            $item->tag(AdminStockCacheTags::ADMIN_CATEGORY->value);

            $categories = $this->audiobookCategoryRepository->findBy([], orderBy: ['dateAdd' => 'ASC']);
            $successModel = new AdminCategoriesSuccessModel();
            foreach ($categories as $category) {
                $newModel = new AdminCategoryModel((string) $category->getId(), $category->getName(), $category->getActive(), $category->getCategoryKey());
                $successModel->addCategory($newModel);
            }

            return $successModel;
        });

        return ResponseTool::getResponse($successModel);
    }

    #[Route('/api/admin/category/active', name: 'adminCategoryActive', methods: ['PATCH'])]
    #[AuthValidation(checkAuthToken: true, roles: [UserRolesNames::ADMINISTRATOR, UserRolesNames::RECRUITER])]
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
    ): Response {
        $adminCategoryActiveQuery = $this->requestService->getRequestBodyContent($request, AdminCategoryActiveQuery::class);

        if ($adminCategoryActiveQuery instanceof AdminCategoryActiveQuery) {
            $category = $this->audiobookCategoryRepository->find($adminCategoryActiveQuery->getCategoryId());

            if (null === $category) {
                $this->endpointLogger->error('AudiobookCategory dont exist');
                $this->translateService->setPreferredLanguage($request);
                throw new DataNotFoundException([$this->translateService->getTranslation('CategoryDontExists')]);
            }

            $category->setActive($adminCategoryActiveQuery->isActive());

            $this->audiobookCategoryRepository->add($category);

            $this->stockCache->invalidateTags([AdminStockCacheTags::ADMIN_CATEGORY->value]);

            return ResponseTool::getResponse();
        }

        $this->endpointLogger->error('Invalid given Query');
        $this->translateService->setPreferredLanguage($request);
        throw new InvalidJsonDataException($this->translateService);
    }

    #[Route('/api/admin/category/detail', name: 'adminCategoryDetail', methods: ['POST'])]
    #[AuthValidation(checkAuthToken: true, roles: [UserRolesNames::ADMINISTRATOR, UserRolesNames::RECRUITER])]
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
    ): Response {
        $adminCategoryDetailQuery = $this->requestService->getRequestBodyContent($request, AdminCategoryDetailQuery::class);

        if ($adminCategoryDetailQuery instanceof AdminCategoryDetailQuery) {
            $category = $this->audiobookCategoryRepository->findOneBy([
                'categoryKey' => $adminCategoryDetailQuery->getCategoryKey(),
            ]);

            if (null === $category) {
                $this->endpointLogger->error('AudiobookCategory dont exist');
                $this->translateService->setPreferredLanguage($request);
                throw new DataNotFoundException([$this->translateService->getTranslation('CategoryDontExists')]);
            }

            $successModel = $this->stockCache->get(AdminCacheKeys::ADMIN_CATEGORY->value . $category->getId(), function (ItemInterface $item) use ($category): AdminCategorySuccessModel {
                $item->expiresAfter(CacheValidTime::DAY->value);
                $item->tag(AdminStockCacheTags::ADMIN_CATEGORY->value);

                return new AdminCategorySuccessModel(
                    (string) $category->getId(),
                    $category->getName(),
                    $category->getActive(),
                    $category->getParent()?->getName(),
                    (string) $category->getParent()?->getId()
                );
            });

            return ResponseTool::getResponse($successModel);
        }

        $this->endpointLogger->error('Invalid given Query');
        $this->translateService->setPreferredLanguage($request);
        throw new InvalidJsonDataException($this->translateService);
    }
}
