<?php

require_once dirname(__FILE__) . '/exceptional.php';
require_once dirname(__FILE__) . '/exceptional/remote_async.php';

/*
 * Asynchronous GetExceptional Handler
 * 
 * @author Tamas Kalman <ktamas77@gmail.com>
 * 
 */

class ExceptionalAsync extends Exceptional
{

    static $dirname;

    /*
     * Installs Exceptional as the default exception handler
     */

    static function setup($api_key, $use_ssl = false, $dirname = '')
    {
        if ($api_key == "") {
            $api_key = null;
        }

        self::$api_key = $api_key;
        self::$use_ssl = $use_ssl;

        self::$dirname = $dirname;

        self::$exceptions = array();
        self::$context = array();
        self::$action = "";
        self::$controller = "";

        // set exception handler & keep old exception handler around
        self::$previous_exception_handler = set_exception_handler(
                array("ExceptionalAsync", "handle_exception")
        );

        self::$previous_error_handler = set_error_handler(
                array("ExceptionalAsync", "handle_error")
        );

        register_shutdown_function(
                array("ExceptionalAsync", "shutdown")
        );
    }

    /**
     * Set up the logging dir 
     * 
     * @param String $dirname Dir name to log 
     */
    static function set_dir($dirname)
    {
        self::$dirname = $dirname;
    }

    /**
     * Unsets dirname (switch back to remote logging)
     */
    static function unset_dir()
    {
        self::$dirname = '';
    }

    static function shutdown()
    {
        if ($e = error_get_last()) {
            self::handle_error($e["type"], $e["message"], $e["file"], $e["line"]);
        }
    }

    static function handle_error($errno, $errstr, $errfile, $errline)
    {
        if (!(error_reporting() & $errno)) {
            // this error code is not included in error_reporting
            return;
        }

        switch ($errno) {
            case E_NOTICE:
            case E_USER_NOTICE:
                $ex = new PhpNotice($errstr, $errno, $errfile, $errline);
                break;

            case E_WARNING:
            case E_USER_WARNING:
                $ex = new PhpWarning($errstr, $errno, $errfile, $errline);
                break;

            case E_STRICT:
                $ex = new PhpStrict($errstr, $errno, $errfile, $errline);
                break;

            case E_PARSE:
                $ex = new PhpParse($errstr, $errno, $errfile, $errline);
                break;

            default:
                $ex = new PhpError($errstr, $errno, $errfile, $errline);
        }

        self::handle_exception($ex, false);

        if (self::$previous_error_handler) {
            call_user_func(self::$previous_error_handler, $errno, $errstr, $errfile, $errline);
        }
    }

    /*
     * Exception handle class. Pushes the current exception onto the exception
     * stack and calls the previous handler, if it exists. Ensures seamless
     * integration.
     */

    static function handle_exception($exception, $call_previous = true)
    {
        self::$exceptions[] = $exception;

        if (Exceptional::$api_key != null) {
            $data = new ExceptionalData($exception);
            ExceptionalRemoteAsync::send_exception($data);
        }

        // if there's a previous exception handler, we call that as well
        if ($call_previous && self::$previous_exception_handler) {
            call_user_func(self::$previous_exception_handler, $exception);
        }
    }

}

?>
