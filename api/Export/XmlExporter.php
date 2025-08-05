<?php

namespace Export;

use Service\Utils;

/**
 * Class for transforming data into xml format, used through the ExporterFactory.
 */
class XmlExporter
{
    public static function export($data): string
    {
        header('Content-Type: application/xml');
        $xmlHeader = '<?xml version="1.0" encoding="UTF-8">';
        $xml = '';
        //Error messages are passed in an array
        if (is_array($data)) {
            $xml = '<Error>';
            foreach ($data as $key => $value) {
                $xml .= '<' . $key . '>' . $value . '</' . $key . '>';
            }
            $xml .= '</Error>';
            return $xmlHeader . $xml;
        }
        //Objects use the class name as xml tag
        $class_name = Utils::getUnqualifiedClassName(get_class($data));
        $class_vars = get_class_vars(get_class($data));
        $xml .= '<' . $class_name . '>';
        foreach ($class_vars as $name => $value) {
            $xml .= '<' . $name . '>' . $data->$name . '</' . $name . '>';
        }
        $xml .= '</' . $class_name . '>';
        return $xmlHeader . $xml;
    }
}
