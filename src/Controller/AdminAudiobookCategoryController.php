<?php

namespace App\Controller;

use App\Annotation\AuthValidation;
use App\Exception\DataNotFoundException;
use App\Exception\InvalidJsonDataException;
use App\Model\DataNotFoundModel;
use App\Model\JsonDataInvalidModel;
use App\Model\NotAuthorizeModel;
use App\Model\PermissionNotGrantedModel;
use App\Query\AdminCategoryActiveQuery;
use App\Query\AdminCategoryAddQuery;
use App\Query\AdminCategoryAudiobooksQuery;
use App\Query\AdminCategoryEditQuery;
use App\Query\AdminCategoryRemoveAudiobookQuery;
use App\Query\AdminCategoryRemoveQuery;
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
    //1 - Dodanie(na start nie widoczna)
    //2 - Edycja(nazwa)
    //3 - Usunięcie - bez usuwania całej kategori tylko kategorii z audiobooków
    //4 - Unięcie audiobooka z kategorii
    //5 - Pobranie wszystkich audiobooków mających kategorie(z paginacją)
    //6 - Pobranie wszystkich kategorii(drzewo)
    //7 - Ustawienie widoczności(flaga active)
    /**
     * @param Request $request
     * @param RequestServiceInterface $requestService
     * @param AuthorizedUserServiceInterface $authorizedUserService
     * @param LoggerInterface $endpointLogger
     * @return Response
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
//                content: new Model(type: InvestmentPaymentDuePaymentsSuccessModel::class)
            )
        ]
    )]
    public function adminCategoryAdd(
        Request                        $request,
        RequestServiceInterface        $requestService,
        AuthorizedUserServiceInterface $authorizedUserService,
        LoggerInterface                $endpointLogger,

    ): Response
    {
        $adminCategoryAddQuery = $requestService->getRequestBodyContent($request, AdminCategoryAddQuery::class);

        if ($adminCategoryAddQuery instanceof AdminCategoryAddQuery) {


//            if ( == null) {
//                $endpointLogger->error("Offer dont exist");
//                throw new DataNotFoundException(["investmentPaymentDuePayments.investmentPaymentDueOffer.not.exist"]);
//            }

            return ResponseTool::getResponse(httpCode: 201);
        } else {
            $endpointLogger->error("Invalid given Query");
            throw new InvalidJsonDataException("adminCategory.add.invalid.query");
        }
    }

    /**
     * @param Request $request
     * @param RequestServiceInterface $requestService
     * @param AuthorizedUserServiceInterface $authorizedUserService
     * @param LoggerInterface $endpointLogger
     * @return Response
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
//                content: new Model(type: InvestmentPaymentDuePaymentsSuccessModel::class)
            )
        ]
    )]
    public function adminCategoryEdit(
        Request                        $request,
        RequestServiceInterface        $requestService,
        AuthorizedUserServiceInterface $authorizedUserService,
        LoggerInterface                $endpointLogger,

    ): Response
    {
        $adminCategoryEditQuery = $requestService->getRequestBodyContent($request, AdminCategoryEditQuery::class);

        if ($adminCategoryEditQuery instanceof AdminCategoryEditQuery) {

//            if ( == null) {
//                $endpointLogger->error("Offer dont exist");
//                throw new DataNotFoundException(["investmentPaymentDuePayments.investmentPaymentDueOffer.not.exist"]);
//            }

            return ResponseTool::getResponse();
        } else {
            $endpointLogger->error("Invalid given Query");
            throw new InvalidJsonDataException("adminCategory.edit.invalid.query");
        }
    }

    /**
     * @param Request $request
     * @param RequestServiceInterface $requestService
     * @param AuthorizedUserServiceInterface $authorizedUserService
     * @param LoggerInterface $endpointLogger
     * @return Response
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
//                content: new Model(type: InvestmentPaymentDuePaymentsSuccessModel::class)
            )
        ]
    )]
    public function adminCategoryRemove(
        Request                        $request,
        RequestServiceInterface        $requestService,
        AuthorizedUserServiceInterface $authorizedUserService,
        LoggerInterface                $endpointLogger,

    ): Response
    {
        $adminCategoryRemoveQuery = $requestService->getRequestBodyContent($request, AdminCategoryRemoveQuery::class);

        if ($adminCategoryRemoveQuery instanceof AdminCategoryRemoveQuery) {

//            if ( == null) {
//                $endpointLogger->error("Offer dont exist");
//                throw new DataNotFoundException(["investmentPaymentDuePayments.investmentPaymentDueOffer.not.exist"]);
//            }

            return ResponseTool::getResponse();
        } else {
            $endpointLogger->error("Invalid given Query");
            throw new InvalidJsonDataException("adminCategory.remove.invalid.query");
        }
    }

    /**
     * @param Request $request
     * @param RequestServiceInterface $requestService
     * @param AuthorizedUserServiceInterface $authorizedUserService
     * @param LoggerInterface $endpointLogger
     * @return Response
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
//                content: new Model(type: InvestmentPaymentDuePaymentsSuccessModel::class)
            )
        ]
    )]
    public function adminCategoryRemoveAudiobook(
        Request                        $request,
        RequestServiceInterface        $requestService,
        AuthorizedUserServiceInterface $authorizedUserService,
        LoggerInterface                $endpointLogger,

    ): Response
    {
        $adminCategoryRemoveAudiobookQuery = $requestService->getRequestBodyContent($request, AdminCategoryRemoveAudiobookQuery::class);

        if ($adminCategoryRemoveAudiobookQuery instanceof AdminCategoryRemoveAudiobookQuery) {

//            if ( == null) {
//                $endpointLogger->error("Offer dont exist");
//                throw new DataNotFoundException(["investmentPaymentDuePayments.investmentPaymentDueOffer.not.exist"]);
//            }

            return ResponseTool::getResponse();
        } else {
            $endpointLogger->error("Invalid given Query");
            throw new InvalidJsonDataException("adminCategory.remove.audiobook.invalid.query");
        }
    }

    /**
     * @param Request $request
     * @param RequestServiceInterface $requestService
     * @param AuthorizedUserServiceInterface $authorizedUserService
     * @param LoggerInterface $endpointLogger
     * @return Response
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
//                content: new Model(type: InvestmentPaymentDuePaymentsSuccessModel::class)
            )
        ]
    )]
    public function adminCategoryAudiobooks(
        Request                        $request,
        RequestServiceInterface        $requestService,
        AuthorizedUserServiceInterface $authorizedUserService,
        LoggerInterface                $endpointLogger,

    ): Response
    {
        $adminCategoryAudiobooksQuery = $requestService->getRequestBodyContent($request, AdminCategoryAudiobooksQuery::class);

        if ($adminCategoryAudiobooksQuery instanceof AdminCategoryAudiobooksQuery) {

//            if ( == null) {
//                $endpointLogger->error("Offer dont exist");
//                throw new DataNotFoundException(["investmentPaymentDuePayments.investmentPaymentDueOffer.not.exist"]);
//            }

            return ResponseTool::getResponse();
        } else {
            $endpointLogger->error("Invalid given Query");
            throw new InvalidJsonDataException("adminCategory.audiobooks.invalid.query");
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
    #[Route("/api/admin/categories", name: "adminCategories", methods: ["GET"])]
    #[AuthValidation(checkAuthToken: true, roles: ["Administrator"])]
    #[OA\Get(
        description: "Endpoint is returning all categories in system",
        requestBody: new OA\RequestBody(),
        responses: [
            new OA\Response(
                response: 200,
                description: "Success",
//                content: new Model(type: InvestmentPaymentDuePaymentsSuccessModel::class)
            )
        ]
    )]
    public function adminCategories(
        Request                        $request,
        RequestServiceInterface        $requestService,
        AuthorizedUserServiceInterface $authorizedUserService,
        LoggerInterface                $endpointLogger,

    ): Response
    {

//        if ( == null) {
//            $endpointLogger->error("Offer dont exist");
//            throw new DataNotFoundException(["investmentPaymentDuePayments.investmentPaymentDueOffer.not.exist"]);
//        }
        return ResponseTool::getResponse();
    }

    /**
     * @param Request $request
     * @param RequestServiceInterface $requestService
     * @param AuthorizedUserServiceInterface $authorizedUserService
     * @param LoggerInterface $endpointLogger
     * @return Response
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
//                content: new Model(type: InvestmentPaymentDuePaymentsSuccessModel::class)
            )
        ]
    )]
    public function adminCategoryActive(
        Request                        $request,
        RequestServiceInterface        $requestService,
        AuthorizedUserServiceInterface $authorizedUserService,
        LoggerInterface                $endpointLogger,

    ): Response
    {
        $adminCategoryActiveQuery = $requestService->getRequestBodyContent($request, AdminCategoryActiveQuery::class);

        if ($adminCategoryActiveQuery instanceof AdminCategoryActiveQuery) {

//            if ( == null) {
//                $endpointLogger->error("Offer dont exist");
//                throw new DataNotFoundException(["investmentPaymentDuePayments.investmentPaymentDueOffer.not.exist"]);
//            }

            return ResponseTool::getResponse();
        } else {
            $endpointLogger->error("Invalid given Query");
            throw new InvalidJsonDataException("adminCategory.active.invalid.query");
        }
    }
}