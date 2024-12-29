<?php

namespace src\controllers;

use PDOException;
use Exception;
use src\helpers\CsvHelper;
use src\models\NewsletterModel;
use src\models\UserModel;
use src\models\UserNewsletterModel;
use src\services\SMSNotificationService;
use src\services\ResponseService;

class UserController extends BaseController
{
    private UserNewsletterModel $userNewsletterModel;
    private NewsletterModel $newsletterModel;
    private UserModel $userModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->newsletterModel = new NewsLetterModel();
        $this->userNewsletterModel = new UserNewsletterModel();

        ResponseService::setApiHeaders();
    }

    /**
     * @return void
     */
    public function saveCsvUsers(): void
    {
        $newsLetterId = $_POST['newsletter_id'] ?? null;
        $uploadedFile = $_FILES['csv_file'] ?? null;
        [$code, $message] = $this->validateSaveCsvUsersRequest($newsLetterId, $uploadedFile);

        if ($code != 200) {
            ResponseService::sendErrorResponse($code, $message);
        }

        $csvRows = CsvHelper::parse($uploadedFile['tmp_name']);
        $phoneNumbers = array_column($csvRows, 'phone_number');
        $placeholders = rtrim(str_repeat('?,', count($phoneNumbers)), ',');
        $existingPhoneNumbers = $this->userModel->getExistingPhoneNumbers($placeholders, $phoneNumbers);
        $newRecords = array_filter($csvRows, function ($row) use ($existingPhoneNumbers) {
            return !in_array($row['phone_number'], $existingPhoneNumbers);
        });

        if (!empty($newRecords)) {
            $batchSize = 50;
            $inserted = 0;
            $errors = [];
            $userIds = [];
            $batches = array_chunk($newRecords, $batchSize);

            foreach ($batches as $batch) {
                $placeholders = [];
                $values = [];

                foreach ($batch as $csvRow) {
                    if (!empty($csvRow['phone_number']) && !empty($csvRow['name'])) {
                        $placeholders[] = "(?, ?)";
                        $values[] = $csvRow['phone_number'];
                        $values[] = $csvRow['name'];
                    }
                }

                if (!empty($placeholders)) {
                    try {
                        $lastInsertId = $this->userModel->insertUsers($placeholders, $values);

                        for ($i = 0; $i < count($placeholders); $i++) {
                            $userIds[] = $lastInsertId + $i;
                        }

                        $inserted += count($placeholders);
                    } catch (PDOException $e) {
                        foreach ($batch as $csvRow) {
                            $errors[] = ["row" => $csvRow, "error" => $e->getMessage()];
                        }
                    }
                }
            }

            if (!empty($userIds)) {
                $placeholders = [];
                $values = [];

                foreach ($userIds as $userId) {
                    $placeholders[] = "(?, ?)";
                    $values[] = $userId;
                    $values[] = $newsLetterId;
                }

                try {
                    $this->userNewsletterModel->setRelation($placeholders, $values);
                } catch (PDOException $e) {
                    $errors[] = ["relation_insert_error" => $e->getMessage()];
                }
            }

            echo json_encode([
                "message" => "$inserted records inserted successfully.",
                "errors" => $errors
            ]);
        } else {
            echo json_encode(["message" => "No new records to insert"]);
        }
    }

    public function sendNewsletters(): void
    {
        try {
            $notificationDataList = $this->userModel->getNotificationData();
            $smsNotification = new SMSNotificationService();
            $sentAmount = 0;

            if (empty($notificationDataList)) {
                echo json_encode([
                    "message" => "No unsent notifications",
                ]);
                die();
            }

            foreach ($notificationDataList as $notificationData) {
                try {
                    $isSent = $smsNotification->send($notificationData);

                    if ($isSent) {
                        $this->userNewsletterModel->updateNotificationSendStatus($notificationData);
                        $sentAmount++;
                    }
                } catch (Exception $e) {
                    continue;
                }
            }

            echo json_encode([
                "message" => "$sentAmount Notifications are successfully sent.",
            ]);
        } catch (Exception $e) {
            ResponseService::sendErrorResponse(500, $e->getMessage());
        }
    }

    /**
     * @param int|null $newsLetterId
     * @param array|null $uploadedFile
     * @return array
     */
    private function validateSaveCsvUsersRequest(int|null $newsLetterId, array|null $uploadedFile): array
    {
        $code = 200;
        $message = null;

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $code = 405;
            $message = "Method Not Allowed";
        } else if (!is_numeric($newsLetterId) || is_null($uploadedFile)) {
            $code = 400;
            $message = "Bad request";
        } else if ($uploadedFile['error'] !== UPLOAD_ERR_OK) {
            $code = 400;
            $message = "File upload error";
        } else if (!CsvHelper::isValidCsvFile($uploadedFile)) {
            $code = 400;
            $message = "File extension must be a file of type: CSV";
        } else {
            $isNewsletterExists = $this->newsletterModel->checkNewsletterExistsById($newsLetterId);

            if (!$isNewsletterExists) {
                $code = 409;
                $message = "No newsletter with provided id";
            }
        }

        return [$code, $message];
    }
}