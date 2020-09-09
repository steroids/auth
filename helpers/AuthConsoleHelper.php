<?php

namespace steroids\auth\helpers;

use steroids\auth\AuthModule;
use steroids\auth\UserInterface;
use steroids\core\base\Model;
use yii\console\widgets\Table;
use yii\helpers\Console;

class AuthConsoleHelper
{
    /**
     * @param bool $onlyById
     * @return int
     * @throws \yii\base\Exception
     */
    public static function searchUser(bool $onlyById = false)
    {
        $authModule = AuthModule::getInstance();

        // Read users login
        /** @var UserInterface|Model $userClass */
        $userClass = $authModule->userClass;
        $userLogin = Console::input($onlyById ? 'Write user ID: ' : 'Search user by id/email/phone/login: ');

        // Search by id
        if ($onlyById || preg_match('/^[0-9]$/', $userLogin)) {
            if ($userClass::find()->where(['id' => (int)$userLogin])->exists()) {
                return (int)$userLogin;
            } else {
                echo "User not found...\n";
                return static::searchUser();
            }
        }

        // Search user by email/phone/login
        /** @var UserInterface[]|Model[] $users */
        $users = $userClass::find()
            ->where([
                'and',
                ...array_map(
                    fn($attr) => ['like', $authModule->getUserAttributeName($attr), $userLogin],
                    $authModule->loginAvailableAttributes
                )
            ])
            ->limit(10)
            ->all();
        if (!$users) {
            echo "Users not found...\n";
            return static::searchUser();
        }

        // Show fined users
        echo Table::widget([
            'headers' => ['id', 'name', ...$authModule->loginAvailableAttributes],
            'rows' => array_map(
                function ($user) use ($authModule) {
                    return [
                        $user->primaryKey,
                        $user->getName(),
                        ...array_map(
                            fn($attr) => $user->getAttribute($authModule->getUserAttributeName($attr)),
                            $authModule->loginAvailableAttributes
                        ),
                    ];
                },
                $users
            ),
        ]);

        // Select id
        return static::searchUser(true);
    }
}