<?php session_start();
 /*Logout prüfen*/
if(isset($_GET['logout'])){
                session_destroy();
                session_start();
            }
//check, ob Besucher schonmal auf der Seite war
            if(!isset($_COOKIE["been_here"]))
                header("Location: tutorial.php");            
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
                    
            <h2>Gesuchte Karten</h2>
            <?php
                    require_once("config/db_confg.php");
                    $mysqli = new mysqli($dbhost,$dbuser,$dbpw,$dbname);
                    if($mysqli->connect_error){
                        echo "<aside class='notification'> Fehler beim Verbinden mit der DB" . mysqli_connect_error()."</aside>";
                        exit();
                    }
                
                    //check, ob eingeloggter User Karte verleihen will
                    if(isset($_SESSION['uid']) && isset($_POST['confirm']) && ($_POST['confirm']==='yes')){
                        echo "<span class='notification'>Karte verliehen!</span>";
                        verleih_karte($mysqli, $_POST['such_id'],$_POST['anzahl_verleih']);
                    }
                    
                    //Hol Liste mit allen aktuellen Gesuchen
                    $get_searches_SQL = "SELECT anzahl, karten.name, image_path, such_id, vorname, nachname, username, bis_wann"
                                    . "      FROM user, karten, suche "
                                    . "      WHERE user.uid = suche.uid AND karten.cid=suche.cid;";
                                             
                    $result = $mysqli->query($get_searches_SQL);

                    //gib alle gesuchten Karten aus
                    while($zeile = $result->fetch_array()){
                        //eigene Suchen nicht mit angeben
                        if(isset($_SESSION['username'])){
                            if($zeile['username']==$_SESSION['username']) continue;
                        }
                        
                        //für jede Karte article mit Bild, Informationen und Button "Verleih" falls eingeloggt
                        echo "<article class='grid4'>\n";
                            echo "<img src='" . htmlspecialchars($zeile['image_path'], ENT_QUOTES) ."'><br>\n";              
                            echo $zeile['anzahl']. " ";
                            echo htmlspecialchars($zeile['name'], ENT_QUOTES) . "<br>\n";
                            echo "von: " . htmlspecialchars($zeile['vorname'], ENT_QUOTES) . " " . htmlspecialchars($zeile['nachname'], ENT_QUOTES) ."<br>\n";
                            if($zeile['bis_wann']!=null){
                                $datum= new DateTime($zeile['bis_wann']);
                                echo "bis: ".$datum->format('d.m.Y');
                                if($datum->format('H:i') != '00:00') 
                                    echo ", " . $datum->format('H:i'); 
                                echo "<br>\n";
                            }
                            else
                                echo "bis: egal<br>\n";
                           
                       
                            if(isset($_SESSION['username'])){
                                //checken, ob HabIch schon geklickt wurde und nur noch Bestätigung erforderlich ist
                                if(isset($_POST['habich']) && ($_POST['habich']===$zeile['such_id'])){

                                    echo "<span class='notification'>Bitte Anzahl bestätigen</span><br>";
                                    ?>
                                    <form action="index.php" method="POST">
                                        <input type="number" name="anzahl_verleih" class="anzahl" value="<?php echo $zeile['anzahl']?>" min="1" max="<?php echo $zeile['anzahl']?>">
                                        <input type="hidden" name="such_id" value=<?php echo $zeile['such_id']?> >
                                        <button type="submit" name="confirm" value="yes">Ja</button>
                                        <button type="submit" name="confirm" value="no">Nö</button>
                                    </form>
                                <?php
                                }
                                else{
                                   echo "<form method='POST' action='index.php'>\n";
                                   echo "<button class='habich' type='submit' name='habich' value='" . $zeile['such_id'] ."'>"
                                        . "Hab ich</button><br>\n"; 
                                   echo "</form>\n";
                                }              
                            }
                        echo "</article>\n";
                    }
              
                    $mysqli->close();
                 
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
    function verleih_karte($mysqli, $such_id, $anzahl_verleih){
        //hol die UserID des aktiven Users
        
        $uid_active = $_SESSION['uid'];
        
        //TODO: check Beschränkung auf $anzahl_verleih

        //hol Daten des suchenden Users und der gesuchten Karte
        $get_search_stmt = $mysqli->prepare("SELECT uid,cid,anzahl FROM suche WHERE such_id=?");
        $get_search_stmt->bind_param('s', $such_id);
        $get_search_stmt->execute();
        $get_search_stmt->bind_result($uid_passive, $cid, $anzahl_suche);
        $get_search_stmt->fetch();
        $get_search_stmt->close();

        //überführen von Tabelle Suche in Tabelle Verleih in einer Transaktion
        $mysqli->autocommit(false);

        $insert_verleih_stmt = $mysqli->prepare("INSERT INTO verleih(uid_von, uid_an, cid, anzahl, seit) VALUES (?,?,?,?,?)");
        $datum_heute = date('Y-m-d');
        $insert_verleih_stmt->bind_param('iiiis', $uid_active, $uid_passive, $cid, $anzahl_verleih, $datum_heute);
        $insert_verleih_stmt->execute();
        $insert_verleih_stmt->close();

        
        if($anzahl_suche==$anzahl_verleih){
            //Verleiher hat genug Karten, Suchanfrage kann gelöscht werden
            $delete_search_stmt = $mysqli->prepare("DELETE FROM suche WHERE such_id=?");
            $delete_search_stmt->bind_param('i', $such_id);
            $delete_search_stmt->execute();        
            $delete_search_stmt->close();

        }
        else{
            //Verleiher hat nicht genug Karten, Suchanfrage bleibt bestehen und Anzahl wird angepasst
            $anzahl_rest = $anzahl_suche - $anzahl_verleih;
            $update_suche_stmt = $mysqli->prepare("UPDATE suche SET anzahl=? WHERE such_id=?");
            $update_suche_stmt->bind_param('ii', $anzahl_rest, $such_id);
            $update_suche_stmt->execute();
            $update_suche_stmt->close();
        }

        $mysqli->commit();
        //TODO: ROLLBACK bei Fehler
        $mysqli->autocommit(true);
    }
?>

