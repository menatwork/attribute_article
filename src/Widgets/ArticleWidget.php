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

namespace MetaModels\AttributeArticleBundle\Widgets;

class ArticleWidget extends \Widget
{

	/**
	 * Submit user input
	 * @var boolean
	 */
	protected $blnSubmitInput = false;

	/**
	 * Add a for attribute
	 * @var boolean
	 */
	protected $blnForAttribute = false;

	/**
	 * Template
	 * @var string
	 */
	protected $strTemplate = 'be_widget';


	/**
	 * @param array
	 */
	public function __construct($arrAttributes=null)
	{
		parent::__construct($arrAttributes);
	}

	/**
	 * @param string
	 * @param mixed
	 */
	public function __set($strKey, $varValue)
	{
		parent::__set($strKey, $varValue);
	}

	/**
	 * @param mixed
	 * @return mixed
	 */
	protected function validator($varInput)
	{
		return parent::validator($varInput);
	}

	/**
	 * Generate the widget and return it as string
	 * @return string
	 */
	public function generate()
	{
		$strQuery = http_build_query([
			'do'     => 'metamodel_' . $this->getRootMetaModelTable($this->strTable) ?: 'table_not_found',
			'table'  => 'tl_content',
			'ptable' => $this->strTable,
			'id'     => $this->currentRecord,
			'slot'   => $this->strName,
			'lang'   => $this->lang,
			'popup'  => 1,
			'nb'     => 1,
			'rt'     => REQUEST_TOKEN,
		]);

		if(!empty($GLOBALS['TL_LANG']['MSC']['edit'])) {
		    $edit = $GLOBALS['TL_LANG']['MSC']['edit'];
        }else {
            $edit = "Bearbeiten";
        }

		return sprintf(
			'<div><p><a href="%s" class="tl_submit" onclick="%s">%s</a></p></div>',
			'contao/main.php?' . $strQuery,
			'Backend.openModalIframe({width:768,title:\''.$this->strLabel.'\',url:this.href});return false',
		    $edit
		);
	}

	/**
	 * @param string
	 * @param mixed
	 */
	private function getRootMetaModelTable($strTable)
	{
		$arrTables = [];
		$objTables = \Database::getInstance()
			->execute('
				SELECT tableName, d.renderType, d.ptable
				FROM tl_metamodel AS m
				JOIN tl_metamodel_dca AS d
				ON m.id = d.pid
			')
		;

		while ($objTables->next()) {
			$arrTables[$objTables->tableName] = [
				'renderType' => $objTables->renderType,
				'ptable'     => $objTables->ptable,
			];
		}

		$getTable = function($strTable) use (&$getTable, $arrTables)
		{
			if (!isset($arrTables[$strTable])) {
				return false;
			}

			$arrTable = $arrTables[$strTable];

			switch ($arrTable['renderType']) {
				case 'standalone':
					return $strTable;

				case 'ctable':
					return $getTable($arrTable['ptable']);

				default:
					throw new \Exception('Unexpected case: '.$arrTable['renderType']);
			}
		};

		return $getTable($strTable);
	}

}
