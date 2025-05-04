<?php

declare(strict_types=1);

namespace App\Adapter\Primary\Command;

use App\Application\Ports\Output\LogLineRepositoryInterface;
use App\Application\Services\TerminateCommandEvent;
use App\Application\Services\TextStreamReaderBuilder;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'app:ingest-logs', description: 'Ingest logs from file')]
class IngestLogsCommand extends Command
{
    public function __construct(
        private readonly TextStreamReaderBuilder $readerBuilder,
        private readonly LogLineRepositoryInterface $lineConsumer,
        private readonly EventDispatcherInterface $eventDispatcher,
    ) {
        parent::__construct();
    }
    
    protected function configure(): void
    {
        $this
            ->addArgument('logfile', InputArgument::REQUIRED, 'Full path of the log file')
            ->addOption('no-tail', null, InputOption::VALUE_NONE, 'Don\t tail the log file')
        ;
    }
    
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        
        $builder = $this->readerBuilder;
        $builder
            ->setConsumer($this->lineConsumer)
            ->setFilename($input->getArgument('logfile'))
            ->shouldTail(!$input->getOption('no-tail'))
            ->build();
        
        $signalHandler = function (int $signal) use ($io) {
            $this->eventDispatcher->dispatch(new TerminateCommandEvent(), TerminateCommandEvent::EVENT_NAME);
            
            $io->writeln("\n\nStop signal received. Stopping gracefully...");
        };
        
        pcntl_signal(SIGINT, $signalHandler);
        pcntl_signal(SIGTERM, $signalHandler);
        
        $reader = $builder->build();
        
        $io->writeln('Starting reading from the file...');
        $reader->start();
        $io->writeln("Done.");
        
        return self::SUCCESS;
    }
}
