<?php
 
 /*
    BASIC API FUNCTIONING WITH SLIM 2.X
    
    APP GENERAL FUNCTIONING
        *A new instance of Slim is accessed with new \Slim\Slim();
        *To run the slim instance the line $app -> run();, this line needs to be placed at last of the script
        *To stop the slim instance the line $app -> stop();
        *Use this layer for data validation before passing the parameters to the functions



    ERORR REPORTING
        Use the function echoResponse passing as parameters the error code and an array with error and message fields
        $errorCode = 4XX; // any valid http code
        $responseArray = Array(); //Generate the response var
        $responseArray["error"] = "" // The error header, could be a code to be handled or anything else
        $responseArray["message"] = "" //The message related to the error, must be used to give information related to how to solve the call error
        echoResponse function will set up, parse and send the response in the MIMETYPE specified, in this case 'application/json'

    CALLING A METHOD
        *Call the methods of the API using the available HTTP methods, POST, GET, PUT, DELETE, etc.
        *Use an HTTP client for mobile devices or other platforms, use AJAX for web.
        
    
 */
require_once '../include/DbHandler.php';
require_once '../include/PassHash.php';
require '.././libs/Slim/Slim.php';
include '../include/Config.php';
 
\Slim\Slim::registerAutoloader();
 
$app = new \Slim\Slim();
 
// User id from db - Global Variable
$user_id = NULL;
 
/*
 * Verifying required params posted or not
 $_REQUEST! is an associative array that by default contains the contents of $_GET, $_POST and $_COOKIE.
 */
function verifyRequiredParams($required_fields) {
    $error = false;
    $error_fields = "";
    $request_params = array();
    $request_params = $_REQUEST;

    // Handling PUT request params
    if ($_SERVER['REQUEST_METHOD'] == 'PUT') {
        $app = \Slim\Slim::getInstance();
        parse_str($app->request()->getBody(), $request_params);
    }
    foreach ($required_fields as $field) {
        if (!isset($request_params[$field]) || strlen(trim($request_params[$field])) <= 0) {
            $error = true;
            $error_fields .= $field . ', ';
        }
    }
 
    if ($error) {
        // Required field(s) are missing or empty
        // echo error json and stop the app
        $response = array();
        $app = \Slim\Slim::getInstance();
        $response["error"] = true;
        $response["message"] = 'Required field(s) ' . substr($error_fields, 0, -2) . ' is missing or empty';
        echoResponse(400, $response);
        $app->stop();
    }
}
 
/**
 * Validating email address
 */
function validateEmail($email) {
    $app = \Slim\Slim::getInstance();
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $response["error"] = true;
        $response["message"] = 'Email address is not valid';
        echoResponse(400, $response);
        $app->stop();
    }
}
 
/*
  UTILITY METHODS
 */



//RESPONSE BUILDER
/*
    PARAMETERS
    @customStatus (int)= Custom status defined constant from Config.php used as an extra code apart the HTTP codes, useful for custom codes.
    @message (string)= Main message related to the custom status
    @details (string)= Details related to the result of the operation

    RETURNS
    @response (Array) = an array with the body of the response, this response is not in JSON format.
*/
function responseBuilder($customStatus,$message,$details){
    $response = Array();
    $response["status"] = $customStatus;
    $response["message"] = $message;
    $response["details"] = $details;
    return $response;
}

//RESPONSE COMMUNICATION.
/*
    PARAMETERS
    @status_code = HTTP code to be reported to the HTTP method caller.
    @response = Response array with the response body, non formated.
*/
function echoResponse($status_code, $response) {
    $app = \Slim\Slim::getInstance();
    // Http response code
    $app->status($status_code);
 
    // setting response content type to json
    $app->contentType('application/json');
 
    echo json_encode($response);
}


function authenticateIndividual(\Slim\Route $route) {
    // Getting request headers
    $headers = apache_request_headers();
    $response = array();
    $app = \Slim\Slim::getInstance();
 
    // Verifying Authorization Header
    if (isset($headers['Authorization'])) {
        $db = new DbHandler();
 
        // get the api key
        $api_key = $headers['Authorization'];
        // validating api key
        if (!$db->isValidApiKey($api_key)) {
            // api key is not present in users table
            $response["error"] = true;
            $response["message"] = "Access Denied. Invalid Api key";
            echoResponse(401, $response);
            $app->stop();
        } else {
            global $user_id;
            // get user primary key id
            $user = $db->getUserId($api_key);
            if ($user != NULL)
                $user_id = $user["id"];
        }
    } else {
        // api key is missing in header
        $response["error"] = true;
        $response["message"] = "Api key is misssing";
        echoResponse(400, $response);
        $app->stop();
    }
}

/*
    SINGLE API KEY AUTHENTICATION
        This method authenticates the API caller with a single key, used commonly for organization or development groups.
        The authentication key is mandatory and the database for api keys must be configured on Config.php
*/
function authenticateMaster(\Slim\Route $route) {
    // Getting request headers
    $headers = apache_request_headers();
    $response = array();
    $app = \Slim\Slim::getInstance();
 
    // Verifying Authorization Header
    if (isset($headers['Authorization'])) {
        $db = new DbHandler();
 
        // get the api key
        $api_key = $headers['Authorization'];
        // validating api key
        if (!$db->checkApiKey($api_key)) {
            // api key is not present in users table
            $response["error"] = true;
            $response["message"] = "Access Denied. Invalid Api key";
            echoResponse(401, $response);
            $app->stop();
        } else {
            //DO anything with an authenticated organization!
           
        }
    } else {
        // api key is missing in header
        $response["error"] = true;
        $response["message"] = "Api key is misssing";
        echoResponse(400, $response);
        $app->stop();
    }
}

 /*
        SAMPLE API ROUTING METHODS
 */
$app->get('/greeting', function() {
            $response = responseBuilder(SUCCESS,"Hello from API foundation","This will be great");
            echoResponse(HTTP_SUCCESS, $response);
        });

$app->get('/greetingVIP', 'authenticateMaster',function() {
            $response = responseBuilder(SUCCESS,"Hello from API foundation","This is a VIP greeting");
            echoResponse(HTTP_SUCCESS, $response);
        });


 /*
        API METHODS
 */

/*
        POST METHODS
*/

        $app->post('/register', function() use ($app) {
            // check for required params
            verifyRequiredParams(array('name', 'email', 'password'));
 
            $response = array();
 
            // reading post params
            $name = $app->request->post('name');
            $email = $app->request->post('email');
            $password = $app->request->post('password');
 
            // validating email address
            validateEmail($email);
 
            $db = new DbHandler();
            $res = $db->createUser($name, $email, $password);
 
            if ($res == USER_CREATED_SUCCESSFULLY) {
                $response["error"] = false;
                $response["message"] = "You are successfully registered";
                echoResponse(201, $response);
            } else if ($res == USER_CREATE_FAILED) {
                $response["error"] = true;
                $response["message"] = "Oops! An error occurred while registereing";
                echoResponse(200, $response);
            } else if ($res == USER_ALREADY_EXISTED) {
                $response["error"] = true;
                $response["message"] = "Sorry, this email already existed";
                echoResponse(200, $response);
            }
        });


        $app->post('/login', function() use ($app) {
            // check for required params
            verifyRequiredParams(array('email', 'password'));
 
            // reading post params
            $email = $app->request()->post('email');
            $password = $app->request()->post('password');
            $response = array();
 
            $db = new DbHandler();
            // check for correct email and password
            if ($db->checkLogin($email, $password)) {
                // get the user by email
                $user = $db->getUserByEmail($email);
 
                if ($user != NULL) {
                    $response["error"] = false;
                    $response['name'] = $user['name'];
                    $response['email'] = $user['email'];
                    $response['apiKey'] = $user['api_key'];
                    $response['createdAt'] = $user['created_at'];
                } else {
                    // unknown error occurred
                    $response['error'] = true;
                    $response['message'] = "An error occurred. Please try again";
                }
            } else {
                // user credentials are wrong
                $response['error'] = true;
                $response['message'] = 'Login failed. Incorrect credentials';
            }
 
            echoResponse(200, $response);
        });

        $app->post('/tasks', 'authenticateIndividual', function() use ($app) {
            // check for required params
            verifyRequiredParams(array('task'));
 
            $response = array();
            $task = $app->request->post('task');
 
            global $user_id;
            $db = new DbHandler();
 
            // creating new task
            $task_id = $db->createTask($user_id, $task);
 
            if ($task_id != NULL) {
                $response["error"] = false;
                $response["message"] = "Task created successfully";
                $response["task_id"] = $task_id;
            } else {
                $response["error"] = true;
                $response["message"] = "Failed to create task. Please try again";
            }
            echoResponse(201, $response);
        });




/*
        GET METHODS
*/
$app->get('/tasks', 'authenticateIndividual', function() {
            global $user_id;
            $response = array();
            $db = new DbHandler();
 
            // fetching all user tasks
            $result = $db->getAllUserTasks($user_id);
 
            $response["error"] = false;
            $response["tasks"] = array();
 
            // looping through result and preparing tasks array
            while ($task = $result->fetch_assoc()) {
                $tmp = array();
                $tmp["id"] = $task["id"];
                $tmp["task"] = $task["task"];
                $tmp["status"] = $task["status"];
                $tmp["createdAt"] = $task["created_at"];
                array_push($response["tasks"], $tmp);
            }
 
            echoResponse(200, $response);
        });



        $app->get('/tasks/:id', 'authenticateIndividual', function($task_id) {
            global $user_id;
            $response = array();
            $db = new DbHandler();
 
            // fetch task
            $result = $db->getTask($task_id, $user_id);
 
            if ($result != NULL) {
                $response["error"] = false;
                $response["id"] = $result["id"];
                $response["task"] = $result["task"];
                $response["status"] = $result["status"];
                $response["createdAt"] = $result["created_at"];
                echoResponse(200, $response);
            } else {
                $response["error"] = true;
                $response["message"] = "The requested resource doesn't exists";
                echoResponse(404, $response);
            }
        });


/*
        PUT METHODS
*/

$app->put('/tasks/:id', 'authenticateIndividual', function($task_id) use($app) {
            // check for required params
            verifyRequiredParams(array('task', 'status'));
 
            global $user_id;            
            $task = $app->request->put('task');
            $status = $app->request->put('status');
 
            $db = new DbHandler();
            $response = array();
 
            // updating task
            $result = $db->updateTask($user_id, $task_id, $task, $status);
            if ($result) {
                // task updated successfully
                $response["error"] = false;
                $response["message"] = "Task updated successfully";
            } else {
                // task failed to update
                $response["error"] = true;
                $response["message"] = "Task failed to update. Please try again!";
            }
            echoResponse(200, $response);
        });

/*
        DELETE METHODS
*/
        $app->delete('/tasks/:id', 'authenticateIndividual', function($task_id) use($app) {
            global $user_id;
 
            $db = new DbHandler();
            $response = array();
            $result = $db->deleteTask($user_id, $task_id);
            if ($result) {
                // task deleted successfully
                $response["error"] = false;
                $response["message"] = "Task deleted succesfully";
            } else {
                // task failed to delete
                $response["error"] = true;
                $response["message"] = "Task failed to delete. Please try again!";
            }
            echoResponse(200, $response);
        });



/*
    API CONFIGURATION END
    IMORTANT
        Use the line $app->run(); at the end of the php script to run the application.
*/
         $app->run();
?>