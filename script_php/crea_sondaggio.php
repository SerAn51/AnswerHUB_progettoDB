<?php
require '../config_connessione.php'; // instaura la connessione con il db

function crea_sondaggio($pdo, $titolo, $max_utenti, $data_chiusura, $dominio, $cf_azienda_creante, $email_utente_creante, $utente_o_azienda)
{
    $stato = 'APERTO';
    $data_creazione = date('Y-m-d H:i:s', time()); //da timestamp a formato Y-m-d H:i:s usato da MySQL
    //NB: $data_chiusura e' gia' nel giusto formato

    //Crea sondaggio impostando lo stato APERTO, come data_creazione la data attuale, come CFAziendacreante NULL, come EmailUtentecreante la mail di sessione
    $proc_crea_sondaggio = $pdo->prepare("CALL CreaSondaggio(:param1, :param2, :param3, :param4, :param5, :param6, :param7, :param8)");
    $proc_crea_sondaggio->bindParam(':param1', $titolo, PDO::PARAM_STR);
    $proc_crea_sondaggio->bindParam(':param2', $stato, PDO::PARAM_STR);
    $proc_crea_sondaggio->bindParam(':param3', $max_utenti, PDO::PARAM_INT);
    $proc_crea_sondaggio->bindParam(':param4', $data_creazione, PDO::PARAM_STR);
    $proc_crea_sondaggio->bindParam(':param5', $data_chiusura, PDO::PARAM_STR);
    $proc_crea_sondaggio->bindParam(':param6', $dominio, PDO::PARAM_STR);
    if ($utente_o_azienda) {
        $proc_crea_sondaggio->bindParam(':param7', $cf_azienda_creante, PDO::PARAM_NULL);
        $proc_crea_sondaggio->bindParam(':param8', $email_utente_creante, PDO::PARAM_STR);
    } else {
        $proc_crea_sondaggio->bindParam(':param7', $cf_azienda_creante, PDO::PARAM_STR);
        $proc_crea_sondaggio->bindParam(':param8', $email_utente_creante, PDO::PARAM_NULL);
    }
    $proc_crea_sondaggio->execute();
}

if (isset($_POST["crea"])) {
    $titolo = $_POST["titolo"];
    $max_utenti = $_POST["max_utenti"];
    $data_chiusura = $_POST["data_chiusura"];
    $dominio = $_POST["dominio"];

    // se la variabile di sessione email non e' vuota, vuol dire che il crea sondaggio e' stato chiamato da un utente premium
    $utente_o_azienda = true; //se true sono un utente che vuole creare un sondaggio, passero' la mail di sessione
    if (!(empty($_SESSION["cf_azienda"]))) {
        $utente_o_azienda = false; // se la cf_azienda di sessione esiste, allora sono un'azienda e passero' la cf_azienda di sessione come cf_azienda_creante
    }

    //Un utente premium non può creare due sondaggi con lo stesso nome (ignorando maiuscole e minuscole), questo perche'
//lato utente che risponde ai sondaggi si potrebbe creare confusione, sono ammessi sondaggi con lo stesso nome a patto che abbiano creatore diverso
    $proc_mostra_sondaggi = $pdo->prepare("CALL MostraSondaggi(:param1, :param2)");
    if ($utente_o_azienda) {
        $email_utente_creante = $_SESSION['email'];
        $cf_azienda_creante = null;
        $proc_mostra_sondaggi->bindParam(':param1', $email_utente_creante, PDO::PARAM_STR);
        $proc_mostra_sondaggi->bindParam(':param2', $cf_azienda_creante, PDO::PARAM_NULL);
    } else {
        $email_utente_creante = null;
        $cf_azienda_creante = $_SESSION['cf_azienda'];
        $proc_mostra_sondaggi->bindParam(':param1', $email_utente_creante, PDO::PARAM_NULL);
        $proc_mostra_sondaggi->bindParam(':param2', $cf_azienda_creante, PDO::PARAM_STR);
    }
    $proc_mostra_sondaggi->execute();
    $sondaggi = $proc_mostra_sondaggi->fetchAll(PDO::FETCH_ASSOC);
    $proc_mostra_sondaggi->closeCursor();

    // se sto inserendo un sondaggio con un titolo già esistente mostra errore e interrompi l'esecuzione
    foreach ($sondaggi as $sondaggio) {
        if (strcasecmp($sondaggio['Titolo'], $titolo) == 0) {
            if ($utente_o_azienda) {
                header("Location: ../premium_home.php?error=10");
            } else {
                header("Location: ../azienda_home.php?error=10");
            }
            exit;
        }
    }

    crea_sondaggio($pdo, $titolo, $max_utenti, $data_chiusura, $dominio, $cf_azienda_creante, $email_utente_creante, $utente_o_azienda);

    if ($utente_o_azienda) {
        header("Location: ../premium_home.php?success=10");
    } else {
        header("Location: ../azienda_home.php?success=10");
    }
    exit;
}