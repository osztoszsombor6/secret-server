<?php

namespace Model;

/**
 * Builder class for Secret objects.
 * Setter functions return builder instance to allow function chaining, ending with a call of the build function.
 */
class SecretBuilder
{
    private string $secretText;
    private string $hash;
    private int $remainingViews;
    private string $createdAt;
    private ?string $expiresAt;

    public function build(): Secret
    {
        return new Secret(
            $this->secretText,
            $this->hash,
            $this->remainingViews,
            $this->createdAt,
            $this->expiresAt
        );
    }

    public function setSecretText(string $secretText): self
    {
        $this->secretText = $secretText;
        return $this;
    }

    public function setHash(string $hash): self
    {
        $this->hash = $hash;
        return $this;
    }

    public function setRemainingViews(int $remainingViews): self
    {
        $this->remainingViews = $remainingViews;
        return $this;
    }

    public function setCreatedAt(string $createdAt): self
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function setExpiresAt(?string $expiresAt): self
    {
        $this->expiresAt = $expiresAt;
        return $this;
    }
}
