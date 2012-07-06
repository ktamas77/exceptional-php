<?php

class ExceptionalRemoteAsync extends ExceptionalRemote
{

    /**
     * Sends a compressed Exception remote or locally
     * 
     * @param Exception $exception EXception 
     * 
     * @return void
     */
    static function send_exception($exception)
    {
        $uniqueness_hash = $exception->uniqueness_hash();
        $hash_param = ($uniqueness_hash) ? null : "&hash={$uniqueness_hash}";
        $url = "/api/errors?api_key=" . Exceptional::$api_key . "&protocol_version=" . Exceptional::$protocol_version . $hash_param;
        $compressed = gzencode($exception->to_json(), 1);
        self::call_remote($url, $compressed);
    }

    /*
     * Logs Remote or Locally
     * 
     * @param String $url       Destination URL
     * @param String $post_data Post Data
     * 
     * @return void
     */
    static function call_remote($url, $post_data)
    {
        if (ExceptionalAsync::$dirname == '') {
            if (Exceptional::$use_ssl === true) {
                $s = fsockopen("ssl://" . Exceptional::$host, 443, $errno, $errstr, 4);
            } else {
                $s = fsockopen(Exceptional::$host, 80, $errno, $errstr, 2);
            }
            if (!$s) {
                echo "[Error $errno] $errstr\n";
                return false;
            }
        } else {
            if (is_dir(ExceptionalAsync::$dirname)) {
                $filename = 'exceptional_' . date('U') . uniqid() . '.log';
                $fullname = ExceptionalAsync::$dirname . DIRECTORY_SEPARATOR . $filename;
                $s = fopen($fullname, 'w');
            } else {
                printf("[No such dir: %s]\n", ExceptionalAsync::$dirname);
                return false;
            }
            if ($s === false) {
                printf("[Cannot write file: %s]\n", $filename);
                return false;
            }
        }

        $request = "POST $url HTTP/1.1\r\n";
        $request .= "Host: " . Exceptional::$host . "\r\n";
        $request .= "Accept: */*\r\n";
        $request .= "User-Agent: " . Exceptional::$client_name . " " . Exceptional::$version . "\r\n";
        $request .= "Content-Type: text/json\r\n";
        $request .= "Connection: close\r\n";
        $request .= "Content-Length: " . strlen($post_data) . "\r\n\r\n";
        $request .= "$post_data\r\n";

        fwrite($s, $request);

        if (ExceptionalAsync::$dirname == '') {
            $response = "";
            while (!feof($s)) {
                $response .= fgets($s);
            }
        }


        fclose($s);
    }

}
