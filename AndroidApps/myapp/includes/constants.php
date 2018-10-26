<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 9/18/18
 * Time: 5:49 PM
 */

//TODO:INITIATE DB connection
define("DB_HOST",'localhost');
define("DB_USER",'root');
define("DB_PASSWORD",'');
define("DB_NAME","my_app");

//TODO:Create operation
define("USER_CREATED",101);
define("USER_EXIST",102);
define("USER_FAILURE",103);

//TODO:Read operations
define("USER_AUTHENTICATED",201);
define("USER_NOT_FOUND",202);
define("USER_PASSWORD_DO_NOT_MATCH",203);

//TODO:Update operation
define('PASSWORD_CHANGED', 301);
define('PASSWORD_DO_NOT_MATCH', 302);
define('PASSWORD_NOT_CHANGED', 303);

