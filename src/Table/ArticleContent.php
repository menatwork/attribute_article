<?php
/**
 * Created by PhpStorm.
 * User: andreas.dziemba
 * Date: 07.05.2018
 * Time: 09:31
 */

namespace MetaModels\AttributeArticleBundle\Table;


use Contao\CoreBundle\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

/**
 * Provide miscellaneous methods that are used by the data configuration array.
 *
 * @author Leo Feyer <https://github.com/leofeyer>
 */
class ArticleContent extends \Backend
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
                    $this->redirect('contao/main.php?act=error');
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
                    $this->redirect('contao/main.php?act=error');
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
                    $this->redirect('contao/main.php?act=error');
                }
            // NO BREAK STATEMENT HERE

            default:
                // Check access to the content element
                if (!$this->checkAccessToElement(\Input::get('id'), $strParentTable))
                {
                    $this->redirect('contao/main.php?act=error');
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
            throw new \Contao\CoreBundle\Exception\AccessDeniedException('Invalid content element ID ' . $id . '.');
        }

        // The page is not mounted
        if (!\in_array($objContent->id, $ptable))
        {
            throw new \Contao\CoreBundle\Exception\AccessDeniedException('Not enough permissions to modify article ID ' . $objContent->aid . ' on page ID ' . $objContent->id . '.');
        }

        // Not enough permissions to modify the article
        if (!$this->User->isAllowed(\BackendUser::CAN_EDIT_ARTICLES, $objContent->row()))
        {
            throw new \Contao\CoreBundle\Exception\AccessDeniedException('Not enough permissions to modify article ID ' . $objContent->aid . '.');
        }

        return true;
    }

    /**
     * Return all content elements as array
     *
     * @return array
     */
    public function getContentElements()
    {
        $groups = array();

        foreach ($GLOBALS['TL_CTE'] as $k=>$v)
        {
            foreach (array_keys($v) as $kk)
            {
                $groups[$k][] = $kk;
            }
        }

        return $groups;
    }

    /**
     * Return the group of a content element
     *
     * @param string $element
     *
     * @return string
     */
    public function getContentElementGroup($element)
    {
        foreach ($GLOBALS['TL_CTE'] as $k=>$v)
        {
            foreach (array_keys($v) as $kk)
            {
                if ($kk == $element)
                {
                    return $k;
                }
            }
        }

        return null;
    }

    /**
     * Adjust the DCA by type
     *
     * @param object
     */
    public function adjustDcaByType($dc)
    {
        if ($_POST || \Input::get('act') != 'edit')
        {
            return;
        }

        $objCte = \ContentModel::findByPk($dc->id);

        if ($objCte === null)
        {
            return;
        }

        switch ($objCte->type)
        {
            case 'hyperlink':
                unset($GLOBALS['TL_DCA']['tl_content']['fields']['imageUrl']);
                break;

            case 'image':
                $GLOBALS['TL_DCA']['tl_content']['fields']['imagemargin']['eval']['tl_class'] .= ' clr';
                break;
        }
    }

    /**
     * Show a hint if a JavaScript library needs to be included in the page layout
     *
     * @param object
     */
    public function showJsLibraryHint($dc)
    {
        if ($_POST || \Input::get('act') != 'edit')
        {
            return;
        }

        // Return if the user cannot access the layout module (see #6190)
        if (!$this->User->hasAccess('themes', 'modules') || !$this->User->hasAccess('layout', 'themes'))
        {
            return;
        }

        $objCte = \ContentModel::findByPk($dc->id);

        if ($objCte === null)
        {
            return;
        }

        switch ($objCte->type)
        {
            case 'code':
                \Message::addInfo(sprintf($GLOBALS['TL_LANG']['tl_content']['includeTemplate'], 'js_highlight'));
                break;

            case 'gallery':
                \Message::addInfo(sprintf($GLOBALS['TL_LANG']['tl_content']['includeTemplates'], 'moo_mediabox', 'j_colorbox'));
                break;

            case 'sliderStart':
            case 'sliderStop':
                \Message::addInfo(sprintf($GLOBALS['TL_LANG']['tl_content']['includeTemplate'], 'js_slider'));
                break;

            case 'accordionSingle':
            case 'accordionStart':
            case 'accordionStop':
                \Message::addInfo(sprintf($GLOBALS['TL_LANG']['tl_content']['includeTemplates'], 'moo_accordion', 'j_accordion'));
                break;

            case 'player':
                \Message::addInfo(sprintf($GLOBALS['TL_LANG']['tl_content']['includeTemplate'], 'js_mediaelement'));
                break;

            case 'table':
                if ($objCte->sortable)
                {
                    \Message::addInfo(sprintf($GLOBALS['TL_LANG']['tl_content']['includeTemplates'], 'moo_tablesort', 'j_tablesort'));
                }
                break;
        }
    }

    /**
     * Add the type of content element
     *
     * @param array $arrRow
     *
     * @return string
     */
    public function addCteType($arrRow)
    {
        $key = $arrRow['invisible'] ? 'unpublished' : 'published';
        $type = $GLOBALS['TL_LANG']['CTE'][$arrRow['type']][0] ?: '&nbsp;';
        $class = 'limit_height';

        // Remove the class if it is a wrapper element
        if (\in_array($arrRow['type'], $GLOBALS['TL_WRAPPERS']['start']) || \in_array($arrRow['type'], $GLOBALS['TL_WRAPPERS']['separator']) || \in_array($arrRow['type'], $GLOBALS['TL_WRAPPERS']['stop']))
        {
            $class = '';

            if (($group = $this->getContentElementGroup($arrRow['type'])) !== null)
            {
                $type = $GLOBALS['TL_LANG']['CTE'][$group] . ' (' . $type . ')';
            }
        }

        // Add the group name if it is a single element (see #5814)
        elseif (\in_array($arrRow['type'], $GLOBALS['TL_WRAPPERS']['single']))
        {
            if (($group = $this->getContentElementGroup($arrRow['type'])) !== null)
            {
                $type = $GLOBALS['TL_LANG']['CTE'][$group] . ' (' . $type . ')';
            }
        }

        // Add the ID of the aliased element
        if ($arrRow['type'] == 'alias')
        {
            $type .= ' ID ' . $arrRow['cteAlias'];
        }

        // Add the protection status
        if ($arrRow['protected'])
        {
            $type .= ' (' . $GLOBALS['TL_LANG']['MSC']['protected'] . ')';
        }
        elseif ($arrRow['guests'])
        {
            $type .= ' (' . $GLOBALS['TL_LANG']['MSC']['guests'] . ')';
        }

        // Add the headline level (see #5858)
        if ($arrRow['type'] == 'headline')
        {
            if (\is_array($headline = \StringUtil::deserialize($arrRow['headline'])))
            {
                $type .= ' (' . $headline['unit'] . ')';
            }
        }

        // Limit the element's height
        if (!\Config::get('doNotCollapse'))
        {
            $class .=  ' h40';
        }

        $objModel = new \ContentModel();
        $objModel->setRow($arrRow);

        return '
<div class="cte_type ' . $key . '">' . $type . '</div>
<div class="' . trim($class) . '">
' . \StringUtil::insertTagToSrc($this->getContentElement($objModel)) . '
</div>' . "\n";
    }

    /**
     * Return the edit article alias wizard
     *
     * @param \DataContainer $dc
     *
     * @return string
     */
    public function editArticleAlias(\DataContainer $dc)
    {
        return ($dc->value < 1) ? '' : ' <a href="contao/main.php?do=article&amp;table=tl_content&amp;id=' . $dc->value . '&amp;popup=1&amp;nb=1&amp;rt=' . REQUEST_TOKEN . '" title="' . sprintf(\StringUtil::specialchars($GLOBALS['TL_LANG']['tl_content']['editalias'][1]), $dc->value) . '" onclick="Backend.openModalIframe({\'title\':\'' . \StringUtil::specialchars(str_replace("'", "\\'", sprintf($GLOBALS['TL_LANG']['tl_content']['editalias'][1], $dc->value))) . '\',\'url\':this.href});return false">' . \Image::getHtml('alias.svg', $GLOBALS['TL_LANG']['tl_content']['editalias'][0]) . '</a>';
    }

    /**
     * Get all articles and return them as array (article alias)
     *
     * @param \DataContainer $dc
     *
     * @return array
     */
    public function getArticleAlias(\DataContainer $dc)
    {
        $arrPids = array();
        $arrAlias = array();

        if (!$this->User->isAdmin)
        {
            foreach ($this->User->pagemounts as $id)
            {
                $arrPids[] = $id;
                $arrPids = array_merge($arrPids, $this->Database->getChildRecords($id, 'tl_page'));
            }

            if (empty($arrPids))
            {
                return $arrAlias;
            }

            $objAlias = $this->Database->prepare("SELECT a.id, a.pid, a.title, a.inColumn, p.title AS parent FROM tl_article a LEFT JOIN tl_page p ON p.id=a.pid WHERE a.pid IN(". implode(',', array_map('\intval', array_unique($arrPids))) .") AND a.id!=(SELECT pid FROM tl_content WHERE id=?) ORDER BY parent, a.sorting")
                ->execute($dc->id);
        }
        else
        {
            $objAlias = $this->Database->prepare("SELECT a.id, a.pid, a.title, a.inColumn, p.title AS parent FROM tl_article a LEFT JOIN tl_page p ON p.id=a.pid WHERE a.id!=(SELECT pid FROM tl_content WHERE id=?) ORDER BY parent, a.sorting")
                ->execute($dc->id);
        }

        if ($objAlias->numRows)
        {
            \System::loadLanguageFile('tl_article');

            while ($objAlias->next())
            {
                $key = $objAlias->parent . ' (ID ' . $objAlias->pid . ')';
                $arrAlias[$key][$objAlias->id] = $objAlias->title . ' (' . ($GLOBALS['TL_LANG']['COLS'][$objAlias->inColumn] ?: $objAlias->inColumn) . ', ID ' . $objAlias->id . ')';
            }
        }

        return $arrAlias;
    }

    /**
     * Return the edit alias wizard
     *
     * @param \DataContainer $dc
     *
     * @return string
     */
    public function editAlias(\DataContainer $dc)
    {
        return ($dc->value < 1) ? '' : ' <a href="contao/main.php?do=article&amp;table=tl_content&amp;act=edit&amp;id=' . $dc->value . '&amp;popup=1&amp;nb=1&amp;rt=' . REQUEST_TOKEN . '" title="' . sprintf(\StringUtil::specialchars($GLOBALS['TL_LANG']['tl_content']['editalias'][1]), $dc->value) . '" onclick="Backend.openModalIframe({\'title\':\'' . \StringUtil::specialchars(str_replace("'", "\\'", sprintf($GLOBALS['TL_LANG']['tl_content']['editalias'][1], $dc->value))) . '\',\'url\':this.href});return false">' . \Image::getHtml('alias.svg', $GLOBALS['TL_LANG']['tl_content']['editalias'][0]) . '</a>';
    }

    /**
     * Get all content elements and return them as array (content element alias)
     *
     * @return array
     */
    public function getAlias()
    {
        $arrPids = array();
        $arrAlias = array();

        if (!$this->User->isAdmin)
        {
            foreach ($this->User->pagemounts as $id)
            {
                $arrPids[] = $id;
                $arrPids = array_merge($arrPids, $this->Database->getChildRecords($id, 'tl_page'));
            }

            if (empty($arrPids))
            {
                return $arrAlias;
            }

            $objAlias = $this->Database->prepare("SELECT c.id, c.pid, c.type, (CASE c.type WHEN 'module' THEN m.name WHEN 'form' THEN f.title WHEN 'table' THEN c.summary ELSE c.headline END) AS headline, c.text, a.title FROM tl_content c LEFT JOIN tl_article a ON a.id=c.pid LEFT JOIN tl_module m ON m.id=c.module LEFT JOIN tl_form f on f.id=c.form WHERE a.pid IN(". implode(',', array_map('\intval', array_unique($arrPids))) .") AND (c.ptable='tl_article' OR c.ptable='') AND c.id!=? ORDER BY a.title, c.sorting")
                ->execute(\Input::get('id'));
        }
        else
        {
            $objAlias = $this->Database->prepare("SELECT c.id, c.pid, c.type, (CASE c.type WHEN 'module' THEN m.name WHEN 'form' THEN f.title WHEN 'table' THEN c.summary ELSE c.headline END) AS headline, c.text, a.title FROM tl_content c LEFT JOIN tl_article a ON a.id=c.pid LEFT JOIN tl_module m ON m.id=c.module LEFT JOIN tl_form f on f.id=c.form WHERE (c.ptable='tl_article' OR c.ptable='') AND c.id!=? ORDER BY a.title, c.sorting")
                ->execute(\Input::get('id'));
        }

        while ($objAlias->next())
        {
            $arrHeadline = \StringUtil::deserialize($objAlias->headline, true);

            if (isset($arrHeadline['value']))
            {
                $headline = \StringUtil::substr($arrHeadline['value'], 32);
            }
            else
            {
                $headline = \StringUtil::substr(preg_replace('/[\n\r\t]+/', ' ', $arrHeadline[0]), 32);
            }

            $text = \StringUtil::substr(strip_tags(preg_replace('/[\n\r\t]+/', ' ', $objAlias->text)), 32);
            $strText = $GLOBALS['TL_LANG']['CTE'][$objAlias->type][0] . ' (';

            if ($headline != '')
            {
                $strText .= $headline . ', ';
            }
            elseif ($text != '')
            {
                $strText .= $text . ', ';
            }

            $key = $objAlias->title . ' (ID ' . $objAlias->pid . ')';
            $arrAlias[$key][$objAlias->id] = $strText . 'ID ' . $objAlias->id . ')';
        }

        return $arrAlias;
    }

    /**
     * Return the edit form wizard
     *
     * @param \DataContainer $dc
     *
     * @return string
     */
    public function editForm(\DataContainer $dc)
    {
        return ($dc->value < 1) ? '' : ' <a href="contao/main.php?do=form&amp;table=tl_form_field&amp;id=' . $dc->value . '&amp;popup=1&amp;nb=1&amp;rt=' . REQUEST_TOKEN . '" title="' . sprintf(\StringUtil::specialchars($GLOBALS['TL_LANG']['tl_content']['editalias'][1]), $dc->value) . '" onclick="Backend.openModalIframe({\'title\':\'' . \StringUtil::specialchars(str_replace("'", "\\'", sprintf($GLOBALS['TL_LANG']['tl_content']['editalias'][1], $dc->value))) . '\',\'url\':this.href});return false">' . \Image::getHtml('alias.svg', $GLOBALS['TL_LANG']['tl_content']['editalias'][0]) . '</a>';
    }

    /**
     * Get all forms and return them as array
     *
     * @return array
     */
    public function getForms()
    {
        if (!$this->User->isAdmin && !\is_array($this->User->forms))
        {
            return array();
        }

        $arrForms = array();
        $objForms = $this->Database->execute("SELECT id, title FROM tl_form ORDER BY title");

        while ($objForms->next())
        {
            if ($this->User->hasAccess($objForms->id, 'forms'))
            {
                $arrForms[$objForms->id] = $objForms->title . ' (ID ' . $objForms->id . ')';
            }
        }

        return $arrForms;
    }

    /**
     * Return the edit module wizard
     *
     * @param \DataContainer $dc
     *
     * @return string
     */
    public function editModule(\DataContainer $dc)
    {
        return ($dc->value < 1) ? '' : ' <a href="contao/main.php?do=themes&amp;table=tl_module&amp;act=edit&amp;id=' . $dc->value . '&amp;popup=1&amp;nb=1&amp;rt=' . REQUEST_TOKEN . '" title="' . sprintf(\StringUtil::specialchars($GLOBALS['TL_LANG']['tl_content']['editalias'][1]), $dc->value) . '" onclick="Backend.openModalIframe({\'title\':\'' . \StringUtil::specialchars(str_replace("'", "\\'", sprintf($GLOBALS['TL_LANG']['tl_content']['editalias'][1], $dc->value))) . '\',\'url\':this.href});return false">' . \Image::getHtml('alias.svg', $GLOBALS['TL_LANG']['tl_content']['editalias'][0]) . '</a>';
    }

    /**
     * Get all modules and return them as array
     *
     * @return array
     */
    public function getModules()
    {
        $arrModules = array();
        $objModules = $this->Database->execute("SELECT m.id, m.name, t.name AS theme FROM tl_module m LEFT JOIN tl_theme t ON m.pid=t.id ORDER BY t.name, m.name");

        while ($objModules->next())
        {
            $arrModules[$objModules->theme][$objModules->id] = $objModules->name . ' (ID ' . $objModules->id . ')';
        }

        return $arrModules;
    }

    /**
     * Return all gallery templates as array
     *
     * @return array
     */
    public function getGalleryTemplates()
    {
        return $this->getTemplateGroup('gallery_');
    }

    /**
     * Return all content element templates as array
     *
     * @param \DataContainer $dc
     *
     * @return array
     */
    public function getElementTemplates(\DataContainer $dc)
    {
        return $this->getTemplateGroup('ce_' . $dc->activeRecord->type);
    }

    /**
     * Return the edit article teaser wizard
     *
     * @param \DataContainer $dc
     *
     * @return string
     */
    public function editArticle(\DataContainer $dc)
    {
        return ($dc->value < 1) ? '' : ' <a href="contao/main.php?do=article&amp;table=tl_content&amp;id=' . $dc->value . '&amp;popup=1&amp;nb=1&amp;rt=' . REQUEST_TOKEN . '" title="' . sprintf(\StringUtil::specialchars($GLOBALS['TL_LANG']['tl_content']['editarticle'][1]), $dc->value) . '" onclick="Backend.openModalIframe({\'title\':\'' . \StringUtil::specialchars(str_replace("'", "\\'", sprintf($GLOBALS['TL_LANG']['tl_content']['editarticle'][1], $dc->value))) . '\',\'url\':this.href});return false">' . \Image::getHtml('alias.svg', $GLOBALS['TL_LANG']['tl_content']['editarticle'][0]) . '</a>';
    }

    /**
     * Get all articles and return them as array (article teaser)
     *
     * @param \DataContainer $dc
     *
     * @return array
     */
    public function getArticles(\DataContainer $dc)
    {
        $arrPids = array();
        $arrArticle = array();
        $arrRoot = array();
        $intPid = $dc->activeRecord->pid;

        if (\Input::get('act') == 'overrideAll')
        {
            $intPid = \Input::get('id');
        }

        // Limit pages to the website root
        $objArticle = $this->Database->prepare("SELECT pid FROM tl_article WHERE id=?")
            ->limit(1)
            ->execute($intPid);

        if ($objArticle->numRows)
        {
            $objPage = \PageModel::findWithDetails($objArticle->pid);
            $arrRoot = $this->Database->getChildRecords($objPage->rootId, 'tl_page');
            array_unshift($arrRoot, $objPage->rootId);
        }

        unset($objArticle);

        // Limit pages to the user's pagemounts
        if ($this->User->isAdmin)
        {
            $objArticle = $this->Database->execute("SELECT a.id, a.pid, a.title, a.inColumn, p.title AS parent FROM tl_article a LEFT JOIN tl_page p ON p.id=a.pid" . (!empty($arrRoot) ? " WHERE a.pid IN(". implode(',', array_map('\intval', array_unique($arrRoot))) .")" : "") . " ORDER BY parent, a.sorting");
        }
        else
        {
            foreach ($this->User->pagemounts as $id)
            {
                if (!\in_array($id, $arrRoot))
                {
                    continue;
                }

                $arrPids[] = $id;
                $arrPids = array_merge($arrPids, $this->Database->getChildRecords($id, 'tl_page'));
            }

            if (empty($arrPids))
            {
                return $arrArticle;
            }

            $objArticle = $this->Database->execute("SELECT a.id, a.pid, a.title, a.inColumn, p.title AS parent FROM tl_article a LEFT JOIN tl_page p ON p.id=a.pid WHERE a.pid IN(". implode(',', array_map('\intval', array_unique($arrPids))) .") ORDER BY parent, a.sorting");
        }

        // Edit the result
        if ($objArticle->numRows)
        {
            \System::loadLanguageFile('tl_article');

            while ($objArticle->next())
            {
                $key = $objArticle->parent . ' (ID ' . $objArticle->pid . ')';
                $arrArticle[$key][$objArticle->id] = $objArticle->title . ' (' . ($GLOBALS['TL_LANG']['COLS'][$objArticle->inColumn] ?: $objArticle->inColumn) . ', ID ' . $objArticle->id . ')';
            }
        }

        return $arrArticle;
    }

    /**
     * Dynamically set the ace syntax
     *
     * @param mixed         $varValue
     * @param \DataContainer $dc
     *
     * @return string
     */
    public function setRteSyntax($varValue, \DataContainer $dc)
    {
        switch ($dc->activeRecord->highlight)
        {
            case 'C':
            case 'CSharp':
                $syntax = 'c_cpp';
                break;

            case 'CSS':
            case 'Diff':
            case 'Groovy':
            case 'HTML':
            case 'Java':
            case 'JavaScript':
            case 'Perl':
            case 'PHP':
            case 'PowerShell':
            case 'Python':
            case 'Ruby':
            case 'Scala':
            case 'SQL':
            case 'Text':
                $syntax = strtolower($dc->activeRecord->highlight);
                break;

            case 'VB':
                $syntax = 'vbscript';
                break;

            case 'XML':
            case 'XHTML':
                $syntax = 'xml';
                break;

            default:
                $syntax = 'text';
                break;
        }

        if ($dc->activeRecord->type == 'markdown')
        {
            $syntax = 'markdown';
        }

        $GLOBALS['TL_DCA']['tl_content']['fields']['code']['eval']['rte'] = 'ace|' . $syntax;

        return $varValue;
    }

    /**
     * Add a link to the list items import wizard
     *
     * @return string
     */
    public function listImportWizard()
    {
        return ' <a href="' . $this->addToUrl('key=list') . '" title="' . \StringUtil::specialchars($GLOBALS['TL_LANG']['MSC']['lw_import'][1]) . '" onclick="Backend.getScrollOffset()">' . \Image::getHtml('tablewizard.svg', $GLOBALS['TL_LANG']['MSC']['tw_import'][0]) . '</a>';
    }

    /**
     * Add a link to the table items import wizard
     *
     * @return string
     */
    public function tableImportWizard()
    {
        return ' <a href="' . $this->addToUrl('key=table') . '" title="' . \StringUtil::specialchars($GLOBALS['TL_LANG']['MSC']['tw_import'][1]) . '" onclick="Backend.getScrollOffset()">' . \Image::getHtml('tablewizard.svg', $GLOBALS['TL_LANG']['MSC']['tw_import'][0]) . '</a> ' . \Image::getHtml('demagnify.svg', '', 'title="' . \StringUtil::specialchars($GLOBALS['TL_LANG']['MSC']['tw_shrink']) . '" style="cursor:pointer" onclick="Backend.tableWizardResize(0.9)"') . \Image::getHtml('magnify.svg', '', 'title="' . \StringUtil::specialchars($GLOBALS['TL_LANG']['MSC']['tw_expand']) . '" style="cursor:pointer" onclick="Backend.tableWizardResize(1.1)"');
    }

    /**
     * Return the link picker wizard
     *
     * @param \DataContainer $dc
     *
     * @return string
     *
     * @deprecated Deprecated since Contao 4.4, to be removed in Contao 5.
     *             Set the "dcaPicker" eval attribute instead.
     */
    public function pagePicker(\DataContainer $dc)
    {
        @trigger_error('Using tl_content::pagePicker() has been deprecated and will no longer work in Contao 5.0. Set the "dcaPicker" eval attribute instead.', E_USER_DEPRECATED);

        return \Backend::getDcaPickerWizard(true, $dc->table, $dc->field, $dc->inputName);
    }

    /**
     * Return the delete content element button
     *
     * @param array  $row
     * @param string $href
     * @param string $label
     * @param string $title
     * @param string $icon
     * @param string $attributes
     *
     * @return string
     */
    public function deleteElement($row, $href, $label, $title, $icon, $attributes)
    {
        $objElement = $this->Database->prepare("SELECT id FROM tl_content WHERE cteAlias=? AND type='alias'")
            ->limit(1)
            ->execute($row['id']);

        return $objElement->numRows ? \Image::getHtml(preg_replace('/\.svg$/i', '_.svg', $icon)) . ' ' : '<a href="'.$this->addToUrl($href.'&amp;id='.$row['id']).'" title="'.\StringUtil::specialchars($title).'"'.$attributes.'>'.\Image::getHtml($icon, $label).'</a> ';
    }

    /**
     * Dynamically add flags to the "singleSRC" field
     *
     * @param mixed         $varValue
     * @param \DataContainer $dc
     *
     * @return mixed
     */
    public function setSingleSrcFlags($varValue, \DataContainer $dc)
    {
        if ($dc->activeRecord)
        {
            switch ($dc->activeRecord->type)
            {
                case 'text':
                case 'hyperlink':
                case 'image':
                case 'accordionSingle':
                    $GLOBALS['TL_DCA'][$dc->table]['fields'][$dc->field]['eval']['extensions'] = \Config::get('validImageTypes');
                    break;

                case 'download':
                    $GLOBALS['TL_DCA'][$dc->table]['fields'][$dc->field]['eval']['extensions'] = \Config::get('allowedDownload');
                    break;
            }
        }

        return $varValue;
    }

    /**
     * Dynamically add flags to the "multiSRC" field
     *
     * @param mixed         $varValue
     * @param \DataContainer $dc
     *
     * @return mixed
     */
    public function setMultiSrcFlags($varValue, \DataContainer $dc)
    {
        if ($dc->activeRecord)
        {
            switch ($dc->activeRecord->type)
            {
                case 'gallery':
                    $GLOBALS['TL_DCA'][$dc->table]['fields'][$dc->field]['eval']['isGallery'] = true;
                    $GLOBALS['TL_DCA'][$dc->table]['fields'][$dc->field]['eval']['extensions'] = \Config::get('validImageTypes');
                    break;

                case 'downloads':
                    $GLOBALS['TL_DCA'][$dc->table]['fields'][$dc->field]['eval']['isDownloads'] = true;
                    $GLOBALS['TL_DCA'][$dc->table]['fields'][$dc->field]['eval']['extensions'] = \Config::get('allowedDownload');
                    break;
            }
        }

        return $varValue;
    }

    /**
     * Extract the YouTube ID from an URL
     *
     * @param mixed         $varValue
     * @param \DataContainer $dc
     *
     * @return mixed
     */
    public function extractYouTubeId($varValue, \DataContainer $dc)
    {
        if ($dc->activeRecord->singleSRC != $varValue)
        {
            $matches = array();

            if (preg_match('%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i', $varValue, $matches))
            {
                $varValue = $matches[1];
            }
        }

        return $varValue;
    }

    /**
     * Extract the Vimeo ID from an URL
     *
     * @param mixed         $varValue
     * @param \DataContainer $dc
     *
     * @return mixed
     */
    public function extractVimeoId($varValue, \DataContainer $dc)
    {
        if ($dc->activeRecord->singleSRC != $varValue)
        {
            $matches = array();

            if (preg_match('%vimeo\.com/(?:channels/(?:\w+/)?|groups/(?:[^/]+)/videos/|album/(?:\d+)/video/)?(\d+)(?:$|/|\?)%i', $varValue, $matches))
            {
                $varValue = $matches[1];
            }
        }

        return $varValue;
    }

    /**
     * Return the "toggle visibility" button
     *
     * @param array  $row
     * @param string $href
     * @param string $label
     * @param string $title
     * @param string $icon
     * @param string $attributes
     *
     * @return string
     */
    public function toggleIcon($row, $href, $label, $title, $icon, $attributes)
    {
        if (\strlen(\Input::get('tid')))
        {
            $this->toggleVisibility(\Input::get('tid'), (\Input::get('state') == 1), (@func_get_arg(12) ?: null));
            $this->redirect($this->getReferer());
        }

        // Check permissions AFTER checking the tid, so hacking attempts are logged
        if (!$this->User->hasAccess('tl_content::invisible', 'alexf'))
        {
            return '';
        }

        $href .= '&amp;id='.\Input::get('id').'&amp;tid='.$row['id'].'&amp;state='.$row['invisible'];

        if ($row['invisible'])
        {
            $icon = 'invisible.svg';
        }

        return '<a href="'.$this->addToUrl($href).'" title="'.\StringUtil::specialchars($title).'"'.$attributes.'>'.\Image::getHtml($icon, $label, 'data-state="' . ($row['invisible'] ? 0 : 1) . '"').'</a> ';
    }

    /**
     * Toggle the visibility of an element
     *
     * @param integer       $intId
     * @param boolean       $blnVisible
     * @param \DataContainer $dc
     *
     * @throws \Contao\CoreBundle\Exception\AccessDeniedException
     */
    public function toggleVisibility($intId, $blnVisible, \DataContainer $dc=null)
    {
        // Set the ID and action
        \Input::setGet('id', $intId);
        \Input::setGet('act', 'toggle');

        if ($dc)
        {
            $dc->id = $intId; // see #8043
        }

        // Trigger the onload_callback
        if (\is_array($GLOBALS['TL_DCA']['tl_content']['config']['onload_callback']))
        {
            foreach ($GLOBALS['TL_DCA']['tl_content']['config']['onload_callback'] as $callback)
            {
                if (\is_array($callback))
                {
                    $this->import($callback[0]);
                    $this->{$callback[0]}->{$callback[1]}($dc);
                }
                elseif (\is_callable($callback))
                {
                    $callback($dc);
                }
            }
        }

        // Check the field access
        if (!$this->User->hasAccess('tl_content::invisible', 'alexf'))
        {
            throw new AccessDeniedException('Not enough permissions to show/hide content element ID ' . $intId . '.');
        }

        // Set the current record
        if ($dc)
        {
            $objRow = $this->Database->prepare("SELECT * FROM tl_content WHERE id=?")
                ->limit(1)
                ->execute($intId);

            if ($objRow->numRows)
            {
                $dc->activeRecord = $objRow;
            }
        }

        $objVersions = new \Versions('tl_content', $intId);
        $objVersions->initialize();

        // Reverse the logic (elements have invisible=1)
        $blnVisible = !$blnVisible;

        // Trigger the save_callback
        if (\is_array($GLOBALS['TL_DCA']['tl_content']['fields']['invisible']['save_callback']))
        {
            foreach ($GLOBALS['TL_DCA']['tl_content']['fields']['invisible']['save_callback'] as $callback)
            {
                if (\is_array($callback))
                {
                    $this->import($callback[0]);
                    $blnVisible = $this->{$callback[0]}->{$callback[1]}($blnVisible, $dc);
                }
                elseif (\is_callable($callback))
                {
                    $blnVisible = $callback($blnVisible, $dc);
                }
            }
        }

        $time = time();

        // Update the database
        $this->Database->prepare("UPDATE tl_content SET tstamp=$time, invisible='" . ($blnVisible ? '1' : '') . "' WHERE id=?")
            ->execute($intId);

        if ($dc)
        {
            $dc->activeRecord->tstamp = $time;
            $dc->activeRecord->invisible = ($blnVisible ? '1' : '');
        }

        // Trigger the onsubmit_callback
        if (\is_array($GLOBALS['TL_DCA']['tl_content']['config']['onsubmit_callback']))
        {
            foreach ($GLOBALS['TL_DCA']['tl_content']['config']['onsubmit_callback'] as $callback)
            {
                if (\is_array($callback))
                {
                    $this->import($callback[0]);
                    $this->{$callback[0]}->{$callback[1]}($dc);
                }
                elseif (\is_callable($callback))
                {
                    $callback($dc);
                }
            }
        }

        $objVersions->create();
    }
}
