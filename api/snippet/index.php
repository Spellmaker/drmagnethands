<?php
    include("../../_lib/#db.php");

    $conn = connect();

    $result = array();

    $user_id = validateUser($conn);
    $room = validateRoom($conn);
    validateUserInRoom($conn, $room, $user_id);

    /*
    operations:
    - draw any number of snippets from the pool
    - add a snippet to the pool
    - get snippets added to the pool this turn by current user
    - show a snippet
    - use a snippet on another snippet
    - view current snippets in the room

    parameter for all things: room id
    */

    if (isset($_GET['action'])) {

    } else {

    }

    header("content-type: application/json");
    echo json_encode($result);
?>