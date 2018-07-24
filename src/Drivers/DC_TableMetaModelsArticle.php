<?php

/**
 * Copyright (c) 2016 by Hinderling Volkart AG
 * All rights reserved
 *
 * http://www.hinderlingvolkart.com/
 *
 * Ronny Binder <rbi@hinderlingvolkart.com>
 *
 */

namespace Contao;

use ContaoCommunityAlliance\DcGeneral\Data\ModelId;
use ContaoCommunityAlliance\DcGeneral\DataContainerInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\ContainerInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinitionContainerInterface;
use ContaoCommunityAlliance\DcGeneral\DC\General;
use ContaoCommunityAlliance\DcGeneral\EnvironmentInterface;
use ContaoCommunityAlliance\DcGeneral\Exception\DcGeneralRuntimeException;
use ContaoCommunityAlliance\DcGeneral\Factory\DcGeneralFactory;
use ContaoCommunityAlliance\DcGeneral\Factory\Event\BuildDataDefinitionEvent;
use ContaoCommunityAlliance\DcGeneral\Factory\Event\PopulateEnvironmentEvent;
use Imagine\Exception\RuntimeException;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use ContaoCommunityAlliance\DcGeneral\Contao\Callback\Callbacks;
use ContaoCommunityAlliance\Translator\TranslatorInterface;

class DC_TableMetaModelsArticle extends DC_Table implements DataContainerInterface
{
    /**
     * @var null|\Symfony\Component\DependencyInjection\ContainerInterface
     */
    private $container = null;

    /**
     * @var null|\ContaoCommunityAlliance\DcGeneral\DcGeneral
     */
    private $dataContainer = null;

    /**
     * The environment instance.
     *
     * @var /EnvironmentInterface
     */
    protected $environment;



    /**
     * DC_TableMetaModelsArticle constructor.
     *
     * @param       $strTable
     * @param array $arrModule
     */
    public function __construct($strTable, array $arrModule = array())
    {

        // Get the container name.
        $do             = \Input::get('do');
        $metaModelsName = substr($do, 10);
        $table = \Input::get('table');

        if (\substr($metaModelsName, 0, 2) != 'mm') {
            throw new \RuntimeException('Only metamodles tables are allowed.');
        }

//        $strTable   = $this->getTablenameCallback($strTable);
        $translator = $this->getTranslator();
//
        $dispatcher = $this->getEventDispatcher();
//        $fetcher    = \Closure::bind(function (PopulateEnvironmentEvent $event) use ($strTable) {
//            // We need to capture the correct environment and save it for later use.
//            if ($strTable !== $event->getEnvironment()->getDataDefinition()->getName()) {
//                return;
//            }
//            $this->environment = $event->getEnvironment();
//        }, $this, $this);
//
//        $dispatcher->addListener(PopulateEnvironmentEvent::NAME, $fetcher, 4800);
        $modelId = ModelId::fromSerialized('mm_member::12');

        $factory = new DcGeneralFactory();
        $general = $factory
            ->setContainerName($modelId->getDataProviderName())
            ->setTranslator($translator)
            ->setEventDispatcher($dispatcher)
            ->createDcGeneral();

        $this->environment = $general->getEnvironment();

//        $factory = new DcGeneralFactory();
//
//        $factory
//            ->setContainerName($strTable)
//            ->setEventDispatcher($dispatcher)
//            ->setTranslator($translator)
//            ->createEnvironment();
//
//        $dispatcher->removeListener(PopulateEnvironmentEvent::NAME, $fetcher);


        // Init the parent.
        parent::__construct($table, $arrModule);

        $this->container = \System::getContainer();
    }

    /**
     * @return null|string|string[]
     */
    protected function parentView()
    {
        return preg_replace(
            [
                // "Edit parent" Button
                '#<div class="tl_header [^>]*>\n<div class="tl_content_right">\n<a #',
                // Parent entry info
                '#<td><span class="tl_label">tstamp:</span>.*\n.*</td>#',
            ],
            [
                '$0style="display:none" ',
                '<td>&nbsp;</td>',
            ],
            parent::parentView()
        );
    }

    /**
     * Create a new instance.
     *
     * @param /EnvironmentInterface $environment The environment.
     */
    public function setEnvironment(EnvironmentInterface $environment)
    {
        $this->environment = $environment;
    }

    public function getEnvironment()
    {
        return $this->environment;

    }


    /**
     * Call the table name callback.
     *
     * @param string $strTable The current table name.
     *
     * @return string New name of current table.
     *
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    protected function getTablenameCallback($strTable)
    {
        if (isset($GLOBALS['TL_DCA'][$strTable]['config']['tablename_callback'])
            && \is_array($GLOBALS['TL_DCA'][$strTable]['config']['tablename_callback'])
        ) {
            foreach ($GLOBALS['TL_DCA'][$strTable]['config']['tablename_callback'] as $callback) {
                $strCurrentTable = Callbacks::call($callback, $strTable, $this);

                if ($strCurrentTable != null) {
                    $strTable = $strCurrentTable;
                }
            }
        }

        return $strTable;
    }

    /**
     * Retrieve the event dispatcher from the DIC.
     *
     * @return EventDispatcherInterface
     */
    private function getEventDispatcher()
    {
        return System::getContainer()->get('event_dispatcher');
    }

    /**
     * Get the translator from the service container.
     *
     * @return TranslatorInterface
     */
    private function getTranslator()
    {
        return System::getContainer()->get('cca.translator.contao_translator');
    }

}
