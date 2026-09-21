<?php

declare(strict_types=1);

namespace App\Resources;

use App\Entities\Category;
use JsonSerializable;

final class CategoryResource implements JsonSerializable
{
    public function __construct(private readonly Category $category)
    {
    }

    /**
     * @return array{id: string, name: string}
     */
    public function jsonSerialize(): array
    {
        return [
            'id' => $this->category->getId()->toString(),
            'name' => $this->category->getName(),
        ];
    }
}
