<?php

namespace App\Controller;

use App\Annotation\AuthValidation;
use App\Entity\AuthenticationToken;
use App\Exception\DataNotFoundException;
use App\Exception\InvalidJsonDataException;
use App\Exception\PermissionException;
use App\Model\AuthorizationSuccessModel;
use App\Model\DataNotFoundModel;
use App\Model\JsonDataInvalidModel;
use App\Model\NotAuthorizeModel;
use App\Model\PermissionNotGrantedModel;
use App\Query\AuthorizeQuery;
use App\Repository\AuthenticationTokenRepository;
use App\Repository\UserInformationRepository;
use App\Repository\UserPasswordRepository;
use App\Service\AuthorizedUserServiceInterface;
use App\Service\RequestServiceInterface;
use App\Tool\ResponseTool;
use App\ValueGenerator\AuthTokenGenerator;
use App\ValueGenerator\PasswordHashGenerator;
use Doctrine\ORM\NonUniqueResultException;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Attributes as OA;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
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
    //1 - Pobranie całej listy kategorii(paginacja)(z aktywnymi audiobookami)(aktywnych kategorii)
    //2 - Pobranie listy proponowanych audiobooków
    //3 - Pobranie detali audiobooka(wraz z tym czy jest w mojej liscie)(jeśli jest aktywny)
    //4 - Pobranie danych o odtwarzaniu audiobooka
    //5 - Dodanie do mojej listy
    //6 - Pobranie audiobooków z mojej listy
    #[Route("/api/user/audiobook/", name: "userAudiobook", methods: ["POST"])]
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
                content: new Model(type: InvestmentPaymentDuePaymentsSuccessModel::class)
            )
        ]
    )]
    public function userAudiobook(
        Request                             $request,
        RequestServiceInterface             $requestService,
        AuthorizedUserServiceInterface      $authorizedUserService,
        RentFlatRepository                  $rentFlatRepository,
        RentFlatPaymentRepository           $rentFlatPaymentRepository,
        InvestmentPaymentDueOfferRepository $investmentPaymentDueOfferRepository,
        LoggerInterface                     $endpointLogger,

    ): Response
    {
        $investmentPaymentDuePaymentsQuery = $requestService->getRequestBodyContent($request, InvestmentPaymentDuePaymentsQuery::class);

        if ($investmentPaymentDuePaymentsQuery instanceof InvestmentPaymentDuePaymentsQuery) {

            $investmentPaymentDueOffer = $investmentPaymentDueOfferRepository->findOneBy([
                "id" => $investmentPaymentDuePaymentsQuery->getInvestmentPaymentDueOffer()
            ]);

            if ($investmentPaymentDueOffer == null) {
                $endpointLogger->error("Offer dont exist");
                throw new DataNotFoundException(["investmentPaymentDuePayments.investmentPaymentDueOffer.not.exist"]);
            }

            return ResponseTool::getResponse();
        } else {
            $endpointLogger->error("Invalid given Query");
            throw new InvalidJsonDataException("investmentPaymentDuePayments.invalid.query");
        }
    }
}