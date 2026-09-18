<?php

declare(strict_types=1);

namespace App\Console;

use App\Entity\Affiliate;
use App\Entity\Area;
use App\Entity\Category;
use App\Entity\Event;
use App\Entity\Price;
use App\Entity\Venue;
use DateTimeImmutable;
use DateTimeZone;
use Doctrine\ORM\EntityManagerInterface;
use Faker\Generator;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * One specific event fixture, flags override Faker defaults. See the seed-fixture skill.
 */
#[AsCommand(name: 'app:fixture:event', description: 'Create one event fixture with specific attributes')]
final class FixtureEventCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly Generator $faker,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('title', null, InputOption::VALUE_REQUIRED)
            ->addOption('category', null, InputOption::VALUE_REQUIRED)
            ->addOption('venue-city', null, InputOption::VALUE_REQUIRED)
            ->addOption('affiliate', null, InputOption::VALUE_REQUIRED, 'Affiliate name (found or created)')
            ->addOption('start', null, InputOption::VALUE_REQUIRED, 'Relative date, e.g. "+2 days"', '+1 week')
            ->addOption('soldout', null, InputOption::VALUE_NONE)
            ->addOption(
                'areas',
                null,
                InputOption::VALUE_REQUIRED,
                'JSON: [{"name":"Freie Platzwahl","capacity":20,"prices":[{"name":"Normalpreis","value":23.76}]}]',
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $affiliate = $this->findOrCreateAffiliate((string) ($input->getOption('affiliate') ?? $this->faker->company()));
        $category = $this->findOrCreateCategory((string) ($input->getOption('category') ?? 'Comedy & Kabarett'));
        $venue = $this->findOrCreateVenue((string) ($input->getOption('venue-city') ?? $this->faker->city()));

        $start = new DateTimeImmutable((string) $input->getOption('start'), new DateTimeZone('UTC'));

        $event = new Event(
            Uuid::uuid7(),
            (string) ($input->getOption('title') ?? $this->faker->words(3, true)),
            $this->faker->optional()->sentence(4),
            '<p>' . $this->faker->paragraphs(3, true) . '</p>',
            $this->faker->optional()->sentence(12),
            $start,
            $start->modify('+3 hours'),
            $start->modify('-1 minute'),
            $start->modify('-30 minutes'),
            $start,
            'PUBLISHED',
            'NORMAL',
            $this->faker->uuid(),
            $this->faker->name(),
            $venue,
            $affiliate,
        );
        $event->addCategory($category);
        $this->entityManager->persist($event);

        $this->createAreas($event, $input->getOption('areas'), (bool) $input->getOption('soldout'));

        $this->entityManager->flush();

        $io->success(sprintf('Created event "%s" (%s).', $event->getTitle(), $event->getId()->toString()));

        return Command::SUCCESS;
    }

    private function createAreas(Event $event, mixed $areasJson, bool $soldOut): void
    {
        $areasSpec = is_string($areasJson) ? json_decode($areasJson, true) : null;
        $areasSpec ??= [[
            'name' => 'Freie Platzwahl',
            'capacity' => 20,
            'prices' => [['name' => 'Normalpreis', 'value' => $this->faker->randomFloat(2, 15, 80)]],
        ]];

        foreach ($areasSpec as $areaSpec) {
            $capacity = (int) $areaSpec['capacity'];
            $area = new Area(Uuid::uuid7(), $event, (string) $areaSpec['name'], $capacity);
            if ($soldOut) {
                $area = new Area(Uuid::uuid7(), $event, (string) $areaSpec['name'], $capacity, 0, $capacity);
            }
            $this->entityManager->persist($area);

            foreach ($areaSpec['prices'] as $priceSpec) {
                $basePrice = (float) $priceSpec['value'];
                $basePriceCents = (int) round($basePrice * 100);
                $ticketFeeCents = (int) round($basePriceCents * 0.08);
                $this->entityManager->persist(new Price(
                    Uuid::uuid7(),
                    $area,
                    (string) $priceSpec['name'],
                    $basePriceCents,
                    $ticketFeeCents,
                ));
            }
        }
    }

    private function findOrCreateAffiliate(string $name): Affiliate
    {
        $repository = $this->entityManager->getRepository(Affiliate::class);
        $existing = $repository->findOneBy(['name' => $name]);
        if ($existing instanceof Affiliate) {
            return $existing;
        }

        $affiliate = new Affiliate(Uuid::uuid7(), $name, $this->faker->imageUrl(200, 200, 'business'));
        $this->entityManager->persist($affiliate);

        return $affiliate;
    }

    private function findOrCreateCategory(string $name): Category
    {
        $repository = $this->entityManager->getRepository(Category::class);
        $existing = $repository->findOneBy(['name' => $name]);
        if ($existing instanceof Category) {
            return $existing;
        }

        $category = new Category(Uuid::uuid7(), $name);
        $this->entityManager->persist($category);

        return $category;
    }

    private function findOrCreateVenue(string $city): Venue
    {
        $repository = $this->entityManager->getRepository(Venue::class);
        $existing = $repository->findOneBy(['city' => $city]);
        if ($existing instanceof Venue) {
            return $existing;
        }

        $venue = new Venue(
            Uuid::uuid7(),
            $this->faker->company(),
            $this->faker->streetAddress(),
            $this->faker->postcode(),
            $city,
            'DE',
            (string) $this->faker->latitude(47, 55),
            (string) $this->faker->longitude(6, 15),
        );
        $this->entityManager->persist($venue);

        return $venue;
    }
}
