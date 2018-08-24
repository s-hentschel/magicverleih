<?php session_start();
 if(isset($_POST['username'])){
    //versuche Verbindung zur DB aufzubauen
    require_once("config/db_confg.php");
    $mysqli = new mysqli($dbhost,$dbuser,$dbpw,$dbname);
    if($mysqli->connect_error){
        echo "Fehler beim Verbinden mit der DB" . mysqli_connect_error();
        exit();
    }
                
    check_password($mysqli,$_POST['username']);
    $mysqli->close();
}
 if(isset($_SESSION['username'])){
    //auf index.php weiterleiten
    header("Location: index.php");
}
?>
<!DOCTYPE html>
<html>
    <head>
        <link rel="stylesheet" type="text/css" href="style.css">
        <title>Magic Give and Take</title>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
    </head>
    <body>
        <header>
            <h1>Magic Give and Take</h1>
        </header>
        <?php    
            require_once("includes/Navleiste.php");
        ?>
            
        <br>
        <div class='newLine'></div>
        <div id="wrapper">
            <h3>Login</h3>
           
                <form class="newSearch" action="login.php" method="POST">
                    <input name ="username" type='text' value='username'>
                    <input name="password" type='text' value='password'>
                    <button type="submit">submit</button> 
                </form>
                
                <br>
                
                    noch kein Account?<br>
                    <a href="registration.php">Hier registrieren</a><br>
                    
                
            <div class="newLine">       
            </div>    
                  
        </div>
        <footer>
            <p>Sebastian Hentschel 2018</p>
        </footer>
    </body>
</html>


<?php
    function check_password($mysqli, $username){
        //Passwort für eingegebenen username holen
        $get_password_stmt = $mysqli->prepare("SELECT password, uid from user where username=?");
        $get_password_stmt->bind_param('s', $username);
        $get_password_stmt->execute();
        $get_password_stmt->store_result();
        $get_password_stmt->bind_result($password, $uid);
        $get_password_stmt->fetch();

        if(($get_password_stmt->num_rows)===0){
            //username existiert nicht
            //TODO: gleiche Fehlermeldung für falschen username/password
            echo "<span class='warning'>username oder passwort falsch</span><br>\n";
        }
        else{
            if(password_verify($_POST['password'], $password)){
                //Login erfolgreich, weiterleiten auf index.php
                $_SESSION['username'] = $username;
                $_SESSION['uid'] = $uid; 
            }
            else
                echo "<span class='warning'>username oder passwort falsch</span><br>\n";    
        }
        $get_password_stmt->close();
    }
?>



