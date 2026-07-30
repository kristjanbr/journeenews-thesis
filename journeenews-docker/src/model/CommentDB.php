<?php

require_once "DBInit.php";
require_once("static/secrets.php");

class CommentDB {

    public static function insert($journeyid, $authortype, $authorid, $comment ) {
        $db = DBInit::getInstance();

        $statement = $db->prepare("INSERT INTO comments (journeyid, authortype, authorid, comment) 
            VALUES (:journeyid, :authortype, :authorid, :comment)");
        $statement->bindParam(":journeyid", $journeyid);
        $statement->bindParam(":authortype", $authortype);
        $statement->bindParam(":authorid", $authorid);
        $statement->bindParam(":comment", $comment);

        $statement->execute();
    }

    public static function get($id) {
        $db = DBInit::getInstance();

        $statement = $db->prepare("SELECT id, comment, commenttimestamp, journeyid, authortype, authorid
            FROM comments 
            WHERE id = :id");
        $statement->bindParam(":id", $id, PDO::PARAM_INT);
        $statement->execute();

        $journey = $statement->fetch();

        return $journey;
    }

    public static function getAllForJourney($journeyid) {
        $db = DBInit::getInstance();
        
        $statement = $db->prepare("SELECT 
            CASE 
                WHEN comments.authortype = :authenticated THEN users.username 
                WHEN comments.authortype = :unauthenticated THEN guests.username 
            END AS authorname,
            authortype, comment, commenttimestamp, comments.authorid, comments.id
            FROM comments 
            LEFT JOIN users ON comments.authortype = :authenticated AND comments.authorid = users.id
            LEFT JOIN guests ON comments.authortype = :unauthenticated AND comments.authorid = guests.id
            WHERE comments.journeyid = :journeyid
            ORDER BY commenttimestamp DESC");
        $statement->bindParam(":journeyid", $journeyid, PDO::PARAM_INT);
        $statement->bindValue(":authenticated", AuthStatus::AUTHENTICATED->value);
        $statement->bindValue(":unauthenticated", AuthStatus::UNAUTHENTICATED->value);
        $statement->execute();

        $journey = $statement->fetchAll();

        return $journey;
    }

    public static function deleteAll($journeyid) {
        $db = DBInit::getInstance();

        $statement = $db->prepare("DELETE FROM comments WHERE journeyid = :journeyid");
        $statement->bindParam(":journeyid", $journeyid, PDO::PARAM_INT);
        $statement->execute();
    }  

    public static function delete($id) {
        $db = DBInit::getInstance();

        $statement = $db->prepare("DELETE FROM comments WHERE id = :id");
        $statement->bindParam(":id", $id, PDO::PARAM_INT);
        $statement->execute();
    }  

}
