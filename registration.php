<!DOCTYPE html>
<?php
    /* Prüft die Formulardaten in $_POST auf Fehler
     * @param mysqli:         DB-Verbindung
     * @param eingabefehler:  assoziatives Boolean-Array für Fehler
     * 
     * @return: assoziatives Boolean-Array mit Fehlern             
     */
    function check_registration_errors($mysqli, $eingabefehler){
            
            if(empty($_POST['vorname'])) $eingabefehler['vorname_empty'] = true;
            if(empty($_POST['nachname'])) $eingabefehler['nachname_empty'] = true;

            if(empty($_POST['username'])) $eingabefehler['username_empty'] = true;          
            else{
                //Check, ob username bereits vergeben ist
                
                

                //Abfrage auf unique username und insert in Transaktion, damit keine doppelten usernames
                $mysqli->autocommit(false);

                //hol usernamen aller aktiven und austehenden Accounts
                $get_usernames_SQL = "(SELECT username FROM user) UNION (SELECT username FROM user_candidate)";
                $result = $mysqli->query($get_usernames_SQL);

                while(($zeile = $result->fetch_array()) && (!$eingabefehler['username_taken'])){
                    if(htmlspecialchars($_POST['username'],ENT_QUOTES) === htmlspecialchars($zeile['username'],ENT_QUOTES) ){
                        $eingabefehler['username_taken'] = true;    
                    }
                }
            }
            if(empty($_POST['password'])) {$eingabefehler['password_empty'] = true;}
                else if (htmlspecialchars($_POST['password'], ENT_QUOTES) != htmlspecialchars ($_POST['password_confirm'], ENT_QUOTES)){ $eingabefehler['password_mismatch'] = true;}

            return $eingabefehler;
    }


?>
<html>
    <head>
        <link rel="stylesheet" type="text/css" href="style.css">
        
        <title>Magic GiveOrTake</title>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
    </head>
    <body>
        <header>
            <h1>Registrierung</h1>
        </header>
        <?php require_once("includes/Navleiste.php");?>
               
        <div class="newLine"></div>
        <div id="wrapper">
           
        <?php        
        $registration_complete = false;
        $eingabefehler = array(
            "vorname_empty" => false,
            "nachname_empty" => false,
            "username_empty" => false,
            "username_taken" => false,
            "password_empty" => false,
            "password_mismatch" =>false               
        );
                  
        if(isset($_POST['registrieren'])){
            require_once("config/db_confg.php");
            $mysqli = new mysqli($dbhost,$dbuser,$dbpw,$dbname);
            if($mysqli->connect_error){
                echo "<aside class='warning'> Fehler beim Verbinden mit der DB" . mysqli_connect_error()."</aside>";
                exit();
            }

            //check auf fehlende/fehlerhafte Eingaben 
            
            $eingabefehler = check_registration_errors($mysqli, $eingabefehler);
            

            //falls keine Fehler: DB Eintrag in Tabelle user_aspirant anlegen, muss dann vom admin bestätigt werden
            if(!in_array(true, $eingabefehler)){

                $insert_user_candidate_stmt = $mysqli->prepare("INSERT INTO user_candidate(username, password, vorname, nachname)"
                                            . "values(?,?,?,?)");
                $password_hashed = password_hash($_POST['password'],PASSWORD_DEFAULT);
                $insert_user_candidate_stmt->bind_param('ssss', $_POST['username'],$password_hashed , $_POST['vorname'], $_POST['nachname']);
                $insert_user_candidate_stmt->execute();



                $insert_user_candidate_stmt->close();
                $registration_complete=true;
            }       
        }

        if(!$registration_complete){    
            ?>
                
            <form action="registration.php" method="POST">
                <label>Vorname:  <input type="text" name="vorname" value="<?php if (isset($_POST['vorname'])) echo htmlspecialchars ($_POST['vorname'], ENT_QUOTES)?>"></label>
                    <?php if($eingabefehler['vorname_empty']) echo "<span class='warning'>Bitte Vornamen angeben!</span>" ?><br>
                <label>Nachname: <input type="text" name="nachname" value="<?php if (isset($_POST['nachname'])) echo htmlspecialchars($_POST['nachname'], ENT_QUOTES)?>"></label>
                    <?php if($eingabefehler['nachname_empty']) echo "<span class='warning'>Bitte Nachnamen angeben!</span>" ?><br>
                <label>Username: <input type="text" name="username" value="<?php if (isset($_POST['username'])) echo htmlspecialchars ($_POST['username'], ENT_QUOTES)?>"></label>
                    <?php if($eingabefehler['username_empty']) echo "<span class='warning'>Bitte Usernamen angeben! </span>";
                          if($eingabefehler['username_taken']) echo "<span class='warning'>Username schon vergeben! </span>"  ?><br>

                <label>Passwort: <input type="password" name="password" value="<?php if (isset($_POST['password'])) echo htmlspecialchars ($_POST['password'], ENT_QUOTES)?>"></label>
                    <?php if($eingabefehler['password_empty']) echo "<span class='warning'>Bitte Password angeben!</span>" ?><br>
                    <label>Passwort bestätigen: <input type="password" name="password_confirm" value="<?php if (isset($_POST['password_confirm'])) echo htmlspecialchars ($_POST['password_confirm'], ENT_QUOTES)?>"></label>
                    <?php if($eingabefehler['password_mismatch']) echo "<span class='warning'>Passwörter stimmen nicht überein</span>" ?><br>

                <button type="submit" name="registrieren">registrieren</button>
            </form>    
        <?php
        }
        else echo "Registrierung wurde abgeschickt. Der Account wird in den nächsten Tagen freigeschaltet";
        ?>
        </div>
        
         <div class="newLine">      
                <footer class="grid12">Sebastian Hentschel 2018</footer>
            </div>
    </body>
</html>




