<?php

require_once "DBInit.php";

class UserDB {

    // Returns true if a valid email is found
    public static function getUserData($email) {
        $dbh = DBInit::getInstance();

        try {
            $stmt = $dbh ->prepare("SELECT * FROM users WHERE email = :email");
            $stmt -> bindValue(":email" , $email);
            $stmt -> execute();
        } catch ( PDOException $e ) {
            JourneyController::error500();
        }

        $user = $stmt->fetch(0);
        if ($user) {
            return $user;
        }
        return false;
        
    }


    public static function userPreviouslyLoggedIn($email){
        $dbh = DBInit::getInstance();
        try {
            $stmt = $dbh ->prepare("SELECT * FROM users WHERE email = :email");
            $stmt -> bindValue(":email" , $email);
            $stmt -> execute();
            if($stmt->fetchColumn(0) > 0)
                return true;
        } catch ( PDOException $e ) {
            JourneyController::error500();
        }
        return false;
    }

    public static function signUpUserWithERPData($signup_data){
        $dbh = DBInit::getInstance();
        try {
            $stmt = $dbh ->prepare("INSERT INTO users (email, username, fullName) 
            VALUES (:email, :username, :fullName)");
            $stmt -> bindValue(":email" , $signup_data['email']);
            $stmt -> bindValue(":username" , $signup_data['username']);
            $stmt -> bindValue(":fullName" , $signup_data['fullName']);
            $stmt -> execute();
        } catch ( PDOException $e ) {
            JourneyController::error500();
        }
    }
}
