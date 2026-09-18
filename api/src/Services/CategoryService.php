<?php

declare(strict_types=1);

namespace App\Services;

use App\Entities\Affiliate;
use App\Repositories\CategoryRepository;
use App\Resources\CategoryResource;
use Predis\ClientInterface;

final class CategoryService
{
    private const int CACHE_TTL_SECONDS = 300;

    public function __construct(
        private readonly CategoryRepository $categoryRepository,
        private readonly ClientInterface $redis,
    ) {
    }

    /**
     * @return list<array{id: string, name: string}>
     */
    public function listPublished(Affiliate $affiliate): array
    {
        $cacheKey = sprintf('categories:%s', $affiliate->getId()->toString());

        $cached = $this->redis->get($cacheKey);
        if (is_string($cached)) {
            /** @var list<array{id: string, name: string}> $decoded */
            $decoded = json_decode($cached, true, flags: JSON_THROW_ON_ERROR);

            return $decoded;
        }

        $categories = $this->categoryRepository->findPublishedForAffiliate($affiliate);
        $result = array_map(
            static fn ($category) => new CategoryResource($category)->toArray(),
            $categories,
        );

        $this->redis->setex($cacheKey, self::CACHE_TTL_SECONDS, json_encode($result, JSON_THROW_ON_ERROR));

        return $result;
    }
}
