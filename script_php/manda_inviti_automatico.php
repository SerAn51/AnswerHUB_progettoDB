<?php

require '../config_connessione.php'; // instaura la connessione con il db

require '../config_conn_mongodb.php'; // instaura la connessione con mongodb, creando db e collezione se non esiste
use MongoDB\BSON\UTCDateTime;

function inserisci_invito_automaticamente($pdo, $email_utente, $codice_sondaggio, $cf_azienda_invitante, $collezione_log)
{
    $email_utente_invitante = NULL;

    $proc_inserisci_invito = $pdo->prepare("CALL InserisciInvito(:param1, :param2, :param3, :param4)");
    $proc_inserisci_invito->bindParam(':param1', $email_utente, PDO::PARAM_STR);
    $proc_inserisci_invito->bindParam(':param2', $codice_sondaggio, PDO::PARAM_INT);
    $proc_inserisci_invito->bindParam(':param3', $cf_azienda_invitante, PDO::PARAM_STR);
    $proc_inserisci_invito->bindParam(':param4', $email_utente_invitante, PDO::PARAM_NULL);
    $proc_inserisci_invito->execute();

    // Informazione da inserire nella collezione di log
    $informazione_log = array(
        "data" => new MongoDB\BSON\UTCDateTime(),
        "azione" => "Inserimento nuovo invito azienda",
        "dettagli" => array(
            "email_utente" => $email_utente,
            "codice_sondaggio" => $codice_sondaggio,
            "cf_azienda_invitante" => $cf_azienda_invitante
        )
    );

    // Inserimento dell'informazione nella collezione di log
    $collezione_log->insertOne($informazione_log);
}

if (isset($_POST["invita"])) {
    $codice_sondaggio = $_POST['codice_sondaggio'];

    // idea: prendo sondaggio join interessato on ParolachiaveDominio filtrando per codice_sondaggio
    // il risultato e' una lista di utenti interessati al dominio a cui appartiene questo sondaggio
    // prendo le mail e le inserisco in invito

    $mostra_utenti_interessati = $pdo->prepare("CALL MostraUtentiInteressati(:param1)");
    $mostra_utenti_interessati->bindParam(':param1', $codice_sondaggio, PDO::PARAM_INT);
    $mostra_utenti_interessati->execute();
    $utenti_interessati = $mostra_utenti_interessati->fetchAll(PDO::FETCH_ASSOC);
    $mostra_utenti_interessati->closeCursor();

    // Ritorna un array non vuoto se esiste almeno una domanda
    $check_domande = $pdo->prepare("SELECT * FROM Domanda JOIN ComponenteSondaggioDomanda ON ID = IDDomanda WHERE CodiceSondaggio = :codice_sondaggio");
    $check_domande->bindParam(':codice_sondaggio', $codice_sondaggio, PDO::PARAM_INT);
    $check_domande->execute();
    $domande = $check_domande->fetchAll();
    $check_domande->closeCursor();

    //  Ritorna un array delle domande chiuse del sondaggio
    $check_domande_chiuse = $pdo->prepare("SELECT * FROM DomandaChiusa JOIN Domanda ON DomandaChiusa.ID = Domanda.ID JOIN ComponenteSondaggioDomanda ON Domanda.ID = ComponenteSondaggioDomanda.IDDomanda WHERE CodiceSondaggio = :codice_sondaggio");
    $check_domande_chiuse->bindParam(':codice_sondaggio', $codice_sondaggio, PDO::PARAM_INT);
    $check_domande_chiuse->execute();
    $domande_chiuse = $check_domande_chiuse->fetchAll();
    $check_domande_chiuse->closeCursor();

    //Controlla se esistono domande per il sondaggio, altrimenti notifica l'utente e non rendere disponibile l'invio di inviti
    $controllo = true;
    if (empty($domande)) {
        echo "Questo sondaggio non ha domande, aggiungi delle domande";
        $controllo = false;
    } else {
        if (!empty($domande_chiuse)) {
            //Cicla tutte le domande chiuse, se ne esiste una che non ha opzioni, interrompe e mostra un messaggio di errore
            foreach ($domande_chiuse as $domanda_chiusa) {
                //Ritorna un array delle opzioni della domanda data in input
                $id_domanda_chiusa = $domanda_chiusa['IDDomanda'];
                $check_opzioni_domanda = $pdo->prepare("SELECT * FROM Opzione WHERE IDDomandachiusa = :id_domanda_chiusa");
                $check_opzioni_domanda->bindParam(':id_domanda_chiusa', $id_domanda_chiusa, PDO::PARAM_INT);
                $check_opzioni_domanda->execute();
                $opzioni_domanda = $check_opzioni_domanda->fetchAll();
                $check_opzioni_domanda->closeCursor();
                if (empty($opzioni_domanda)) {
                    echo 'La domanda "' . $domanda_chiusa['Testo'] . '" non ha opzioni, aggiungine almeno una';
                    $controllo = false;
                }
            }
        }

        // Se i controlli vengono passati (la variabile $controllo non e' stata modificata in false), procedi
        if ($controllo) {
            // se non esistono utenti con interessati al dominio del sondaggio, mostra messaggio
            //(lo mostri solo se sono passati i controlli precedenti sull'esistenza di domande e opzioni per domande chiuse-->

            if (count($utenti_interessati) == 0) {
                echo "Non ci sono utenti interessati al dominio di questo sondaggio (non sono considerati eventuali utenti interessati e gia' invitati)";
            } else {

                $mostra_max_utenti = $pdo->prepare("SELECT MaxUtenti FROM Sondaggio WHERE Codice = :codice");
                $mostra_max_utenti->bindParam(':codice', $codice_sondaggio, PDO::PARAM_INT);
                $mostra_max_utenti->execute();
                $riga_max_utenti = $mostra_max_utenti->fetch(PDO::FETCH_ASSOC);
                $mostra_max_utenti->closeCursor();
                $max_utenti = $riga_max_utenti['MaxUtenti']; //salva l'intero

                if (isset($utenti_interessati) && is_array($utenti_interessati)) {
                    foreach ($utenti_interessati as $utente_interessato) {
                        // gli utenti verranno invitati in ordine di aggiunta al db, cioè in ordine di chi ha per primo mostrato intresse per il sondaggio (politica fair)
                        $email_utente = $utente_interessato['EmailUtente'];
                        $cf_azienda_invitante = $_SESSION['cf_azienda'];
                        inserisci_invito_automaticamente($pdo, $email_utente, $codice_sondaggio, $cf_azienda_invitante, $collezione_log);
                        $count++;
                        if ($count == $max_utenti) {
                            echo "utenti invitati";
                            header("Location: ../azienda_home.php");
                            exit;
                        }
                    }
                    // se non ho raggiunto il massimo ma ho invitato tutti gli utenti disponibili, comunque torno alla home
                    header("Location: ../azienda_home.php");
                    exit;
                }
            }
        }
    }
}