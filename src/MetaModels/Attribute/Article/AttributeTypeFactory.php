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

use MetaModels\Attribute\AbstractAttributeTypeFactory;


/**
 * Attribute type factory for article attributes.
 */
class AttributeTypeFactory extends AbstractAttributeTypeFactory
{

	/**
	 * {@inheritDoc}
	 */
	public function __construct()
	{
		parent::__construct();

		$this->typeName  = 'article';
		$this->typeIcon  = 'system/modules/metamodelsattribute_article/html/article.png';
		$this->typeClass = 'MetaModels\Attribute\Article\Article';
	}

}
