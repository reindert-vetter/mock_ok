<?php

if (! function_exists('save_example')) {
    /**
     * @param        $data
     * @param string $reference
     * @return mixed
     */
    function save_example($data, string $reference = null)
    {
        $reference = $reference ?: FeatureContext::$exampleReference;

        if (! is_dir(FeatureContext::$examplePath)) {
            mkdir(FeatureContext::$examplePath, 0777, true);
        }

        $baseFileName = FeatureContext::$examplePath . $reference;

        file_put_contents("$baseFileName.serialize", serialize($data));

        echo "Example saved to: $baseFileName.serialize\n";

        return $data;
    }
}
