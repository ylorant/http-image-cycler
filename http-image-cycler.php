<?php

define('DS', DIRECTORY_SEPARATOR);

/**
 * Quick and dirty image cycler, to be able to choose a picture from a set according to a file content.
 */

$opts = "c:";
$longopts = [
    "config:"
];

$options = getopt($opts, $longopts);
$configFile = $options['c'] ?? $options['config'] ?? null;

if (empty($configFile)) {
    die("Missing config.\n");
}

$config = json_decode(file_get_contents($configFile), true);

if(empty($config)) {
    die("Invalid config.\n");
}

$previousContents = null;
$currentValues = [];

while (true) {
    $contents = file_get_contents($config['input']);
    $now = new DateTimeImmutable();

    if($contents != $previousContents) {
        echo "Updating target files...\n";
        $previousContents = $contents;
        
        foreach($config['output'] as $output) {
            foreach($config['mappings'][$output['mapping']] as $file => $value) {
                if(strpos($contents, $value) === 0) {
                    if(!is_dir($output['basepath'])) {
                        mkdir($output['basepath'], 0777, true);
                    }

                    echo "Updating ". $output['basepath']. DS . $output['file']. "...\n";
                    copy($output['basepath']. DS . $file, $output['basepath']. DS . $output['file']);
                    break;
                }
            }
        }
    }

    sleep($config['time']);
}