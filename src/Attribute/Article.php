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

namespace MetaModels\AttributeArticleBundle\Attribute;

use Contao\System;
use MetaModels\Attribute\BaseSimple;
use MetaModels\Attribute\ITranslated;
use MetaModels\IMetaModel;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * This is the MetaModelAttribute class for handling article fields.
 */
class Article extends BaseSimple implements ITranslated
{

    /**
     * The event dispatcher.
     *
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

	private static $arrCallIds = [];

    /**
     * Create a new instance.
     *
     * @param IMetaModel            $objMetaModel     The MetaModel instance this attribute belongs to.
     * @param array                 $arrData          The attribute information array.
     */
    public function __construct(
        IMetaModel $objMetaModel,
        $arrData = [],
        EventDispatcherInterface $eventDispatcher = null
    ){
        parent::__construct($objMetaModel, $arrData);

        if (null === $eventDispatcher) {
            // @codingStandardsIgnoreStart Silencing errors is discouraged
            @\trigger_error(
                'Event dispatcher is missing. It has to be passed in the constructor. Fallback will be dropped.',
                E_USER_DEPRECATED
            );
            // @codingStandardsIgnoreEnd
            $eventDispatcher = System::getContainer()->get('event_dispatcher');
        }

        $this->eventDispatcher = $eventDispatcher;

    }

    /**
     * @param       $strPattern
     * @param array $arrLanguages
     * @return string[]
     */
    public function searchForInLanguages($strPattern, $arrLanguages = array()) {
        // Needed to fake implement ITranslate.
        return [];
    }

	/**
	 * {@inheritdoc}
	 */
	public function getSQLDataType()
	{
		return 'varchar(255) NOT NULL default \'\'';
	}

	/**
	 * {@inheritdoc}
	 */
	public function getAttributeSettingNames()
	{
		return array_merge(parent::getAttributeSettingNames(), array(
            //'isunique',
            //'searchable',
            //'filterable',
            //'mandatory',
            //'allowHtml',
            //'preserveTags',
            //'decodeEntities',
            //'trailingSlash',
            //'spaceToUnderscore',
            //'rgxp'
		));
	}

	/**
	 * {@inheritdoc}
	 */
	public function getFieldDefinition($arrOverrides = array())
	{
		$arrFieldDef              = parent::getFieldDefinition($arrOverrides);
		$arrFieldDef['inputType'] = 'MetaModelAttributeArticle';

		return $arrFieldDef;
	}

	/**
	 * @param $arrValues
	 * @param $strLangCode
     * @return void
	 */
	public function setTranslatedDataFor($arrValues, $strLangCode) {
	    // Needed to fake implement ITranslate.
	}

	/**
	 * @param $arrIds
	 * @param $strLangCode
     * @return mixed[]
	 */
	public function getTranslatedDataFor($arrIds, $strLangCode)
	{
		// Generate only for frontend (speeds up the backend a little)
		if (TL_MODE == 'BE') return [];

		$strTable    = $this->getMetaModel()->getTableName();
		$strColumn   = $this->getColName();
		$strLanguage = $this->getMetaModel()->isTranslated() ? $strLangCode : '-';
		$arrData     = [];

		foreach ($arrIds as $intId)
		{
			// Continue if it's a recursive call
			$strCallId  = $strTable . '_' . $strColumn . '_' . $strLanguage . '_' . $intId;
			if (isset(static::$arrCallIds[$strCallId])) {
				$arrData[$intId]['value'] = sprintf('RECURSION: %s', $strCallId);
				continue;
			}
			static::$arrCallIds[$strCallId] = true;

			$objContent = \ContentModel::findPublishedByPidAndTable($intId, $strTable);
			$arrContent = [];

			if ($objContent !== null) {
				while ($objContent->next()) {
					if ($objContent->mm_slot == $strColumn &&
						$objContent->mm_lang == $strLanguage
					) {
						$arrContent[] = $this->getContentElement($objContent->current());
					}
				}
			}

			$arrData[$intId]['value'] = $arrContent;
			unset(static::$arrCallIds[$strCallId]);
		}

		return $arrData;
	}

	/**
	 * @param $arrIds
	 * @param $strLangCode
     * @return void
	 */
	public function unsetValueFor($arrIds, $strLangCode) {
		// Needed to fake implement ITranslate.
	}

	/**
	 * {@inheritDoc}
	 */
	private function getContentElement($objContent)
	{
		if (version_compare(VERSION, '3.5', '>=')) {
			return \Controller::getContentElement($objContent);
		}

		// In contao < 3.5 the function is not directly available
		if (!class_exists('ControllerHelper')) {
			eval('
				class ControllerHelper extends Controller {
					public function __construct() {
						// Needed as the parent constructor is not public!
					}

					public function getContentElement($objContent, $strColumn=\'main\') {
						return parent::getContentElement($objContent, $strColumn);
					}
				}
			');
		}

		$objControllerHelper = new \ControllerHelper();
		return $objControllerHelper->getContentElement($objContent);
	}

}
