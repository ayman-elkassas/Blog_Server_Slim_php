<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 9/18/18
 * Time: 5:52 PM
 */

class DBConnect{
    private $con;

    function connect()
    {
        include_once dirname(__FILE__).'/constants.php';
        $this->con=new mysqli(DB_HOST,DB_USER,DB_PASSWORD,DB_NAME);

        if(mysqli_connect_errno())
        {
            echo "Failed to connect : ".mysqli_connect_error();
            return null;
        }

        return $this->con;
    }
}