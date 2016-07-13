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

namespace MetaModels\Attribute\Article;

use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\ManipulateWidgetEvent;
use MetaModels\DcGeneral\Events\BaseSubscriber;


/**
 * Handles event operations on tl_metamodel_dcasetting.
 */
class BackendSubscriber extends BaseSubscriber
{

	/**
	 * Register all listeners to handle creation of a data container.
	 *
	 * @return void
	 */
	protected function registerEventsInDispatcher()
	{
		$this->addListener(ManipulateWidgetEvent::NAME, array($this, 'setLanguage'));
	}

	/**
	 * Set the language for the widget.
	 *
	 * @param ManipulateWidgetEvent $event The event.
	 *
	 * @return void
	 */
	public function setLanguage(ManipulateWidgetEvent $event)
	{
		if ($event->getWidget()->type != 'metamodelsArticle') {
			return;
		}

		$dataProvider = $event->getEnvironment()->getDataProvider($event->getModel()->getProviderName());
		$language     = $dataProvider->getCurrentLanguage() ?: '-';

		$event->getWidget()->lang = $language;
	}

}
