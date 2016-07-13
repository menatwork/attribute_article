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

namespace MetaModels\Attribute\Article;

use MetaModels\Attribute\BaseSimple;
use MetaModels\Render\Template;
use MetaModels\Attribute\ITranslated;


/**
 * This is the MetaModelAttribute class for handling article fields.
 */
class Article extends BaseSimple implements ITranslated
{

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
		$arrFieldDef['inputType'] = 'metamodelsArticle';

		return $arrFieldDef;
	}

	/**
	 * {@inheritDoc}
	 */
	protected function prepareTemplate(Template $objTemplate, $arrRowData, $objSettings)
	{
		parent::prepareTemplate($objTemplate, $arrRowData, $objSettings);

		$strLanguage = $this->getMetaModel()->isTranslated() ? $this->getMetaModel()->getActiveLanguage() : '-';
		$objContent = \ContentModel::findPublishedByPidAndTable($arrRowData['id'], $this->getMetaModel()->getTableName());
		$strContent = '';

		if ($objContent !== null) {
			while ($objContent->next()) {
				if ($objContent->mm_slot == $this->getColName() &&
					$objContent->mm_lang == $strLanguage
				) {
					$strContent .= $this->getContentElement($objContent->current());
				}
			}
		}

		$objTemplate->raw = $strContent;
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

	/**
	 * @param       $strPattern
	 * @param array $arrLanguages
	 */
	public function searchForInLanguages($strPattern, $arrLanguages = array()) {
		// Needed to fake implement ITranslate.
	}

	/**
	 * @param $arrValues
	 * @param $strLangCode
	 */
	public function setTranslatedDataFor($arrValues, $strLangCode) {
		// Needed to fake implement ITranslate.
	}

	/**
	 * @param $arrIds
	 * @param $strLangCode
	 */
	public function getTranslatedDataFor($arrIds, $strLangCode) {
		// Needed to fake implement ITranslate.
	}

	/**
	 * @param $arrIds
	 * @param $strLangCode
	 */
	public function unsetValueFor($arrIds, $strLangCode) {
		// Needed to fake implement ITranslate.
	}

}
