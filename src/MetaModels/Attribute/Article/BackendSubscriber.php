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
use ContaoCommunityAlliance\DcGeneral\Event\PostDuplicateModelEvent;
use ContaoCommunityAlliance\DcGeneral\Event\PostPasteModelEvent;
use MetaModels\DcGeneral\Data\Driver;
use MetaModels\DcGeneral\Data\Model;
use MetaModels\DcGeneral\Events\BaseSubscriber;


/**
 * Handles event operations on tl_metamodel_dcasetting.
 */
class BackendSubscriber extends BaseSubscriber
{
	private $intDuplicationSourceId;

	/**
	 * Register all listeners to handle creation of a data container.
	 *
	 * @return void
	 */
	protected function registerEventsInDispatcher()
	{
		$this->addListener(ManipulateWidgetEvent::NAME, array($this, 'setWidgetLanguage'));
		$this->addListener(PostDuplicateModelEvent::NAME, array($this, 'setDuplicationSourceId'));
		$this->addListener(PostPasteModelEvent::NAME, array($this, 'duplicateContentEntries'));
	}

	/**
	 * Set the language for the widget.
	 *
	 * @param ManipulateWidgetEvent $event The event.
	 *
	 * @return void
	 */
	public function setWidgetLanguage(ManipulateWidgetEvent $event)
	{
		if ($event->getWidget()->type != 'metamodelsArticle') {
			return;
		}

		/* @var Driver $dataProvider */
		$dataProvider = $event->getEnvironment()->getDataProvider($event->getModel()->getProviderName());
		$language     = $dataProvider->getCurrentLanguage() ?: '-';

		$event->getWidget()->lang = $language;
	}

	/**
	 * Set the source id for duplicating the content entries below.
	 *
	 * @param PostDuplicateModelEvent $event The event.
	 *
	 * @return void
	 */
	public function setDuplicationSourceId(PostDuplicateModelEvent $event)
	{
		/* @var Model $objModel */
		$objModel = $event->getSourceModel();

		$this->intDuplicationSourceId = $objModel->getId();
	}

	/**
	 * Duplicate the content entries
	 *
	 * @param PostPasteModelEvent $event The event.
	 *
	 * @return void
	 */
	public function duplicateContentEntries(PostPasteModelEvent $event)
	{
		if (!$this->intDuplicationSourceId) {
			return;
		}

		/* @var Model $objModel */
		$objModel = $event->getModel();

		$strTable         = $objModel->getProviderName();
		$intSourceId      = $this->intDuplicationSourceId;
		$intDestinationId = $objModel->getId();

		$objContent = \Database::getInstance()
			->prepare('SELECT * FROM tl_content WHERE pid=? AND ptable=?')
			->execute($intSourceId, $strTable)
		;

		for ($i=0; $objContent->next(); $i++)
		{
			$arrContent = $objContent->row();
			$arrContent['pid'] = $intDestinationId;
			unset($arrContent['id']);

			\Database::getInstance()
				->prepare('INSERT INTO tl_content %s')
				->set($arrContent)
				->execute()
			;
		}
	}

}
