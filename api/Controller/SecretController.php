<?php

namespace Controller;

use Model\NewSecretRequest;
use Service\SecretService;
use Service\Utils;
use Export\ExporterFactory;

/**
 * Class responsible for processing incoming requests and generating responses for them.
 */
class SecretController
{
    /**
     * Function which processes post requests for adding secrets, using form data.
     *
     * Creates an object storing the post request form data and passes this on to a SecretService
     * instance in order to create the corresponding secret. If the form data is invalid, can set
     * http error code 405.
     *
     * @return string The result of the computation run based on the request,
     * formatted into the appropriate MIME type based on the accept header.
     */
    public static function processAddSecretRequest(): string
    {
        try {
            $secretText = htmlspecialchars($_POST["secret"], ENT_QUOTES);
            $expiresAfterViews = htmlspecialchars($_POST["expireAfterViews"], ENT_QUOTES);
            $expiresAfter = htmlspecialchars($_POST["expireAfter"], ENT_QUOTES);

            $secret = new NewSecretRequest($secretText, $expiresAfterViews, $expiresAfter);
            $secretService = new SecretService();
            $result = $secretService->addSecret($secret);
            return self::export($result, "Invalid input", 405);
        } catch (\Throwable $e) {
            return self::export(null, "Invalid input", 405);
        }
    }

    /**
     * Function which processes get requests for retrieving secrets stored in the database.
     *
     * Uses the hash provided in the uri to select the secret which should be returned.
     * Secret objects are retrieved from the database through a SecretService object.
     * If a secret is not found or has expired, 404 error code is set.
     *
     * @return string The result of the computation run based on the request,
     * formatted into the appropriate MIME type based on the accept header.
     */
    public static function processGetSecretRequest(): string
    {
        $uri = $_SERVER['REQUEST_URI'];
        $params = array_slice(explode("/", $uri), 1);
        // Length of params array is known because of regex matching in Router class.
        $hash = $params[2];
        $secretService = new SecretService();
        $result = $secretService->getSecretByHash($hash);
        return self::export($result, "Secret not found", 404);
    }

    /**
     * Function for extracting the MIME type that should be used in the response based on the http Accept header.
     *
     * It is assumed that the Accept header contains only a single MIME type.
     *
     * @return string Content of the Accept header in the response, defaulting to application/json.
     */
    private static function getExportType(): string
    {
        $headers = Utils::getRequestHeaders();

        foreach ($headers as $header => $value) {
            if ($header == "Accept") {
                return $value;
            }
        }
        return "application/json";
    }

    /**
     * Exports to data the correct format which should be sent in the response.
     *
     * @param $data The object which should be formatted, can be null.
     * @param $errorMessage The message that should be shown if the $data parameter is null.
     * @param $errorCode Http error code which is used if the $data parameter is null.
     * @return string Formatted representation response body.
     */
    private static function export($data, $errorMessage, $errorCode): string
    {
        if ($data == null) {
            http_response_code($errorCode);
            $data = ["description" => $errorMessage];
        }
        $exportType = self::getExportType();
        return ExporterFactory::export($data, $exportType);
    }
}
