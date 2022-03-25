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

$previousContents = [];
$currentValues = [];

while (true) {

    foreach($config['input'] as $inputId => $inputFile) {
        $contents = file_get_contents($inputFile);
        $now = new DateTimeImmutable();
    
        if(!isset($previousContents[$inputId]) || $contents != $previousContents[$inputId]) {
            echo "Updating target files...\n";
            $previousContents[$inputId] = $contents;
            
            foreach($config['output'] as $output) {
                if($output['input'] != $inputId) {
                    continue;
                }

                $found = false;
    
                foreach($config['mappings'][$output['mapping']] as $value => $file) {
                    if(strpos($contents, $value) === 0) {
                        if(!is_dir($output['basepath'])) {
                            mkdir($output['basepath'], 0777, true);
                        }
    
                        echo "Updating ". $output['basepath']. DS . $output['file']. "...\n";
                        copy($output['basepath']. DS . $file, $output['basepath']. DS . $output['file']);
                        $found = true;
                        break;
                    }
                }
    
                if(!$found && !empty($output['default'])) {
                    copy($output['basepath']. DS . $output['default'], $output['basepath']. DS . $output['file']);
                }
            }
        }
    }

    sleep($config['time']);
}