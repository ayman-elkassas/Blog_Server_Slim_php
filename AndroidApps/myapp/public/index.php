<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

require '../vendor/autoload.php';

//TODO:INCLUDES CONNECTION DB
require '../includes/DBConnect.php';
//TODO:OPERATIONS
require '../includes/DbOperations.php';

//TODO:instantiate the App object
$app = new \Slim\App([
    'settings'=>[
        'displayErrorDetails'=>true
    ]
]);

//TODO:FOR AUTHORIZED YOUR APIs
$app->add(new Tuupola\Middleware\HttpBasicAuthentication([
    "secure"=>false,
    "users" => [
        "ayman" => "123456"
    ]
]));

//TODO:1-INSERT NEW USER
$app->post('/createuser',function (Request $request,Response $response){

    if(!haveEmptyParameters(array('email','password','name','school'),$request,$response))
    {
        //TODO:TO PREPARE REQUEST AND DATA INSIDE API SHOULD GET BODY OF REQUEST
        $request_data=$request->getParsedBody();

        //TODO:PREPARE PARAMETERS REQUEST
        $email=$request_data['email'];
        $password=$request_data['password'];
        $name=$request_data['name'];
        $school=$request_data['school'];

        $hash_pass=password_hash($password,PASSWORD_DEFAULT);

        //TODO:PREPARE DbOperation OBJECT TO CALL METHOD
        $db=new DbOperations();
        $result=$db->createUser($email,$hash_pass,$name,$school);

        $message = array();
        //TODO:PREPARE RESPONSE OBJECT
        if($result == USER_CREATED){
            //todo:PREPARE BODY MESSAGE
            $message['error'] = false;
            $message['message'] = 'User created successfully';

            //todo:WRITE IN BODY OF RESPONSE and determine content type json
            //todo:and status value 201 mean user created successfully
            $response->write(json_encode($message));
            return $response
                ->withHeader('Content-type', 'application/json')
                ->withStatus(201);

        }else if($result == USER_FAILURE){
            $message['error'] = true;
            $message['message'] = 'Some error occurred';

            $response->write(json_encode($message));

            return $response
                ->withHeader('Content-type', 'application/json')
                ->withStatus(422);

        }else if($result == USER_EXIST){
            $message['error'] = true;
            $message['message'] = 'User Already Exists';

            $response->write(json_encode($message));
            return $response
                ->withHeader('Content-type', 'application/json')
                ->withStatus(422);
        }
    }
    return $response
        ->withHeader('Content-type', 'application/json')
        ->withStatus(422);
});

//TODO:2-Login Api
$app->post('/userlogin',function (Request $request,Response $response){

    if(!haveEmptyParameters(array('email','password'),$request,$response)) {
        //TODO:TO PREPARE REQUEST AND DATA INSIDE API SHOULD GET BODY OF REQUEST
        $request_data = $request->getParsedBody();

        //TODO:PREPARE DATA
        $email = $request_data['email'];
        $password = $request_data['password'];

        $db = new DbOperations();
        $result = $db->userLogin($email, $password);

        //PREPARE RESPONSE JSON
        $response_data = array();

        if ($result == USER_AUTHENTICATED) {

            $user = $db->getUserByEmail($email);

            //DESIGN RESPONSE
            $response_data['error']=false;
            $response_data['message'] = 'Login Successful';
            $response_data['user']=$user;

            $response->write(json_encode($response_data));
            return $response
                ->withHeader('Content-type', 'application/json')
                ->withStatus(200);
        }else if($result == USER_NOT_FOUND){
            $response_data['error']=true;
            $response_data['message'] = 'User not exist';
            $response->write(json_encode($response_data));
            return $response
                ->withHeader('Content-type', 'application/json')
                ->withStatus(200);
        }else if($result == USER_PASSWORD_DO_NOT_MATCH){
            $response_data['error']=true;
            $response_data['message'] = 'Invalid credential';
            $response->write(json_encode($response_data));

            return $response
                ->withHeader('Content-type', 'application/json')
                ->withStatus(200);
        }
    }

    return $response
        ->withHeader('Content-type', 'application/json')
        ->withStatus(422);
});

//TODO:3-Get all users
$app->get('/allusers',function (Request $request,Response $response){
    $db=new DbOperations();
    $users=$db->getAllusers();

//    echo $users[0]['name'];

    $response_data=array();

    $response_data["error"]=false;
    $response_data["users"]=$users;

    $response->write(json_encode($response_data));
    return $response
        ->withHeader('content-type','application/json')
        ->withStatus(200);


});

//TODO:4-Update user (with specific id) all http operations can include $args expect post
$app->put('/updateuser/{id}', function(Request $request, Response $response, array $args){
    $id = $args['id'];

    if(!haveEmptyParameters(array('email','name','school'),$request, $response)){

        $request_data = $request->getParsedBody();

        $email = $request_data['email'];
        $name = $request_data['name'];
        $school = $request_data['school'];

        $response_data = array();
        $db = new DbOperations;
        if($db->updateUser($email, $name, $school, $id)){

            $response_data['error'] = false;
            $response_data['message'] = 'User Updated Successfully';

            $user = $db->getUserByEmail($email);
            $response_data['user'] = $user;

            $response->write(json_encode($response_data));

            return $response
                ->withHeader('Content-type', 'application/json')
                ->withStatus(200);

        }else{
            $response_data = array();

            $response_data['error'] = true;
            $response_data['message'] = 'Please try again later';

            $user = $db->getUserByEmail($email);
            $response_data['user'] = $user;

            $response->write(json_encode($response_data));

            return $response
                ->withHeader('Content-type', 'application/json')
                ->withStatus(200);

        }
    }

    return $response
        ->withHeader('Content-type', 'application/json')
        ->withStatus(200);
});

//TODO:5-Update password
$app->put('/updatepassword', function(Request $request, Response $response){
    if(!haveEmptyParameters(array('currentpassword', 'newpassword', 'email'),$request, $response)){

        $request_data = $request->getParsedBody();

        $currentpassword = $request_data['currentpassword'];
        $newpassword = $request_data['newpassword'];
        $email = $request_data['email'];

        $db = new DbOperations;
        $result = $db->updatePassword($currentpassword, $newpassword, $email);

        if($result == PASSWORD_CHANGED){
            $response_data = array();

            $response_data['error'] = false;
            $response_data['message'] = 'Password Changed Done...';

            $user = $db->getUserByEmail($email);
            $response_data['user'] = $user;

            $response->write(json_encode($response_data));

            return $response->withHeader('Content-type', 'application/json')
                ->withStatus(200);

        }else if($result == PASSWORD_DO_NOT_MATCH){
            $response_data = array();

            $response_data['error'] = true;
            $response_data['message'] = 'You have given wrong password';

            $response->write(json_encode($response_data));

            return $response->withHeader('Content-type', 'application/json')
                ->withStatus(200);
        }else if($result == PASSWORD_NOT_CHANGED){
            $response_data = array();

            $response_data['error'] = true;
            $response_data['message'] = 'Some error occurred';

            $response->write(json_encode($response_data));

            return $response->withHeader('Content-type', 'application/json')
                ->withStatus(200);
        }
    }
    return $response
        ->withHeader('Content-type', 'application/json')
        ->withStatus(422);
});

//TODO:6-Delete user by id
$app->delete('/deleteuser/{id}', function(Request $request, Response $response, array $args){
    $id = $args['id'];

    $db = new DbOperations;

    $response_data = array();

    if($db->deleteUser($id)){
        $response_data['error'] = false;
        $response_data['message'] = 'User has been deleted';
    }else{
        $response_data['error'] = true;
        $response_data['message'] = 'Plase try again later';
    }
    $response->write(json_encode($response_data));
    return $response
        ->withHeader('Content-type', 'application/json')
        ->withStatus(200);
});

function haveEmptyParameters($required_params,$request,$response)
{
    $error=false;
    $error_params='';

    $request_params=$request->getParsedBody();

    foreach ($required_params as $param)
    {
        if(!isset($request_params[$param]) || strlen($request_params[$param])<=0)
        {
            $error=true;
            $error_params.=$param.', ';
        }
    }

    if($error)
    {
        $error_details=array();

        $error_details['error']=true;
        $error_details['message']="Required parameters "
            .substr($error_params,0,-2).' are missing or empty';
        $response->write(json_encode($error_details));
    }

    return $error;
}

$app->run();