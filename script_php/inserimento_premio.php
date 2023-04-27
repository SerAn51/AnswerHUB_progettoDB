<?php

require '../config_connessione.php'; // instaura la connessione con il db

require '../config_conn_mongodb.php'; // instaura la connessione con mongodb, creando db e collezione se non esiste
use MongoDB\BSON\UTCDateTime;

function inserisciPremio($pdo, $nome, $descrizione, $foto, $punti_necessari, $collezione_log)
{
    //Insert nella tabella Premio che mette come EmailUtenteAmministratore la mail di sessione
    try {
        $proc_inserisci_premio = "CALL InserisciPremio(:param1, :param2, :param3, :param4, :param5)";
        $prep_proc_inserisci_premio = $pdo->prepare($proc_inserisci_premio);
        $prep_proc_inserisci_premio->bindParam(':param1', $nome, PDO::PARAM_STR);
        $prep_proc_inserisci_premio->bindParam(':param2', $descrizione, PDO::PARAM_STR);
        $prep_proc_inserisci_premio->bindParam(':param3', $foto, PDO::PARAM_LOB);
        $prep_proc_inserisci_premio->bindParam(':param4', $punti_necessari, PDO::PARAM_INT);
        $prep_proc_inserisci_premio->bindParam(':param5', $_SESSION["email"], PDO::PARAM_STR);
        $prep_proc_inserisci_premio->execute();
    } catch (PDOException $e) {
        echo "Errore Stored Procedure: " . $e->getMessage();
        header("Location: logout.php");
        exit;
    }
    // Informazione da inserire nella collezione di log
    $informazione_log = array(
        "data" => new MongoDB\BSON\UTCDateTime(),
        "azione" => "Inserimento nuovo premio",
        "dettagli" => array(
            "nome" => $nome,
            "descrizione" => $descrizione,
            "punti_necessari" => $punti_necessari
        )
    );

    // Inserimento dell'informazione nella collezione di log
    $collezione_log->insertOne($informazione_log);
}

if (isset($_POST["inserisci"])) {
    $nome = $_POST["nome"];
    $descrizione = $_POST["descrizione"];
    $punti_necessari = $_POST["punti_necessari"];
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] !== UPLOAD_ERR_NO_FILE) { //il file è stato caricato e non è vuoto
        $formati_consentiti = array(IMAGETYPE_PNG, IMAGETYPE_JPEG);
        $formato_rilevato = exif_imagetype($_FILES['foto']['tmp_name']); //restituisce il tipo di immagine basandosi sulla sua firma
        if (in_array($formato_rilevato, $formati_consentiti)) {
            // il tipo di file dell'immagine selezionata è valido
            $foto = file_get_contents($_FILES['foto']['tmp_name']);
            inserisciPremio($pdo, $nome, $descrizione, $foto, $punti_necessari, $collezione_log);

            header('Location: ../amministratore_home.php?success=10'); //i codici da 10 a 19 gestiscono l'inserimento del premio
        } else {
            // il tipo di file dell'immagine selezionata non è valido
            header('Location: ../amministratore_home.php?error=10');
        }
    } else {
        //il file non è stato caricato o è vuoto
        header('Location: ../amministratore_home.php?error11');
    }
}