<?php

require_once("model/JourneyDB.php");
require_once("model/CommentDB.php");
require_once("model/GuestDB.php");
require_once("ViewHelper.php");
require_once("static/secrets.php");
require_once("static/config.php");

define("PUBLIC_IMAGES_URL", rtrim($_SERVER["SCRIPT_NAME"], "index.php") . "public/images/");

class JourneyController {

    public static function index() {
        if (isset($_GET["id"])) {
            $journey = JourneyDB::get($_GET["id"]);
            if ($journey === false)
                JourneyController::error404();
            else{
                $errors["comment"] = "";
                $errors["username"] = "";
                $comments = CommentDB::getAllForJourney($_GET["id"]);
                ViewHelper::render("view/viewjourney.php", ["journey" => $journey, "errors" => $errors, "comments" => $comments]);
            }
        } else {
            ViewHelper::render("view/viewjourneys.php", ["journeys" => JourneyDB::getAll()]);
        }
    }

    public static function search() {
        ViewHelper::render("view/searchjourneys.php");
    }

    public static function searchApi() {
        if (isset($_GET["query"]) && !empty($_GET["query"])) {
            $hits = JourneyDB::search($_GET["query"]);
        } else {
            $hits = [];
        }

        header('Content-type: application/json; charset=utf-8');
        echo json_encode($hits);
    }

    // Function can be called without providing arguments. In such case,
    // $data and $errors paramateres are initialized as empty arrays
    public static function showAddForm($data = [], $errors = []) {
        if(!isset($_SESSION['user_id'])){
            JourneyController::error401();
        }
        else{
            if (empty($errors)) {
                // Or rather empty values
                $errors = [
                    "title" => "",
                    "description" => "",
                    "picture_url" => "",
                    "upload" => ""
                ];
            }

            $vars = ["journey" => $data, "errors" => $errors];
            ViewHelper::render("view/addjourney.php", $vars);
        }
    }

    // Adding post
    public static function add() {
        if(!isset($_SESSION['user_id'])){
            JourneyController::error401();
        }
        else{
            $rules = [
                // we convert HTML special characters
                "title" => FILTER_DEFAULT,
                "description" => FILTER_DEFAULT,
                "picture_url" => FILTER_VALIDATE_URL
            ];
            // Apply filter to all POST variables; from here onwards we never
            // access $_POST directly, but use the $data array
            $data = filter_input_array(INPUT_POST, $rules);

            $errors["title"] = empty($data["title"]) ? "Provide the journey title." : "";
            $errors["description"] = empty($data["description"]) ? "Provide the journey description." : "";
            $errors["upload"] = "";
            $errors["picture_url"] = "";
            
            $pic_data = $_FILES['picture_data'];
            if($pic_data['name']==='' && $_POST["picture_url"]!==''){
                $errors["picture_url"] = $data["picture_url"] === false ? "Invalid image URL." : "";
            }

            $isDataValid = true;
            foreach ($errors as $error) {
                $isDataValid = $isDataValid && empty($error);
            }
            
            if ($isDataValid) {

                $pic_name = basename($pic_data["name"]);
                if ($pic_name!==''){
                    $tmp_path = $pic_data["tmp_name"];

                    $unique_name = uniqid() . $pic_name;
                    $db_path = PUBLIC_IMAGES_URL . $unique_name;
                    $disk_path = "public/images/" . $unique_name;

                    if(move_uploaded_file($tmp_path, $disk_path)){
                    JourneyDB::insert($data["title"], $data["description"], 
                        $db_path, $_SESSION['user_id']);
                    ViewHelper::redirect(BASE_URL . "journey");
                    }
                    else {
                        $errors["upload"] = "Error uploading image.";
                        self::showAddForm($data, $errors);
                    }
                }
                else{
                    JourneyDB::insert($data["title"], $data["description"], 
                        $data["picture_url"], $_SESSION['user_id']);
                    ViewHelper::redirect(BASE_URL . "journey");
                }
            } else {
                self::showAddForm($data, $errors);
            }
        }
    }

    public static function showEditForm($journey = [], $errors = []) {
        if (!isset($_GET["id"])){
            JourneyController::error403();
            exit;
        }
        $journey = JourneyDB::get($_GET["id"]);
        if ($journey === false)
            JourneyController::error404();
        else if (isset($_SESSION['user_id']) && $journey['userid']===$_SESSION['user_id']){
            if (empty($errors)) {
                $errors = [
                    "title" => "",
                    "description" => "",
                    "picture_url" => "",
                    "upload" => ""
                ];
            }
            ViewHelper::render("view/editjourney.php", ["journey" => $journey, "errors" => $errors]);
        }
        else{
            JourneyController::error403();
        }

    }    

    // Editing post
    public static function edit() {
        $rules = [
            "title" => FILTER_DEFAULT,
            "description" => FILTER_DEFAULT,
            "picture_url" => [
                "filter" => FILTER_CALLBACK,
                "options" => function ($value) { 
                    if(filter_var($value, FILTER_VALIDATE_URL) || str_starts_with($value, PUBLIC_IMAGES_URL)){
                        return $value;
                    }
                    return false;
                 }
            ],
            "id" => [
                "filter" => FILTER_CALLBACK,
                "options" => function ($value) { return (is_numeric($value) && $value > 0) ? floatval($value) : false; }
            ]
        ];
        $data = filter_input_array(INPUT_POST, $rules);

        $errors["title"] = empty($data["title"]) ? "Provide the journey title." : "";
        $errors["description"] = empty($data["description"]) ? "Provide the journey description." : "";
        $errors["upload"] = "";
        $errors["picture_url"] = "";
        $errors["id"] = $data["id"] === false ? "ID should be positive." : "";

        if(!$data['id']){
            self::showAddForm($data, $errors);
            exit;
        }

        $journey = JourneyDB::get($data["id"]);
        if(!isset($_SESSION['user_id'])){
            JourneyController::error401();
        }
        else if ($journey['userid']!==$_SESSION['user_id']){
            JourneyController::error403();
        }
        else{
            $pic_data = $_FILES['picture_data'];
            if($pic_data['name']==='' && $_POST["picture_url"]!==''){
                $errors["picture_url"] = $data["picture_url"] === false ? "Invalid image URL." : "";
            }

            $isDataValid = true;
            foreach ($errors as $error) {
                $isDataValid = $isDataValid && empty($error);
            }
            
            if ($isDataValid) {


                $pic_name = basename($pic_data["name"]);
                if ($pic_name!==''){
                    $tmp_path = $pic_data["tmp_name"];

                    $unique_name = uniqid() . $pic_name;
                    $db_path = PUBLIC_IMAGES_URL . $unique_name;
                    $disk_path = "public/images/" . $unique_name;

                    if(move_uploaded_file($tmp_path, $disk_path)){
                        JourneyDB::update($data["title"], $data["description"], 
                        $db_path, $data["id"]);
                        ViewHelper::redirect(BASE_URL . "journey?id=" . $data["id"]);
        
                    } else {
                        $errors["upload"] = "Error uploading image.";
                        self::showAddForm($data, $errors);
                    }
                }
                else{
                    JourneyDB::update($data["title"], $data["description"], 
                        $data["picture_url"], $data["id"]);
                    ViewHelper::redirect(BASE_URL . "journey?id=" . $data["id"]);
                }
            } else {
                self::showAddForm($data, $errors);
            }
        }
        
    }

    public static function delete() {
        $rules = [
            "id" => [
                "filter" => FILTER_VALIDATE_INT,
                "options" => ["min_range" => 1]
            ]
        ];
        $data = filter_input_array(INPUT_GET, $rules);
        
        if(!$data['id']){
            JourneyController::error403();
            exit;
        }

        $errors["id"] = $data["id"] === null ? "Invalid ID" : "";

        $journey = JourneyDB::get($data["id"]);
        if(!isset($_SESSION['user_id'])){
            JourneyController::error401();
        }
        else if ($journey === false)
            JourneyController::error404();
        else if ($journey['userid']!==$_SESSION['user_id']){
            JourneyController::error403();
        }
        else{
            $isDataValid = true;
            foreach ($errors as $error) {
                $isDataValid = $isDataValid && empty($error);
            }

            if ($isDataValid) {
                CommentDB::deleteAll($data["id"]);
                JourneyDB::delete($data["id"]);
                $url = BASE_URL . "journey";
            } else {
                if ($data["id"] !== null) {
                    $url = BASE_URL . "journey/edit?id=" . $data["id"];
                } else {
                    $url = BASE_URL . "journey";
                }
            }
            ViewHelper::redirect($url);
        }
        
    }

    public static function comment($authStatus) {
        $data = $_POST;
        if(!isset($data) || !$data['id']){
            JourneyController::error403();
            exit;
        }

        $errors["id"] = $data["id"] === null ? "Invalid ID" : "";
        $errors["comment"] = empty($data["comment"]) ? "Comment cannot be empty." : "";
        $errors["username"] = "";

        $journey = JourneyDB::get($data["id"]);
        if ($journey === false){
            JourneyController::error404();
            exit;
        }
        if($authStatus == AuthStatus::AUTHENTICATED){
             self::commentAuthenticated($data, $errors);
        }
        else{
            $errors = self::commentUnauthenticated($data, $errors);
        }
        foreach ($errors as $error) {
            echo $error;
        }
        ViewHelper::redirect(BASE_URL . "journey/?id=" . $data["id"]);        
    }

    public static function commentAuthenticated($data, $errors) {
        // Somebody could still try and make GET request to comment/addAuth, so here we actually check if they are authorized
        if(!isset($_SESSION['user_id'])){
            JourneyController::error401();
            exit;
        }
        if (self::checkDataValid($errors))
            CommentDB::insert($data["id"], AuthStatus::AUTHENTICATED->value, $_SESSION["user_id"], $data["comment"]);
            
        return $errors;
    }

    public static function commentUnauthenticated($data, $errors) {
        $errors["username"] = empty($data["username"]) ? "Username cannot be empty." : "";
        if (self::checkDataValid($errors)){
            $guest_id = GuestDB::registerGuestReturnID($data);
            if(!$guest_id){
                $errors["username"] = "Error posting as guest user.";
            }
            else{
                CommentDB::insert($data["id"], AuthStatus::UNAUTHENTICATED->value, $guest_id, $data["comment"]);
            }
        }
            
        return $errors;
    }

    public static function checkDataValid($errors) {
        $isDataValid = true;
        foreach ($errors as $error) {
            $isDataValid = $isDataValid && empty($error);
        }
        return $isDataValid;
    }


    public static function deleteComment() {
        $rules = [
            "id" => [
                "filter" => FILTER_VALIDATE_INT,
                "options" => ["min_range" => 1]
            ]
        ];
        $data = filter_input_array(INPUT_POST, $rules);
        
        if(!isset($data) || !$data['id']){
            JourneyController::error403();
            exit;
        }

        $errors["id"] = $data["id"] === null ? "Invalid ID" : "";

        $comment = CommentDB::get($data["id"]);
        if(!isset($_SESSION['user_id'])){
            JourneyController::error401();
        }
        
        else if ($comment === false)
            JourneyController::error404();
        else if ($comment['authortype'] !== AuthStatus::AUTHENTICATED->value || $comment['authorid']!==$_SESSION['user_id']){
            JourneyController::error403();
        }
        else{
            $isDataValid = true;
            foreach ($errors as $error) {
                $isDataValid = $isDataValid && empty($error);
            }

            if ($isDataValid) {
                CommentDB::delete($data["id"]);
            }
            ViewHelper::redirect(BASE_URL . "journey/?id=".$comment['journeyid'],$errors);
        }
        
    }

    public static function about() {
        ViewHelper::render("view/about.php");
    }

    public static function error401() {
        ViewHelper::render("view/401.php", [], 401);
    }

    public static function error403() {
        ViewHelper::render("view/403.php", [], 403);
    }

    public static function error404() {
        ViewHelper::render("view/404.php", [], 404);
    }

    public static function error500() {
        ViewHelper::render("view/500.php", [], 500);
    }
}
