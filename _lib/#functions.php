<?

function esc($s){
    return mysql_escape_string(htmlentities($s));
}

function connect(){
	$user_name = "web662";
    $password = "wp==db12serv";
    $database = "usr_web662_1";
    $server = "localhost";
    $db_handle = mysql_connect($server, $user_name, $password);
	$db_found = mysql_select_db($database, $db_handle);
	$ret[0] = $db_handle;
	$ret[1] = $db_found;
	return $ret;
}

function loggedin(){
    session_start();
    return (isset($_SESSION['name']) && ($_SESSION['name'] != false));
}

function login($user, $pass){
    $user = esc($user);
    $pass = esc($pass);
    $db = connect();
    $SQL = "SELECT id, name, password FROM users WHERE name = '".$user."'";
    $res = mysql_query($SQL) or die("A MySQL error has occurred.<br />Your Query: " . $SQL . "<br /> Error: (" . mysql_errno() . ") " . mysql_error());
    $db_field = mysql_fetch_assoc($res);
    if($db_field != false){
        if($db_field['name'] == $user && $db_field['password'] == md5($pass)){
            mysql_close($ret[0]);
            $_SESSION['name'] = $user;
            $_SESSION['id'] = $db_field['id'];
            return true;
        }
    }
    mysql_close($ret[0]);
    return false;
}

function logout(){
    $_SESSION['name'] = false;
}

function hasright($user, $right){
    $user = esc($user);
    $right = esc($right);
    $db = connect();
    $SQL = "
SELECT users.name, rights.name
FROM users
 JOIN groups ON users.group = groups.id
 JOIN grouprights ON grouprights.groupid = groups.id
 JOIN rights ON grouprights.rightid = rights.id
WHERE users.name = '".$user."' AND rights.name = '".$right."'";
    
    
    $res = mysql_query($SQL) or die("A MySQL error has occurred.<br />Your Query: " . $SQL . "<br /> Error: (" . mysql_errno() . ") " . mysql_error());
    $db_field = mysql_fetch_assoc($res);
    $erg = ($db_field != false);
    mysql_close($db[0]);
    return $erg;
}

function canUpload($user){
    return hasright($user, "upload");
}



function approvedType($typ){
    if($typ=="application/zip"||$typ=="image/gif"||$typ="image/jpeg"||$typ=="image/png"){
        return true;
    }
    else{
        return false;
    }
}
//display parts
function printLoginform($actiontarget){
    echo "<form action=\"".$actiontarget."\" method=\"POST\">";
    echo "<table>";
    echo "<tr>";
    echo "<td>User:</td><td><input type=\"text\" name=\"user\"></td>";
    echo "</tr>";
    echo "<tr>";
    echo "<td>Pass:</td><td><input type=\"password\" name=\"pass\"></td>";
    echo "</tr>";
    echo "<tr>";
    echo "<td></td><td>";
    echo "<input type=\"submit\" value=\"Login\">";
    echo "</td></tr>";
    echo "</table>";
    echo "</form>";
}

//processing
function processUploader($action){
    if(canUpload($_SESSION['name'])){
        echo "<form enctype=\"multipart/form-data\" action=\"".$action."\" method=\"POST\">";
		echo "Hochladen: <input name=\"file\" type=\"file\">";
		echo "<input type=\"submit\" value=\"Upload\"></form>";
        
        if(isset($_FILES['file']) && approvedType($_FILES['file']['type'])){       
            if(!approvedType($_FILES['file']['type'])){
                echo "<br>".esc($_FILES['file']['type'])." is not an allowed filetype";
            }
            else{
                //gather sql data
                $name = $_FILES['file']['name'];
                $date = date("Y.m.d.H.i.s");
                $file = $_SERVER['DOCUMENT_ROOT']."/files/".$date;
                $uploader = $_SESSION['id'];
                
                $SQL = "INSERT INTO uploads (name, file, uploader, date) VALUES ('".$name."', '".$file."', '".$uploader."', '".$date."')";
                $db = connect();
                mysql_query($SQL);
                
                $genid = mysql_insert_id();
                
                mysql_close($db[0]);
                
                move_uploaded_file($_FILES['file']['tmp_name'], $_SERVER['DOCUMENT_ROOT']."/files/".$date);
                
                echo "Upload completed. <a href=\"http://spellmaker.de/download.php?id=".$genid."\">Downloadlink</a>";
            }
        }
    }
    else{
        echo "You are not allowed to upload files!";
    }
}

function processLogin($action){
    if(loggedin()){
        return true;
    }
    else if(isset($_POST['user'])){
        if(login($_POST['user'], $_POST['pass'])){
            echo "logged in as ".$_POST['user']."<br>";
            return true;
        }
        else{
            echo "wrong username or password<br>";
            printLoginform($action);
        }
    }
    else{
        printLoginform($action);
    }
    return false;
}

?>
