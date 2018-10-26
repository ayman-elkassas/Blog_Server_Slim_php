<?php

class DbOperations{

    private $con;

    function __construct(){
        require_once dirname(__FILE__) . '/DBConnect.php';
        $db = new DbConnect;
        $this->con = $db->connect();
    }

    public function createUser($email, $password, $name, $school){
        if(!$this->isEmailExist($email)){
            $Query = $this->con->prepare("INSERT INTO users (email, password, name, school) VALUES (?, ?, ?, ?)");
            $Query->bind_param("ssss", $email, $password, $name, $school);
            if($Query->execute()){
                return USER_CREATED;
            }else{
                return USER_FAILURE;
            }
        }
        return USER_EXIST;
    }

    public function userLogin($email,$password)
    {
        if($this->isEmailExist($email))
        {
            $hashed_password=$this->getUsersPasswordByEmail($email);
            //TODO:password_verify() compare password with hash password
            if(password_verify($password,$hashed_password))
            {
                return USER_AUTHENTICATED;
            }
            else
            {
                return USER_PASSWORD_DO_NOT_MATCH;
            }
        }
        else
        {
            return USER_NOT_FOUND;
        }
    }

    private function getUsersPasswordByEmail($email)
    {
        $Query=$this->con->prepare("Select password from users where email =?");
        $Query->bind_param('s',$email);
        $Query->execute();
        $Query->bind_result($password);
        $Query->fetch();
        return $password;

    }

    public function getUserByEmail($email)
    {
        $Query=$this->con->prepare("Select id,email,name,school from users where email =? ");
        $Query->bind_param('s',$email);
        $Query->execute();
        $Query->bind_result($id,$email,$name,$school);
        $Query->fetch();
        $user=array();
        $user["id"]=$id;
        $user["email"]=$email;
        $user["name"]=$name;
        $user["school"]=$school;

        return $user;
    }

    public function getAllusers()
    {
        $Query = $this->con->prepare("SELECT id, email, name, school FROM users;");
        $Query->execute();
        $Query->bind_result($id, $email, $name, $school);
        $users = array();
        while($Query->fetch()){
            $user = array();
            $user['id'] = $id;
            $user['email']=$email;
            $user['name'] = $name;
            $user['school'] = $school;
            array_push($users, $user);
        }
        return $users;
    }
    
    public function updateUser($email, $name, $school, $id){
        $Query = $this->con->prepare("UPDATE users SET email = ?, name = ?, school = ? WHERE id = ?");
        //TODO:bind_param() on arrange of query parameter
        $Query->bind_param("sssi", $email, $name, $school, $id);
        if($Query->execute()) return true;
        return false;
    }

    public function updatePassword($currentpassword, $newpassword, $email){
        $hashed_password = $this->getUsersPasswordByEmail($email);

        //TODO:verify if you really this user or not
        if(password_verify($currentpassword, $hashed_password)){

            $hash_password = password_hash($newpassword, PASSWORD_DEFAULT);
            $Query = $this->con->prepare("UPDATE users SET password = ? WHERE email = ?");
            $Query->bind_param("ss",$hash_password, $email);
            if($Query->execute())
                return PASSWORD_CHANGED;
            return PASSWORD_NOT_CHANGED;
        }else{
            return PASSWORD_DO_NOT_MATCH;
        }
    }

    public function deleteUser($id){
        $stmt = $this->con->prepare("DELETE FROM users WHERE id = ?");
        //i-> int s-> string
        $stmt->bind_param("i", $id);
        if($stmt->execute())
            return true;
        return false;
    }

    private function isEmailExist($email){
        $Query = $this->con->prepare("SELECT id FROM users where email=?");
        $Query->bind_param("s", $email);
        $Query->execute();
        $Query->store_result();
        return $Query->num_rows > 0;
    }
}