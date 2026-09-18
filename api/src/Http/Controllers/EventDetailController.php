<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Exceptions\InvalidRequestException;
use App\Services\EventService;
use App\Shared\ValidatesQueryParams;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

final class EventDetailController extends Controller
{
    use ValidatesQueryParams;

    public function __construct(private readonly EventService $eventService)
    {
    }

    public function __invoke(Request $request): Response
    {
        $affiliate = $this->affiliate($request);

        $eventIdAttribute = $request->getAttribute('eventId');
        $eventId = $this->parseUuidParam(is_string($eventIdAttribute) ? $eventIdAttribute : null, 'eventId');
        if ($eventId === null) {
            throw new InvalidRequestException('"eventId" is required.');
        }

        $data = $this->eventService->getDetail($affiliate, $eventId);

        return $this->json(['data' => $data]);
    }
}
