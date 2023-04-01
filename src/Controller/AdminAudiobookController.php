<?php

namespace App\Controller;

use App\Annotation\AuthValidation;
use App\Entity\Audiobook;
use App\Enums\AudiobookAgeRange;
use App\Exception\AudiobookConfigServiceException;
use App\Exception\DataNotFoundException;
use App\Exception\InvalidJsonDataException;
use App\Model\AdminAudiobookCategoryModel;
use App\Model\AdminAudiobookDetailsSuccessModel;
use App\Model\AdminAudiobooksSuccessModel;
use App\Model\AdminCategoryAudiobookModel;
use App\Model\AudiobookCommentsSuccessModel;
use App\Model\DataNotFoundModel;
use App\Model\JsonDataInvalidModel;
use App\Model\NotAuthorizeModel;
use App\Model\PermissionNotGrantedModel;
use App\Query\AdminAudiobookActiveQuery;
use App\Query\AdminAudiobookAddQuery;
use App\Query\AdminAudiobookChangeCoverQuery;
use App\Query\AdminAudiobookCommentDeleteQuery;
use App\Query\AdminAudiobookDeleteQuery;
use App\Query\AdminAudiobookDetailsQuery;
use App\Query\AdminAudiobookEditQuery;
use App\Query\AdminAudiobookReAddingQuery;
use App\Query\AdminAudiobooksQuery;
use App\Query\AdminAudiobookZipQuery;
use App\Query\AudiobookCommentGetQuery;
use App\Repository\AudiobookCategoryRepository;
use App\Repository\AudiobookRepository;
use App\Repository\AudiobookUserCommentLikeRepository;
use App\Repository\AudiobookUserCommentRepository;
use App\Service\AudiobookService;
use App\Service\AuthorizedUserServiceInterface;
use App\Service\RequestServiceInterface;
use App\Tool\ResponseTool;
use App\ValueGenerator\BuildAudiobookCommentTreeGenerator;
use DateTime;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Attributes as OA;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Uid\Uuid;
use ZipArchive;

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
    /**
     * @param Request $request
     * @param RequestServiceInterface $requestService
     * @param AuthorizedUserServiceInterface $authorizedUserService
     * @param LoggerInterface $endpointLogger
     * @param AudiobookRepository $audiobookRepository
     * @param AudiobookCategoryRepository $audiobookCategoryRepository
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
        AudiobookCategoryRepository    $audiobookCategoryRepository
    ): Response
    {
        $adminAudiobookDetailsQuery = $requestService->getRequestBodyContent($request, AdminAudiobookDetailsQuery::class);

        if ($adminAudiobookDetailsQuery instanceof AdminAudiobookDetailsQuery) {

            $audiobook = $audiobookRepository->findOneBy([
                "id" => $adminAudiobookDetailsQuery->getAudiobookId()
            ]);

            if ($audiobook == null) {
                $endpointLogger->error("Audiobook dont exist");
                throw new DataNotFoundException(["adminAudiobook.details.audiobook.not.exist"]);
            }

            $categories = $audiobookCategoryRepository->getAudiobookCategories($audiobook);

            $audiobookCategories = [];

            foreach ($categories as $category) {
                $audiobookCategories[] = new AdminAudiobookCategoryModel(
                    $category->getId(),
                    $category->getName(),
                    $category->getActive(),
                    $category->getCategoryKey()
                );
            }

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
                $audiobook->getAvgRating(),
                $audiobookCategories
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
     * @param AudiobookService $audiobookService
     * @param AudiobookCategoryRepository $audiobookCategoryRepository
     * @param AudiobookRepository $audiobookRepository
     * @return Response
     * @throws AudiobookConfigServiceException
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
                content: new Model(type: AdminAudiobookDetailsSuccessModel::class)
            )
        ]
    )]
    public function adminAudiobookAdd(
        Request                        $request,
        RequestServiceInterface        $requestService,
        AuthorizedUserServiceInterface $authorizedUserService,
        LoggerInterface                $endpointLogger,
        AudiobookService               $audiobookService,
        AudiobookCategoryRepository    $audiobookCategoryRepository,
        AudiobookRepository            $audiobookRepository
    ): Response
    {
        $adminAudiobookAddQuery = $requestService->getRequestBodyContent($request, AdminAudiobookAddQuery::class);

        if ($adminAudiobookAddQuery instanceof AdminAudiobookAddQuery) {

            $audiobookService->configure($adminAudiobookAddQuery);
            $audiobookService->checkAndAddFile();

            if ($audiobookService->lastFile()) {

                $audiobookService->combineFiles();

                $folderDir = $audiobookService->unzip();

                $ID3JsonData = $audiobookService->createAudiobookJsonData($folderDir);

                if (array_key_exists("version", $ID3JsonData)) {
                    $version = $ID3JsonData["version"];
                } else {
                    $version = "1";
                }

                if (array_key_exists("album", $ID3JsonData)) {
                    $album = $ID3JsonData["album"];
                } else {
                    $album = "album";
                }

                if (array_key_exists("author", $ID3JsonData)) {
                    $author = $ID3JsonData["author"];
                } else {
                    $author = "author";
                }

                if (array_key_exists("year", $ID3JsonData)) {
                    if (DateTime::createFromFormat('d.m.Y', $ID3JsonData["year"])) {
                        $year = DateTime::createFromFormat('d.m.Y', $ID3JsonData["year"]);
                    } else {
                        $year = new \DateTime("Now");
                    }
                } else {
                    $year = new \DateTime("Now");
                }

                if (array_key_exists("encoded", $ID3JsonData)) {
                    $encoded = $ID3JsonData["encoded"];
                } else {
                    $encoded = "";
                }

                if (array_key_exists("comments", $ID3JsonData)) {
                    $description = $ID3JsonData["comments"];
                } else {
                    $description = "desc";
                }

                if (array_key_exists("duration", $ID3JsonData)) {
                    $duration = $ID3JsonData["duration"];
                } else {
                    $duration = 0;
                }

                if (array_key_exists("size", $ID3JsonData)) {
                    $size = $ID3JsonData["size"];
                } else {
                    $size = "1";
                }

                if (array_key_exists("parts", $ID3JsonData)) {
                    $parts = $ID3JsonData["parts"];
                } else {
                    $parts = "1";
                }

                if (array_key_exists("title", $ID3JsonData)) {
                    $title = $ID3JsonData["title"];
                } else {
                    $title = "title";
                }

                $additionalData = $adminAudiobookAddQuery->getAdditionalData();

                if (array_key_exists("title", $additionalData)) {
                    $title = $additionalData["title"];
                }
                if (array_key_exists("author", $additionalData)) {
                    $author = $additionalData["author"];
                }

                $newAudiobook = new Audiobook($title, $author, $version, $album, $year, $duration, $size, $parts, $description, AudiobookAgeRange::FROM3TO7, $folderDir);

                if ($encoded != "") {
                    $newAudiobook->setEncoded($encoded);
                }

                $audiobookCategories = [];

                if (array_key_exists("categories", $additionalData)) {

                    $categories = $additionalData["categories"];

                    foreach ($categories as $category) {

                        $audiobookCategory = $audiobookCategoryRepository->findOneBy([
                            "id" => Uuid::fromString($category)
                        ]);

                        if ($audiobookCategory != null) {
                            $newAudiobook->addCategory($audiobookCategory);

                            $audiobookCategories[] = new AdminAudiobookCategoryModel(
                                $audiobookCategory->getId(),
                                $audiobookCategory->getName(),
                                $audiobookCategory->getActive(),
                                $audiobookCategory->getCategoryKey()
                            );
                        }
                    }
                }

                $audiobookRepository->add($newAudiobook);

                $successModel = new AdminAudiobookDetailsSuccessModel(
                    $newAudiobook->getId(),
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
                    $audiobookCategories
                );

                if ($newAudiobook->getEncoded() != null) {
                    $successModel->setEncoded($newAudiobook->getEncoded());
                }

                return ResponseTool::getResponse($successModel, 201);
            } else {
                return ResponseTool::getResponse();
            }

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
            $audiobook->setEncoded($adminAudiobookEditQuery->getEncoded());

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
     * @param AudiobookService $audiobookService
     * @param AudiobookRepository $audiobookRepository
     * @return Response
     * @throws DataNotFoundException
     * @throws InvalidJsonDataException
     */
    #[Route("/api/admin/audiobook/delete", name: "adminAudiobookDelete", methods: ["DELETE"])]
    #[AuthValidation(checkAuthToken: true, roles: ["Administrator"])]
    #[OA\Delete(
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
            )
        ]
    )]
    public function adminAudiobookDelete(
        Request                        $request,
        RequestServiceInterface        $requestService,
        AuthorizedUserServiceInterface $authorizedUserService,
        LoggerInterface                $endpointLogger,
        AudiobookService               $audiobookService,
        AudiobookRepository            $audiobookRepository
    ): Response
    {
        $adminAudiobookDeleteQuery = $requestService->getRequestBodyContent($request, AdminAudiobookDeleteQuery::class);

        if ($adminAudiobookDeleteQuery instanceof AdminAudiobookDeleteQuery) {

            $audiobook = $audiobookRepository->findOneBy([
                "id" => $adminAudiobookDeleteQuery->getAudiobookId()
            ]);

            if ($audiobook == null) {
                $endpointLogger->error("Audiobook dont exist");
                throw new DataNotFoundException(["adminAudiobook.delete.audiobook.not.exist"]);
            }

            $audiobookRepository->remove($audiobook);

            $audiobookService->removeFolder($audiobook->getFileName());

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
     * @param AudiobookRepository $audiobookRepository
     * @return Response
     * @throws DataNotFoundException
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
            )
        ]
    )]
    public function adminAudiobookZip(
        Request                        $request,
        RequestServiceInterface        $requestService,
        AuthorizedUserServiceInterface $authorizedUserService,
        LoggerInterface                $endpointLogger,
        AudiobookRepository            $audiobookRepository
    ): Response
    {
        $adminAudiobookZipQuery = $requestService->getRequestBodyContent($request, AdminAudiobookZipQuery::class);

        if ($adminAudiobookZipQuery instanceof AdminAudiobookZipQuery) {

            $audiobook = $audiobookRepository->findOneBy([
                "id" => $adminAudiobookZipQuery->getAudiobookId()
            ]);

            if ($audiobook == null) {
                $endpointLogger->error("Audiobook dont exist");
                throw new DataNotFoundException(["adminAudiobook.zip.audiobook.not.exist"]);
            }

            $zipFile = $audiobook->getFileName() . ".zip";

            $zip = new ZipArchive;

            if (file_exists($zipFile)) {
                unlink($zipFile);
            }

            $zip->open($zipFile, ZipArchive::CREATE | ZipArchive::OVERWRITE);

            $dir = opendir($audiobook->getFileName() . "/");

            while ($file = readdir($dir)) {
                if (is_file($audiobook->getFileName() . "/" . $file)) {
                    $zip->addFile($audiobook->getFileName() . "/" . $file, basename($audiobook->getFileName() . "/" . $file));
                }
            }

            $zip->close();

            return ResponseTool::getBinaryFileResponse($zipFile, true);
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
     * @param AudiobookRepository $audiobookRepository
     * @param AudiobookService $audiobookService
     * @param AudiobookCategoryRepository $audiobookCategoryRepository
     * @return Response
     * @throws AudiobookConfigServiceException
     * @throws DataNotFoundException
     * @throws InvalidJsonDataException
     */
    #[Route("/api/admin/audiobook/reAdding", name: "adminAudiobookReAdding", methods: ["PATCH"])]
    #[AuthValidation(checkAuthToken: true, roles: ["Administrator"])]
    #[OA\Patch(
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
                content: new Model(type: AdminAudiobookDetailsSuccessModel::class)
            )
        ]
    )]
    public function adminAudiobookReAdding(
        Request                        $request,
        RequestServiceInterface        $requestService,
        AuthorizedUserServiceInterface $authorizedUserService,
        LoggerInterface                $endpointLogger,
        AudiobookRepository            $audiobookRepository,
        AudiobookService               $audiobookService,
        AudiobookCategoryRepository    $audiobookCategoryRepository
    ): Response
    {
        $adminAudiobookReAddingQuery = $requestService->getRequestBodyContent($request, AdminAudiobookReAddingQuery::class);

        if ($adminAudiobookReAddingQuery instanceof AdminAudiobookReAddingQuery) {

            $audiobook = $audiobookRepository->findOneBy([
                "id" => $adminAudiobookReAddingQuery->getAudiobookId()
            ]);

            if ($audiobook == null) {
                $endpointLogger->error("Audiobook dont exist");
                throw new DataNotFoundException(["adminAudiobook.reAdding.audiobook.not.exist"]);
            }

            $audiobookService->configure($adminAudiobookReAddingQuery);

            $audiobookService->checkAndAddFile();

            if ($audiobookService->lastFile()) {

                $audiobookService->combineFiles();
                $folderDir = $audiobookService->unzip(true);

                $ID3JsonData = $audiobookService->createAudiobookJsonData($folderDir);

                if (array_key_exists("version", $ID3JsonData)) {
                    $version = $ID3JsonData["version"];
                } else {
                    $version = "1";
                }

                if (array_key_exists("album", $ID3JsonData)) {
                    $album = $ID3JsonData["album"];
                } else {
                    $album = "album";
                }

                if (array_key_exists("author", $ID3JsonData)) {
                    $author = $ID3JsonData["author"];
                } else {
                    $author = "author";
                }

                if (array_key_exists("year", $ID3JsonData)) {
                    if (DateTime::createFromFormat('d.m.Y', $ID3JsonData["year"])) {
                        $year = DateTime::createFromFormat('d.m.Y', $ID3JsonData["year"]);
                    } else {
                        $year = new \DateTime("Now");
                    }
                } else {
                    $year = new \DateTime("Now");
                }

                if (array_key_exists("encoded", $ID3JsonData)) {
                    $encoded = $ID3JsonData["encoded"];
                } else {
                    $encoded = "";
                }

                if (array_key_exists("comments", $ID3JsonData)) {
                    $description = $ID3JsonData["comments"];
                } else {
                    $description = "desc";
                }

                if (array_key_exists("duration", $ID3JsonData)) {
                    $duration = $ID3JsonData["duration"];
                } else {
                    $duration = "1";
                }

                if (array_key_exists("size", $ID3JsonData)) {
                    $size = $ID3JsonData["size"];
                } else {
                    $size = "1";
                }

                if (array_key_exists("parts", $ID3JsonData)) {
                    $parts = $ID3JsonData["parts"];
                } else {
                    $parts = "1";
                }

                if (array_key_exists("title", $ID3JsonData)) {
                    $title = $ID3JsonData["title"];
                } else {
                    $title = "title";
                }

                $additionalData = $adminAudiobookReAddingQuery->getAdditionalData();

                if (array_key_exists("title", $additionalData)) {
                    $title = $additionalData["title"];
                }
                if (array_key_exists("author", $additionalData)) {
                    $author = $additionalData["author"];
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
                $audiobook->setAge(AudiobookAgeRange::FROM3TO7);
                $audiobook->setFileName($folderDir);


                if ($encoded != "") {
                    $audiobook->setEncoded($encoded);
                }

                foreach ($audiobook->getCategories() as $category) {
                    $audiobook->removeCategory($category);
                }

                $audiobookCategories = [];

                if (array_key_exists("categories", $additionalData)) {

                    $categories = [];

                    if (!empty($additionalData["categories"])) {
                        foreach ($additionalData["categories"] as $category) {
                            $categories[] = Uuid::fromString($category)->toBinary();
                        }
                    }
                    foreach ($categories as $category) {

                        $audiobookCategory = $audiobookCategoryRepository->findOneBy([
                            "id" => $category
                        ]);

                        if ($audiobookCategory != null) {
                            $audiobook->addCategory($audiobookCategory);

                            $audiobookCategories[] = new AdminAudiobookCategoryModel(
                                $audiobookCategory->getId(),
                                $audiobookCategory->getName(),
                                $audiobookCategory->getActive(),
                                $audiobookCategory->getCategoryKey()
                            );
                        }
                    }
                }

                $audiobookRepository->add($audiobook);

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
                    $audiobook->getAvgRating(),
                    $audiobookCategories
                );

                if ($audiobook->getEncoded() != null) {
                    $successModel->setEncoded($audiobook->getEncoded());
                }

                return ResponseTool::getResponse($successModel, 201);
            } else {
                return ResponseTool::getResponse();
            }
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
     * @param AudiobookRepository $audiobookRepository
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

            $audiobookSearchData = $adminAudiobooksQuery->getSearchData();

            $categories = [];
            $author = null;
            $title = null;
            $album = null;
            $duration = null;
            $parts = null;
            $age = null;
            $order = null;
            $year = null;

            if (array_key_exists('categories', $audiobookSearchData)) {
                if (!empty($audiobookSearchData["categories"])) {
                    foreach ($audiobookSearchData["categories"] as $category) {
                        $categories[] = Uuid::fromString($category)->toBinary();
                    }
                }
            }

            if (array_key_exists('author', $audiobookSearchData)) {
                $author = ($audiobookSearchData['author'] && '' != $audiobookSearchData['author']) ? "%" . $audiobookSearchData['author'] . "%" : null;
            }
            if (array_key_exists('title', $audiobookSearchData)) {
                $title = ($audiobookSearchData['title'] && '' != $audiobookSearchData['title']) ? "%" . $audiobookSearchData['title'] . "%" : null;
            }
            if (array_key_exists('album', $audiobookSearchData)) {
                $album = ($audiobookSearchData['album'] && '' != $audiobookSearchData['album']) ? "%" . $audiobookSearchData['album'] . "%" : null;
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
            if (array_key_exists('year', $audiobookSearchData) && $audiobookSearchData['year'] != false) {
                $year = $audiobookSearchData['year'];
            }

            $successModel = new AdminAudiobooksSuccessModel();

            $audiobooks = $audiobookRepository->getAudiobooksByPage($categories, $author, $title, $album, $duration, $age, $year, $parts, $order);

            $minResult = $adminAudiobooksQuery->getPage() * $adminAudiobooksQuery->getLimit();
            $maxResult = $adminAudiobooksQuery->getLimit() + $minResult;

            foreach ($audiobooks as $index => $audiobook) {
                if ($index < $minResult) {
                    continue;
                } elseif ($index < $maxResult) {
                    $audiobookModel = new AdminCategoryAudiobookModel(
                        $audiobook->getId(),
                        $audiobook->getTitle(),
                        $audiobook->getAuthor(),
                        $audiobook->getYear(),
                        $audiobook->getDuration(),
                        $audiobook->getSize(),
                        $audiobook->getParts(),
                        $audiobook->getAvgRating(),
                        $audiobook->getAge(),
                        $audiobook->getActive()
                    );
                    $successModel->addAudiobook($audiobookModel);
                } else {
                    break;
                }

            }

            $successModel->setPage($adminAudiobooksQuery->getPage());
            $successModel->setLimit($adminAudiobooksQuery->getLimit());
            $successModel->setMaxPage(ceil(count($audiobooks) / $adminAudiobooksQuery->getLimit()));

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
     * @param AudiobookRepository $audiobookRepository
     * @return Response
     * @throws DataNotFoundException
     * @throws InvalidJsonDataException
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

    /**
     * @param Request $request
     * @param RequestServiceInterface $requestService
     * @param AuthorizedUserServiceInterface $authorizedUserService
     * @param LoggerInterface $endpointLogger
     * @param AudiobookUserCommentRepository $audiobookUserCommentRepository
     * @return Response
     * @throws DataNotFoundException
     * @throws InvalidJsonDataException
     */
    #[Route("/api/admin/audiobook/comment/delete", name: "adminAudiobookCommentDelete", methods: ["DELETE"])]
    #[AuthValidation(checkAuthToken: true, roles: ["Administrator"])]
    #[OA\Delete(
        description: "Endpoint is deleting given comment",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                ref: new Model(type: AdminAudiobookCommentDeleteQuery::class),
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
    public function adminAudiobookCommentDelete(
        Request                        $request,
        RequestServiceInterface        $requestService,
        AuthorizedUserServiceInterface $authorizedUserService,
        LoggerInterface                $endpointLogger,
        AudiobookUserCommentRepository $audiobookUserCommentRepository
    ): Response
    {
        $adminAudiobookCommentDeleteQuery = $requestService->getRequestBodyContent($request, AdminAudiobookCommentDeleteQuery::class);

        if ($adminAudiobookCommentDeleteQuery instanceof AdminAudiobookCommentDeleteQuery) {

            $audiobookComment = $audiobookUserCommentRepository->findOneBy([
                "id" => $adminAudiobookCommentDeleteQuery->getAudiobookCommentId()
            ]);

            if ($audiobookComment == null) {
                $endpointLogger->error("Audiobook comment dont exist");
                throw new DataNotFoundException(["adminAudiobook.delete.comment.audiobookComment.not.exist"]);
            }

            $audiobookComment->setDeleted(true);

            $audiobookUserCommentRepository->add($audiobookComment);

            return ResponseTool::getResponse();

        } else {
            $endpointLogger->error("Invalid given Query");
            throw new InvalidJsonDataException("adminAudiobook.delete.comment.invalid.query");
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
    #[Route("/api/admin/audiobook/change/cover", name: "adminAudiobookChangeCover", methods: ["PATCH"])]
    #[AuthValidation(checkAuthToken: true, roles: ["Administrator"])]
    #[OA\Patch(
        description: "Endpoint is changing given cover",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                ref: new Model(type: AdminAudiobookChangeCoverQuery::class),
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
    public function adminAudiobookChangeCover(
        Request                        $request,
        RequestServiceInterface        $requestService,
        AuthorizedUserServiceInterface $authorizedUserService,
        LoggerInterface                $endpointLogger,
        AudiobookRepository            $audiobookRepository
    ): Response
    {
        $adminAudiobookChangeCoverQuery = $requestService->getRequestBodyContent($request, AdminAudiobookChangeCoverQuery::class);

        if ($adminAudiobookChangeCoverQuery instanceof AdminAudiobookChangeCoverQuery) {

            $audiobook = $audiobookRepository->findOneBy([
                "id" => $adminAudiobookChangeCoverQuery->getAudiobookId()
            ]);

            if ($audiobook == null) {
                $endpointLogger->error("Audiobook dont exist");
                throw new DataNotFoundException(["adminAudiobook.change.cover.audiobook.not.exist"]);
            }

            $handle = opendir($audiobook->getFileName());

            while (false !== ($entry = readdir($handle))) {

                if ($entry != "." && $entry != "..") {

                    $file_parts = pathinfo($entry);

                    if ($file_parts['extension'] == "jpg" || $file_parts['extension'] == "jpeg" || $file_parts['extension'] == "png") {

                        $img = $audiobook->getFileName() . "/" . $file_parts["basename"];

                        if (file_exists($img)) {
                            unlink($img);
                        }

                    }
                }
            }

            $decodedImageData = base64_decode($adminAudiobookChangeCoverQuery->getBase64());
            file_put_contents($audiobook->getFileName() . "/cover." . $adminAudiobookChangeCoverQuery->getType(), $decodedImageData);

            return ResponseTool::getResponse();

        } else {
            $endpointLogger->error("Invalid given Query");
            throw new InvalidJsonDataException("adminAudiobook.change.cover.query");
        }
    }

    /**
     * @param Request $request
     * @param RequestServiceInterface $requestService
     * @param AuthorizedUserServiceInterface $authorizedUserService
     * @param LoggerInterface $endpointLogger
     * @param AudiobookUserCommentRepository $audiobookUserCommentRepository
     * @param AudiobookUserCommentLikeRepository $audiobookUserCommentLikeRepository
     * @param AudiobookRepository $audiobookRepository
     * @return Response
     * @throws DataNotFoundException
     * @throws InvalidJsonDataException
     */
    #[Route("/api/admin/audiobook/comment/get", name: "adminAudiobookCommentGet", methods: ["POST"])]
    #[AuthValidation(checkAuthToken: true, roles: ["Administrator"])]
    #[OA\Post(
        description: "Endpoint is returning comments for given audiobook for admin",
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
                content: new Model(type: AudiobookCommentsSuccessModel::class)
            )
        ]
    )]
    public function adminAudiobookCommentGet(
        Request                            $request,
        RequestServiceInterface            $requestService,
        AuthorizedUserServiceInterface     $authorizedUserService,
        LoggerInterface                    $endpointLogger,
        AudiobookUserCommentRepository     $audiobookUserCommentRepository,
        AudiobookUserCommentLikeRepository $audiobookUserCommentLikeRepository,
        AudiobookRepository                $audiobookRepository
    ): Response
    {
        $audiobookCommentGetQuery = $requestService->getRequestBodyContent($request, AudiobookCommentGetQuery::class);

        if ($audiobookCommentGetQuery instanceof AudiobookCommentGetQuery) {

            $user = $authorizedUserService->getAuthorizedUser();

            $audiobook = $audiobookRepository->findOneBy([
                "id" => $audiobookCommentGetQuery->getAudiobookId()
            ]);

            if ($audiobook == null) {
                $endpointLogger->error("Audiobook dont exist");
                throw new DataNotFoundException(["adminAudiobook.comment.get.audiobook.not.exist"]);
            }

            $audiobookUserComments = $audiobookUserCommentRepository->findBy([
                "parent" => null,
                "audiobook" => $audiobook->getId()
            ]);

            $treeGenerator = new BuildAudiobookCommentTreeGenerator($audiobookUserComments, $audiobookUserCommentRepository, $audiobookUserCommentLikeRepository, $user, true);

            $successModel = new AudiobookCommentsSuccessModel($treeGenerator->generate());

            return ResponseTool::getResponse($successModel);
        } else {
            $endpointLogger->error("Invalid given Query");
            throw new InvalidJsonDataException("adminAudiobook.comment.get.invalid.query");
        }
    }
}