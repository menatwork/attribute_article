<?php
/**
 * Created by PhpStorm.
 * User: andreas.dziemba
 * Date: 14.05.2018
 * Time: 12:57
 */

namespace MetaModels\AttributeArticleBundle\Table;


class MetaModelAttributeArticle extends \Backend
{
    public function initializeSystem()
    {
        //Function moved in InitializeListener
    }

    public function addMainLangContent($dc)
    {
        $factory         = \System::getContainer()->get('metamodels.factory');
        /** @var \MetaModels\IFactory $factory */
        $objMetaModel    = $factory->getMetaModel($dc->parentTable);

        $intId           = $dc->id;
        $strParentTable  = $dc->parentTable;
        $strSlot         = \Input::get('slot');
        $strLanguage     = \Input::get('lang');
        $strMainLanguage = $objMetaModel->getFallbackLanguage();

        if ($strLanguage == $strMainLanguage) {
            \Message::addError('Hauptsprache kann nicht in die Hauptsprache kopiert werden.'); // TODO übersetzen
            \Controller::redirect(\System::getReferer());

            return;
        }

        $objContent = \Database::getInstance()
            ->prepare('SELECT * FROM tl_content WHERE pid=? AND ptable=? AND mm_slot=? AND mm_lang=?')
            ->execute($intId, $strParentTable, $strSlot, $strMainLanguage);

        for ($i = 0; $objContent->next(); $i++) {
            $arrContent            = $objContent->row();
            $arrContent['mm_lang'] = $strLanguage;
            unset($arrContent['id']);

            \Database::getInstance()
                ->prepare('INSERT INTO tl_content %s')
                ->set($arrContent)
                ->execute();
        }

        \Message::addInfo(sprintf('%s Element(e) kopiert', $i)); // TODO übersetzen
        \Controller::redirect(\System::getReferer());
    }
}
