<?php

declare(strict_types = 1);

namespace App\Controller\Admin;

use App\Annotation\AuthValidation;
use App\Builder\NotificationBuilder;
use App\Entity\Audiobook;
use App\Enums\AudiobookAgeRange;
use App\Enums\Cache\AdminCacheKeys;
use App\Enums\Cache\AdminStockCacheTags;
use App\Enums\Cache\CacheValidTime;
use App\Enums\Cache\UserStockCacheTags;
use App\Enums\NotificationType;
use App\Enums\NotificationUserType;
use App\Enums\UserAudiobookActivationType;
use App\Enums\UserRolesNames;
use App\Exception\DataNotFoundException;
use App\Exception\InvalidJsonDataException;
use App\Model\Admin\AdminAudiobookDetailsSuccessModel;
use App\Model\Admin\AdminAudiobooksSuccessModel;
use App\Model\Admin\AdminCategoryAudiobookModel;
use App\Model\Common\AudiobookCommentsSuccessModel;
use App\Model\Common\AudiobookDetailCategoryModel;
use App\Model\Error\DataNotFoundModel;
use App\Model\Error\JsonDataInvalidModel;
use App\Model\Error\NotAuthorizeModel;
use App\Model\Error\PermissionNotGrantedModel;
use App\Model\Serialization\AdminAudiobooksSearchModel;
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
use App\Service\Admin\Audiobook\AudiobookAddService;
use App\Service\Admin\Audiobook\AudiobookServiceInterface;
use App\Service\AuthorizedUserServiceInterface;
use App\Service\RequestServiceInterface;
use App\Service\TranslateServiceInterface;
use App\Tool\ResponseTool;
use App\ValueGenerator\BuildAudiobookCommentTreeGenerator;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;
use Throwable;
use ZipArchive;

use function array_key_exists;
use function count;
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
#[OA\Tag(name: 'AdminAudiobook')]
class AdminAudiobookController extends AbstractController
{
    public function __construct(
        private readonly RequestServiceInterface $requestService,
        private readonly LoggerInterface $endpointLogger,
        private readonly AudiobookRepository $audiobookRepository,
        private readonly AudiobookCategoryRepository $audiobookCategoryRepository,
        private readonly AudiobookRatingRepository $audiobookRatingRepository,
        private readonly TranslateServiceInterface $translateService,
        private readonly TagAwareCacheInterface $stockCache,
        private readonly LoggerInterface $usersLogger,
        private readonly AudiobookServiceInterface $audiobookService,
        private readonly AudiobookAddService $addService,
        private readonly NotificationRepository $notificationRepository,
        private readonly AudiobookInfoRepository $audiobookInfoRepository,
        private readonly AudiobookUserCommentRepository $commentRepository,
        private readonly SerializerInterface $serializer,
        private readonly UserRepository $userRepository,
        private readonly RoleRepository $roleRepository,
        private readonly AudiobookUserCommentRepository $audiobookUserCommentRepository,
        private readonly AuthorizedUserServiceInterface $authorizedUserService,
        private readonly AudiobookUserCommentLikeRepository $audiobookUserCommentLikeRepository,
    ) {}

    #[Route('/api/admin/audiobook/details', name: 'adminAudiobookDetails', methods: ['POST'])]
    #[AuthValidation(checkAuthToken: true, roles: [UserRolesNames::ADMINISTRATOR, UserRolesNames::RECRUITER])]
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
        Request $request,
    ): Response {
        $adminAudiobookDetailsQuery = $this->requestService->getRequestBodyContent($request, AdminAudiobookDetailsQuery::class);

        if ($adminAudiobookDetailsQuery instanceof AdminAudiobookDetailsQuery) {
            $audiobook = $this->audiobookRepository->find($adminAudiobookDetailsQuery->getAudiobookId());

            if (null === $audiobook) {
                $this->endpointLogger->error('Audiobook dont exist');
                $this->translateService->setPreferredLanguage($request);
                throw new DataNotFoundException([$this->translateService->getTranslation('AudiobookDontExists')]);
            }

            $successModel = $this->stockCache->get(AdminCacheKeys::ADMIN_AUDIOBOOK->value . $audiobook->getId(), function (ItemInterface $item) use ($audiobook): AdminAudiobookDetailsSuccessModel {
                $item->expiresAfter(CacheValidTime::HALF_A_DAY->value);
                $item->tag(AdminStockCacheTags::ADMIN_AUDIOBOOK->value);

                $categories = $this->audiobookCategoryRepository->getAudiobookCategories($audiobook);

                $audiobookCategories = [];

                foreach ($categories as $category) {
                    $audiobookCategories[] = new AudiobookDetailCategoryModel(
                        (string) $category->getId(),
                        $category->getName(),
                        $category->getActive(),
                        $category->getCategoryKey(),
                    );
                }

                if ($audiobook->getImgFileChangeDate() === null) {
                    try {
                        $handle = opendir($audiobook->getFileName());
                        $img = '';
                        if (false !== $handle) {
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
                            $audiobook->setImgFile('/files/' . pathinfo((string) $audiobook->getFileName())['filename'] . '/' . $img);
                            $audiobook->setImgFileChangeDate();
                            $this->audiobookRepository->add($audiobook);
                        }
                    } catch (Throwable) {
                        $audiobook
                            ->setImgFile(null)
                            ->setImgFileChangeDate();

                        $this->audiobookRepository->add($audiobook);
                    }
                }

                $successModel = new AdminAudiobookDetailsSuccessModel(
                    (string) $audiobook->getId(),
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
                    count($this->audiobookRatingRepository->findBy([
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

        $this->endpointLogger->error('Invalid given Query');
        $this->translateService->setPreferredLanguage($request);
        throw new InvalidJsonDataException($this->translateService);
    }

    #[Route('/api/admin/audiobook/add', name: 'adminAudiobookAdd', methods: ['PUT'])]
    #[AuthValidation(checkAuthToken: true, roles: [UserRolesNames::ADMINISTRATOR])]
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
        Request $request,
    ): Response {
        $adminAudiobookAddQuery = $this->requestService->getRequestBodyContent($request, AdminAudiobookAddQuery::class);
        if ($adminAudiobookAddQuery instanceof AdminAudiobookAddQuery) {
            $this->audiobookService->configure($adminAudiobookAddQuery);
            $this->audiobookService->checkAndAddFile();

            try {
                if ($this->audiobookService->lastFile()) {
                    $this->audiobookService->combineFiles();
                    $folderDir = $this->audiobookService->unzip();
                    $ID3JsonData = $this->audiobookService->createAudiobookJsonData($folderDir);

                    $additionalData = $adminAudiobookAddQuery->getAdditionalData();

                    $id3TagsModel = $this->addService->getAudiobookId3Tags($ID3JsonData);

                    $title = $id3TagsModel->getTitle();
                    $author = $id3TagsModel->getArtist();

                    if (array_key_exists('title', $additionalData)) {
                        $title = $additionalData['title'];
                    }

                    if (array_key_exists('author', $additionalData)) {
                        $author = $additionalData['author'];
                    }

                    $age = null;

                    if (array_key_exists('age', $additionalData)) {
                        $age = match ($additionalData['age']) {
                            1 => AudiobookAgeRange::FROM3TO7,
                            2 => AudiobookAgeRange::FROM7TO12,
                            3 => AudiobookAgeRange::FROM12TO16,
                            4 => AudiobookAgeRange::FROM16TO18,
                            5 => AudiobookAgeRange::ABOVE18,
                        };
                    }

                    $newAudiobook = new Audiobook(
                        $title,
                        $author,
                        $id3TagsModel->getVersion(),
                        $id3TagsModel->getAlbum(),
                        $id3TagsModel->getYear(),
                        $id3TagsModel->getDuration(),
                        $id3TagsModel->getSize(),
                        $id3TagsModel->getParts(),
                        $id3TagsModel->getComment(),
                        $age ?? AudiobookAgeRange::ABOVE18,
                        $folderDir,
                    );

                    if ($id3TagsModel->getEncoded() !== '') {
                        $newAudiobook->setEncoded($id3TagsModel->getEncoded());
                    }

                    if (!empty($id3TagsModel->getImgFileDir())) {
                        $newAudiobook
                            ->setImgFile($id3TagsModel->getImgFileDir())
                            ->setImgFileChangeDate();
                    }

                    $audiobookCategories = [];

                    $this->addService->addAudiobookCategories($newAudiobook, $additionalData, $audiobookCategories);

                    $this->audiobookRepository->add($newAudiobook);

                    $successModel = new AdminAudiobookDetailsSuccessModel(
                        (string) $newAudiobook->getId(),
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
                        count($this->audiobookRatingRepository->findBy([
                            'audiobook' => $newAudiobook->getId(),
                        ])),
                        $newAudiobook->getImgFile(),
                    );

                    if ($newAudiobook->getEncoded() !== null) {
                        $successModel->setEncoded($newAudiobook->getEncoded());
                    }

                    $this->stockCache->invalidateTags([AdminStockCacheTags::ADMIN_AUDIOBOOK->value,
                        AdminStockCacheTags::ADMIN_CATEGORY_AUDIOBOOKS->value,
                        AdminStockCacheTags::ADMIN_CATEGORY->value]);

                    return ResponseTool::getResponse($successModel, Response::HTTP_CREATED);
                }
            } catch (Throwable $e) {
                $this->usersLogger->error($e->getMessage());

                return ResponseTool::getResponse(httpCode: Response::HTTP_INTERNAL_SERVER_ERROR);
            }

            return ResponseTool::getResponse(httpCode: Response::HTTP_CREATED);
        }

        $this->usersLogger->error('Invalid given Query');
        $this->translateService->setPreferredLanguage($request);
        throw new InvalidJsonDataException($this->translateService);
    }

    #[Route('/api/admin/audiobook/edit', name: 'adminAudiobookEdit', methods: ['PATCH'])]
    #[AuthValidation(checkAuthToken: true, roles: [UserRolesNames::ADMINISTRATOR, UserRolesNames::RECRUITER])]
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
        Request $request,
    ): Response {
        $adminAudiobookEditQuery = $this->requestService->getRequestBodyContent($request, AdminAudiobookEditQuery::class);

        if ($adminAudiobookEditQuery instanceof AdminAudiobookEditQuery) {
            $audiobook = $this->audiobookRepository->find($adminAudiobookEditQuery->getAudiobookId());

            if (null === $audiobook) {
                $this->endpointLogger->error('Audiobook dont exist');
                $this->translateService->setPreferredLanguage($request);
                throw new DataNotFoundException([$this->translateService->getTranslation('AudiobookDontExists')]);
            }

            $audiobook
                ->setTitle($adminAudiobookEditQuery->getTitle())
                ->setAuthor($adminAudiobookEditQuery->getAuthor())
                ->setVersion($adminAudiobookEditQuery->getVersion())
                ->setAlbum($adminAudiobookEditQuery->getAlbum())
                ->setYear($adminAudiobookEditQuery->getYear())
                ->setDuration((int) $adminAudiobookEditQuery->getDuration())
                ->setSize($adminAudiobookEditQuery->getSize())
                ->setParts($adminAudiobookEditQuery->getParts())
                ->setDescription($adminAudiobookEditQuery->getDescription())
                ->setAge($adminAudiobookEditQuery->getAge())
                ->setEncoded($adminAudiobookEditQuery->getEncoded());

            $this->audiobookRepository->add($audiobook);

            $this->stockCache->invalidateTags([
                AdminStockCacheTags::ADMIN_AUDIOBOOK->value,
                UserStockCacheTags::USER_AUDIOBOOK_DETAIL->value . $audiobook->getId(),
                UserStockCacheTags::USER_PROPOSED_AUDIOBOOKS->value,
                UserStockCacheTags::USER_AUDIOBOOKS->value,
            ]);

            return ResponseTool::getResponse();
        }

        $this->endpointLogger->error('Invalid given Query');
        $this->translateService->setPreferredLanguage($request);
        throw new InvalidJsonDataException($this->translateService);
    }

    #[Route('/api/admin/audiobook/delete', name: 'adminAudiobookDelete', methods: ['DELETE'])]
    #[AuthValidation(checkAuthToken: true, roles: [UserRolesNames::ADMINISTRATOR])]
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
        Request $request,
    ): Response {
        $adminAudiobookDeleteQuery = $this->requestService->getRequestBodyContent($request, AdminAudiobookDeleteQuery::class);

        if ($adminAudiobookDeleteQuery instanceof AdminAudiobookDeleteQuery) {
            $audiobook = $this->audiobookRepository->find($adminAudiobookDeleteQuery->getAudiobookId());

            if (null === $audiobook) {
                $this->endpointLogger->error('Audiobook dont exist');
                $this->translateService->setPreferredLanguage($request);
                throw new DataNotFoundException([$this->translateService->getTranslation('AudiobookDontExists')]);
            }

            $audId = $audiobook->getId();
            $this->audiobookRepository->remove($audiobook);

            $this->notificationRepository->updateDeleteNotificationsByAction($audId);

            $this->audiobookService->removeFolder($audiobook->getFileName());

            $this->stockCache->invalidateTags([
                AdminStockCacheTags::ADMIN_AUDIOBOOK->value,
                AdminStockCacheTags::ADMIN_CATEGORY->value,
                AdminStockCacheTags::ADMIN_CATEGORY_AUDIOBOOKS->value,
            ]);

            return ResponseTool::getResponse();
        }

        $this->endpointLogger->error('Invalid given Query');
        $this->translateService->setPreferredLanguage($request);
        throw new InvalidJsonDataException($this->translateService);
    }

    #[Route('/api/admin/audiobook/zip', name: 'adminAudiobookZip', methods: ['POST'])]
    #[AuthValidation(checkAuthToken: true, roles: [UserRolesNames::ADMINISTRATOR, UserRolesNames::RECRUITER])]
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
        Request $request,
    ): Response {
        $adminAudiobookZipQuery = $this->requestService->getRequestBodyContent($request, AdminAudiobookZipQuery::class);

        if ($adminAudiobookZipQuery instanceof AdminAudiobookZipQuery) {
            $audiobook = $this->audiobookRepository->find($adminAudiobookZipQuery->getAudiobookId());

            if (null === $audiobook) {
                $this->endpointLogger->error('Audiobook dont exist');
                $this->translateService->setPreferredLanguage($request);
                throw new DataNotFoundException([$this->translateService->getTranslation('AudiobookDontExists')]);
            }

            $zipFile = $audiobook->getFileName() . '.zip';

            $zip = new ZipArchive();

            if (file_exists($zipFile)) {
                unlink($zipFile);
            }

            $zip->open($zipFile, ZipArchive::CREATE | ZipArchive::OVERWRITE);

            $dir = opendir($audiobook->getFileName() . '/');

            if (!$dir) {
                $this->endpointLogger->error('Audiobook Folder dont Exists');
                $this->translateService->setPreferredLanguage($request);
                throw new DataNotFoundException([$this->translateService->getTranslation('AudiobookDontExists')]);
            }

            $hash = bin2hex(random_bytes(4));

            while ($file = readdir($dir)) {
                if (is_file($audiobook->getFileName() . '/' . $file)) {
                    $zip->addFile($audiobook->getFileName() . '/' . $file, $audiobook->getTitle() . '_' . $audiobook->getAuthor() . $hash . '/' . $file);
                }
            }

            $zip->close();

            return ResponseTool::getBinaryFileResponse($zipFile, true);
        }

        $this->endpointLogger->error('Invalid given Query');
        $this->translateService->setPreferredLanguage($request);
        throw new InvalidJsonDataException($this->translateService);
    }

    #[Route('/api/admin/audiobook/reAdding', name: 'adminAudiobookReAdding', methods: ['PATCH'])]
    #[AuthValidation(checkAuthToken: true, roles: [UserRolesNames::ADMINISTRATOR])]
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
        Request $request,
    ): Response {
        $adminAudiobookReAddingQuery = $this->requestService->getRequestBodyContent($request, AdminAudiobookReAddingQuery::class);

        if ($adminAudiobookReAddingQuery instanceof AdminAudiobookReAddingQuery) {
            $audiobook = $this->audiobookRepository->find($adminAudiobookReAddingQuery->getAudiobookId());

            if (null === $audiobook) {
                $this->endpointLogger->error('Audiobook dont exist');
                $this->translateService->setPreferredLanguage($request);
                throw new DataNotFoundException([$this->translateService->getTranslation('AudiobookDontExists')]);
            }

            $this->audiobookService->configure($adminAudiobookReAddingQuery);

            $this->audiobookService->checkAndAddFile();

            if ($this->audiobookService->lastFile()) {
                $this->audiobookService->combineFiles();
                $folderDir = $this->audiobookService->unzip($audiobook->getFileName());
                $ID3JsonData = $this->audiobookService->createAudiobookJsonData($folderDir);

                $additionalData = $adminAudiobookReAddingQuery->getAdditionalData();

                $id3TagsModel = $this->addService->getAudiobookId3Tags($ID3JsonData);

                $title = $id3TagsModel->getTitle();
                $author = $id3TagsModel->getArtist();

                if (array_key_exists('title', $additionalData)) {
                    $title = $additionalData['title'];
                }

                if (array_key_exists('author', $additionalData)) {
                    $author = $additionalData['author'];
                }

                $age = null;

                if (array_key_exists('age', $additionalData)) {
                    $age = match ($additionalData['age']) {
                        1 => AudiobookAgeRange::FROM3TO7,
                        2 => AudiobookAgeRange::FROM7TO12,
                        3 => AudiobookAgeRange::FROM12TO16,
                        4 => AudiobookAgeRange::FROM16TO18,
                        5 => AudiobookAgeRange::ABOVE18,
                    };
                }

                $audiobook->setActive(false);
                $audiobook->setTitle($title);
                $audiobook->setAuthor($author);
                $audiobook->setVersion($id3TagsModel->getVersion());
                $audiobook->setAlbum($id3TagsModel->getAlbum());
                $audiobook->setYear($id3TagsModel->getYear());
                $audiobook->setDuration($id3TagsModel->getDuration());
                $audiobook->setSize($id3TagsModel->getSize());
                $audiobook->setParts($id3TagsModel->getParts());
                $audiobook->setDescription($id3TagsModel->getComment());
                $audiobook->setAge($age ?? AudiobookAgeRange::ABOVE18);
                $audiobook->setFileName($folderDir);

                if ($id3TagsModel->getEncoded() !== '') {
                    $audiobook->setEncoded($id3TagsModel->getEncoded());
                }

                if (!empty($id3TagsModel->getImgFileDir())) {
                    $audiobook
                        ->setImgFile($id3TagsModel->getImgFileDir())
                        ->setImgFileChangeDate();
                }

                foreach ($audiobook->getCategories() as $category) {
                    $audiobook->removeCategory($category);
                }

                $audiobookCategories = [];

                $this->addService->addAudiobookCategories($audiobook, $additionalData, $audiobookCategories);

                $this->audiobookRepository->add($audiobook);

                $successModel = new AdminAudiobookDetailsSuccessModel(
                    (string) $audiobook->getId(),
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
                    count($this->audiobookRatingRepository->findBy([
                        'audiobook' => $audiobook->getId(),
                    ])),
                    $audiobook->getImgFile(),
                );

                if ($audiobook->getEncoded() !== null) {
                    $successModel->setEncoded($audiobook->getEncoded());
                }

                $audiobookInfos = $this->audiobookInfoRepository->findBy([
                    'audiobook' => $audiobook->getId(),
                ]);

                foreach ($audiobookInfos as $audiobookInfo) {
                    $this->audiobookInfoRepository->remove($audiobookInfo);
                }

                if ($adminAudiobookReAddingQuery->isDeleteNotifications()) {
                    $this->notificationRepository->updateDeleteNotificationsByAction($audiobook->getId());
                }

                if ($adminAudiobookReAddingQuery->isDeleteComments()) {
                    foreach ($audiobook->getAudiobookUserComments() as $comment) {
                        $this->commentRepository->remove($comment);
                    }
                }

                $this->stockCache->invalidateTags([
                    AdminStockCacheTags::ADMIN_AUDIOBOOK->value . $audiobook->getId(),
                    UserStockCacheTags::USER_AUDIOBOOKS->value,
                    UserStockCacheTags::USER_PROPOSED_AUDIOBOOKS->value,
                    UserStockCacheTags::USER_AUDIOBOOKS->value,
                ]);

                return ResponseTool::getResponse($successModel);
            }

            return ResponseTool::getResponse();
        }

        $this->endpointLogger->error('Invalid given Query');
        $this->translateService->setPreferredLanguage($request);
        throw new InvalidJsonDataException($this->translateService);
    }

    #[Route('/api/admin/audiobooks', name: 'adminAudiobooks', methods: ['POST'])]
    #[AuthValidation(checkAuthToken: true, roles: [UserRolesNames::ADMINISTRATOR, UserRolesNames::RECRUITER])]
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
        Request $request,
    ): Response {
        $adminAudiobooksQuery = $this->requestService->getRequestBodyContent($request, AdminAudiobooksQuery::class);

        if ($adminAudiobooksQuery instanceof AdminAudiobooksQuery) {
            $audiobookSearchData = $adminAudiobooksQuery->getSearchData();

            $audiobookSearchModel = new AdminAudiobooksSearchModel();
            $this->serializer->deserialize(
                json_encode($audiobookSearchData),
                AdminAudiobooksSearchModel::class,
                'json',
                [
                    AbstractNormalizer::OBJECT_TO_POPULATE             => $audiobookSearchModel,
                    AbstractObjectNormalizer::DISABLE_TYPE_ENFORCEMENT => true,
                ],
            );

            $successModel = new AdminAudiobooksSuccessModel();

            $audiobooks = $this->audiobookRepository->getAudiobooksByPage($audiobookSearchModel);

            $minResult = $adminAudiobooksQuery->getPage() * $adminAudiobooksQuery->getLimit();
            $maxResult = $adminAudiobooksQuery->getLimit() + $minResult;

            foreach ($audiobooks as $index => $audiobook) {
                if ($index < $minResult) {
                    continue;
                }

                if ($index < $maxResult) {
                    $audiobookModel = new AdminCategoryAudiobookModel(
                        (string) $audiobook->getId(),
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
            $successModel->setMaxPage((int) ceil(count($audiobooks) / $adminAudiobooksQuery->getLimit()));

            return ResponseTool::getResponse($successModel);
        }

        $this->endpointLogger->error('Invalid given Query');
        $this->translateService->setPreferredLanguage($request);
        throw new InvalidJsonDataException($this->translateService);
    }

    #[Route('/api/admin/audiobook/active', name: 'adminAudiobookActive', methods: ['PATCH'])]
    #[AuthValidation(checkAuthToken: true, roles: [UserRolesNames::ADMINISTRATOR, UserRolesNames::RECRUITER])]
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
        Request $request,
    ): Response {
        $adminAudiobookActiveQuery = $this->requestService->getRequestBodyContent($request, AdminAudiobookActiveQuery::class);

        if ($adminAudiobookActiveQuery instanceof AdminAudiobookActiveQuery) {
            $audiobook = $this->audiobookRepository->find($adminAudiobookActiveQuery->getAudiobookId());

            if (null === $audiobook) {
                $this->endpointLogger->error('Audiobook dont exist');
                $this->translateService->setPreferredLanguage($request);
                throw new DataNotFoundException([$this->translateService->getTranslation('AudiobookDontExists')]);
            }

            $additionalData = $adminAudiobookActiveQuery->getAdditionalData();

            if (
                array_key_exists('text', $additionalData)
                && !empty($additionalData['text'])
                && array_key_exists('type', $additionalData)
                && $adminAudiobookActiveQuery->isActive()
            ) {
                $users = [];

                switch ($additionalData['type']) {
                    case UserAudiobookActivationType::ALL->value:
                        $userRole = $this->roleRepository->findOneBy([
                            'name' => UserRolesNames::USER->value,
                        ]);

                        $users = $this->userRepository->getUsersByRole($userRole);
                        break;
                    case UserAudiobookActivationType::CATEGORY_PROPOSED_RELATED->value:
                        $users = $this->userRepository->getUsersWhereAudiobookInProposed($audiobook);
                        break;
                    case UserAudiobookActivationType::MY_LIST_RELATED->value:
                        $users = $this->userRepository->getUsersWhereAudiobookInMyList($audiobook);
                        break;
                    case UserAudiobookActivationType::AUDIOBOOK_INFO_RELATED->value:
                        $usersIds = $this->audiobookInfoRepository->getUsersWhereAudiobookInAudiobookInfo($audiobook);

                        foreach ($usersIds as $id) {
                            $user = $this->userRepository->find($id);

                            if (null !== $user) {
                                $users[] = $user;
                            }
                        }
                }

                $notificationBuilder = new NotificationBuilder();

                $notificationBuilder
                    ->setType(NotificationType::NEW_AUDIOBOOK)
                    ->setAction($audiobook->getId())
                    ->setUserAction(NotificationUserType::SYSTEM)
                    ->setActive(true)
                    ->setText($additionalData['text']);

                foreach ($users as $user) {
                    $notificationBuilder->addUser($user);
                }

                $notification = $notificationBuilder->build($this->stockCache);

                $this->notificationRepository->add($notification);
            }

            $audiobook->setActive($adminAudiobookActiveQuery->isActive());
            $this->audiobookRepository->add($audiobook);

            $this->stockCache->invalidateTags([
                AdminStockCacheTags::ADMIN_AUDIOBOOK->value . $audiobook->getId(),
                UserStockCacheTags::USER_AUDIOBOOKS->value,
                UserStockCacheTags::USER_PROPOSED_AUDIOBOOKS->value,
                UserStockCacheTags::USER_AUDIOBOOKS->value,
            ]);

            return ResponseTool::getResponse();
        }

        $this->endpointLogger->error('Invalid given Query');
        $this->translateService->setPreferredLanguage($request);
        throw new InvalidJsonDataException($this->translateService);
    }

    #[Route('/api/admin/audiobook/change/cover', name: 'adminAudiobookChangeCover', methods: ['PATCH'])]
    #[AuthValidation(checkAuthToken: true, roles: [UserRolesNames::ADMINISTRATOR])]
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
        Request $request,
    ): Response {
        $adminAudiobookChangeCoverQuery = $this->requestService->getRequestBodyContent($request, AdminAudiobookChangeCoverQuery::class);

        if ($adminAudiobookChangeCoverQuery instanceof AdminAudiobookChangeCoverQuery) {
            $audiobook = $this->audiobookRepository->find($adminAudiobookChangeCoverQuery->getAudiobookId());

            if (null === $audiobook) {
                $this->endpointLogger->error('Audiobook dont exist');
                $this->translateService->setPreferredLanguage($request);
                throw new DataNotFoundException([$this->translateService->getTranslation('AudiobookDontExists')]);
            }

            $handle = opendir($audiobook->getFileName());

            if (!$handle) {
                $this->endpointLogger->error('Audiobook Folder dont exists');
                $this->translateService->setPreferredLanguage($request);
                throw new DataNotFoundException([$this->translateService->getTranslation('AudiobookDontExists')]);
            }

            while (false !== ($entry = readdir($handle))) {
                if ('.' !== $entry && '..' !== $entry) {
                    $file_parts = pathinfo($entry);
                    if (in_array($file_parts['extension'], ['jpg', 'jpeg', 'png'], true)) {
                        $img = $audiobook->getFileName() . '/' . $file_parts['basename'];
                        if (file_exists($img)) {
                            unlink($img);
                        }
                    }
                }
            }

            $decodedImageData = base64_decode($adminAudiobookChangeCoverQuery->getBase64());
            file_put_contents($audiobook->getFileName() . '/cover.' . $adminAudiobookChangeCoverQuery->getType(), $decodedImageData);

            $audiobook
                ->setImgFile('/files/' . pathinfo((string) $audiobook->getFileName())['filename'] . '/cover.' . $adminAudiobookChangeCoverQuery->getType())
                ->setImgFileChangeDate();

            $this->audiobookRepository->add($audiobook);

            $this->stockCache->invalidateTags([
                AdminStockCacheTags::ADMIN_AUDIOBOOK->value,
                UserStockCacheTags::USER_AUDIOBOOK_DETAIL->value . $audiobook->getId(),
            ]);

            return ResponseTool::getResponse();
        }

        $this->endpointLogger->error('Invalid given Query');
        $this->translateService->setPreferredLanguage($request);
        throw new InvalidJsonDataException($this->translateService);
    }

    #[Route('/api/admin/audiobook/comment/delete', name: 'adminAudiobookCommentDelete', methods: ['PATCH'])]
    #[AuthValidation(checkAuthToken: true, roles: [UserRolesNames::ADMINISTRATOR, UserRolesNames::RECRUITER])]
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
        Request $request,
    ): Response {
        $adminAudiobookCommentDeleteQuery = $this->requestService->getRequestBodyContent($request, AdminAudiobookCommentDeleteQuery::class);

        if ($adminAudiobookCommentDeleteQuery instanceof AdminAudiobookCommentDeleteQuery) {
            $audiobookComment = $this->audiobookUserCommentRepository->find($adminAudiobookCommentDeleteQuery->getAudiobookCommentId());

            if (null === $audiobookComment) {
                $this->endpointLogger->error('Audiobook comment dont exist');
                $this->translateService->setPreferredLanguage($request);
                throw new DataNotFoundException([$this->translateService->getTranslation('AudiobookCommentDontExists')]);
            }

            $audiobookComment->setDeleted(!$audiobookComment->getDeleted());

            $this->audiobookUserCommentRepository->add($audiobookComment);

            $this->stockCache->invalidateTags([
                UserStockCacheTags::AUDIOBOOK_COMMENTS->value,
                AdminStockCacheTags::ADMIN_AUDIOBOOK->value,
            ]);

            return ResponseTool::getResponse();
        }

        $this->endpointLogger->error('Invalid given Query');
        $this->translateService->setPreferredLanguage($request);
        throw new InvalidJsonDataException($this->translateService);
    }

    #[Route('/api/admin/audiobook/comment/get', name: 'adminAudiobookCommentGet', methods: ['POST'])]
    #[AuthValidation(checkAuthToken: true, roles: [UserRolesNames::ADMINISTRATOR, UserRolesNames::RECRUITER])]
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
        Request $request,
    ): Response {
        $audiobookCommentGetQuery = $this->requestService->getRequestBodyContent($request, AdminAudiobookCommentGetQuery::class);

        if ($audiobookCommentGetQuery instanceof AdminAudiobookCommentGetQuery) {
            $audiobook = $this->audiobookRepository->find($audiobookCommentGetQuery->getAudiobookId());

            if (null === $audiobook) {
                $this->endpointLogger->error('Audiobook dont exist');
                $this->translateService->setPreferredLanguage($request);
                throw new DataNotFoundException([$this->translateService->getTranslation('AudiobookDontExists')]);
            }

            $user = $this->authorizedUserService::getAuthorizedUser();

            $successModel = $this->stockCache->get(AdminCacheKeys::ADMIN_AUDIOBOOK_COMMENTS->value . $audiobook->getId(), function (ItemInterface $item) use ($user, $audiobook): AudiobookCommentsSuccessModel {
                $item->expiresAfter(CacheValidTime::FIVE_MINUTES->value);
                $item->tag(UserStockCacheTags::AUDIOBOOK_COMMENTS->value);

                $audiobookUserComments = $this->audiobookUserCommentRepository->findBy([
                    'parent'    => null,
                    'audiobook' => $audiobook->getId(),
                ]);

                $treeGenerator = new BuildAudiobookCommentTreeGenerator($audiobookUserComments, $this->audiobookUserCommentRepository, $this->audiobookUserCommentLikeRepository, $user, true);

                return new AudiobookCommentsSuccessModel($treeGenerator->generate());
            });

            return ResponseTool::getResponse($successModel);
        }

        $this->endpointLogger->error('Invalid given Query');
        $this->translateService->setPreferredLanguage($request);
        throw new InvalidJsonDataException($this->translateService);
    }
}
