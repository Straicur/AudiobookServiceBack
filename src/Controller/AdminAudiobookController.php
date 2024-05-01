<?php

declare(strict_types=1);

namespace App\Controller;

use App\Annotation\AuthValidation;
use App\Builder\NotificationBuilder;
use App\Entity\Audiobook;
use App\Enums\AudiobookAgeRange;
use App\Enums\CacheKeys;
use App\Enums\CacheValidTime;
use App\Enums\NotificationType;
use App\Enums\NotificationUserType;
use App\Enums\StockCacheTags;
use App\Enums\UserAudiobookActivationType;
use App\Enums\UserRolesNames;
use App\Exception\AudiobookConfigServiceException;
use App\Exception\DataNotFoundException;
use App\Exception\InvalidJsonDataException;
use App\Exception\NotificationException;
use App\Model\Admin\AdminAudiobookDetailsSuccessModel;
use App\Model\Admin\AdminAudiobooksSuccessModel;
use App\Model\Admin\AdminCategoryAudiobookModel;
use App\Model\Common\AudiobookCommentsSuccessModel;
use App\Model\Common\AudiobookDetailCategoryModel;
use App\Model\Error\DataNotFoundModel;
use App\Model\Error\JsonDataInvalidModel;
use App\Model\Error\NotAuthorizeModel;
use App\Model\Error\PermissionNotGrantedModel;
use App\Query\Admin\AdminAudiobookActiveQuery;
use App\Query\Admin\AdminAudiobookAddQuery;
use App\Query\Admin\AdminAudiobookChangeCoverQuery;
use App\Query\Admin\AdminAudiobookCommentDeleteQuery;
use App\Query\Admin\AdminAudiobookCommentGetQuery;
use App\Query\Admin\AdminAudiobookDeleteQuery;
use App\Query\Admin\AdminAudiobookDetailsQuery;
use App\Query\Admin\AdminAudiobookEditQuery;
use App\Query\Admin\AdminAudiobookReAddingQuery;
use App\Query\Admin\AdminAudiobooksQuery;
use App\Query\Admin\AdminAudiobookZipQuery;
use App\Repository\AudiobookCategoryRepository;
use App\Repository\AudiobookInfoRepository;
use App\Repository\AudiobookRatingRepository;
use App\Repository\AudiobookRepository;
use App\Repository\AudiobookUserCommentLikeRepository;
use App\Repository\AudiobookUserCommentRepository;
use App\Repository\NotificationRepository;
use App\Repository\RoleRepository;
use App\Repository\UserRepository;
use App\Service\AudiobookService;
use App\Service\AuthorizedUserServiceInterface;
use App\Service\RequestServiceInterface;
use App\Service\TranslateService;
use App\Tool\ResponseTool;
use App\ValueGenerator\BuildAudiobookCommentTreeGenerator;
use DateTime;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Attributes as OA;
use Psr\Cache\InvalidArgumentException;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Uid\Uuid;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;
use ZipArchive;

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
#[OA\Tag(name: 'AdminAudiobook')]
class AdminAudiobookController extends AbstractController
{
    /**
     * @param Request $request
     * @param RequestServiceInterface $requestService
     * @param AuthorizedUserServiceInterface $authorizedUserService
     * @param LoggerInterface $endpointLogger
     * @param AudiobookRepository $audiobookRepository
     * @param AudiobookCategoryRepository $audiobookCategoryRepository
     * @param AudiobookRatingRepository $audiobookRatingRepository
     * @param TranslateService $translateService
     * @param TagAwareCacheInterface $stockCache
     * @return Response
     * @throws DataNotFoundException
     * @throws InvalidJsonDataException
     * @throws InvalidArgumentException
     */
    #[Route('/api/admin/audiobook/details', name: 'adminAudiobookDetails', methods: ['POST'])]
    #[AuthValidation(checkAuthToken: true, roles: ['Administrator'])]
    #[OA\Post(
        description: 'Endpoint is getting details of audiobook',
        requestBody: new OA\RequestBody(
            required: true,
            content : new OA\JsonContent(
                ref : new Model(type: AdminAudiobookDetailsQuery::class),
                type: 'object',
            ),
        ),
        responses  : [
            new OA\Response(
                response   : 200,
                description: 'Success',
                content    : new Model(type: AdminAudiobookDetailsSuccessModel::class),
            ),
        ]
    )]
    public function adminAudiobookDetails(
        Request                        $request,
        RequestServiceInterface        $requestService,
        AuthorizedUserServiceInterface $authorizedUserService,
        LoggerInterface                $endpointLogger,
        AudiobookRepository            $audiobookRepository,
        AudiobookCategoryRepository    $audiobookCategoryRepository,
        AudiobookRatingRepository      $audiobookRatingRepository,
        TranslateService               $translateService,
        TagAwareCacheInterface         $stockCache,
    ): Response {
        $adminAudiobookDetailsQuery = $requestService->getRequestBodyContent($request, AdminAudiobookDetailsQuery::class);

        if ($adminAudiobookDetailsQuery instanceof AdminAudiobookDetailsQuery) {

            $audiobook = $audiobookRepository->findOneBy([
                'id' => $adminAudiobookDetailsQuery->getAudiobookId(),
            ]);

            if ($audiobook === null) {
                $endpointLogger->error('Audiobook dont exist');
                $translateService->setPreferredLanguage($request);
                throw new DataNotFoundException([$translateService->getTranslation('AudiobookDontExists')]);
            }

            $successModel = $stockCache->get(CacheKeys::ADMIN_AUDIOBOOK->value . $audiobook->getId(), function (ItemInterface $item) use ($audiobook, $audiobookRatingRepository, $audiobookCategoryRepository, $audiobookRepository) {
                $item->expiresAfter(CacheValidTime::HALF_A_DAY->value);
                $item->tag(StockCacheTags::ADMIN_AUDIOBOOK->value);

                $categories = $audiobookCategoryRepository->getAudiobookCategories($audiobook);

                $audiobookCategories = [];

                foreach ($categories as $category) {
                    $audiobookCategories[] = new AudiobookDetailCategoryModel(
                        (string)$category->getId(),
                        $category->getName(),
                        $category->getActive(),
                        $category->getCategoryKey(),
                    );
                }

                if ($audiobook->getImgFileChangeDate() == null || $audiobook->getImgFileChangeDate() < new DateTime()) {
                    try {
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
                            $audiobook->setImgFile('/files/' . pathinfo($audiobook->getFileName())['filename'] . '/' . $img);
                            $audiobook->setImgFileChangeDate();
                            $audiobookRepository->add($audiobook);
                        }
                    } catch (\Throwable) {
                        $audiobook->setImgFile(null);
                        $audiobook->setImgFileChangeDate();
                        $audiobookRepository->add($audiobook);
                    }
                }

                $successModel = new AdminAudiobookDetailsSuccessModel(
                    (string)$audiobook->getId(),
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
                    $audiobook->getAvgRating(),
                    $audiobookCategories,
                    count($audiobookRatingRepository->findBy([
                        'audiobook' => $audiobook->getId(),
                    ])),
                    $audiobook->getImgFile(),
                );

                if ($audiobook->getEncoded() !== null) {
                    $successModel->setEncoded($audiobook->getEncoded());
                }

                return $successModel;
            });

            return ResponseTool::getResponse($successModel);
        }

        $endpointLogger->error('Invalid given Query');
        $translateService->setPreferredLanguage($request);
        throw new InvalidJsonDataException($translateService);
    }

    /**
     * @param Request $request
     * @param RequestServiceInterface $requestService
     * @param AuthorizedUserServiceInterface $authorizedUserService
     * @param LoggerInterface $usersLogger
     * @param AudiobookService $audiobookService
     * @param AudiobookCategoryRepository $audiobookCategoryRepository
     * @param AudiobookRepository $audiobookRepository
     * @param AudiobookRatingRepository $audiobookRatingRepository
     * @param TranslateService $translateService
     * @param TagAwareCacheInterface $stockCache
     * @return Response
     * @throws AudiobookConfigServiceException
     * @throws DataNotFoundException
     * @throws InvalidArgumentException
     * @throws InvalidJsonDataException
     */
    #[Route('/api/admin/audiobook/add', name: 'adminAudiobookAdd', methods: ['PUT'])]
    #[AuthValidation(checkAuthToken: true, roles: ['Administrator'])]
    #[OA\Put(
        description: 'Endpoint is adding new audiobook with files',
        requestBody: new OA\RequestBody(
            required: true,
            content : new OA\JsonContent(
                ref : new Model(type: AdminAudiobookAddQuery::class),
                type: 'object',
            ),
        ),
        responses  : [
            new OA\Response(
                response   : 201,
                description: 'Success',
                content    : new Model(type: AdminAudiobookDetailsSuccessModel::class),
            ),
        ]
    )]
    public function adminAudiobookAdd(
        Request                        $request,
        RequestServiceInterface        $requestService,
        AuthorizedUserServiceInterface $authorizedUserService,
        LoggerInterface                $usersLogger,
        AudiobookService               $audiobookService,
        AudiobookCategoryRepository    $audiobookCategoryRepository,
        AudiobookRepository            $audiobookRepository,
        AudiobookRatingRepository      $audiobookRatingRepository,
        TranslateService               $translateService,
        TagAwareCacheInterface         $stockCache,
    ): Response {
        $adminAudiobookAddQuery = $requestService->getRequestBodyContent($request, AdminAudiobookAddQuery::class);

        if ($adminAudiobookAddQuery instanceof AdminAudiobookAddQuery) {
            $audiobookService->configure($adminAudiobookAddQuery);
            $audiobookService->checkAndAddFile();

            if ($audiobookService->lastFile()) {
                $audiobookService->combineFiles();
                $folderDir = $audiobookService->unzip();

                $ID3JsonData = $audiobookService->createAudiobookJsonData($folderDir);

                if (array_key_exists('id3v2', $ID3JsonData['tags'])) {
                    $ID3JsonFileData = $ID3JsonData['tags']['id3v2'];
                } elseif (array_key_exists('id3v1', $ID3JsonData)) {
                    $ID3JsonFileData = $ID3JsonData['tags']['id3v1'];
                } else {
                    $ID3JsonFileData = $ID3JsonData;
                }

                if (array_key_exists('version', $ID3JsonFileData)) {
                    if (count($ID3JsonFileData['version']) > 0) {
                        $version = $ID3JsonFileData['version'][0];
                    } else {
                        $version = $ID3JsonFileData['version'];
                    }
                } else {
                    $version = '1';
                }

                if (array_key_exists('album', $ID3JsonFileData)) {
                    if (count($ID3JsonFileData['album']) > 0) {
                        $album = $ID3JsonFileData['album'][0];
                    } else {
                        $album = $ID3JsonFileData['album'];
                    }
                } else {
                    $album = 'album';
                }

                if (array_key_exists('artist', $ID3JsonFileData)) {
                    if (count($ID3JsonFileData['artist']) > 0) {
                        $author = $ID3JsonFileData['artist'][0];
                    } else {
                        $author = $ID3JsonFileData['artist'];
                    }
                } else {
                    $author = 'author';
                }

                if (array_key_exists('year', $ID3JsonFileData)) {
                    if (count($ID3JsonFileData['year']) > 0) {
                        $year = '01.01.' . $ID3JsonFileData['year'][0];
                    } else {
                        $year = '01.01.' . $ID3JsonFileData['year'];
                    }

                    if (DateTime::createFromFormat('d.m.Y', $year)) {
                        $year = DateTime::createFromFormat('d.m.Y', $year);
                    } else {
                        $year = new DateTime();
                    }
                } else {
                    $year = new DateTime();
                }

                if (array_key_exists('encoded', $ID3JsonFileData)) {
                    if (count($ID3JsonFileData['encoded']) > 0) {
                        $encoded = $ID3JsonFileData['encoded'][0];
                    } else {
                        $encoded = $ID3JsonFileData['encoded'];
                    }
                } else {
                    $encoded = "";
                }

                if (array_key_exists('comment', $ID3JsonFileData)) {
                    if (count($ID3JsonFileData['comment']) > 0) {
                        $description = $ID3JsonFileData['comment'][0];
                    } else {
                        $description = $ID3JsonFileData['comment'];
                    }
                } else {
                    $description = 'desc';
                }

                if (array_key_exists('duration', $ID3JsonData)) {
                    $duration = $ID3JsonData['duration'];
                } else {
                    $duration = 0;
                }

                if (array_key_exists('size', $ID3JsonData)) {
                    $size = $ID3JsonData['size'];
                } else {
                    $size = '1';
                }

                if (array_key_exists('parts', $ID3JsonData)) {
                    $parts = $ID3JsonData['parts'];
                } else {
                    $parts = '1';
                }

                if (array_key_exists('title', $ID3JsonData)) {
                    $title = $ID3JsonData['title'];
                } else {
                    $title = 'title';
                }

                if (array_key_exists('imgFileDir', $ID3JsonData)) {
                    $img = $ID3JsonData['imgFileDir'];
                } else {
                    $img = 'imgFileDir';
                }

                $additionalData = $adminAudiobookAddQuery->getAdditionalData();

                if (array_key_exists('title', $additionalData)) {
                    $title = $additionalData['title'];
                }
                if (array_key_exists('author', $additionalData)) {
                    $author = $additionalData['author'];
                }

                $newAudiobook = new Audiobook($title, $author, $version, $album, $year, $duration, $size, $parts, $description, AudiobookAgeRange::ABOVE18, $folderDir);

                if ($encoded !== "") {
                    $newAudiobook->setEncoded($encoded);
                }

                if ($img !== "") {
                    $newAudiobook->setImgFile($img);
                    $newAudiobook->setImgFileChangeDate();
                }

                $audiobookCategories = [];

                if (array_key_exists('categories', $additionalData)) {

                    $categories = $additionalData['categories'];

                    foreach ($categories as $category) {

                        $audiobookCategory = $audiobookCategoryRepository->findOneBy([
                            'id' => Uuid::fromString($category),
                        ]);

                        if ($audiobookCategory !== null) {
                            $newAudiobook->addCategory($audiobookCategory);

                            $audiobookCategories[] = new AudiobookDetailCategoryModel(
                                (string)$audiobookCategory->getId(),
                                $audiobookCategory->getName(),
                                $audiobookCategory->getActive(),
                                $audiobookCategory->getCategoryKey(),
                            );
                        }
                    }
                }

                $audiobookRepository->add($newAudiobook);

                $successModel = new AdminAudiobookDetailsSuccessModel(
                    (string)$newAudiobook->getId(),
                    $newAudiobook->getTitle(),
                    $newAudiobook->getAuthor(),
                    $newAudiobook->getVersion(),
                    $newAudiobook->getAlbum(),
                    $newAudiobook->getYear(),
                    $newAudiobook->getDuration(),
                    $newAudiobook->getSize(),
                    $newAudiobook->getParts(),
                    $newAudiobook->getDescription(),
                    $newAudiobook->getAge(),
                    $newAudiobook->getActive(),
                    $newAudiobook->getAvgRating(),
                    $audiobookCategories,
                    count($audiobookRatingRepository->findBy([
                        'audiobook' => $newAudiobook->getId(),
                    ])),
                    $newAudiobook->getImgFile(),
                );

                if ($newAudiobook->getEncoded() !== null) {
                    $successModel->setEncoded($newAudiobook->getEncoded());
                }

                $stockCache->invalidateTags([StockCacheTags::ADMIN_AUDIOBOOK->value]);
                $stockCache->invalidateTags([StockCacheTags::ADMIN_CATEGORY_AUDIOBOOKS->value]);
                $stockCache->invalidateTags([StockCacheTags::ADMIN_CATEGORY->value]);

                return ResponseTool::getResponse($successModel, 201);
            }

            return ResponseTool::getResponse(httpCode: 201);
        }

        $usersLogger->error('Invalid given Query');
        $translateService->setPreferredLanguage($request);
        throw new InvalidJsonDataException($translateService);
    }

    /**
     * @param Request $request
     * @param RequestServiceInterface $requestService
     * @param AuthorizedUserServiceInterface $authorizedUserService
     * @param LoggerInterface $endpointLogger
     * @param AudiobookRepository $audiobookRepository
     * @param TranslateService $translateService
     * @param TagAwareCacheInterface $stockCache
     * @return Response
     * @throws DataNotFoundException
     * @throws InvalidJsonDataException
     * @throws InvalidArgumentException
     */
    #[Route('/api/admin/audiobook/edit', name: 'adminAudiobookEdit', methods: ['PATCH'])]
    #[AuthValidation(checkAuthToken: true, roles: ['Administrator'])]
    #[OA\Patch(
        description: 'Endpoint is editing given audiobook data',
        requestBody: new OA\RequestBody(
            required: true,
            content : new OA\JsonContent(
                ref : new Model(type: AdminAudiobookEditQuery::class),
                type: 'object',
            ),
        ),
        responses  : [
            new OA\Response(
                response   : 200,
                description: 'Success',
            ),
        ]
    )]
    public function adminAudiobookEdit(
        Request                        $request,
        RequestServiceInterface        $requestService,
        AuthorizedUserServiceInterface $authorizedUserService,
        LoggerInterface                $endpointLogger,
        AudiobookRepository            $audiobookRepository,
        TranslateService               $translateService,
        TagAwareCacheInterface         $stockCache,
    ): Response {
        $adminAudiobookEditQuery = $requestService->getRequestBodyContent($request, AdminAudiobookEditQuery::class);

        if ($adminAudiobookEditQuery instanceof AdminAudiobookEditQuery) {

            $audiobook = $audiobookRepository->findOneBy([
                'id' => $adminAudiobookEditQuery->getAudiobookId(),
            ]);

            if ($audiobook === null) {
                $endpointLogger->error('Audiobook dont exist');
                $translateService->setPreferredLanguage($request);
                throw new DataNotFoundException([$translateService->getTranslation('AudiobookDontExists')]);
            }

            $audiobook->setTitle($adminAudiobookEditQuery->getTitle());
            $audiobook->setAuthor($adminAudiobookEditQuery->getAuthor());
            $audiobook->setVersion($adminAudiobookEditQuery->getVersion());
            $audiobook->setAlbum($adminAudiobookEditQuery->getAlbum());
            $audiobook->setYear($adminAudiobookEditQuery->getYear());
            $audiobook->setDuration((int)$adminAudiobookEditQuery->getDuration());
            $audiobook->setSize($adminAudiobookEditQuery->getSize());
            $audiobook->setParts($adminAudiobookEditQuery->getParts());
            $audiobook->setDescription($adminAudiobookEditQuery->getDescription());
            $audiobook->setAge($adminAudiobookEditQuery->getAge());
            $audiobook->setEncoded($adminAudiobookEditQuery->getEncoded());

            $audiobookRepository->add($audiobook);

            $stockCache->invalidateTags([StockCacheTags::ADMIN_AUDIOBOOK->value]);
            $stockCache->invalidateTags([StockCacheTags::USER_AUDIOBOOK_DETAIL->value . $audiobook->getId()]);

            return ResponseTool::getResponse();
        }

        $endpointLogger->error('Invalid given Query');
        $translateService->setPreferredLanguage($request);
        throw new InvalidJsonDataException($translateService);
    }

    /**
     * @param Request $request
     * @param RequestServiceInterface $requestService
     * @param AuthorizedUserServiceInterface $authorizedUserService
     * @param LoggerInterface $endpointLogger
     * @param AudiobookService $audiobookService
     * @param AudiobookRepository $audiobookRepository
     * @param TranslateService $translateService
     * @param NotificationRepository $notificationRepository
     * @param TagAwareCacheInterface $stockCache
     * @return Response
     * @throws DataNotFoundException
     * @throws InvalidArgumentException
     * @throws InvalidJsonDataException
     */
    #[Route('/api/admin/audiobook/delete', name: 'adminAudiobookDelete', methods: ['DELETE'])]
    #[AuthValidation(checkAuthToken: true, roles: ['Administrator'])]
    #[OA\Delete(
        description: 'Endpoint is deleting audiobook with his files',
        requestBody: new OA\RequestBody(
            required: true,
            content : new OA\JsonContent(
                ref : new Model(type: AdminAudiobookDeleteQuery::class),
                type: 'object',
            ),
        ),
        responses  : [
            new OA\Response(
                response   : 200,
                description: 'Success',
            ),
        ]
    )]
    public function adminAudiobookDelete(
        Request                        $request,
        RequestServiceInterface        $requestService,
        AuthorizedUserServiceInterface $authorizedUserService,
        LoggerInterface                $endpointLogger,
        AudiobookService               $audiobookService,
        AudiobookRepository            $audiobookRepository,
        TranslateService               $translateService,
        NotificationRepository         $notificationRepository,
        TagAwareCacheInterface         $stockCache,
    ): Response {
        $adminAudiobookDeleteQuery = $requestService->getRequestBodyContent($request, AdminAudiobookDeleteQuery::class);

        if ($adminAudiobookDeleteQuery instanceof AdminAudiobookDeleteQuery) {

            $audiobook = $audiobookRepository->findOneBy([
                'id' => $adminAudiobookDeleteQuery->getAudiobookId(),
            ]);

            if ($audiobook === null) {
                $endpointLogger->error('Audiobook dont exist');
                $translateService->setPreferredLanguage($request);
                throw new DataNotFoundException([$translateService->getTranslation('AudiobookDontExists')]);
            }

            $audId = $audiobook->getId();
            $audiobookRepository->remove($audiobook);

            $notificationRepository->updateDeleteNotificationsByAction($audId);

            $audiobookService->removeFolder($audiobook->getFileName());

            $stockCache->invalidateTags([StockCacheTags::ADMIN_AUDIOBOOK->value]);
            $stockCache->invalidateTags([StockCacheTags::ADMIN_CATEGORY->value]);
            $stockCache->invalidateTags([StockCacheTags::ADMIN_CATEGORY_AUDIOBOOKS->value]);

            return ResponseTool::getResponse();
        }

        $endpointLogger->error('Invalid given Query');
        $translateService->setPreferredLanguage($request);
        throw new InvalidJsonDataException($translateService);
    }

    /**
     * @param Request $request
     * @param RequestServiceInterface $requestService
     * @param AuthorizedUserServiceInterface $authorizedUserService
     * @param LoggerInterface $endpointLogger
     * @param AudiobookRepository $audiobookRepository
     * @param TranslateService $translateService
     * @return Response
     * @throws DataNotFoundException
     * @throws InvalidJsonDataException
     */
    #[Route('/api/admin/audiobook/zip', name: 'adminAudiobookZip', methods: ['POST'])]
    #[AuthValidation(checkAuthToken: true, roles: ['Administrator'])]
    #[OA\Post(
        description: 'Endpoint is returning zip blob',
        requestBody: new OA\RequestBody(
            required: true,
            content : new OA\JsonContent(
                ref : new Model(type: AdminAudiobookZipQuery::class),
                type: 'object',
            ),
        ),
        responses  : [
            new OA\Response(
                response   : 200,
                description: 'Success',
            ),
        ]
    )]
    public function adminAudiobookZip(
        Request                        $request,
        RequestServiceInterface        $requestService,
        AuthorizedUserServiceInterface $authorizedUserService,
        LoggerInterface                $endpointLogger,
        AudiobookRepository            $audiobookRepository,
        TranslateService               $translateService,
    ): Response {
        $adminAudiobookZipQuery = $requestService->getRequestBodyContent($request, AdminAudiobookZipQuery::class);

        if ($adminAudiobookZipQuery instanceof AdminAudiobookZipQuery) {

            $audiobook = $audiobookRepository->findOneBy([
                'id' => $adminAudiobookZipQuery->getAudiobookId(),
            ]);

            if ($audiobook === null) {
                $endpointLogger->error('Audiobook dont exist');
                $translateService->setPreferredLanguage($request);
                throw new DataNotFoundException([$translateService->getTranslation('AudiobookDontExists')]);
            }

            $zipFile = $audiobook->getFileName() . '.zip';

            $zip = new ZipArchive;

            if (file_exists($zipFile)) {
                unlink($zipFile);
            }

            $zip->open($zipFile, ZipArchive::CREATE | ZipArchive::OVERWRITE);

            $dir = opendir($audiobook->getFileName() . '/');

            if (!$dir) {
                $endpointLogger->error('Audiobook Folder dont Exists');
                $translateService->setPreferredLanguage($request);
                throw new DataNotFoundException([$translateService->getTranslation('AudiobookDontExists')]);
            }

            while ($file = readdir($dir)) {
                if (is_file($audiobook->getFileName() . '/' . $file)) {
                    $zip->addFile($audiobook->getFileName() . '/' . $file, basename($audiobook->getFileName() . '/' . $file));
                }
            }

            $zip->close();

            return ResponseTool::getBinaryFileResponse($zipFile, true);
        }

        $endpointLogger->error('Invalid given Query');
        $translateService->setPreferredLanguage($request);
        throw new InvalidJsonDataException($translateService);
    }

    /**
     * @param Request $request
     * @param RequestServiceInterface $requestService
     * @param AuthorizedUserServiceInterface $authorizedUserService
     * @param LoggerInterface $endpointLogger
     * @param AudiobookRepository $audiobookRepository
     * @param AudiobookService $audiobookService
     * @param AudiobookCategoryRepository $audiobookCategoryRepository
     * @param AudiobookRatingRepository $audiobookRatingRepository
     * @param TranslateService $translateService
     * @param NotificationRepository $notificationRepository
     * @param AudiobookUserCommentRepository $commentRepository
     * @param TagAwareCacheInterface $stockCache
     * @return Response
     * @throws AudiobookConfigServiceException
     * @throws DataNotFoundException
     * @throws InvalidArgumentException
     * @throws InvalidJsonDataException
     */
    #[Route('/api/admin/audiobook/reAdding', name: 'adminAudiobookReAdding', methods: ['PATCH'])]
    #[AuthValidation(checkAuthToken: true, roles: ['Administrator'])]
    #[OA\Patch(
        description: 'Endpoint is re-adding audiobook by changing files',
        requestBody: new OA\RequestBody(
            required: true,
            content : new OA\JsonContent(
                ref : new Model(type: AdminAudiobookReAddingQuery::class),
                type: 'object',
            ),
        ),
        responses  : [
            new OA\Response(
                response   : 200,
                description: 'Success',
                content    : new Model(type: AdminAudiobookDetailsSuccessModel::class),
            ),
        ]
    )]
    public function adminAudiobookReAdding(
        Request                        $request,
        RequestServiceInterface        $requestService,
        AuthorizedUserServiceInterface $authorizedUserService,
        LoggerInterface                $endpointLogger,
        AudiobookRepository            $audiobookRepository,
        AudiobookService               $audiobookService,
        AudiobookCategoryRepository    $audiobookCategoryRepository,
        AudiobookRatingRepository      $audiobookRatingRepository,
        TranslateService               $translateService,
        NotificationRepository         $notificationRepository,
        AudiobookUserCommentRepository $commentRepository,
        TagAwareCacheInterface         $stockCache,
    ): Response {
        $adminAudiobookReAddingQuery = $requestService->getRequestBodyContent($request, AdminAudiobookReAddingQuery::class);

        if ($adminAudiobookReAddingQuery instanceof AdminAudiobookReAddingQuery) {

            $audiobook = $audiobookRepository->findOneBy([
                'id' => $adminAudiobookReAddingQuery->getAudiobookId(),
            ]);

            if ($audiobook === null) {
                $endpointLogger->error('Audiobook dont exist');
                $translateService->setPreferredLanguage($request);
                throw new DataNotFoundException([$translateService->getTranslation('AudiobookDontExists')]);
            }

            $audiobookService->configure($adminAudiobookReAddingQuery);

            $audiobookService->checkAndAddFile();

            if ($audiobookService->lastFile()) {

                $audiobookService->combineFiles();
                $folderDir = $audiobookService->unzip($audiobook->getFileName());

                $ID3JsonData = $audiobookService->createAudiobookJsonData($folderDir);

                if (array_key_exists('id3v2', $ID3JsonData['tags'])) {
                    $ID3JsonFileData = $ID3JsonData['tags']['id3v2'];
                } elseif (array_key_exists('id3v1', $ID3JsonData)) {
                    $ID3JsonFileData = $ID3JsonData['tags']['id3v1'];
                } else {
                    $ID3JsonFileData = $ID3JsonData;
                }

                if (array_key_exists('version', $ID3JsonFileData)) {
                    if (count($ID3JsonFileData['version']) > 0) {
                        $version = $ID3JsonFileData['version'][0];
                    } else {
                        $version = $ID3JsonFileData['version'];
                    }
                } else {
                    $version = '1';
                }

                if (array_key_exists('album', $ID3JsonFileData)) {
                    if (count($ID3JsonFileData['album']) > 0) {
                        $album = $ID3JsonFileData['album'][0];
                    } else {
                        $album = $ID3JsonFileData['album'];
                    }
                } else {
                    $album = 'album';
                }

                if (array_key_exists('artist', $ID3JsonFileData)) {
                    if (count($ID3JsonFileData['artist']) > 0) {
                        $author = $ID3JsonFileData['artist'][0];
                    } else {
                        $author = $ID3JsonFileData['artist'];
                    }
                } else {
                    $author = 'author';
                }

                if (array_key_exists('year', $ID3JsonFileData)) {
                    if (count($ID3JsonFileData['year']) > 0) {
                        $year = '01.01.' . $ID3JsonFileData['year'][0];
                    } else {
                        $year = '01.01.' . $ID3JsonFileData['year'];
                    }

                    if (DateTime::createFromFormat('d.m.Y', $year)) {
                        $year = DateTime::createFromFormat('d.m.Y', $year);
                    } else {
                        $year = new DateTime();
                    }
                } else {
                    $year = new DateTime();
                }

                if (array_key_exists('encoded', $ID3JsonFileData)) {
                    if (count($ID3JsonFileData['encoded']) > 0) {
                        $encoded = $ID3JsonFileData['encoded'][0];
                    } else {
                        $encoded = $ID3JsonFileData['encoded'];
                    }
                } else {
                    $encoded = "";
                }

                if (array_key_exists('comment', $ID3JsonFileData)) {
                    if (count($ID3JsonFileData['comment']) > 0) {
                        $description = $ID3JsonFileData['comment'][0];
                    } else {
                        $description = $ID3JsonFileData['comment'];
                    }
                } else {
                    $description = 'desc';
                }

                if (array_key_exists('duration', $ID3JsonData)) {
                    $duration = $ID3JsonData['duration'];
                } else {
                    $duration = '1';
                }

                if (array_key_exists('size', $ID3JsonData)) {
                    $size = $ID3JsonData['size'];
                } else {
                    $size = '1';
                }

                if (array_key_exists('parts', $ID3JsonData)) {
                    $parts = $ID3JsonData['parts'];
                } else {
                    $parts = '1';
                }

                if (array_key_exists('title', $ID3JsonData)) {
                    $title = $ID3JsonData['title'];
                } else {
                    $title = 'title';
                }

                if (array_key_exists('imgFileDir', $ID3JsonData)) {
                    $img = $ID3JsonData['imgFileDir'];
                } else {
                    $img = 'imgFileDir';
                }

                $additionalData = $adminAudiobookReAddingQuery->getAdditionalData();

                if (array_key_exists('title', $additionalData)) {
                    $title = $additionalData['title'];
                }
                if (array_key_exists('author', $additionalData)) {
                    $author = $additionalData['author'];
                }

                $audiobook->setActive(false);
                $audiobook->setTitle($title);
                $audiobook->setAuthor($author);
                $audiobook->setVersion($version);
                $audiobook->setAlbum($album);
                $audiobook->setYear($year);
                $audiobook->setDuration($duration);
                $audiobook->setSize($size);
                $audiobook->setParts($parts);
                $audiobook->setDescription($description);
                $audiobook->setAge(AudiobookAgeRange::ABOVE18);
                $audiobook->setFileName($folderDir);

                if ($encoded !== "") {
                    $audiobook->setEncoded($encoded);
                }

                if ($img !== "") {
                    $audiobook->setImgFile($img);
                    $audiobook->setImgFileChangeDate();
                }

                foreach ($audiobook->getCategories() as $category) {
                    $audiobook->removeCategory($category);
                }

                $audiobookCategories = [];

                if (array_key_exists('categories', $additionalData)) {

                    $categories = [];

                    if (!empty($additionalData['categories'])) {
                        foreach ($additionalData['categories'] as $category) {
                            $categories[] = Uuid::fromString($category)->toBinary();
                        }
                    }
                    foreach ($categories as $category) {

                        $audiobookCategory = $audiobookCategoryRepository->findOneBy([
                            'id' => $category,
                        ]);

                        if ($audiobookCategory !== null) {
                            $audiobook->addCategory($audiobookCategory);

                            $audiobookCategories[] = new AudiobookDetailCategoryModel(
                                (string)$audiobookCategory->getId(),
                                $audiobookCategory->getName(),
                                $audiobookCategory->getActive(),
                                $audiobookCategory->getCategoryKey(),
                            );
                        }
                    }
                }

                $audiobookRepository->add($audiobook);

                $successModel = new AdminAudiobookDetailsSuccessModel(
                    (string)$audiobook->getId(),
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
                    $audiobook->getAvgRating(),
                    $audiobookCategories,
                    count($audiobookRatingRepository->findBy([
                        'audiobook' => $audiobook->getId(),
                    ])),
                    $audiobook->getImgFile(),
                );

                if ($audiobook->getEncoded() !== null) {
                    $successModel->setEncoded($audiobook->getEncoded());
                }

                if ($adminAudiobookReAddingQuery->isDeleteNotifications()) {
                    $notificationRepository->updateDeleteNotificationsByAction($audiobook->getId());
                }

                if ($adminAudiobookReAddingQuery->isDeleteComments()) {
                    foreach ($audiobook->getAudiobookUserComments() as $comment) {
                        $commentRepository->remove($comment);
                    }
                }

                $stockCache->invalidateTags([StockCacheTags::ADMIN_AUDIOBOOK->value]);

                return ResponseTool::getResponse($successModel);
            }

            return ResponseTool::getResponse();
        }

        $endpointLogger->error('Invalid given Query');
        $translateService->setPreferredLanguage($request);
        throw new InvalidJsonDataException($translateService);
    }

    /**
     * @param Request $request
     * @param RequestServiceInterface $requestService
     * @param AuthorizedUserServiceInterface $authorizedUserService
     * @param LoggerInterface $endpointLogger
     * @param AudiobookRepository $audiobookRepository
     * @param TranslateService $translateService
     * @return Response
     * @throws InvalidJsonDataException
     */
    #[Route('/api/admin/audiobooks', name: 'adminAudiobooks', methods: ['POST'])]
    #[AuthValidation(checkAuthToken: true, roles: ['Administrator'])]
    #[OA\Post(
        description: 'Endpoint is returning list of all audiobooks',
        requestBody: new OA\RequestBody(
            required: true,
            content : new OA\JsonContent(
                ref : new Model(type: AdminAudiobooksQuery::class),
                type: 'object',
            ),
        ),
        responses  : [
            new OA\Response(
                response   : 200,
                description: 'Success',
                content    : new Model(type: AdminAudiobooksSuccessModel::class),
            ),
        ]
    )]
    public function adminAudiobooks(
        Request                        $request,
        RequestServiceInterface        $requestService,
        AuthorizedUserServiceInterface $authorizedUserService,
        LoggerInterface                $endpointLogger,
        AudiobookRepository            $audiobookRepository,
        TranslateService               $translateService,
    ): Response {
        $adminAudiobooksQuery = $requestService->getRequestBodyContent($request, AdminAudiobooksQuery::class);

        if ($adminAudiobooksQuery instanceof AdminAudiobooksQuery) {

            $audiobookSearchData = $adminAudiobooksQuery->getSearchData();

            $categories = null;
            $author = null;
            $title = null;
            $album = null;
            $duration = null;
            $parts = null;
            $age = null;
            $order = null;
            $year = null;

            if (array_key_exists('categories', $audiobookSearchData) && !empty($audiobookSearchData['categories'])) {
                foreach ($audiobookSearchData['categories'] as $category) {
                    $categories[] = Uuid::fromString($category)->toBinary();
                }
            }

            if (array_key_exists('author', $audiobookSearchData)) {
                $author = ($audiobookSearchData['author'] && '' !== $audiobookSearchData['author']) ? '%' . $audiobookSearchData['author'] . '%' : null;
            }
            if (array_key_exists('title', $audiobookSearchData)) {
                $title = ($audiobookSearchData['title'] && '' !== $audiobookSearchData['title']) ? '%' . $audiobookSearchData['title'] . '%' : null;
            }
            if (array_key_exists('album', $audiobookSearchData)) {
                $album = ($audiobookSearchData['album'] && '' !== $audiobookSearchData['album']) ? '%' . $audiobookSearchData['album'] . '%' : null;
            }
            if (array_key_exists('duration', $audiobookSearchData)) {
                $duration = $audiobookSearchData['duration'];
            }
            if (array_key_exists('age', $audiobookSearchData)) {
                $age = $audiobookSearchData['age'];
            }
            if (array_key_exists('parts', $audiobookSearchData)) {
                $parts = $audiobookSearchData['parts'];
            }
            if (array_key_exists('order', $audiobookSearchData)) {
                $order = $audiobookSearchData['order'];
            }
            if (array_key_exists('year', $audiobookSearchData) && $audiobookSearchData['year'] !== false) {
                $year = $audiobookSearchData['year'];
            }

            $successModel = new AdminAudiobooksSuccessModel();

            $audiobooks = $audiobookRepository->getAudiobooksByPage($categories, $author, $title, $album, $duration, $age, $year, $parts, $order);

            $minResult = $adminAudiobooksQuery->getPage() * $adminAudiobooksQuery->getLimit();
            $maxResult = $adminAudiobooksQuery->getLimit() + $minResult;

            foreach ($audiobooks as $index => $audiobook) {
                if ($index < $minResult) {
                    continue;
                }

                if ($index < $maxResult) {
                    $audiobookModel = new AdminCategoryAudiobookModel(
                        (string)$audiobook->getId(),
                        $audiobook->getTitle(),
                        $audiobook->getAuthor(),
                        $audiobook->getYear(),
                        $audiobook->getDuration(),
                        $audiobook->getSize(),
                        $audiobook->getParts(),
                        $audiobook->getAvgRating(),
                        $audiobook->getAge(),
                        $audiobook->getActive(),
                    );
                    $successModel->addAudiobook($audiobookModel);
                } else {
                    break;
                }
            }

            $successModel->setPage($adminAudiobooksQuery->getPage());
            $successModel->setLimit($adminAudiobooksQuery->getLimit());
            $successModel->setMaxPage((int)ceil(count($audiobooks) / $adminAudiobooksQuery->getLimit()));

            return ResponseTool::getResponse($successModel);
        }

        $endpointLogger->error('Invalid given Query');
        $translateService->setPreferredLanguage($request);
        throw new InvalidJsonDataException($translateService);
    }

    /**
     * @param Request $request
     * @param RequestServiceInterface $requestService
     * @param AuthorizedUserServiceInterface $authorizedUserService
     * @param LoggerInterface $endpointLogger
     * @param AudiobookRepository $audiobookRepository
     * @param TranslateService $translateService
     * @param NotificationRepository $notificationRepository
     * @param AudiobookInfoRepository $audiobookInfoRepository
     * @param UserRepository $userRepository
     * @param RoleRepository $roleRepository
     * @param TagAwareCacheInterface $stockCache
     * @return Response
     * @throws DataNotFoundException
     * @throws InvalidArgumentException
     * @throws InvalidJsonDataException
     * @throws NotificationException
     */
    #[Route('/api/admin/audiobook/active', name: 'adminAudiobookActive', methods: ['PATCH'])]
    #[AuthValidation(checkAuthToken: true, roles: ['Administrator'])]
    #[OA\Patch(
        description: 'Endpoint is activating given audiobook',
        requestBody: new OA\RequestBody(
            required: true,
            content : new OA\JsonContent(
                ref : new Model(type: AdminAudiobookActiveQuery::class),
                type: 'object',
            ),
        ),
        responses  : [
            new OA\Response(
                response   : 200,
                description: 'Success',
            ),
        ]
    )]
    public function adminAudiobookActive(
        Request                        $request,
        RequestServiceInterface        $requestService,
        AuthorizedUserServiceInterface $authorizedUserService,
        LoggerInterface                $endpointLogger,
        AudiobookRepository            $audiobookRepository,
        TranslateService               $translateService,
        NotificationRepository         $notificationRepository,
        AudiobookInfoRepository        $audiobookInfoRepository,
        UserRepository                 $userRepository,
        RoleRepository                 $roleRepository,
        TagAwareCacheInterface         $stockCache,
    ): Response {
        $adminAudiobookActiveQuery = $requestService->getRequestBodyContent($request, AdminAudiobookActiveQuery::class);

        if ($adminAudiobookActiveQuery instanceof AdminAudiobookActiveQuery) {
            $audiobook = $audiobookRepository->findOneBy([
                'id' => $adminAudiobookActiveQuery->getAudiobookId(),
            ]);

            if ($audiobook === null) {
                $endpointLogger->error('Audiobook dont exist');
                $translateService->setPreferredLanguage($request);
                throw new DataNotFoundException([$translateService->getTranslation('AudiobookDontExists')]);
            }

            $additionalData = $adminAudiobookActiveQuery->getAdditionalData();

            if ($adminAudiobookActiveQuery->isActive() && array_key_exists('text', $additionalData) && !empty($additionalData['text'])) {
                if (array_key_exists('type', $additionalData)) {
                    $users = [];

                    switch ($additionalData['type']) {
                        case UserAudiobookActivationType::ALL->value:
                            $userRole = $roleRepository->findOneBy([
                                'name' => UserRolesNames::USER->value,
                            ]);

                            $users = $userRepository->getUsersByRole($userRole);
                            break;
                        case UserAudiobookActivationType::CATEGORY_PROPOSED_RELATED->value:
                            $users = $userRepository->getUsersWhereAudiobookInProposed($audiobook);
                            break;
                        case UserAudiobookActivationType::MY_LIST_RELATED->value:
                            $users = $userRepository->getUsersWhereAudiobookInMyList($audiobook);
                            break;
                        case UserAudiobookActivationType::AUDIOBOOK_INFO_RELATED->value:
                            $usersIds = $audiobookInfoRepository->getUsersWhereAudiobookInAudiobookInfo($audiobook);

                            foreach ($usersIds as $id) {
                                $user = $userRepository->findOneBy([
                                    'id' => $id,
                                ]);

                                if ($user !== null) {
                                    $users[] = $user;
                                }
                            }
                    }
                    $notificationBuilder = new NotificationBuilder();

                    $notificationBuilder
                        ->setType(NotificationType::USER_DELETE_DECLINE)
                        ->setAction($audiobook->getId())
                        ->setUserAction(NotificationUserType::SYSTEM)
                        ->setText($additionalData['text']);

                    foreach ($users as $user) {
                        $notificationBuilder->addUser($user);
                    }

                    $notification = $notificationBuilder->build($stockCache);

                    $notificationRepository->add($notification);
                }
            }

            $audiobook->setActive($adminAudiobookActiveQuery->isActive());
            $audiobookRepository->add($audiobook);

            $stockCache->invalidateTags([StockCacheTags::ADMIN_AUDIOBOOK->value]);
            $stockCache->invalidateTags([StockCacheTags::USER_AUDIOBOOKS->value]);

            return ResponseTool::getResponse();
        }

        $endpointLogger->error('Invalid given Query');
        $translateService->setPreferredLanguage($request);
        throw new InvalidJsonDataException($translateService);
    }

    /**
     * @param Request $request
     * @param RequestServiceInterface $requestService
     * @param AuthorizedUserServiceInterface $authorizedUserService
     * @param LoggerInterface $endpointLogger
     * @param AudiobookRepository $audiobookRepository
     * @param TranslateService $translateService
     * @param TagAwareCacheInterface $stockCache
     * @return Response
     * @throws DataNotFoundException
     * @throws InvalidArgumentException
     * @throws InvalidJsonDataException
     */
    #[Route('/api/admin/audiobook/change/cover', name: 'adminAudiobookChangeCover', methods: ['PATCH'])]
    #[AuthValidation(checkAuthToken: true, roles: ['Administrator'])]
    #[OA\Patch(
        description: 'Endpoint is changing given cover',
        requestBody: new OA\RequestBody(
            required: true,
            content : new OA\JsonContent(
                ref : new Model(type: AdminAudiobookChangeCoverQuery::class),
                type: 'object',
            ),
        ),
        responses  : [
            new OA\Response(
                response   : 200,
                description: 'Success',
            ),
        ]
    )]
    public function adminAudiobookChangeCover(
        Request                        $request,
        RequestServiceInterface        $requestService,
        AuthorizedUserServiceInterface $authorizedUserService,
        LoggerInterface                $endpointLogger,
        AudiobookRepository            $audiobookRepository,
        TranslateService               $translateService,
        TagAwareCacheInterface         $stockCache,
    ): Response {
        $adminAudiobookChangeCoverQuery = $requestService->getRequestBodyContent($request, AdminAudiobookChangeCoverQuery::class);

        if ($adminAudiobookChangeCoverQuery instanceof AdminAudiobookChangeCoverQuery) {

            $audiobook = $audiobookRepository->findOneBy([
                'id' => $adminAudiobookChangeCoverQuery->getAudiobookId(),
            ]);

            if ($audiobook === null) {
                $endpointLogger->error('Audiobook dont exist');
                $translateService->setPreferredLanguage($request);
                throw new DataNotFoundException([$translateService->getTranslation('AudiobookDontExists')]);
            }

            $handle = opendir($audiobook->getFileName());

            if (!$handle) {
                $endpointLogger->error('Audiobook Folder dont exists');
                $translateService->setPreferredLanguage($request);
                throw new DataNotFoundException([$translateService->getTranslation('AudiobookDontExists')]);
            }

            while (false !== ($entry = readdir($handle))) {
                if ($entry !== '.' && $entry !== '..') {
                    $file_parts = pathinfo($entry);
                    if ($file_parts['extension'] === 'jpg' || $file_parts['extension'] === 'jpeg' || $file_parts['extension'] === 'png') {

                        $img = $audiobook->getFileName() . '/' . $file_parts['basename'];
                        if (file_exists($img)) {
                            unlink($img);
                        }
                    }
                }
            }

            $decodedImageData = base64_decode($adminAudiobookChangeCoverQuery->getBase64());
            file_put_contents($audiobook->getFileName() . '/cover.' . $adminAudiobookChangeCoverQuery->getType(), $decodedImageData);

            $audiobook->setImgFile($audiobook->getFileName() . '/' . $audiobook->getFileName() . '/cover.' . $adminAudiobookChangeCoverQuery->getType());
            $audiobook->setImgFileChangeDate();
            $audiobookRepository->add($audiobook);

            $stockCache->invalidateTags([StockCacheTags::ADMIN_AUDIOBOOK->value]);

            return ResponseTool::getResponse();
        }

        $endpointLogger->error('Invalid given Query');
        $translateService->setPreferredLanguage($request);
        throw new InvalidJsonDataException($translateService);
    }

    /**
     * @param Request $request
     * @param RequestServiceInterface $requestService
     * @param AuthorizedUserServiceInterface $authorizedUserService
     * @param LoggerInterface $endpointLogger
     * @param AudiobookUserCommentRepository $audiobookUserCommentRepository
     * @param TranslateService $translateService
     * @param TagAwareCacheInterface $stockCache
     * @return Response
     * @throws DataNotFoundException
     * @throws InvalidArgumentException
     * @throws InvalidJsonDataException
     */
    #[Route('/api/admin/audiobook/comment/delete', name: 'adminAudiobookCommentDelete', methods: ['DELETE'])]
    #[AuthValidation(checkAuthToken: true, roles: ['Administrator'])]
    #[OA\Delete(
        description: 'Endpoint is deleting given comment',
        requestBody: new OA\RequestBody(
            required: true,
            content : new OA\JsonContent(
                ref : new Model(type: AdminAudiobookCommentDeleteQuery::class),
                type: 'object',
            ),
        ),
        responses  : [
            new OA\Response(
                response   : 200,
                description: 'Success',
            ),
        ]
    )]
    public function adminAudiobookCommentDelete(
        Request                        $request,
        RequestServiceInterface        $requestService,
        AuthorizedUserServiceInterface $authorizedUserService,
        LoggerInterface                $endpointLogger,
        AudiobookUserCommentRepository $audiobookUserCommentRepository,
        TranslateService               $translateService,
        TagAwareCacheInterface         $stockCache,
    ): Response {
        $adminAudiobookCommentDeleteQuery = $requestService->getRequestBodyContent($request, AdminAudiobookCommentDeleteQuery::class);

        if ($adminAudiobookCommentDeleteQuery instanceof AdminAudiobookCommentDeleteQuery) {

            $audiobookComment = $audiobookUserCommentRepository->findOneBy([
                'id' => $adminAudiobookCommentDeleteQuery->getAudiobookCommentId(),
            ]);

            if ($audiobookComment === null) {
                $endpointLogger->error('Audiobook comment dont exist');
                $translateService->setPreferredLanguage($request);
                throw new DataNotFoundException([$translateService->getTranslation('AudiobookCommentDontExists')]);
            }

            $audiobookComment->setDeleted(true);

            $audiobookUserCommentRepository->add($audiobookComment);

            $stockCache->invalidateTags([StockCacheTags::AUDIOBOOK_COMMENTS->value]);

            return ResponseTool::getResponse();
        }

        $endpointLogger->error('Invalid given Query');
        $translateService->setPreferredLanguage($request);
        throw new InvalidJsonDataException($translateService);
    }

    /**
     * @param Request $request
     * @param RequestServiceInterface $requestService
     * @param AuthorizedUserServiceInterface $authorizedUserService
     * @param LoggerInterface $endpointLogger
     * @param AudiobookUserCommentRepository $audiobookUserCommentRepository
     * @param AudiobookUserCommentLikeRepository $audiobookUserCommentLikeRepository
     * @param AudiobookRepository $audiobookRepository
     * @param TranslateService $translateService
     * @param TagAwareCacheInterface $stockCache
     * @return Response
     * @throws DataNotFoundException
     * @throws InvalidArgumentException
     * @throws InvalidJsonDataException
     */
    #[Route('/api/admin/audiobook/comment/get', name: 'adminAudiobookCommentGet', methods: ['POST'])]
    #[AuthValidation(checkAuthToken: true, roles: ['Administrator'])]
    #[OA\Post(
        description: 'Endpoint is returning comments for given audiobook for admin',
        requestBody: new OA\RequestBody(
            required: true,
            content : new OA\JsonContent(
                ref : new Model(type: AdminAudiobookCommentGetQuery::class),
                type: 'object',
            ),
        ),
        responses  : [
            new OA\Response(
                response   : 200,
                description: 'Success',
                content    : new Model(type: AudiobookCommentsSuccessModel::class),
            ),
        ]
    )]
    public function adminAudiobookCommentGet(
        Request                            $request,
        RequestServiceInterface            $requestService,
        AuthorizedUserServiceInterface     $authorizedUserService,
        LoggerInterface                    $endpointLogger,
        AudiobookUserCommentRepository     $audiobookUserCommentRepository,
        AudiobookUserCommentLikeRepository $audiobookUserCommentLikeRepository,
        AudiobookRepository                $audiobookRepository,
        TranslateService                   $translateService,
        TagAwareCacheInterface             $stockCache,
    ): Response {
        $audiobookCommentGetQuery = $requestService->getRequestBodyContent($request, AdminAudiobookCommentGetQuery::class);

        if ($audiobookCommentGetQuery instanceof AdminAudiobookCommentGetQuery) {

            $audiobook = $audiobookRepository->findOneBy([
                'id' => $audiobookCommentGetQuery->getAudiobookId(),
            ]);

            if ($audiobook === null) {
                $endpointLogger->error('Audiobook dont exist');
                $translateService->setPreferredLanguage($request);
                throw new DataNotFoundException([$translateService->getTranslation('AudiobookDontExists')]);
            }

            $user = $authorizedUserService->getAuthorizedUser();

            $successModel = $stockCache->get(CacheKeys::ADMIN_AUDIOBOOK_COMMENTS->value . $audiobook->getId(), function (ItemInterface $item) use ($user, $audiobook, $audiobookUserCommentRepository, $audiobookUserCommentLikeRepository) {
                $item->expiresAfter(CacheValidTime::FIVE_MINUTES->value);
                $item->tag(StockCacheTags::AUDIOBOOK_COMMENTS->value);

                $audiobookUserComments = $audiobookUserCommentRepository->findBy([
                    'parent'    => null,
                    'audiobook' => $audiobook->getId(),
                ]);

                $treeGenerator = new BuildAudiobookCommentTreeGenerator($audiobookUserComments, $audiobookUserCommentRepository, $audiobookUserCommentLikeRepository, $user, true);

                return new AudiobookCommentsSuccessModel($treeGenerator->generate());
            });

            return ResponseTool::getResponse($successModel);
        }

        $endpointLogger->error('Invalid given Query');
        $translateService->setPreferredLanguage($request);
        throw new InvalidJsonDataException($translateService);
    }
}
