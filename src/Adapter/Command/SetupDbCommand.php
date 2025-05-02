<?php

declare(strict_types=1);

namespace App\Adapter\Command;

use App\Domain\Port\Database\DatabasePreparerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'app:setup-db', description: 'Create database from scratch')]
class SetupDbCommand extends Command
{
    public function __construct(
        private readonly DatabasePreparerInterface $preparer,
    ) {
        parent::__construct();
    }
    
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        
//        $io->warning('You will lose all data! Are you sure?');
//        $question = new Question('Type "YES!" if you are sure');
//        $answer = $io->askQuestion($question);
//
//        if ($answer !== 'YES!') {
//            $io->writeln('Aborted.');
//            return self::FAILURE;
//        }
        
        $this->preparer->prepare();
        $io->writeln([
            'Old index dropped',
            'Old mapping templated dropped',
            'New mapping template created',
        ]);
        
        return self::SUCCESS;
    }
}
