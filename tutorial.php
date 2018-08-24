<?php session_start();
//Cookie setzen, damit der User nächstes Mal nicht mehr zum Tutorial weitergeleitet wird
setcookie("been_here","ja");?>
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
            <h2>Was wird hier gemacht?</h2>
            <article>
                Hast du noch Karte XYZ dabei? Ich brauch die noch für mein Deck um heute zu spielen.<br>
                - Nee, sorry. Aber ich hab die noch zuhause liegen, warum hast du mir denn nicht eher Bescheid gegeben?
                <br><br>
                Kommt euch bekannt vor? Um solche Anfragen komfortabler zu gestalten und nicht die WhatsApp-Chats damit zu überladen, habe ich diese 
                Seite geschrieben. Hier könnt ihr euch anmelden, um selber eure gesuchten Karten einzutragen und anderen Usern zuzusichern, dass ihr ihnen
                ihre Karten mitbringt, falls ihr sie zu verleihen habt. Aktuell ist diese Seite nur für Spieler aus dem Funtainment in Berlin angedacht.
            </article>
            <h3>Registrieren</h3>
            <article>
                Ihr könnt euch <a href='registration.php'>hier registrieren</a>. Wie gesagt, habe ich die Seite vorerst nur für Spieler aus dem Funtainment konzipiert,
                daher wird eure Registrierung auch nur von mir per Hand freigeschaltet, falls ich euch kenne. Also bitte verwendet euren richtigen Namen ;)
            </article>
            <h3>Kartensuche einstellen</h3>
            <article>
                Unter <a href='sucheKarte.php'>Meine Wants</a> seht ihr eine Auflistung eurer gesuchten Karten und falls ihr eine davon nicht mehr benötigt, könnt
                ihr mit dem - Button die Karte aus der Suche nehmen. Unten habt ihr die Möglichkeit, eine neue Kartensuche ins System zu stellen. Die Felder Anzahl
                und Name sind Pflicht, optional könnt ihr auch noch Datum und Uhrzeit einstellen, bis zu dem ihr die Karte spätestens braucht. Falls dieser Termin überschritten wird
                , wird die Kartensuche automatisch entfernt.
                <br>
                <figure>
                    <img src="TutorialImages/neueSuche.jpg">
                    <figcaption>Die Karte wird gebraucht zum FNM am 6. Juli um 16 Uhr</figcaption>
                </figure>  
            </article>      
            <h3>Karten verleihen</h3>
            <article>
                Unter <a href="index.php">aktueller Bedarf</a> seht ihr alle Karten, die andere User gerade benötigen. Falls ihr die gesuchte Karte da habt und
                bereit seid, sie zu verleihen, klickt ihr auf den Button "Hab ich". Ihr müsst dann noch spezifizieren, wieviele Exemplare der Karte ihr zu verleihen habt
                und müsst den Verleih bestätigen. 
            </article>
            <h3>Verleih beenden</h3>
            <article>
                Unter <a href="profil.php">Profil</a> seht ihr eine Auflistung aller von euch aktuell geliehenen und verliehenen Karten. Sobald ihr eine von euch 
                verliehene Karte wieder zurückbekommen habt, weil sie nicht mehr benötigt wird, nehmt ihr sie bitte mit dem Button "-" aus dem System. 
                <figure>
                    <img src="TutorialImages/verleihBeenden.jpg">
                    <figcaption>Der Verleiher sollte die Karte aus dem System nehmen, sobald er sie zurück erhalten hat.</figcaption>     
                </figure>
            </article>
            
                
         <div class="newLine">      
         </div>     
        </div>
         <footer>
                <p>Sebastian Hentschel 2018</p>
            </footer>
    </body>
</html>


