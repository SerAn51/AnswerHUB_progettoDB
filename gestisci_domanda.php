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
    <link rel="stylesheet" href="stile_css/gestisci_domanda.css">
    <link rel="stylesheet" href="stile_css/bottone_logout.css">
    <link rel="stylesheet" href="stile_css/crea_sondaggio_inputs.css">
    <link rel="stylesheet" href="stile_css/upload_file.css">
    <link rel="stylesheet" href="stile_css/crea_sondaggio_button.css">
    <link rel="stylesheet" href="stile_css/bottone_opzioni.css">
    <link rel="stylesheet" href="stile_css/non_bottone_domanda_aperta.css">
    <link rel="stylesheet" href="stile_css/bottone_elimina_domanda.css">

    <style>
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

    <header class="header">
        <a href="premium_home.php" class="home">
            <button class="logout_btn">
                <p class="paragraph"> Home </p>
                <span class="logout_icon-wrapper">
                    <svg class="logout_icon" width="30px" height="30px" viewBox="0 0 1024 1024" class="icon"
                        version="1.1" xmlns="http://www.w3.org/2000/svg" fill="#000000">
                        <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                        <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g>
                        <g id="SVGRepo_iconCarrier">
                            <path
                                d="M981.4 502.3c-9.1 0-18.3-2.9-26-8.9L539 171.7c-15.3-11.8-36.7-11.8-52 0L70.7 493.4c-18.6 14.4-45.4 10.9-59.7-7.7-14.4-18.6-11-45.4 7.7-59.7L435 104.3c46-35.5 110.2-35.5 156.1 0L1007.5 426c18.6 14.4 22 41.1 7.7 59.7-8.5 10.9-21.1 16.6-33.8 16.6z"
                                fill="#000000"></path>
                            <path
                                d="M810.4 981.3H215.7c-70.8 0-128.4-57.6-128.4-128.4V534.2c0-23.5 19.1-42.6 42.6-42.6s42.6 19.1 42.6 42.6v318.7c0 23.8 19.4 43.2 43.2 43.2h594.8c23.8 0 43.2-19.4 43.2-43.2V534.2c0-23.5 19.1-42.6 42.6-42.6s42.6 19.1 42.6 42.6v318.7c-0.1 70.8-57.7 128.4-128.5 128.4z"
                                fill="#00000000000"></path>
                        </g>
                    </svg>
                </span>
            </button>
        </a>
        <h1>Inserisci domanda</h1>
        <a href="logout.php" class="logout">
            <button class="logout_btn" onclick="return confirm('Confermi logout?')">
                <p class="paragraph"> Logout </p>
                <span class="logout_icon-wrapper">
                    <svg class="logout_icon" width="30px" height="30px" viewBox="0 0 24 24" fill="none"
                        xmlns="http://www.w3.org/2000/svg">
                        <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                        <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g>
                        <g id="SVGRepo_iconCarrier">
                            <path
                                d="M14 4L17.5 4C20.5577 4 20.5 8 20.5 12C20.5 16 20.5577 20 17.5 20H14M3 12L15 12M3 12L7 8M3 12L7 16"
                                stroke="#000000" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                            </path>
                        </g>
                    </svg>
                </span>
            </button>
        </a>
    </header>

    <main class="main">

        <!--CREA UNA NUOVA DOMANDA-->
        <div class="space_inserisci_domanda">
            <form action="script_php/inserimento_domanda.php" method="POST" enctype="multipart/form-data">
                <h1>Inserisci una nuova domanda</h1>
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
                    <div class="input-group">
                        <input type="text" name="testo" id="testo" required autocomplete="off" class="input">
                        <label class="user-label" for="testo">Testo domanda*<label>
                    </div>
                    <br>
                    <!--input box Foto-->
                    <input type="file" name="foto" id="foto">
                    <br>
                    <!--input box Punteggio-->
                    <div class="input-group">
                        <input type="number" min="0" name="punteggio" id="punteggio" required autocomplete="off"
                            class="input">
                        <label class="user-label">Punteggio*</label>
                    </div>
                    <br>
                    <!--checkbox per inserire max caratteri risposta se la domanda e' APERTA, altrimenti si da per scontato sia chiusa-->

                    <label name="label_checkbox_aperta" id="label_checkbox_aperta" for="checkbox_aperta">Spunta se
                        la
                        domanda e' aperta</label>
                    <input type="checkbox" name="checkbox_aperta" id="checkbox_aperta">
                    <br>
                    <!--input box massimo caratteri se risposta aperta-->
                    <div name="inputbox_max_caratteri_domanda_aperta" id="inputbox_max_caratteri_domanda_aperta"
                        class="inputbox">
                        <div class="input-group">
                            <!--TODO: gestire lato utente il max_caratteri-->
                            <input type="number" min="1" max="3000" name="max_caratteri_domanda_aperta"
                                id="max_caratteri_domanda_aperta" class="input">
                            <label class="user-label" for="max_caratteri_domanda_aperta">Max caratteri</label>
                            <!--Mi trovo ad usare un form, invio anche il codice con post così da non dover gestire eventuali cambiamenti di url-->
                            <input type="hidden" name="codice_sondaggio" id="codice_sondaggio"
                                value="<?php echo $codice_sondaggio; ?>">
                        </div>
                        <br>
                    </div>
                    <!--bottone crea domanda-->
                    <button class="crea" type="submit" name="crea" id="crea">
                        Crea
                        <div class="arrow-wrapper">
                            <div class="arrow"></div>

                        </div>
                    </button>
                </form>
            <?php } else {
                    echo "Un utente invitato ha accettato, per questo sondaggio non e' piu' possibile inserire domande";
                } ?>
        </div>

        <!--MOSTRA TUTTE LE DOMANDE-->
        <!--Mostra tutte le domande: lista di domande, le chiuse sono cliccabili e rimandano ad una pagina che mostra le opzioni e il form per inserire opzioni-->
        <div class="space_domande">
            <ul>
                <h1>Domande</h1>
                <!--Eventuale messaggio di successo per domanda rimossa-->
                <?php if (isset($_GET['success']) && $_GET['success'] == 20) {
                    echo "Domanda rimossa con successo"; ?>
                    <br><br>
                <?php } ?>
                <?php if (empty($domande_sondaggio)) {
                    echo "Non ci sono domande, inseriscine una!";
                } else { ?>
                    <p>Passa il mouse sulla domanda
                        <br> per gestire le opzioni
                        <br><br>
                        Se la domanda e' aperta viene mostrato il massimo numero di caratteri per la risposta
                    </p>
                    <h3>Elenco domande:</h3>
                    <div class="lista_scrollabile">
                        <?php foreach ($domande_sondaggio as $domanda) { ?>
                            <li>
                                <form action="script_php/rimuovi_domanda.php" method="POST">
                                    <!--Nome del sondaggio-->
                                    <?php if ($domanda["ApertaChiusa"] == "CHIUSA") { ?>
                                        <a
                                            href="gestisci_opzioni.php?cod_sondaggio=<?php echo $codice_sondaggio; ?>&id_domanda=<?php echo $domanda['ID']; ?>">
                                            <div class="domanda_chiusa">
                                                <span class="tooltip">Gestisci opzioni</span>
                                                <span>
                                                    <label class="lista_scrollabile_orizzontalmente">
                                                        <?php echo $domanda['Testo']; ?>
                                                    </label>
                                                </span>
                                            </div>
                                        </a>
                                    <?php } else if ($domanda["ApertaChiusa"] == "APERTA") { ?>
                                            <?php
                                            try {
                                                $max_caratteri_risposta = $pdo->prepare("SELECT * FROM DomandaAperta WHERE ID = ?");
                                                $max_caratteri_risposta->execute([$domanda['ID']]);
                                                $max_caratteri = $max_caratteri_risposta->fetch(PDO::FETCH_ASSOC);
                                            } catch (PDOException $e) {
                                                echo "Errore Stored Procedure: " . $e->getMessage();
                                                header("Location: index.php");
                                                exit;
                                            }
                                            ?>
                                            <div class="domanda_aperta">
                                                <span class="tooltip">Max caratteri:
                                                <?php echo $max_caratteri['MaxCaratteriRisposta']; ?>
                                                </span>
                                                <span>
                                                    <label class="lista_scrollabile_orizzontalmente">
                                                    <?php echo $domanda['Testo']; ?>
                                                    </label>
                                                </span>
                                            </div>
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

                                    // se la query restituisce almeno una riga, vuol dire che ho invitato almeno un utente quindi non posso piu' rimuovere opzioni
                                    if (($inviti && count($inviti) > 0)) {
                                        foreach ($inviti as $invito) {
                                            if ($invito['Esito'] == "ACCETTATO") {
                                                $tutti_sospesi = false;
                                                break;
                                            }
                                        }
                                    }

                                    // se non ci sono invitati la variabile booleana non e' stata modificata quindi accedo,
                                    // se tutti gli invitati sono con Esito='Sospeso' oppure con Esito='Rifiutato' ho eseguito i controlli ma la variabile booleana non e' stata modificata, quindi posso eliminare il sondaggio
                                    if ($tutti_sospesi) { ?>
                                        <input type="hidden" name="codice_sondaggio" id="codice_sondaggio"
                                            value="<?php echo $codice_sondaggio ?>">
                                        <input type="hidden" name="id_domanda" id="id_domanda" value="<?php echo $domanda['ID'] ?>">

                                        <button type="submit" name="bottone" id="bottone" class="noselect"><span
                                                class="text">Elimina</span><span class="icon"><svg
                                                    xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
                                                    <path
                                                        d="M24 20.188l-8.315-8.209 8.2-8.282-3.697-3.697-8.212 8.318-8.31-8.203-3.666 3.666 8.321 8.24-8.206 8.313 3.666 3.666 8.237-8.318 8.285 8.203z">
                                                    </path>
                                                </svg></span>
                                        </button>
                                        <!--<input type="submit" name="bottone" id="bottone" value="Elimina">-->
                                    <?php }
                                    ?>
                                </form>
                            </li>
                        <?php } ?>
                    </div>
                <?php } ?>
            </ul>

        </div>
    </main>

    <section class="footer">

    </section>
</body>

</html>