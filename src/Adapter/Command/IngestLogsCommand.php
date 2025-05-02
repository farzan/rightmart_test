<?php

declare(strict_types=1);

namespace App\Adapter\Command;

use App\Domain\Port\TextLineConsumerInterface;
use App\Domain\TextStreamReader;
use App\Domain\TextStreamReaderBuilder;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

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
        
        pcntl_signal(SIGINT, static function (int $signal) use (&$shouldStop, $output) {
            $shouldStop = true;
            $output->writeln("\n\nStopping gracefully...");
        });
        
        $reader = $builder->build();
        
        $reader->start();
        $output->writeln("Done.");
        
        return self::SUCCESS;
    }
}
