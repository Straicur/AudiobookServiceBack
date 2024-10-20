<?php

declare(strict_types=1);

namespace App\Controller;

use App\Annotation\AuthValidation;
use App\Enums\Cache\CacheValidTime;
use App\Enums\Cache\UserCacheKeys;
use App\Enums\Cache\UserStockCacheTags;
use App\Enums\UserRolesNames;
use App\Exception\DataNotFoundException;
use App\Exception\InvalidJsonDataException;
use App\Model\Common\AudiobookCoverModel;
use App\Model\Common\AudiobookCoversSuccessModel;
use App\Model\Common\AudiobookPartSuccessModel;
use App\Model\Error\DataNotFoundModel;
use App\Model\Error\JsonDataInvalidModel;
use App\Model\Error\NotAuthorizeModel;
use App\Model\Error\PermissionNotGrantedModel;
use App\Query\Common\AudiobookCoversQuery;
use App\Query\Common\AudiobookPartQuery;
use App\Repository\AudiobookRepository;
use App\Service\RequestServiceInterface;
use App\Service\TranslateServiceInterface;
use App\Tool\ResponseTool;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Attributes as OA;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;
use Throwable;

#[OA\Response(
    response   : 400,
    description: 'JSON Data Invalid',
    content    : new Model(type: JsonDataInvalidModel::class)
)]
#[OA\Response(
    response   : 404,
    description: 'Data not found',
    content    : new Model(type: DataNotFoundModel::class)
)]
#[OA\Response(
    response   : 401,
    description: 'User not authorized',
    content    : new Model(type: NotAuthorizeModel::class)
)]
#[OA\Response(
    response   : 403,
    description: 'User have no permission',
    content    : new Model(type: PermissionNotGrantedModel::class)
)]
#[OA\Tag(name: 'Audiobook')]
#[Route('/api')]
class AudiobookController extends AbstractController
{
    #[Route('/audiobook/part', name: 'audiobookPart', methods: ['POST'])]
    #[AuthValidation(checkAuthToken: true, roles: [UserRolesNames::ADMINISTRATOR, UserRolesNames::USER, UserRolesNames::RECRUITER])]
    #[OA\Post(
        description: 'Endpoint is returning specific part of audiobook',
        requestBody: new OA\RequestBody(
            required: true,
            content : new OA\JsonContent(
                ref : new Model(type: AudiobookPartQuery::class),
                type: 'object',
            ),
        ),
        responses  : [
            new OA\Response(
                response   : 200,
                description: 'Success',
                content    : new Model(type: AudiobookPartSuccessModel::class),
            ),
        ]
    )]
    public function audiobookPart(
        Request $request,
        RequestServiceInterface $requestService,
        LoggerInterface $endpointLogger,
        AudiobookRepository $audiobookRepository,
        TranslateServiceInterface $translateService,
        TagAwareCacheInterface $stockCache,
    ): Response {
        $audiobookPartQuery = $requestService->getRequestBodyContent($request, AudiobookPartQuery::class);

        if ($audiobookPartQuery instanceof AudiobookPartQuery) {
            $audiobook = $audiobookRepository->find($audiobookPartQuery->getAudiobookId());

            if ($audiobook === null) {
                $endpointLogger->error('Audiobook dont exist');
                $translateService->setPreferredLanguage($request);
                throw new DataNotFoundException([$translateService->getTranslation('AudiobookDontExists')]);
            }

            $dir = $stockCache->get(UserCacheKeys::USER_AUDIOBOOK_PART->value . $audiobook->getId() . '_' . $audiobookPartQuery->getPart(), function (ItemInterface $item) use ($audiobook, $audiobookPartQuery) {
                $item->expiresAfter(CacheValidTime::HOUR->value);
                $item->tag(UserStockCacheTags::USER_AUDIOBOOK_PART->value);
                $allParts = [];

                try {
                    $handle = opendir($audiobook->getFileName());
                } catch (Throwable) {
                    $handle = false;
                }

                if ($handle) {
                    while (false !== ($entry = readdir($handle))) {
                        if ($entry !== '.' && $entry !== '..') {
                            $file_parts = pathinfo($entry);

                            if ($file_parts['extension'] === 'mp3') {
                                $allParts[] = $file_parts['basename'];
                            }
                        }
                    }
                }

                $dir = "";

                sort($allParts);

                foreach ($allParts as $x => $val) {
                    if ($x === $audiobookPartQuery->getPart()) {
                        $dir = $val;
                        break;
                    }
                }
                return $dir;
            });

            if ($dir === "") {
                return ResponseTool::getResponse(new AudiobookPartSuccessModel(''));
            }

            $partDir = '/files/' . pathinfo($audiobook->getFileName())['filename'] . '/' . $dir;

            return ResponseTool::getResponse(new AudiobookPartSuccessModel($partDir));
        }

        $endpointLogger->error('Invalid given Query');
        $translateService->setPreferredLanguage($request);
        throw new InvalidJsonDataException($translateService);
    }

    #[Route('/audiobook/covers', name: 'audiobookCovers', methods: ['POST'])]
    #[AuthValidation(checkAuthToken: true, roles: [UserRolesNames::ADMINISTRATOR, UserRolesNames::USER, UserRolesNames::RECRUITER])]
    #[OA\Post(
        description: 'Endpoint is returning covers paths for given audiobooks',
        requestBody: new OA\RequestBody(
            required: true,
            content : new OA\JsonContent(
                ref : new Model(type: AudiobookCoversQuery::class),
                type: 'object',
            ),
        ),
        responses  : [
            new OA\Response(
                response   : 200,
                description: 'Success',
                content    : new Model(type: AudiobookCoversSuccessModel::class),
            ),
        ]
    )]
    public function audiobookCovers(
        Request $request,
        RequestServiceInterface $requestService,
        LoggerInterface $endpointLogger,
        AudiobookRepository $audiobookRepository,
        TranslateServiceInterface $translateService,
    ): Response {
        $audiobookCoversQuery = $requestService->getRequestBodyContent($request, AudiobookCoversQuery::class);

        if ($audiobookCoversQuery instanceof AudiobookCoversQuery) {
            $successModel = new AudiobookCoversSuccessModel();

            foreach ($audiobookCoversQuery->getAudiobooks() as $audiobookId) {
                $audiobook = null;
                if ($audiobookId) {
                    $audiobook = $audiobookRepository->find($audiobookId);
                }

                $imgUrl = "";
                if ($audiobook) {
                    $handle = opendir($audiobook->getFileName());
                    $img = "";
                    if ($handle) {
                        while (false !== ($entry = readdir($handle))) {
                            if ($entry !== '.' && $entry !== '..') {
                                $file_parts = pathinfo($entry);
                                if ($file_parts['extension'] === 'jpg' || $file_parts['extension'] === 'jpeg' || $file_parts['extension'] === 'png') {
                                    $img = $file_parts['basename'];
                                    break;
                                }
                            }
                        }
                    }
                    if ($img !== "") {
                        $imgUrl = '/files/' . pathinfo($audiobook->getFileName())['filename'] . '/' . $img;
                    }
                    $successModel->addAudiobookCoversModel(new AudiobookCoverModel((string)$audiobook->getId(), $imgUrl));
                }
            }

            return ResponseTool::getResponse($successModel);
        }

        $endpointLogger->error('Invalid given Query');
        $translateService->setPreferredLanguage($request);
        throw new InvalidJsonDataException($translateService);
    }
}
