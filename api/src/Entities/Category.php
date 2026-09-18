<?php

declare(strict_types=1);

namespace App\Entities;

use App\Repositories\CategoryRepository;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\UuidInterface;

#[ORM\Entity(repositoryClass: CategoryRepository::class)]
#[ORM\Table(name: 'category')]
#[ORM\HasLifecycleCallbacks]
class Category
{
    use HasUuidId;
    use Timestampable;
    use HasFactory;

    public function __construct(
        UuidInterface $id,
        #[ORM\Column(length: 255)]
        private string $name,
    ) {
        $this->id = $id;
    }

    public function getName(): string
    {
        return $this->name;
    }
}
