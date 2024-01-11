<?php

namespace App\Controller;

use App\Annotation\AuthValidation;
use App\Entity\TechnicalBreak;
use App\Exception\DataNotFoundException;
use App\Exception\InvalidJsonDataException;
use App\Model\Admin\AdminTechnicalBreakModel;
use App\Model\Admin\AdminTechnicalBreakSuccessModel;
use App\Model\Error\DataNotFoundModel;
use App\Model\Error\JsonDataInvalidModel;
use App\Model\Error\NotAuthorizeModel;
use App\Model\Error\PermissionNotGrantedModel;
use App\Query\Admin\AdminTechnicalBreakListQuery;
use App\Query\Admin\AdminTechnicalBreakPatchQuery;
use App\Repository\TechnicalBreakRepository;
use App\Service\AuthorizedUserServiceInterface;
use App\Service\RequestServiceInterface;
use App\Service\TranslateService;
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
#[OA\Tag(name: "AdminTechnical")]
class AdminTechnicalController extends AbstractController
{
    /**
     * @param Request $request
     * @param RequestServiceInterface $requestService
     * @param AuthorizedUserServiceInterface $authorizedUserService
     * @param TechnicalBreakRepository $technicalBreakRepository
     * @return Response
     */
    #[Route("/api/admin/technical/break", name: "adminTechnicalBreakPut", methods: ["PUT"])]
    #[AuthValidation(checkAuthToken: true, roles: ["Administrator"])]
    #[OA\Put(
        description: "Endpoint is used to add Technical Break for admin",
        requestBody: new OA\RequestBody(),
        responses: [
            new OA\Response(
                response: 201,
                description: "Success",
            )
        ]
    )]
    public function adminTechnicalBreakPut(
        Request                        $request,
        RequestServiceInterface        $requestService,
        AuthorizedUserServiceInterface $authorizedUserService,
        TechnicalBreakRepository       $technicalBreakRepository
    ): Response
    {
        $user = $authorizedUserService->getAuthorizedUser();
        $technicalBreakRepository->add(new TechnicalBreak(true, $user));

        return ResponseTool::getResponse(httpCode: 201);
    }

    /**
     * @param Request $request
     * @param RequestServiceInterface $requestService
     * @param AuthorizedUserServiceInterface $authorizedUserService
     * @param TechnicalBreakRepository $technicalBreakRepository
     * @param LoggerInterface $endpointLogger
     * @param TranslateService $translateService
     * @return Response
     * @throws DataNotFoundException
     * @throws InvalidJsonDataException
     */
    #[Route("/api/admin/technical/break", name: "adminTechnicalBreakPatch", methods: ["PATCH"])]
    #[AuthValidation(checkAuthToken: true, roles: ["Administrator"])]
    #[OA\Patch(
        description: "Endpoint is used to edit Technical Break by admin",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                ref: new Model(type: AdminTechnicalBreakPatchQuery::class),
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
    public function adminTechnicalBreakPatch(
        Request                        $request,
        RequestServiceInterface        $requestService,
        AuthorizedUserServiceInterface $authorizedUserService,
        TechnicalBreakRepository       $technicalBreakRepository,
        LoggerInterface                $endpointLogger,
        TranslateService               $translateService
    ): Response
    {
        $adminTechnicalBreakPatchQuery = $requestService->getRequestBodyContent($request, AdminTechnicalBreakPatchQuery::class);

        if ($adminTechnicalBreakPatchQuery instanceof AdminTechnicalBreakPatchQuery) {

            $technicalBreak = $technicalBreakRepository->findOneBy([
                "id" => $adminTechnicalBreakPatchQuery->getTechnicalBreakId()
            ]);

            if ($technicalBreak === null) {
                $endpointLogger->error("TechnicalBreak dont exist");
                $translateService->setPreferredLanguage($request);
                throw new DataNotFoundException([$translateService->getTranslation("TechnicalBreakDontExists")]);
            }

            $technicalBreak->setUser($authorizedUserService->getAuthorizedUser());
            $technicalBreak->setDateTo(new \DateTime('Now'));
            $technicalBreak->setActive(false);

            $technicalBreakRepository->add($technicalBreak);

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
     * @param TechnicalBreakRepository $technicalBreakRepository
     * @param TranslateService $translateService
     * @return Response
     * @throws InvalidJsonDataException
     */
    #[Route("/api/admin/technical/break/list", name: "adminTechnicalBreakList", methods: ["POST"])]
    #[AuthValidation(checkAuthToken: true, roles: ["Administrator"])]
    #[OA\Post(
        description: "Endpoint is used to get list of Technical Breaks for admin",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                ref: new Model(type: AdminTechnicalBreakListQuery::class),
                type: "object"
            ),
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Success",
                content: new Model(type: AdminTechnicalBreakSuccessModel::class)
            )
        ]
    )]
    public function adminTechnicalBreakList(
        Request                        $request,
        RequestServiceInterface        $requestService,
        AuthorizedUserServiceInterface $authorizedUserService,
        LoggerInterface                $endpointLogger,
        TechnicalBreakRepository       $technicalBreakRepository,
        TranslateService               $translateService
    ): Response
    {
        $adminTechnicalBreakListQuery = $requestService->getRequestBodyContent($request, AdminTechnicalBreakListQuery::class);

        if ($adminTechnicalBreakListQuery instanceof AdminTechnicalBreakListQuery) {

            $technicalBreakListData = $adminTechnicalBreakListQuery->getSearchData();

            $userId = null;
            $active = null;
            $order = null;
            $dateFrom = null;
            $dateTo = null;

            if (array_key_exists('userId', $technicalBreakListData)) {
                $userId = $technicalBreakListData['userId'];
            }
            if (array_key_exists('active', $technicalBreakListData)) {
                $active = $technicalBreakListData['active'];
            }
            if (array_key_exists('order', $technicalBreakListData)) {
                $order = $technicalBreakListData['order'];
            }
            if (array_key_exists('dateFrom', $technicalBreakListData)) {
                $dateFrom = $technicalBreakListData['dateFrom'];
            }
            if (array_key_exists('dateTo', $technicalBreakListData) && $technicalBreakListData['dateTo'] !== false) {
                $dateTo = $technicalBreakListData['dateTo'];
            }

            $successModel = new AdminTechnicalBreakSuccessModel();

            $technicalBreaks = $technicalBreakRepository->getTechnicalBreakByPage($userId, $active, $order, $dateFrom, $dateTo);

            $minResult = $adminTechnicalBreakListQuery->getPage() * $adminTechnicalBreakListQuery->getLimit();
            $maxResult = $adminTechnicalBreakListQuery->getLimit() + $minResult;

            foreach ($technicalBreaks as $index => $technicalBreak) {
                if ($index < $minResult) {
                    continue;
                }

                if ($index < $maxResult) {
                    $technicalBreakModel = new AdminTechnicalBreakModel(
                        $technicalBreak->getId(),
                        $technicalBreak->getActive(),
                        $technicalBreak->getDateFrom(),
                        $technicalBreak->getUser()->getId(),
                    );

                    if ($technicalBreak->getDateTo() != null) {
                        $technicalBreakModel->setDateTo($technicalBreak->getDateTo());
                    }

                    $successModel->addTechnicalBreak($technicalBreakModel);
                } else {
                    break;
                }
            }

            $successModel->setPage($adminTechnicalBreakListQuery->getPage());
            $successModel->setLimit($adminTechnicalBreakListQuery->getLimit());
            $successModel->setMaxPage(ceil(count($technicalBreaks) / $adminTechnicalBreakListQuery->getLimit()));

            return ResponseTool::getResponse($successModel);
        }

        $endpointLogger->error("Invalid given Query");
        $translateService->setPreferredLanguage($request);
        throw new InvalidJsonDataException($translateService);
    }
}