<?php session_start();?>
<!DOCTYPE html>
<html>
    <head>
        <link rel="stylesheet" type="text/css" href="style.css"> 
        <title>Magic GiveOrTake</title>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
    </head>
    <body>
        <header>
            <h1>Magic Give And Take</h1>
        </header>
        <?php
            
            
        
            require_once("includes/Navleiste.php");
        ?>
        
        
        
        <br>
        <div id="wrapper">
           
            <h3>aktuelle Gesuche</h3>

            <?php 
                require_once("config/db_confg.php");
                $mysqli = new mysqli($dbhost,$dbuser,$dbpw,$dbname);
                if($mysqli->connect_error){
                    echo "Fehler beim Verbinden mit der DB" . mysqli_connect_error();
                    exit();
                }

                if(isset($_SESSION['username'])){

                    //checken, ob eine Karte gelöscht werden soll
                    if(isset($_POST['delete'])){
                        delete_search_entry($_POST['delete'], $mysqli);
                    }

                    //checken, ob Karte hinzugefügt werden soll   
                    
                    $add_card_error=false;
                    if(isset($_POST['cardname']) && isset($_POST['anzahl'])){
                        
                        if(!add_search_entry($_POST['cardname'], $_POST['anzahl'], $_POST['datum'], $_POST['uhrzeit'], $mysqli)){
                            $add_card_error=true;
                        }       
                    }

                    //Hol Liste mit allen aktuellen Gesuchen des aktiven Users
                    $select_search_stmt = $mysqli->prepare("SELECT anzahl, karten.name, such_id, bis_wann"
                                                         . " FROM user, karten, suche "
                                                         . " WHERE user.uid = suche.uid AND karten.cid=suche.cid"
                                                         . " AND user.username =?");
                    $select_search_stmt->bind_param('s', $_SESSION['username']);
                    $select_search_stmt->execute();
                    $select_search_stmt->bind_result($anzahl, $cardname, $such_id, $date);
                    $select_search_stmt->store_result();
                    if($select_search_stmt->num_rows>0){
                        ?>
                        <table>
                            <tr>
                            <th>Anzahl</th>
                            <th>Kartenname</th>
                            <th>benötigt bis</th>
                            <th>löschen</th>
                            </tr>
                        <?php
                        while($select_search_stmt->fetch()){
                            //gib aktuelle Gesuche des Users aus

                            echo "<tr>\n";
                            echo "<td>" . $anzahl ."</td>\n";
                            echo "<td>" . htmlspecialchars($cardname, ENT_QUOTES) . "</td>\n";
                            echo "<td>";
                                if($date!=null){
                                    $datum= new DateTime($date);
                                    echo $datum->format('d.m.Y');
                                    if($datum->format('H:i') != '00:00') 
                                        echo ", " . $datum->format('H:i'); 
                                
                                }
                            echo "</td>";
                            echo "<td><form action='sucheKarte.php' method='POST'><button class='delete' type='submit' name='delete' value='" . $such_id ."'>-</button></form></td>\n";
                            echo "</tr>";
                        }

                        echo "</table>";    
                    }
                    else echo "<span class='notification'>Du suchst zur Zeit keine Karten.</span>";
                    $select_search_stmt->close();  
                    $mysqli->close();    
            ?>

            <br>
            <h3>neues Gesuch</h3>
            <?php 
                if($add_card_error)
                    echo "<span class='notification'> Karte " . $_POST['cardname'] . " existiert nicht, bitte Rechtschreibung überprüfen!</span>";
            ?>
            <form class="newSearch" action="sucheKarte.php" method="POST">
                <input name="anzahl" type='text' value='4' class="anzahl">
                <input name="cardname" type='text' value='Lightning Bolt'>
                <input name="datum" type="date" value="" min="<?php echo date('Y-m-d'); ?>">
                <input name="uhrzeit" type="time">
                <Button class="add" type="submit">+</button>
            </form>

            <?php
                }
                else echo "<span class='notification'>Bitte erst einloggen</span>";
            ?>
                        
            </div>
            <div class="newLine">      
                <footer class="grid12">Sebastian Hentschel 2018</footer>
            </div>
       
    </body>
</html>

<?php

    /* Löscht einen Sucheintrag aus der DB, falls $such_id dem eingeloggten user gehört,
     * sonst wird Fehlermeldung ausgegeben
     * 
     * @param such_id: id des zu löschenden Eintrags in Tabelle suche
     * @param $mysqli: DB-Verbindung
     * 
     */
    function delete_search_entry($such_id, $mysqli){
        /*check ob $_POST['verleih_id'] manipuliert wurde und tatsächlich
         * nur eigener Verleih beendet wird     */

        $get_suche_stmt = $mysqli->prepare("SELECT uid from suche WHERE such_id=?");
        $get_suche_stmt->bind_param('i', $such_id);
        $get_suche_stmt->execute();
        $get_suche_stmt->bind_result($uid_for_search);
        $get_suche_stmt->fetch();
        $get_suche_stmt->close();

        if($uid_for_search != ((int)$_SESSION['uid'])){
            echo "<span class='notification'>Diese Suche gehört dir nicht!</span>\n";
        }
        else{
            $delete_search_stmt = $mysqli->prepare("DELETE FROM suche WHERE such_id=?");
            $delete_search_stmt->bind_param('i', $such_id);
            $delete_search_stmt->execute();        
            $delete_search_stmt->close();
        }  
    }
    
    
    /* Fügt 
     * 
     * @param such_id: id des zu löschenden Eintrags in Tabelle suche
     * @param $mysqli: DB-Verbindung
     * 
     */
    function add_search_entry($cardname, $anzahl, $datum, $uhrzeit, $mysqli){

        //prüfe, ob Karte schon in DB existiert
        $get_card_stmt = $mysqli->prepare("SELECT cid FROM karten WHERE name=?");
        $get_card_stmt->bind_param('s', $cardname);
        $get_card_stmt->execute();
        $get_card_stmt->store_result();
        $num_rows=$get_card_stmt->num_rows;

        $get_card_stmt->bind_result($cid);
        $get_card_stmt->fetch();                     
        $get_card_stmt->close();


        if($num_rows==0){
            //Anfrage an Scryfall schicken, falls nicht
            if(!$cid = contact_scryfall($cardname, $mysqli))
                    //Kartenname existiert nicht
                    return false;   
        }

        $datetime=null;
        if(!empty($datum) && !empty($uhrzeit)){
            $datetime=$datum . " " . $uhrzeit; 
            
            
        }
        else if(!empty($datum)){
            $datetime=$datum; 
        }
        
        
        
        $insert_search_stmt = $mysqli->prepare("INSERT INTO suche(uid, cid, anzahl, bis_wann) VALUES (?,?,?,?)");
        $insert_search_stmt->bind_param('iiis', $_SESSION['uid'], $cid, $anzahl, $datetime);
        $insert_search_stmt->execute();
        $insert_search_stmt->close();
        return true;

    }
    
     /* Sendet Anfrage an Scryfall, falls erfolgreich wird Karte in Tabelle Karten hinzugefügt
      * und Kartenbild auf Server gespeichert
      * 
      * @param cardname: Kartenname als String
      * @param $mysqli: DB-Verbindung
      * 
      *@return: id des neu hinzugefügten Eintrags in Tabelle Karte falls erfolgreich,
      *         false, falls Karte von Scryfall nicht gefunden wurde
      * 
     */
    function contact_scryfall($cardname, $mysqli){
        $urlmagic = "https://api.scryfall.com/cards/search?q="  
               .urlencode($cardname) ;

        
        if (!$contents = file_get_contents($urlmagic)) {
            //Kartenname existiert nicht
            return false;
        } 
        
        $json_data = json_decode($contents);
       
        $scryfall_card_name = $json_data->data[0]->name;
        $scryfall_image_path = $json_data->data[0]->image_uris->small;      //19kB Bild speichern

        $imagepath= "./cardimages/". $scryfall_card_name .".jpg";
        copy($scryfall_image_path, $imagepath);   //Kopiere Image auf PHP-Server

        $insert_card_SQL = $mysqli->prepare("INSERT into karten(name, image_path) values (?,?)");
        $insert_card_SQL->bind_param('ss', $scryfall_card_name, $imagepath);
        $insert_card_SQL->execute();
        $insert_card_SQL->close();
        
        return $mysqli->insert_id;
    }
    ?>