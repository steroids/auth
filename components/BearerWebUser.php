<?php

namespace steroids\auth\components;

use steroids\auth\AuthModule;
use steroids\auth\UserInterface;
use Yii;
use steroids\auth\models\AuthLogin;
use yii\helpers\ArrayHelper;
use yii\web\ForbiddenHttpException;
use yii\web\IdentityInterface;

/**
 * @property-read bool $isGuest
 * @property-read int|null $id
 * @property-read UserInterface|null $identity
 * @property-read UserInterface|null $model
 * @property-read string|null $accessToken
 */
class BearerWebUser extends \yii\web\User
{
    protected const REQUEST_ACCESS_TOKEN_KEY = 'accessToken';
    protected const WS_TOKEN_CACHE_TIME = 60;

    /**
     * @var AuthLogin
     */
    private $_login = false;

    /**
     * @var UserInterface
     */
    private $_identity = false;

    /**
     * @var string
     */
    private $_accessToken = false;

    public function init()
    {
        if ($this->identityClass === null) {
            $this->identityClass = AuthModule::getInstance()->userClass;
        }

        parent::init();
    }

    /**
     * @return bool
     */
    public function getIsGuest()
    {
        return !$this->getIdentity();
    }

    /**
     * @return AuthLogin|null
     */
    public function getLogin()
    {
        if ($this->_login === false) {
            /** @var AuthLogin $authLoginClass */
            $authLoginClass = AuthModule::resolveClass(AuthLogin::class);
            $this->_login = $authLoginClass::findByToken($this->accessToken);
        }
        return $this->_login;
    }

    /**
     * @inheritdoc
     */
    public function getIdentity($autoRenew = true)
    {
        if ($this->_identity !== false) {
            return $this->_identity;
        }
        return ArrayHelper::getValue($this->getLogin(), 'user');
    }

    public function setIdentity($value)
    {
        $this->_identity = $value;
    }

    /**
     * @return UserInterface|null
     */
    public function getModel()
    {
        return $this->getIdentity();
    }

    /**
     * @return int|null
     */
    public function getId()
    {
        return ArrayHelper::getValue($this->getLogin(), 'userId');
    }

    /**
     * @return string
     */
    public function getName()
    {
        return ArrayHelper::getValue($this->getModel(), 'name');
    }

    /**
     * @inheritdoc
     */
    public function login(IdentityInterface $identity, $duration = 0)
    {
        /** @var AuthLogin $authLoginClass */
        $authLoginClass = AuthModule::resolveClass(AuthLogin::class);
        $this->_login = $authLoginClass::create($identity, \Yii::$app->request);
        $this->_accessToken = false;
        $this->regenerateCsrfToken();
    }

    /**
     * @inheritdoc
     */
    public function switchIdentity($user, $duration = 0)
    {
        if ($user) {
            $this->login($user);
        } else {
            $this->logout();
        }
    }

    /**
     * @inheritdoc
     */
    public function logout($destroySession = true)
    {
        $login = $this->getLogin();
        if ($login) {
            $login->logout();
            $this->_login = false;
            $this->_accessToken = false;
            $this->regenerateCsrfToken();
        }
    }

    /**
     * @return string|null
     */
    public function getAccessToken()
    {
        if ($this->_accessToken === false) {
            if ($this->_login) {
                $this->_accessToken = $this->_login->accessToken;
            } else {
                // Try get from headers
                $authHeader = \Yii::$app->request->headers->get('Authorization');
                $this->_accessToken = $authHeader && preg_match('/^Bearer\s+(.*)$/', $authHeader, $match)
                    ? trim($match[1])
                    : null;

                // Try get from get param
                if (!$this->_accessToken) {
                    $this->_accessToken = Yii::$app->request->get(static::REQUEST_ACCESS_TOKEN_KEY);
                }
            }
        }
        return $this->_accessToken;
    }

    /**
     * Regenerates CSRF token
     */
    protected function regenerateCsrfToken()
    {
        $request = Yii::$app->getRequest();
        if ($request->enableCsrfCookie) {
            $request->getCsrfToken(true);
        }
    }

    public function loginRequired($checkAjax = true, $checkAcceptHeader = true)
    {
        throw new ForbiddenHttpException(Yii::t('yii', 'Login Required'));
    }

    /**
     * @param false $force
     * @return string|null
     * @throws \yii\base\Exception
     */
    public function refreshWsToken($force = false)
    {
        $authLogin = $this->getLogin();

        if (!$authLogin) {
            return null;
        }

        if (
            !$authLogin->wsToken
            || $force
            // Don't refresh token more often than static::WS_TOKEN_CACHE_TIME seconds
            || time() - strtotime($authLogin->createTime) > static::WS_TOKEN_CACHE_TIME
        ) {
            $authLogin->updateAttributes([
                'wsToken' => \Yii::$app->security->generateRandomString(AuthLogin::WS_TOKEN_LENGTH),
            ]);

            // Set in redis
            \Yii::$app->ws->addToken($this->identity, $authLogin->wsToken);
        }

        return $authLogin->wsToken;
    }
}
