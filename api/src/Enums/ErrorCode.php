<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Stable error codes returned in the JSON error envelope — one per
 * DomainException subclass.
 */
enum ErrorCode: string
{
    case AffiliateNotFound = 'affiliate_not_found';
    case CartEmpty = 'cart_empty';
    case CartExpired = 'cart_expired';
    case EventNotFound = 'event_not_found';
    case InsufficientStock = 'insufficient_stock';
    case InternalError = 'internal_error';
    case InvalidRequest = 'invalid_request';
    case MethodNotAllowed = 'method_not_allowed';
    case NotFound = 'not_found';
    case PriceNotFound = 'price_not_found';
    case TicketReservationNotFound = 'cart_item_not_found';
}
