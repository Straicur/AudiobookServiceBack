<?php

declare(strict_types=1);

namespace App\Service\Admin\Audiobook;

use App\Entity\Audiobook;
use App\Model\Common\AudiobookDetailCategoryModel;
use App\Model\Serialization\AudiobookId3TagsModel;
use App\Repository\AudiobookCategoryRepository;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Uid\Uuid;

class AudiobookAddService implements AudiobookAddServiceInterface
{
    public function __construct(
        private readonly SerializerInterface $serializer,
        private readonly AudiobookCategoryRepository $audiobookCategoryRepository,
    ) {
    }

    public function getAudiobookId3Tags(array $ID3JsonData): AudiobookId3TagsModel
    {
        $id3SerializeModel = new AudiobookId3TagsModel();
        $this->serializer->deserialize(
            json_encode($ID3JsonData),
            AudiobookId3TagsModel::class,
            'json',
            [
                AbstractNormalizer::OBJECT_TO_POPULATE             => $id3SerializeModel,
                AbstractObjectNormalizer::DISABLE_TYPE_ENFORCEMENT => true,
            ],
        );

        return $id3SerializeModel;
    }

    public function addAudiobookCategories(Audiobook $audiobook, $additionalData, array &$audiobookCategories): void
    {
        if (array_key_exists('categories', $additionalData)) {
            $categories = $additionalData['categories'];

            foreach ($categories as $category) {
                $audiobookCategory = $this->audiobookCategoryRepository->find(Uuid::fromString($category));

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
    }
}
