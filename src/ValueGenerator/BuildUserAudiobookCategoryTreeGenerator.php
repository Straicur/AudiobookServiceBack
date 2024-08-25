<?php

declare(strict_types=1);

namespace App\ValueGenerator;

use App\Entity\AudiobookCategory;
use App\Model\User\UserCategoryTreeModel;
use App\Repository\AudiobookCategoryRepository;
use Symfony\Component\Uid\Uuid;

class BuildUserAudiobookCategoryTreeGenerator implements ValueGeneratorInterface
{
    /**
     * @param AudiobookCategory[] $elements
     */
    public function __construct(
        private array                                $elements,
        private readonly AudiobookCategoryRepository $categoryRepository,
    ) {
    }

    /**
     * @param AudiobookCategory[] $elements
     * @return UserCategoryTreeModel[]
     */
    private function buildTree(array $elements, ?string $parentKey = null): array
    {
        $branch = [];

        foreach ($elements as $element) {
            if ($element->getParent() === null || ($element->getParent() !== null && $element->getParent()->getCategoryKey() === $parentKey)) {
                $children = $this->categoryRepository->findBy([
                    'parent' => $element->getId(),
                    'active' => true,
                ]);

                $child = new UserCategoryTreeModel(
                    $element->getName(),
                    $element->getCategoryKey(),
                    (string)$parentKey,
                );

                if (!empty($children)) {
                    $children = $this->buildTree($children, $element->getCategoryKey());

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
     * @return UserCategoryTreeModel[]
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
