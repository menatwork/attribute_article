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
$GLOBALS['BE_FFL']['metamodelsArticle'] = 'MetaModelsArticle';

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

	public function addMainLangContent()
	{
		return 'test';
	}

}
