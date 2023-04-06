<?php
require '../config_connessione.php'; // instaura la connessione con il db
//EmailUtente, CodiceSondaggio, CFAziendainvitante, EmailUtenteinvitante)
function inserisci_invito($pdo, $email_utente, $codice_sondaggio, $email_utente_invitante)
{
    $CF_azienda_invitante = NULL;

    $proc_inserisci_invito = $pdo->prepare("CALL InserisciInvito(:param1, :param2, :param3, :param4)");
    $proc_inserisci_invito->bindParam(':param1', $email_utente, PDO::PARAM_STR);
    $proc_inserisci_invito->bindParam(':param2', $codice_sondaggio, PDO::PARAM_INT);
    $proc_inserisci_invito->bindParam(':param3', $CF_azienda_invitante, PDO::PARAM_NULL);
    $proc_inserisci_invito->bindParam(':param4', $email_utente_invitante, PDO::PARAM_STR);
    $proc_inserisci_invito->execute();
}

if (isset($_POST['invita'])) {
    // Controllo se almeno una checkbox è stata selezionata
    if (!empty($_POST['utenti_selezionati'])) {
        $utenti_selezionati = $_POST['utenti_selezionati'];
        $codice_sondaggio = $_POST['codice_sondaggio'];
        // Utilizzo l'array $utenti_selezionati per inviare gli inviti
        foreach ($utenti_selezionati as $utente_selezionato) {
            // Invio invito all'utente con email $utente_selezionato
            // Codice per l'invio dell'invito
            inserisci_invito($pdo, $utente_selezionato, $codice_sondaggio, $_SESSION['email']);
        }
        //necessario specificare anche il codice sondaggio per non avere true al primo controllo di inviti_sondaggio.php e venire reindirizzati a premium_home.php
        header("Location: ../inviti_sondaggio.php?cod_sondaggio='$codice_sondaggio'&success=10");
        exit;
    } else {
        header("Location: ../inviti_sondaggio.php?cod_sondaggio='$codice_sondaggio'&error=10");
        exit;
    }
}
