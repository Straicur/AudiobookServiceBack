<?php

namespace App\ValueGenerator;

use App\Model\AdminCategoryModel;
use App\Repository\AudiobookCategoryRepository;
use Symfony\Component\Uid\Uuid;


class BuildAudiobookCategoryTreeGenerator implements ValueGeneratorInterface
{
    private array $elements;
    private AudiobookCategoryRepository $categoryRepository;

    /**
     * @param array $elements
     * @param AudiobookCategoryRepository $categoryRepository
     */
    public function __construct(array $elements, AudiobookCategoryRepository $categoryRepository)
    {
        $this->elements = $elements;
        $this->categoryRepository = $categoryRepository;
    }

    private function buildTree(array $elements, AudiobookCategoryRepository $categoryRepository, ?Uuid $parentId = null): array
    {
        $branch = array();

        foreach ($elements as $element) {

            if ($element->getParent() == $parentId || ($element->getParent() != null && $element->getParent()->getId() == $parentId)) {

                $children = $categoryRepository->findBy([
                    "parent" => $element->getId()
                ]);

                $child = new AdminCategoryModel($element->getId(), $element->getName(), $element->getActive(), $element->getCategoryKey(), $parentId);

                if (!empty($children)) {

                    $children = $this->buildTree($children, $categoryRepository, $element->getId());

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
        return $this->buildTree($this->getElements(), $this->getCategoryRepository());
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

    /**
     * @return AudiobookCategoryRepository
     */
    private function getCategoryRepository(): AudiobookCategoryRepository
    {
        return $this->categoryRepository;
    }

    /**
     * @param AudiobookCategoryRepository $categoryRepository
     */
    private function setCategoryRepository(AudiobookCategoryRepository $categoryRepository): void
    {
        $this->categoryRepository = $categoryRepository;
    }

}