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

namespace Contao;

class DC_TableMetaModelsArticle extends DC_Table //implements DataContainerInterface
{

    /**
     * Create a new instance.
     *
     * @param string $strTable The table name.
     *
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    public function __construct($strTable)
    {
        parent::__construct($strTable);
        dump("__construct");
        dump($GLOBALS['BE_MOD']['content']['metamodel_mm_member']);
    }

    /**
     * @return null|string|string[]
     */
    protected function parentView()
    {
        return preg_replace(
            [
                // "Edit parent" Button
                '#<div class="tl_header [^>]*>\n<div class="tl_content_right">\n<a #',
                // Parent entry info
                '#<td><span class="tl_label">tstamp:</span>.*\n.*</td>#',
            ],
            [
                '$0style="display:none" ',
                '<td>&nbsp;</td>',
            ],
            parent::parentView()
        );
    }

}
