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

$GLOBALS['TL_DCA']['tl_content']['fields']['mm_slot'] = [
	'sql' => "varchar(255) NOT NULL default ''",
];

$GLOBALS['TL_DCA']['tl_content']['fields']['mm_lang'] = [
	'sql' => "varchar(5) NOT NULL default ''",
];

if (Input::get('do') == 'article' && Input::get('type') == 'metamodels_article') {
	$GLOBALS['TL_DCA']['tl_content']['config']['dataContainer']       = 'TableMetaModelsArticle';
	$GLOBALS['TL_DCA']['tl_content']['config']['ptable']              = Input::get('ptable');
	$GLOBALS['TL_DCA']['tl_content']['config']['onsubmit_callback'][] = ['mm_tl_content', 'save'];
	$GLOBALS['TL_DCA']['tl_content']['list']['sorting']['filter'][]   = ['mm_slot=?', Input::get('slot')];
	$GLOBALS['TL_DCA']['tl_content']['list']['sorting']['filter'][]   = ['mm_lang=?', Input::get('lang')];
}


class mm_tl_content extends Backend
{

	public function save($dc)
	{
		Database::getInstance()
			->prepare('UPDATE tl_content SET mm_slot=?, mm_lang=? WHERE id=?')
			->execute(Input::get('slot'), Input::get('lang'), $dc->id)
		;
	}

}
