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
 * Back end modules
 */
$GLOBALS['BE_MOD']['content']['metamodels_article'] = [
	'tables' => ['tl_content'],
];

/**
 * Style sheet
 */
if (TL_MODE == 'BE') {
	$GLOBALS['TL_CSS'][] = 'system/modules/metamodelsattribute_article/html/style.css';
}
