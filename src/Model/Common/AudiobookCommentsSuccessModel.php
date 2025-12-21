<?php

declare(strict_types = 1);

namespace App\Model\Common;

use App\Model\ModelInterface;
use OpenApi\Attributes as OA;
use Nelmio\ApiDocBundle\Attribute\Model;

class AudiobookCommentsSuccessModel implements ModelInterface
{
    public function __construct(
        /**
         * @var AudiobookCommentsModel[]
         */
        #[OA\Property(
            type: 'array',
            items: new OA\Items(ref: new Model(type: AudiobookCommentsModel::class))
        )]
        private array $comments,
    ) {}

    public function getComments(): array
    {
        return $this->comments;
    }

    public function setComments(array $comments): void
    {
        $this->comments = $comments;
    }

    public function addComment(AudiobookCommentsModel $category): void
    {
        $this->comments[] = $category;
    }
}
