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

/**
 * Register backend form fields
 */
$GLOBALS['BE_FFL']['MetaModelAttributeArticle'] = 'MetaModels\\AttributeArticleBundle\\Widgets\\Article';

/**
 * Register hooks
 */
$GLOBALS['TL_HOOKS']['initializeSystem'][] = ['metamodelsattribute_article', 'initializeSystem'];


class metamodelsattribute_article extends Backend
{

	public function initializeSystem()
	{
		$strModule = Input::get('do');
		$strTable  = Input::get('table');

		if (substr($strModule, 0, 10) == 'metamodel_' && $strTable == 'tl_content') {
			$GLOBALS['BE_MOD']['content'][$strModule]['tables'][] = 'tl_content';
			$GLOBALS['BE_MOD']['content'][$strModule]['callback'] = null;
			$GLOBALS['BE_MOD']['content'][$strModule]['addMainLangContent'] = ['metamodelsattribute_article', 'addMainLangContent'];
		}
	}

	public function addMainLangContent($dc)
	{
		$factory = $GLOBALS['container']['metamodels.attribute_article.factory'];
		/** @var \MetaModels\IFactory $factory */
		$objMetaModel = $factory->getMetaModel($dc->parentTable);

		$intId           = $dc->id;
		$strParentTable  = $dc->parentTable;
		$strSlot         = Input::get('slot');
		$strLanguage     = Input::get('lang');
		$strMainLanguage = $objMetaModel->getFallbackLanguage();

		if ($strLanguage == $strMainLanguage) {
			Message::addError('Hauptsprache kann nicht in die Hauptsprache kopiert werden.'); // TODO übersetzen
			Controller::redirect(System::getReferer());
			return;
		}

		$objContent = Database::getInstance()
			->prepare('SELECT * FROM tl_content WHERE pid=? AND ptable=? AND mm_slot=? AND mm_lang=?')
			->execute($intId, $strParentTable, $strSlot, $strMainLanguage)
		;

		for ($i=0; $objContent->next(); $i++)
		{
			$arrContent = $objContent->row();
			$arrContent['mm_lang'] = $strLanguage;
			unset($arrContent['id']);

			Database::getInstance()
				->prepare('INSERT INTO tl_content %s')
				->set($arrContent)
				->execute()
			;
		}

		Message::addInfo(sprintf('%s Element(e) kopiert', $i)); // TODO übersetzen
		Controller::redirect(System::getReferer());
	}

}
