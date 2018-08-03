<?php
/**
 * Created by PhpStorm.
 * User: andreas.dziemba
 * Date: 26.06.2018
 * Time: 09:34
 */

namespace MetaModels\AttributeArticleBundle\EventListener;

use MetaModels\IFactory;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetPropertyOptionsEvent;
use MultiColumnWizard\Event\GetOptionsEvent;

/**
 * Handle events for tl_metamodel_attribute.
 */
class GetOptionsListener
{
    /**
     * The factory.
     *
     * @var IFactory
     */
    private $factory;

    /**
     * Create a new instance.
     *
     * @param IFactory $factory The factory.
     */
    public function __construct(IFactory $factory)
    {
        $this->factory = $factory;
    }

    public function getPropertyOptions(GetPropertyOptionsEvent $event)
    {

    }

    /* Retrieve the options for the attributes.
    *
    * @param GetOptionsEvent $event The event.
    *
    * @return void
    */
    public function getOptions(GetOptionsEvent $event)
    {

        $model = $event->getModel();

        switch ($event->getPropertyName()) {
            case '':
                break;
            default:
                break;
        }
    }
}