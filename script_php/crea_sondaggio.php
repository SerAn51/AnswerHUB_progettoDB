<?php
require '../config_connessione.php'; // instaura la connessione con il db

function crea_sondaggio($pdo, $titolo, $max_utenti, $data_chiusura, $dominio)
{
    $stato = 'APERTO';
    $data_creazione = date('Y-m-d H:i:s', time()); //da timestamp a formato Y-m-d H:i:s usato da MySQL
//NB: $data_chiusura e' gia' nel giusto formato
    $null = NULL;

    //Crea sondaggio impostando lo stato APERTO, come data_creazione la data attuale, come CFAziendacreante NULL, come EmailUtentecreante la mail di sessione
    $proc_crea_sondaggio = $pdo->prepare("CALL CreaSondaggio(:param1, :param2, :param3, :param4, :param5, :param6, :param7, :param8)");
    $proc_crea_sondaggio->bindParam(':param1', $titolo, PDO::PARAM_STR);
    $proc_crea_sondaggio->bindParam(':param2', $stato, PDO::PARAM_STR);
    $proc_crea_sondaggio->bindParam(':param3', $max_utenti, PDO::PARAM_INT);
    $proc_crea_sondaggio->bindParam(':param4', $data_creazione, PDO::PARAM_STR);
    $proc_crea_sondaggio->bindParam(':param5', $data_chiusura, PDO::PARAM_STR);
    $proc_crea_sondaggio->bindParam(':param6', $dominio, PDO::PARAM_STR);
    $proc_crea_sondaggio->bindParam(':param7', $null, PDO::PARAM_NULL);
    $proc_crea_sondaggio->bindParam(':param8', $_SESSION["email"], PDO::PARAM_STR);
    $proc_crea_sondaggio->execute();
}

if (isset($_POST["crea"])) {
    $titolo = $_POST["titolo"];
    $max_utenti = $_POST["max_utenti"];
    $data_chiusura = $_POST["data_chiusura"];
    $dominio = $_POST["dominio"];
    $cf_azienda_creante = NULL;

    //Un utente premium non può creare due sondaggi con lo stesso nome, questo perche'
    //lato utente che risponde ai sondaggi si potrebbe creare confusione, sono ammessi sondaggi con lo stesso nome a patto che abbiano creatore diverso
    $proc_mostra_sondaggi = $pdo->prepare("CALL MostraSondaggi(:param1, :param2)");
    $proc_mostra_sondaggi->bindParam(':param1', $_SESSION['email'], PDO::PARAM_STR);
    $proc_mostra_sondaggi->bindParam(':param2', $cf_azienda_creante, PDO::PARAM_NULL);
    $proc_mostra_sondaggi->execute();
    $sondaggi = $proc_mostra_sondaggi->fetchAll(PDO::FETCH_ASSOC);
    $proc_mostra_sondaggi->closeCursor();

    foreach ($sondaggi as $sondaggio) {
        if ($sondaggio['Titolo'] == $titolo) {
            header("Location: ../premium_home.php?error=10");
            exit;
        }
    }

    crea_sondaggio($pdo, $titolo, $max_utenti, $data_chiusura, $dominio);

    header("Location: ../premium_home.php?success=10");
    exit;
}