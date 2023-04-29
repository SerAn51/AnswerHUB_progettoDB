<?php
try {
    require 'config_connessione.php'; // instaura la connessione con il db

    $codice_sondaggio = $_GET['cod_sondaggio'];
    $id_domanda = $_GET['id_domanda'];

    // controllo per evitare che si cambi url e si faccia l'accesso ad un sondaggio di un altro utente premium/azienda, al massimo se cambio url per il get del codice posso mettere il codice di un sondaggio da me (utente premium/azienda) gestito:
// se email non e' vuota vuol dire che ho richiamato gestisci_domanda come utente premium
    if (!(empty($_SESSION["email"]))) {
        $check_sondaggio = $pdo->prepare("SELECT Codice FROM Sondaggio WHERE EmailUtentecreante = :email AND Codice = :codice");
        $check_sondaggio->bindParam(':email', $_SESSION['email'], PDO::PARAM_STR);
        // altrimenti l'ho richiamato come azienda
    } else if (!(empty($_SESSION["cf_azienda"]))) {
        $check_sondaggio = $pdo->prepare("SELECT Codice FROM Sondaggio WHERE CFAziendacreante = :cf_azienda AND Codice = :codice");
        $check_sondaggio->bindParam(':cf_azienda', $_SESSION['cf_azienda'], PDO::PARAM_STR);
    }
    $check_sondaggio->bindParam(':codice', $codice_sondaggio, PDO::PARAM_INT);
    $check_sondaggio->execute();
    $sondaggio = $check_sondaggio->fetch(PDO::FETCH_ASSOC);
    $check_sondaggio->closeCursor();
    if (!$sondaggio) {
        header("Location: index.php");
        exit;
    }

    // controllo per evitare che si cambi url e si faccia l'accesso ad una domanda di un altro utente premium/azienda, al massimo se cambio url per il get dell'ID della domanda posso mettere quella di una domanda chiusa da me (utente premium/azienda) gestita:
    $aperta_chiusa = "CHIUSA";
    if (!(empty($_SESSION["email"]))) {
        $check_domanda = $pdo->prepare("SELECT ID FROM Domanda WHERE EmailUtenteinserente = :email AND ID = :id AND ApertaChiusa = :aperta_chiusa");
        $check_domanda->bindParam(':email', $_SESSION['email'], PDO::PARAM_STR);
    } else if (!(empty($_SESSION["cf_azienda"]))) {
        $check_domanda = $pdo->prepare("SELECT ID FROM Domanda WHERE CFAziendainserente = :cf_azienda AND ID = :id AND ApertaChiusa = :aperta_chiusa");
        $check_domanda->bindParam(':cf_azienda', $_SESSION['cf_azienda'], PDO::PARAM_STR);
    }
    $check_domanda->bindParam(':id', $id_domanda, PDO::PARAM_INT);
    $check_domanda->bindParam(':aperta_chiusa', $aperta_chiusa, PDO::PARAM_STR);
    $check_domanda->execute();
    $domanda = $check_domanda->fetch(PDO::FETCH_ASSOC);
    $check_domanda->closeCursor();
    if (!$domanda) {
        header("Location: index.php");
        exit;
    }

    $mostra_opzioni_domanda = $pdo->prepare("CALL MostraOpzioni(:id_domanda)");
    $mostra_opzioni_domanda->bindParam(':id_domanda', $id_domanda, PDO::PARAM_INT);
    $mostra_opzioni_domanda->execute();
    $opzioni_domanda = $mostra_opzioni_domanda->fetchAll(PDO::FETCH_ASSOC);
    $mostra_opzioni_domanda->closeCursor();


    $check_inviti = $pdo->prepare("SELECT * FROM Invito WHERE CodiceSondaggio = :codice_sondaggio");
    $check_inviti->bindParam(':codice_sondaggio', $codice_sondaggio, PDO::PARAM_INT);
    $check_inviti->execute();
    $inviti = $check_inviti->fetchAll();
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
    <title>AnswerHUB | Gestisci opzioni</title>

    <link rel="icon" type="image/png" href="images/favicon.png">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="stile_css/checkbox_style.css">
    <link rel="stylesheet" href="stile_css/gestisci_opzioni.css">
    <link rel="stylesheet" href="stile_css/bottone_logout.css">
    <link rel="stylesheet" href="stile_css/crea_sondaggio_inputs.css">
    <link rel="stylesheet" href="stile_css/upload_file.css">
    <link rel="stylesheet" href="stile_css/crea_sondaggio_button.css">
    <link rel="stylesheet" href="stile_css/bottone_opzioni.css">
    <link rel="stylesheet" href="stile_css/non_bottone_domanda_aperta.css">
    <link rel="stylesheet" href="stile_css/bottone_elimina_domanda.css">

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
        <h1>Gestisci opzioni</h1>
        <a href="gestisci_domanda.php?cod_sondaggio=<?php echo $codice_sondaggio ?>" class="logout">
            <button class="logout_btn">
                <p class="paragraph"> Indietro </p>
                <span class="logout_icon-wrapper">
                    <svg class="logout_icon" width="30px" height="30px" xmlns="http://www.w3.org/2000/svg" version="1.0"
                        viewBox="0 0 1000 1000">
                        <path fill="#001D3B" fill-opacity=".1"
                            d="m494.5 125.2-5 5.3 5.3-5c4.8-4.6 5.7-5.5 4.9-5.5-.1 0-2.5 2.4-5.2 5.2zm-11 11-5 5.3 5.3-5c4.8-4.6 5.7-5.5 4.9-5.5-.1 0-2.5 2.4-5.2 5.2zm-8.6 8.5c-1.3 1.6-1.2 1.7.4.4 1.6-1.3 2.1-2.1 1.3-2.1-.2 0-1 .8-1.7 1.7z" />
                        <path fill="#003"
                            d="m404.5 216-96 96 96.3 96.2 96.2 96.3 22.6-22.6 22.6-22.6-58.1-57.5c-32-31.6-58.1-57.8-58.1-58.2 0-1 121.5-.7 134 .4 40.1 3.5 78.2 18.6 109 43 8.6 6.8 24.8 22.8 32 31.5 22.6 27.6 37.8 62 43.7 98.6 2.3 14.4 2.3 44.8 0 59.4-9.2 58.9-41.9 109.4-91.7 141.8-17.5 11.3-39.8 21.1-59.9 26.2-24.5 6.2-11.6 5.8-209.8 6.2l-181.3.4v63l183.8-.4 183.7-.3 13-2.2c18.1-3.1 35.8-7.6 50.5-12.9 84-30.4 147.2-100.4 169-187.3 5.5-21.9 7.3-37.9 7.3-64s-1.8-42.1-7.3-64c-28.1-111.9-123.3-192.4-239-202-6.3-.5-39.8-1-74.5-1h-63l35.8-35.8c19.7-19.7 35.5-36.1 35.2-36.5-.3-.4-.2-.5.2-.2s10.7-9.5 23-21.7l22.3-22.3-21.7-21.7c-12-12-22.2-21.8-22.8-21.8-.5 0-44.2 43.2-97 96z" />
                        <path fill="#08193B" fill-opacity=".12"
                            d="m465 154.7-8.5 8.8 8.8-8.5c4.8-4.6 8.7-8.6 8.7-8.7 0-.8-1.1.3-9 8.4zm-12.1 12-2.4 2.8 2.8-2.4c2.5-2.3 3.2-3.1 2.4-3.1-.2 0-1.4 1.2-2.8 2.7zm-12.9 13-9.5 9.8 9.8-9.5c9-8.8 10.2-10 9.4-10-.1 0-4.5 4.4-9.7 9.7z" />
                        <path fill="#061D3A" fill-opacity=".17"
                            d="m542.9 166.7-2.4 2.8 2.8-2.4c1.5-1.4 2.7-2.6 2.7-2.8 0-.8-.8-.1-3.1 2.4zm-19.9 20-16.5 16.8 16.8-16.5c15.5-15.3 17.2-17 16.4-17-.1 0-7.6 7.5-16.7 16.7z" />
                        <path fill="#08173E" fill-opacity=".13"
                            d="m408.5 211.2-22 22.3 22.3-22c20.6-20.4 22.7-22.5 21.9-22.5-.1 0-10.1 10-22.2 22.2z" />
                        <path fill="#051A3E" fill-opacity=".19"
                            d="m481 228.7-25.5 25.8 25.8-25.5c23.9-23.7 26.2-26 25.4-26-.1 0-11.7 11.6-25.7 25.7z" />
                        <path fill="#071B3C" fill-opacity=".15"
                            d="m364.5 255.2-22 22.3 22.3-22c20.6-20.4 22.7-22.5 21.9-22.5-.1 0-10.1 10-22.2 22.2z" />
                        <path fill="#051C3D" fill-opacity=".21"
                            d="m442.5 267.2-13 13.3 13.3-13c12.3-12 13.7-13.5 12.9-13.5-.1 0-6.1 6-13.2 13.2zm99.4 196.5c-1.3 1.6-1.2 1.7.4.4.9-.7 1.7-1.5 1.7-1.7 0-.8-.8-.3-2.1 1.3zm-6 6c-1.3 1.6-1.2 1.7.4.4.9-.7 1.7-1.5 1.7-1.7 0-.8-.8-.3-2.1 1.3z" />
                        <path fill="#06183D" fill-opacity=".16"
                            d="M340.9 278.7c-1.3 1.6-1.2 1.7.4.4.9-.7 1.7-1.5 1.7-1.7 0-.8-.8-.3-2.1 1.3zm-7.5 7.5-1.9 2.3 2.3-1.9c2.1-1.8 2.7-2.6 1.9-2.6-.2 0-1.2 1-2.3 2.2zm-6.5 6.5-3.4 3.8 3.8-3.4c2-1.9 3.7-3.6 3.7-3.8 0-.8-.8 0-4.1 3.4zm-7 7c-1.3 1.6-1.2 1.7.4.4 1.6-1.3 2.1-2.1 1.3-2.1-.2 0-1 .8-1.7 1.7zm-6.9 6.8c-2.4 2.5-4.2 4.5-3.9 4.5.3 0 2.5-2 4.9-4.5 2.4-2.5 4.2-4.5 3.9-4.5-.3 0-2.5 2-4.9 4.5z" />
                        <path fill="#071D3D" fill-opacity=".28"
                            d="m514.4 491.2-1.9 2.3 2.3-1.9c1.2-1.1 2.2-2.1 2.2-2.3 0-.8-.8-.2-2.6 1.9zm-5 5-1.9 2.3 2.3-1.9c1.2-1.1 2.2-2.1 2.2-2.3 0-.8-.8-.2-2.6 1.9z" />
                        <path fill="#071D3E" fill-opacity=".42"
                            d="M436 350.5c1.3 1.4 2.6 2.5 2.8 2.5.3 0-.5-1.1-1.8-2.5s-2.6-2.5-2.8-2.5c-.3 0 .5 1.1 1.8 2.5z" />
                        <path fill="#081B3B" fill-opacity=".25"
                            d="m520.9 484.7-2.4 2.8 2.8-2.4c1.5-1.4 2.7-2.6 2.7-2.8 0-.8-.8-.1-3.1 2.4zM205.1 765.6c0 1.1.3 1.4.6.6.3-.7.2-1.6-.1-1.9-.3-.4-.6.2-.5 1.3z" />
                        <path fill="#091A3D" fill-opacity=".23"
                            d="M530 475.5c-2.4 2.5-4.2 4.5-3.9 4.5.3 0 2.5-2 4.9-4.5 2.4-2.5 4.2-4.5 3.9-4.5-.3 0-2.5 2-4.9 4.5z" />
                        <path fill-opacity=".01"
                            d="M494.5 499c2.7 2.7 5.1 5 5.4 5 .3 0-1.7-2.3-4.4-5-2.7-2.8-5.1-5-5.4-5-.3 0 1.7 2.2 4.4 5z" />
                        <path fill="none"
                            d="M336 340.4c0 .2.8 1 1.8 1.7 1.5 1.3 1.6 1.2.3-.4s-2.1-2.1-2.1-1.3zm12.5 12.6c2.7 2.7 5.1 5 5.4 5 .3 0-1.7-2.3-4.4-5-2.7-2.8-5.1-5-5.4-5-.3 0 1.7 2.2 4.4 5zm11.5 11.2c4.1 4.5 5 5.3 5 4.5 0-.2-2.1-2.3-4.7-4.7l-4.8-4.5 4.5 4.7zm16 16c9.7 10 11 11.3 11 10.5 0-.1-4.8-5-10.7-10.7l-10.8-10.5 10.5 10.7zm23.5 23.8c6.6 6.6 12.2 12 12.5 12 .2 0-4.9-5.4-11.5-12s-12.2-12-12.5-12c-.2 0 4.9 5.4 11.5 12z" />
                        <path fill="#071A3C" fill-opacity=".3"
                            d="m504.4 501.2-2.9 3.3 3.3-2.9c3-2.8 3.7-3.6 2.9-3.6-.2 0-1.6 1.5-3.3 3.2z" />
                        <path fill="#081C3C" fill-opacity=".25"
                            d="M459.3 343.7c16.3.2 43.1.2 59.5 0 16.3-.1 2.9-.2-29.8-.2s-46.1.1-29.7.2zm-168 407c47.1.2 124.3.2 171.5 0 47.1-.1 8.5-.2-85.8-.2s-132.9.1-85.7.2z" />
                        <path fill="#081B3D" fill-opacity=".26"
                            d="M205.3 754.5c0 2.2.2 3 .4 1.7.2-1.2.2-3 0-4-.3-.9-.5.1-.4 2.3zm-.1 7c0 1.6.2 2.2.5 1.2.2-.9.2-2.3 0-3-.3-.6-.5.1-.5 1.8zm.1 11c0 3.3.2 4.5.4 2.7.2-1.8.2-4.5 0-6s-.4 0-.4 3.3zm.1 15c0 4.9.1 7.1.3 4.8.2-2.3.2-6.4 0-9-.2-2.6-.3-.8-.3 4.2zm-.1 17.5c0 4.1.2 5.8.4 3.7.2-2 .2-5.4 0-7.5-.2-2-.4-.3-.4 3.8z" />
                        <path fill-opacity=".01"
                            d="M425 429.2c12.5 12.8 14 14.3 14 13.5 0-.1-6.2-6.3-13.7-13.7l-13.8-13.5 13.5 13.7zm17 17.3c1.3 1.4 2.6 2.5 2.8 2.5.3 0-.5-1.1-1.8-2.5s-2.6-2.5-2.8-2.5c-.3 0 .5 1.1 1.8 2.5zm5.5 5.5c1 1.1 2 2 2.3 2 .3 0-.3-.9-1.3-2s-2-2-2.3-2c-.3 0 .3.9 1.3 2zm5 5c1 1.1 2 2 2.3 2 .3 0-.3-.9-1.3-2s-2-2-2.3-2c-.3 0 .3.9 1.3 2zm5.5 5.4c0 .2.8 1 1.8 1.7 1.5 1.3 1.6 1.2.3-.4s-2.1-2.1-2.1-1.3zm12 12.1c3.5 3.6 6.7 6.5 6.9 6.5.3 0-2.4-2.9-5.9-6.5-3.5-3.6-6.7-6.5-6.9-6.5-.3 0 2.4 2.9 5.9 6.5zm10 10c1.3 1.4 2.6 2.5 2.8 2.5.3 0-.5-1.1-1.8-2.5s-2.6-2.5-2.8-2.5c-.3 0 .5 1.1 1.8 2.5zm5.5 5.5c1 1.1 2 2 2.3 2 .3 0-.3-.9-1.3-2s-2-2-2.3-2c-.3 0 .3.9 1.3 2z" />
                        <path fill="#091D3E" fill-opacity=".45"
                            d="M441 355.5c1.3 1.4 2.6 2.5 2.8 2.5.3 0-.5-1.1-1.8-2.5s-2.6-2.5-2.8-2.5c-.3 0 .5 1.1 1.8 2.5zm9 8.7c4.1 4.5 5 5.3 5 4.5 0-.2-2.1-2.3-4.7-4.7l-4.8-4.5 4.5 4.7z" />
                    </svg>
                </span>
            </button>
        </a>
    </header>

    <main class="main">
        <!--AGGIUNGI UN'OPZIONE-->
        <div class="space_aggiungi_opzione">
            <form action="script_php/aggiungi_opzione.php" method="POST">
                <h1>Aggiungi opzione</h1>
                <p>I campi con * sono obbligatori</p>

                <?php if ((isset($_GET['success'])) && ($_GET['success'] == 10)) {
                    echo "Opzione inserita con successo";
                } else if ((isset($_GET['error'])) && ($_GET['error'] == 10)) {
                    echo "Opzione gia' esistente";
                }
                ?>

                <?php
                // se non ci sono utenti invitati dai la possibilita' di inserire una nuova opzione;
                // se ci sono, continua a dare la possibilità solo se nessuno ha ancora accettato l'invito
                $tutti_sospesi_due = true;

                // se la query restituisce almeno una riga, vuol dire che ho invitato almeno un utente quindi non posso piu' aggiungere opzioni-->
                if (($inviti && count($inviti) > 0)) {
                    foreach ($inviti as $invito) {
                        if ($invito['Esito'] == "ACCETTATO") {
                            $tutti_sospesi_due = false;
                            break;
                        }
                    }
                } ?>
                <?php

                // se non ci sono invitati la variabile booleana non e' stata modificata quindi posso aggiungere un'opzione,
                // se tutti gli invitati sono con Esito='Sospeso' oppure con Esito='Rifiutato' ho eseguito i controlli ma la variabile booleana non e' stata modificata, quindi posso aggiungere un'opzione
                if ($tutti_sospesi_due) { ?>

                    <div class="input-group">
                        <input type="text" name="testo_opzione" id="testo_opzione" required autocomplete="off"
                            class="input">
                        <label class="user-label">Testo*</label>
                    </div>
                    <br>
                    <input type="hidden" name="codice_sondaggio" id="codice_sondaggio"
                        value="<?php echo $codice_sondaggio; ?>">
                    <input type="hidden" name="id_domanda_chiusa" id="id_domanda_chiusa" value="<?php echo $id_domanda; ?>">
                    <!--bottone crea domanda-->
                    <button class="crea" type="submit" name="aggiungi_opzione" id="aggiungi_opzione">
                        Aggiungi
                        <div class="arrow-wrapper">
                            <div class="arrow"></div>

                        </div>
                    </button>

                <?php } else {
                    echo "Un utente invitato ha accettato, per questa domanda non e' piu' possibile inserire opzioni";
                } ?>
            </form>
        </div>

        <!--VISUALIZZA E RIMUOVI OPZIONI (se nessun utente e' stato ancora invitato)-->
        <div class="space_rimuovi_opzione">
            <ul>
                <h1>Opzioni</h1>
                <h3>Elenco opzioni:</h3>
                <div class="lista_scrollabile">
                    <?php foreach ($opzioni_domanda as $opzione) { ?>
                        <li>
                            <form action="script_php/rimuovi_opzione.php" method="POST">

                                <!--Etichetta per mostrare numero e nome dell'opzione-->
                                <h3>
                                    <?php echo $opzione['Testo']; ?>
                                </h3>

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
                                    <input type="hidden" name="id_domanda" id="id_domanda" value="<?php echo $id_domanda ?>">
                                    <input type="hidden" name="numero_progressivo" id="numero_progressivo"
                                        value="<?php echo $opzione['Numeroprogressivo'] ?>">
                                    <button type="submit" name="bottone" id="bottone" class="noselect"><span
                                            class="text">Elimina</span><span class="icon"><svg
                                                xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
                                                <path
                                                    d="M24 20.188l-8.315-8.209 8.2-8.282-3.697-3.697-8.212 8.318-8.31-8.203-3.666 3.666 8.321 8.24-8.206 8.313 3.666 3.666 8.237-8.318 8.285 8.203z">
                                                </path>
                                            </svg></span>
                                    </button>
                                <?php } ?>
                            </form>
                        </li>
                    <?php } ?>
                </div>
            </ul>
        </div>
    </main>

    <section class="footer">

    </section>
</body>

</html>