<?php

declare(strict_types=1);

namespace App\Tests\Controller\AdminAudiobookController;

use App\Enums\AudiobookAgeRange;
use App\Repository\AudiobookRepository;
use App\Tests\AbstractWebTest;
use DateTime;

class AdminAudiobookEditTest extends AbstractWebTest
{
    /**
     * step 1 - Preparing data
     * step 2 - Preparing JsonBodyContent
     * step 3 - Sending Request
     * step 4 - Checking response
     * step 5 - Checking response if category is active
     * @return void
     */
    public function test_adminAudiobookEditCorrect(): void
    {
        $audiobookRepository = $this->getService(AudiobookRepository::class);

        $this->assertInstanceOf(AudiobookRepository::class, $audiobookRepository);

        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx');

        $category1 = $this->databaseMockManager->testFunc_addAudiobookCategory('1', null, true);
        $category2 = $this->databaseMockManager->testFunc_addAudiobookCategory('2', $category1);

        $audiobook1 = $this->databaseMockManager->testFunc_addAudiobook('t', 'a', '2', 'd', new DateTime(), 20, '20', 2, 'desc', AudiobookAgeRange::ABOVE18, 'd', [$category1, $category2], null, (new DateTime())->modify('- 1 month'), active: true);
        $this->databaseMockManager->testFunc_addAudiobook('t', 'a', '2', 'd', new DateTime(), 20, '20', 2, 'desc', AudiobookAgeRange::ABOVE18, 'd1', [$category2], active: true);

        $content = [
            'audiobookId' => $audiobook1->getId(),
            'title' => 'fsafsa',
            'author' => 'dsafeafas',
            'version' => '3.0',
            'album' => 'Krlowa nieg',
            'year' => '27.11.2022',
            'duration' => 0,
            'size' => '30.40',
            'parts' => 3,
            'description' => 'engiTunPGAP0',
            'age' => AudiobookAgeRange::FROM7TO12->value,
            'encoded' => '2XD',
        ];
        $token = $this->databaseMockManager->testFunc_loginUser($user);

        self::$webClient->request('PATCH', '/api/admin/audiobook/edit', server: [
            'HTTP_authorization' => $token->getToken()
        ], content: json_encode($content));

        self::assertResponseIsSuccessful();
        self::assertResponseStatusCodeSame(200);

        $audiobook1After = $audiobookRepository->findOneBy([
            'id' => $audiobook1->getId()
        ]);

        $this->assertSame($audiobook1After->getTitle(), $content['title']);
        $this->assertSame($audiobook1After->getAuthor(), $content['author']);
        $this->assertSame($audiobook1After->getVersion(), $content['version']);
        $this->assertSame($audiobook1After->getAlbum(), $content['album']);
        $this->assertSame($audiobook1After->getYear()->format('d.m.Y'), $content['year']);
        $this->assertSame($audiobook1After->getDuration(), $content['duration']);
        $this->assertSame($audiobook1After->getSize(), $content['size']);
        $this->assertSame($audiobook1After->getParts(), $content['parts']);
        $this->assertSame($audiobook1After->getDescription(), $content['description']);
        $this->assertSame($audiobook1After->getAge()->value, $content['age']);
    }

    /**
     * step 1 - Preparing data
     * step 2 - Preparing JsonBodyContent with bad audiobookId
     * step 3 - Sending Request
     * step 4 - Checking response
     *
     * @return void
     */
    public function test_adminAudiobookEditIncorrectAudiobookId(): void
    {
        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx');

        $category1 = $this->databaseMockManager->testFunc_addAudiobookCategory('1');
        $category2 = $this->databaseMockManager->testFunc_addAudiobookCategory('2', $category1);
        $category3 = $this->databaseMockManager->testFunc_addAudiobookCategory('3', $category1);
        $this->databaseMockManager->testFunc_addAudiobookCategory('4', $category3);
        $this->databaseMockManager->testFunc_addAudiobookCategory('5', $category2, true);
        $this->databaseMockManager->testFunc_addAudiobook('t', 'a', '2', 'd', new DateTime(), 20, '20', 2, 'desc', AudiobookAgeRange::ABOVE18, 'd', [$category1,
            $category2], null, (new DateTime())->modify('- 1 month'), active: true);
        $this->databaseMockManager->testFunc_addAudiobook('t', 'a', '2', 'd', new DateTime(), 20, '20', 2, 'desc', AudiobookAgeRange::ABOVE18, 'd1', [$category2], active: true);
        $token = $this->databaseMockManager->testFunc_loginUser($user);

        $content = [
            'audiobookId' => '66666c4e-16e6-1ecc-9890-a7e8b0073d3b',
            'title' => 'ty',
            'author' => 'au',
            'version' => '3',
            'album' => 'da',
            'year' => '27.11.2022',
            'duration' => '30',
            'size' => '30',
            'parts' => '3',
            'description' => 'Desc',
            'age' => AudiobookAgeRange::FROM7TO12->value,
            'encoded' => '2',
        ];

        self::$webClient->request('PATCH', '/api/admin/audiobook/edit', server: [
            'HTTP_authorization' => $token->getToken()
        ], content: json_encode($content));

        self::assertResponseStatusCodeSame(404);

        $this->responseTool->testErrorResponseData(self::$webClient);
    }

    public function test_adminAudiobookEditEmptyRequestData(): void
    {
        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx');

        $category1 = $this->databaseMockManager->testFunc_addAudiobookCategory('1');
        $category2 = $this->databaseMockManager->testFunc_addAudiobookCategory('2', $category1);
        $category3 = $this->databaseMockManager->testFunc_addAudiobookCategory('3', $category1);
        $this->databaseMockManager->testFunc_addAudiobookCategory('4', $category3);
        $this->databaseMockManager->testFunc_addAudiobookCategory('5', $category2, true);
        $this->databaseMockManager->testFunc_addAudiobook('t', 'a', '2', 'd', new DateTime(), 20, '20', 2, 'desc', AudiobookAgeRange::ABOVE18, 'd', [$category1,
            $category2], null, (new DateTime())->modify('- 1 month'), active: true);
        $this->databaseMockManager->testFunc_addAudiobook('t', 'a', '2', 'd', new DateTime(), 20, '20', 2, 'desc', AudiobookAgeRange::ABOVE18, 'd1', [$category2], active: true);
        $token = $this->databaseMockManager->testFunc_loginUser($user);

        $content = [];

        self::$webClient->request('PATCH', '/api/admin/audiobook/edit', server: [
            'HTTP_authorization' => $token->getToken()
        ], content: json_encode($content));

        self::assertResponseStatusCodeSame(400);

        $this->responseTool->testBadResponseData(self::$webClient);
    }

    public function test_adminAudiobookEditPermission(): void
    {
        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest', 'User'], true, 'zaq12wsx');

        $category1 = $this->databaseMockManager->testFunc_addAudiobookCategory('1');
        $category2 = $this->databaseMockManager->testFunc_addAudiobookCategory('2', $category1);
        $category3 = $this->databaseMockManager->testFunc_addAudiobookCategory('3', $category1);
        $this->databaseMockManager->testFunc_addAudiobookCategory('4', $category3);
        $this->databaseMockManager->testFunc_addAudiobookCategory('5', $category2, true);
        $this->databaseMockManager->testFunc_addAudiobook('t', 'a', '2', 'd', new DateTime(), 20, '20', 2, 'desc', AudiobookAgeRange::ABOVE18, 'd', [$category1,
            $category2], null, (new DateTime())->modify('- 1 month'), active: true);
        $this->databaseMockManager->testFunc_addAudiobook('t', 'a', '2', 'd', new DateTime(), 20, '20', 2, 'desc', AudiobookAgeRange::ABOVE18, 'd1', [$category2], active: true);
        $token = $this->databaseMockManager->testFunc_loginUser($user);

        $content = [
            'audiobookId' => $category1->getId(),
            'title' => 'ty',
            'author' => 'au',
            'version' => '3',
            'album' => 'da',
            'year' => '27.11.2022',
            'duration' => '30',
            'size' => '30',
            'parts' => '3',
            'description' => 'Desc',
            'age' => AudiobookAgeRange::FROM7TO12->value,
            'encoded' => '2',
        ];

        self::$webClient->request('PATCH', '/api/admin/audiobook/edit', server: [
            'HTTP_authorization' => $token->getToken()
        ], content: json_encode($content));

        self::assertResponseStatusCodeSame(403);

        $this->responseTool->testBadResponseData(self::$webClient);
    }

    public function test_adminAudiobookEditLogOut(): void
    {
        $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest',
            'User',
            'Administrator'], true, 'zaq12wsx');

        $category1 = $this->databaseMockManager->testFunc_addAudiobookCategory('1');
        $category2 = $this->databaseMockManager->testFunc_addAudiobookCategory('2', $category1);
        $category3 = $this->databaseMockManager->testFunc_addAudiobookCategory('3', $category1);
        $this->databaseMockManager->testFunc_addAudiobookCategory('4', $category3);
        $this->databaseMockManager->testFunc_addAudiobookCategory('5', $category2, true);
        $this->databaseMockManager->testFunc_addAudiobook('t', 'a', '2', 'd', new DateTime(), 20, '20', 2, 'desc', AudiobookAgeRange::ABOVE18, 'd', [$category1,
            $category2], null, (new DateTime())->modify('- 1 month'), active: true);
        $this->databaseMockManager->testFunc_addAudiobook('t', 'a', '2', 'd', new DateTime(), 20, '20', 2, 'desc', AudiobookAgeRange::ABOVE18, 'd1', [$category2], active: true);

        $content = [
            'audiobookId' => $category1->getId(),
            'title' => 'fsafsa',
            'author' => 'dsafeafas',
            'version' => '3.0',
            'album' => 'Krlowa nieg',
            'year' => '27.11.2022',
            'duration' => '0',
            'size' => '30.40',
            'parts' => 3,
            'description' => 'engiTunPGAP0',
            'age' => AudiobookAgeRange::FROM7TO12->value,
            'encoded' => '2XD',
        ];

        self::$webClient->request('PATCH', '/api/admin/audiobook/edit', content: json_encode($content));

        self::assertResponseStatusCodeSame(401);

        $this->responseTool->testBadResponseData(self::$webClient);
    }
}
