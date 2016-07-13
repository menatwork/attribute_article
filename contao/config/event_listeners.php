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

use MetaModels\Attribute\Events\CreateAttributeFactoryEvent;
use MetaModels\Attribute\Article\AttributeTypeFactory;
use MetaModels\MetaModelsEvents;
use MetaModels\Events\MetaModelsBootEvent;
use MetaModels\Attribute\Article\BackendSubscriber;

return array(
	MetaModelsEvents::SUBSYSTEM_BOOT_BACKEND => array(
		function (MetaModelsBootEvent $event) {
			new BackendSubscriber($event->getServiceContainer());
		}
	),

	MetaModelsEvents::ATTRIBUTE_FACTORY_CREATE => array(
		function (CreateAttributeFactoryEvent $event) {
			$factory = $event->getFactory();

			$factory->addTypeFactory(new AttributeTypeFactory());
		}
	)
);
