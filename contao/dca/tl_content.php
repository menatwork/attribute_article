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

$strModule = Input::get('do');
$strTable  = Input::get('table');

if (substr($strModule, 0, 10) == 'metamodel_' && $strTable == 'tl_content') {
	$GLOBALS['TL_DCA']['tl_content']['config']['dataContainer']                         = 'TableMetaModelsArticle';
	$GLOBALS['TL_DCA']['tl_content']['config']['ptable']                                = Input::get('ptable');
	$GLOBALS['TL_DCA']['tl_content']['config']['onsubmit_callback'][]                   = ['mm_tl_content', 'save'];
    $GLOBALS['TL_DCA']['tl_content']['config']['onload_callback'][]                     = ['mm_tl_content', 'checkPermission'];
    $GLOBALS['TL_DCA']['tl_content']['list']['operations']['toggle']['button_callback'] = ['mm_tl_content', 'toggleIcon'];
	$GLOBALS['TL_DCA']['tl_content']['list']['sorting']['filter'][]                     = ['mm_slot=?', Input::get('slot')];
	$GLOBALS['TL_DCA']['tl_content']['list']['sorting']['filter'][]                     = ['mm_lang=?', Input::get('lang')];

	$GLOBALS['TL_DCA']['tl_content']['list']['global_operations']['addMainLangContent'] = [
		'label'      => &$GLOBALS['TL_LANG']['tl_content']['addMainLangContent'],
		'href'       => 'key=addMainLangContent',
		'class'      => 'header_new',
		'attributes' => 'onclick="Backend.getScrollOffset()"',
    ];
}


class mm_tl_content extends tl_content
{

    public function __construct()
    {
        parent::__construct();
    }

    public function save($dc)
	{
		Database::getInstance()
			->prepare('UPDATE tl_content SET mm_slot=?, mm_lang=? WHERE id=?')
			->execute(Input::get('slot'), Input::get('lang'), $dc->id)
		;
	}

    /**
     * Copy of system/modules/core/dca/tl_content.php (Contao 3.5.14)
     */
    public function checkPermission()
    {
        if ($this->User->isAdmin)
        {
            return;
        }

        $strModule      = Input::get('do');
        $strParentTable = substr($strModule, 10);

        // Check the current action
        switch (Input::get('act'))
        {
            case 'paste':
                // Allow
                break;

            case '': // empty
            case 'create':
            case 'select':
                // Check access to the article
                if (!$this->checkAccessToElement(CURRENT_ID, $strParentTable, true))
                {
                    $this->redirect('contao/main.php?act=error');
                }
                break;

            case 'editAll':
            case 'deleteAll':
            case 'overrideAll':
            case 'cutAll':
            case 'copyAll':
                // Check access to the parent element if a content element is moved
                if ((Input::get('act') == 'cutAll' || Input::get('act') == 'copyAll') && !$this->checkAccessToElement(Input::get('pid'), $strParentTable))
                {
                    $this->redirect('contao/main.php?act=error');
                }

                $objCes = $this->Database->prepare("SELECT id FROM tl_content WHERE ptable=? AND pid=?")
                    ->execute($strParentTable, CURRENT_ID);

                $session = $this->Session->getData();
                $session['CURRENT']['IDS'] = array_intersect($session['CURRENT']['IDS'], $objCes->fetchEach('id'));
                $this->Session->setData($session);
                break;

            case 'cut':
            case 'copy':
                // Check access to the parent element if a content element is moved
                if (!$this->checkAccessToElement(Input::get('pid'), $strParentTable))
                {
                    $this->redirect('contao/main.php?act=error');
                }
                // NO BREAK STATEMENT HERE

            default:
                // Check access to the content element
                if (!$this->checkAccessToElement(Input::get('id'), $strParentTable))
                {
                    $this->redirect('contao/main.php?act=error');
                }
                break;
        }
    }

    /**
     * Copy of system/modules/core/dca/tl_content.php (Contao 3.5.14)
     *
     * @param integer $id
     *
     * @return boolean
     */
    protected function checkAccessToElement($id, $ptable, $blnIsPid=false)
    {
        if ($blnIsPid)
        {
            $objContent = $this->Database->prepare("SELECT 1 FROM tl_content WHERE pid=? AND ptable=?")
                ->limit(1)
                ->execute($id, $ptable);
        }
        else
        {
            $objContent = $this->Database->prepare("SELECT 1 FROM tl_content WHERE id=? AND ptable=?")
                ->limit(1)
                ->execute($id, $ptable);
        }

        // Invalid ID
        if ($objContent->numRows < 1)
        {
            $this->log('Invalid content element ID ' . $id, __METHOD__, TL_ERROR);

            return false;
        }

        return true;
    }

}
