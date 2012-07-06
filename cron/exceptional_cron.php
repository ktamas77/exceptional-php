<?php

/*
 * This file can be called from cron. Picks up files from getExceptional libary
 * and sends it to the remote server.
 */

$host = 'plugin.getexceptional.com';

if (empty($argv[1])) {
    printf("Usage: %s <dirname> [--ssl]\n", $argv[0]);
    exit(1);
}

$dir = $argv[1];

$ssl = ((isset($argv[2])) && (strtolower($argv[2]) == '--ssl')) ? true : false;

$files = glob($argv . DIRECTORY_SEPARATOR . '*.log');

foreach ($files as $file) {
    $filename = $argv . DIRECTORY_SEPARATOR . $file;
    $request = file_get_content($filename);
    
    if ($ssl === true) {
        $s = fsockopen("ssl://".$host, 443, $errno, $errstr, 4);
    } else {
        $s = fsockopen($host, 80, $errno, $errstr, 2);
    }
    
    if ($s === false) {
        die(sprintf('cannot connect to getexceptional server [%s]', $host));
    }
    
    fwrite($s, $request);

    $response = "";
    while (!feof($s)) {
        $response .= fgets($s);
    }

    fclose($s);
    
    unset($filename);
}

?>