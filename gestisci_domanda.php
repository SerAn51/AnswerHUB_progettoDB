<?php
try {
    require 'config_connessione.php'; // instaura la connessione con il db
    $codice_sondaggio = $_GET['cod_sondaggio'];

    // controllo per evitare che si cambi url e si faccia l'accesso ad un sondaggio di un altro utente premium/azienda, al massimo se cambio url per il get del codice posso mettere il codice di un sondaggio da me (utente premium/azienda) gestito:
    // se email non e' vuota vuol dire che ho richiamato gestisci_domanda come utente premium
    if (!(empty($_SESSION["email"]))) {
        $check_sondaggio = $pdo->prepare("SELECT Codice FROM Sondaggio WHERE EmailUtentecreante = :email AND Codice = :codice");
        $check_sondaggio->bindParam(':email', $_SESSION['email'], PDO::PARAM_STR);
        // altrimenti l'ho richiamato come azienda
    } else if (!(empty($_SESSION["cf_azienda"]))) {
        $check_sondaggio = $pdo->prepare("SELECT Codice FROM Sondaggio WHERE CFAziendacreante = :cf_azienda AND Codice = :codice");
        $check_sondaggio->bindParam(':cf_azienda', $_SESSION['cf_azienda'], PDO::PARAM_STR);
    } else {
        // errore nel caso in cui non esiste ne' email ne' cf_azienda sono settati
        die("Errore: utente non loggato");
    }
    $check_sondaggio->bindParam(':codice', $codice_sondaggio, PDO::PARAM_INT);
    $check_sondaggio->execute();
    $sondaggio = $check_sondaggio->fetch(PDO::FETCH_ASSOC);
    $check_sondaggio->closeCursor();
    if (!$sondaggio) {
        header("Location: premium_home.php");
        exit;
    }

    $mostra_domande_sondaggio = $pdo->prepare("CALL MostraDomande(:codice)");
    $mostra_domande_sondaggio->bindParam(':codice', $codice_sondaggio, PDO::PARAM_INT);
    $mostra_domande_sondaggio->execute();
    $domande_sondaggio = $mostra_domande_sondaggio->fetchAll(PDO::FETCH_ASSOC);
    $mostra_domande_sondaggio->closeCursor();


    $check_inviti = $pdo->prepare("SELECT * FROM Invito WHERE CodiceSondaggio = :codice_sondaggio");
    $check_inviti->bindParam(':codice_sondaggio', $codice_sondaggio, PDO::PARAM_INT);
    $check_inviti->execute();
    $inviti = $check_inviti->fetchAll(PDO::FETCH_ASSOC);
    $check_inviti->closeCursor();
} catch (PDOException $e) {
    echo "Errore Stored Procedure: " . $e->getMessage();
    header("Location: index.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AnswerHUB | Inserisci domanda</title>

    <link rel="icon" type="image/png" href="images/favicon.png">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="stile_css/checkbox_style.css">

    <style>
        * {
            font-family: 'Poppins', sans-serif;
            background: #0c2840;
            color: #f3f7f9;
        }

        .space {
            border: 2px solid #f3f7f9;
            border-radius: 30px;
            display: flex;
            justify-content: center;
            text-align: center;
            width: auto;
            padding: 10px;
            margin: 20px;
        }

        li {
            list-style: none;
        }

        #inputbox_max_caratteri_domanda_aperta {
            display: none;
        }

        #checkbox_aperta:checked~#inputbox_max_caratteri_domanda_aperta {
            display: block;
            transition: .5s;
        }
    </style>
</head>

<body>

    <!--MOSTRA TUTTE LE DOMANDE-->
    <div class="space">
        <!--Mostra tutte le domande: lista di domande, le chiuse sono cliccabili e rimandano ad una pagina che mostra le opzioni e il form per inserire opzioni-->
        <h2>Domande</h2>
        <!--Eventuale messaggio di successo-->
        <?php if (isset($_GET['success']) && $_GET['success'] == 20) {
            echo "Domanda rimossa con successo";
        } ?>
        <ul>
            <?php foreach ($domande_sondaggio as $domanda) { ?>
                <li>
                    <form action="script_php/rimuovi_domanda.php" method="POST">
                        <!--Nome del sondaggio-->
                        <?php if ($domanda["ApertaChiusa"] == "CHIUSA") { ?>
                            <label for="bottone">
                                <a
                                    href="gestisci_opzioni.php?cod_sondaggio=<?php echo $codice_sondaggio; ?>&id_domanda=<?php echo $domanda['ID']; ?>"><?php echo $domanda['Testo']; ?></a>
                            </label>
                        <?php } else if ($domanda["ApertaChiusa"] == "APERTA") { ?>
                            <?php echo $domanda['Testo']; ?>
                        <?php } ?>
                        <!--Che sia domanda chiusa o aperta, mostro un bottone per rimuovere la domanda-->
                        <!--Nel momento in cui rimuovo domanda, mi si deve rimuovere la reference in:
                        - ComponenteSondaggioDomanda
                        - DomandaAperta o DomandaChiusa
                        - In Opzione se eliminato una CHIUSA
                        Questo viene gia' gestito dalle foreign key del db-->
                        <?php
                        // se non ci sono utenti invitati mostra il bottone per eliminare, se ci sono mostra i bottoni solo se nessuno ha ancora accettato l'invito
                        $tutti_sospesi = true;

                        // se la query restituisce almeno una riga, vuol dire che ho invitato almeno un utente quindi non posso piu' rimuovere opzioni-->
                        if (($inviti && count($inviti) > 0)) {
                            foreach ($inviti as $invito) {
                                if ($invito['Esito'] == "ACCETTATO") {
                                    $tutti_sospesi = false;
                                    break;
                                }
                            }
                        } ?>
                        <?php

                        // se non ci sono invitati la variabile booleana non e' stata modificata quindi accedo,
                        // se tutti gli invitati sono con Esito='Sospeso' oppure con Esito='Rifiutato' ho eseguito i controlli ma la variabile booleana non e' stata modificata, quindi posso eliminare il sondaggio
                        if ($tutti_sospesi) {
                            ?>
                            <input type="hidden" name="codice_sondaggio" id="codice_sondaggio"
                                value="<?php echo $codice_sondaggio ?>">
                            <input type="hidden" name="id_domanda" id="id_domanda" value="<?php echo $domanda['ID'] ?>">
                            <input type="submit" name="bottone" id="bottone" value="Elimina">
                        <?php } ?>
                    </form>
                </li>
            <?php } ?>
        </ul>
    </div>

    <!--CREA UNA NUOVA DOMANDA-->
    <div class="space">
        <h2>Inserisci una nuova domanda</h2>

        <?php
        // se non ci sono utenti invitati dai la possibilita' di inserire una nuova domanda;
        // se ci sono, continua a dare la possibilità solo se nessuno ha ancora accettato l'invito
        $tutti_sospesi_due = true;

        // se la query restituisce almeno una riga, vuol dire che ho invitato almeno un utente quindi non posso piu' aggiungere domande-->
        if (($inviti && count($inviti) > 0)) {
            foreach ($inviti as $invito) {
                if ($invito['Esito'] == "ACCETTATO") {
                    $tutti_sospesi_due = false;
                    break;
                }
            }
        } ?>
        <?php

        // se non ci sono invitati la variabile booleana non e' stata modificata quindi posso aggiungere una domanda,
        // se tutti gli invitati sono con Esito='Sospeso' oppure con Esito='Rifiutato' ho eseguito i controlli ma la variabile booleana non e' stata modificata, quindi posso aggiungere una domanda
        if ($tutti_sospesi_due) {
            ?>
            <!--L'inserimento deve avvenire in Domanda, in ComponenteSondaggioDomanda e
            se APERTA anche in DomandaAperta, altrimenti in DomandaChiusa
            (in questo caso, si devono inserire le opzioni...vedi space "Domande"-->
            <p>I campi con * sono obbligatori</p>
            <form action="script_php/inserimento_domanda.php" method="POST" enctype="multipart/form-data">
                <?php
                if ((isset($_GET['error']))) {
                    if ($_GET['error'] == 10) {
                        echo "Tipo immagine non valido, supportati: PNG, JPEG";
                    } else if ($_GET['error'] == 20) {
                        echo "Domanda con stesso testo gia' esistente";
                    }
                } else if (isset($_GET['success']) && $_GET['success'] == 10) {
                    echo "Domanda inserita con successo";
                }
                ?>
                <!--input box Testo-->
                <input type="text" name="testo" id="testo" required>
                <label for="testo">Testo domanda*<label>
                        <!--input box Foto-->
                        <input type="file" name="foto" id="foto">
                        <label for="foto">Foto</label>
                        <!--input box Punteggio-->
                        <input type="number" min="0" name="punteggio" id="punteggio">
                        <label for="punteggio">Punteggio</label>
                        <!--checkbox per inserire max caratteri risposta se la domanda e' APERTA, altrimenti si da per scontato sia chiusa-->
                        <label name="label_checkbox_aperta" id="label_checkbox_aperta" for="checkbox_aperta">Spunta se la
                            domanda e' aperta</label>
                        <input type="checkbox" name="checkbox_aperta" id="checkbox_aperta">
                        <!--input box Codice amministratore-->
                        <div name="inputbox_max_caratteri_domanda_aperta" id="inputbox_max_caratteri_domanda_aperta"
                            class="inputbox">
                            <!--TODO: gestire lato utente il max_caratteri-->
                            <input type="number" min="1" max="3000" name="max_caratteri_domanda_aperta"
                                id="max_caratteri_domanda_aperta">
                            <label for="max_caratteri_domanda_aperta">Numero massimo di caratteri</label>
                            <!--Mi trovo ad usare un form, invio anche il codice con post così da non dover gestire eventuali cambiamenti di url-->
                            <input type="hidden" name="codice_sondaggio" id="codice_sondaggio"
                                value="<?php echo $codice_sondaggio; ?>">
                        </div>
                        <!--bottone crea domanda-->
                        <input type="submit" name="crea" id="crea" value="Crea">
            </form>
        <?php } else {
            echo "Un utente invitato ha accettato, per questo sondaggio non e' piu' possibile inserire domande";
        } ?>

    </div>

    <a href="premium_home.php">Torna alla home</a>
    <a href="logout.php">Effettua il logout</a>
</body>

</html>