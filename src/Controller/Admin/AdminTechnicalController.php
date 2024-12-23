<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Annotation\AuthValidation;
use App\Entity\TechnicalBreak;
use App\Enums\Cache\AdminStockCacheTags;
use App\Enums\Cache\UserStockCacheTags;
use App\Enums\UserRolesNames;
use App\Exception\DataNotFoundException;
use App\Exception\InvalidJsonDataException;
use App\Model\Admin\AdminTechnicalBreakModel;
use App\Model\Admin\AdminTechnicalBreakSuccessModel;
use App\Model\Admin\AdminTechnicalCachePoolsModel;
use App\Model\Admin\CacheModel;
use App\Model\Error\DataNotFoundModel;
use App\Model\Error\JsonDataInvalidModel;
use App\Model\Error\NotAuthorizeModel;
use App\Model\Error\PermissionNotGrantedModel;
use App\Model\Serialization\AdminTechnicalBreaksSearchModel;
use App\Query\Admin\AdminTechnicalBreakListQuery;
use App\Query\Admin\AdminTechnicalBreakPatchQuery;
use App\Query\Admin\AdminTechnicalCacheClearQuery;
use App\Repository\TechnicalBreakRepository;
use App\Service\AuthorizedUserServiceInterface;
use App\Service\RequestServiceInterface;
use App\Service\TranslateServiceInterface;
use App\Tool\ResponseTool;
use DateTime;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Attributes as OA;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use Symfony\Component\Serializer\SerializerInterface;
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
#[OA\Tag(name: 'AdminTechnical')]
#[Route('/api/admin')]
class AdminTechnicalController extends AbstractController
{
    #[Route('/technical/break', name: 'adminTechnicalBreakPut', methods: ['PUT'])]
    #[AuthValidation(checkAuthToken: true, roles: [UserRolesNames::ADMINISTRATOR, UserRolesNames::RECRUITER])]
    #[OA\Put(
        description: 'Endpoint is used to add Technical Break for admin',
        requestBody: new OA\RequestBody(),
        responses  : [
            new OA\Response(
                response   : 201,
                description: 'Success',
            ),
        ]
    )]
    public function adminTechnicalBreakPut(
        AuthorizedUserServiceInterface $authorizedUserService,
        TechnicalBreakRepository $technicalBreakRepository,
        TagAwareCacheInterface $stockCache,
    ): Response {
        $user = $authorizedUserService::getAuthorizedUser();

        $activeTechnicalBreak = $technicalBreakRepository->findOneBy([
            'active' => true,
        ]);

        if ($activeTechnicalBreak === null) {
            $technicalBreakRepository->add(new TechnicalBreak(true, $user));
            $stockCache->invalidateTags([AdminStockCacheTags::ADMIN_TECHNICAL_BREAK->value]);
        }

        return ResponseTool::getResponse(httpCode: Response::HTTP_CREATED);
    }

    #[Route('/technical/break', name: 'adminTechnicalBreakPatch', methods: ['PATCH'])]
    #[AuthValidation(checkAuthToken: true, roles: [UserRolesNames::ADMINISTRATOR, UserRolesNames::RECRUITER])]
    #[OA\Patch(
        description: 'Endpoint is used to edit Technical Break by admin',
        requestBody: new OA\RequestBody(
            required: true,
            content : new OA\JsonContent(
                ref : new Model(type: AdminTechnicalBreakPatchQuery::class),
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
    public function adminTechnicalBreakPatch(
        Request $request,
        RequestServiceInterface $requestService,
        AuthorizedUserServiceInterface $authorizedUserService,
        TechnicalBreakRepository $technicalBreakRepository,
        LoggerInterface $endpointLogger,
        TranslateServiceInterface $translateService,
        TagAwareCacheInterface $stockCache,
    ): Response {
        $adminTechnicalBreakPatchQuery = $requestService->getRequestBodyContent($request, AdminTechnicalBreakPatchQuery::class);

        if ($adminTechnicalBreakPatchQuery instanceof AdminTechnicalBreakPatchQuery) {
            $technicalBreak = $technicalBreakRepository->find($adminTechnicalBreakPatchQuery->getTechnicalBreakId());

            if ($technicalBreak === null) {
                $endpointLogger->error('TechnicalBreak dont exist');
                $translateService->setPreferredLanguage($request);
                throw new DataNotFoundException([$translateService->getTranslation('TechnicalBreakDontExists')]);
            }

            $technicalBreak
                ->setUser($authorizedUserService::getAuthorizedUser())
                ->setDateTo(new DateTime())
                ->setActive(false);

            $technicalBreakRepository->add($technicalBreak);

            $stockCache->invalidateTags([AdminStockCacheTags::ADMIN_TECHNICAL_BREAK->value]);

            return ResponseTool::getResponse();
        }

        $endpointLogger->error('Invalid given Query');
        $translateService->setPreferredLanguage($request);
        throw new InvalidJsonDataException($translateService);
    }

    #[Route('/technical/break/list', name: 'adminTechnicalBreakList', methods: ['POST'])]
    #[AuthValidation(checkAuthToken: true, roles: [UserRolesNames::ADMINISTRATOR, UserRolesNames::RECRUITER])]
    #[OA\Post(
        description: 'Endpoint is used to get list of Technical Breaks for admin',
        requestBody: new OA\RequestBody(
            required: true,
            content : new OA\JsonContent(
                ref : new Model(type: AdminTechnicalBreakListQuery::class),
                type: 'object',
            ),
        ),
        responses  : [
            new OA\Response(
                response   : 200,
                description: 'Success',
                content    : new Model(type: AdminTechnicalBreakSuccessModel::class),
            ),
        ]
    )]
    public function adminTechnicalBreakList(
        Request $request,
        RequestServiceInterface $requestService,
        LoggerInterface $endpointLogger,
        TechnicalBreakRepository $technicalBreakRepository,
        TranslateServiceInterface $translateService,
        SerializerInterface $serializer,
    ): Response {
        $adminTechnicalBreakListQuery = $requestService->getRequestBodyContent($request, AdminTechnicalBreakListQuery::class);

        if ($adminTechnicalBreakListQuery instanceof AdminTechnicalBreakListQuery) {
            $technicalBreakListData = $adminTechnicalBreakListQuery->getSearchData();

            $reportSearchModel = new AdminTechnicalBreaksSearchModel();
            $serializer->deserialize(
                json_encode($technicalBreakListData),
                AdminTechnicalBreaksSearchModel::class,
                'json',
                [
                    AbstractNormalizer::OBJECT_TO_POPULATE             => $reportSearchModel,
                    AbstractObjectNormalizer::DISABLE_TYPE_ENFORCEMENT => true,
                ],
            );

            $successModel = new AdminTechnicalBreakSuccessModel();

            $technicalBreaks = $technicalBreakRepository->getTechnicalBreakByPage($reportSearchModel);

            $minResult = $adminTechnicalBreakListQuery->getPage() * $adminTechnicalBreakListQuery->getLimit();
            $maxResult = $adminTechnicalBreakListQuery->getLimit() + $minResult;

            foreach ($technicalBreaks as $index => $technicalBreak) {
                if ($index < $minResult) {
                    continue;
                }

                if ($index < $maxResult) {
                    $technicalBreakModel = new AdminTechnicalBreakModel(
                        (string)$technicalBreak->getId(),
                        $technicalBreak->getActive(),
                        $technicalBreak->getDateFrom(),
                        $technicalBreak->getUser()->getUserInformation()->getFirstname() . ' ' . $technicalBreak->getUser()->getUserInformation()->getLastname(),
                    );

                    if ($technicalBreak->getDateTo() !== null) {
                        $technicalBreakModel->setDateTo($technicalBreak->getDateTo());
                    }

                    $successModel->addTechnicalBreak($technicalBreakModel);
                } else {
                    break;
                }
            }

            $successModel->setPage($adminTechnicalBreakListQuery->getPage());
            $successModel->setLimit($adminTechnicalBreakListQuery->getLimit());
            $successModel->setMaxPage((int)ceil(count($technicalBreaks) / $adminTechnicalBreakListQuery->getLimit()));

            return ResponseTool::getResponse($successModel);
        }

        $endpointLogger->error('Invalid given Query');
        $translateService->setPreferredLanguage($request);
        throw new InvalidJsonDataException($translateService);
    }

    #[Route('/technical/cache/clear', name: 'adminTechnicalCacheClear', methods: ['PATCH'])]
    #[AuthValidation(checkAuthToken: true, roles: [UserRolesNames::ADMINISTRATOR, UserRolesNames::RECRUITER])]
    #[OA\Patch(
        description: 'Endpoint is used to clear cache pools by admin',
        requestBody: new OA\RequestBody(
            required: true,
            content : new OA\JsonContent(
                ref : new Model(type: AdminTechnicalCacheClearQuery::class),
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
    public function adminTechnicalCacheClear(
        Request $request,
        RequestServiceInterface $requestService,
        TagAwareCacheInterface $stockCache,
        LoggerInterface $endpointLogger,
        TranslateServiceInterface $translateService,
        KernelInterface $kernel,
    ): Response {
        $adminTechnicalCacheClearQuery = $requestService->getRequestBodyContent($request, AdminTechnicalCacheClearQuery::class);

        if ($adminTechnicalCacheClearQuery instanceof AdminTechnicalCacheClearQuery) {
            $cacheData = $adminTechnicalCacheClearQuery->getCacheData();

            if (array_key_exists('all', $cacheData) && $cacheData['all']) {
                $application = new Application($kernel);
                $application->setAutoExit(false);

                $output = new BufferedOutput();
                $application->run(new ArrayInput([
                    'command' => 'cache:pool:clear',
                    '--all' => true,
                ]), $output);
            }

            if (array_key_exists('admin', $cacheData) && $cacheData['admin']) {
                $stockCache->invalidateTags([AdminStockCacheTags::ADMIN_CATEGORY->value,
                    AdminStockCacheTags::ADMIN_CATEGORY_AUDIOBOOKS->value,
                    AdminStockCacheTags::ADMIN_AUDIOBOOK->value,
                    AdminStockCacheTags::ADMIN_STATISTICS->value,
                    AdminStockCacheTags::ADMIN_ROLES->value,
                    AdminStockCacheTags::ADMIN_TECHNICAL_BREAK->value]);
            } else {
                if (array_key_exists('user', $cacheData) && $cacheData['user']) {
                    $stockCache->invalidateTags([UserStockCacheTags::USER_AUDIOBOOK_PART->value,
                        UserStockCacheTags::USER_NOTIFICATIONS->value,
                        UserStockCacheTags::USER_AUDIOBOOKS->value,
                        UserStockCacheTags::USER_AUDIOBOOK_DETAIL->value,
                        UserStockCacheTags::USER_AUDIOBOOK_RATING->value,
                        UserStockCacheTags::USER_PROPOSED_AUDIOBOOKS->value,
                        UserStockCacheTags::AUDIOBOOK_COMMENTS->value,
                        UserStockCacheTags::USER_CATEGORIES_TREE->value]);
                }
                if (array_key_exists('pools', $cacheData) && !empty($cacheData['pools'])) {
                    foreach ($cacheData['pools'] as $pool) {
                        match ($pool) {
                            AdminStockCacheTags::ADMIN_CATEGORY->value => $stockCache->invalidateTags([AdminStockCacheTags::ADMIN_CATEGORY->value]),
                            AdminStockCacheTags::ADMIN_CATEGORY_AUDIOBOOKS->value => $stockCache->invalidateTags([AdminStockCacheTags::ADMIN_CATEGORY_AUDIOBOOKS->value]),
                            AdminStockCacheTags::ADMIN_AUDIOBOOK->value => $stockCache->invalidateTags([AdminStockCacheTags::ADMIN_AUDIOBOOK->value]),
                            AdminStockCacheTags::ADMIN_STATISTICS->value => $stockCache->invalidateTags([AdminStockCacheTags::ADMIN_STATISTICS->value]),
                            AdminStockCacheTags::ADMIN_ROLES->value => $stockCache->invalidateTags([AdminStockCacheTags::ADMIN_ROLES->value]),
                            AdminStockCacheTags::ADMIN_TECHNICAL_BREAK->value => $stockCache->invalidateTags([AdminStockCacheTags::ADMIN_TECHNICAL_BREAK->value]),
                            UserStockCacheTags::USER_AUDIOBOOK_PART->value => $stockCache->invalidateTags([UserStockCacheTags::USER_AUDIOBOOK_PART->value]),
                            UserStockCacheTags::USER_NOTIFICATIONS->value => $stockCache->invalidateTags([UserStockCacheTags::USER_NOTIFICATIONS->value]),
                            UserStockCacheTags::USER_AUDIOBOOKS->value => $stockCache->invalidateTags([UserStockCacheTags::USER_AUDIOBOOKS->value]),
                            UserStockCacheTags::USER_AUDIOBOOK_DETAIL->value => $stockCache->invalidateTags([UserStockCacheTags::USER_AUDIOBOOK_DETAIL->value]),
                            UserStockCacheTags::USER_AUDIOBOOK_RATING->value => $stockCache->invalidateTags([UserStockCacheTags::USER_AUDIOBOOK_RATING->value]),
                            UserStockCacheTags::USER_PROPOSED_AUDIOBOOKS->value => $stockCache->invalidateTags([UserStockCacheTags::USER_PROPOSED_AUDIOBOOKS->value]),
                            UserStockCacheTags::USER_CATEGORIES_TREE->value => $stockCache->invalidateTags([UserStockCacheTags::USER_CATEGORIES_TREE->value]),
                            default =>
                            $stockCache->invalidateTags([UserStockCacheTags::AUDIOBOOK_COMMENTS->value])
                        };
                    }
                }
            }

            return ResponseTool::getResponse();
        }

        $endpointLogger->error('Invalid given Query');
        $translateService->setPreferredLanguage($request);
        throw new InvalidJsonDataException($translateService);
    }

    #[Route('/technical/cache/pools', name: 'adminTechnicalCachePools', methods: ['GET'])]
    #[AuthValidation(checkAuthToken: true, roles: [UserRolesNames::ADMINISTRATOR, UserRolesNames::RECRUITER])]
    #[OA\Post(
        description: 'Endpoint is used to clear cache pools by admin',
        requestBody: new OA\RequestBody(),
        responses  : [
            new OA\Response(
                response   : 200,
                description: 'Success',
                content    : new Model(type: AdminTechnicalCachePoolsModel::class),
            ),
        ]
    )]
    public function adminTechnicalCachePools(): Response
    {
        $successModel = new AdminTechnicalCachePoolsModel();

        foreach (AdminStockCacheTags::cases() as $case) {
            $successModel->addAdminCachePool(new CacheModel($case->value));
        }

        foreach (UserStockCacheTags::cases() as $case) {
            $successModel->addUserCachePool(new CacheModel($case->value));
        }

        return ResponseTool::getResponse($successModel);
    }
}
