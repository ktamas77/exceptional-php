<?php

class ExceptionalRemoteAsync extends ExceptionalRemote {

    /*
     * Logs Remote or Locally
     */
    static function call_remote($url, $post_data) {
        if (ExceptionalAsync::$dirname == '') {
            if (Exceptional::$use_ssl === true) {
                $s = fsockopen("ssl://".Exceptional::$host, 443, $errno, $errstr, 4);
            } else {
                $s = fsockopen(Exceptional::$host, 80, $errno, $errstr, 2);
            }
        } else {
            if (is_dir(ExceptionalAsync::$dirname)) {
                $filename = 'exceptional_' . date('U') . uniqid() . '.log';
                $s = fopen($dirname . DIRECTORY_SEPARATOR . $filename, 'w');
            } else {
                echo "[Error $errno] $errstr\n";
                return false;
            }
        }

        if (!$s) {
            echo "[Error $errno] $errstr\n";
            return false;
        }

        $request  = "POST $url HTTP/1.1\r\n";
        $request .= "Host: ".Exceptional::$host."\r\n";
        $request .= "Accept: */*\r\n";
        $request .= "User-Agent: ".Exceptional::$client_name." ".Exceptional::$version."\r\n";
        $request .= "Content-Type: text/json\r\n";
        $request .= "Connection: close\r\n";
        $request .= "Content-Length: ".strlen($post_data)."\r\n\r\n";
        $request .= "$post_data\r\n";

        fwrite($s, $request);

        $response = "";
        while (!feof($s)) {
            $response .= fgets($s);
        }

        fclose($s);
    }

}
