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

/**
 * Register templates
 */
TemplateLoader::addFiles(array(
	'mm_attr_article' => 'system/modules/metamodelsattribute_article/templates',
));

/**
 * Register classes
 */
ClassLoader::addClasses(array(
	'MetaModelsArticle'         => 'system/modules/metamodelsattribute_article/widgets/MetaModelsArticle.php',
	'DC_TableMetaModelsArticle' => 'system/modules/metamodelsattribute_article/drivers/DC_TableMetaModelsArticle.php',
));
