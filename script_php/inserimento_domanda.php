<?php
require '../config_connessione.php'; // instaura la connessione con il db

function inserisciDomanda($pdo, $testo, $foto, $punteggio, $aperta_chiusa, $cf_azienda_inserente, $email_utente_inserente, $max_caratteri_domanda_aperta, $codice_sondaggio) {
    $inserisci_domanda = $pdo->prepare("CALL InserisciDomanda(:param1, :param2, :param3, :param4, :param5, :param6, :param7, :param8)");
    $inserisci_domanda->bindParam(':param1', $testo, PDO::PARAM_STR);
    $inserisci_domanda->bindParam(':param2', $foto, PDO::PARAM_LOB);
    $inserisci_domanda->bindParam(':param3', $punteggio, PDO::PARAM_INT);
    $inserisci_domanda->bindParam(':param4', $aperta_chiusa, PDO::PARAM_STR);
    $inserisci_domanda->bindParam(':param5', $cf_azienda_inserente, PDO::PARAM_STR);
    $inserisci_domanda->bindParam(':param6', $email_utente_inserente, PDO::PARAM_STR);
    $inserisci_domanda->bindParam(':param7', $max_caratteri_domanda_aperta, PDO::PARAM_INT);
    $inserisci_domanda->bindParam(':param8', $codice_sondaggio, PDO::PARAM_INT);
    $inserisci_domanda->execute();
}

//imposta tipo utente a seconda che il codice amministratore ci sia e sia corretto
//TODO:controllo del file e punteggio
$testo = $_POST['testo'];
$foto = $_POST['foto'];
$punteggio = $_POST['punteggio'];
$cf_azienda_inserente = NULL;
$codice_sondaggio = $_POST['codice_sondaggio'];
if (isset($_POST['checkbox_aperta']) && $_POST['checkbox_aperta'] == 'on') { // La checkbox è stata selezionata
    $max_caratteri_domanda_aperta = $_POST["max_caratteri_domanda_aperta"];
    if (isset($num_caratteri_domanda_aperta)) {
        $aperta_chiusa = "APERTA";
        //il minimo impostabile dall'utente e' 1, quindi se metto 0 posso usarlo nella stored procedure per controllare che la domanda sia aperta
        //InserisciDomanda(Testo varchar(65535), Foto longblob, Punteggio integer, ApertaChiusa ENUM ('APERTA', 'CHIUSA'), CFAziendainserente varchar(16), EmailUtenteinserente varchar(255), MaxCaratteriRisposta integer
        inserisciDomanda($pdo, $testo, $foto, $punteggio, $aperta_chiusa, $cf_azienda_inserente, $_SESSION['email'], $max_caratteri_domanda_aperta, $codice_sondaggio);
        echo "OK";
    }
} else { // La checkbox non è stata selezionata
    $aperta_chiusa = "CHIUSA";
    $max_caratteri_domanda_aperta = 0;
        //InserisciDomanda(Testo varchar(65535), Foto longblob, Punteggio integer, ApertaChiusa ENUM ('APERTA', 'CHIUSA'), CFAziendainserente varchar(16), EmailUtenteinserente varchar(255), MaxCaratteriRisposta integer
        inserisciDomanda($pdo, $testo, $foto, $punteggio, $aperta_chiusa, $cf_azienda_inserente, $_SESSION['email'], $max_caratteri_domanda_aperta, $codice_sondaggio);
}
