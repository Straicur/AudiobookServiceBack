<?php

namespace App\Tests;

use App\Builder\NotificationBuilder;
use App\Entity\Audiobook;
use App\Entity\AudiobookCategory;
use App\Entity\AudiobookInfo;
use App\Entity\AudiobookRating;
use App\Entity\AudiobookUserComment;
use App\Entity\AudiobookUserCommentLike;
use App\Entity\AuthenticationToken;
use App\Entity\Institution;
use App\Entity\MyList;
use App\Entity\Notification;
use App\Entity\NotificationCheck;
use App\Entity\ProposedAudiobooks;
use App\Entity\RegisterCode;
use App\Entity\User;
use App\Entity\UserDelete;
use App\Entity\UserEdit;
use App\Entity\UserInformation;
use App\Entity\UserPassword;
use App\Entity\UserSettings;
use App\Enums\AudiobookAgeRange;
use App\Enums\NotificationType;
use App\Enums\NotificationUserType;
use App\Exception\NotificationException;
use App\Repository\AudiobookCategoryRepository;
use App\Repository\AudiobookInfoRepository;
use App\Repository\AudiobookRatingRepository;
use App\Repository\AudiobookRepository;
use App\Repository\AudiobookUserCommentLikeRepository;
use App\Repository\AudiobookUserCommentRepository;
use App\Repository\AuthenticationTokenRepository;
use App\Repository\InstitutionRepository;
use App\Repository\MyListRepository;
use App\Repository\NotificationCheckRepository;
use App\Repository\NotificationRepository;
use App\Repository\ProposedAudiobooksRepository;
use App\Repository\RegisterCodeRepository;
use App\Repository\RoleRepository;
use App\Repository\UserDeleteRepository;
use App\Repository\UserEditRepository;
use App\Repository\UserInformationRepository;
use App\Repository\UserPasswordRepository;
use App\Repository\UserRepository;
use App\Repository\UserSettingsRepository;
use App\ValueGenerator\AuthTokenGenerator;
use App\ValueGenerator\CategoryKeyGenerator;
use App\ValueGenerator\PasswordHashGenerator;
use App\ValueGenerator\RegisterCodeGenerator;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Uid\Uuid;

class DatabaseMockManager
{
    private KernelInterface $kernel;

    public function __construct(KernelInterface $kernel)
    {
        $this->kernel = $kernel;
    }

    protected function getService(string $serviceName): object
    {
        return $this->kernel->getContainer()->get($serviceName);
    }

    private function testFunc_addRole(User $user, array $rolesNames): void
    {
        $roleRepository = $this->getService(RoleRepository::class);

        $roles = $roleRepository->findBy([
            "name" => $rolesNames
        ]);

        foreach ($roles as $role) {
            $role->addUser($user);
            $roleRepository->add($role);
        }
    }

    public function testFunc_addUser(string $firstname, string $lastname, string $email, string $phone, array $rolesNames = [], bool $mainGroup = false, string $password = null, \DateTime $addedDate = null, bool $banned = false, bool $notActive = false, bool $edited = false, \DateTime $editableDate = null, \DateTime $bannedTo = null): User
    {
        $userRepository = $this->getService(UserRepository::class);
        $userPasswordRepository = $this->getService(UserPasswordRepository::class);
        $myListRepository = $this->getService(MyListRepository::class);
        $proposedAudiobooksRepository = $this->getService(ProposedAudiobooksRepository::class);
        $userInformationRepository = $this->getService(UserInformationRepository::class);
        $userSettingsRepository = $this->getService(UserSettingsRepository::class);

        $user = new User();

        if ($banned) {
            $user->setBanned(true);
        }

        if ($notActive) {
            $user->setActive(false);
        } else {
            $user->setActive(true);
        }

        if ($edited) {
            $user->setEdited($edited);
        }

        if ($editableDate != null) {
            $user->setEditableDate($editableDate);
        }

        if ($addedDate != null) {
            $user->setDateCreate($addedDate);
        }
        
        if ($bannedTo != null) {
            $user->setBannedTo($bannedTo);
        }

        $userRepository->add($user, false);

        $userProposedAudiobooks = new ProposedAudiobooks($user);

        $proposedAudiobooksRepository->add($userProposedAudiobooks);

        $userInformationEntity = new UserInformation($user, $email, $phone, $firstname, $lastname);

        $userInformationRepository->add($userInformationEntity, false);

        $userSettingsEntity = new UserSettings($user);

        $userSettingsRepository->add($userSettingsEntity, false);

        $userMyList = new MyList($user);

        $myListRepository->add($userMyList);

        $this->testFunc_addRole($user, $rolesNames);

        if ($password != null) {
            $userPassword = new UserPassword($user, new PasswordHashGenerator($password));
            $userPasswordRepository->add($userPassword);
        }

        return $userRepository->findOneBy(["id" => $user->getId()]);
    }

    public function testFunc_loginUser(User $user, \DateTime $dateEnd = null): AuthenticationToken
    {
        $authenticationTokenRepository = $this->getService(AuthenticationTokenRepository::class);

        $authenticationToken = new AuthenticationToken($user, new AuthTokenGenerator($user));

        if ($dateEnd != null) {
            $authenticationToken->setDateExpired($dateEnd);
        }

        $authenticationTokenRepository->add($authenticationToken);

        return $authenticationTokenRepository->findOneBy(["id" => $authenticationToken->getId()]);
    }

    public function testFunc_addRegisterCode(User $user, \DateTime $dateAccept = null, bool $active = false, string $code = null): RegisterCode
    {
        $registerCodeRepository = $this->getService(RegisterCodeRepository::class);

        if ($code) {
            $registerCodeGenerator = new RegisterCodeGenerator($code);
        } else {
            $registerCodeGenerator = new RegisterCodeGenerator();
        }

        $newRegisterCode = new RegisterCode($registerCodeGenerator, $user);

        if ($dateAccept != null) {
            $newRegisterCode->setDateAccept($dateAccept);
        }
        if ($active) {
            $newRegisterCode->setActive($active);
        }

        $registerCodeRepository->add($newRegisterCode);

        return $newRegisterCode;
    }

    public function testFunc_getInstitution(): Institution
    {
        return $this->getService(InstitutionRepository::class)->findOneBy([
            "name" => $_ENV["INSTITUTION_NAME"]
        ]);
    }

    public function testFunc_addAudiobook(string $title, string $author, string $version, string $album, \DateTime $year, int $duration, string $size, int $parts, string $description, AudiobookAgeRange $age, string $fileName, array $categories, string $encoded = null, \DateTime $dateAdd = null, bool $active = false, float $rating = null): Audiobook
    {
        $audiobookRepository = $this->getService(AudiobookRepository::class);

        $newAudiobook = new Audiobook($title, $author, $version, $album, $year, $duration, $size, $parts, $description, $age, $fileName);

        if ($encoded != null) {
            $newAudiobook->setEncoded($encoded);
        }

        if ($dateAdd != null) {
            $newAudiobook->setDateAdd($dateAdd);
        }

        if ($active) {
            $newAudiobook->setActive($active);
        }

        if ($rating != null) {
            $newAudiobook->setAvgRating($rating);
        }

        foreach ($categories as $category) {
            $newAudiobook->addCategory($category);
        }

        $audiobookRepository->add($newAudiobook);

        return $newAudiobook;
    }

    public function testFunc_addAudiobookCategory(string $name, AudiobookCategory $parent = null, bool $active = false): AudiobookCategory
    {
        $audiobookCategoryRepository = $this->getService(AudiobookCategoryRepository::class);

        $categoryKeyGenerator = new CategoryKeyGenerator();

        $newAudiobookCategory = new AudiobookCategory($name, $categoryKeyGenerator);

        if ($parent != null) {
            $newAudiobookCategory->setParent($parent);
        }

        if ($active) {
            $newAudiobookCategory->setActive(false);
        } else {
            $newAudiobookCategory->setActive(true);
        }

        $audiobookCategoryRepository->add($newAudiobookCategory);

        return $newAudiobookCategory;
    }

    public function testFunc_addAudiobookInfo(User $user, Audiobook $audiobook, int $part, int $endedTime, bool $deActive = false, bool $watched = false): AudiobookInfo
    {
        $registerCodeRepository = $this->getService(AudiobookInfoRepository::class);

        $newRegisterCode = new AudiobookInfo($user, $audiobook, $part, $endedTime, $watched);

        if ($watched) {
            $newRegisterCode->setWatched($watched);
        }

        if ($deActive) {
            $newRegisterCode->setActive(false);
        }

        $registerCodeRepository->add($newRegisterCode);

        return $newRegisterCode;
    }

    public function testFunc_addMyList(User $user, Audiobook $audiobook): void
    {
        $myListRepository = $this->getService(MyListRepository::class);

        $myList = $user->getMyList();

        $myList->addAudiobook($audiobook);

        $myListRepository->add($myList);
    }

    public function testFunc_addProposedAudiobooks(User $user, Audiobook $audiobook): void
    {
        $proposedAudiobooksRepository = $this->getService(ProposedAudiobooksRepository::class);

        $proposedAudiobooks = $user->getProposedAudiobooks();

        $proposedAudiobooks->addAudiobook($audiobook);

        $proposedAudiobooksRepository->add($proposedAudiobooks);
    }

    public function testFunc_addUserDelete(User $user, bool $deleted = false, bool $declined = false, \DateTime $dateDeleted = null): UserDelete
    {
        $userDeleteRepository = $this->getService(UserDeleteRepository::class);

        $newUserDelete = new UserDelete($user);

        if ($deleted) {
            $newUserDelete->setDeleted($deleted);
        }
        if ($declined) {
            $newUserDelete->setDeclined($declined);
        }
        if ($dateDeleted != null) {
            $newUserDelete->setDateDeleted($dateDeleted);
        }

        $userDeleteRepository->add($newUserDelete);

        return $newUserDelete;
    }

    /**
     * @param User[] $users
     * @param NotificationType $notificationType
     * @param Uuid $actionId
     * @param NotificationUserType $userAction
     * @param string|null $text
     * @param bool $deleted
     * @param string|null $categoryKey
     * @return Notification
     * @throws NotificationException
     */
    public function testFunc_addNotifications(array $users, NotificationType $notificationType, Uuid $actionId, NotificationUserType $userAction, ?string $text = null, bool $deleted = false, ?string $categoryKey = null): Notification
    {
        $systemNotificationRepository = $this->getService(NotificationRepository::class);

        $newSystemNotification = new NotificationBuilder();

        $newSystemNotification = $newSystemNotification->setUserAction($userAction)
            ->setAction($actionId)
            ->setType($notificationType);

        foreach ($users as $user) {
            $newSystemNotification = $newSystemNotification->addUser($user);
        }

        if ($text != null) {
            $newSystemNotification = $newSystemNotification->setText($text);
        }

        if ($categoryKey != null) {
            $newSystemNotification = $newSystemNotification->setCategoryKey($categoryKey);
        }

        $newSystemNotification = $newSystemNotification->build();

        if ($deleted) {
            $newSystemNotification = $newSystemNotification->setDeleted($deleted);
        }

        $systemNotificationRepository->add($newSystemNotification);

        return $newSystemNotification;
    }

    /**
     * @param User $user
     * @param Notification $notification
     * @return NotificationCheck
     */
    public function testFunc_addNotificationCheck(User $user, Notification $notification): NotificationCheck
    {
        $notificationCheckRepository = $this->getService(NotificationCheckRepository::class);

        $newNotificationCheck = new NotificationCheck($user, $notification);

        $notificationCheckRepository->add($newNotificationCheck);

        return $newNotificationCheck;
    }

    public function testFunc_addAudiobookRating(Audiobook $audiobook, bool $rating, User $user): AudiobookRating
    {
        $audiobookRatingRepository = $this->getService(AudiobookRatingRepository::class);

        $newAudiobookRating = new AudiobookRating($audiobook, $rating, $user);

        $audiobookRatingRepository->add($newAudiobookRating);

        return $newAudiobookRating;
    }

    public function testFunc_addAudiobookUserComment(string $comment, Audiobook $audiobook, User $user, ?AudiobookUserComment $parent = null, bool $deleted = false, bool $edited = false): AudiobookUserComment
    {
        $audiobookUserCommentRepository = $this->getService(AudiobookUserCommentRepository::class);

        $newAudiobookUserComment = new AudiobookUserComment($comment, $audiobook, $user);

        if ($parent != null) {
            $newAudiobookUserComment->setParent($parent);
        }

        if ($deleted) {
            $newAudiobookUserComment->setDeleted($deleted);
        }

        if ($edited) {
            $newAudiobookUserComment->setEdited($edited);
        }

        $audiobookUserCommentRepository->add($newAudiobookUserComment);

        return $newAudiobookUserComment;
    }

    public function testFunc_addAudiobookUserCommentLike(bool $liked, AudiobookUserComment $audiobookUserComment, User $user, bool $deleted = false): AudiobookUserCommentLike
    {
        $audiobookUserCommentLikeRepository = $this->getService(AudiobookUserCommentLikeRepository::class);

        $newAudiobookUserCommentLike = new AudiobookUserCommentLike($liked, $audiobookUserComment, $user);

        if ($deleted) {
            $newAudiobookUserCommentLike->setDeleted($deleted);
        }

        $audiobookUserCommentLikeRepository->add($newAudiobookUserCommentLike);

        return $newAudiobookUserCommentLike;
    }

    public function testFunc_addUserEdit(User $user, bool $edited, int $type, ?\DateTime $editableDate = null): UserEdit
    {
        $userEditRepository = $this->getService(UserEditRepository::class);

        $newUserEdit = new UserEdit($user, $edited, $type);

        if ($editableDate != null) {
            $newUserEdit->setEditableDate($editableDate);
        }

        $userEditRepository->add($newUserEdit);

        return $newUserEdit;
    }
}
