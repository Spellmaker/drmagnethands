<?php

function connect(){
	$user_name = "root";
    $password = "";
    $database = "drmagnethands";
    $server = "localhost";
    $mysqli = mysqli_connect($server, $user_name, $password, $database);

    // Check connection
    if (mysqli_connect_errno()) {
        echo "Failed to connect to MySQL: " . mysqli_connect_error();
        exit();
    }

	return $mysqli;
}

function esc($conn, $s){
    return mysqli_escape_string($conn, htmlentities($s));
}

function validateUser($conn) {
    if (isset($_COOKIE["magnet_user_id"])) {
        $query = "SELECT name FROM user WHERE id = \"".esc($conn, $_COOKIE["magnet_user_id"])."\"";
        if (mysqli_num_rows(mysqli_query($conn, $query)) > 0) {
            return esc($conn, $_COOKIE["magnet_user_id"]);
        }
    }
    header("Content-Type: application/json");
    $result = array();
    $result["error"] = "invalid user";
    echo json_encode($result);
    exit();
}

function validateRoom($conn) {
    if (isset($_GET["room"])) {
        if (mysqli_num_rows(mysqli_query($conn, "SELECT * FROM room WHERE id=".esc($conn,$_GET["room"]).";")) > 0) {
            return esc($conn,$_GET["room"]);
        } else {
            header("Content-Type: application/json");
            $result = array();
            $result["error"] = "invalid room id";
            echo json_encode($result);
            exit();
        }
    } else {
        header("Content-Type: application/json");
        $result = array();
        $result["error"] = "invalid room id";
        echo json_encode($result);
        exit();
    }
}

function validateUserInRoom($conn, $room, $user) {
    if (mysqli_num_rows(mysqli_query($conn, "SELECT * FROM user_to_room WHERE user=\"".$user."\" AND room=\"".$room."\";")) > 0) {
        return true;
    } else {
        header("Content-Type: application/json");
        $result = array();
        $result["error"] = "user is not in room";
        echo json_encode($result);
        exit();
    }
}

function validateUserIsOwner($conn, $room, $user) {
    if (mysqli_num_rows(mysqli_query($conn, "SELECT * FROM user_to_room JOIN room ON user_to_room.room = room.id WHERE user=\"".$user."\" AND room=\"".$room."\" AND owner=\"".$user."\";")) > 0) {
        return true;
    } else {
        header("Content-Type: application/json");
        $result = array();
        $result["error"] = "user is not the owner";
        echo json_encode($result);
        exit();
    }
}

?>