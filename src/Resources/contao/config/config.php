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
 * Register backend form fields
 */
$GLOBALS['BE_FFL']['MetaModelAttributeArticle'] = 'MetaModels\\AttributeArticleBundle\\Widgets\\ArticleWidget';

/**
 * Register hooks
 */
$GLOBALS['TL_HOOKS']['initializeSystem'][] = ['MetaModels\\AttributeArticleBundle\\Table\\MetaModelAttributeArticle', 'initializeSystem'];
