<?php
require 'config_connessione.php'; // instaura la connessione con il db

if ((isset($_POST["rispondi"])) || (isset($_POST["visualizza_risposte"]))) {
    $email = $_SESSION["email"];
    $codice_sondaggio = $_POST['codice_sondaggio'];

    $mostra_domande_sondaggio = $pdo->prepare("CALL MostraDomande(:param1)");
    $mostra_domande_sondaggio->bindParam(':param1', $codice_sondaggio, PDO::PARAM_INT);
    $mostra_domande_sondaggio->execute();
    $domande_sondaggio = $mostra_domande_sondaggio->fetchAll(PDO::FETCH_ASSOC);
    $mostra_domande_sondaggio->closeCursor();
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

        ul {
            list-style: none;
        }
    </style>
</head>

<body>
    <!--Se ho cliccato il bottone rispondi...-->
    <?php if (isset($_POST["rispondi"])) { ?>
        <form action="script_php/controlla_risposte.php" method="POST">
            <?php foreach ($domande_sondaggio as $domanda_sondaggio) { ?>
                <div class="space">
                    <!--Mostra domanda-->
                    <?php $id_domanda = $domanda_sondaggio['ID']; ?>
                    <h3>
                        <?php echo $domanda_sondaggio['Testo'] ?>
                    </h3>
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
                    <?php echo $domanda_sondaggio['Punteggio'] ?>

                    <!--Mostra box per risposta o mostra opzioni in base a APERTA o CHIUSA-->
                    <?php if ($domanda_sondaggio['ApertaChiusa'] == 'APERTA') { ?>
                        <?php
                        $max_caratteri_risposta = $pdo->prepare("SELECT * FROM DomandaAperta WHERE ID = ?");
                        $max_caratteri_risposta->execute([$id_domanda]);
                        $max_caratteri = $max_caratteri_risposta->fetch(PDO::FETCH_ASSOC);
                        $textarea_name_id = 'risposte_aperte[' . $id_domanda . ']';
                        ?>
                        <!--Salva il contenuto della textarea in un array associativo, l'indice e' l'id della domanda, il valore e' la risposta effettiva-->
                        <textarea maxlength="<?php echo $max_caratteri['MaxCaratteriRisposta']; ?>"
                            name="<?php echo $textarea_name_id; ?>" id="<?php echo $textarea_name_id; ?>" required></textarea>
                    <?php } else if ($domanda_sondaggio['ApertaChiusa'] == 'CHIUSA') { ?>
                            <!--Prendi le opzioni e mostrale in una radio-->
                            <?php
                            $mostra_opzioni_domanda = $pdo->prepare("CALL MostraOpzioni(:id_domanda)");
                            $mostra_opzioni_domanda->bindParam(':id_domanda', $id_domanda, PDO::PARAM_INT);
                            $mostra_opzioni_domanda->execute();
                            $opzioni_domanda = $mostra_opzioni_domanda->fetchAll(PDO::FETCH_ASSOC);
                            $mostra_opzioni_domanda->closeCursor();
                            $radio_name_id = 'opzioni_selezionate[' . $id_domanda . ']';
                            ?>

                        <?php foreach ($opzioni_domanda as $opzione) { ?>
                                <label for="opzione">
                                <?php echo $opzione['Testo']; ?>
                                </label>
                                <input type="radio" name="<?php echo $radio_name_id ?>" id="<?php echo $radio_name_id ?>"
                                    value="<?php echo $opzione['Numeroprogressivo']; ?>" required>
                        <?php } ?>
                    <?php } ?>
                </div>
            <?php } ?>
            <input type="hidden" name="codice_sondaggio" id="codice_sondaggio" value="<?php echo $codice_sondaggio; ?>">
            <input type="submit" name="invia_risposte" id="invia_risposte" value="Invia risposte">
        </form>
    <?php } ?>


    <!--...altrimenti, se ho clccato il bottone visualizza risposte-->
    <?php if (isset($_POST["visualizza_risposte"])) { ?>
        <?php foreach ($domande_sondaggio as $domanda_sondaggio) { ?>
            <div class="space">
                <!--Mostra domanda-->
                <?php $id_domanda = $domanda_sondaggio['ID']; ?>
                <h3>
                    <?php echo $domanda_sondaggio['Testo'] ?>
                </h3>
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
                <?php echo $domanda_sondaggio['Punteggio'] ?>
                <!--Mostra risposta aperta o mostra opzione selezionata, in base a domanda APERTA o CHIUSA-->
                <?php if ($domanda_sondaggio['ApertaChiusa'] == 'APERTA') { ?>
                    <?php
                    $mostra_risposta = $pdo->prepare("CALL MostraRispostaAperta(:id_domanda_aperta)");
                    $mostra_risposta->bindParam(':id_domanda_aperta', $id_domanda, PDO::PARAM_INT);
                    $mostra_risposta->execute();
                    $risposta = $mostra_risposta->fetch(PDO::FETCH_ASSOC);
                    $mostra_risposta->closeCursor();
                    echo $risposta['Testo'];
                    ?>
                <?php } else if ($domanda_sondaggio['ApertaChiusa'] == 'CHIUSA') { ?>
                    <?php
                    $mostra_opzione_selezionata = $pdo->prepare("CALL MostraOpzioneSelezionata(:id_domanda_chiusa)");
                    $mostra_opzione_selezionata->bindParam(':id_domanda_chiusa', $id_domanda, PDO::PARAM_INT);
                    $mostra_opzione_selezionata->execute();
                    $opzione_selezionata = $mostra_opzione_selezionata->fetch(PDO::FETCH_ASSOC);
                    $mostra_opzione_selezionata->closeCursor();
                    echo $opzione_selezionata['Testo'];
                    ?>
                <?php } ?>
            </div>
        <?php } ?>
    <?php } ?>


    <a href="semplice_home.php">Torna alla home</a>
    <a href="logout.php">Effettua il logout</a>
</body>

</html>