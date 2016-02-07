<?php

namespace WADE\CoreBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use WADE\CoreBundle\Manager\PhobiaManager;
use WADE\CoreBundle\Service\DbpediaService;

class ImportPhobiaCommand extends ContainerAwareCommand
{
    /** @var InputInterface $input */
    protected $input;

    /** @var OutputInterface $output */
    protected $output;

    /** @var DbpediaService $dbpediaService */
    protected $dbpediaService;

    /** @var PhobiaManager $phobiaManger */
    protected $phobiaManger;

    protected function configure()
    {
        $this
            ->setName('wade-phos:core:import-phobia')
            ->setHelp('Imports all phobias from Dbpedia into Stardog.')
            ->setDescription('Imports all phobias from Dbpedia into Stardog.');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return null
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->input  = $input;
        $this->output = $output;
        $this->dbpediaService = $this->getContainer()->get('wade_core.service.dbpedia');
        $this->phobiaManger = $this->getContainer()->get('wade_core.manager.phobia_manager');

        /** @var \EasyRdf_Sparql_Result $phobiaArr */
        $phobiaArr = $this->dbpediaService->queryPhobias();

        $i = 0;
        foreach ($phobiaArr as $phobia) {
            $id = $phobia->id->getValue();
            $label = $phobia->label->getValue();
            $info = $phobia->info->getValue();
            $link = $phobia->link->getUri();

            $this->phobiaManger->updateDatabase($id, $label, $info, $link);

            $this->output->writeln("Imported phobia: " . $label . ' data: ' . print_r([$id, $label, $info, $link], true));
            $i++;
        }

        $this->output->writeln('Total number of phobias imported: ' . $i);

        return null;
    }
}
