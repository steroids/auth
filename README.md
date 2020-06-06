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
