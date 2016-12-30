<?php
/*
 * DATABASE AND CODES CONFIGURATION

 	DATABASE
 		*Use these settings to configure the database server and users

 	CONSTANT CODES
 		*Declare all needed codes to help with the responses from the database operations
 		*Call from a file were Config.php is included as if it was within the same file
 		*include Config.php;
 			echo USER_CREATED_SUCCESSFULLY;
 */

//DATABASE PARAMETERS
define('DB_USERNAME', 'root');
define('DB_PASSWORD', '');
define('DB_HOST', 'localhost');
define('DB_NAME', 'task_manager');

 // DATABASE ERROR CODES
define('USER_CREATED_SUCCESSFULLY', 0);
define('USER_CREATE_FAILED', 1);
define('USER_ALREADY_EXISTED', 2);

// GENERIC API CODES
define('SUCCESS', 0);
define('GENERIC_ERROR', 50);

//HTTP CODES
//Successful 2xx
define('HTTP_SUCCESS', 200);
define('HTTP_CREATED', 201);
define('HTTP_ACCEPTED', 202);

//Client Error 4xx
define('HTTP_BAD_REQUEST', 400);
define('HTTP_UNAUTHORIZED', 401);
define('HTTP_FORBIDEN', 403);
define('HTTP_NOT_FOUND', 404);
define('HTTP_METHOD_NOT_ALLOWED', 405);
define('HTTP_NOT_ACCEPTABLE', 406);
define('HTTP_TOO_MANY_REQUESTS', 429);

//Server Error 5xx
define('HTTP_INTERNAL_SERVER_ERROR', 500);
define('HTTP_NOT_IMPLEMENTED', 501);
define('HTTP_BAD_GATEWAY', 502);
define('HTTP_SERVICE_UNAVAILABLE', 503);
define('HTTP_GATEWAY_TIMEOUT', 504);

?>