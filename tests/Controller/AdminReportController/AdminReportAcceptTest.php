<?php

namespace App\Tests\Controller\AdminReportController;

use App\Enums\AudiobookAgeRange;
use App\Enums\ReportType;
use App\Repository\ReportRepository;
use App\Repository\UserRepository;
use App\Tests\AbstractWebTest;

/**
 * adminAcceptTest
 */
class AdminReportAcceptTest extends AbstractWebTest
{
    /**
     * step 1 - Preparing data
     * step 2 - Sending Request
     * step 3 - Checking response
     * step 4 - Checking response
     * @return void
     */
    public function test_adminAcceptCorrect(): void
    {
        $reportRepository = $this->getService(ReportRepository::class);

        $this->assertInstanceOf(ReportRepository::class, $reportRepository);

        /// step 1
        $user = $this->databaseMockManager->testFunc_addUser("User", "Test", "test@cos.pl", "+48123123123", ["Guest", "User", "Administrator"], true, "zaq12wsx");

        $category1 = $this->databaseMockManager->testFunc_addAudiobookCategory("1");
        $category2 = $this->databaseMockManager->testFunc_addAudiobookCategory("2", $category1);

        $audiobook1 = $this->databaseMockManager->testFunc_addAudiobook("t", "a", "2", "d", new \DateTime("Now"), "20", "20", 2, "desc", AudiobookAgeRange::ABOVE18, "d1", [$category1, $category2], active: true);

        $comment1 = $this->databaseMockManager->testFunc_addAudiobookUserComment("comment1", $audiobook1, $user);
        $report = $this->databaseMockManager->testFunc_addReport(ReportType::COMMENT, ip: "127.0.0.1");

        $token = $this->databaseMockManager->testFunc_loginUser($user);
        /// step 2
        $content = [
            "reportId" => $report->getId(),
            "banPeriod" => 2,
        ];
        /// step 2
        $crawler = self::$webClient->request("PATCH", "/api/report/admin/accept", server: [
            "HTTP_authorization" => $token->getToken()
        ], content: json_encode($content));

        /// step 3
        self::assertResponseIsSuccessful();
        self::assertResponseStatusCodeSame(200);

        /// step 5
        $acceptedAfter = $reportRepository->findOneBy([
            "id" => $report->getId()
        ]);
        $this->assertNotNull($acceptedAfter);
        $this->assertTrue($acceptedAfter->getAccepted());
    }

    /**
     * step 1 - Preparing data
     * step 2 - Sending Request
     * step 3 - Checking response
     * step 4 - Checking response
     * @return void
     */
    public function test_adminAcceptSystemBanCorrect(): void
    {
        $reportRepository = $this->getService(ReportRepository::class);
        $userRepository = $this->getService(UserRepository::class);

        $this->assertInstanceOf(UserRepository::class, $userRepository);
        $this->assertInstanceOf(ReportRepository::class, $reportRepository);

        /// step 1
        $user = $this->databaseMockManager->testFunc_addUser("User", "Test", "test@cos.pl", "+48123123123", ["Guest", "User", "Administrator"], true, "zaq12wsx");

        $category1 = $this->databaseMockManager->testFunc_addAudiobookCategory("1");
        $category2 = $this->databaseMockManager->testFunc_addAudiobookCategory("2", $category1);

        $audiobook1 = $this->databaseMockManager->testFunc_addAudiobook("t", "a", "2", "d", new \DateTime("Now"), "20", "20", 2, "desc", AudiobookAgeRange::ABOVE18, "d1", [$category1, $category2], active: true);

        $comment1 = $this->databaseMockManager->testFunc_addAudiobookUserComment("comment1", $audiobook1, $user);
        $report = $this->databaseMockManager->testFunc_addReport(ReportType::COMMENT, ip: "127.0.0.1",actionId: $comment1->getId());
        $newDate = new \DateTime("Now");

        $userBanHistory = $this->databaseMockManager->testFunc_addUserBanHistory($user, (clone $newDate)->modify('-11 month'), (clone $newDate)->modify('-10 month'));

        $token = $this->databaseMockManager->testFunc_loginUser($user);
        /// step 2
        $content = [
            "reportId" => $report->getId(),
            "banPeriod" => 1,
        ];
        /// step 2
        $crawler = self::$webClient->request("PATCH", "/api/report/admin/accept", server: [
            "HTTP_authorization" => $token->getToken()
        ], content: json_encode($content));

        /// step 3
        self::assertResponseIsSuccessful();
        self::assertResponseStatusCodeSame(200);

        /// step 5
        $acceptedAfter = $reportRepository->findOneBy([
            "id" => $report->getId()
        ]);
        $this->assertNotNull($acceptedAfter);
        $this->assertTrue($acceptedAfter->getAccepted());

        $userAfter = $userRepository->findOneBy([
            "id" => $user->getId()
        ]);

        $this->assertTrue($userAfter->isBanned());
        $this->assertTrue($userAfter->getBannedTo() > $newDate);

    }

    /**
     * step 1 - Preparing data
     * step 2 - Preparing JsonBodyContent with bad reportId
     * step 3 - Sending Request
     * step 4 - Checking response
     *
     * @return void
     */
    public function test_adminAcceptIncorrectReportId(): void
    {
        /// step 1
        $user = $this->databaseMockManager->testFunc_addUser("User", "Test", "test@cos.pl", "+48123123123", ["Guest", "User", "Administrator"], true, "zaq12wsx");

        $category1 = $this->databaseMockManager->testFunc_addAudiobookCategory("1");
        $category2 = $this->databaseMockManager->testFunc_addAudiobookCategory("2", $category1);

        $audiobook1 = $this->databaseMockManager->testFunc_addAudiobook("t", "a", "2", "d", new \DateTime("Now"), "20", "20", 2, "desc", AudiobookAgeRange::ABOVE18, "d1", [$category1, $category2], active: true);

        $comment1 = $this->databaseMockManager->testFunc_addAudiobookUserComment("comment1", $audiobook1, $user);

        $token = $this->databaseMockManager->testFunc_loginUser($user);
        /// step 2
        $content = [
            "reportId" => "66666c4e-16e6-1ecc-9890-a7e8b0073d3b",
            "banPeriod" => 1,
        ];

        /// step 2
        $crawler = self::$webClient->request("PATCH", "/api/report/admin/accept", server: [
            "HTTP_authorization" => $token->getToken()
        ], content: json_encode($content));

        /// step 4
        self::assertResponseStatusCodeSame(404);

        $responseContent = self::$webClient->getResponse()->getContent();

        $this->assertNotNull($responseContent);
        $this->assertNotEmpty($responseContent);
        $this->assertJson($responseContent);

        $responseContent = json_decode($responseContent, true);

        $this->assertIsArray($responseContent);
        $this->assertArrayHasKey("error", $responseContent);
        $this->assertArrayHasKey("data", $responseContent);
    }

    /**
     * step 1 - Preparing data
     * step 2 - Sending Request without content
     * step 3 - Checking response
     *
     * @return void
     */
    public function test_adminAcceptEmptyRequestData(): void
    {
        /// step 1
        $user = $this->databaseMockManager->testFunc_addUser("User", "Test", "test@cos.pl", "+48123123123", ["Guest", "User", "Administrator"], true, "zaq12wsx");

        $category1 = $this->databaseMockManager->testFunc_addAudiobookCategory("1");
        $category2 = $this->databaseMockManager->testFunc_addAudiobookCategory("2", $category1);

        $audiobook1 = $this->databaseMockManager->testFunc_addAudiobook("t", "a", "2", "d", new \DateTime("Now"), "20", "20", 2, "desc", AudiobookAgeRange::ABOVE18, "d1", [$category1, $category2], active: true);

        $comment1 = $this->databaseMockManager->testFunc_addAudiobookUserComment("comment1", $audiobook1, $user);

        $token = $this->databaseMockManager->testFunc_loginUser($user);
        /// step 2
        $content = [];

        /// step 2
        $crawler = self::$webClient->request("PATCH", "/api/report/admin/accept", server: [
            "HTTP_authorization" => $token->getToken()
        ], content: json_encode($content));

        /// step 3
        self::assertResponseStatusCodeSame(400);

        $responseContent = self::$webClient->getResponse()->getContent();

        $this->assertNotNull($responseContent);
        $this->assertNotEmpty($responseContent);
        $this->assertJson($responseContent);

        $responseContent = json_decode($responseContent, true);

        $this->assertIsArray($responseContent);
        $this->assertArrayHasKey("error", $responseContent);
    }

    /**
     * step 1 - Preparing data
     * step 2 - Sending Request with bad permission
     * step 3 - Checking response
     *
     * @return void
     */
    public function test_adminAcceptPermission(): void
    {
        /// step 1
        $user = $this->databaseMockManager->testFunc_addUser("User", "Test", "test@cos.pl", "+48123123123", ["Guest"], true, "zaq12wsx");

        $category1 = $this->databaseMockManager->testFunc_addAudiobookCategory("1");
        $category2 = $this->databaseMockManager->testFunc_addAudiobookCategory("2", $category1);

        $audiobook1 = $this->databaseMockManager->testFunc_addAudiobook("t", "a", "2", "d", new \DateTime("Now"), "20", "20", 2, "desc", AudiobookAgeRange::ABOVE18, "d1", [$category1, $category2], active: true);

        $comment1 = $this->databaseMockManager->testFunc_addAudiobookUserComment("comment1", $audiobook1, $user);
        $report = $this->databaseMockManager->testFunc_addReport(ReportType::COMMENT, accepted: true, ip: "127.0.0.1");

        $token = $this->databaseMockManager->testFunc_loginUser($user);
        /// step 2
        $content = [
            "reportId" => $report->getId()
        ];
        /// step 2
        $crawler = self::$webClient->request("PATCH", "/api/report/admin/accept", server: [
            "HTTP_authorization" => $token->getToken()
        ], content: json_encode($content));

        /// step 3
        self::assertResponseStatusCodeSame(403);

        $responseContent = self::$webClient->getResponse()->getContent();

        $this->assertNotNull($responseContent);
        $this->assertNotEmpty($responseContent);
        $this->assertJson($responseContent);

        $responseContent = json_decode($responseContent, true);

        $this->assertIsArray($responseContent);
        $this->assertArrayHasKey("error", $responseContent);
    }

    /**
     * step 1 - Preparing data
     * step 2 - Sending Request without token
     * step 3 - Checking response
     *
     * @return void
     */
    public function test_adminAcceptLogOut(): void
    {
        /// step 1
        $user = $this->databaseMockManager->testFunc_addUser("User", "Test", "test@cos.pl", "+48123123123", ["Guest", "User", "Administrator"], true, "zaq12wsx");

        $category1 = $this->databaseMockManager->testFunc_addAudiobookCategory("1");
        $category2 = $this->databaseMockManager->testFunc_addAudiobookCategory("2", $category1);

        $audiobook1 = $this->databaseMockManager->testFunc_addAudiobook("t", "a", "2", "d", new \DateTime("Now"), "20", "20", 2, "desc", AudiobookAgeRange::ABOVE18, "d1", [$category1, $category2], active: true);

        $comment1 = $this->databaseMockManager->testFunc_addAudiobookUserComment("comment1", $audiobook1, $user);
        $report = $this->databaseMockManager->testFunc_addReport(ReportType::COMMENT, accepted: true, ip: "127.0.0.1");

        /// step 2
        $content = [
            "reportId" => $report->getId()
        ];
        /// step 2
        $crawler = self::$webClient->request("PATCH", "/api/report/admin/accept", content: json_encode($content));

        /// step 3
        self::assertResponseStatusCodeSame(401);

        $responseContent = self::$webClient->getResponse()->getContent();

        $this->assertNotNull($responseContent);
        $this->assertNotEmpty($responseContent);
        $this->assertJson($responseContent);

        $responseContent = json_decode($responseContent, true);

        $this->assertIsArray($responseContent);
        $this->assertArrayHasKey("error", $responseContent);
    }
}