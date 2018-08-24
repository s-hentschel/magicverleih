<?php session_start();?>
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
            <h3>Verliehene Karten</h3>
            <?php
            if(!isset($_SESSION['username'])){
                echo "<span class='notification'>Bitte erst einloggen</span>";
            }
            else{
                require_once("config/db_confg.php");
                $mysqli = new mysqli($dbhost,$dbuser,$dbpw,$dbname);
                if($mysqli->connect_error){
                    echo "Fehler beim Verbinden mit der DB" . mysqli_connect_error();
                    exit();
                }
                
                $uid_active = $_SESSION['uid'];

                //check, ob Verleih beendet werden soll
                if(isset($_POST['beenden']) && ($_POST['beenden']=='yes')){
                              
                    if(!verleih_beenden($mysqli, $_POST['verleih_id']))
                        echo "<span class='notification'>Ungültige ID!</span>\n";    //$_POST['verleih_id'] wurde manipuliert
                    else echo "<span class='notification'>Verleih beendet!</span>";    
                }
                    
                verleih_ausgeben($mysqli, $uid_active);
                ?>
    
            <h3>Geliehene Karten</h3>
                <?php geliehen_ausgeben($mysqli, $_SESSION['uid']); ?>
                
            <h3>Profileinstellungen</h3>
            
                <?php
                profil_ausgeben($mysqli, $_SESSION['uid']);
                $mysqli->close();
            }
            ?>
            <div class="newLine">        
            </div>
        </div>
        
            <footer>
                <p>Sebastian Hentschel 2018</p>
            </footer>
    </body>
</html>

<?php
    function verleih_beenden($mysqli,$verleih_id){
        //alle verleih_ids des angemeldeten users holen
        $get_verleih_stmt = $mysqli->prepare("SELECT verleih_id from verleih WHERE uid_von=?");
        $get_verleih_stmt->bind_param('i', $_SESSION['uid']);
        $get_verleih_stmt->execute();
        $get_verleih_stmt->bind_result($verleih_id_user);

        //check ob $_POST['verleih_id'] manipuliert wurde und tatsächlich nur eigener Verleih beendet wird
        $valid=false;
        while($get_verleih_stmt->fetch() &&!$valid){
            if($verleih_id == $verleih_id_user)  $valid=true;
        }
        $get_verleih_stmt->close();

        if($valid){
            $delete_verleih_stmt = $mysqli->prepare("DELETE FROM verleih WHERE verleih_id=?");
            $delete_verleih_stmt->bind_param('i', $verleih_id);
            $delete_verleih_stmt->execute();        
            $delete_verleih_stmt->close();   
            return true;
        }
        else{
            return false;
        }                                           
    }
    
    function verleih_ausgeben($mysqli, $uid_active){
        //alle verliehenen Karten des aktiven Users holen   
        $select_verleih_stmt = $mysqli->prepare("SELECT anzahl, name, vorname, nachname, verleih_id, seit"
                        ." FROM verleih, karten, user"
                        ." WHERE verleih.uid_von=? AND verleih.uid_an=user.uid AND verleih.cid=karten.cid");
        $select_verleih_stmt->bind_param('i', $uid_active);
        $select_verleih_stmt->execute();
        $select_verleih_stmt->bind_result($anzahl,$cardname,$vorname,$nachname,$verleih_id, $seit);   
        $select_verleih_stmt->store_result();
        if($select_verleih_stmt->num_rows>0){
            ?>
            <table class='floatTable'>
                <tr>
                    <th>Anzahl</th>
                    <th>Kartenname</th>
                    <th>verliehen an</th>
                    <th>verliehen seit</th>
                    <th>Verleih beenden</th>
                </tr>


                <?php
                while($select_verleih_stmt->fetch()){
                    echo "<tr>\n";
                    echo "<td>". $anzahl ."</td>\n";
                    echo "<td>" . htmlspecialchars($cardname, ENT_QUOTES) ."</td>\n";
                    echo "<td>" . htmlspecialchars($vorname, ENT_QUOTES) ." ". htmlspecialchars($nachname, ENT_QUOTES)  ."</td>\n";
                    echo "<td>" .$seit . "</td>\n";
                    echo "<td>";

                    //Bestätigung für Verleih beenden
                    if(isset($_POST['beenden']) && $_POST['beenden']==$verleih_id){
                        echo "<span class='notification'>Verleih wirklich beenden?</span><br>\n";
                            ?>
                            <form action="profil.php" method="POST">
                                <input type="hidden" name="verleih_id" value="<?php echo $verleih_id?>">    
                                <button type="submit" name="beenden" value="yes">Ja</button>
                                <button type="submit" name="beenden" value="no">Nö</button>
                            </form>
                            <?php                  
                    }
                    else echo "<form action='profil.php' method='POST'><button class='verleih_beenden' type='submit' name='beenden' value='" . $verleih_id ."'>"
                                    . "-</button><br></form>\n"; 
                    echo "</td>";
                    echo "</tr>";
                }
                $select_verleih_stmt->close();
                echo "</table>\n";
        }
        else echo "<span class='notification'>Du hast keine Karten verliehen.</span>";
    }
    
    /* Gibt alle geliehenen Karten des users $_SESSION['uid'] 
     * in Tabelle aus
     */
    function geliehen_ausgeben($mysqli, $uid_active){
        
        $select_geliehen_stmt = $mysqli->prepare("SELECT anzahl, name, vorname, nachname, verleih_id, seit"
                            ." FROM verleih, karten, user"
                            ." WHERE verleih.uid_an=?"
                            ." AND verleih.uid_von=user.uid"
                            ." AND verleih.cid=karten.cid");
        $select_geliehen_stmt->bind_param('i', $uid_active);
        $select_geliehen_stmt->execute();
        $select_geliehen_stmt->bind_result($anzahl,$cardname,$vorname,$nachname,$verleih_id, $seit);  
        $select_geliehen_stmt->store_result();
        if($select_geliehen_stmt->num_rows>0){
        ?>
            <table>
                <tr>
                <th>Anzahl</th>
                <th>Kartenname</th>
                <th>geliehen von</th>
                <th>geliehen seit</th>
                </tr>

                <?php      
                while($select_geliehen_stmt->fetch()){
                    echo "<tr>\n";
                    echo "<td>". $anzahl ."</td>\n";
                    echo "<td>" . htmlspecialchars($cardname, ENT_QUOTES) ."</td>\n";
                    echo "<td>" . htmlspecialchars($vorname, ENT_QUOTES) ." ". htmlspecialchars($nachname, ENT_QUOTES)  ."</td>\n";
                    echo "<td>$seit</td>\n";
                    echo "</tr>\n";
                }
                $select_geliehen_stmt->close(); 
            echo "</table>\n";
        }
        else echo "<span class='notification'>Du hast keine Karten geliehen</span>";
    }
    
    /* Gibt die Profileinstellungen des users $uid_active aus
     * 
     */
    function profil_ausgeben($mysqli, $uid_active){
        
        //hol die Daten des eingeloggten Users
        $get_userData_stmt = $mysqli->prepare("SELECT vorname, nachname, username, password FROM user WHERE uid=?");
        $get_userData_stmt->bind_param('s', $uid_active);
        $get_userData_stmt->execute();
        $get_userData_stmt->bind_result($vorname,$nachname,$username,$password);
        $get_userData_stmt->fetch();

        $get_userData_stmt->close();

        echo "<article>";
        echo "Vorname: " . htmlspecialchars($vorname, ENT_QUOTES) ."<br>\n";
        echo "Nachname: " . htmlspecialchars($nachname, ENT_QUOTES) ."<br>\n";
        echo "Accountname: " . htmlspecialchars($username, ENT_QUOTES) ."<br>\n";
        //echo "Passwort: " . htmlspecialchars($password, ENT_QUOTES) ."<br>\n";
        echo "</article>";
        
        
            
    }
?>
