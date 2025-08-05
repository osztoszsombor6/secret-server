<?php

namespace Export;

/**
 * Class for transforming data into json format, used through the ExporterFactory.
 */
class JsonExporter
{
    public static function export($data): string
    {
        header('Content-Type: application/json');
        return json_encode($data);
    }
}
