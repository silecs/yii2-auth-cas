<?php

/**
 * @license MIT License
 */

namespace silecs\yii2auth\cas\controllers;

use Yii;
use yii\helpers\Url;

/**
 * A controller inside the Module that will handle the HTTP query of the CAS server.
 *
 * Provides 2 actions, usually /cas/login and /cas/logout,
 * where "cas" is the key in the configuration file of the Yii2 application
 * `"modules" => ['cas' => ...]`.
 *
 * @author François Gannaz <francois.gannaz@silecs.info>
 */
class AuthController extends \yii\web\Controller
{
    public function actionLogin()
    {
        $this->module->casService->forceAuthentication();
        $username = $this->module->casService->getUsername();
        if ($username) {
            $userClass = Yii::$app->user->identityClass;
            $user = $userClass::findIdentity($username);
            if ($user) {
                Yii::$app->user->login($user);
            } else {
                throw new \yii\web\HttpException(403, "This user has no access to the application.");
            }
        }
        return $this->goBack();
    }

    public function actionLogout()
    {
        $this->module->casService->logout(Url::home(true));
        if (!Yii::$app->getUser()->isGuest) {
            Yii::$app->getUser()->logout(true);
        }
        // In case the logout fails (not authenticated)
        return $this->redirect(Url::home(true));
    }
}
