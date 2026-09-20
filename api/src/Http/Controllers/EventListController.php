<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Entities\Affiliate;
use App\Services\EventService;
use App\Shared\ValidatesQueryParams;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

final class EventListController extends Controller
{
    use ValidatesQueryParams;

    public function __construct(private readonly EventService $eventService)
    {
    }

    public function __invoke(Request $request, Affiliate $affiliate): Response
    {
        $query = $request->getQueryParams();
        $limit = max(1, min(100, (int) ($query['limit'] ?? 20)));

        $result = $this->eventService->listPublished(
            $affiliate,
            isset($query['q']) && $query['q'] !== '' ? (string) $query['q'] : null,
            $this->parseUuidListParam(isset($query['category']) ? (string) $query['category'] : null, 'category'),
            $this->parseDateParam(isset($query['date_from']) ? (string) $query['date_from'] : null, 'date_from'),
            $this->parseDateParam(isset($query['date_to']) ? (string) $query['date_to'] : null, 'date_to'),
            $this->parseUuidParam(isset($query['cursor']) ? (string) $query['cursor'] : null, 'cursor'),
            $limit,
        );

        return $this->json([
            'data' => $result['items'],
            'meta' => ['next_cursor' => $result['nextCursor'], 'has_more' => $result['hasMore']],
        ]);
    }
}
