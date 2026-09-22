<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Shared\ValidatesQueryParams;

final class EventListRequest extends FormRequest
{
    use ValidatesQueryParams;

    protected function validate(): array
    {
        $query = $this->request()->getQueryParams();

        return [
            'q' => isset($query['q']) && $query['q'] !== '' ? (string) $query['q'] : null,
            'category' => $this->parseUuidListParam(isset($query['category']) ? (string) $query['category'] : null, 'category'),
            'dateFrom' => $this->parseDateParam(isset($query['date_from']) ? (string) $query['date_from'] : null, 'date_from'),
            'dateTo' => $this->parseDateParam(isset($query['date_to']) ? (string) $query['date_to'] : null, 'date_to'),
            'cursor' => $this->parseUuidParam(isset($query['cursor']) ? (string) $query['cursor'] : null, 'cursor'),
            'limit' => max(1, min(100, (int) ($query['limit'] ?? 20))),
        ];
    }
}
