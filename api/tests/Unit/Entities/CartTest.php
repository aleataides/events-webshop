<?php

declare(strict_types=1);

namespace Tests\Unit\Entities;

use DateTimeImmutable;
use DateTimeZone;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;
use Tests\Support\UsesFactories;

final class CartTest extends TestCase
{
    use UsesFactories;

    protected function setUp(): void
    {
        $this->setUpFactories();
    }

    #[Test]
    #[TestDox('isExpired is false right after creation (now < expiresAt)')]
    public function isExpiredFalseRightAfterCreation(): void
    {
        $now = new DateTimeImmutable('now', new DateTimeZone('UTC'));
        $cart = $this->cartFactory->make(['affiliate' => $this->affiliateFactory->make()]);

        self::assertFalse($cart->isExpired($now));
    }

    #[Test]
    #[TestDox('isExpired is true once now is past expiresAt')]
    public function isExpiredTrueOnceExpiresAtHasPassed(): void
    {
        $now = new DateTimeImmutable('now', new DateTimeZone('UTC'));
        $cart = $this->cartFactory->make([
            'affiliate' => $this->affiliateFactory->make(),
            'expiresAt' => $now->modify('-1 second'),
        ]);

        self::assertTrue($cart->isExpired($now));
    }

    #[Test]
    #[TestDox('renewExpiry resets the whole-cart clock to +15 minutes from now')]
    public function renewExpiryResetsTheClock(): void
    {
        $now = new DateTimeImmutable('now', new DateTimeZone('UTC'));
        $cart = $this->cartFactory->make([
            'affiliate' => $this->affiliateFactory->make(),
            'expiresAt' => $now->modify('-1 second'),
        ]);

        $cart->renewExpiry($now);

        self::assertFalse($cart->isExpired($now));
        self::assertSame($now->modify('+15 minutes')->getTimestamp(), $cart->getExpiresAt()->getTimestamp());
    }
}
