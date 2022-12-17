<?php

namespace App\Controller;

use App\Annotation\AuthValidation;
use App\Exception\DataNotFoundException;
use App\Exception\InvalidJsonDataException;
use App\Model\DataNotFoundModel;
use App\Model\JsonDataInvalidModel;
use App\Model\NotAuthorizeModel;
use App\Model\PermissionNotGrantedModel;
use App\Repository\RoleRepository;
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
 * AdminUserController
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
#[OA\Tag(name: "AdminStatistics")]
class AdminStatisticsController extends AbstractController
{
//    /**
//     * @param Request $request
//     * @param RequestServiceInterface $requestService
//     * @param AuthorizedUserServiceInterface $authorizedUserService
//     * @param LoggerInterface $endpointLogger
//     * @param UserRepository $userRepository
//     * @param RoleRepository $roleRepository
//     * @return Response
//     * @throws DataNotFoundException
//     * @throws InvalidJsonDataException
//     */
//    #[Route("/api/admin/statistic/main", name: "adminStatisticMain", methods: ["POST"])]
//    #[AuthValidation(checkAuthToken: true, roles: ["Administrator"])]
//    #[OA\Patch(
//        description: "Endpoint is ",
//        requestBody: new OA\RequestBody(
//            required: true,
//            content: new OA\JsonContent(
////                ref: new Model(type: AdminUserRoleAddQuery::class),
//                type: "object"
//            ),
//        ),
//        responses: [
//            new OA\Response(
//                response: 200,
//                description: "Success",
////                content: new Model(type: UserAudiobookRatingGetSuccessModel::class)
//            )
//        ]
//    )]
//    public function adminUserRoleAdd(
//        Request                        $request,
//        RequestServiceInterface        $requestService,
//        AuthorizedUserServiceInterface $authorizedUserService,
//        LoggerInterface                $endpointLogger,
//        UserRepository                 $userRepository,
//        RoleRepository                 $roleRepository
//    ): Response
//    {
//        return ResponseTool::getResponse();
//    }
    //todo
    // endp do pobierania ilości(użytkowników w systemi,kategorii oraz audiobooków)
    // pobierania top 3 najlepiej ocenianych
    // pobierania ilości zarejestrowanych w ostatnim tygodniu nowych użytkowników oraz logowań
    // pobierania ilości dodanych w ostatnim tygoniu powiadomień
}