<?php

declare(strict_types = 1);

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
use Nelmio\ApiDocBundle\Attribute\Model;
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
#[OA\Tag(name: 'AdminTechnical')]
class AdminTechnicalController extends AbstractController
{
    public function __construct(private readonly AuthorizedUserServiceInterface $authorizedUserService, private readonly TechnicalBreakRepository $technicalBreakRepository, private readonly TagAwareCacheInterface $stockCache, private readonly RequestServiceInterface $requestService, private readonly LoggerInterface $endpointLogger, private readonly TranslateServiceInterface $translateService, private readonly SerializerInterface $serializer, private readonly KernelInterface $kernel) {}

    #[Route('/api/admin/technical/break', name: 'adminTechnicalBreakPut', methods: ['PUT'])]
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
    public function adminTechnicalBreakPut(): Response
    {
        $user = $this->authorizedUserService::getAuthorizedUser();
        $activeTechnicalBreak = $this->technicalBreakRepository->findOneBy([
            'active' => true,
        ]);
        if (null === $activeTechnicalBreak) {
            $this->technicalBreakRepository->add(new TechnicalBreak(true, $user));
            $this->stockCache->invalidateTags([AdminStockCacheTags::ADMIN_TECHNICAL_BREAK->value]);
        }

        return ResponseTool::getResponse(httpCode: Response::HTTP_CREATED);
    }

    #[Route('/api/admin/technical/break', name: 'adminTechnicalBreakPatch', methods: ['PATCH'])]
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
    ): Response {
        $adminTechnicalBreakPatchQuery = $this->requestService->getRequestBodyContent($request, AdminTechnicalBreakPatchQuery::class);

        if ($adminTechnicalBreakPatchQuery instanceof AdminTechnicalBreakPatchQuery) {
            $technicalBreak = $this->technicalBreakRepository->find($adminTechnicalBreakPatchQuery->getTechnicalBreakId());

            if (null === $technicalBreak) {
                $this->endpointLogger->error('TechnicalBreak dont exist');
                $this->translateService->setPreferredLanguage($request);
                throw new DataNotFoundException([$this->translateService->getTranslation('TechnicalBreakDontExists')]);
            }

            $technicalBreak
                ->setUser($this->authorizedUserService::getAuthorizedUser())
                ->setDateTo(new DateTime())
                ->setActive(false);

            $this->technicalBreakRepository->add($technicalBreak);

            $this->stockCache->invalidateTags([AdminStockCacheTags::ADMIN_TECHNICAL_BREAK->value]);

            return ResponseTool::getResponse();
        }

        $this->endpointLogger->error('Invalid given Query');
        $this->translateService->setPreferredLanguage($request);
        throw new InvalidJsonDataException($this->translateService);
    }

    #[Route('/api/admin/technical/break/list', name: 'adminTechnicalBreakList', methods: ['POST'])]
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
    ): Response {
        $adminTechnicalBreakListQuery = $this->requestService->getRequestBodyContent($request, AdminTechnicalBreakListQuery::class);

        if ($adminTechnicalBreakListQuery instanceof AdminTechnicalBreakListQuery) {
            $technicalBreakListData = $adminTechnicalBreakListQuery->getSearchData();

            $reportSearchModel = new AdminTechnicalBreaksSearchModel();
            $this->serializer->deserialize(
                json_encode($technicalBreakListData),
                AdminTechnicalBreaksSearchModel::class,
                'json',
                [
                    AbstractNormalizer::OBJECT_TO_POPULATE             => $reportSearchModel,
                    AbstractObjectNormalizer::DISABLE_TYPE_ENFORCEMENT => true,
                ],
            );

            $successModel = new AdminTechnicalBreakSuccessModel();

            $technicalBreaks = $this->technicalBreakRepository->getTechnicalBreakByPage($reportSearchModel);

            $minResult = $adminTechnicalBreakListQuery->getPage() * $adminTechnicalBreakListQuery->getLimit();
            $maxResult = $adminTechnicalBreakListQuery->getLimit() + $minResult;

            foreach ($technicalBreaks as $index => $technicalBreak) {
                if ($index < $minResult) {
                    continue;
                }

                if ($index < $maxResult) {
                    $technicalBreakModel = new AdminTechnicalBreakModel(
                        (string) $technicalBreak->getId(),
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
            $successModel->setMaxPage((int) ceil(count($technicalBreaks) / $adminTechnicalBreakListQuery->getLimit()));

            return ResponseTool::getResponse($successModel);
        }

        $this->endpointLogger->error('Invalid given Query');
        $this->translateService->setPreferredLanguage($request);
        throw new InvalidJsonDataException($this->translateService);
    }

    #[Route('/api/admin/technical/cache/clear', name: 'adminTechnicalCacheClear', methods: ['PATCH'])]
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
    ): Response {
        $adminTechnicalCacheClearQuery = $this->requestService->getRequestBodyContent($request, AdminTechnicalCacheClearQuery::class);

        if ($adminTechnicalCacheClearQuery instanceof AdminTechnicalCacheClearQuery) {
            $cacheData = $adminTechnicalCacheClearQuery->getCacheData();

            if (array_key_exists('all', $cacheData) && $cacheData['all']) {
                $application = new Application($this->kernel);
                $application->setAutoExit(false);

                $output = new BufferedOutput();
                $application->run(new ArrayInput([
                    'command' => 'cache:pool:clear',
                    '--all'   => true,
                ]), $output);
            }

            if (array_key_exists('admin', $cacheData) && $cacheData['admin']) {
                $this->stockCache->invalidateTags([AdminStockCacheTags::ADMIN_CATEGORY->value,
                    AdminStockCacheTags::ADMIN_CATEGORY_AUDIOBOOKS->value,
                    AdminStockCacheTags::ADMIN_AUDIOBOOK->value,
                    AdminStockCacheTags::ADMIN_STATISTICS->value,
                    AdminStockCacheTags::ADMIN_ROLES->value,
                    AdminStockCacheTags::ADMIN_TECHNICAL_BREAK->value]);
            } else {
                if (array_key_exists('user', $cacheData) && $cacheData['user']) {
                    $this->stockCache->invalidateTags([UserStockCacheTags::USER_AUDIOBOOK_PART->value,
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
                            AdminStockCacheTags::ADMIN_CATEGORY->value            => $this->stockCache->invalidateTags([AdminStockCacheTags::ADMIN_CATEGORY->value]),
                            AdminStockCacheTags::ADMIN_CATEGORY_AUDIOBOOKS->value => $this->stockCache->invalidateTags([AdminStockCacheTags::ADMIN_CATEGORY_AUDIOBOOKS->value]),
                            AdminStockCacheTags::ADMIN_AUDIOBOOK->value           => $this->stockCache->invalidateTags([AdminStockCacheTags::ADMIN_AUDIOBOOK->value]),
                            AdminStockCacheTags::ADMIN_STATISTICS->value          => $this->stockCache->invalidateTags([AdminStockCacheTags::ADMIN_STATISTICS->value]),
                            AdminStockCacheTags::ADMIN_ROLES->value               => $this->stockCache->invalidateTags([AdminStockCacheTags::ADMIN_ROLES->value]),
                            AdminStockCacheTags::ADMIN_TECHNICAL_BREAK->value     => $this->stockCache->invalidateTags([AdminStockCacheTags::ADMIN_TECHNICAL_BREAK->value]),
                            UserStockCacheTags::USER_AUDIOBOOK_PART->value        => $this->stockCache->invalidateTags([UserStockCacheTags::USER_AUDIOBOOK_PART->value]),
                            UserStockCacheTags::USER_NOTIFICATIONS->value         => $this->stockCache->invalidateTags([UserStockCacheTags::USER_NOTIFICATIONS->value]),
                            UserStockCacheTags::USER_AUDIOBOOKS->value            => $this->stockCache->invalidateTags([UserStockCacheTags::USER_AUDIOBOOKS->value]),
                            UserStockCacheTags::USER_AUDIOBOOK_DETAIL->value      => $this->stockCache->invalidateTags([UserStockCacheTags::USER_AUDIOBOOK_DETAIL->value]),
                            UserStockCacheTags::USER_AUDIOBOOK_RATING->value      => $this->stockCache->invalidateTags([UserStockCacheTags::USER_AUDIOBOOK_RATING->value]),
                            UserStockCacheTags::USER_PROPOSED_AUDIOBOOKS->value   => $this->stockCache->invalidateTags([UserStockCacheTags::USER_PROPOSED_AUDIOBOOKS->value]),
                            UserStockCacheTags::USER_CATEGORIES_TREE->value       => $this->stockCache->invalidateTags([UserStockCacheTags::USER_CATEGORIES_TREE->value]),
                            default                                               => $this->stockCache->invalidateTags([UserStockCacheTags::AUDIOBOOK_COMMENTS->value]),
                        };
                    }
                }
            }

            return ResponseTool::getResponse();
        }

        $this->endpointLogger->error('Invalid given Query');
        $this->translateService->setPreferredLanguage($request);
        throw new InvalidJsonDataException($this->translateService);
    }

    #[Route('/api/admin/technical/cache/pools', name: 'adminTechnicalCachePools', methods: ['GET'])]
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
