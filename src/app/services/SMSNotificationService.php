<?php

namespace src\services;

use src\interfaces\NotificationInterface;

class SMSNotificationService implements NotificationInterface
{

    /**
     * @param array $notificationData
     * @return bool
     */
    public function send(array $notificationData): bool
    {
        // Sends email notifications to users
        return true;
    }
}