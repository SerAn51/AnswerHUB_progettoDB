<?php
require '../config_connessione.php'; // instaura la connessione con il db

function crea_sondaggio($pdo, $titolo, $max_utenti, $data_chiusura, $dominio) {
//Crea sondaggio impostando lo stato APERTO, come data_creazione la data attuale, come CFAziendacreante NULL, come EmailUtentecreante la mail di sessione
$proc_crea_sondaggio = "CALL CreaSondaggio(:param1, :param2, :param3, :param4, :param5, :param6, :param7, :param8)";
//(Titolo, Stato, MaxUtenti, DataCreazione, DataChiusura, ParolachiaveDominio, CFAziendacreante, EmailUtentecreante)
$stato = 'APERTO';
$data_creazione = date('Y-m-d H:i:s', time()); //da timestamp a formato Y-m-d H:i:s usato da MySQL
//NB: $data_chiusura e' gia' nel giusto formato
$null = NULL;

$prep_proc_crea_sondaggio = $pdo->prepare($proc_crea_sondaggio);
$prep_proc_crea_sondaggio->bindParam(':param1', $titolo, PDO::PARAM_STR);
$prep_proc_crea_sondaggio->bindParam(':param2', $stato, PDO::PARAM_STR);
$prep_proc_crea_sondaggio->bindParam(':param3', $max_utenti, PDO::PARAM_INT);
$prep_proc_crea_sondaggio->bindParam(':param4', $data_creazione, PDO::PARAM_STR);
$prep_proc_crea_sondaggio->bindParam(':param5', $data_chiusura, PDO::PARAM_STR);
$prep_proc_crea_sondaggio->bindParam(':param6', $dominio, PDO::PARAM_STR);
$prep_proc_crea_sondaggio->bindParam(':param7', $null, PDO::PARAM_NULL);
$prep_proc_crea_sondaggio->bindParam(':param8', $_SESSION["email"], PDO::PARAM_STR);
$prep_proc_crea_sondaggio->execute();
}

if (isset($_POST["crea"])) {
    $titolo = $_POST["titolo"];
    $max_utenti = $_POST["max_utenti"];
    $data_chiusura = $_POST["data_chiusura"];
    $dominio = $_POST["dominio"];

    crea_sondaggio($pdo, $titolo, $max_utenti, $data_chiusura, $dominio);

    header("Location: ../premium_home.php?success=10");
}
