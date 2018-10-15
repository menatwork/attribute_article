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
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Attribute type factory for article attributes.
 */
class AttributeTypeFactory extends AbstractAttributeTypeFactory
{
    /**
     * Event dispatcher.
     *
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * {@inheritDoc}
     */
    public function __construct(EventDispatcherInterface $eventDispatcher)
    {
        parent::__construct();

        $this->typeName        = 'article';
        $this->typeIcon        = 'bundles/metamodelsattributearticle/article.png';
        $this->typeClass       = Article::class;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * {@inheritDoc}
     */
    public function createInstance($information, $metaModel)
    {
        return new $this->typeClass($metaModel, $information, $this->eventDispatcher);
    }
}
