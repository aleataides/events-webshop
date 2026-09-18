<?php

declare(strict_types=1);

namespace App\Exceptions;

use LogicException;

/**
 * Thrown when a controller reads the "affiliate" request attribute but
 * AffiliateMiddleware wasn't applied to its route — a wiring bug, not a
 * domain/HTTP case, so it isn't a DomainException (falls through to 500).
 */
final class MissingAffiliateContextException extends LogicException
{
    public function __construct()
    {
        parent::__construct('Affiliate missing from request context — AffiliateMiddleware not applied to this route.');
    }
}
