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
 * AdminAudiobookController
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
#[OA\Tag(name: "AdminAudiobook")]
class AdminAudiobookController extends AbstractController
{
    //1 - Pobranie danych audiobooka(z wszystkimi danymi kategorii(nazwa,id),aktywności)
    //2 - Dodanie audiobooka(w jednym folderze wszystkie) z wyborem kategorii
    //3 - Edycja
    //4 - Usunięcie(wszędzie)
    //5 - Pobranie zipa
    //6 - Ponowne przesłanie
    //7 - Lista wszystkich audiobooków
    //8 - Lista Ostatnio dodanych audiobooków
    //9 - Aktywacja audiobooka
    #[Route("/api/admin/audiobook/", name: "adminAudiobook", methods: ["POST"])]
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
    public function adminAudiobook(
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