<?php

namespace App\Controller;

use App\Annotation\AuthValidation;
use App\Exception\DataNotFoundException;
use App\Exception\InvalidJsonDataException;
use App\Model\AdminAudiobookDetailsSuccessModel;
use App\Model\AdminAudiobooksSuccessModel;
use App\Model\AdminCategoryAudiobookModel;
use App\Model\DataNotFoundModel;
use App\Model\JsonDataInvalidModel;
use App\Model\NotAuthorizeModel;
use App\Model\PermissionNotGrantedModel;
use App\Query\AdminAudiobookActiveQuery;
use App\Query\AdminAudiobookAddQuery;
use App\Query\AdminAudiobookDeleteQuery;
use App\Query\AdminAudiobookDetailsQuery;
use App\Query\AdminAudiobookEditQuery;
use App\Query\AdminAudiobookReAddingQuery;
use App\Query\AdminAudiobooksQuery;
use App\Query\AdminAudiobookZipQuery;
use App\Repository\AudiobookCategoryRepository;
use App\Repository\AudiobookRepository;
use App\Service\AuthorizedUserServiceInterface;
use App\Service\RequestServiceInterface;
use App\Tool\ResponseTool;
use App\ValueGenerator\BuildAudiobookCategoryTreeGenerator;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Attributes as OA;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
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
    /**
     * @param Request $request
     * @param RequestServiceInterface $requestService
     * @param AuthorizedUserServiceInterface $authorizedUserService
     * @param LoggerInterface $endpointLogger
     * @param AudiobookRepository $audiobookRepository
     * @return Response
     * @throws DataNotFoundException
     * @throws InvalidJsonDataException
     */
    #[Route("/api/admin/audiobook/details", name: "adminAudiobookDetails", methods: ["POST"])]
    #[AuthValidation(checkAuthToken: true, roles: ["Administrator"])]
    #[OA\Post(
        description: "Endpoint is getting details of audiobook",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                ref: new Model(type: AdminAudiobookDetailsQuery::class),
                type: "object"
            ),
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Success",
                content: new Model(type: AdminAudiobookDetailsSuccessModel::class)
            )
        ]
    )]
    public function adminAudiobookDetails(
        Request                        $request,
        RequestServiceInterface        $requestService,
        AuthorizedUserServiceInterface $authorizedUserService,
        LoggerInterface                $endpointLogger,
        AudiobookRepository            $audiobookRepository,
        AudiobookCategoryRepository $audiobookCategoryRepository
    ): Response
    {
        $adminAudiobookDetailsQuery = $requestService->getRequestBodyContent($request, AdminAudiobookDetailsQuery::class);

        if ($adminAudiobookDetailsQuery instanceof AdminAudiobookDetailsQuery) {

            $audiobook = $audiobookRepository->findOneBy([
                "id" => $adminAudiobookDetailsQuery->getAudiobookId()
            ]);

            if ($audiobook == null) {
                $endpointLogger->error("Audiobook dont exist");
                throw new DataNotFoundException(["adminAudiobook.audiobook.details.not.exist"]);
            }

            $categories = $audiobookCategoryRepository->findBy([
                "audiobooks"=>$audiobook
            ]);

            $successModel = new AdminAudiobookDetailsSuccessModel(
                $audiobook->getId(),
                $audiobook->getTitle(),
                $audiobook->getAuthor(),
                $audiobook->getVersion(),
                $audiobook->getAlbum(),
                $audiobook->getYear(),
                $audiobook->getDuration(),
                $audiobook->getSize(),
                $audiobook->getParts(),
                $audiobook->getDescription(),
                $audiobook->getAge(),
                $audiobook->getActive(),
                $categories
            );

            if ($audiobook->getEncoded() != null) {
                $successModel->setEncoded($audiobook->getEncoded());
            }

            return ResponseTool::getResponse($successModel);
        } else {
            $endpointLogger->error("Invalid given Query");
            throw new InvalidJsonDataException("adminAudiobook.details.invalid.query");
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
    #[Route("/api/admin/audiobook/add", name: "adminAudiobookAdd", methods: ["PUT"])]
    #[AuthValidation(checkAuthToken: true, roles: ["Administrator"])]
    #[OA\Put(
        description: "Endpoint is adding new audiobook with files",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                ref: new Model(type: AdminAudiobookAddQuery::class),
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
    public function adminAudiobookAdd(
        Request                        $request,
        RequestServiceInterface        $requestService,
        AuthorizedUserServiceInterface $authorizedUserService,
        LoggerInterface                $endpointLogger,

    ): Response
    {
        $adminAudiobookAddQuery = $requestService->getRequestBodyContent($request, AdminAudiobookAddQuery::class);

        if ($adminAudiobookAddQuery instanceof AdminAudiobookAddQuery) {

//            if ( == null) {
//                $endpointLogger->error("Offer dont exist");
//                throw new DataNotFoundException(["investmentPaymentDuePayments.investmentPaymentDueOffer.not.exist"]);
//            }

            return ResponseTool::getResponse();
        } else {
            $endpointLogger->error("Invalid given Query");
            throw new InvalidJsonDataException("adminAudiobook.add.invalid.query");
        }
    }

    /**
     * @param Request $request
     * @param RequestServiceInterface $requestService
     * @param AuthorizedUserServiceInterface $authorizedUserService
     * @param LoggerInterface $endpointLogger
     * @param AudiobookRepository $audiobookRepository
     * @return Response
     * @throws DataNotFoundException
     * @throws InvalidJsonDataException
     */
    #[Route("/api/admin/audiobook/edit", name: "adminAudiobookEdit", methods: ["PATCH"])]
    #[AuthValidation(checkAuthToken: true, roles: ["Administrator"])]
    #[OA\Patch(
        description: "Endpoint is editing given audiobook data",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                ref: new Model(type: AdminAudiobookEditQuery::class),
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
    public function adminAudiobookEdit(
        Request                        $request,
        RequestServiceInterface        $requestService,
        AuthorizedUserServiceInterface $authorizedUserService,
        LoggerInterface                $endpointLogger,
        AudiobookRepository            $audiobookRepository
    ): Response
    {
        $adminAudiobookEditQuery = $requestService->getRequestBodyContent($request, AdminAudiobookEditQuery::class);

        if ($adminAudiobookEditQuery instanceof AdminAudiobookEditQuery) {

            $audiobook = $audiobookRepository->findOneBy([
                "id" => $adminAudiobookEditQuery->getAudiobookId()
            ]);

            if ($audiobook == null) {
                $endpointLogger->error("Audiobook dont exist");
                throw new DataNotFoundException(["adminAudiobook.edit.audiobook.not.exist"]);
            }

            $audiobook->setTitle($adminAudiobookEditQuery->getTitle());
            $audiobook->setAuthor($adminAudiobookEditQuery->getAuthor());
            $audiobook->setVersion($adminAudiobookEditQuery->getVersion());
            $audiobook->setAlbum($adminAudiobookEditQuery->getAlbum());
            $audiobook->setYear($adminAudiobookEditQuery->getYear());
            $audiobook->setDuration($adminAudiobookEditQuery->getDuration());
            $audiobook->setSize($adminAudiobookEditQuery->getSize());
            $audiobook->setParts($adminAudiobookEditQuery->getParts());
            $audiobook->setDescription($adminAudiobookEditQuery->getDescription());
            $audiobook->setAge($adminAudiobookEditQuery->getAge());

            $audiobookRepository->add($audiobook);

            return ResponseTool::getResponse();
        } else {
            $endpointLogger->error("Invalid given Query");
            throw new InvalidJsonDataException("adminAudiobook.edit.invalid.query");
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
    #[Route("/api/admin/audiobook/delete", name: "adminAudiobookDelete", methods: ["DELETE"])]
    #[AuthValidation(checkAuthToken: true, roles: ["Administrator"])]
    #[OA\Post(
        description: "Endpoint is deleting audiobook with his files",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                ref: new Model(type: AdminAudiobookDeleteQuery::class),
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
    public function adminAudiobookDelete(
        Request                        $request,
        RequestServiceInterface        $requestService,
        AuthorizedUserServiceInterface $authorizedUserService,
        LoggerInterface                $endpointLogger,

    ): Response
    {
        $adminAudiobookDeleteQuery = $requestService->getRequestBodyContent($request, AdminAudiobookDeleteQuery::class);

        if ($adminAudiobookDeleteQuery instanceof AdminAudiobookDeleteQuery) {

//            if ( == null) {
//                $endpointLogger->error("Offer dont exist");
//                throw new DataNotFoundException(["investmentPaymentDuePayments.investmentPaymentDueOffer.not.exist"]);
//            }

            return ResponseTool::getResponse();
        } else {
            $endpointLogger->error("Invalid given Query");
            throw new InvalidJsonDataException("adminAudiobook.delete.invalid.query");
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
    #[Route("/api/admin/audiobook/zip", name: "adminAudiobookZip", methods: ["POST"])]
    #[AuthValidation(checkAuthToken: true, roles: ["Administrator"])]
    #[OA\Post(
        description: "Endpoint is returning zip blob",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                ref: new Model(type: AdminAudiobookZipQuery::class),
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
    public function adminAudiobookZip(
        Request                        $request,
        RequestServiceInterface        $requestService,
        AuthorizedUserServiceInterface $authorizedUserService,
        LoggerInterface                $endpointLogger,

    ): Response
    {
        $adminAudiobookZipQuery = $requestService->getRequestBodyContent($request, AdminAudiobookZipQuery::class);

        if ($adminAudiobookZipQuery instanceof AdminAudiobookZipQuery) {

//            if ( == null) {
//                $endpointLogger->error("Offer dont exist");
//                throw new DataNotFoundException(["investmentPaymentDuePayments.investmentPaymentDueOffer.not.exist"]);
//            }

            return ResponseTool::getResponse();
        } else {
            $endpointLogger->error("Invalid given Query");
            throw new InvalidJsonDataException("adminAudiobook.zip.invalid.query");
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
    #[Route("/api/admin/audiobook/reAdding", name: "adminAudiobookReAdding", methods: ["POST"])]
    #[AuthValidation(checkAuthToken: true, roles: ["Administrator"])]
    #[OA\Post(
        description: "Endpoint is re-adding audiobook by changing files",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                ref: new Model(type: AdminAudiobookReAddingQuery::class),
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
    public function adminAudiobookReAdding(
        Request                        $request,
        RequestServiceInterface        $requestService,
        AuthorizedUserServiceInterface $authorizedUserService,
        LoggerInterface                $endpointLogger,

    ): Response
    {
        $adminAudiobookReAddingQuery = $requestService->getRequestBodyContent($request, AdminAudiobookReAddingQuery::class);

        if ($adminAudiobookReAddingQuery instanceof AdminAudiobookReAddingQuery) {

//            if ( == null) {
//                $endpointLogger->error("Offer dont exist");
//                throw new DataNotFoundException(["investmentPaymentDuePayments.investmentPaymentDueOffer.not.exist"]);
//            }

            return ResponseTool::getResponse();
        } else {
            $endpointLogger->error("Invalid given Query");
            throw new InvalidJsonDataException("adminAudiobook.reAdding.invalid.query");
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
    #[Route("/api/admin/audiobooks", name: "adminAudiobooks", methods: ["POST"])]
    #[AuthValidation(checkAuthToken: true, roles: ["Administrator"])]
    #[OA\Post(
        description: "Endpoint is returning list of all audiobooks",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                ref: new Model(type: AdminAudiobooksQuery::class),
                type: "object"
            ),
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Success",
                content: new Model(type: AdminAudiobooksSuccessModel::class)
            )
        ]
    )]
    public function adminAudiobooks(
        Request                        $request,
        RequestServiceInterface        $requestService,
        AuthorizedUserServiceInterface $authorizedUserService,
        LoggerInterface                $endpointLogger,
        AudiobookRepository            $audiobookRepository
    ): Response
    {
        $adminAudiobooksQuery = $requestService->getRequestBodyContent($request, AdminAudiobooksQuery::class);

        if ($adminAudiobooksQuery instanceof AdminAudiobooksQuery) {

            $successModel = new AdminAudiobooksSuccessModel();

            $audiobooks = $audiobookRepository->getAudiobooksByPage($adminAudiobooksQuery->getPage(), $adminAudiobooksQuery->getLimit());

            foreach ($audiobooks as $audiobook) {
                $audiobookModel = new AdminCategoryAudiobookModel(
                    $audiobook->getId(),
                    $audiobook->getTitle(),
                    $audiobook->getAuthor(),
                    $audiobook->getYear(),
                    $audiobook->getDuration(),
                    $audiobook->getSize(),
                    $audiobook->getParts(),
                    $audiobook->getAge(),
                    $audiobook->getActive()
                );

                $successModel->setPage($adminAudiobooksQuery->getPage());
                $successModel->setLimit($adminAudiobooksQuery->getLimit());

                $allAudiobooks = $audiobookRepository->findAll();

                $successModel->setMaxPage($allAudiobooks / $adminAudiobooksQuery->getLimit());
                $successModel->addAudiobook($audiobookModel);
            }

            return ResponseTool::getResponse($successModel);
        } else {
            $endpointLogger->error("Invalid given Query");
            throw new InvalidJsonDataException("adminAudiobooks.invalid.query");
        }
    }

    /**
     * @param Request $request
     * @param RequestServiceInterface $requestService
     * @param AuthorizedUserServiceInterface $authorizedUserService
     * @param LoggerInterface $endpointLogger
     * @return Response
     * @throws InvalidJsonDataException
     * @throws DataNotFoundException
     */
    #[Route("/api/admin/audiobook/active", name: "adminAudiobookActive", methods: ["PATCH"])]
    #[AuthValidation(checkAuthToken: true, roles: ["Administrator"])]
    #[OA\Patch(
        description: "Endpoint is activating given audiobook",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                ref: new Model(type: AdminAudiobookActiveQuery::class),
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
    public function adminAudiobookActive(
        Request                        $request,
        RequestServiceInterface        $requestService,
        AuthorizedUserServiceInterface $authorizedUserService,
        LoggerInterface                $endpointLogger,
        AudiobookRepository            $audiobookRepository
    ): Response
    {
        $adminAudiobookActiveQuery = $requestService->getRequestBodyContent($request, AdminAudiobookActiveQuery::class);

        if ($adminAudiobookActiveQuery instanceof AdminAudiobookActiveQuery) {
            $audiobook = $audiobookRepository->findOneBy([
                "id" => $adminAudiobookActiveQuery->getAudiobookId()
            ]);

            if ($audiobook == null) {
                $endpointLogger->error("Audiobook dont exist");
                throw new DataNotFoundException(["adminAudiobook.active.audiobook.not.exist"]);
            }

            $audiobook->setActive($adminAudiobookActiveQuery->isActive());
            $audiobookRepository->add($audiobook);

            return ResponseTool::getResponse();
        } else {
            $endpointLogger->error("Invalid given Query");
            throw new InvalidJsonDataException("adminAudiobook.active.invalid.query");
        }
    }


}