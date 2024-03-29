<?php
require '../config_connessione.php'; // instaura la connessione con il db

require '../config_conn_mongodb.php'; // instaura la connessione con mongodb, creando db e collezione se non esiste
use MongoDB\BSON\UTCDateTime;

function inserisciDomanda($pdo, $testo, $foto, $punteggio, $aperta_chiusa, $cf_azienda_inserente, $email_utente_inserente, $max_caratteri_domanda_aperta, $codice_sondaggio, $collezione_log)
{
    try {
        $inserisci_domanda = $pdo->prepare("CALL InserisciDomanda(:param1, :param2, :param3, :param4, :param5, :param6, :param7, :param8)");
        $inserisci_domanda->bindParam(':param1', $testo, PDO::PARAM_STR);
        if (!(is_null($foto))) { //se non è null
            $inserisci_domanda->bindParam(':param2', $foto, PDO::PARAM_LOB);
        } else {
            $inserisci_domanda->bindParam(':param2', $foto, PDO::PARAM_NULL);
        }
        if (!(is_null($punteggio))) {
            $inserisci_domanda->bindParam(':param3', $punteggio, PDO::PARAM_INT);
        } else {
            $inserisci_domanda->bindParam(':param3', $punteggio, PDO::PARAM_NULL);
        }
        $inserisci_domanda->bindParam(':param4', $aperta_chiusa, PDO::PARAM_STR);
        if (!(empty($_SESSION["email"]))) {
            $inserisci_domanda->bindParam(':param5', $cf_azienda_inserente, PDO::PARAM_NULL);
            $inserisci_domanda->bindParam(':param6', $email_utente_inserente, PDO::PARAM_STR);
        } else if (!(empty($_SESSION["cf_azienda"]))) {
            $inserisci_domanda->bindParam(':param5', $cf_azienda_inserente, PDO::PARAM_STR);
            $inserisci_domanda->bindParam(':param6', $email_utente_inserente, PDO::PARAM_NULL);
        }
        $inserisci_domanda->bindParam(':param7', $max_caratteri_domanda_aperta, PDO::PARAM_INT);
        $inserisci_domanda->bindParam(':param8', $codice_sondaggio, PDO::PARAM_INT);
        $inserisci_domanda->execute();
    } catch (PDOException $e) {
        echo "Errore Stored Procedure: " . $e->getMessage();
        header("Location: ../logout.php");
        exit;
    }
    // Informazione da inserire nella collezione di log
    $informazione_log = array(
        "data" => new MongoDB\BSON\UTCDateTime(),
        "azione" => "Inserimento nuova domanda",
        "dettagli" => array(
            "testo" => $testo,
            "punteggio" => $punteggio,
            "aperta_chiusa" => $aperta_chiusa,
            "cf_azienda_inserente" => $cf_azienda_inserente,
            "email_utente_inserente" => $email_utente_inserente,
            "max_caratteri_domanda_aperta" => $max_caratteri_domanda_aperta,
            "codice_sondaggio" => $codice_sondaggio
        )
    );

    // Inserimento dell'informazione nella collezione di log
    $collezione_log->insertOne($informazione_log);
}

if (isset($_POST["crea"])) {
    //salvo il codice del sondaggio inviato hidden tramite form
    $codice_sondaggio = $_POST['codice_sondaggio'];
    //salvo il testo dal form
    $testo = $_POST['testo'];


    //Un utente premium non può creare due opzioni con lo stesso testo (ignorando maiuscole e minuscole), questo perche'
    //lato utente che risponde ai sondaggi si potrebbe creare confusione, sono ammesse opzioni con lo stesso testo a patto che siano appartenenti a due domande diverse
    try {
        $proc_mostra_domande = $pdo->prepare("CALL MostraDomande(:param1)");
        $proc_mostra_domande->bindParam(':param1', $codice_sondaggio, PDO::PARAM_INT);
        $proc_mostra_domande->execute();
        $domande = $proc_mostra_domande->fetchAll(PDO::FETCH_ASSOC);
        $proc_mostra_domande->closeCursor();
    } catch (PDOException $e) {
        echo "Errore Stored Procedure: " . $e->getMessage();
        header("Location: ../logout.php");
        exit;
    }

    foreach ($domande as $domanda) {
        if (strcasecmp($domanda['Testo'], $testo) == 0) {
            //testo domanda duplicato
            header("Location: ../gestisci_domanda.php?cod_sondaggio=$codice_sondaggio&error=20");
            exit;
        }
    }

    //salvo la foto dal form, se c'e' ed e' un formato consentito, altrimenti la setto a NULL
    //FIXME: non prende l'immagine, anche se la inserisco nel form, viene settata null
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] !== UPLOAD_ERR_NO_FILE) { //il file è stato caricato e non è vuoto
        $formati_consentiti = array(IMAGETYPE_PNG, IMAGETYPE_JPEG);
        $formato_rilevato = exif_imagetype($_FILES['foto']['tmp_name']); //restituisce il tipo di immagine basandosi sulla sua firma
        if (in_array($formato_rilevato, $formati_consentiti)) {
            // il tipo di file dell'immagine selezionata è valido
            $foto = file_get_contents($_FILES['foto']['tmp_name']);
        } else {
            // il tipo di file dell'immagine selezionata non è valido
            header("Location: ../gestisci_domanda.php?cod_sondaggio=$codice_sondaggio&error=10");
            exit;
        }
    } else {
        $foto = NULL;
    }
    //salvo il punteggio se e' stato impostato, altrimenti lo setto a NULL
    if (isset($_POST['punteggio']) && ($_POST['punteggio'] != "")) {
        $punteggio = $_POST['punteggio'];
    } else {
        $punteggio = NULL;
    }

    // se ho richiamato inserimento_domanda.php in quanto utente, email non è vuoto quindi imposto $cf_azienda_inserente e $email_utente_inserente di conseguenza
    if (!(empty($_SESSION["email"]))) {
        $cf_azienda_inserente = NULL; //setto cf_azienda_inserente a NULL perche' la domanda viene inserita dall'utente premium (di sessione)
        $email_utente_inserente = $_SESSION["email"];
        // altrimenti l'ho richiamato come azienda
    } else if (!(empty($_SESSION["cf_azienda"]))) {
        $cf_azienda_inserente = $_SESSION["cf_azienda"];
        $email_utente_inserente = NULL; //setto email_utente_inserente a NULL perche' la domanda viene inserita dall'azienda (di sessione)
    }

    if (isset($_POST['checkbox_aperta']) && $_POST['checkbox_aperta'] == 'on') { // controllo che la checkbox sia stata selezionata
        $max_caratteri_domanda_aperta = $_POST["max_caratteri_domanda_aperta"];
        $aperta_chiusa = "APERTA";
        //InserisciDomanda(Testo varchar(3000), Foto longblob, Punteggio integer, ApertaChiusa ENUM ('APERTA', 'CHIUSA'), CFAziendainserente varchar(16), EmailUtenteinserente varchar(255), MaxCaratteriRisposta integer
        inserisciDomanda($pdo, $testo, $foto, $punteggio, $aperta_chiusa, $cf_azienda_inserente, $email_utente_inserente, $max_caratteri_domanda_aperta, $codice_sondaggio, $collezione_log);
        //reindirizza con un messaggio di successo
        header("Location: ../gestisci_domanda.php?cod_sondaggio=$codice_sondaggio&success=10");
        exit;
    } else { // La checkbox non è stata selezionata
        $aperta_chiusa = "CHIUSA";
        $max_caratteri_domanda_aperta = 0;
        //InserisciDomanda(Testo varchar(3000), Foto longblob, Punteggio integer, ApertaChiusa ENUM ('APERTA', 'CHIUSA'), CFAziendainserente varchar(16), EmailUtenteinserente varchar(255), MaxCaratteriRisposta integer
        inserisciDomanda($pdo, $testo, $foto, $punteggio, $aperta_chiusa, $cf_azienda_inserente, $email_utente_inserente, $max_caratteri_domanda_aperta, $codice_sondaggio, $collezione_log);
        //reindirizza con un messaggio di successo
        header("Location: ../gestisci_domanda.php?cod_sondaggio=$codice_sondaggio&success=10");
        exit;
    }
}
?>