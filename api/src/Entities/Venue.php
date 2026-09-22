<?php

declare(strict_types=1);

namespace App\Entities;

use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\UuidInterface;

#[ORM\Entity]
#[ORM\Table(name: 'venue')]
#[ORM\HasLifecycleCallbacks]
class Venue
{
    use HasUuidId;
    use Timestampable;
    use HasFactory;

    public function __construct(
        UuidInterface $id,
        #[ORM\Column(length: 255)]
        private string $name,
        #[ORM\Column(length: 255)]
        private string $street,
        #[ORM\Column(length: 20)]
        private string $zipCode,
        #[ORM\Column(length: 255)]
        private string $city,
        #[ORM\Column(length: 2)]
        private string $country,
        #[ORM\Column(type: 'decimal', precision: 10, scale: 7)]
        private string $latitude,
        #[ORM\Column(type: 'decimal', precision: 10, scale: 7)]
        private string $longitude,
    ) {
        $this->id = $id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getStreet(): string
    {
        return $this->street;
    }

    public function getZipCode(): string
    {
        return $this->zipCode;
    }

    public function getCity(): string
    {
        return $this->city;
    }

    public function getCountry(): string
    {
        return $this->country;
    }

    public function getLatitude(): string
    {
        return $this->latitude;
    }

    public function getLongitude(): string
    {
        return $this->longitude;
    }
}
