<?php

declare(strict_types=1);

namespace App\Resources;

use App\Entities\Price;
use App\Shared\MoneyConvertible;

final class PriceResource
{
    use MoneyConvertible;

    public function __construct(private readonly Price $price)
    {
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->price->getId()->toString(),
            'name' => $this->price->getName(),
            'value' => $this->toMajorUnits($this->price->getValueCents()),
            'basePrice' => $this->toMajorUnits($this->price->getBasePriceCents()),
            'ticketFee' => $this->toMajorUnits($this->price->getTicketFeeCents()),
            'outletFee' => $this->toMajorUnits($this->price->getOutletFeeCents()),
            'currency' => $this->price->getCurrency(),
        ];
    }
}
