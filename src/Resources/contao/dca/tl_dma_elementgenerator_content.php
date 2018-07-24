<?php
/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2005-2015 Leo Feyer
 *
 * @license LGPL-3.0+
 */


/*
 * Callbacks
 */
$GLOBALS['TL_DCA']['tl_content']['config']['onload_callback'][] = array('DMA\\DMAElementGeneratorCallbacks','content_onload');


/*
 * Fields
 */
$GLOBALS['TL_DCA']['tl_content']['fields']['dma_eg_data'] = array
(
    'sql'                     => "longtext NULL"
);
$GLOBALS['TL_DCA']['tl_content']['fields']['dmaElementTpl'] = array
(
    'label'                   => &$GLOBALS['TL_LANG']['tl_content']['dmaElementTpl'],
    'exclude'                 => true,
    'inputType'               => 'select',
    'options_callback'        => array('MetaModels\\AttributeArticleBundle\\Table\\ArticelDmaElementgenatorContent', 'getDmaElementTemplates'),
    'eval'                    => array('includeBlankOption'=>true, 'chosen'=>true, 'tl_class'=>'w50'),
    'sql'                     => "varchar(64) NOT NULL default ''"
);


// Compatibility
if (TL_MODE == 'BE' && version_compare(VERSION.BUILD, '3.10','>=') && version_compare(VERSION.BUILD, '3.20','<'))
{
    $GLOBALS['TL_JAVASCRIPT'][] = 'system/modules/dma_elementgenerator/html/DMA-uncompressed.js';
}