<?php

require '../config_connessione.php'; // instaura la connessione con il db

require '../config_conn_mongodb.php'; // instaura la connessione con mongodb, creando db e collezione se non esiste
use MongoDB\BSON\UTCDateTime;

function rimuoviInteresse($pdo, string $email, string $parola_chiave)
{
    //Delete nella tabella Interessato che rimuove la riga con questa email e questa password
    try {
        $rimuovi_interesse = $pdo->prepare("CALL RimuoviInteresse(:param1, :param2)");
        $rimuovi_interesse->bindParam(':param1', $email, PDO::PARAM_STR);
        $rimuovi_interesse->bindParam(':param2', $parola_chiave, PDO::PARAM_STR);
        $rimuovi_interesse->execute();
    } catch (PDOException $e) {
        echo "Errore Stored Procedure: " . $e->getMessage();
        header("Location: logout.php");
        exit;
    }
}

function inserisciInteresse($pdo, string $email, string $parola_chiave, $collezione_log)
{
    //Insert nella tabella Interessato che mette come EmailUtente la mail di sessione e come ParolachiaveDominio il valore di questo foreach
    try {
        $inserisci_interesse = $pdo->prepare("CALL InserisciInteresse(:param1, :param2)");
        $inserisci_interesse->bindParam(':param1', $email, PDO::PARAM_STR);
        $inserisci_interesse->bindParam(':param2', $parola_chiave, PDO::PARAM_STR);
        $inserisci_interesse->execute();
    } catch (PDOException $e) {
        echo "Errore Stored Procedure: " . $e->getMessage();
        header("Location: logout.php");
        exit;
    }
    // Informazione da inserire nella collezione di log
    $informazione_log = array(
        "data" => new MongoDB\BSON\UTCDateTime(),
        "azione" => "Inserimento interesse dominio",
        "dettagli" => array(
            "email_utente" => $email,
            "parola_chiave_dominio" => $parola_chiave
        )
    );

    // Inserimento dell'informazione nella collezione di log
    $collezione_log->insertOne($informazione_log);
}

//COLLEGAMENTO DOMINIO INTERESSE
if (isset($_POST["invia"])) {
    try {
        //array con i domini gia' in precedenza selezionati dall'utente
        $prep_query_interessato = $pdo->prepare('SELECT * FROM Interessato WHERE EmailUtente = :email');
        $prep_query_interessato->bindParam(':email', $_SESSION["email"], PDO::PARAM_STR);
        $prep_query_interessato->execute();
        $domini_salvati_in_passato = $prep_query_interessato->fetchAll(PDO::FETCH_ASSOC);
        //NB: se questa query restituisce un risultato vuoto, ritorna un booleano

        //lista di tutti i domini
        $sql = "CALL MostraDomini()";
        $mostra_domini = $pdo->prepare($sql);
        $mostra_domini->execute();
        $domini = $mostra_domini->fetchAll(PDO::FETCH_ASSOC);
        $mostra_domini->closeCursor(); // chiude il cursore del set di risultati corrente
    } catch (PDOException $e) {
        echo "Errore Stored Procedure: " . $e->getMessage();
        header("Location: logout.php");
        exit;
    }
    $array_parolachiave_tutti_i_domini = array();
    foreach ($domini as $dominio) {
        $parole_chiave = $dominio["Parolachiave"];
        $parole_chiave_array = explode(",", $parole_chiave);
        $array_parolachiave_tutti_i_domini = array_merge($array_parolachiave_tutti_i_domini, $parole_chiave_array);
    }

    //$array_domini_selezionati = $_POST["domini_selezionati"]; //domini selezionati nella index
    $array_domini_selezionati = $_POST["domini_selezionati"] ?? array(); //se $_POST["domini_selezionati"] non è definita, usa un array vuoto come valore predefinito (evito il warning e il non accesso al codice se ho una sola checkbox attiva e la disattivo)
    if (is_array($array_domini_selezionati)) { //se l'utente non seleziona nulla ma clicca il bottone, questo controllo ritorna false
        if (is_array($array_parolachiave_tutti_i_domini)) {
            $array_domini_non_selezionati = array_diff($array_parolachiave_tutti_i_domini, $array_domini_selezionati); //sottrai quelli selezionati a tutti i domini e ottieni un nuovo array
            foreach ($array_domini_selezionati as $chiave => $parola_chiave) {
                //Se il dominio con la sua Parolachiave NON e' già presente nella tabella Interessato, lo inserisci; altrimenti non fai nulla (non inserisci due volte lo stesso)
                $check_domini_salvati_in_passato = false;
                foreach ($domini_salvati_in_passato as $dominio_salvato) {
                    if ($parola_chiave == $dominio_salvato["ParolachiaveDominio"]) {
                        $check_domini_salvati_in_passato = true;
                        break; //mi fermo perché ho trovato la parolachiave uguale quindi non ha senso continuare a cercarla
                    }
                } //se esistono domini selezionati in passato, saranno stati salvati come un array, quindi fai il controllo per non inserirli due volte
                if (!$check_domini_salvati_in_passato) { //se non sono un array vuol dire che in passato l'utente non ha mai selezionato nulla quindi posso inserire tutti quelli ora selezionati senza controlli
                    inserisciInteresse($pdo, $_SESSION["email"], $parola_chiave, $collezione_log);
                    echo "Interesse inserito";
                }
            }
            //Rimozione domini deselezionati (rimozione dei domini non selezionati che corrispondono a domini selezionati in passato)
            foreach ($array_domini_non_selezionati as $chiave => $parola_chiave) {
                foreach ($domini_salvati_in_passato as $dominio_salvato) {
                    if ($parola_chiave == $dominio_salvato["ParolachiaveDominio"]) {
                        rimuoviInteresse($pdo, $_SESSION["email"], $parola_chiave);
                        break;
                    }
                }
            }

            //reindirizza alla pagina index.php
            header("Location: ../index.php");
            exit(); //termina l'esecuzione dello script dopo il reindirizzamento

        } else {
            header("Location: ../index.php");
            exit();
        }
    }
}