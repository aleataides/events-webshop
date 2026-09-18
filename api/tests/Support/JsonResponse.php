<?php

declare(strict_types=1);

namespace Tests\Support;

use Psr\Http\Message\ResponseInterface;

final class JsonResponse
{
    public readonly int $status;

    /**
     * @var array<string, mixed>
     */
    public readonly array $json;

    public function __construct(ResponseInterface $response)
    {
        $this->status = $response->getStatusCode();
        $this->json = json_decode((string) $response->getBody(), true) ?? [];
    }
}
