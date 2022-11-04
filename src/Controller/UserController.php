<?php

namespace App\Controller;

use App\Annotation\AuthValidation;
use App\Exception\DataNotFoundException;
use App\Exception\InvalidJsonDataException;
use App\Model\DataNotFoundModel;
use App\Model\JsonDataInvalidModel;
use App\Model\NotAuthorizeModel;
use App\Model\PermissionNotGrantedModel;
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
 * UserController
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
#[OA\Tag(name: "User")]
class UserController extends AbstractController
{
    //1 - Zmiana hasła
    //2 - Zmiana emaila
    //3 - Usunięcie konta
    //4 - Zmiana numeru tel,imienia,nazwiska
    // todo tu porządnie przemyśl te endpointy i na koniec dodaj notyfikacje jako encję
    //Do tego jakiś enum który oznajmi z czego ma dostawać
    /**
     * @param Request $request
     * @param RequestServiceInterface $requestService
     * @param AuthorizedUserServiceInterface $authorizedUserService
     * @param LoggerInterface $endpointLogger
     * @return Response
     * @throws DataNotFoundException
     * @throws InvalidJsonDataException
     */
    #[Route("/api/user/settings/password", name: "userSettingsPassword", methods: ["PATCH"])]
    #[AuthValidation(checkAuthToken: true, roles: ["User"])]
    #[OA\Patch(
        description: "Endpoint is changing password of logged user",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
//                ref: new Model(type: InvestmentPaymentDuePaymentsQuery::class),
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
    public function userSettingsPassword(
        Request                        $request,
        RequestServiceInterface        $requestService,
        AuthorizedUserServiceInterface $authorizedUserService,
        LoggerInterface                $endpointLogger,

    ): Response
    {
//        $investmentPaymentDuePaymentsQuery = $requestService->getRequestBodyContent($request, InvestmentPaymentDuePaymentsQuery::class);
//
//        if ($investmentPaymentDuePaymentsQuery instanceof InvestmentPaymentDuePaymentsQuery) {

//            if ( == null) {
//                $endpointLogger->error("Offer dont exist");
//                throw new DataNotFoundException(["investmentPaymentDuePayments.investmentPaymentDueOffer.not.exist"]);
//            }

            return ResponseTool::getResponse();
//        } else {
//            $endpointLogger->error("Invalid given Query");
//            throw new InvalidJsonDataException("investmentPaymentDuePayments.invalid.query");
//        }
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
    #[Route("/api/user/settings/email", name: "userSettingsEmail", methods: ["POST"])]
    #[AuthValidation(checkAuthToken: true, roles: ["User"])]
    #[OA\Post(
        description: "Endpoint is sending confirmation email to change user email",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
//                ref: new Model(type: InvestmentPaymentDuePaymentsQuery::class),
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
    public function userSettingsEmail(
        Request                        $request,
        RequestServiceInterface        $requestService,
        AuthorizedUserServiceInterface $authorizedUserService,
        LoggerInterface                $endpointLogger,

    ): Response
    {
//        $investmentPaymentDuePaymentsQuery = $requestService->getRequestBodyContent($request, InvestmentPaymentDuePaymentsQuery::class);
//
//        if ($investmentPaymentDuePaymentsQuery instanceof InvestmentPaymentDuePaymentsQuery) {

//            if ( == null) {
//                $endpointLogger->error("Offer dont exist");
//                throw new DataNotFoundException(["investmentPaymentDuePayments.investmentPaymentDueOffer.not.exist"]);
//            }

        return ResponseTool::getResponse();
//        } else {
//            $endpointLogger->error("Invalid given Query");
//            throw new InvalidJsonDataException("investmentPaymentDuePayments.invalid.query");
//        }
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
    #[Route("/api/user/settings/email/change/{email}/{id}", name: "userSettingsEmailChange", methods: ["GET"])]
    #[AuthValidation(checkAuthToken: true, roles: ["User"])]
    #[OA\Get(
        description: "Endpoint is sending confirmation email to change user email",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
//                ref: new Model(type: InvestmentPaymentDuePaymentsQuery::class),
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
    public function userSettingsEmailChange(
        Request                        $request,
        RequestServiceInterface        $requestService,
        AuthorizedUserServiceInterface $authorizedUserService,
        LoggerInterface                $endpointLogger,

    ): Response
    {
//        $investmentPaymentDuePaymentsQuery = $requestService->getRequestBodyContent($request, InvestmentPaymentDuePaymentsQuery::class);
//
//        if ($investmentPaymentDuePaymentsQuery instanceof InvestmentPaymentDuePaymentsQuery) {

//            if ( == null) {
//                $endpointLogger->error("Offer dont exist");
//                throw new DataNotFoundException(["investmentPaymentDuePayments.investmentPaymentDueOffer.not.exist"]);
//            }

        return ResponseTool::getResponse();
//        } else {
//            $endpointLogger->error("Invalid given Query");
//            throw new InvalidJsonDataException("investmentPaymentDuePayments.invalid.query");
//        }
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
    #[Route("/api/user/settings/delete", name: "userSettingsDelete", methods: ["POST"])]
    #[AuthValidation(checkAuthToken: true, roles: ["User"])]
    #[OA\Post(
        description: "Endpoint is setting user account to not active",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
//                ref: new Model(type: InvestmentPaymentDuePaymentsQuery::class),
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
    public function userSettingsDelete(
        Request                        $request,
        RequestServiceInterface        $requestService,
        AuthorizedUserServiceInterface $authorizedUserService,
        LoggerInterface                $endpointLogger,

    ): Response
    {
        //todo na później będzie lista z zbanowanymi i też dodatego odpowiendia tabela(chyba że będę miał dużo czasu to to dorób)
        // czyli do tego jeszcze akceptacja/usunięcie prośby I tu powiadomienie i email, lista kont do usunięcia
        // I tu może złyżyć prośbę jeśli już jakiejś w systemie nie ma
        // Ale jak już jakąś złuży to jest ustawiany jako nieaktywny
//        $investmentPaymentDuePaymentsQuery = $requestService->getRequestBodyContent($request, InvestmentPaymentDuePaymentsQuery::class);
//
//        if ($investmentPaymentDuePaymentsQuery instanceof InvestmentPaymentDuePaymentsQuery) {

//            if ( == null) {
//                $endpointLogger->error("Offer dont exist");
//                throw new DataNotFoundException(["investmentPaymentDuePayments.investmentPaymentDueOffer.not.exist"]);
//            }

        return ResponseTool::getResponse();
//        } else {
//            $endpointLogger->error("Invalid given Query");
//            throw new InvalidJsonDataException("investmentPaymentDuePayments.invalid.query");
//        }
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
    #[Route("/api/user/settings/change", name: "userSettingsChange", methods: ["POST"])]
    #[AuthValidation(checkAuthToken: true, roles: ["User"])]
    #[OA\Post(
        description: "Endpoint is changing given user informations",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
//                ref: new Model(type: InvestmentPaymentDuePaymentsQuery::class),
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
    public function userSettingsChange(
        Request                        $request,
        RequestServiceInterface        $requestService,
        AuthorizedUserServiceInterface $authorizedUserService,
        LoggerInterface                $endpointLogger,

    ): Response
    {
//        $investmentPaymentDuePaymentsQuery = $requestService->getRequestBodyContent($request, InvestmentPaymentDuePaymentsQuery::class);
//
//        if ($investmentPaymentDuePaymentsQuery instanceof InvestmentPaymentDuePaymentsQuery) {

//            if ( == null) {
//                $endpointLogger->error("Offer dont exist");
//                throw new DataNotFoundException(["investmentPaymentDuePayments.investmentPaymentDueOffer.not.exist"]);
//            }

        return ResponseTool::getResponse();
//        } else {
//            $endpointLogger->error("Invalid given Query");
//            throw new InvalidJsonDataException("investmentPaymentDuePayments.invalid.query");
//        }
    }
}