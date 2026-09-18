<?php

declare(strict_types=1);

namespace App\Console;

use App\Entities\Affiliate;
use App\Entities\Category;
use App\Entities\Event;
use App\Entities\Venue;
use App\Factories\ModelFactories;
use DateTimeImmutable;
use DateTimeZone;
use Doctrine\ORM\EntityManagerInterface;
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
        private readonly ModelFactories $factories,
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

        $affiliate = $this->findOrCreateAffiliate($input->getOption('affiliate'));
        $category = $this->findOrCreateCategory((string) ($input->getOption('category') ?? 'Comedy & Kabarett'));
        $venue = $this->findOrCreateVenue($input->getOption('venue-city'));

        $start = new DateTimeImmutable((string) $input->getOption('start'), new DateTimeZone('UTC'));
        $title = $input->getOption('title');

        $event = $this->factories->event->create([
            'venue' => $venue,
            'affiliate' => $affiliate,
            ...($title !== null ? ['title' => (string) $title] : []),
            'start' => $start,
            'end' => $start->modify('+3 hours'),
            'salesEnd' => $start->modify('-1 minute'),
            'doorsOpen' => $start->modify('-30 minutes'),
            'doorsClose' => $start,
        ]);
        $event->addCategory($category);

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
            'prices' => [['name' => 'Normalpreis', 'value' => random_int(1500, 8000) / 100]],
        ]];

        foreach ($areasSpec as $areaSpec) {
            $capacity = (int) $areaSpec['capacity'];
            $area = $this->factories->area->create([
                'event' => $event,
                'name' => (string) $areaSpec['name'],
                'capacity' => $capacity,
                'soldQty' => $soldOut ? $capacity : 0,
            ]);

            foreach ($areaSpec['prices'] as $priceSpec) {
                $basePriceCents = (int) round((float) $priceSpec['value'] * 100);
                $this->factories->price->create([
                    'area' => $area,
                    'name' => (string) $priceSpec['name'],
                    'basePriceCents' => $basePriceCents,
                    'ticketFeeCents' => (int) round($basePriceCents * 0.08),
                ]);
            }
        }
    }

    private function findOrCreateAffiliate(mixed $name): Affiliate
    {
        if (is_string($name)) {
            $existing = $this->entityManager->getRepository(Affiliate::class)->findOneBy(['name' => $name]);
            if ($existing instanceof Affiliate) {
                return $existing;
            }
        }

        return $this->factories->affiliate->create(is_string($name) ? ['name' => $name] : []);
    }

    private function findOrCreateCategory(string $name): Category
    {
        $existing = $this->entityManager->getRepository(Category::class)->findOneBy(['name' => $name]);
        if ($existing instanceof Category) {
            return $existing;
        }

        return $this->factories->category->create(['name' => $name]);
    }

    private function findOrCreateVenue(mixed $city): Venue
    {
        if (is_string($city)) {
            $existing = $this->entityManager->getRepository(Venue::class)->findOneBy(['city' => $city]);
            if ($existing instanceof Venue) {
                return $existing;
            }
        }

        return $this->factories->venue->create(is_string($city) ? ['city' => $city] : []);
    }
}
