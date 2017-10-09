<?php

/**
 * @license MIT License
 */

namespace silecs\yii2auth\cas;

/**
 * A Yii2 Module that will handle the HTTP query of the CAS server.
 *
 * @author François Gannaz <francois.gannaz@silecs.info>
 */
class CasModule extends \yii\base\Module
{
    /**
     * @var array Must be filled by the declaration of the module
     */
    public $config;

    /**
     * @var string
     */
    public $controllerNamespace = 'silecs\yii2auth\cas\controllers';

    /**
     * @var CasService
     */
    protected $casService;

    public function init()
    {
        parent::init();
        $this->casService = new CasService($this->config);
    }

    public function getCasService()
    {
        return $this->casService;
    }
}
