<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Exceptions\InvalidRequestException;
use App\Shared\ValidatesQueryParams;

final class EventDetailRequest extends FormRequest
{
    use ValidatesQueryParams;

    protected function validate(): array
    {
        $eventIdAttribute = $this->request()->getAttribute('eventId');
        $eventId = $this->parseUuidParam(is_string($eventIdAttribute) ? $eventIdAttribute : null, 'eventId');
        if ($eventId === null) {
            throw new InvalidRequestException('"eventId" is required.');
        }

        return ['eventId' => $eventId];
    }
}
