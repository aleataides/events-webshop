<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Entities\Affiliate;
use App\Http\Requests\EventListRequest;
use App\Services\EventService;
use Psr\Http\Message\ResponseInterface as Response;

final class EventListController extends Controller
{
    public function __construct(private readonly EventService $eventService)
    {
    }

    public function __invoke(EventListRequest $request, Affiliate $affiliate): Response
    {
        $query = $request->validated();

        $result = $this->eventService->listPublished(
            $affiliate,
            $query['q'],
            $query['category'],
            $query['dateFrom'],
            $query['dateTo'],
            $query['cursor'],
            $query['limit'],
        );

        return $this->json([
            'data' => $result['items'],
            'meta' => ['next_cursor' => $result['nextCursor'], 'has_more' => $result['hasMore']],
        ]);
    }
}
