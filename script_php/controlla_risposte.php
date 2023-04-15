<?php
require '../config_connessione.php'; // instaura la connessione con il db

$codice_sondaggio = $_POST['codice_sondaggio'];

function inserisci_risposta($pdo, $testo, $id_domanda, $email_utente)
{
    $inserisci_risposta = $pdo->prepare("CALL InserisciRisposta(:testo, :id_domanda, :email_utente)");
    $inserisci_risposta->bindParam(':testo', $testo, PDO::PARAM_STR);
    $inserisci_risposta->bindParam(':id_domanda', $id_domanda, PDO::PARAM_INT);
    $inserisci_risposta->bindParam(':email_utente', $email_utente, PDO::PARAM_STR);
    $inserisci_risposta->execute();
    $inserisci_risposta->closeCursor();
}

function inserisci_opzione($pdo, $email_utente, $id_domanda_chiusa, $numero_progressivo_opzione_selezionata)
{
    $inserisci_opzione = $pdo->prepare("CALL InserisciOpzioneRisposta(:email_utente, :id_domanda_chiusa, :numero_progressivo_opzione_selezionata)");
    $inserisci_opzione->bindParam(':email_utente', $email_utente, PDO::PARAM_STR);
    $inserisci_opzione->bindParam(':id_domanda_chiusa', $id_domanda_chiusa, PDO::PARAM_INT);
    $inserisci_opzione->bindParam(':numero_progressivo_opzione_selezionata', $numero_progressivo_opzione_selezionata, PDO::PARAM_INT);
    $inserisci_opzione->execute();
    $inserisci_opzione->closeCursor();
}

if (isset($_POST['invia_risposte'])) {
    // se ho una domanda aperta, devo inserire la risposta in Risposta che prende anche l'id della domanda aperta e l'email dell'utente che l'ha inserita
    //Testo text, IDDomandaaperta integer, EmailUtente varchar(255)
    $risposte_aperte = $_POST['risposte_aperte'];

    // per ogni risposta aperta, recupera id_domanda e risposta in sé e inserisci la risposta nel DB
    foreach ($risposte_aperte as $id_domanda => $testo) {
        // Risposta vuole il testo, l'id della domanda aperta e l'email dell'utente che l'ha scritta (email di sessione)
        inserisci_risposta($pdo, $testo, $id_domanda, $_SESSION['email']);
    }

    $opzioni_selezionate = $_POST['opzioni_selezionate'];

    // per ogni risposta aperta, recupera id_domanda e risposta in sé e inserisci la risposta nel DB
    foreach ($opzioni_selezionate as $id_domanda_chiusa => $numero_progressivo_opzione_selezionata) {
        // Risposta vuole il testo, l'id della domanda aperta e l'email dell'utente che l'ha scritta (email di sessione)
        inserisci_opzione($pdo, $_SESSION['email'], $id_domanda_chiusa, $numero_progressivo_opzione_selezionata);
    }
    
    // uso una variabile di sessione così che in semplice home mostro l'opzione per rispondere o per visualizzare
    $_SESSION['completato_sondaggio_' . $codice_sondaggio] = true;
    header("Location: ../semplice_home.php");
    exit;

}
?>