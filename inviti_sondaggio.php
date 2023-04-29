<?php
try {
    require 'config_connessione.php'; // instaura la connessione con il db
    $codice_sondaggio = $_GET['cod_sondaggio'];

    // controllo per evitare che si cambi url e si faccia l'accesso ad un sondaggio di un altro utente premium, al massimo se cambio url per il get del codice posso mettere il codice di un sondaggio da me (utente premium) gestito:
    $check_sondaggio = $pdo->prepare("SELECT Codice FROM Sondaggio WHERE EmailUtentecreante = :email AND Codice = :codice");
    $check_sondaggio->bindParam(':email', $_SESSION['email'], PDO::PARAM_STR);
    $check_sondaggio->bindParam(':codice', $codice_sondaggio, PDO::PARAM_INT);
    $check_sondaggio->execute();
    $sondaggio = $check_sondaggio->fetch(PDO::FETCH_ASSOC);
    $check_sondaggio->closeCursor();
    if (!$sondaggio) {
        header("Location: index.php");
        exit;
    }

    $mostra_dati_sondaggio = $pdo->prepare("SELECT * FROM Sondaggio WHERE Codice = :codice");
    $mostra_dati_sondaggio->bindParam(':codice', $codice_sondaggio, PDO::PARAM_INT);
    $mostra_dati_sondaggio->execute();
    $dati_sondaggio = $mostra_dati_sondaggio->fetch(PDO::FETCH_ASSOC);
    $mostra_dati_sondaggio->closeCursor();
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
    <title>AnswerHUB | Invita</title>

    <link rel="icon" type="image/png" href="images/favicon.png">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="stile_css/inviti_sondaggio.css">
    <link rel="stylesheet" href="stile_css/bottone_logout.css">
    <link rel="stylesheet" href="stile_css/crea_sondaggio_inputs.css">
    <link rel="stylesheet" href="stile_css/upload_file.css">
    <link rel="stylesheet" href="stile_css/crea_sondaggio_button.css">
    <link rel="stylesheet" href="stile_css/bottone_opzioni.css">
    <link rel="stylesheet" href="stile_css/non_bottone_domanda_aperta.css">
    <link rel="stylesheet" href="stile_css/checkbox_invita_utente.css">

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
        <h1>Invita utenti</h1>
        <a href="logout.php" class="logout">
            <button class="logout_btn">
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
        <div class="space">
            <!--mostri i dati di tutti gli utenti interessati al dominio di questo specifico sondaggio-->
            <?php
            try {
                if (isset($dati_sondaggio) && is_array($dati_sondaggio)) {
                    $parola_chiave_dominio_sondaggio = $dati_sondaggio['ParolachiaveDominio'];
                    $mostra_utenti_interessati = $pdo->prepare("CALL MostraUtentiInteressatiSenzaInvito(:param1, :param2)");
                    $mostra_utenti_interessati->bindParam(':param1', $parola_chiave_dominio_sondaggio, PDO::PARAM_STR);
                    $mostra_utenti_interessati->bindParam(':param2', $codice_sondaggio, PDO::PARAM_INT);
                    $mostra_utenti_interessati->execute();
                    $utenti_interessati = $mostra_utenti_interessati->fetchAll(PDO::FETCH_ASSOC);
                    $mostra_utenti_interessati->closeCursor();
                }
            } catch (PDOException $e) {
                echo "Errore Stored Procedure: " . $e->getMessage();
                header("Location: index.php");
                exit;
            }
            ?>

            <!--Ritorna un array non vuoto se esiste almeno una domanda-->
            <?php
            try {
                $check_domande = $pdo->prepare("SELECT * FROM Domanda JOIN ComponenteSondaggioDomanda ON ID = IDDomanda WHERE CodiceSondaggio = :codice_sondaggio");
                $check_domande->bindParam(':codice_sondaggio', $codice_sondaggio, PDO::PARAM_INT);
                $check_domande->execute();
                $domande = $check_domande->fetchAll();
                $check_domande->closeCursor();

                //Ritorna un array delle domande chiuse del sondaggio
            
                $check_domande_chiuse = $pdo->prepare("SELECT * FROM DomandaChiusa JOIN Domanda ON DomandaChiusa.ID = Domanda.ID JOIN ComponenteSondaggioDomanda ON Domanda.ID = ComponenteSondaggioDomanda.IDDomanda WHERE CodiceSondaggio = :codice_sondaggio");
                $check_domande_chiuse->bindParam(':codice_sondaggio', $codice_sondaggio, PDO::PARAM_INT);
                $check_domande_chiuse->execute();
                $domande_chiuse = $check_domande_chiuse->fetchAll();
                $check_domande_chiuse->closeCursor();
            } catch (PDOException $e) {
                echo "Errore Stored Procedure: " . $e->getMessage();
                header("Location: index.php");
                exit;
            }
            ?>

            <!--Controlla se esistono domande per il sondaggio, altrimenti notifica l'utente e non rendere disponibile l'invio di inviti-->
            <?php
            $controllo = true;
            if (empty($domande)) {
                echo "Questo sondaggio non ha domande, aggiungi delle domande";
                $controllo = false;
            } else {
                if (!empty($domande_chiuse)) {
                    //Cicla tutte le domande chiuse, se ne esiste una che non ha opzioni, interrompe e mostra un messaggio di errore
                    foreach ($domande_chiuse as $domanda_chiusa) {
                        //Ritorna un array delle opzioni della domanda data in input
                        try {
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
                        } catch (PDOException $e) {
                            echo "Errore Stored Procedure: " . $e->getMessage();
                            header("Location: index.php");
                            exit;
                        }
                    }
                }

                // Se i controlli vengono passati (la variabile $controllo non e' stata modificata in false), procedi
                if ($controllo) { ?>
                    <!--Se non esistono utenti con interessati al dominio del sondaggio, mostra messaggio
                (lo mostri solo se sono passati i controlli precedenti sull'esistenza di domande e opzioni per domande chiuse-->
                    <?php
                    if (count($utenti_interessati) == 0) {
                        echo "Non ci sono utenti interessati al dominio di questo sondaggio (non sono considerati eventuali utenti interessati e gia' invitati)";
                    } else {
                        ?>
                        <form action="script_php/manda_inviti.php" method="POST">
                            <ul>
                                <?php if (isset($dati_sondaggio) && is_array($dati_sondaggio)) { ?>
                                    <h2>Seleziona gli utenti da invitare per il sondaggio
                                        <?php echo $dati_sondaggio['Titolo']; ?>
                                    </h2>
                                <?php } ?>
                                <?php if (isset($_GET['error']) && ($_GET['error'] == 10)) {
                                    echo "Devi selezionare almeno un utente";
                                } else if (isset($_GET['success']) && $_GET['success'] == 10) {
                                    echo "Utenti invitati con successo";
                                } else if (isset($_GET['error']) && $_GET['error'] == 20) {
                                    echo "Per questo sondaggio hai già invitato tutti gli utenti possibili";
                                } else if (isset($_GET['error']) && $_GET['error'] == 30) {
                                    echo "Hai selezionato troppi utenti, puoi selezionare" . " " . $_GET['num_selezionabili'] . " " . "utente/i";
                                }
                                ?>
                                <div class="lista_scrollabile">
                                    <?php if (isset($utenti_interessati) && is_array($utenti_interessati)) {
                                        foreach ($utenti_interessati as $utente_interessato) { ?>
                                            <li>
                                                <input type="checkbox" name="utenti_selezionati[]"
                                                    value="<?php echo $utente_interessato['Email'] ?>">
                                                <label for="utente_interessato[]">
                                                    <?php echo $utente_interessato['Email'] . ' | ' . $utente_interessato['Nome'];
                                                    echo ' ' . $utente_interessato['Cognome']; ?>
                                                </label>
                                            </li>
                                        <?php }
                                    } ?>
                                </div>
                            </ul>
                            <input type="hidden" name="codice_sondaggio"
                                value="<?php echo $codice_sondaggio; ?>"><!--Per inviare il codice_sondaggio tramite POST-->
                            <!--<input type="submit" name="invita" id="invita" value="Invita">-->
                            <button class="crea" type="submit" name="invita" id="invita">
                                Invita
                                <div class="arrow-wrapper">
                                    <div class="arrow"></div>

                                </div>
                            </button>
                        </form>
                    <?php } ?>
                <?php } ?>
            <?php } ?>
        </div>
    </main>

    <section class="footer">

    </section>
</body>

</html>