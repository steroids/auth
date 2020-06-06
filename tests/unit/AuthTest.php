<?php

namespace steroids\auth\tests\unit;

use PHPUnit\Framework\TestCase;
use steroids\auth\AuthModule;
use steroids\auth\forms\ConfirmForm;
use steroids\auth\forms\LoginForm;
use steroids\auth\forms\RecoveryPasswordConfirmForm;
use steroids\auth\forms\RecoveryPasswordForm;
use steroids\auth\forms\RegistrationForm;

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
     * Обычная регистрация через соц сеть
     */
    public function testSocial()
    {

    }
}
