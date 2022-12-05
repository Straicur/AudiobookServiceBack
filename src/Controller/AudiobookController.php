<?php

namespace App\Controller;

use App\Annotation\AuthValidation;
use App\Entity\Audiobook;
use App\Exception\DataNotFoundException;
use App\Exception\InvalidJsonDataException;
use App\Model\AudiobookCommentGetDetailModel;
use App\Model\AudiobookCommentGetDetailSuccessModel;
use App\Model\AudiobookCommentGetModel;
use App\Model\AudiobookCommentGetSuccessModel;
use App\Model\AudiobookCommentUserModel;
use App\Model\DataNotFoundModel;
use App\Model\JsonDataInvalidModel;
use App\Model\NotAuthorizeModel;
use App\Model\PermissionNotGrantedModel;
use App\Query\AudiobookCommentGetDetailQuery;
use App\Query\AudiobookCommentGetQuery;
use App\Query\AudiobookPartQuery;
use App\Repository\AudiobookRepository;
use App\Repository\AudiobookUserCommentRepository;
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
    #[Route("/api/audiobook/part", name: "audiobookPart", methods: ["POST"])]
    #[AuthValidation(checkAuthToken: true, roles: ["Administrator", "User"])]
    #[OA\Post(
        description: "Endpoint is returning specific part of audiobook",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                ref: new Model(type: AudiobookPartQuery::class),
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
    public function audiobookPart(
        Request                        $request,
        RequestServiceInterface        $requestService,
        AuthorizedUserServiceInterface $authorizedUserService,
        LoggerInterface                $endpointLogger,
        AudiobookRepository            $audiobookRepository
    ): Response
    {
        $audiobookPartQuery = $requestService->getRequestBodyContent($request, AudiobookPartQuery::class);

        if ($audiobookPartQuery instanceof AudiobookPartQuery) {

            $audiobook = $audiobookRepository->findOneBy([
                "id" => $audiobookPartQuery->getAudiobookId()
            ]);

            if ($audiobook == null) {
                $endpointLogger->error("Audiobook dont exist");
                throw new DataNotFoundException(["audiobook.part.audiobook.not.exist"]);
            }

            $allParts = [];

            $handle = opendir($audiobook->getFileName());

            while (false !== ($entry = readdir($handle))) {
                if ($entry != "." && $entry != "..") {

                    $file_parts = pathinfo($entry);

                    if ($file_parts['extension'] == "mp3") {

                        $allParts[] = $file_parts['basename'];

                    }
                }
            }

            $dir = "";

            sort($allParts);

            foreach ($allParts as $x => $val) {
                if ($x === $audiobookPartQuery->getPart()) {
                    $dir = $audiobook->getFileName() . "/" . $val;
                    break;
                }
            }

            if ($dir == "") {
                $endpointLogger->error("Parts dont exist");
                throw new DataNotFoundException(["audiobook.part.parts.not.exist"]);
            }

            return ResponseTool::getBinaryFileResponse($dir);
        } else {
            $endpointLogger->error("Invalid given Query");
            throw new InvalidJsonDataException("audiobook.part.invalid.query");
        }
    }

    /**
     * @param Request $request
     * @param RequestServiceInterface $requestService
     * @param AuthorizedUserServiceInterface $authorizedUserService
     * @param LoggerInterface $endpointLogger
     * @param Audiobook $id
     * @return Response
     * @throws DataNotFoundException
     */
    #[Route("/api/audiobook/cover/{id}", name: "audiobookCover", methods: ["GET"])]
    #[AuthValidation(checkAuthToken: true, roles: ["Administrator", "User"])]
    #[OA\Get(
        description: "Endpoint is returning cover ov given audiobook",
        requestBody: new OA\RequestBody(),
        responses: [
            new OA\Response(
                response: 200,
                description: "Success",
            )
        ]
    )]
    public function audiobookCover(
        Request                        $request,
        RequestServiceInterface        $requestService,
        AuthorizedUserServiceInterface $authorizedUserService,
        LoggerInterface                $endpointLogger,
        Audiobook                      $id
    ): Response
    {
        $img = "";

        $handle = opendir($id->getFileName());

        while (false !== ($entry = readdir($handle))) {

            if ($entry != "." && $entry != "..") {

                $file_parts = pathinfo($entry);

                if ($file_parts['extension'] == "jpg" || $file_parts['extension'] == "jpeg" || $file_parts['extension'] == "png") {

                    $img = $file_parts["basename"];

                    break;
                }
            }
        }

        if ($img == "") {
            $endpointLogger->error("Cover dont exist");
            throw new DataNotFoundException(["audiobook.cover.cover.not.exist"]);
        }

        return ResponseTool::getBinaryFileResponse($id->getFileName() . "/" . $img, true);
    }

    /**
     * @param Request $request
     * @param RequestServiceInterface $requestService
     * @param AuthorizedUserServiceInterface $authorizedUserService
     * @param LoggerInterface $endpointLogger
     * @param AudiobookRepository $audiobookRepository
     * @param AudiobookUserCommentRepository $audiobookUserCommentRepository
     * @return Response
     * @throws DataNotFoundException
     * @throws InvalidJsonDataException
     */
    #[Route("/api/audiobook/comment/get", name: "audiobookCommentGet", methods: ["POST"])]
    #[AuthValidation(checkAuthToken: true, roles: ["Administrator", "User"])]
    #[OA\Put(
        description: "Endpoint is returning comments for given audiobook",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                ref: new Model(type: AudiobookCommentGetQuery::class),
                type: "object"
            ),
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Success",
                content: new Model(type: AudiobookCommentGetSuccessModel::class)
            )
        ]
    )]
    public function audiobookCommentGet(
        Request                        $request,
        RequestServiceInterface        $requestService,
        AuthorizedUserServiceInterface $authorizedUserService,
        LoggerInterface                $endpointLogger,
        AudiobookRepository            $audiobookRepository,
        AudiobookUserCommentRepository $audiobookUserCommentRepository
    ): Response
    {
        $audiobookCommentGetQuery = $requestService->getRequestBodyContent($request, AudiobookCommentGetQuery::class);

        if ($audiobookCommentGetQuery instanceof AudiobookCommentGetQuery) {

            $user = $authorizedUserService->getAuthorizedUser();

            $audiobook = $audiobookRepository->getAudiobookByCategoryKeyAndId($audiobookCommentGetQuery->getAudiobookId(), $audiobookCommentGetQuery->getCategoryKey());

            if ($audiobook == null) {
                $endpointLogger->error("Audiobook dont exist");
                throw new DataNotFoundException(["audiobook.comment.get.audiobook.not.exist"]);
            }

            $audiobookParentComments = $audiobookUserCommentRepository->findBy([
                "audiobook" => $audiobook->getId(),
                "deleted" => false,
                "parent" => null
            ]);

            $successModel = new AudiobookCommentGetSuccessModel();

            foreach ($audiobookParentComments as $audiobookParentComment) {

                $childComments = count($audiobookUserCommentRepository->findBy(["parent"=>$audiobookParentComment->getId()]));
                $audiobookParentUser = $audiobookParentComment->getUser();
                $myComment = $audiobookParentUser === $user;

                $userModel = new AudiobookCommentUserModel($audiobookParentUser->getUserInformation()->getEmail(),$audiobookParentUser->getUserInformation()->getFirstname());

                $successModel->addAudiobookCommentGetModel(new AudiobookCommentGetModel(
                    $userModel,$audiobookParentComment->getId(),
                    $audiobookParentComment->getComment(),
                    $audiobookParentComment->getEdited(),
                    $childComments
                    ,$myComment
                ));
            }

            return ResponseTool::getResponse($successModel);

        } else {
            $endpointLogger->error("Invalid given Query");
            throw new InvalidJsonDataException("audiobook.comment.get.invalid.query");
        }
    }

    /**
     * @param Request $request
     * @param RequestServiceInterface $requestService
     * @param AuthorizedUserServiceInterface $authorizedUserService
     * @param LoggerInterface $endpointLogger
     * @param AudiobookUserCommentRepository $audiobookUserCommentRepository
     * @return Response
     * @throws InvalidJsonDataException
     * @throws DataNotFoundException
     */
    #[Route("/api/audiobook/comment/get/detail", name: "audiobookCommentGetDetail", methods: ["POST"])]
    #[AuthValidation(checkAuthToken: true, roles: ["Administrator", "User"])]
    #[OA\Put(
        description: "Endpoint is returning child comments for given comment",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                ref: new Model(type: AudiobookCommentGetDetailQuery::class),
                type: "object"
            ),
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Success",
                content: new Model(type: AudiobookCommentGetDetailSuccessModel::class)
            )
        ]
    )]
    public function audiobookCommentGetDetail(
        Request                        $request,
        RequestServiceInterface        $requestService,
        AuthorizedUserServiceInterface $authorizedUserService,
        LoggerInterface                $endpointLogger,
        AudiobookUserCommentRepository $audiobookUserCommentRepository
    ): Response
    {
        $audiobookCommentGetDetailQuery = $requestService->getRequestBodyContent($request, AudiobookCommentGetDetailQuery::class);

        if ($audiobookCommentGetDetailQuery instanceof AudiobookCommentGetDetailQuery) {

            $user = $authorizedUserService->getAuthorizedUser();

            $audiobookComment = $audiobookUserCommentRepository->findOneBy([
                "id" => $audiobookCommentGetDetailQuery->getAudiobookCommentId(),
            ]);

            if ($audiobookComment == null) {
                $endpointLogger->error("Audiobook Comment dont exist");
                throw new DataNotFoundException(["userAudiobook.comment.get.audiobook.comment.not.exist"]);
            }

            $audiobookCommentKids = $audiobookUserCommentRepository->getParentCommentKids($audiobookComment);

            $successModel = new AudiobookCommentGetDetailSuccessModel();

            foreach ($audiobookCommentKids as $audiobookCommentKid) {

                $audiobookParentUser = $audiobookCommentKid->getUser();
                $myComment = $audiobookParentUser === $user;

                $userModel = new AudiobookCommentUserModel($audiobookParentUser->getUserInformation()->getEmail(),$audiobookParentUser->getUserInformation()->getFirstname());

                $successModel->addAudiobookCommentGetDetailModel(new AudiobookCommentGetDetailModel(
                    $userModel,
                    $audiobookCommentKid->getId(),
                    $audiobookCommentKid->getComment(),
                    $audiobookCommentKid->getEdited(),
                    $myComment
                ));
            }

            return ResponseTool::getResponse($successModel);

        } else {
            $endpointLogger->error("Invalid given Query");
            throw new InvalidJsonDataException("audiobook.comment.get.details.invalid.query");
        }
    }
}