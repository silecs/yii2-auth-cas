<?php

/**
 * @license MIT License
 */

namespace silecs\yii2auth\cas;

use phpCAS;

/**
 * The user profile that the CAS server returned.
 *
 * @property bool $isGuest If false, the user was authenticated by CAS
 * @property bool $username Username if the user was authenticated by CAS, else an empty string
 *
 * @author François Gannaz <francois.gannaz@silecs.info>
 */
class CasProfile extends \yii\base\Component
{
    public function getIsGuest(): bool
    {
        return phpCAS::isAuthenticated();
    }

    /**
     * Get the username if the CAS authentication succeeded.
     *
     * @return string Username, or empty string if not authenticated.
     */
    public function getUsername(): string
    {
        if (phpCAS::isAuthenticated()) {
            return phpCAS::getUser();
        }
        return "";
    }
}
