<?php
/**
 * Created by PhpStorm.
 * User: andreas.dziemba
 * Date: 07.05.2018
 * Time: 09:31
 */

namespace MetaModels\AttributeArticleBundle\Table;

use Symfony\Component\HttpFoundation\Session\SessionInterface;

/**
 * Provide miscellaneous methods that are used by the data configuration array.
 *
 * @author Leo Feyer <https://github.com/leofeyer>
 */
class ArticleContent extends \tl_content
{
    /**
     * Import the back end user object
     */
    public function __construct()
    {
        parent::__construct();
        $this->import('BackendUser', 'User');
    }

    public function save(\DataContainer $dc)
    {
        \Database::getInstance()
            ->prepare('UPDATE tl_content SET mm_slot=?, mm_lang=? WHERE id=?')
            ->execute(\Input::get('slot'), \Input::get('lang'), $dc->id)
        ;
    }

    /**
     * Check permissions to edit table tl_content
     */
    public function checkPermission()
    {
        /** @var SessionInterface $objSession */
        $objSession = \System::getContainer()->get('session');

        // Prevent deleting referenced elements (see #4898)
        if (\Input::get('act') == 'deleteAll')
        {
            $objCes = $this->Database->prepare("SELECT cteAlias FROM tl_content WHERE (ptable='tl_article' OR ptable='') AND type='alias'")
                ->execute();

            $session = $objSession->all();
            $session['CURRENT']['IDS'] = array_diff($session['CURRENT']['IDS'], $objCes->fetchEach('cteAlias'));
            $objSession->replace($session);
        }

        if ($this->User->isAdmin)
        {
            return;
        }

        $strParentTable = \Input::get('ptable');
        $strParentTable = preg_replace('#[^A-Za-z0-9_]#', '', $strParentTable);

        // Check the current action
        switch (\Input::get('act'))
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
                    $this->redirect('contao?act=error');
                }
                break;

            case 'editAll':
            case 'deleteAll':
            case 'overrideAll':
            case 'cutAll':
            case 'copyAll':
                // Check access to the parent element if a content element is moved
                if ((\Input::get('act') == 'cutAll' || \Input::get('act') == 'copyAll') && !$this->checkAccessToElement(\Input::get('pid'), $strParentTable))
                {
                    $this->redirect('contao?act=error');
                }

                $objCes = $this->Database->prepare("SELECT id FROM tl_content WHERE ptable=? AND pid=?")
                    ->execute($strParentTable, CURRENT_ID);

                $session = $this->Session->getData();
                $session['CURRENT']['IDS'] = array_intersect($session['CURRENT']['IDS'], $objCes->fetchEach('id'));
                $objSession->replace($session);
                break;

            case 'cut':
            case 'copy':
                // Check access to the parent element if a content element is moved
                if (!$this->checkAccessToElement(\Input::get('pid'), $strParentTable))
                {
                    $this->redirect('contao?act=error');
                }
            // NO BREAK STATEMENT HERE

            default:
                // Check access to the content element
                if (!$this->checkAccessToElement(\Input::get('id'), $strParentTable))
                {
                    $this->redirect('contao?act=error');
                }
                break;
        }
    }

    /**
     * Check access to a particular content element
     *
     * @param integer $id
     * @param array   $ptable
     * @param boolean $blnIsPid
     *
     * @throws \Contao\CoreBundle\Exception\AccessDeniedException
     */
    protected function checkAccessToElement($id, $ptable, $blnIsPid=false)
    {
        $strScript = \Environment::get('script');

        if ($strScript != 'contao/page.php' && $strScript != 'contao/file.php') // Workaround for missing ptable when called via Page/File Picker
        {
            if ($blnIsPid) {
                $objContent = $this->Database->prepare("SELECT 1 FROM `$ptable` WHERE id=?")
                    ->limit(1)
                    ->execute($id);
            } else {
                $objContent = $this->Database->prepare("SELECT 1 FROM tl_content WHERE id=? AND ptable=?")
                    ->limit(1)
                    ->execute($id, $ptable);
            }
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
