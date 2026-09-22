<?php

declare(strict_types=1);

namespace App\Resources;

use App\Entities\Affiliate;
use JsonSerializable;

final class AffiliateResource implements JsonSerializable
{
    public function __construct(private readonly Affiliate $affiliate)
    {
    }

    /**
     * @return array{id: string, name: string}
     */
    public function jsonSerialize(): array
    {
        return [
            'id' => $this->affiliate->getId()->toString(),
            'name' => $this->affiliate->getName(),
        ];
    }
}
