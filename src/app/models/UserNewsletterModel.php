<?php

namespace src\models;

class UserNewsletterModel extends BaseModel
{

    /**
     * @param array $placeholders
     * @param array $values
     * @return void
     */
    public function setRelation(array $placeholders, array $values): void
    {
        $relationSql = "INSERT INTO users_newsletters (`user_id`, `newsletter_id`) VALUES " . implode(", ", $placeholders);

        $relationStmt = $this->connection->prepare($relationSql);
        $relationStmt->execute($values);
    }

    /**
     * @param array $notificationData
     * @return void
     */
    public function updateNotificationSendStatus(array $notificationData): void
    {
        $stmt = $this->connection->prepare('UPDATE users_newsletters SET is_sent = 1 WHERE user_id = ? AND newsletter_id = ?');
        $stmt->execute([$notificationData['user_id'], $notificationData['newsletter_id']]);
    }
}