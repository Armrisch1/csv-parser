<?php

namespace src\models;

class NewsletterModel extends BaseModel
{

    /**
     * @param int $newsLetterId
     * @return int
     */
    public function checkNewsletterExistsById(int $newsLetterId): int
    {
        $stmt = $this->connection->prepare('SELECT COUNT(*) FROM newsletters WHERE id = :id');
        $stmt->execute([':id' => $newsLetterId]);

        return $stmt->fetchColumn();
    }
}