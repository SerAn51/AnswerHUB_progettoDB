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

//FIXME: fa accettare l'invito anche se il numero massimo di utenti per sondaggio è già stato raggiunto
//3 alternative:
//alt_1 - effettua qui un ulteriore controllo verificare che il sondaggio è ancora aperto
//alt_2 - quando il premium invita, non può selezionare più di max_utenti checkbox. Se non raggiunge il max_utenti e torna in un secondo momento, devo poter selezionare il nuovo massimo (max_utenti - n_utenti_gia_invitati)
//alt_3 - SCELTO QUESTA -> quando invia il form, controlla che gli elementi dell'array siamo <= max_utenti, se si procede altrimenti ritorna un errore. Se non raggiunge il max_utenti e torna in un secondo momento, devo poter selezionare il nuovo massimo (max_utenti - n_utenti_gia_invitati)

if (isset($_POST['invita'])) {
    $codice_sondaggio = $_POST['codice_sondaggio'];

    // Controllo che il numero di elementi selezionati sia <= di max_utenti
    $mostra_max_utenti = $pdo->prepare("SELECT MaxUtenti FROM Sondaggio WHERE Codice = :codice");
    $mostra_max_utenti->bindParam(':codice', $codice_sondaggio, PDO::PARAM_INT);
    $mostra_max_utenti->execute();
    $riga_max_utenti = $mostra_max_utenti->fetch(PDO::FETCH_ASSOC);
    $mostra_max_utenti->closeCursor();
    $max_utenti_sondaggio = $riga_max_utenti['MaxUtenti']; //salva l'intero

    // conta il numero di utenti già invitati (in passato) per questo sondaggio
    $conta_utenti_invitati = $pdo->prepare("SELECT COUNT(*) AS NumUtentiInvitati FROM Invito WHERE CodiceSondaggio = :codice");
    $conta_utenti_invitati->bindParam(':codice', $codice_sondaggio, PDO::PARAM_INT);
    $conta_utenti_invitati->execute();
    $riga_numero_utenti_invitati = $conta_utenti_invitati->fetch(PDO::FETCH_ASSOC);
    $conta_utenti_invitati->closeCursor();
    $numero_utenti_invitati = $riga_numero_utenti_invitati['NumUtentiInvitati'];

    $max_utenti_selezionabili = $max_utenti_sondaggio - $numero_utenti_invitati;

    if ($max_utenti_selezionabili <= 0) {
        header("Location: ../inviti_sondaggio.php?cod_sondaggio=$codice_sondaggio&error=20");
        exit;
    } else {
        // Controllo se almeno una checkbox è stata selezionata
        if (!empty($_POST['utenti_selezionati'])) {
            $utenti_selezionati = $_POST['utenti_selezionati'];

            if ($max_utenti_selezionabili < count($utenti_selezionati)) {
                header("Location: ../inviti_sondaggio.php?cod_sondaggio=$codice_sondaggio&error=30&num_selezionabili=$max_utenti_selezionabili");
                exit;
            } else {
                // Utilizzo l'array $utenti_selezionati per inviare gli inviti
                foreach ($utenti_selezionati as $utente_selezionato) {
                    // Invio invito all'utente con email $utente_selezionato
                    // Codice per l'invio dell'invito
                    inserisci_invito($pdo, $utente_selezionato, $codice_sondaggio, $_SESSION['email']);
                }
                //necessario specificare anche il codice sondaggio per non avere true al primo controllo di inviti_sondaggio.php e venire reindirizzati a premium_home.php
                header("Location: ../inviti_sondaggio.php?cod_sondaggio=$codice_sondaggio&success=10");
                exit;
            }
        } else {
            header("Location: ../inviti_sondaggio.php?cod_sondaggio=$codice_sondaggio&error=10");
            exit;
        }
    }
}
