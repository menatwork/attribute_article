<?php
/**
 * Created by PhpStorm.
 * User: andreas.dziemba
 * Date: 08.05.2018
 * Time: 16:07
 */

namespace MetaModels\AttributeArticleBundle\Table;


class ArticelDmaElementgenatorContent extends \Backend
{

    /**
     * Import the back end user object
     */
    public function __construct()
    {
        parent::__construct();
        $this->import('BackendUser', 'User');
    }

    /**
     * Return all dma element templates as array
     *
     * @return array
     */
    public function getDmaElementTemplates()
    {
        return $this->getTemplateGroup('dma_eg_');
    }
}