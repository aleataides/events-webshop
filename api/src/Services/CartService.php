<?php

declare(strict_types=1);

namespace App\Services;

use App\Entities\Affiliate;
use App\Entities\Cart;
use App\Entities\Order;
use App\Entities\OrderItem;
use App\Entities\Price;
use App\Entities\TicketReservation;
use App\Exceptions\CartEmptyException;
use App\Exceptions\CartExpiredException;
use App\Exceptions\InsufficientStockException;
use App\Exceptions\InvalidRequestException;
use App\Exceptions\PriceNotFoundException;
use App\Exceptions\TicketReservationNotFoundException;
use App\Repositories\AreaRepository;
use App\Repositories\CartRepository;
use App\Repositories\PriceRepository;
use App\Resources\CartResource;
use App\Resources\OrderResource;
use DateTimeImmutable;
use DateTimeZone;
use Doctrine\ORM\EntityManagerInterface;
use Ramsey\Uuid\Exception\InvalidUuidStringException;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

final class CartService
{
    public function __construct(
        private readonly CartRepository $cartRepository,
        private readonly AreaRepository $areaRepository,
        private readonly PriceRepository $priceRepository,
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
        $cart = $this->findValidCart($affiliate, $cartIdRaw);

        return $cart instanceof Cart ? new CartResource($cart)->toArray() : null;
    }

    /**
     * Lazily creates a cart if none was supplied and always renews its
     * expiry clock — see docs/shared/business-rules.md#cart-identity/#cart-expiry.
     *
     * @return array<string, mixed>
     */
    public function addItem(Affiliate $affiliate, ?string $cartIdRaw, string $priceIdRaw, int $qty): array
    {
        if ($qty < 1) {
            throw new InvalidRequestException('"qty" must be at least 1.');
        }

        $price = $this->priceRepository->findOneForAffiliate($affiliate, $this->parseUuidOrFail($priceIdRaw, 'priceId'));
        if (!$price instanceof Price) {
            throw new PriceNotFoundException($priceIdRaw);
        }

        $now = new DateTimeImmutable('now', new DateTimeZone('UTC'));
        $cart = $this->findValidCart($affiliate, $cartIdRaw) ?? Cart::startNew(Uuid::uuid7(), $affiliate, $now);

        $this->transactional(function () use ($cart, $price, $priceIdRaw, $qty, $now): void {
            if (!$this->areaRepository->tryReserve($price->getArea()->getId(), $qty)) {
                throw new InsufficientStockException($priceIdRaw);
            }

            new TicketReservation(Uuid::uuid7(), $cart, $price, $qty);
            $this->entityManager->persist($cart);
            $cart->renewExpiry($now);
        });

        return new CartResource($cart)->toArray();
    }

    /**
     * Renews the cart's expiry clock on a qty increase, not a decrease —
     * see docs/shared/business-rules.md#cart-expiry.
     *
     * @return array<string, mixed>
     */
    public function updateItemQty(Affiliate $affiliate, ?string $cartIdRaw, string $reservationIdRaw, int $newQty): array
    {
        if ($newQty < 0) {
            throw new InvalidRequestException('"qty" must be zero or greater.');
        }

        $reservation = $this->findReservationOrFail($affiliate, $cartIdRaw, $reservationIdRaw);
        $cart = $reservation->getCart();
        $isIncrease = $newQty > $reservation->getQty();

        $this->transactional(function () use ($reservation, $cart, $newQty, $isIncrease): void {
            if ($newQty === 0) {
                $this->releaseReservation($reservation);
            } else {
                $this->adjustReservationQty($reservation, $newQty);
            }

            if ($isIncrease) {
                $cart->renewExpiry(new DateTimeImmutable('now', new DateTimeZone('UTC')));
            }
        });

        return new CartResource($cart)->toArray();
    }

    /**
     * Does not touch the cart's expiry clock — see
     * docs/shared/business-rules.md#cart-expiry.
     *
     * @return array<string, mixed>
     */
    public function removeItem(Affiliate $affiliate, ?string $cartIdRaw, string $reservationIdRaw): array
    {
        $reservation = $this->findReservationOrFail($affiliate, $cartIdRaw, $reservationIdRaw);
        $cart = $reservation->getCart();
        $this->releaseReservation($reservation);

        $this->entityManager->flush();

        return new CartResource($cart)->toArray();
    }

    /**
     * Atomically converts every reservation into a sale — see
     * docs/shared/business-rules.md#buy--checkout. No payment gateway; this
     * response IS the confirmation, there's no separate GET /orders/{id}.
     *
     * @return array<string, mixed>
     */
    public function buy(Affiliate $affiliate, ?string $cartIdRaw): array
    {
        $cart = $this->findValidCart($affiliate, $cartIdRaw);
        if (!$cart instanceof Cart || $cart->getReservations()->isEmpty()) {
            throw new CartEmptyException();
        }

        $order = new Order(Uuid::uuid7(), $affiliate);
        foreach ($cart->getReservations() as $reservation) {
            $reservation->getPrice()->getArea()->sell($reservation->getQty());
            new OrderItem(Uuid::uuid7(), $order, $reservation->getPrice(), $reservation->getQty());
            $this->entityManager->remove($reservation);
        }

        $this->entityManager->persist($order);
        $this->entityManager->remove($cart);
        $this->entityManager->flush();

        return new OrderResource($order)->toArray();
    }

    /**
     * Runs raw-SQL area writes and the ORM flush in one DB transaction —
     * unlike EntityManager::wrapInTransaction(), never closes the EntityManager.
     */
    private function transactional(callable $fn): void
    {
        $this->entityManager->getConnection()->transactional(function () use ($fn): void {
            $fn();
            $this->entityManager->flush();
        });
    }

    private function adjustReservationQty(TicketReservation $reservation, int $newQty): void
    {
        $delta = $newQty - $reservation->getQty();
        $reserved = $delta <= 0 || $this->areaRepository->tryReserve($reservation->getPrice()->getArea()->getId(), $delta);

        if (!$reserved) {
            throw new InsufficientStockException($reservation->getPrice()->getId()->toString());
        }

        if ($delta < 0) {
            $reservation->getPrice()->getArea()->release(-$delta);
        }

        $reservation->setQty($newQty);
    }

    private function releaseReservation(TicketReservation $reservation): void
    {
        $reservation->getPrice()->getArea()->release($reservation->getQty());
        $this->entityManager->remove($reservation);
    }

    private function findReservationOrFail(Affiliate $affiliate, ?string $cartIdRaw, string $reservationIdRaw): TicketReservation
    {
        $cart = $this->findValidCart($affiliate, $cartIdRaw);
        $reservationId = $this->parseUuidOrFail($reservationIdRaw, 'reservationId');

        $reservation = $cart instanceof Cart ? $this->findReservation($cart, $reservationId) : null;
        if (!$reservation instanceof TicketReservation) {
            throw new TicketReservationNotFoundException($reservationIdRaw);
        }

        return $reservation;
    }

    private function findReservation(Cart $cart, UuidInterface $reservationId): ?TicketReservation
    {
        foreach ($cart->getReservations() as $reservation) {
            if ($reservation->getId()->equals($reservationId)) {
                return $reservation;
            }
        }

        return null;
    }

    /**
     * Null means "no cart" (missing/invalid id, or expired-and-released).
     */
    private function findValidCart(Affiliate $affiliate, ?string $cartIdRaw): ?Cart
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

        return $cart;
    }

    private function releaseExpiredCart(Cart $cart): void
    {
        foreach ($cart->getReservations() as $reservation) {
            $this->releaseReservation($reservation);
        }

        $this->entityManager->flush();
    }

    private function parseUuidOrFail(string $raw, string $paramName): UuidInterface
    {
        try {
            return Uuid::fromString($raw);
        } catch (InvalidUuidStringException) {
            throw new InvalidRequestException(sprintf('"%s" must be a valid UUID.', $paramName));
        }
    }
}
