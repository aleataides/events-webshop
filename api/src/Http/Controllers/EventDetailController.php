<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Entities\Affiliate;
use App\Http\Requests\EventDetailRequest;
use App\Services\EventService;
use Psr\Http\Message\ResponseInterface as Response;

final class EventDetailController extends Controller
{
    public function __construct(private readonly EventService $eventService)
    {
    }

    public function __invoke(EventDetailRequest $request, Affiliate $affiliate): Response
    {
        $data = $this->eventService->getDetail($affiliate, $request->validated()['eventId']);

        return $this->json(['data' => $data]);
    }
}
