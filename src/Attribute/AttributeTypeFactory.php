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

		$this->typeName        = 'article';
		$this->typeIcon        = 'bundles/metamodelsattributearticle/article.png';
		$this->typeClass       = Article::class;
	}

    /**
     * {@inheritDoc}
     */
    public function createInstance($information, $metaModel)
    {
        return new $this->typeClass($metaModel, $information);
    }
}
