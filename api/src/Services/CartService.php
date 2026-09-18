<?php

declare(strict_types=1);

namespace App\Services;

use App\Entities\Affiliate;
use App\Entities\Cart;
use App\Exceptions\CartExpiredException;
use App\Repositories\CartRepository;
use App\Resources\CartResource;
use DateTimeImmutable;
use DateTimeZone;
use Doctrine\ORM\EntityManagerInterface;
use Ramsey\Uuid\Exception\InvalidUuidStringException;
use Ramsey\Uuid\Uuid;

final class CartService
{
    public function __construct(
        private readonly CartRepository $cartRepository,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    /**
     * Null means "no cart" — an unknown/invalid/missing id is never an
     * error on read, see docs/shared/business-rules.md#cart-identity.
     *
     * @return array<string, mixed>|null
     */
    public function getCart(Affiliate $affiliate, ?string $cartIdRaw): ?array
    {
        if ($cartIdRaw === null || $cartIdRaw === '') {
            return null;
        }

        try {
            $cartId = Uuid::fromString($cartIdRaw);
        } catch (InvalidUuidStringException) {
            return null;
        }

        $cart = $this->cartRepository->findOneForAffiliate($affiliate, $cartId);
        if (!$cart instanceof Cart) {
            return null;
        }

        $now = new DateTimeImmutable('now', new DateTimeZone('UTC'));
        if ($cart->isExpired($now)) {
            $this->releaseExpiredCart($cart);

            throw new CartExpiredException($cartId->toString());
        }

        return new CartResource($cart)->toArray();
    }

    private function releaseExpiredCart(Cart $cart): void
    {
        foreach ($cart->getReservations() as $reservation) {
            $reservation->getPrice()->getArea()->release($reservation->getQty());
            $this->entityManager->remove($reservation);
        }

        $this->entityManager->flush();
    }
}
