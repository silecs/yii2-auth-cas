<?php

/**
 * @license MIT License
 */

namespace silecs\yii2auth\cas;

use phpCAS;
use Yii;
use yii\helpers\Url;

/**
 * Wrapper on phpCAS
 *
 * @author François Gannaz <francois.gannaz@silecs.info>
 */
class CasService extends \yii\base\BaseObject
{
    const LOGPATH = '@runtime/logs/cas.log';

    public $host;
    public $port;
    public $path;

    /**
     *
     * @var string|boolean If defined, local path to a SSL certificate file,
     *                     or false to disable the certificate validation.
     */
    public $certfile;

    /**
     * @var ?object PSR-3 Logger
     */
    public $logger;

    public function init()
    {
        if (!isset($this->host, $this->port, $this->path)) {
            throw new \Exception("Incomplete CAS config. Required: host, port, path.");
        }
        // Force a Yii session to open to prevent phpCas from doing it on its own
        Yii::$app->session->open();
        // Init the phpCAS singleton
        phpCAS::client(CAS_VERSION_2_0, $this->host, (int) $this->port, $this->path);
        if ($this->logger) {
            phpCAS::setLogger($this->logger);
        }
        if ($this->certfile) {
            phpCAS::setCasServerCACert($this->certfile);
        } else {
            phpCAS::setNoCasServerValidation();
        }
    }

    /**
     * Try to authenticate the current user.
     *
     * @return boolean
     */
    public function forceAuthentication()
    {
        phpCAS::setFixedServiceURL(Url::current([], true));
        return phpCAS::forceAuthentication();
    }

    /**
     * Check if the current user is already authenticated.
     *
     * @return boolean
     */
    public function checkAuthentication()
    {
        return phpCAS::checkAuthentication();
    }

    /**
     * Logout on the CAS server. The user is then redirected to $url.
     *
     * @param string $url
     */
    public function logout($url)
    {
        if (phpCAS::isAuthenticated()) {
            phpCAS::logout(['service' => $url]);
        }
    }

    /**
     * Return the username if authenticated by CAS, else the empty string.
     *
     * @return string
     */
    public function getUsername()
    {
        if (phpCAS::isAuthenticated()) {
            return phpCAS::getUser();
        } else {
            return "";
        }
    }

    /**
     * Toggle the CAS debug mode that will add more logs into self::LOGPATH.
     *
     * @param boolean $debug
     * @return $this
     */
    public function setDebug($debug = true)
    {
        phpCAS::setVerbose($debug);
        return $this;
    }
}
