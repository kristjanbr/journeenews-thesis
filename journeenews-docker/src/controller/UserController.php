<?php

require_once("model/UserDB.php");
require_once("ViewHelper.php");
require_once("static/secrets.php");

session_start();

class UserController {

    public static function showLoginForm() {
       ViewHelper::render("view/login.php",[
        "errorMessage" => ""]);
    }

    public static function login() {
        $rules = [
            "email" => FILTER_SANITIZE_EMAIL,
            "password" => FILTER_DEFAULT,
        ];
        $data = filter_input_array(INPUT_POST, $rules);
        
        $user_verified = self::verifiedByERP($data["email"], $data["password"]);
        if ($user_verified) {
            // Check if user is in DB by comparing email - nextERP uses that as "ID"
            // If not, register them with data from ERP
            if(!UserDB::userPreviouslyLoggedIn($data["email"])){
                self::registerUserWithERPData($data["email"]);
            }

            $user_data = UserDB::getUserData($data["email"]);
            $_SESSION['user_id'] = $user_data['id'];
            $_SESSION['user_username'] = $user_data['username'];
            $_SESSION['user_name'] = $user_data['fullName'];
            ViewHelper::redirect(BASE_URL . "journey");

        } else {
             ViewHelper::render("view/login.php", [
                 "errorMessage" => "Invalid email or password."]);
        }
    }

    // The user trying to log in is verified by NextERP, there is no need to check for duplicates...
    // as that is ERP's job already.
    public static function verifiedByERP($email, $password){
        $url = ERP_URL . "/api/method/login";
        $post_data = json_encode(["usr" => $email, "pwd" => $password]);

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $post_data,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_RETURNTRANSFER => true
        ]);
        $response = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($code === 200) {
            $data = json_decode($response, true);
            return isset($data['message']) && str_contains($data['message'], 'Logged In');
        }
        return false;
    }

    public static function registerUserWithERPData($email){
        $url = ERP_URL . "/api/resource/User/" . urlencode($email);
        $auth_token = ERP_API_KEY . ":" . ERP_API_SECRET;
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_HTTPGET => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: token ' . $auth_token
            ],
            CURLOPT_RETURNTRANSFER => true
        ]);
        $response = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($code === 200) {
            $data = json_decode($response, true);
            if (isset($data['data'])) {
                $user_data = $data['data'];
                $signup_data = [
                    "email" => $user_data['email'],
                    "username" => $user_data['username'],
                    "fullName" => $user_data['full_name'],
                ];
                UserDB::signUpUserWithERPData($signup_data);
            } else {
                JourneyController::error500();
            }
        }
        // Something went wrong with ERP system/request, any 400 / 500 error by ERP I treat as 500 here
        else if ($code >=400 && $code <600) {
            JourneyController::error500();
        }
    }

     public static function logout() {
        session_destroy();
        ViewHelper::redirect(BASE_URL . "journey");
    }
    

}