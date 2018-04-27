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

namespace MetaModels\AttributeArticleBundle\EventListener;

use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\ManipulateWidgetEvent;
use ContaoCommunityAlliance\DcGeneral\Event\PostDuplicateModelEvent;
use ContaoCommunityAlliance\DcGeneral\Event\PostPasteModelEvent;
use MetaModels\DcGeneral\Data\Driver;
use MetaModels\DcGeneral\Data\Model;

/**
 * Handles event operations on tl_metamodel_dcasetting.
 */
class BackendEventListener
{
	private $intDuplicationSourceId;

	/**
	 * Set the language for the widget.
	 *
	 * @param ManipulateWidgetEvent $event The event.
	 *
	 * @return void
	 */
	public function setWidgetLanguage(ManipulateWidgetEvent $event)
	{
		if ($event->getWidget()->type != 'article') {
		    return;
		}
		/* @var Driver $dataProvider */
		$dataProvider = $event->getEnvironment()->getDataProvider($event->getModel()->getProviderName());
		$language     = $dataProvider->getCurrentLanguage() ?: '-';

		$event->getWidget()->lang = $language;
	}


	/**
	 * @param PostDuplicateModelEvent $event The event.
	 *
	 * @return void
	 */
	public function handlePostDuplicationModel(PostDuplicateModelEvent $event)
	{
		/* @var Model $objSourceModel */
		$objSourceModel = $event->getSourceModel();

		/* @var Model $objDestinationModel */
		$objDestinationModel = $event->getModel();

		$strTable         = $objDestinationModel->getProviderName();
		$intSourceId      = $objSourceModel->getId();
		$intDestinationId = $objDestinationModel->getId();

		if ($intDestinationId) {
			$this->duplicateContentEntries($strTable, $intSourceId, $intDestinationId);
		} else {
			$this->intDuplicationSourceId = $intSourceId;
		}
	}


	/**
	 * @param PostPasteModelEvent $event The event.
	 *
	 * @return void
	 */
	public function handlePostPasteModel(PostPasteModelEvent $event)
	{
		if (!$this->intDuplicationSourceId) {
			return;
		}

		/* @var Model $objDestinationModel */
		$objDestinationModel = $event->getModel();

		$strTable         = $objDestinationModel->getProviderName();
		$intSourceId      = $this->intDuplicationSourceId;
		$intDestinationId = $objDestinationModel->getId();

		$this->duplicateContentEntries($strTable, $intSourceId, $intDestinationId);
	}


	/**
	 * Duplicate the content entries
	 *
	 * @param string $strTable
	 * @param int $intSourceId
	 * @param int $intDestinationId
	 *
	 * @return void
	 */
	private function duplicateContentEntries($strTable, $intSourceId, $intDestinationId)
	{
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
