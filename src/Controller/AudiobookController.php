<?php

declare(strict_types = 1);

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
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;
use Throwable;

use function in_array;

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
class AudiobookController extends AbstractController
{
    public function __construct(private readonly RequestServiceInterface $requestService, private readonly LoggerInterface $endpointLogger, private readonly AudiobookRepository $audiobookRepository, private readonly TranslateServiceInterface $translateService, private readonly TagAwareCacheInterface $stockCache) {}

    #[Route('/api/audiobook/part', name: 'audiobookPart', methods: ['POST'])]
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
    ): Response {
        $audiobookPartQuery = $this->requestService->getRequestBodyContent($request, AudiobookPartQuery::class);

        if ($audiobookPartQuery instanceof AudiobookPartQuery) {
            $audiobook = $this->audiobookRepository->find($audiobookPartQuery->getAudiobookId());

            if (null === $audiobook) {
                $this->endpointLogger->error('Audiobook dont exist');
                $this->translateService->setPreferredLanguage($request);
                throw new DataNotFoundException([$this->translateService->getTranslation('AudiobookDontExists')]);
            }

            $dir = $this->stockCache->get(UserCacheKeys::USER_AUDIOBOOK_PART->value . $audiobook->getId() . '_' . $audiobookPartQuery->getPart(), function (ItemInterface $item) use ($audiobook, $audiobookPartQuery) {
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
                        if ('.' !== $entry && '..' !== $entry) {
                            $file_parts = pathinfo($entry);

                            if ('mp3' === $file_parts['extension']) {
                                $allParts[] = $file_parts['basename'];
                            }
                        }
                    }
                }

                $dir = '';

                sort($allParts);

                foreach ($allParts as $x => $val) {
                    if ($audiobookPartQuery->getPart() === $x) {
                        $dir = $val;
                        break;
                    }
                }

                return $dir;
            });

            if ('' === $dir) {
                return ResponseTool::getResponse(new AudiobookPartSuccessModel(''));
            }

            $partDir = '/files/' . pathinfo((string) $audiobook->getFileName())['filename'] . '/' . $dir;

            return ResponseTool::getResponse(new AudiobookPartSuccessModel($partDir));
        }

        $this->endpointLogger->error('Invalid given Query');
        $this->translateService->setPreferredLanguage($request);
        throw new InvalidJsonDataException($this->translateService);
    }

    #[Route('/api/audiobook/covers', name: 'audiobookCovers', methods: ['POST'])]
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
    ): Response {
        $audiobookCoversQuery = $this->requestService->getRequestBodyContent($request, AudiobookCoversQuery::class);

        if ($audiobookCoversQuery instanceof AudiobookCoversQuery) {
            $successModel = new AudiobookCoversSuccessModel();

            foreach ($audiobookCoversQuery->getAudiobooks() as $audiobookId) {
                $audiobook = null;
                if ($audiobookId) {
                    $audiobook = $this->audiobookRepository->find($audiobookId);
                }

                $imgUrl = '';
                if ($audiobook) {
                    $handle = opendir($audiobook->getFileName());
                    $img = '';
                    if ($handle) {
                        while (false !== ($entry = readdir($handle))) {
                            if ('.' !== $entry && '..' !== $entry) {
                                $file_parts = pathinfo($entry);
                                if (in_array($file_parts['extension'], ['jpg', 'jpeg', 'png'], true)) {
                                    $img = $file_parts['basename'];
                                    break;
                                }
                            }
                        }
                    }

                    if ('' !== $img) {
                        $imgUrl = '/files/' . pathinfo((string) $audiobook->getFileName())['filename'] . '/' . $img;
                    }

                    $successModel->addAudiobookCoversModel(new AudiobookCoverModel((string) $audiobook->getId(), $imgUrl));
                }
            }

            return ResponseTool::getResponse($successModel);
        }

        $this->endpointLogger->error('Invalid given Query');
        $this->translateService->setPreferredLanguage($request);
        throw new InvalidJsonDataException($this->translateService);
    }
}
