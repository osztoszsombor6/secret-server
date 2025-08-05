<?php

namespace Export;

/**
 * Class used to automatically select the correct method
 * in order to export data in the format required by the current request.
 * Uses application/json MIME type as a default.
 */
class ExporterFactory
{
    private static ExporterFactory $instance;
    private array $exporters = [];

    private const DEFAULT_EXPORTER = "DEFAULT";

    public static function getInstance(): ExporterFactory
    {
        if (!isset(self::$instance)) {
            self::$instance = new ExporterFactory();
            self::$instance->addExporter("application/json", [JsonExporter::class, 'export']);
            self::$instance->addExporter("*/*", [JsonExporter::class, 'export']);
            self::$instance->addExporter("application/xml", [XmlExporter::class, 'export']);
            self::$instance->addExporter(self::DEFAULT_EXPORTER, [JsonExporter::class, 'export']);
        }
        return self::$instance;
    }

    /**
     * Function for adding an exporter, which has a function to change format of data to a specific MIME type.
     *
     * @param string $type MIME type which the current added exporter will use.
     * @param callable|array $callback A callable exporter method,
     * or array containing an exporter class followed by the method of this class
     * which should be used to export data in the given type.
     *
     * @return void
     */
    public function addExporter(string $type, callable|array $callback)
    {
        $this->exporters[$type] = $callback;
    }

    /**
     * Function used to export data in the given MIME type.
     *
     * If the type is not provided by one of the exporters stored by the ExporterFactory instance,
     * a default is used instead.
     *
     * @param $data An object or error array which will be converted to a formatted string.
     * @param string $type MIME type of the created output.
     *
     * @return ?string Return value of the exporter method.
     */
    public static function export($data, string $type): ?string
    {
        $instance = self::getInstance();
        $callback = $instance->exporters[$type] ?? null;
        if ($callback == null) {
            $callback = $instance->exporters[self::DEFAULT_EXPORTER];
        }
        if (is_array($callback)) {
            [$class, $method] = $callback;
            $exporter = new $class();
            return $exporter->$method($data);
        }
        return $callback($data);
    }
}
