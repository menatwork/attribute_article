<?php

/**
 * Copyright (c) 2016 by Hinderling Volkart AG
 * All rights reserved
 *
 * http://www.hinderlingvolkart.com/
 *
 * Ronny Binder <rbi@hinderlingvolkart.com>
 * Andreas Dziemba <dziemba@men-at-work.de>
 *
 */

$GLOBALS['TL_DCA']['tl_content']['fields']['mm_slot'] = [
    'sql' => "varchar(255) NOT NULL default ''",
];

$GLOBALS['TL_DCA']['tl_content']['fields']['mm_lang'] = [
    'sql' => "varchar(5) NOT NULL default ''",
];

$strModule = \Input::get('do');
$strTable  = \Input::get('table');


//change TL_Content for the article popup
if (\substr($strModule, 0, 10) == 'metamodel_' && $strTable == 'tl_content') {
    $GLOBALS['TL_DCA']['tl_content']['config']['dataContainer']                         = 'TableMetaModelsArticle';
    $GLOBALS['TL_DCA']['tl_content']['config']['ptable']                                = \Input::get('ptable');
    $GLOBALS['TL_DCA']['tl_content']['config']['onsubmit_callback'][]                   = array(
        'MetaModels\\AttributeArticleBundle\\Table\\ArticleContent',
        'save'
    );
    $GLOBALS['TL_DCA']['tl_content']['config']['onload_callback'] []                    = array (
        'MetaModels\\AttributeArticleBundle\\Table\\ArticleContent',
        'checkPermission'
    );
    $GLOBALS['TL_DCA']['tl_content']['list']['operations']['toggle']['button_callback'] = array(
        'MetaModels\\AttributeArticleBundle\\Table\\ArticleContent',
        'toggleIcon'
    );
    $GLOBALS['TL_DCA']['tl_content']['list']['sorting']['filter'][]                     = array(
        'mm_slot=?',
        \Input::get('slot')
    );
    $GLOBALS['TL_DCA']['tl_content']['list']['sorting']['filter'][]                     = array(
        'mm_lang=?',
        \Input::get('lang')
    );

    $GLOBALS['TL_DCA']['tl_content']['list']['global_operations']['addMainLangContent'] = [
        'label'      => &$GLOBALS['TL_LANG']['tl_content']['addMainLangContent'],
        'href'       => 'key=addMainLangContent',
        'class'      => 'header_new',
        'attributes' => 'onclick="Backend.getScrollOffset()"',
    ];
}
