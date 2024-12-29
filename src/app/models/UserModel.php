<?php

namespace src\models;
use PDO;

class UserModel extends BaseModel
{

    /**
     * @param string $placeholders
     * @param array $phoneNumbers
     * @return array
     */
    public function getExistingPhoneNumbers(string $placeholders, array $phoneNumbers): array
    {
        $sql = "SELECT phone_number FROM users WHERE phone_number IN ($placeholders)";

        $stmt = $this->connection->prepare($sql);
        $stmt->execute($phoneNumbers);

        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * @param string $placeholders
     * @param array $values
     * @return int
     */
    public function insertUsers(array $placeholders, array $values): int
    {
        $sql = "INSERT INTO users (`phone_number`, `name`) VALUES " . implode(", ", $placeholders) . " ON DUPLICATE KEY UPDATE phone_number = phone_number";

        $stmt = $this->connection->prepare($sql);
        $stmt->execute($values);

        return $this->connection->lastInsertId();
    }

    /**
     * @return array
     */
    public function getNotificationData(): array
    {
        $sql = "
            SELECT un.user_id, un.newsletter_id, u.phone_number, n.title, n.text FROM users AS u
            INNER JOIN users_newsletters AS un ON un.user_id = u.id
            INNER JOIN newsletters AS n ON n.id = un.newsletter_id
            WHERE un.is_sent = 0
        ";

        $stmt = $this->connection->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}