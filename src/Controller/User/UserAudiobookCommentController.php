<?php

declare(strict_types = 1);

namespace App\Controller\User;

use App\Annotation\AuthValidation;
use App\Entity\AudiobookUserComment;
use App\Entity\AudiobookUserCommentLike;
use App\Entity\UserBanHistory;
use App\Enums\BanPeriodRage;
use App\Enums\Cache\CacheValidTime;
use App\Enums\Cache\UserCacheKeys;
use App\Enums\Cache\UserStockCacheTags;
use App\Enums\UserBanType;
use App\Enums\UserRolesNames;
use App\Exception\DataNotFoundException;
use App\Exception\InvalidJsonDataException;
use App\Model\Common\AudiobookCommentsSuccessModel;
use App\Model\Error\DataNotFoundModel;
use App\Model\Error\JsonDataInvalidModel;
use App\Model\Error\NotAuthorizeModel;
use App\Model\Error\PermissionNotGrantedModel;
use App\Query\User\UserAudiobookCommentAddQuery;
use App\Query\User\UserAudiobookCommentEditQuery;
use App\Query\User\UserAudiobookCommentGetQuery;
use App\Query\User\UserAudiobookCommentLikeAddQuery;
use App\Query\User\UserAudiobookCommentLikeDeleteQuery;
use App\Repository\AudiobookInfoRepository;
use App\Repository\AudiobookRepository;
use App\Repository\AudiobookUserCommentLikeRepository;
use App\Repository\AudiobookUserCommentRepository;
use App\Repository\UserBanHistoryRepository;
use App\Repository\UserRepository;
use App\Service\AuthorizedUserServiceInterface;
use App\Service\RequestServiceInterface;
use App\Service\TranslateServiceInterface;
use App\Tool\ResponseTool;
use App\ValueGenerator\BuildAudiobookCommentTreeGenerator;
use DateTime;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

use function array_key_exists;
use function count;

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
#[OA\Tag(name: 'UserAudiobookComment')]
class UserAudiobookCommentController extends AbstractController
{
    public function __construct(private readonly RequestServiceInterface $requestService, private readonly AuthorizedUserServiceInterface $authorizedUserService, private readonly LoggerInterface $endpointLogger, private readonly AudiobookRepository $audiobookRepository, private readonly AudiobookUserCommentRepository $audiobookUserCommentRepository, private readonly AudiobookInfoRepository $audiobookInfoRepository, private readonly TranslateServiceInterface $translateService, private readonly TagAwareCacheInterface $stockCache, private readonly UserRepository $userRepository, private readonly UserBanHistoryRepository $banHistoryRepository, private readonly AudiobookUserCommentLikeRepository $audiobookUserCommentLikeRepository) {}

    #[Route('/api/user/audiobook/comment/add', name: 'userAudiobookCommentAdd', methods: ['PUT'])]
    #[AuthValidation(checkAuthToken: true, roles: [UserRolesNames::USER])]
    #[OA\Put(
        description: 'Endpoint is adding comment for given audiobook',
        requestBody: new OA\RequestBody(
            required: true,
            content : new OA\JsonContent(
                ref : new Model(type: UserAudiobookCommentAddQuery::class),
                type: 'object',
            ),
        ),
        responses  : [
            new OA\Response(
                response   : 201,
                description: 'Success',
            ),
        ]
    )]
    public function userAudiobookCommentAdd(
        Request $request,
    ): Response {
        $userAudiobookCommentAddQuery = $this->requestService->getRequestBodyContent($request, UserAudiobookCommentAddQuery::class);

        if ($userAudiobookCommentAddQuery instanceof UserAudiobookCommentAddQuery) {
            $user = $this->authorizedUserService::getAuthorizedUser();

            $audiobook = $this->audiobookRepository->getAudiobookByCategoryKeyAndId($userAudiobookCommentAddQuery->getAudiobookId(), $userAudiobookCommentAddQuery->getCategoryKey());

            if (null === $audiobook) {
                $this->endpointLogger->error('Audiobook dont exist');
                $this->translateService->setPreferredLanguage($request);
                throw new DataNotFoundException([$this->translateService->getTranslation('AudiobookDontExists')]);
            }

            if (!$audiobook->getActive()) {
                $this->endpointLogger->error('Audiobook is not active');
                $this->translateService->setPreferredLanguage($request);
                throw new DataNotFoundException([$this->translateService->getTranslation('AudiobookNotActive')]);
            }

            $watchedParts = $this->audiobookInfoRepository->findBy([
                'audiobook' => $audiobook->getId(),
                'user'      => $user->getId(),
                'watched'   => true,
            ]);

            if (floor($audiobook->getParts() / 2) > $watchedParts) {
                $this->endpointLogger->error('Audiobook dont exist');
                $this->translateService->setPreferredLanguage($request);
                throw new DataNotFoundException([$this->translateService->getTranslation('AudiobookNotListened')]);
            }

            $lastUserComments = count($this->audiobookUserCommentRepository->getUserLastCommentsByMinutes($user, '20'));

            if ((int) $_ENV['INSTITUTION_USER_COMMENTS_LIMIT'] < $lastUserComments) {
                $banPeriod = new DateTime()->modify(BanPeriodRage::HOUR_BAN->value);

                $user
                    ->setBanned(true)
                    ->setBannedTo($banPeriod);

                $this->userRepository->add($user);

                $this->banHistoryRepository->add(new UserBanHistory($user, new DateTime(), $banPeriod, UserBanType::SPAM));

                $this->audiobookUserCommentRepository->setLastUserLastCommentsByMinutesToDeleted($user, '20');

                $this->endpointLogger->error('User got banned for to many comments in short period');
                $this->translateService->setPreferredLanguage($request);
                throw new DataNotFoundException([$this->translateService->getTranslation('ToManyUserComments')]);
            }

            $audiobookComment = new AudiobookUserComment($userAudiobookCommentAddQuery->getComment(), $audiobook, $user);

            $additionalData = $userAudiobookCommentAddQuery->getAdditionalData();

            if (array_key_exists('parentId', $additionalData)) {
                $audiobookParentComment = $this->audiobookUserCommentRepository->find($additionalData['parentId']);

                if (null === $audiobookParentComment || $audiobookParentComment->getParent() !== null) {
                    $this->endpointLogger->error('Audiobook Parent Comment dont exist');
                    $this->translateService->setPreferredLanguage($request);
                    throw new DataNotFoundException([$this->translateService->getTranslation('AudiobookParentCommentDontExists')]);
                }

                $audiobookComment->setParent($audiobookParentComment);
            }

            $this->audiobookUserCommentRepository->add($audiobookComment);

            $this->stockCache->invalidateTags([
                UserStockCacheTags::AUDIOBOOK_COMMENTS->value,
                UserStockCacheTags::USER_AUDIOBOOK_DETAIL->value . $audiobook->getId() . $user->getId(),
            ]);

            return ResponseTool::getResponse(httpCode: Response::HTTP_CREATED);
        }

        $this->endpointLogger->error('Invalid given Query');
        $this->translateService->setPreferredLanguage($request);
        throw new InvalidJsonDataException($this->translateService);
    }

    #[Route('/api/user/audiobook/comment/edit', name: 'userAudiobookCommentEdit', methods: ['PATCH'])]
    #[AuthValidation(checkAuthToken: true, roles: [UserRolesNames::USER])]
    #[OA\Patch(
        description: 'Endpoint is editing given comment',
        requestBody: new OA\RequestBody(
            required: true,
            content : new OA\JsonContent(
                ref : new Model(type: UserAudiobookCommentEditQuery::class),
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
    public function userAudiobookCommentEdit(
        Request $request,
    ): Response {
        $userAudiobookCommentEditQuery = $this->requestService->getRequestBodyContent($request, UserAudiobookCommentEditQuery::class);

        if ($userAudiobookCommentEditQuery instanceof UserAudiobookCommentEditQuery) {
            $user = $this->authorizedUserService::getAuthorizedUser();

            $audiobook = $this->audiobookRepository->getAudiobookByCategoryKeyAndId($userAudiobookCommentEditQuery->getAudiobookId(), $userAudiobookCommentEditQuery->getCategoryKey());

            if (null === $audiobook) {
                $this->endpointLogger->error('Audiobook dont exist');
                $this->translateService->setPreferredLanguage($request);
                throw new DataNotFoundException([$this->translateService->getTranslation('AudiobookDontExists')]);
            }

            if (!$audiobook->getActive()) {
                $this->endpointLogger->error('Audiobook is not active');
                $this->translateService->setPreferredLanguage($request);
                throw new DataNotFoundException([$this->translateService->getTranslation('AudiobookNotActive')]);
            }

            $audiobookComment = $this->audiobookUserCommentRepository->findOneBy([
                'id'   => $userAudiobookCommentEditQuery->getAudiobookCommentId(),
                'user' => $user->getId(),
            ]);

            if (null === $audiobookComment) {
                $this->endpointLogger->error('Audiobook Comment dont exist');
                $this->translateService->setPreferredLanguage($request);
                throw new DataNotFoundException([$this->translateService->getTranslation('AudiobookCommentDontExists')]);
            }

            $audiobookComment
                ->setDeleted($userAudiobookCommentEditQuery->isDeleted())
                ->setComment($userAudiobookCommentEditQuery->getComment())
                ->setEdited(true);

            $this->audiobookUserCommentRepository->add($audiobookComment);

            $this->stockCache->invalidateTags([
                UserStockCacheTags::AUDIOBOOK_COMMENTS->value,
                UserStockCacheTags::USER_AUDIOBOOK_DETAIL->value . $audiobook->getId() . $user->getId(),
            ]);

            return ResponseTool::getResponse();
        }

        $this->endpointLogger->error('Invalid given Query');
        $this->translateService->setPreferredLanguage($request);
        throw new InvalidJsonDataException($this->translateService);
    }

    #[Route('/api/user/audiobook/comment/like/add', name: 'userAudiobookCommentLikeAdd', methods: ['PATCH'])]
    #[AuthValidation(checkAuthToken: true, roles: [UserRolesNames::USER])]
    #[OA\Patch(
        description: 'Endpoint is adding/editing user audiobook comment like',
        requestBody: new OA\RequestBody(
            required: true,
            content : new OA\JsonContent(
                ref : new Model(type: UserAudiobookCommentLikeAddQuery::class),
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
    public function userAudiobookCommentLikeAdd(
        Request $request,
    ): Response {
        $userAudiobookCommentLikeAddQuery = $this->requestService->getRequestBodyContent($request, UserAudiobookCommentLikeAddQuery::class);

        if ($userAudiobookCommentLikeAddQuery instanceof UserAudiobookCommentLikeAddQuery) {
            $user = $this->authorizedUserService::getAuthorizedUser();

            $comment = $this->audiobookUserCommentRepository->find($userAudiobookCommentLikeAddQuery->getCommentId());

            if (null === $comment) {
                $this->endpointLogger->error('Audiobook dont exist');
                $this->translateService->setPreferredLanguage($request);
                throw new DataNotFoundException([$this->translateService->getTranslation('AudiobookCommentDontExists')]);
            }

            $commentLike = $this->audiobookUserCommentLikeRepository->findOneBy([
                'audiobookUserComment' => $comment->getId(),
                'user'                 => $user->getId(),
            ]);

            if (null === $commentLike) {
                $commentLike = new AudiobookUserCommentLike($userAudiobookCommentLikeAddQuery->isLike(), $comment, $user);
            } else {
                $commentLike->setLiked($userAudiobookCommentLikeAddQuery->isLike());
                if ($commentLike->getDeleted()) {
                    $commentLike->setDeleted(!$commentLike->getDeleted());
                }
            }

            $this->audiobookUserCommentLikeRepository->add($commentLike);
            $this->stockCache->invalidateTags([UserStockCacheTags::AUDIOBOOK_COMMENTS->value]);

            return ResponseTool::getResponse();
        }

        $this->endpointLogger->error('Invalid given Query');
        $this->translateService->setPreferredLanguage($request);
        throw new InvalidJsonDataException($this->translateService);
    }

    #[Route('/api/user/audiobook/comment/like/delete', name: 'userAudiobookCommentLikeDelete', methods: ['DELETE'])]
    #[AuthValidation(checkAuthToken: true, roles: [UserRolesNames::USER])]
    #[OA\Delete(
        description: 'Endpoint is adding/editing user audiobook comment like',
        requestBody: new OA\RequestBody(
            required: true,
            content : new OA\JsonContent(
                ref : new Model(type: UserAudiobookCommentLikeDeleteQuery::class),
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
    public function userAudiobookCommentLikeDelete(
        Request $request,
    ): Response {
        $userAudiobookCommentLikeDeleteQuery = $this->requestService->getRequestBodyContent($request, UserAudiobookCommentLikeDeleteQuery::class);

        if ($userAudiobookCommentLikeDeleteQuery instanceof UserAudiobookCommentLikeDeleteQuery) {
            $user = $this->authorizedUserService::getAuthorizedUser();

            $comment = $this->audiobookUserCommentRepository->find($userAudiobookCommentLikeDeleteQuery->getCommentId());

            if (null === $comment) {
                $this->endpointLogger->error('Audiobook dont exist');
                $this->translateService->setPreferredLanguage($request);
                throw new DataNotFoundException([$this->translateService->getTranslation('AudiobookCommentDontExists')]);
            }

            $commentLike = $this->audiobookUserCommentLikeRepository->findOneBy([
                'audiobookUserComment' => $comment->getId(),
                'user'                 => $user->getId(),
                'deleted'              => false,
            ]);

            if (null === $commentLike) {
                $this->endpointLogger->error('Comment like dont exist');
                $this->translateService->setPreferredLanguage($request);
                throw new DataNotFoundException([$this->translateService->getTranslation('AudiobookCommentLikeDontExists')]);
            }

            $commentLike->setDeleted(true);

            $this->audiobookUserCommentLikeRepository->add($commentLike);
            $this->stockCache->invalidateTags([UserStockCacheTags::AUDIOBOOK_COMMENTS->value]);

            return ResponseTool::getResponse();
        }

        $this->endpointLogger->error('Invalid given Query');
        $this->translateService->setPreferredLanguage($request);
        throw new InvalidJsonDataException($this->translateService);
    }

    #[Route('/api/user/audiobook/comment/get', name: 'userAudiobookCommentGet', methods: ['POST'])]
    #[AuthValidation(checkAuthToken: true, roles: [UserRolesNames::USER])]
    #[OA\Post(
        description: 'Endpoint is returning comments for given audiobook for user',
        requestBody: new OA\RequestBody(
            required: true,
            content : new OA\JsonContent(
                ref : new Model(type: UserAudiobookCommentGetQuery::class),
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
    public function userAudiobookCommentGet(
        Request $request,
    ): Response {
        $userAudiobookCommentGetQuery = $this->requestService->getRequestBodyContent($request, UserAudiobookCommentGetQuery::class);

        if ($userAudiobookCommentGetQuery instanceof UserAudiobookCommentGetQuery) {
            $user = $this->authorizedUserService::getAuthorizedUser();

            $audiobook = $this->audiobookRepository->getAudiobookByCategoryKeyAndId($userAudiobookCommentGetQuery->getAudiobookId(), $userAudiobookCommentGetQuery->getCategoryKey());

            if (null === $audiobook) {
                $this->endpointLogger->error('Audiobook dont exist');
                $this->translateService->setPreferredLanguage($request);
                throw new DataNotFoundException([$this->translateService->getTranslation('AudiobookCommentDontExists')]);
            }

            if (!$audiobook->getActive()) {
                $this->endpointLogger->error('Audiobook is not active');
                $this->translateService->setPreferredLanguage($request);
                throw new DataNotFoundException([$this->translateService->getTranslation('AudiobookNotActive')]);
            }

            $successModel = $this->stockCache->get(UserCacheKeys::USER_AUDIOBOOK_COMMENTS->value . $user->getId() . '_' . $audiobook->getId(), function (ItemInterface $item) use ($user, $audiobook): AudiobookCommentsSuccessModel {
                $item->expiresAfter(CacheValidTime::FIVE_MINUTES->value);
                $item->tag(UserStockCacheTags::AUDIOBOOK_COMMENTS->value);

                $audiobookUserComments = $this->audiobookUserCommentRepository->findBy([
                    'parent'    => null,
                    'audiobook' => $audiobook->getId(),
                    'deleted'   => false,
                ]);

                $treeGenerator = new BuildAudiobookCommentTreeGenerator($audiobookUserComments, $this->audiobookUserCommentRepository, $this->audiobookUserCommentLikeRepository, $user, false);

                return new AudiobookCommentsSuccessModel($treeGenerator->generate());
            });

            return ResponseTool::getResponse($successModel);
        }

        $this->endpointLogger->error('Invalid given Query');
        $this->translateService->setPreferredLanguage($request);
        throw new InvalidJsonDataException($this->translateService);
    }
}
