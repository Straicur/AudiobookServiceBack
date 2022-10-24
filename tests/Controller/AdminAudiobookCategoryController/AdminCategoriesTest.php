<?php

namespace App\Tests\Controller\AdminAudiobookCategoryController;

use App\Repository\AudiobookCategoryRepository;
use App\Tests\AbstractWebTest;

/**
 * AdminCategoriesTest
 */
class AdminCategoriesTest extends AbstractWebTest
{
    /**
     * step 1 - Preparing data
     * step 2 - Sending Request
     * step 3 - Checking response
     * step 4 - Checking response if user is active
     * @return void
     */
    public function test_adminCategoriesCorrect(): void
    {
        $audiobookCategoryRepository = $this->getService(AudiobookCategoryRepository::class);

        $this->assertInstanceOf(AudiobookCategoryRepository::class, $audiobookCategoryRepository);
        /// step 1
        $user = $this->databaseMockManager->testFunc_addUser("User", "Test", "test@cos.pl", "+48123123123", ["Guest", "User"], true, "zaq12wsx");

        $category1 = $this->databaseMockManager->testFunc_addAudiobookCategory("1");
        $category2 = $this->databaseMockManager->testFunc_addAudiobookCategory("2", $category1);
        $category3 = $this->databaseMockManager->testFunc_addAudiobookCategory("3", $category1);
        $category4 = $this->databaseMockManager->testFunc_addAudiobookCategory("4", $category3);
        $category5 = $this->databaseMockManager->testFunc_addAudiobookCategory("5", $category2);

        $token = $this->databaseMockManager->testFunc_loginUser($user);
        /// step 2
        $crawler = self::$webClient->request("GET", "/api/admin/categories", server: [
            "HTTP_authorization" => $token->getToken()
        ]);

        /// step 3
        $this->assertResponseIsSuccessful();
        $this->assertResponseStatusCodeSame(200);

    }
}
