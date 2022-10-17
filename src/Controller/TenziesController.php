<?php

namespace App\Controller;

use App\Annotation\AuthValidation;
use App\Entity\TenzieResult;
use App\Exception\DataNotFoundException;
use App\Exception\InvalidJsonDataException;
use App\Model\AuthorizationSuccessModel;
use App\Model\DataNotFoundModel;
use App\Model\JsonDataInvalidModel;
use App\Model\TenzieAllModel;
use App\Model\TenzieAllSuccessModel;
use App\Query\TenzieAddQuery;
use App\Repository\AuthenticationTokenRepository;
use App\Repository\TenzieResultRepository;
use App\Repository\UserRepository;
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
 * TenziesController
 *
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
#[OA\Tag(name: "Tenzies")]
class TenziesController extends AbstractController
{
    /**
     * @param Request $request
     * @param RequestServiceInterface $requestServiceInterface
     * @param AuthorizedUserServiceInterface $authorizedUserService
     * @param LoggerInterface $endpointLogger
     * @param TenzieResultRepository $tenzieResultRepository
     * @return Response
     * @throws DataNotFoundException
     * @throws InvalidJsonDataException
     */
    #[Route("/api/tenzie/add", name: "apiTenzieAdd", methods: ["PUT"])]
    #[AuthValidation(checkAuthToken: true, roles: ["User"])]
    #[OA\Post(
        description: "Method used to add tenzie game result",
        security: [],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                ref: new Model(type: TenzieAddQuery::class),
                type: "object"
            ),
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Success",
                content: new Model(type: AuthorizationSuccessModel::class)
            ),
        ]
    )]
    public function tenzieAdd(
        Request                        $request,
        RequestServiceInterface        $requestServiceInterface,
        AuthorizedUserServiceInterface $authorizedUserService,
        LoggerInterface                $endpointLogger,
        TenzieResultRepository         $tenzieResultRepository,
        UserRepository         $userRepository,
        AuthenticationTokenRepository $authenticationTokenRepository
    ): Response
    {
        $tenzieAddQuery = $requestServiceInterface->getRequestBodyContent($request, TenzieAddQuery::class);

        if ($tenzieAddQuery instanceof TenzieAddQuery) {

            $user = $authorizedUserService->getAuthorizedUser();

            $duplicateTenzie = $tenzieResultRepository->findOneBy([
                "title" => $tenzieAddQuery->getTitle()
            ]);

            if ($duplicateTenzie != null) {
                $endpointLogger->error("Tenzie result title already exists");
                throw new DataNotFoundException(["tenzie.add.invalid.title"]);
            }

            $tenzieResults = $tenzieResultRepository->getActiveUserTenzieResults($user);

            print_r("Tenzies".count($tenzieResults));
            print_r("Tokeny".count($authenticationTokenRepository->getActiveUserTenzieResults($user)));
            print_r("Tokeny".count($authenticationTokenRepository->findBy([
                    "user"=>$user
                ])));
            $newTenzieResult = new TenzieResult(
                $user,
                $tenzieAddQuery->getTitle(),
                $tenzieAddQuery->getLevel(),
                $tenzieAddQuery->getTime()
            );

            $tenzieResultRepository->add($newTenzieResult);

            return ResponseTool::getResponse();

        } else {
            $endpointLogger->error("Invalid given Query");
            throw new InvalidJsonDataException("tenzie.add.invalid.query");
        }
    }

    /**
     * @param Request $request
     * @param RequestServiceInterface $requestServiceInterface
     * @param AuthorizedUserServiceInterface $authorizedUserService
     * @param LoggerInterface $endpointLogger
     * @param TenzieResultRepository $tenzieResultRepository
     * @return Response
     */
    #[Route("/api/tenzie/all", name: "apiTenzieAll", methods: ["POST"])]
    #[AuthValidation(checkAuthToken: true, roles: ["User"])]
    #[OA\Post(
        description: "Method return all user results",
        security: [],
        requestBody: new OA\RequestBody(),
        responses: [
            new OA\Response(
                response: 200,
                description: "Success",
                content: new Model(type: TenzieAllSuccessModel::class)
            ),
        ]
    )]
    public function tenzieAll(
        Request                        $request,
        RequestServiceInterface        $requestServiceInterface,
        AuthorizedUserServiceInterface $authorizedUserService,
        LoggerInterface                $endpointLogger,
        TenzieResultRepository         $tenzieResultRepository,
        AuthenticationTokenRepository $authenticationTokenRepository
    ): Response
    {
        $user = $authorizedUserService->getAuthorizedUser();

        $tenzieResults = $tenzieResultRepository->getActiveUserTenzieResults($user);

        $successModel = new TenzieAllSuccessModel();

        foreach ($tenzieResults as $tenzieResult) {
            $successModel->addTenzieAllModel(new TenzieAllModel(
                $tenzieResult->getLevel(),
                $tenzieResult->getTitle(),
                $tenzieResult->getTime(),
                $tenzieResult->getDateAdd()
            ));
        }

        return ResponseTool::getResponse($successModel);
    }
//
//    /**
//     * @throws InvalidJsonDataException
//     * @throws DataNotFoundException
//     *
//     */
//    #[Route("/api/tenzie/{id}", name: "apiTenzieDelete", methods: ["DELETE"])]
//    #[OA\Delete(
//        description: "Method delete given result",
//        security: [],
//        requestBody: new OA\RequestBody(
//            required: true,
//            content: new OA\JsonContent(
//                ref: new Model(type: AuthorizeQuery::class),
//                type: "object"
//            ),
//        ),
//        responses: [
//            new OA\Response(
//                response: 200,
//                description: "Success",
//                content: new Model(type: AuthorizationSuccessModel::class)
//            ),
//        ]
//    )]
//    public function tenzieDelete(
//        Request                       $request,
//        RequestServiceInterface       $requestServiceInterface,
//        LoggerInterface               $usersLogger,
//        UserInformationRepository     $userInformationRepository,
//        UserPasswordRepository        $userPasswordRepository,
//        AuthenticationTokenRepository $authenticationTokenRepository,
//        LoggerInterface               $endpointLogger,
//        TenzieResult                  $id
//    ): Response
//    {
//
//    }
//
//    /**
//     * @throws InvalidJsonDataException
//     * @throws DataNotFoundException
//     *
//     */
//    #[Route("/api/tenzie/best", name: "apiTenzieBest", methods: ["POST"])]
//    #[OA\Post(
//        description: "Method returns best results in system",
//        security: [],
//        requestBody: new OA\RequestBody(
//            required: true,
//            content: new OA\JsonContent(
//                ref: new Model(type: AuthorizeQuery::class),
//                type: "object"
//            ),
//        ),
//        responses: [
//            new OA\Response(
//                response: 200,
//                description: "Success",
//                content: new Model(type: AuthorizationSuccessModel::class)
//            ),
//        ]
//    )]
//    public function tenzieBest(
//        Request                       $request,
//        RequestServiceInterface       $requestServiceInterface,
//        LoggerInterface               $usersLogger,
//        UserInformationRepository     $userInformationRepository,
//        UserPasswordRepository        $userPasswordRepository,
//        AuthenticationTokenRepository $authenticationTokenRepository,
//        LoggerInterface               $endpointLogger,
//    ): Response
//    {
//
//    }
}