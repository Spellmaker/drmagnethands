<?php
    include("../../_lib/#db.php");

    $conn = connect();

    $user_id = validateUser($conn);

    /* 
    operations:
    - create a room
    - join a room
    - view room details (users, current round)
    - start a new round (only owner)
    */

    $result = array();

    if (isset($_GET['action'])) {
        if ($_GET['action'] == "create") {
            /* create a new room */
            mysqli_query($conn, "INSERT INTO room(roomcode, roundnumber, owner) VALUES ('unused', '0', ".$user_id.")");
            $result["roomid"] = mysqli_insert_id($conn);
        } else if ($_GET['action'] == "join") {
            /* join a room */
            $room = validateRoom($conn);
            $query = "INSERT INTO user_to_room(user, room) VALUES(".$user_id.", ".$room.")";
            if (mysqli_query($conn, $query) == false) {
                $result["error"] = mysqli_error($conn);
            } else {
                $result["message"] = "joined room with id ".$room;
            }
        } else if ($_GET['action'] == "round") {
            /* start a round in a room */
            $room = validateRoom($conn);
            validateUserInRoom($conn, $room, $user_id);
            validateUserIsOwner($conn, $room, $user_id);
            mysqli_query($conn, "UPDATE room SET roundnumber = roundnumber + 1 WHERE id = ".$room."");
            $result["message"] = "started a new round";
        } else if ($_GET['action'] == "kick") {
            /* kick a user from a room */
            if (!isset($_GET['user'])) {
                $result["error"] = "user must be set";
            } else {
                $room = validateRoom($conn);
                validateUserInRoom($conn, $room, $user_id);
                validateUserInRoom($conn, $room, $_GET['user']);
                validateUserIsOwner($conn, $room, $user_id);
                $query = "DELETE FROM user_to_room WHERE room=\"".$room."\" AND user=\"".esc($conn, $_GET['user'])."\"";
                if (mysqli_query($conn, $query)) {
                    $result['message'] = "kicked user ".$_GET['user']." from room ".$room;
                } else {
                    $result['error'] = "query failed";
                }
            }
        } else {
            /* error invalid action*/
            $result["error"] = "unknown action type";
        }
    } else {
        /* show room details */
        $room = validateRoom($conn);
        validateUserInRoom($conn, $room, $user_id);
        $rs1 = mysqli_query($conn, "SELECT * FROM room WHERE id=\"".$room."\"");
        $array = mysqli_fetch_assoc($rs1);
        $result["id"] = $array["id"];
        $result["round"] = $array["roundnumber"];
        
        $rs2 = mysqli_query($conn, "SELECT user.id, user.name FROM user JOIN room ON user.id = room.owner AND room.id = \"".$room."\"");
        $result["owner"] = mysqli_fetch_assoc($rs2);

        $result["users"] = array();
        $rs3 = mysqli_query($conn, "SELECT user.id, user.name FROM user JOIN user_to_room ON user.id = user_to_room.user WHERE user_to_room.room =\"".$room."\"");
        while($row = mysqli_fetch_assoc($rs3)) {
            array_push($result["users"], $row);
        }

        $result["shown"] = array();
        $rs4 = mysqli_query($conn, "SELECT snippet.id, snippet.content FROM snippet JOIN shown_snippets ON snippet.id = shown_snippets.snippet WHERE shown_snippets.room = \"".$room."\"");
        while($row = mysqli_fetch_assoc($rs4)) {
            array_push($result["shown"], $row);
        }
    }

    header("content-type: application/json");
    echo json_encode($result);
?>