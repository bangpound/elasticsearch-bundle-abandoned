<?php

namespace Bangpound\Bundle\ElasticsearchBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\Output;
use Bangpound\Bundle\ElasticsearchBundle\Resetter;

/**
 * Reset search indexes
 */
class ResetCommand extends ContainerAwareCommand
{
    /**
     * @var Resetter
     */
    private $resetter;

    /**
     * @see Symfony\Component\Console\Command\Command::configure()
     */
    protected function configure()
    {
        $this
            ->setName('bangpound:elasticsearch:reset')
            ->addOption('index', null, InputOption::VALUE_OPTIONAL, 'The index to reset')
            ->addOption('type', null, InputOption::VALUE_OPTIONAL, 'The type to reset')
            ->setDescription('Reset search indexes')
        ;
    }

    /**
     * @see Symfony\Component\Console\Command\Command::initialize()
     */
    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->resetter = $this->getContainer()->get('bangpound_elasticsearch.resetter');
    }

    /**
     * @see Symfony\Component\Console\Command\Command::execute()
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $index  = $input->getOption('index');
        $type   = $input->getOption('type');

        if (null === $index && null !== $type) {
            throw new \InvalidArgumentException('Cannot specify type option without an index.');
        }

        if (null !== $type) {
            $output->writeln(sprintf('<info>Resetting</info> <comment>%s/%s</comment>', $index, $type));
            $this->resetter->resetIndexType($index, $type);
        } else {
            if (null === $index) {
                $this->resetter->resetAllIndexes();
                $output->writeln(sprintf('<info>Resetting</info> all indexes', $index));
            } else {
                $indexes = array($index);

                foreach ($indexes as $index) {
                    $output->writeln(sprintf('<info>Resetting</info> <comment>%s</comment>', $index));
                    $this->resetter->resetIndex($index);
                }
            }
        }
    }
}
