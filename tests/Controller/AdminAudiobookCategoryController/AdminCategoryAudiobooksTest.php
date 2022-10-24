<?php

namespace App\Tests\Controller\AdminAudiobookCategoryController;

use App\Enums\AudiobookAgeRange;
use App\Repository\AudiobookCategoryRepository;
use App\Tests\AbstractWebTest;

/**
 * AdminCategoryAudiobooksTest
 */
class AdminCategoryAudiobooksTest extends AbstractWebTest
{
    /**
     * step 1 - Preparing data
     * step 2 - Preparing JsonBodyContent
     * step 3 - Sending Request
     * step 4 - Checking response
     * step 5 - Checking response if user is active
     * @return void
     */
    public function test_adminCategoryEditCorrect(): void
    {
        $audiobookCategoryRepository = $this->getService(AudiobookCategoryRepository::class);

        $this->assertInstanceOf(AudiobookCategoryRepository::class, $audiobookCategoryRepository);
        /// step 1
        $user = $this->databaseMockManager->testFunc_addUser("User", "Test", "test@cos.pl", "+48123123123", ["Guest", "User"], true, "zaq12wsx");

        $category1 = $this->databaseMockManager->testFunc_addAudiobookCategory("1");
        $category2 = $this->databaseMockManager->testFunc_addAudiobookCategory("2", $category1);

        $audiobook = $this->databaseMockManager->testFunc_addAudiobook("t","a","2","d",new \DateTime("Now"),"20","20",2,"desc",AudiobookAgeRange::ABOVE18,[$category1,$category2]);
        $audiobook = $this->databaseMockManager->testFunc_addAudiobook("t","a","2","d",new \DateTime("Now"),"20","20",2,"desc",AudiobookAgeRange::ABOVE18,[$category2]);
        $audiobook = $this->databaseMockManager->testFunc_addAudiobook("t","a","2","d",new \DateTime("Now"),"20","20",2,"desc",AudiobookAgeRange::ABOVE18,[$category2]);

        /// step 2
        $content = [
            "categoryKey" => $category2->getCategoryKey(),
            "page" => 0,
            "limit" => 10,
        ];
        $token = $this->databaseMockManager->testFunc_loginUser($user);
        /// step 3
        $crawler = self::$webClient->request("POST", "/api/admin/category/audiobooks", server: [
            "HTTP_authorization" => $token->getToken()
        ], content: json_encode($content));

        /// step 4
        $this->assertResponseIsSuccessful();
        $this->assertResponseStatusCodeSame(200);

    }
}
