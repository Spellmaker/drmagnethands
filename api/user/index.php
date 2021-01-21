<?php
    include("../../_lib/#db.php");

    $conn = connect();

    $result = array();

    if (isset($_GET['name'])) {
        /* create a new user */
        $query = "INSERT INTO `user`(`name`, `created`, `secret`) VALUES ('".esc($conn, $_GET['name'])."', 'a', 'a');";
        $rs = mysqli_query($conn, $query);
        $id = mysqli_insert_id($conn);
        setcookie("magnet_user_name", $_GET['name'], time() + (3600 * 5), "/");
        setcookie("magnet_user_id", $id, time() + (3600 * 5), "/");

        $result["name"] = $_GET['name'];
        $result["id"] = $id;
        $result["loggedin"] = true;
    } else if (isset($_COOKIE['magnet_user_id'])) {
        /* display current user */
        $rs = mysqli_query($conn, "SELECT * FROM user WHERE id = \"".esc($conn, $_COOKIE['magnet_user_id'])."\"");
        if (mysqli_num_rows($rs) > 0) {
            $row = mysqli_fetch_assoc($rs);
            $result["name"] = $row['name'];
            $result["id"] = $row['id'];
            $result["loggedin"] = true;
        } else {
            $result["loggedin"] = false;
        }
    } else {
        /* display not logged in */
        $result["loggedin"] = false;
    }

    header("content-type: application/json");
    echo json_encode($result);

?>