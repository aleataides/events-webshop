<?php

declare(strict_types=1);

namespace App\Entities;

use App\Factories\Factory;
use App\Factories\FactoryRegistry;

trait HasFactory
{
    /**
     * @return Factory<static>
     */
    public static function factory(): Factory
    {
        return FactoryRegistry::resolve(static::class);
    }
}
