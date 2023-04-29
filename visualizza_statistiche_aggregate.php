<?php
require 'config_connessione.php'; // instaura la connessione con il db

if (isset($_POST["statistiche_aggregate"])) {
    $codice_sondaggio = $_POST['codice_sondaggio'];

    try {
        $mostra_domande_sondaggio = $pdo->prepare("CALL MostraDomande(:codice)");
        $mostra_domande_sondaggio->bindParam(':codice', $codice_sondaggio, PDO::PARAM_INT);
        $mostra_domande_sondaggio->execute();
        $domande_sondaggio = $mostra_domande_sondaggio->fetchAll(PDO::FETCH_ASSOC);
        $mostra_domande_sondaggio->closeCursor();
    } catch (PDOException $e) {
        echo "Errore Stored Procedure: " . $e->getMessage();
        header("Location: index.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AnswerHUB | Statistiche aggregate</title>

    <link rel="icon" type="image/png" href="images/favicon.png">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="stile_css/visualizza_statistiche_aggregate.css">
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
        <h1>Statistiche aggregate</h1>
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
        <!--NUMERO DI RISPOSTE PER OGNI DOMANDA-->
        <div class="space">

            <ul>
                <h2>
                    Numero di risposte per ogni domanda (quanti utenti hanno risposto?)
                </h2>

                <!--Se il sondaggio non ha domande, mostri un messaggio-->
                <?php if (empty($domande_sondaggio)) {
                    echo "Il sondaggio non contiene domande";
                } else { ?>

                    <div class="lista_scrollabile">
                        <?php
                        foreach ($domande_sondaggio as $domanda_sondaggio) { ?>
                            <li>
                                <?php
                                try {
                                    if ($domanda_sondaggio['ApertaChiusa'] == "APERTA") {
                                        //prendi ID e usalo in una query che ritorna le risposte
                                        $conta_numero_risposte = $pdo->prepare("CALL ContaNumeroRisposteDomandaAperta(:id_domanda_aperta)");
                                        $conta_numero_risposte->bindParam(':id_domanda_aperta', $domanda_sondaggio['ID'], PDO::PARAM_INT);
                                        $conta_numero_risposte->execute();
                                        $numero_risposte = $conta_numero_risposte->fetch(PDO::FETCH_ASSOC);
                                        $conta_numero_risposte->closeCursor();
                                    } else {
                                        $conta_numero_risposte = $pdo->prepare("CALL ContaNumeroRisposteDomandaChiusa(:id_domanda_chiusa)");
                                        $conta_numero_risposte->bindParam(':id_domanda_chiusa', $domanda_sondaggio['ID'], PDO::PARAM_INT);
                                        $conta_numero_risposte->execute();
                                        $numero_risposte = $conta_numero_risposte->fetch(PDO::FETCH_ASSOC);
                                        $conta_numero_risposte->closeCursor();
                                    }
                                } catch (PDOException $e) {
                                    echo "Errore Stored Procedure: " . $e->getMessage();
                                    header("Location: index.php");
                                    exit;
                                }
                                echo $domanda_sondaggio['Testo'] . ": " . $numero_risposte['NumeroRisposte']; ?>
                            </li>
                        <?php } ?>

                    </div>
                <?php } ?>
            </ul>

        </div>

        <!--DISTRIBUZIONE DELLE RISPOSTE SULLE VARIE OPZIONI-->
        <div class="space">
            <ul>
                <h2>
                    Distribuzione delle risposte sulle varie opzioni
                </h2>

                <?php
                if (empty($domande_sondaggio)) {
                    echo "Il sondaggio non contiene domande";
                } else { ?>
                    <div class="lista_scrollabile">
                        <?php
                        foreach ($domande_sondaggio as $domanda_sondaggio) { ?>
                            <li>
                                <?php if ($domanda_sondaggio['ApertaChiusa'] == 'CHIUSA') { ?>
                                    <?php echo $domanda_sondaggio['Testo'] . ':<br>'; ?>
                                    <?php
                                    try {
                                        $conta_numero_risposte_totali = $pdo->prepare("CALL ContaNumeroRisposteDomandaChiusa(:id_domanda_chiusa)");
                                        $conta_numero_risposte_totali->bindParam(':id_domanda_chiusa', $domanda_sondaggio['ID'], PDO::PARAM_INT);
                                        $conta_numero_risposte_totali->execute();
                                        $numero_risposte_totali = $conta_numero_risposte_totali->fetch(PDO::FETCH_ASSOC);
                                        $conta_numero_risposte_totali->closeCursor();
                                    } catch (PDOException $e) {
                                        echo "Errore Stored Procedure: " . $e->getMessage();
                                        header("Location: index.php");
                                        exit;
                                    }
                                    if (empty($numero_risposte_totali)) {
                                        echo "Non ci sono risposte";
                                    } else {
                                        $num_risp_tot = $numero_risposte_totali['NumeroRisposte'];

                                        // prende le opzioni di una domanda
                                        try {
                                            $mostra_opzioni_domanda_chiusa = $pdo->prepare("CALL MostraOpzioni(:id_domanda_chiusa)");
                                            $mostra_opzioni_domanda_chiusa->bindParam(':id_domanda_chiusa', $domanda_sondaggio['ID'], PDO::PARAM_INT);
                                            $mostra_opzioni_domanda_chiusa->execute();
                                            $opzioni_domanda_chiusa = $mostra_opzioni_domanda_chiusa->fetchAll(PDO::FETCH_ASSOC);
                                            $mostra_opzioni_domanda_chiusa->closeCursor();
                                        } catch (PDOException $e) {
                                            echo "Errore Stored Procedure: " . $e->getMessage();
                                            header("Location: index.php");
                                            exit;
                                        }

                                        // per ogni opzione della domanda chiusa conta il numero di occorrenze e ne calcola la percentuale rispetto al numero totale di risposte
                                        foreach ($opzioni_domanda_chiusa as $opzione_domanda_chiusa) {

                                            // interroga il db per contare il numero di risposte che occorrono per ogni opzione
                                            try {
                                                $conta_numero_risposte_opzione = $pdo->prepare("CALL ContaNumeroOccorrenzeOpzione(:id_domanda_chiusa, :numero_progressivo)");
                                                $conta_numero_risposte_opzione->bindParam(':id_domanda_chiusa', $domanda_sondaggio['ID'], PDO::PARAM_INT);
                                                $conta_numero_risposte_opzione->bindParam(':numero_progressivo', $opzione_domanda_chiusa['Numeroprogressivo'], PDO::PARAM_INT);
                                                $conta_numero_risposte_opzione->execute();
                                                $numero_risposte_opzione = $conta_numero_risposte_opzione->fetch(PDO::FETCH_ASSOC);
                                                $conta_numero_risposte_opzione->closeCursor();
                                            } catch (PDOException $e) {
                                                echo "Errore Stored Procedure: " . $e->getMessage();
                                                header("Location: index.php");
                                                exit;
                                            }
                                            $num_risp_opz = $numero_risposte_opzione['NumeroOccorrenze'];

                                            // stampa la percentuale
                                            try {
                                                $percentuale = ($num_risp_opz * 100) / $num_risp_tot;
                                                echo $opzione_domanda_chiusa['Testo'] . ': ';
                                                echo $percentuale . '% <br>';
                                            } catch (DivisionByZeroError $e) {
                                                echo 'Nessuna risposta. <br>';
                                                break;
                                            }

                                        }
                                    }
                                    ?>
                                <?php } ?>
                            </li>
                        <?php } ?>
                    </div>
                <?php } ?>
            </ul>
        </div>

        <!--VALORE MEDIO/MINIMO E MASSIMO DEL NUMERO DI CARATTERI-->
        <div class="space">
            <ul>
                <h2>
                    Valore medio/minimo e massimo del numero di caratteri
                </h2>

                <?php if (empty($domande_sondaggio)) {
                    echo "Il sondaggio non contiene domande";
                } else { ?>
                    <div class="lista_scrollabile">
                        <?php
                        foreach ($domande_sondaggio as $domanda_sondaggio) { ?>
                            <?php if ($domanda_sondaggio['ApertaChiusa'] == 'APERTA') { ?>
                                <li>
                                    <?php echo $domanda_sondaggio['Testo'] . ':<br>'; ?>
                                    <?php

                                    //interrogo il database per prendere tutte le risposte ad una domanda aperta
                                    try {
                                        $mostra_risposte = $pdo->prepare("CALL MostraRisposte(:id_domanda_aperta)");
                                        $mostra_risposte->bindParam(':id_domanda_aperta', $domanda_sondaggio['ID'], PDO::PARAM_INT);
                                        $mostra_risposte->execute();
                                        $risposte = $mostra_risposte->fetchAll(PDO::FETCH_ASSOC);
                                        $mostra_risposte->closeCursor();
                                    } catch (PDOException $e) {
                                        echo "Errore Stored Procedure: " . $e->getMessage();
                                        header("Location: index.php");
                                        exit;
                                    }
                                    if (empty($risposte)) {
                                        echo "Non ci sono risposte";
                                    } else {

                                        $num_risposte = 0;
                                        $totale_lunghezza = 0;
                                        $min = PHP_INT_MAX; // valore massimo predefinito di PHP
                                        $max = 0;
                                        foreach ($risposte as $risposta) {
                                            $num_risposte += 1;
                                            $testo = $risposta['Testo'];
                                            $lunghezza = strlen($testo);
                                            $totale_lunghezza += $lunghezza;
                                            if ($lunghezza < $min) {
                                                $min = $lunghezza;
                                            }
                                            if ($lunghezza > $max) {
                                                $max = $lunghezza;
                                            }
                                        }
                                        echo "Media: " . $totale_lunghezza / $num_risposte . " | " . "Minimo: " . $min . " | " . "Massimo: " . $max;
                                    }
                                    ?>
                                </li>
                            <?php } ?>
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