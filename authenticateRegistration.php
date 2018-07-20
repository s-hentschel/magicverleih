<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" type="text/css" href="style.css">
    <title>Registrierung authentifizieren</title>
    <meta charset="UTF-8">
</head>    
<body>
    <header>
            <h1>Magic Give And Take</h1>
    </header>
    <h2>wartende Registrierungen</h2>
    <table>
        <tr>
            <th>Vorname</th>
            <th>Nachname</th>
            <th>Username</th>
            <th>bestätigen</th>
        </tr>
        
        <?php
            require_once("config/db_confg.php");
            $mysqli = new mysqli($dbhost,$dbuser,$dbpw,$dbname);
            if($mysqli->connect_error){
                echo "<aside class='warning'> Fehler beim Verbinden mit der DB" . mysqli_connect_error()."</aside>";
                exit();
            }
            
            //check, ob User authentifiziert werden soll
            if(isset($_POST['confirm']) && ($_POST['confirm'] == 'yes')){
                $get_user_stmt = $mysqli->prepare("SELECT uid, vorname, nachname, username, password from user_candidate WHERE uid=?");
                $get_user_stmt->bind_param('i', $_POST['uid']);
                $get_user_stmt->execute();
                $get_user_stmt->bind_result($uid,$vorname,$nachname,$username,$password);
                $get_user_stmt->fetch();
                $get_user_stmt->close();
                
                
                //Überführen in user Tabelle in einer Transaktion
                $mysqli->autocommit(FALSE);
                
                $insert_user_stmt = $mysqli->prepare("INSERT into user(vorname,nachname,username,password) values(?,?,?,?)");
                $insert_user_stmt->bind_param('ssss',$vorname,  $nachname, $username, $password);             
                $insert_user_stmt->execute();
                
                $delete_user_candidate_stmt = $mysqli->prepare("DELETE FROM user_candidate WHERE uid=?");
                $delete_user_candidate_stmt->bind_param('i',$uid);               
                $delete_user_candidate_stmt->execute();
                        
                
                if ($mysqli->commit()) {
                    echo "User " . $username . " wurde authentifiziert"; 
                } else {
                    echo "Fehler: " . $mysqli->error;
                }
                
                
                $insert_user_stmt->close();
                $delete_user_candidate_stmt->close();           
                $mysqli->autocommit(TRUE);
                
                
            }   
            
            
            $get_pending_users_SQL = "SELECT * FROM user_candidate";
            $results = $mysqli->query($get_pending_users_SQL);
            
            while($zeile=$results->fetch_array()){
                echo "<tr>\n";
                echo "<td>" . htmlspecialchars($zeile['vorname'], ENT_QUOTES) ."</td>\n";
                echo "<td>" . htmlspecialchars($zeile['nachname'], ENT_QUOTES) ."</td>\n";
                echo "<td>" . htmlspecialchars($zeile['username'], ENT_QUOTES) ."</td>\n";
                if(isset($_POST['authenticate']) && ($_POST['authenticate']===$zeile['uid'])){
                    
                        ?>
                        <td>Bitte bestätigen<br>
                            <form action="authenticateRegistration.php" method="POST">
                            <input type="hidden" name="uid" value=<?php echo $zeile['uid']?> >
                            <button type="submit" name="confirm" value="yes">Ja</button>
                            <button type="submit" name="confirm" value="no">Nö</button>
                        </form>
                        </td>
                        <?php
                }
                else echo "<td><form action='authenticateRegistration.php' method='POST'><button type='submit' value='" . $zeile['uid'] . "'name='authenticate'>Bestätigen</button></form></td>\n";
                echo "</tr>\n";
                
            }
            
        
        
            $mysqli->close();
    ?>
        

    </table>    
</body>
</html>



