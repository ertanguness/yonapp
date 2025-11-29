<?php
namespace App\Services;

use Database\Db;
use Model\UserModel;

class NotificationService
{
    public static function notifyByRegistrationMethod(int $userId, string $message, ?string $emailSubject = null): bool
    {
        $User = new UserModel();
        $user = $User->getUser($userId);
        if (!$user) { return false; }

        $pdo = Db::getInstance()->connect();
        $stmt = $pdo->prepare('SELECT method FROM user_registration_methods WHERE user_id = ?');
        $stmt->execute([$userId]);
        $row = $stmt->fetch(\PDO::FETCH_OBJ);
        $method = $row->method ?? 'email';

        if ($method === 'phone') {
            if (empty($user->phone)) { return false; }
            return SmsGonderService::gonder([$user->phone], $message);
        }

        return MailGonderService::gonder([$user->email], $user->full_name, $message);
    }
}