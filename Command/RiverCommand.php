<?php

namespace Bangpound\Bundle\ElasticsearchBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\Output;

/**
 * Reset search rivers
 */
class RiverCommand extends ContainerAwareCommand
{
    /**
     * @see Symfony\Component\Console\Command\Command::configure()
     */
    protected function configure()
    {
        $this
            ->setName('bangpound:elasticsearch:river')
            ->addOption('name', null, InputOption::VALUE_REQUIRED, 'The river to reset')
            ->setDescription('Reset search rivers')
        ;
    }

    /**
     * @see Symfony\Component\Console\Command\Command::execute()
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $name  = $input->getOption('name');

        $output->writeln(sprintf('<info>Resetting</info> river <comment>%s</comment>', $name));
        $client = new \Elasticsearch\Client();
    }
}
