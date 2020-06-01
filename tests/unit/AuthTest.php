<?php

namespace steroids\auth\tests\unit;

use PHPUnit\Framework\TestCase;
use steroids\auth\forms\LoginForm;
use steroids\auth\forms\RegistrationForm;

class AuthTest extends TestCase
{
    public function testRegistration()
    {
        // Register
        $registrationForm = new RegistrationForm();
        $registrationForm->email = 'test' . time() . '@test.com';
        $registrationForm->password = '123456';
        $registrationForm->passwordAgain = '123456';

        $registrationForm->register();
        $this->assertFalse($registrationForm->hasErrors());
        $this->assertNotNull($registrationForm->user->primaryKey);

        // Confirm
        $registrationForm->confirm->markConfirmed();

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
    }
}
