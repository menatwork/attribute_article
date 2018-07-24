<?php

/**
 * Lists
 */
$GLOBALS['TL_DCA']['tl_content']['list']['sorting']['child_record_callback'] = array('MenAtWork\ContentSelectionBundle\\Contao\\Controller\\ContentSelectController', 'childRecordCallback');

/**
 * Palettes
 */
foreach ($GLOBALS['TL_DCA']['tl_content']['palettes'] as $key => $row)
{
    if ($key == '__selector__')
    {
        continue;
    }

    $arrPalettes = explode(";", $row);
    $arrPalettes[] = '{contentSelection_legend},contentSelection';

    $GLOBALS['TL_DCA']['tl_content']['palettes'][$key] = implode(";", $arrPalettes);
}

/**
 * Fields
 */
$GLOBALS['TL_DCA']['tl_content']['fields']['contentSelection'] = array
(
    'label'     => &$GLOBALS['TL_LANG']['tl_content']['contentSelection'],
    'exclude'   => true,
    'inputType' => 'multiColumnWizard',
    'eval'      => array
    (
        'columnFields' => array
        (
            'cs_client_os' => array
            (
                'label' => &$GLOBALS['TL_LANG']['tl_content']['cs_client_os'],
                'exclude' => true,
                'inputType' => 'select',
                //'options_callback' => array('MenAtWork\ContentSelectionBundle\\Contao\\ContentSelection', 'getClientOs'),
                'eval' => array(
                    'style' => 'width:158px',
                    'chosen' => true,
                    'includeBlankOption' => true
                )
            ),
            'cs_client_browser' => array
            (
                'label' => &$GLOBALS['TL_LANG']['tl_content']['cs_client_browser'],
                'exclude' => true,
                'inputType' => 'select',
                //'options_callback' => array('MenAtWork\ContentSelectionBundle\\Contao\\ContentSelection', 'getClientBrowser'),
                'eval' => array(
                    'style' => 'width:158px',
                    'chosen' => true,
                    'includeBlankOption' => true
                )
            ),
            'cs_client_browser_operation' => array
            (
                'label' => &$GLOBALS['TL_LANG']['tl_content']['cs_client_browser_operation'],
                'inputType' => 'select',
                'options' => array(
                    'lt' => '<',
                    'lte' => '<=',
                    'gte' => '>=',
                    'gt' => '>'
                ),
                'eval' => array(
                    'style' => 'width:70px',
                    'chosen' => true,
                    'includeBlankOption' => true
                )
            ),
            'cs_client_browser_version' => array
            (
                'label' => &$GLOBALS['TL_LANG']['tl_content']['cs_client_browser_version'],
                'inputType' => 'text',
                'eval' => array(
                    'style' => 'width:70px'
                )
            ),
            'cs_client_is_mobile' => array
            (
                'label' => &$GLOBALS['TL_LANG']['tl_content']['cs_client_is_mobile'],
                'exclude' => true,
                'inputType' => 'select',
                'options' => array(
                    '1' => $GLOBALS['TL_LANG']['MSC']['yes'],
                    '2' => $GLOBALS['TL_LANG']['MSC']['no']
                ),
                'eval' => array(
                    'includeBlankOption' => true,
                    'style' => 'width:70px',
                    'chosen' => true
                )
            ),
            'cs_client_is_invert' => array
            (
                'label' => &$GLOBALS['TL_LANG']['tl_content']['cs_client_is_invert'],
                'exclude' => true,
                'inputType' => 'checkbox',
                'eval' => array(
                    'style' => 'min-width:30px'
                )
            )
        )
    ),
    'sql'       => "blob NULL"
);