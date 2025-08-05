<?php

namespace Model;

/**
 * Class representing a secret object.
 */
class Secret
{
    public string $secretText;
    public string $hash;
    public int $remainingViews;
    public string $createdAt;

    /**
     * String representing the expiration date of the secret, may be null if it does not expire with time.
     * @var $expiresAt
     */
    public ?string $expiresAt;

    public function __construct(
        string $secretText,
        string $hash,
        int $remainingViews,
        string $createdAt,
        ?string $expiresAt
    ) {
        $this->secretText = $secretText;
        $this->hash = $hash;
        $this->remainingViews = $remainingViews;
        $this->createdAt = $createdAt;
        $this->expiresAt = $expiresAt;
    }

    /**
     * Function for creating a secret object with use of builder design pattern.
     * @return SecretBuilder
     */
    public static function builder(): SecretBuilder
    {
        return new SecretBuilder();
    }
}
