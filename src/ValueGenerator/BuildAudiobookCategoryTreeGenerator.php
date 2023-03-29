<?php

namespace App\ValueGenerator;

use App\Model\AdminCategoryModel;
use App\Repository\AudiobookCategoryRepository;
use App\Repository\AudiobookRepository;
use Symfony\Component\Uid\Uuid;


class BuildAudiobookCategoryTreeGenerator implements ValueGeneratorInterface
{
    private array $elements;
    private AudiobookCategoryRepository $categoryRepository;
    private AudiobookRepository $audiobookRepository;

    /**
     * @param array $elements
     * @param AudiobookCategoryRepository $categoryRepository
     * @param AudiobookRepository $audiobookRepository
     */
    public function __construct(array $elements, AudiobookCategoryRepository $categoryRepository, AudiobookRepository $audiobookRepository)
    {
        $this->elements = $elements;
        $this->categoryRepository = $categoryRepository;
        $this->audiobookRepository = $audiobookRepository;
    }

    private function buildTree(array $elements, ?Uuid $parentId = null): array
    {
        $branch = array();

        foreach ($elements as $element) {

            if ($element->getParent() == $parentId || ($element->getParent() != null && $element->getParent()->getId() == $parentId)) {

                $children = $this->categoryRepository->findBy([
                    "parent" => $element->getId()
                ]);

                $audiobooks = $this->audiobookRepository->getCategoryAudiobooks($element);

                $child = new AdminCategoryModel($element->getId(), $element->getName(), $element->getActive(), $element->getCategoryKey(), count($audiobooks), $parentId);

                if (!empty($children)) {

                    $children = $this->buildTree($children, $element->getId());

                    foreach ($children as $parentChild) {
                        $child->addChildren($parentChild);
                    }
                }

                $branch[] = $child;
            }
        }

        return $branch;
    }

    public function generate(): array
    {
        return $this->buildTree($this->getElements());
    }

    /**
     * @return array
     */
    private function getElements(): array
    {
        return $this->elements;
    }

    /**
     * @param array $elements
     */
    private function setElements(array $elements): void
    {
        $this->elements = $elements;
    }

}