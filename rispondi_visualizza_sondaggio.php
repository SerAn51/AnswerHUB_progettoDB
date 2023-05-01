<?php
require 'config_connessione.php'; // instaura la connessione con il db

if ((isset($_POST["rispondi"])) || (isset($_POST["visualizza_risposte"]))) {
    $email = $_SESSION["email"];
    $codice_sondaggio = $_POST['codice_sondaggio'];
    try {
        $mostra_domande_sondaggio = $pdo->prepare("CALL MostraDomande(:param1)");
        $mostra_domande_sondaggio->bindParam(':param1', $codice_sondaggio, PDO::PARAM_INT);
        $mostra_domande_sondaggio->execute();
        $domande_sondaggio = $mostra_domande_sondaggio->fetchAll(PDO::FETCH_ASSOC);
        $mostra_domande_sondaggio->closeCursor();
    } catch (PDOException $e) {
        echo "Errore Stored Procedure: " . $e->getMessage();
        header("Location: index.php");
        exit;
    }

    try {
        $check_sondaggio = $pdo->prepare("SELECT * FROM Sondaggio WHERE Codice = :codice");
        $check_sondaggio->bindParam(':codice', $codice_sondaggio, PDO::PARAM_INT);
        $check_sondaggio->execute();
        $sondaggio = $check_sondaggio->fetch(PDO::FETCH_ASSOC);
        $check_sondaggio->closeCursor();
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
    <title>AnswerHUB | Rispondi</title>

    <link rel="icon" type="image/png" href="images/favicon.png">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="stile_css/checkbox_style.css">
    <link rel="stylesheet" href="stile_css/bottone_logout.css">
    <link rel="stylesheet" href="stile_css/crea_sondaggio_inputs.css">
    <link rel="stylesheet" href="stile_css/upload_file.css">
    <link rel="stylesheet" href="stile_css/crea_sondaggio_button.css">
    <link rel="stylesheet" href="stile_css/bottone_opzioni.css">
    <link rel="stylesheet" href="stile_css/non_bottone_domanda_aperta.css">
    <link rel="stylesheet" href="stile_css/checkbox_invita_utente.css">
    <link rel="stylesheet" href="stile_css/rispondi_visualizza_sondaggio.css">
    <link rel="stylesheet" href="stile_css/risposta_opzioni_radio.css">

</head>

<body>
    <header class="header">
        <h1 class="lista_scrollabile_orizzontalmente">
            <?php if (isset($_POST["rispondi"])) { ?>
                Rispondi: 
            <?php } else if (isset($_POST["visualizza_risposte"])) { ?>
                Visualizza risposte: 
            <?php } ?>
            <?php echo $sondaggio['Titolo']; ?>
        </h1>
        <a href="index.php" class="home">
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
        <!--Se ho cliccato il bottone rispondi...-->
        <?php if (isset($_POST["rispondi"])) { ?>
            <form action="script_php/controlla_risposte.php" method="POST">
                <?php foreach ($domande_sondaggio as $domanda_sondaggio) { ?>
                    <div class="space">
                        <!--Mostra domanda-->
                        <?php $id_domanda = $domanda_sondaggio['ID']; ?>

                        <div class="titolo_immagine">
                            <div class="lista_scrollabile">
                                <h2>
                                    <?php echo $domanda_sondaggio['Testo']; ?>
                                    <!--Se la domanda è aperta, mostro il massimo numero di caratteri-->
                                    <?php if ($domanda_sondaggio['ApertaChiusa'] == "APERTA") { ?>
                                        <?php
                                        try {
                                            $max_caratteri_risposta = $pdo->prepare("SELECT * FROM DomandaAperta WHERE ID = ?");
                                            $max_caratteri_risposta->execute([$id_domanda]);
                                            $max_caratteri = $max_caratteri_risposta->fetch(PDO::FETCH_ASSOC);
                                        } catch (PDOException $e) {
                                            echo "Errore Stored Procedure: " . $e->getMessage();
                                            header("Location: index.php");
                                            exit;
                                        }
                                        ?>
                                        <?php echo ' (max caratteri: ' . $max_caratteri['MaxCaratteriRisposta'] . ')'; ?>
                                    <?php } ?>
                                </h2>
                            </div>

                            <!--Mostra la foto, se c'e'-->
                            <?php
                            if (isset($domanda_sondaggio["Foto"])) { ?>
                                <?php
                                // leggi il contenuto del blob dal database
                                $blob = $domanda_sondaggio["Foto"];

                                // decodifica il contenuto del blob in una stringa base64
                                $base64 = base64_encode($blob);

                                // determina il tipo di immagine dal contenuto del blob con la funzione getimagesizefromstring e prendendo
                                //il valore della chiave mime che dice il tipo dell'immagine
                                $image_info = getimagesizefromstring($blob);
                                $mime_type = $image_info["mime"];
                                ?>
                                <img width="10%" src="data:<?php echo $mime_type; ?>;base64,<?php echo $base64; ?>">
                            <?php } ?>
                            <!--Mostra il punteggio-->
                            <h3>
                                Punteggio:
                                <?php echo $domanda_sondaggio['Punteggio'] ?>
                            </h3>

                        </div>

                        <div class="campo_risposta">
                            <!--Mostra box per risposta o mostra opzioni in base a APERTA o CHIUSA-->
                            <?php if ($domanda_sondaggio['ApertaChiusa'] == 'APERTA') { ?>
                                <?php
                                $textarea_name_id = 'risposte_aperte[' . $id_domanda . ']';
                                ?>
                                <!--Salva il contenuto della textarea in un array associativo, l'indice e' l'id della domanda, il valore e' la risposta effettiva-->
                                <textarea placeholder="Scrivi la tua risposta qui"
                                    maxlength="<?php echo $max_caratteri['MaxCaratteriRisposta']; ?>"
                                    name="<?php echo $textarea_name_id; ?>" id="<?php echo $textarea_name_id; ?>"
                                    required></textarea>
                            <?php } else if ($domanda_sondaggio['ApertaChiusa'] == 'CHIUSA') { ?>
                                    <!--Prendi le opzioni e mostrale in una radio-->
                                    <?php
                                    try {
                                        $mostra_opzioni_domanda = $pdo->prepare("CALL MostraOpzioni(:id_domanda)");
                                        $mostra_opzioni_domanda->bindParam(':id_domanda', $id_domanda, PDO::PARAM_INT);
                                        $mostra_opzioni_domanda->execute();
                                        $opzioni_domanda = $mostra_opzioni_domanda->fetchAll(PDO::FETCH_ASSOC);
                                        $mostra_opzioni_domanda->closeCursor();
                                        $radio_name_id = 'opzioni_selezionate[' . $id_domanda . ']';
                                    } catch (PDOException $e) {
                                        echo "Errore Stored Procedure: " . $e->getMessage();
                                        header("Location: index.php");
                                        exit;
                                    }
                                    ?>

                                    <div class="opzioni">
                                        <h3>Seleziona l'opzione:</h3>
                                        <div class="lista_scrollabile">
                                        <?php foreach ($opzioni_domanda as $opzione) { ?>
                                                <label class="radio-button">
                                                    <input type="radio" name="<?php echo $radio_name_id ?>"
                                                        id="<?php echo $radio_name_id ?>"
                                                        value="<?php echo $opzione['Numeroprogressivo']; ?>" required>
                                                    <span class="radio"></span>
                                                <?php echo $opzione['Testo']; ?>
                                                </label>
                                        <?php } ?>
                                        </div>
                                    </div>

                            <?php } ?>
                        </div>
                    </div>
                <?php } ?>
                <input type="hidden" name="codice_sondaggio" id="codice_sondaggio" value="<?php echo $codice_sondaggio; ?>">
                <button class="crea" type="submit" name="invia_risposte" id="invia_risposte">
                    Invia risposte
                    <div class="arrow-wrapper">
                        <div class="arrow"></div>

                    </div>
                </button>
            </form>
        <?php } ?>


        <!--...altrimenti, se ho clccato il bottone visualizza risposte-->
        <?php if (isset($_POST["visualizza_risposte"])) { ?>
            <?php foreach ($domande_sondaggio as $domanda_sondaggio) { ?>
                <div class="space">
                    <!--Mostra domanda-->
                    <?php $id_domanda = $domanda_sondaggio['ID']; ?>

                    <div class="titolo_immagine">
                        <div class="lista_scrollabile">
                            <h2>
                                <?php echo $domanda_sondaggio['Testo'] ?>
                                <!--Se la domanda è aperta, mostro il massimo numero di caratteri-->
                                <?php if ($domanda_sondaggio['ApertaChiusa'] == "APERTA") { ?>
                                    <?php
                                    try {
                                        $max_caratteri_risposta = $pdo->prepare("SELECT * FROM DomandaAperta WHERE ID = ?");
                                        $max_caratteri_risposta->execute([$id_domanda]);
                                        $max_caratteri = $max_caratteri_risposta->fetch(PDO::FETCH_ASSOC);
                                    } catch (PDOException $e) {
                                        echo "Errore Stored Procedure: " . $e->getMessage();
                                        header("Location: index.php");
                                        exit;
                                    }
                                    ?>
                                    <?php echo ' (max caratteri: ' . $max_caratteri['MaxCaratteriRisposta'] . ')'; ?>
                                <?php } ?>
                            </h2>
                        </div>

                        <!--Mostra la foto, se c'e'-->
                        <?php
                        if (isset($domanda_sondaggio["Foto"])) { ?>
                            <?php
                            // leggi il contenuto del blob dal database
                            $blob = $domanda_sondaggio["Foto"];

                            // decodifica il contenuto del blob in una stringa base64
                            $base64 = base64_encode($blob);

                            // determina il tipo di immagine dal contenuto del blob con la funzione getimagesizefromstring e prendendo
                            //il valore della chiave mime che dice il tipo dell'immagine
                            $image_info = getimagesizefromstring($blob);
                            $mime_type = $image_info["mime"];
                            ?>
                            <img width="10%" src="data:<?php echo $mime_type; ?>;base64,<?php echo $base64; ?>">
                        <?php } ?>
                        <!--Mostra il punteggio-->
                        <h3>
                            Punteggio:
                            <?php echo $domanda_sondaggio['Punteggio'] ?>
                        </h3>
                    </div>

                    <div class="campo_risposta">
                        <!--Mostra risposta aperta o mostra opzione selezionata, in base a domanda APERTA o CHIUSA-->
                        <?php if ($domanda_sondaggio['ApertaChiusa'] == 'APERTA') { ?>
                            <?php
                            try {
                                $mostra_risposta = $pdo->prepare("CALL MostraRispostaAperta(:email, :id_domanda_aperta)");
                                $mostra_risposta->bindParam(':email', $email, PDO::PARAM_STR);
                                $mostra_risposta->bindParam(':id_domanda_aperta', $id_domanda, PDO::PARAM_INT);
                                $mostra_risposta->execute();
                                $risposta = $mostra_risposta->fetch(PDO::FETCH_ASSOC);
                                $mostra_risposta->closeCursor();
                                //mostro la risposta alla domanda aperta
                                ?>
                                <textarea readonly><?php echo $risposta['Testo']; ?></textarea>
                                <?php
                            } catch (PDOException $e) {
                                echo "Errore Stored Procedure: " . $e->getMessage();
                                header("Location: index.php");
                                exit;
                            }
                            ?>
                        <?php } else if ($domanda_sondaggio['ApertaChiusa'] == 'CHIUSA') { ?>
                                <?php
                                try {
                                    $mostra_opzione_selezionata = $pdo->prepare("CALL MostraOpzioneSelezionata(:email, :id_domanda_chiusa)");
                                    $mostra_opzione_selezionata->bindParam(':email', $email, PDO::PARAM_STR);
                                    $mostra_opzione_selezionata->bindParam(':id_domanda_chiusa', $id_domanda, PDO::PARAM_INT);
                                    $mostra_opzione_selezionata->execute();
                                    $opzione_selezionata = $mostra_opzione_selezionata->fetch(PDO::FETCH_ASSOC);
                                    $mostra_opzione_selezionata->closeCursor();

                                    $mostra_opzioni_non_selezionate = $pdo->prepare("CALL MostraOpzioniNonSelezionate(:email, :id_domanda_chiusa)");
                                    $mostra_opzioni_non_selezionate->bindParam(':email', $email, PDO::PARAM_STR);
                                    $mostra_opzioni_non_selezionate->bindParam(':id_domanda_chiusa', $id_domanda, PDO::PARAM_INT);
                                    $mostra_opzioni_non_selezionate->execute();
                                    $opzioni_non_selezionate = $mostra_opzioni_non_selezionate->fetchAll(PDO::FETCH_ASSOC);
                                    $mostra_opzioni_non_selezionate->closeCursor();
                                } catch (PDOException $e) {
                                    echo "Errore Stored Procedure: " . $e->getMessage();
                                    header("Location: index.php");
                                    exit;
                                }
                                ?>

                                <div class="opzioni">
                                    <h3>Opzione selezionata:</h3>
                                    <div class="lista_scrollabile">
                                        <!--Le opzioni vengono rappresentate con una radio, selezionate o deselezionate in base a quale opzione e' stata scelta-->
                                        <label class="radio-button">
                                            <input type="radio" name="opzione_selezionata_<?php echo $id_domanda ?>" checked disabled>
                                            <span class="radio"></span>
                                        <?php echo $opzione_selezionata['Testo']; ?>
                                        </label>
                                    <?php foreach ($opzioni_non_selezionate as $opzione_non_selezionata) { ?>
                                            <label class="radio-button">
                                                <input type="radio" name="opzione_non_selezionata<?php echo $id_domanda ?>" disabled>
                                                <span class="radio"></span>
                                            <?php echo $opzione_non_selezionata['Testo']; ?>
                                            </label>
                                    <?php } ?>
                                    </div>
                                </div>
                        <?php } ?>
                    </div>
                </div>
            <?php } ?>
        <?php } ?>
    </main>

    <section class="footer">
    </section>
</body>

</html>