<?php

namespace steroids\auth\tests\unit;

use app\user\models\User;
use Exception;
use PHPUnit\Framework\TestCase;
use steroids\auth\AuthModule;
use steroids\auth\enums\AuthAttributeTypeEnum;
use steroids\auth\forms\ConfirmForm;
use steroids\auth\forms\LoginForm;
use steroids\auth\forms\AuthProviderLoginForm;
use steroids\auth\forms\RecoveryPasswordConfirmForm;
use steroids\auth\forms\RecoveryPasswordForm;
use steroids\auth\forms\RegistrationForm;
use steroids\auth\forms\TwoFactorConfirmForm;
use steroids\auth\models\AuthConfirm;
use steroids\auth\tests\mocks\TestAuthProvider;
use steroids\auth\tests\mocks\TestPayForm;
use steroids\auth\validators\TwoFactorRequireValidator;
use yii\base\Exception as YiiBaseException;
use yii\helpers\Json;

class AuthTest extends TestCase
{
    /**
     * Обычная регистрация через email с подтверждением и восстановлением пароля
     */
    public function testEmailWithConfirmAndRecovery()
    {
        $module = AuthModule::getInstance();
        $module->registrationMainAttribute = AuthAttributeTypeEnum::EMAIL;
        $module->loginAvailableAttributes = [AuthAttributeTypeEnum::EMAIL];

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
        $module->registrationMainAttribute = AuthAttributeTypeEnum::PHONE;
        $module->loginAvailableAttributes = [AuthAttributeTypeEnum::PHONE];

        // Register
        $registrationForm = new RegistrationForm();
        $registrationForm->phone = '+7' . (string)rand(1000000000, 9999999999);
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
        $authModule->registrationMainAttribute = AuthAttributeTypeEnum::EMAIL;
        $authModule->loginAvailableAttributes = [AuthAttributeTypeEnum::EMAIL];

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
        $authModule->registrationMainAttribute = AuthAttributeTypeEnum::PHONE;
        $authModule->loginAvailableAttributes = [AuthAttributeTypeEnum::PHONE];

        // auth only by email/phone + code
        $authModule->isPasswordAvailable = false;

        // Register
        $regForm = new RegistrationForm();
        $regForm->phone = '+7' . (string)rand(1000000000, 9999999999);

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
        $authModule->registrationMainAttribute = AuthAttributeTypeEnum::EMAIL;
        $authModule->loginAvailableAttributes = [
            AuthAttributeTypeEnum::PHONE,
            AuthAttributeTypeEnum::EMAIL,
            AuthAttributeTypeEnum::LOGIN,
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
            'phone' => '+7' . (string)rand(1000000000, 9999999999),
            'login' => 'any-login' . time(),
        ];

        $regForm->register();

        /** @var User $user */
        $user = $regForm->user;

        $this->assertNotNull($user);
        $this->assertNotEmpty($user->phone);

        // Confirm registration
        $regForm->confirm->markConfirmed();

        // Login via phone
        $loginForm = new LoginForm();
        $loginForm->login = $user->phone;
        $loginForm->login();

        $this->assertEquals('[]', Json::encode($loginForm->errors));
        $this->assertNotNull($loginForm->user);

        // Login via login
        $loginForm = new LoginForm();
        $loginForm->login = $user->login;
        $loginForm->login();

        $this->assertEquals('[]', Json::encode($loginForm->errors));
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
        $authModule->registrationMainAttribute = AuthAttributeTypeEnum::EMAIL;
        $authProviderName = 'test-auth-provider';

        $authModule->authProvidersClasses = [
            $authProviderName => TestAuthProvider::class,
        ];

        $authModule->authProviders = [
            $authProviderName => [
                'class' => TestAuthProvider::class,
                //configure any properties there
            ],
        ];

        // Auth only by email/phone + code
        $authModule->isPasswordAvailable = false;

        // Register
        $regSocialForm = new AuthProviderLoginForm();

        // Get auth-name from frontend
        $regSocialForm->name = $authProviderName;
        $regSocialForm->login();

        /** @var User $user */
        $user = $regSocialForm->social->user;
        $this->assertNotNull($user);
        $this->assertNotNull($user->email);

        // Recovery
        $recoveryForm = new RecoveryPasswordForm();
        $recoveryForm->login = $user->email;
        $recoveryForm->send();

        $this->assertEquals('[]', Json::encode($recoveryForm->errors));

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

        // Login via email and password
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
        $authModule->registrationMainAttribute = AuthAttributeTypeEnum::EMAIL;

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

    /**
     * Тестирование 2FA функционала (provider: notifier)
     */
    public function testTwoFactorNotifier()
    {
        $authModule = AuthModule::getInstance();
        $authModule->registrationMainAttribute = AuthAttributeTypeEnum::EMAIL;
        $authModule->twoFactorProviders = [
            'notifier' => [
                'attributeType' => AuthAttributeTypeEnum::EMAIL,
            ],
            'google' => [],
        ];

        // Register
        $regForm = new RegistrationForm();
        $regForm->email = 'test' . time() . '@test.dot';
        $regForm->password = '123456';
        $regForm->passwordAgain = '123456';
        $regForm->register();
        $this->assertNotNull($regForm->user);

        // Run validator in pay form
        $payForm = new TestPayForm();
        $payForm->user = $regForm->user;
        $payForm->providerName = 'notifier';
        $payForm->amount = 10;
        $payForm->validate();
        $this->assertEquals('{"amount":["2FA_REQUIRED:notifier"]}', Json::encode($payForm->errors));

        // Send verification code (wrong)
        $confirmForm = new TwoFactorConfirmForm();
        $confirmForm->user = $regForm->user;
        $confirmForm->providerName = $payForm->providerName;
        $confirmForm->code = '123aa';
        $confirmForm->validate();
        $this->assertEquals('{"code":["Валидация не пройдена"]}', Json::encode($confirmForm->errors));

        // Send verification code (true)
        $confirmForm->clearErrors();
        $confirmForm->code = AuthConfirm::find()
            ->select('code')
            ->where(['userId' => $regForm->user->getId()])
            ->scalar();
        $confirmForm->validate();
        $this->assertEquals('[]', Json::encode($confirmForm->errors));

        // Run validator in pay form again
        $payForm->clearErrors();
        $payForm->validate();
        $this->assertEquals('[]', Json::encode($payForm->errors));
    }
}
