<?php

// Async library is a backward compatible extension of the original
// getexceptional library. This extension optionally extends the original
// concept with a local logging functionality
//
// each exception is get logged into a local file asynchronously
// which can be picked up periodically by a handler which could
// help to send these packages to the destination
// instead of sending it directly from the application
//

// setup Exceptional with the following two lines
require dirname(__FILE__)."/../exceptional_async.php";
ExceptionalAsync::setup("YOUR-API-KEY", false, "/var/log/exceptional");

// control which errors are caught with error_reporting
error_reporting(E_ALL);

// start testing
$math = 1 / 0;

echo "end\n";

?>