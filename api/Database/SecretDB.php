<?php

namespace Database;

use Model\Secret;

/**
 * Class providing database CRUD operations on the secret database.
 *
 * Uses singleton design pattern, limiting the number of instances per request to one.
 */
class SecretDB
{
    private \mysqli $db;

    public const DB_DATE_FORMAT = "Y-m-d H:i:s";

    private static SecretDB $instance;

    public static function getInstance(): SecretDB
    {
        if (!isset(self::$instance)) {
            self::$instance = new SecretDB();
        }
        return self::$instance;
    }

    private function __construct()
    {
        $this->db = DB::getDB();
        if ($this->db->connect_error) {
            die("Connection failed:{$this->db->connect_error}");
        }
    }

    /**
     * Function which retrieves a Secret object from the database.
     *
     * @param string $hash Hash used to identify the secret to retrieve.
     * @return ?Secret A secret object matching the hash, or null if no object is found.
     */
    public function findByHash(string $hash): ?Secret
    {
        $sql = "SELECT secretText, hashValue, createdAt, expiresAt, remainingViews FROM secret WHERE hashValue=?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("s", $hash);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();

            $secretText = $row["secretText"];
            $hashValue = $row["hashValue"];
            $remainingViews = $row["remainingViews"];
            $createdAt = $row["createdAt"];
            $expiresAt = $row["expiresAt"];
            $secret = Secret::builder()->setCreatedAt($createdAt)
            ->setExpiresAt($expiresAt)
            ->setSecretText($secretText)
            ->setRemainingViews($remainingViews)
            ->setHash($hashValue)
            ->build();
            return $secret;
        } else {
            return null;
        }
    }

    /**
     * Function which stores a Secret object in the database.
     *
     * @param Secret $Secret The object to be saved.
     */
    public function addSecret(Secret $secret): void
    {
        $sql = "INSERT into secret (secretText, hashValue, remainingViews, createdAt, expiresAt) VALUES (?,?,?,?,?)";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param(
            "ssiss",
            $secret->secretText,
            $secret->hash,
            $secret->remainingViews,
            $secret->createdAt,
            $secret->expiresAt
        );
        $stmt->execute();
    }

    /**
     * Function for removing Secret objects from the database.
     *
     * @param Secret $Secret The object to be removed.
     */
    public function removeSecret($secret): void
    {
        if ($secret != null && $secret->hash != null) {
            $sql = "DELETE FROM secret WHERE hashValue=?";
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param("s", $secret->hash);
            $stmt->execute();
        }
    }

    /**
     * Function for changing existing Secret objects in the database.
     *
     * @param Secret $Secret The Secret object representing the state which the corresponding database row should match.
     */
    public function updateSecret($secret)
    {
        if ($secret != null && $secret->hash != null) {
            $sql = "UPDATE secret SET remainingViews = ? WHERE hashValue = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param("is", $secret->remainingViews, $secret->hash);
            $stmt->execute();
        }
    }
}
