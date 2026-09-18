<?php

declare(strict_types=1);

namespace App\Resources;

use App\Entities\Category;

final class CategoryResource
{
    public function __construct(private readonly Category $category)
    {
    }

    /**
     * @return array{id: string, name: string}
     */
    public function toArray(): array
    {
        return [
            'id' => $this->category->getId()->toString(),
            'name' => $this->category->getName(),
        ];
    }
}
