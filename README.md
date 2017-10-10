yii2-auth-cas
=============

Yii2 library for authentication by CAS,
using the library [phpCAS](https://wiki.jasig.org/display/CASC/phpCAS).

Usage
-----

1. Add this to the project with `composer require silecs/yii2-auth-cas`

2. Configure the Yii2 application, e.g. in `backend/config/main.php` :

    ```
    return [
        ...
        'modules' => [
            'cas' => [
                'class' => 'silecs\yii2auth\cas\CasModule',
                'config' => [
                    'host' => 'ssoserver.example.com',
                    'port' => '443',
                    'path' => '/idp/profile/cas',
                    // optional parameters
                    'certfile' => '', // empty, or path to a SSL cert, or false to ignore certs
                    'debug' => true, // will add many logs into X/runtime/logs/cas.log
                ],
            ],
    ```

3. Add actions that use this CAS module, e.g. in `SiteController` :

    ```
    public function actionLogin()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }
        return $this->redirect(['/cas/auth/login']);
    }

    public function actionLogout()
    {
        if (Yii::$app->user->isGuest) {
            return $this->redirect(['/cas/auth/logout']);
        }
        return $this->goHome();
    }
    ```


Notes
-----

The `user` component that implements `yii\web\IdentityInterface`
will be used to fetch the local profile after querying the CAS server.
It means that if `User` is the App component and CAS returns a username of "bibendum",
the authentication will be successful if and only if
the result of `User::findIdentity("bibendum")` is not null.

The action path '/cas/auth/login' starts with the alias of the module,
as defined in the application configuration, e.g.
`'cas'` in `'modules' => [ 'cas' => [ ... ] ]`.


### Testing with a CAS container

Here are some instructions on deploying a Docker CAS server
to test this library.
This procedure will use the CAS interface of a Shibboleth instance.
This was tested on Debian Stretch and Buster (testing).

1. Install `docker` from the extra repository at docker.io
   (I had errors with the older docker from the official Debian repository).

2. Install `docker-compose` either from Debian or docker.io.

3. Git clone https://hub.docker.com/r/unicon/shibboleth-idp/
   If using an old docker-compose, then chekout 3c29f10
   because later commits require a too recent feature.

4. Modify `docker-compose.yml` so that the container won't try to use the port 80,
   so replace `"80:80"` with `"8080:80"`.

5. If your local Yii2 application is not using HTTPS,
   modify `idp/shibboleth-idp/conf/cas-protocol.xml`
   to replace `c:regex="https://idptestbed/.*"` by `c:regex="https?://idptestbed/.*"`.

6. Add `127.0.0.1 idptestbed` to `/etc/hosts`, as root.

7. Configure your Yii2 application to use:

        'host' => 'idptestbed',
        'port' => '443',
        'path' => '/idp/profile/cas',
        'certfile' => false,
        'debug' => true,

8. Start the containers:

        docker-compose build
        docker-compose run

9. Go to the login page of your Yii2 app.

10. Ctrl-C in the containers termainal to end them.

You can modify `ldap/users.ldif` if you want to add users to the CAS.
Don't forget to rebuild the Docker images after this.
