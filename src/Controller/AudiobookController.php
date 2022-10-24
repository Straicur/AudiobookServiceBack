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
 * AudiobookController
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
#[OA\Tag(name: "Audiobook")]
class AudiobookController extends AbstractController
{
    //1 - Pobranie cover.jpg
    //2 - Pobranie parta(w obie strony)
    /**
     * @param Request $request
     * @param RequestServiceInterface $requestService
     * @param AuthorizedUserServiceInterface $authorizedUserService
     * @param LoggerInterface $endpointLogger
     * @return Response
     * @throws DataNotFoundException
     * @throws InvalidJsonDataException
     */
    #[Route("/api/audiobook/", name: "audiobook", methods: ["POST"])]
    #[AuthValidation(checkAuthToken: true, roles: ["Administrator"])]
    #[OA\Post(
        description: "Endpoint is ",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                ref: new Model(type: InvestmentPaymentDuePaymentsQuery::class),
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
    public function audiobook(
        Request                        $request,
        RequestServiceInterface        $requestService,
        AuthorizedUserServiceInterface $authorizedUserService,
        LoggerInterface                $endpointLogger,

    ): Response
    {
        $investmentPaymentDuePaymentsQuery = $requestService->getRequestBodyContent($request, InvestmentPaymentDuePaymentsQuery::class);

        if ($investmentPaymentDuePaymentsQuery instanceof InvestmentPaymentDuePaymentsQuery) {

//            if ( == null) {
//                $endpointLogger->error("Offer dont exist");
//                throw new DataNotFoundException(["investmentPaymentDuePayments.investmentPaymentDueOffer.not.exist"]);
//            }

            return ResponseTool::getResponse();
        } else {
            $endpointLogger->error("Invalid given Query");
            throw new InvalidJsonDataException("investmentPaymentDuePayments.invalid.query");
        }
    }
}