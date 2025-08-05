<?php

namespace Model;

/**
 * Class for encapsulating form data, used when adding a new secret.
 */
class NewSecretRequest
{
    public string $secretText;
    public int $expiresAfterViews;
    public int $expiresAfter;

    public function __construct(string $secretText, int $expiresAfterViews, int $expiresAfter)
    {
        $this->secretText = $secretText;
        $this->expiresAfterViews = $expiresAfterViews;
        $this->expiresAfter = $expiresAfter;
    }
}
