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
if(class_exists('DMABundle')) {
    $GLOBALS['TL_DCA']['tl_content']['config']['onload_callback'][] = array(
        'DMA\\DMABundle\\Contao\\DMAElementGeneratorCallbacks',
        'content_onload'
    );
}else {
    $GLOBALS['TL_DCA']['tl_content']['config']['onload_callback'][] = array('DMA\\DMAElementGeneratorCallbacks','content_onload');
}


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
if (TL_MODE == 'BE' && class_exists('DMABundle'))
{
    $GLOBALS['TL_JAVASCRIPT'][] = 'bundles/dma/DMA-uncompressed.js';
}elseif (TL_MODE == 'BE') {
    $GLOBALS['TL_JAVASCRIPT'][] = 'system/modules/dma_elementgenerator/html/DMA-uncompressed.js';
}