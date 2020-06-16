<?php

namespace steroids\auth\tests\unit;

use app\user\models\User;
use Exception;
use PHPUnit\Framework\TestCase;
use steroids\auth\AuthModule;
use steroids\auth\forms\ConfirmForm;
use steroids\auth\forms\LoginForm;
use steroids\auth\forms\ProviderLoginForm;
use steroids\auth\forms\RecoveryPasswordConfirmForm;
use steroids\auth\forms\RecoveryPasswordForm;
use steroids\auth\forms\RegistrationForm;
use steroids\auth\tests\mocks\TestAuthProvider;
use yii\base\Exception as YiiBaseException;

class AuthTest extends TestCase
{
    /**
     * Обычная регистрация через email с подтверждением и восстановлением пароля
     */
    public function testEmailWithConfirmAndRecovery()
    {
        $module = AuthModule::getInstance();
        $module->registrationMainAttribute = AuthModule::ATTRIBUTE_EMAIL;
        $module->loginAvailableAttributes = [AuthModule::ATTRIBUTE_EMAIL];

        // Register
        $registrationForm = new RegistrationForm();
        $registrationForm->email = 'test' . time() . '@test.com';
        $registrationForm->password = '123456';
        $registrationForm->passwordAgain = '123456';

        $registrationForm->register();
        $this->assertFalse($registrationForm->hasErrors());
        $this->assertNotNull($registrationForm->user->primaryKey);

        // Confirm registration
        $registrationConfirmForm = new ConfirmForm();
        $registrationConfirmForm->login = $registrationForm->email;
        $registrationConfirmForm->code = $registrationForm->confirm->code;
        $registrationConfirmForm->confirm();
        $this->assertFalse($registrationConfirmForm->hasErrors());
        $this->assertNotNull($registrationConfirmForm->confirm->user);

        // Login
        $loginForm = new LoginForm();
        $loginForm->login = $registrationForm->email;
        $loginForm->password = 'wrong';
        $loginForm->login();
        $this->assertTrue($loginForm->hasErrors());
        $loginForm->password = $registrationForm->password;
        $loginForm->login();
        $this->assertFalse($loginForm->hasErrors());
        $this->assertNotNull($loginForm->user);

        // Recovery
        $recoveryForm = new RecoveryPasswordForm();
        $recoveryForm->login = $loginForm->login;
        $recoveryForm->send();
        $this->assertFalse($recoveryForm->hasErrors());
        $this->assertNotNull($recoveryForm->user);
        $this->assertNotNull($recoveryForm->confirm);

        // Confirm recovery
        $registrationConfirmForm = new RecoveryPasswordConfirmForm();
        $registrationConfirmForm->login = $recoveryForm->login;
        $registrationConfirmForm->code = $recoveryForm->confirm->code;
        $registrationConfirmForm->newPassword = 'qwerty';
        $registrationConfirmForm->newPasswordAgain = 'qwerty';
        $registrationConfirmForm->confirm();
        $this->assertFalse($registrationConfirmForm->hasErrors());
        $this->assertNotNull($registrationConfirmForm->confirm->user);
    }

    /**
     * Обычная регистрация через телефон с подтверждением
     */
    public function testPhoneWithConfirm()
    {
        $module = AuthModule::getInstance();
        $module->registrationMainAttribute = AuthModule::ATTRIBUTE_PHONE;
        $module->loginAvailableAttributes = [AuthModule::ATTRIBUTE_PHONE];

        // Register
        $registrationForm = new RegistrationForm();
        $registrationForm->phone = '+7' . time();
        $registrationForm->password = '123456';
        $registrationForm->passwordAgain = '123456';

        $registrationForm->register();
        $this->assertFalse($registrationForm->hasErrors());
        $this->assertNotNull($registrationForm->user->primaryKey);

        // Confirm
        $registrationForm->confirm->markConfirmed();

        // Login
        $loginForm = new LoginForm();
        $loginForm->login = $registrationForm->phone;
        $loginForm->password = $registrationForm->password;
        $loginForm->login();
        $this->assertFalse($loginForm->hasErrors());
        $this->assertNotNull($loginForm->user);
    }

    /**
     * Авторизация через email без пароля
     *
     * @throws YiiBaseException
     * @throws Exception
     */
    public function testEmailAuthorize()
    {
        $authModule = AuthModule::getInstance();
        $authModule->registrationMainAttribute = AuthModule::ATTRIBUTE_EMAIL;
        $authModule->loginAvailableAttributes = [AuthModule::ATTRIBUTE_EMAIL];

        // auth only by email/phone + code
        $authModule->isPasswordAvailable = false;

        // Registration via email
        $regForm = new RegistrationForm();
        $regForm->email = 'test' . time() . '@test.com';

        $regForm->register();
        $this->assertNotNull($regForm->user);

        // Confirm registration
        $regForm->confirm->markConfirmed();

        // Login via email without password
        $loginForm = new LoginForm();
        $loginForm->login = $regForm->email;
        $loginForm->login();

        $this->assertNotNull($loginForm->user);
    }

    /**
     * Авторизация через телефон без пароля
     *
     * @throws YiiBaseException
     * @throws Exception
     */
    public function testPhoneAuth()
    {
        $authModule = AuthModule::getInstance();
        $authModule->registrationMainAttribute = AuthModule::ATTRIBUTE_PHONE;
        $authModule->loginAvailableAttributes = [AuthModule::ATTRIBUTE_PHONE];

        // auth only by email/phone + code
        $authModule->isPasswordAvailable = false;

        // Register
        $regForm = new RegistrationForm();
        $regForm->phone = '+7' . time();

        $regForm->register();
        $this->assertNotNull($regForm->user);

        // Confirm registration
        $regForm->confirm->markConfirmed();

        // Login via phone without password
        $loginForm = new LoginForm();
        $loginForm->login = $regForm->phone;
        $loginForm->login();

        $this->assertNotNull($loginForm->user);
    }
//

    /**
     * Регистрация через email, указываются phone и login.
     * Возможность выполнить вход через phone и login
     *
     * @throws YiiBaseException
     * @throws Exception
     */
    public function testRegistration()
    {
        $authModule = AuthModule::getInstance();
        $authModule->registrationMainAttribute = AuthModule::ATTRIBUTE_EMAIL;
        $authModule->loginAvailableAttributes = [
            AuthModule::ATTRIBUTE_PHONE,
            AuthModule::ATTRIBUTE_EMAIL,
            AuthModule::ATTRIBUTE_LOGIN,
        ];

        //custom fields
        $authModule->registrationCustomAttributes = [
            'phone',
            'login',
        ];

        // auth only by email/phone + code
        $authModule->isPasswordAvailable = false;

        // Registration
        $regForm = new RegistrationForm();
        $regForm->email = 'test' . time() . rand(0, 5000) . '@test.com';
        $regForm->custom = [
            'phone' => '+7' . time(),
            'login' => 'any-login' . time(),
        ];

        $regForm->register();

        /** @var User $user */
        $user = $regForm->user;

        $this->assertNotNull($user);
        $this->assertNotEmpty($user->phone);

        // Confirm registration
        $regForm->confirm->markConfirmed();

        //Login via phone
        $loginForm = new LoginForm();
        $loginForm->login = $user->phone;
        $loginForm->login();

        $this->assertNotNull($loginForm->user);

        //reset user
        $loginForm->user = null;

        //Login via login
        $loginForm->login = $user->login;
        $loginForm->login();

        $this->assertNotNull($loginForm->user);
    }

    /**
     * Регистрация через соц сеть,
     * восстановление пароля через email,
     * вход через email + password
     *
     * @throws YiiBaseException
     * @throws Exception
     */
    public function testSocial()
    {
        $authModule = AuthModule::getInstance();
        $authModule->registrationMainAttribute = AuthModule::ATTRIBUTE_EMAIL;
        $authProviderName = 'test-auth-provider';

        $authModule->providersClasses = [
            $authProviderName => TestAuthProvider::class,
        ];

        $authModule->providers = [
            $authProviderName => [
                'class' => TestAuthProvider::class,
                //configure any properties there
            ],
        ];

        // auth only by email/phone + code
        $authModule->isPasswordAvailable = false;

        // Register
        $regSocialForm = new ProviderLoginForm();

        //get auth-name from frontend
        $regSocialForm->name = $authProviderName;
        $regSocialForm->login();

        /** @var User $user */
        $user = $regSocialForm->social->user;
        $this->assertNotNull($user);

        //Recovery
        $recoveryForm = new RecoveryPasswordForm();
        $recoveryForm->login = $user->email;

        $this->assertTrue($recoveryForm->send());

        $recoveryForm->confirm->markConfirmed();

        // Confirm recovery
        $confirmForm = new RecoveryPasswordConfirmForm();
        $confirmForm->login = $recoveryForm->login;
        $confirmForm->code = $recoveryForm->confirm->code;

        $password = '123456';
        $confirmForm->newPassword = $password;
        $confirmForm->newPasswordAgain = $password;
        $confirmForm->confirm();

        $this->assertNotNull($confirmForm->confirm->user);

        //Login via email and password
        $loginForm = new LoginForm();
        $loginForm->login = $user->email;
        $loginForm->password = $password;
        $loginForm->login();

        $this->assertTrue(!$loginForm->hasErrors());
    }

    /**
     * Регистрация с кастомными полями (например, username)
     * @throws YiiBaseException
     */
    public function testCustomRegistrationFields()
    {
        $authModule = AuthModule::getInstance();
        $authModule->registrationMainAttribute = AuthModule::ATTRIBUTE_EMAIL;

        //custom field
        $authModule->registrationCustomAttributes = [
            'username'
        ];

        // auth only by email/phone + code
        $authModule->isPasswordAvailable = false;

        // Register
        $regForm = new RegistrationForm();
        $regForm->email = 'test' . time() . '@test.dot';
        $regForm->custom = [
            'username' => 'unique-login:' . time(),
        ];
        $regForm->register();

        $this->assertNotNull($regForm->user);
    }
}
