<?php

declare(strict_types=1);

namespace App\Entities;

use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\UuidInterface;

#[ORM\Entity]
#[ORM\Table(name: 'event')]
#[ORM\HasLifecycleCallbacks]
class Event
{
    use HasUuidId;
    use Timestampable;
    use HasFactory;

    /**
     * @var Collection<int, Category>
     */
    #[ORM\ManyToMany(targetEntity: Category::class)]
    #[ORM\JoinTable(name: 'event_category')]
    private Collection $categories;

    /**
     * @var Collection<int, Area>
     */
    #[ORM\OneToMany(mappedBy: 'event', targetEntity: Area::class, cascade: ['persist'])]
    private Collection $areas;

    public function __construct(
        UuidInterface $id,
        #[ORM\Column(length: 255)]
        private string $title,
        #[ORM\Column(length: 255, nullable: true)]
        private ?string $subtitle,
        #[ORM\Column(type: 'text', nullable: true)]
        private ?string $description,
        #[ORM\Column(type: 'text', nullable: true)]
        private ?string $priceInfo,
        #[ORM\Column]
        private DateTimeImmutable $start,
        #[ORM\Column]
        private DateTimeImmutable $end,
        #[ORM\Column]
        private DateTimeImmutable $salesEnd,
        #[ORM\Column(nullable: true)]
        private ?DateTimeImmutable $doorsOpen,
        #[ORM\Column(nullable: true)]
        private ?DateTimeImmutable $doorsClose,
        #[ORM\Column(length: 20)]
        private string $status,
        #[ORM\Column(length: 20)]
        private string $eventType,
        #[ORM\Column(length: 255, nullable: true)]
        private ?string $imageId,
        #[ORM\Column(length: 255, nullable: true)]
        private ?string $imageCopyright,
        #[ORM\ManyToOne(targetEntity: Venue::class)]
        #[ORM\JoinColumn(nullable: false)]
        private Venue $venue,
        #[ORM\ManyToOne(targetEntity: Affiliate::class)]
        #[ORM\JoinColumn(nullable: false)]
        private Affiliate $affiliate,
    ) {
        $this->id = $id;
        $this->categories = new ArrayCollection();
        $this->areas = new ArrayCollection();
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getSubtitle(): ?string
    {
        return $this->subtitle;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function getPriceInfo(): ?string
    {
        return $this->priceInfo;
    }

    public function getStart(): DateTimeImmutable
    {
        return $this->start;
    }

    public function getEnd(): DateTimeImmutable
    {
        return $this->end;
    }

    public function getSalesEnd(): DateTimeImmutable
    {
        return $this->salesEnd;
    }

    public function getDoorsOpen(): ?DateTimeImmutable
    {
        return $this->doorsOpen;
    }

    public function getDoorsClose(): ?DateTimeImmutable
    {
        return $this->doorsClose;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getEventType(): string
    {
        return $this->eventType;
    }

    public function getImageId(): ?string
    {
        return $this->imageId;
    }

    public function getImageCopyright(): ?string
    {
        return $this->imageCopyright;
    }

    public function getVenue(): Venue
    {
        return $this->venue;
    }

    public function getAffiliate(): Affiliate
    {
        return $this->affiliate;
    }

    /**
     * @return Collection<int, Category>
     */
    public function getCategories(): Collection
    {
        return $this->categories;
    }

    public function addCategory(Category $category): void
    {
        if (!$this->categories->contains($category)) {
            $this->categories->add($category);
        }
    }

    /**
     * @return Collection<int, Area>
     */
    public function getAreas(): Collection
    {
        return $this->areas;
    }

    public function addArea(Area $area): void
    {
        if (!$this->areas->contains($area)) {
            $this->areas->add($area);
        }
    }
}
