<?php

declare(strict_types=1);

namespace App\ValueGenerator;

use App\Model\Admin\AdminCategoryModel;
use App\Repository\AudiobookCategoryRepository;
use App\Repository\AudiobookRepository;
use Symfony\Component\Uid\Uuid;

class BuildAudiobookCategoryTreeGenerator implements ValueGeneratorInterface
{

    public function __construct(
        private array                                $elements,
        private readonly AudiobookCategoryRepository $categoryRepository,
        private readonly AudiobookRepository         $audiobookRepository,
    )
    {

    }

    private function buildTree(array $elements, ?Uuid $parentId = null): array
    {
        $branch = [];

        foreach ($elements as $element) {

            if ($element->getParent() === $parentId || ($element->getParent() !== null && $element->getParent()->getId() === $parentId)) {

                $children = $this->categoryRepository->findBy([
                    'parent' => $element->getId(),
                ]);

                $audiobooks = $this->audiobookRepository->getCategoryAudiobooks($element);

                $child = new AdminCategoryModel((string)$element->getId(), $element->getName(), $element->getActive(), $element->getCategoryKey(), count($audiobooks), (string)$parentId);

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
     * @return AdminCategoryModel[]
     */
    private function getElements(): array
    {
        return $this->elements;
    }

    private function setElements(array $elements): void
    {
        $this->elements = $elements;
    }

}