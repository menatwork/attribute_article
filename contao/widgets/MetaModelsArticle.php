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

class MetaModelsArticle extends Widget
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
			'do'     => 'article',
			'type'   => 'metamodels_article',
			'table'  => 'tl_content',
			'ptable' => $this->strTable,
			'id'     => $this->currentRecord,
			'slot'   => $this->strName,
			'lang'   => $this->lang,
			'popup'  => 1,
			'nb'     => 1,
			'rt'     => REQUEST_TOKEN,
		]);

		return sprintf(
			'<div><p><a href="%s" class="tl_submit" onclick="%s">Bearbeiten</a></p></div>',
			'contao/main.php?' . $strQuery,
			'Backend.openModalIframe({width:768,title:\''.$this->strLabel.'\',url:this.href});return false'
		);
	}

}
