## Основные сценарии авторизации

### 1. Email/phone/login + password *(если isPasswordAvailable = true)

1.1 Регистрация

```
RegistrationForm
    [login] (email/phone/login)
    [password]
    [...custom attrubites]
```

1.2 Регистрация -> Подтверждение

```
ConfirmForm
    [email/phone]
    [code]
```

1.3 Вход

```
LoginForm
    [login] (email/phone/login)
    [password]
```

1.4 Вход -> Подтверждение *(если isPasswordAvailable = false)

```
ConfirmForm
    [email/phone]
    [code]
```

1.5 Восстановление

```
RecoveryPasswordForm
    [login] (email/phone)
```

1.6 Восстановление -> Подтверждение

```
RecoveryPasswordConfirmForm
    [login] (email/phone)
    [code]
```


### 2. Вход/регистрация через социальные сети (oauth)

2.1 Вход/регистрация

```
ProviderlLoginForm
    [socialParams]
```

2.2 Ввод email (если социальная сеть не выдала его)

```
SocialEmailForm
    [uid]
    [email]
```

2.2 Ввод email/phone -> Подтверждение

```
SocialConfirmForm
    [uid]
    [email]
    [code]
```

## Использование 2FA

1. В конфигурации необходимо объявить провайдеры, которые могут использоваться для 2fa, их сейчас может быть два - `notifier` и `google`.

```
    'modules' => [
        'auth' => [
            'twoFactorProviders' => [
                'notifier' => [],
            ],
        ],
    ],
```

2. В формах, где необходима 2FA, необходимо добавить TwoFactorRequireValidator

```
    [
        'amount',
        TwoFactorRequireValidator::class,
        'userId' => $this->user->primaryKey,
        'providerName' => 'notifier',
        'codeAttribute' => 'code',
    ]
```

Последний параметр `codeAttribute` не обязательный. Он необходим для случая, когда 2fa используется и обрабатывается
на фронтенде прям в форме (с помощью onTwoFactor обработчика компонента `Form`) и затем форма отправляет те же данные, только с кодом
подтверждения (`code`).

Если обработчик `onTwoFactor` не указывается на фронтенде, то код будет вводить в отдельной форме (например, в модальном окне)
и отправляться на бекенд методом `POST /api/v1/auth/2fa/<providerName>/confirm`. Тогда `codeAttribute` указывать не нужно.