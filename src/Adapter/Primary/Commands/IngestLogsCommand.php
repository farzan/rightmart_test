<?php

declare(strict_types=1);

namespace Adapter\Primary\Commands;

use Application\Ports\Output\TextLineConsumerInterface;
use Application\Services\TextStreamReaderBuilder;
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
        private readonly TextLineConsumerInterface $lineConsumer,
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
        
        $shouldStop = false;
        
        $builder = $this->readerBuilder;
        $builder
            ->setConsumer($this->lineConsumer)
            ->setFilename($input->getArgument('logfile'))
            ->setStopSignaler(static function () use (&$shouldStop) {
                return $shouldStop;
            })
            ->shouldTail(!$input->getOption('no-tail'))
            ->build();

        
        $signalHandler = static function (int $signal) use (&$shouldStop, $io) {
            $shouldStop = true;
            
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
