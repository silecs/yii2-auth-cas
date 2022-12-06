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

    public string $host = '';
    public int $port = 443;
    public string $path = '';
    public string $returnUrl = '';

    /**
     * @var string If defined, local path to a SSL certificate file,
     *    or '' to disable the certificate validation.
     */
    public $certfile;

    /**
     * @var ?object PSR-3 Logger
     */
    public $logger;

    public function init()
    {
        if (!$this->host || !$this->port) {
            throw new \Exception("Incomplete CAS config. Required: host, port, path.");
        }
        $returnUrl = $this->returnUrl ?: Url::current([], true);
        // Force a Yii session to open to prevent phpCas from doing it on its own
        Yii::$app->session->open();
        // Init the phpCAS singleton
        phpCAS::client(CAS_VERSION_2_0, $this->host, (int) $this->port, $this->path, $returnUrl);

        if ($this->logger) {
            phpCAS::setLogger($this->logger);
        }
        if ($this->certfile !== '') {
            phpCAS::setCasServerCACert($this->certfile);
        } else {
            phpCAS::setNoCasServerValidation();
        }
    }

    /**
     * Try to authenticate the current user.
     */
    public function forceAuthentication(): bool
    {
        phpCAS::setFixedServiceURL(Url::current([], true));
        return phpCAS::forceAuthentication();
    }

    /**
     * Check if the current user is already authenticated.
     */
    public function checkAuthentication(): bool
    {
        return phpCAS::checkAuthentication();
    }

    /**
     * Logout on the CAS server. The user is then redirected to $url.
     */
    public function logout(string $url): void
    {
        if (phpCAS::isAuthenticated()) {
            phpCAS::logout(['service' => $url]);
        }
    }

    /**
     * Return the username if authenticated by CAS, else the empty string.
     */
    public function getUsername(): string
    {
        if (phpCAS::isAuthenticated()) {
            return phpCAS::getUser();
        }
        return "";
    }

    /**
     * Toggle the CAS debug mode that will add more logs into self::LOGPATH.
     *
     * @param boolean $debug
     * @return $this
     */
    public function setDebug(bool $debug = true): void
    {
        phpCAS::setVerbose($debug);
    }
}
