<?php

require_once "DBInit.php";

class GuestDB {

    public static function registerGuestReturnID($data) {
        $dbh = DBInit::getInstance();
        try {
            $stmt = $dbh ->prepare("INSERT INTO guests (username) 
            VALUES (:username)");
            $stmt -> bindValue(":username" , $data['username']);
            $stmt -> execute();
            return $dbh->lastInsertId();
        } catch ( PDOException $e ) {
            JourneyController::error500();
        }
        return null;

    }

}
