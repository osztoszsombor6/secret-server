<?php

namespace Service;

use Model\NewSecretRequest;
use Model\Secret;
use Database\SecretDB;

/**
 * Class responsible for encapsulating business logic of the secret server.
 */
class SecretService
{
    /**
     * Function for removing a secret object from the database.
     *
     * Objects which have expired based on time are not removed until a request attempts to retrieve them,
     * so an automatic scheduled process may be needed for removing these.
     *
     * @param Secret $secret The secret object to remove.
     * @return void
     */
    public function removeSecret(Secret $secret)
    {
        if ($secret != null) {
            $db = SecretDB::getInstance();
            $db->removeSecret($secret);
        }
    }

    /**
     * Function which returns the secret identified by the given hash.
     *
     * Checks if the secret is expired and also reduces the number of times
     * the given secret can be viewed in the future,
     * updating the corresponding database row.
     *
     * @param string $hash The hash used to search for a secret.
     * @return Secret|null A Secret object if the hash identifies a stored and viewable secret, null otherwise.
     */
    public function getSecretByHash(string $hash): ?Secret
    {
        $db = SecretDB::getInstance();
        $secret = $db->findByHash($hash);
        if ($secret != null) {
            if ($secret->remainingViews <= 0) {
                $this->removeSecret($secret);
                return null;
            }
            $now = time();
            $expiresAt = Utils::getDateTimeFromString($secret->expiresAt);
            // If $expiresAt is null, the secret never expires based on time
            if ($expiresAt != null && $expiresAt->format('U') < $now) {
                $this->removeSecret($secret);
                return null;
            }
            $secret->remainingViews--;
            $db->updateSecret($secret);
        }
        return $secret;
    }

    /**
     * Function for creating and adding a Secret object to the database, from data sent in a post request.
     *
     * Checking the validity of the input is also done here.
     *
     * @param NewSecretRequest $secretRequest Class containing the secret data given in the form.
     * @return Secret|null The created secret object is returned, or null in case of invalid form data.
     */
    public function addSecret(NewSecretRequest $secretRequest): ?Secret
    {
        if (!$this->checkNewSecretRequest($secretRequest)) {
            return null;
        }
        $secret = $this->createSecretFromRequest($secretRequest);
        $db = SecretDB::getInstance();
        $db->addSecret($secret);
        return $secret;
    }

    /**
     * Function for creating a secret object from form data provided in a post request.
     *
     * The date fields of the secret object are calculated here, along with the hash
     * used to uniquely identify it.
     *
     * @param NewSecretRequest $secretRequest Class containing the secret data given in the form.
     * @return Secret The created secret object.
     */
    private function createSecretFromRequest(NewSecretRequest $secretRequest): Secret
    {
        $createdAt = new \DateTime('now');
        $hashValue = UuidService::generateUuid();
        $secretText = $secretRequest->secretText;
        $remainingViews = $secretRequest->expiresAfterViews;
        $expiresAt = $this->getExpiresAt($secretRequest, $createdAt);
        $createdAtStr = $createdAt->format(SecretDB::DB_DATE_FORMAT);
        if ($expiresAt == null) {
            $expiresAtStr = $expiresAt;
        } else {
            $expiresAtStr = $expiresAt->format(SecretDB::DB_DATE_FORMAT);
        }
        $secret = Secret::builder()->setCreatedAt($createdAtStr)
            ->setExpiresAt($expiresAtStr)
            ->setSecretText($secretText)
            ->setRemainingViews($remainingViews)
            ->setHash($hashValue)
            ->build();
        return $secret;
    }

    /**
     * Function for checking validity of input data used to create a new secret.
     *
     * @param NewSecretRequest $secretRequest Class containing the secret data given in the form.
     * @return bool Returns true if the form data is valid, false otherwise.
     */
    public function checkNewSecretRequest(NewSecretRequest $secretRequest): bool
    {
        if ($secretRequest->expiresAfterViews <= 0) {
            return false;
        }
        if ($secretRequest->expiresAfter < 0) {
            return false;
        }
        if ($secretRequest->secretText == null || $secretRequest->secretText == '') {
            return false;
        }
        return true;
    }

    /**
     * Function for calculating when a secret's expiry date should be based on posted form data.
     *
     * @param NewSecretRequest $secretRequest Class containing the secret data given in the form.
     * @param \DateTime $createdAt Date with timestamp documenting the creation of the secret object.
     * @return \DateTime|null The resulting date with timestamp markind expiry, or null if the secret is nonexpiring.
     */
    private function getExpiresAt(NewSecretRequest $secretRequest, \DateTime $createdAt): ?\DateTime
    {
        if ($secretRequest->expiresAfter == 0) {
            return null;
        }
        $expiresAt = clone $createdAt;
        $expiresAt->add(new \DateInterval('PT' . $secretRequest->expiresAfter . 'M'));
        return $expiresAt;
    }
}
