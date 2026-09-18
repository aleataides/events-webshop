<?php

declare(strict_types=1);

namespace App\Console;

use App\Seeder\DemoDataSeeder;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Bulk demo data via Faker. For one specific scenario, use app:fixture:event.
 */
#[AsCommand(name: 'app:seed', description: 'Seed the database with demo data')]
final class SeedCommand extends Command
{
    public function __construct(private readonly DemoDataSeeder $demoDataSeeder)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('affiliates', null, InputOption::VALUE_REQUIRED, 'Number of affiliates', 2)
            ->addOption('venues', null, InputOption::VALUE_REQUIRED, 'Number of venues', 5)
            ->addOption('events', null, InputOption::VALUE_REQUIRED, 'Number of events', 30);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $counts = $this->demoDataSeeder->seed(
            (int) $input->getOption('affiliates'),
            (int) $input->getOption('venues'),
            (int) $input->getOption('events'),
        );

        new SymfonyStyle($input, $output)->success(sprintf(
            'Seeded %d affiliates, %d categories, %d venues, %d events.',
            $counts['affiliates'],
            $counts['categories'],
            $counts['venues'],
            $counts['events'],
        ));

        return Command::SUCCESS;
    }
}
