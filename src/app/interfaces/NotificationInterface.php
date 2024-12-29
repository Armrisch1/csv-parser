<?php

namespace src\interfaces;

interface NotificationInterface
{
    public function send(array $notificationData): bool;
}