<?php

declare(strict_types=1);

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
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Attributes as OA;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

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
#[Route('/api/user')]
class UserAudiobookCommentController extends AbstractController
{
    #[Route('/audiobook/comment/add', name: 'userAudiobookCommentAdd', methods: ['PUT'])]
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
        RequestServiceInterface $requestService,
        AuthorizedUserServiceInterface $authorizedUserService,
        LoggerInterface $endpointLogger,
        AudiobookRepository $audiobookRepository,
        AudiobookUserCommentRepository $audiobookUserCommentRepository,
        AudiobookInfoRepository $audiobookInfoRepository,
        TranslateServiceInterface $translateService,
        TagAwareCacheInterface $stockCache,
        UserRepository $userRepository,
        UserBanHistoryRepository $banHistoryRepository,
    ): Response {
        $userAudiobookCommentAddQuery = $requestService->getRequestBodyContent($request, UserAudiobookCommentAddQuery::class);

        if ($userAudiobookCommentAddQuery instanceof UserAudiobookCommentAddQuery) {
            $user = $authorizedUserService::getAuthorizedUser();

            $audiobook = $audiobookRepository->getAudiobookByCategoryKeyAndId($userAudiobookCommentAddQuery->getAudiobookId(), $userAudiobookCommentAddQuery->getCategoryKey());

            if ($audiobook === null) {
                $endpointLogger->error('Audiobook dont exist');
                $translateService->setPreferredLanguage($request);
                throw new DataNotFoundException([$translateService->getTranslation('AudiobookDontExists')]);
            }

            if (!$audiobook->getActive()) {
                $endpointLogger->error('Audiobook is not active');
                $translateService->setPreferredLanguage($request);
                throw new DataNotFoundException([$translateService->getTranslation('AudiobookNotActive')]);
            }

            $watchedParts = $audiobookInfoRepository->findBy([
                'audiobook' => $audiobook->getId(),
                'user'      => $user->getId(),
                'watched'   => true,
            ]);

            if (floor($audiobook->getParts() / 2) > $watchedParts) {
                $endpointLogger->error('Audiobook dont exist');
                $translateService->setPreferredLanguage($request);
                throw new DataNotFoundException([$translateService->getTranslation('AudiobookNotListened')]);
            }

            $lastUserComments = count($audiobookUserCommentRepository->getUserLastCommentsByMinutes($user, '20'));

            if ($lastUserComments > (int)$_ENV['INSTITUTION_USER_COMMENTS_LIMIT']) {
                $banPeriod = (new DateTime())->modify(BanPeriodRage::HOUR_BAN->value);

                $user
                    ->setBanned(true)
                    ->setBannedTo($banPeriod);

                $userRepository->add($user);

                $banHistoryRepository->add(new UserBanHistory($user, new DateTime(), $banPeriod, UserBanType::SPAM));

                $audiobookUserCommentRepository->setLastUserLastCommentsByMinutesToDeleted($user, '20');

                $endpointLogger->error('User got banned for to many comments in short period');
                $translateService->setPreferredLanguage($request);
                throw new DataNotFoundException([$translateService->getTranslation('ToManyUserComments')]);
            }

            $audiobookComment = new AudiobookUserComment($userAudiobookCommentAddQuery->getComment(), $audiobook, $user);

            $additionalData = $userAudiobookCommentAddQuery->getAdditionalData();

            if (array_key_exists('parentId', $additionalData)) {
                $audiobookParentComment = $audiobookUserCommentRepository->find($additionalData['parentId']);

                if ($audiobookParentComment === null || $audiobookParentComment->getParent() !== null) {
                    $endpointLogger->error('Audiobook Parent Comment dont exist');
                    $translateService->setPreferredLanguage($request);
                    throw new DataNotFoundException([$translateService->getTranslation('AudiobookParentCommentDontExists')]);
                }

                $audiobookComment->setParent($audiobookParentComment);
            }

            $audiobookUserCommentRepository->add($audiobookComment);

            $stockCache->invalidateTags([
                UserStockCacheTags::AUDIOBOOK_COMMENTS->value,
                UserStockCacheTags::USER_AUDIOBOOK_DETAIL->value . $audiobook->getId() . $user->getId(),
            ]);

            return ResponseTool::getResponse(httpCode: Response::HTTP_CREATED);
        }

        $endpointLogger->error('Invalid given Query');
        $translateService->setPreferredLanguage($request);
        throw new InvalidJsonDataException($translateService);
    }

    #[Route('/audiobook/comment/edit', name: 'userAudiobookCommentEdit', methods: ['PATCH'])]
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
        RequestServiceInterface $requestService,
        AuthorizedUserServiceInterface $authorizedUserService,
        LoggerInterface $endpointLogger,
        AudiobookRepository $audiobookRepository,
        AudiobookUserCommentRepository $audiobookUserCommentRepository,
        TranslateServiceInterface $translateService,
        TagAwareCacheInterface $stockCache,
    ): Response {
        $userAudiobookCommentEditQuery = $requestService->getRequestBodyContent($request, UserAudiobookCommentEditQuery::class);

        if ($userAudiobookCommentEditQuery instanceof UserAudiobookCommentEditQuery) {
            $user = $authorizedUserService::getAuthorizedUser();

            $audiobook = $audiobookRepository->getAudiobookByCategoryKeyAndId($userAudiobookCommentEditQuery->getAudiobookId(), $userAudiobookCommentEditQuery->getCategoryKey());

            if ($audiobook === null) {
                $endpointLogger->error('Audiobook dont exist');
                $translateService->setPreferredLanguage($request);
                throw new DataNotFoundException([$translateService->getTranslation('AudiobookDontExists')]);
            }

            if (!$audiobook->getActive()) {
                $endpointLogger->error('Audiobook is not active');
                $translateService->setPreferredLanguage($request);
                throw new DataNotFoundException([$translateService->getTranslation('AudiobookNotActive')]);
            }

            $audiobookComment = $audiobookUserCommentRepository->findOneBy([
                'id'   => $userAudiobookCommentEditQuery->getAudiobookCommentId(),
                'user' => $user->getId(),
            ]);

            if ($audiobookComment === null) {
                $endpointLogger->error('Audiobook Comment dont exist');
                $translateService->setPreferredLanguage($request);
                throw new DataNotFoundException([$translateService->getTranslation('AudiobookCommentDontExists')]);
            }

            $audiobookComment
                ->setDeleted($userAudiobookCommentEditQuery->isDeleted())
                ->setComment($userAudiobookCommentEditQuery->getComment())
                ->setEdited(true);

            $audiobookUserCommentRepository->add($audiobookComment);

            $stockCache->invalidateTags([
                UserStockCacheTags::AUDIOBOOK_COMMENTS->value,
                UserStockCacheTags::USER_AUDIOBOOK_DETAIL->value . $audiobook->getId() . $user->getId(),
            ]);

            return ResponseTool::getResponse();
        }

        $endpointLogger->error('Invalid given Query');
        $translateService->setPreferredLanguage($request);
        throw new InvalidJsonDataException($translateService);
    }

    #[Route('/audiobook/comment/like/add', name: 'userAudiobookCommentLikeAdd', methods: ['PATCH'])]
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
        RequestServiceInterface $requestService,
        AuthorizedUserServiceInterface $authorizedUserService,
        LoggerInterface $endpointLogger,
        AudiobookUserCommentLikeRepository $audiobookUserCommentLikeRepository,
        AudiobookUserCommentRepository $audiobookUserCommentRepository,
        TranslateServiceInterface $translateService,
        TagAwareCacheInterface $stockCache,
    ): Response {
        $userAudiobookCommentLikeAddQuery = $requestService->getRequestBodyContent($request, UserAudiobookCommentLikeAddQuery::class);

        if ($userAudiobookCommentLikeAddQuery instanceof UserAudiobookCommentLikeAddQuery) {
            $user = $authorizedUserService::getAuthorizedUser();

            $comment = $audiobookUserCommentRepository->find($userAudiobookCommentLikeAddQuery->getCommentId());

            if ($comment === null) {
                $endpointLogger->error('Audiobook dont exist');
                $translateService->setPreferredLanguage($request);
                throw new DataNotFoundException([$translateService->getTranslation('AudiobookCommentDontExists')]);
            }

            $commentLike = $audiobookUserCommentLikeRepository->findOneBy([
                'audiobookUserComment' => $comment->getId(),
                'user'                 => $user->getId(),
            ]);

            if ($commentLike === null) {
                $commentLike = new AudiobookUserCommentLike($userAudiobookCommentLikeAddQuery->isLike(), $comment, $user);
            } else {
                $commentLike->setLiked($userAudiobookCommentLikeAddQuery->isLike());
                if ($commentLike->getDeleted()) {
                    $commentLike->setDeleted(!$commentLike->getDeleted());
                }
            }

            $audiobookUserCommentLikeRepository->add($commentLike);
            $stockCache->invalidateTags([UserStockCacheTags::AUDIOBOOK_COMMENTS->value]);

            return ResponseTool::getResponse();
        }

        $endpointLogger->error('Invalid given Query');
        $translateService->setPreferredLanguage($request);
        throw new InvalidJsonDataException($translateService);
    }

    #[Route('/audiobook/comment/like/delete', name: 'userAudiobookCommentLikeDelete', methods: ['DELETE'])]
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
        RequestServiceInterface $requestService,
        AuthorizedUserServiceInterface $authorizedUserService,
        LoggerInterface $endpointLogger,
        AudiobookUserCommentRepository $audiobookUserCommentRepository,
        AudiobookUserCommentLikeRepository $audiobookUserCommentLikeRepository,
        TranslateServiceInterface $translateService,
        TagAwareCacheInterface $stockCache,
    ): Response {
        $userAudiobookCommentLikeDeleteQuery = $requestService->getRequestBodyContent($request, UserAudiobookCommentLikeDeleteQuery::class);

        if ($userAudiobookCommentLikeDeleteQuery instanceof UserAudiobookCommentLikeDeleteQuery) {
            $user = $authorizedUserService::getAuthorizedUser();

            $comment = $audiobookUserCommentRepository->find($userAudiobookCommentLikeDeleteQuery->getCommentId());

            if ($comment === null) {
                $endpointLogger->error('Audiobook dont exist');
                $translateService->setPreferredLanguage($request);
                throw new DataNotFoundException([$translateService->getTranslation('AudiobookCommentDontExists')]);
            }

            $commentLike = $audiobookUserCommentLikeRepository->findOneBy([
                'audiobookUserComment' => $comment->getId(),
                'user'                 => $user->getId(),
                'deleted'              => false,
            ]);

            if ($commentLike === null) {
                $endpointLogger->error('Comment like dont exist');
                $translateService->setPreferredLanguage($request);
                throw new DataNotFoundException([$translateService->getTranslation('AudiobookCommentLikeDontExists')]);
            }

            $commentLike->setDeleted(true);

            $audiobookUserCommentLikeRepository->add($commentLike);
            $stockCache->invalidateTags([UserStockCacheTags::AUDIOBOOK_COMMENTS->value]);

            return ResponseTool::getResponse();
        }

        $endpointLogger->error('Invalid given Query');
        $translateService->setPreferredLanguage($request);
        throw new InvalidJsonDataException($translateService);
    }

    #[Route('/audiobook/comment/get', name: 'userAudiobookCommentGet', methods: ['POST'])]
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
        RequestServiceInterface $requestService,
        AuthorizedUserServiceInterface $authorizedUserService,
        LoggerInterface $endpointLogger,
        AudiobookUserCommentRepository $audiobookUserCommentRepository,
        AudiobookUserCommentLikeRepository $audiobookUserCommentLikeRepository,
        AudiobookRepository $audiobookRepository,
        TranslateServiceInterface $translateService,
        TagAwareCacheInterface $stockCache,
    ): Response {

        $userAudiobookCommentGetQuery = $requestService->getRequestBodyContent($request, UserAudiobookCommentGetQuery::class);

        if ($userAudiobookCommentGetQuery instanceof UserAudiobookCommentGetQuery) {
            $user = $authorizedUserService::getAuthorizedUser();

            $audiobook = $audiobookRepository->getAudiobookByCategoryKeyAndId($userAudiobookCommentGetQuery->getAudiobookId(), $userAudiobookCommentGetQuery->getCategoryKey());

            if ($audiobook === null) {
                $endpointLogger->error('Audiobook dont exist');
                $translateService->setPreferredLanguage($request);
                throw new DataNotFoundException([$translateService->getTranslation('AudiobookCommentDontExists')]);
            }

            if (!$audiobook->getActive()) {
                $endpointLogger->error('Audiobook is not active');
                $translateService->setPreferredLanguage($request);
                throw new DataNotFoundException([$translateService->getTranslation('AudiobookNotActive')]);
            }

            $successModel = $stockCache->get(UserCacheKeys::USER_AUDIOBOOK_COMMENTS->value . $user->getId() . '_' . $audiobook->getId(), function (ItemInterface $item) use ($user, $audiobook, $audiobookUserCommentLikeRepository, $audiobookUserCommentRepository) {
                $item->expiresAfter(CacheValidTime::FIVE_MINUTES->value);
                $item->tag(UserStockCacheTags::AUDIOBOOK_COMMENTS->value);

                $audiobookUserComments = $audiobookUserCommentRepository->findBy([
                    'parent'    => null,
                    'audiobook' => $audiobook->getId(),
                    'deleted'   => false,
                ]);

                $treeGenerator = new BuildAudiobookCommentTreeGenerator($audiobookUserComments, $audiobookUserCommentRepository, $audiobookUserCommentLikeRepository, $user, false);

                return new AudiobookCommentsSuccessModel($treeGenerator->generate());
            });

            return ResponseTool::getResponse($successModel);
        }

        $endpointLogger->error('Invalid given Query');
        $translateService->setPreferredLanguage($request);
        throw new InvalidJsonDataException($translateService);
    }
}
