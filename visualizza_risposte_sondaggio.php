<?php

require 'config_connessione.php'; // instaura la connessione con il db

$codice_sondaggio = $_GET['cod_sondaggio'];

// controllo per evitare che si cambi url e si faccia l'accesso ad un sondaggio di un altro utente premium, al massimo se cambio url per il get del codice posso mettere il codice di un sondaggio da me (utente premium) gestito:
try {
    $check_sondaggio = $pdo->prepare("SELECT Codice FROM Sondaggio WHERE EmailUtentecreante = :email AND Codice = :codice");
    $check_sondaggio->bindParam(':email', $_SESSION['email'], PDO::PARAM_STR);
    $check_sondaggio->bindParam(':codice', $codice_sondaggio, PDO::PARAM_INT);
    $check_sondaggio->execute();
    $sondaggio = $check_sondaggio->fetch(PDO::FETCH_ASSOC);
    $check_sondaggio->closeCursor();
} catch (PDOException $e) {
    echo "Errore Stored Procedure: " . $e->getMessage();
    header("Location: index.php");
    exit;
}
if (!$sondaggio) {
    header("Location: premium_home.php");
    exit;
}

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

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AnswerHUB | Visualizza risposte</title>

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
    </style>
</head>

<body>
    <!--Per ogni domanda mostra email utente e risposta-->
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

            <!--Prendi la lista di utenti che hanno risposto al sondaggio-->
            <?php
            try {
                $mostra_utenti_che_hanno_risposto = $pdo->prepare("CALL MostraUtentiCheHannoRisposto(:id_domanda, :codice_sondaggio)");
                $mostra_utenti_che_hanno_risposto->bindParam(':id_domanda', $id_domanda, PDO::PARAM_INT);
                $mostra_utenti_che_hanno_risposto->bindParam(':codice_sondaggio', $codice_sondaggio, PDO::PARAM_INT);
                $mostra_utenti_che_hanno_risposto->execute();
                $utenti_che_hanno_risposto = $mostra_utenti_che_hanno_risposto->fetchAll(PDO::FETCH_ASSOC);
                $mostra_utenti_che_hanno_risposto->closeCursor();
            } catch (PDOException $e) {
                echo "Errore Stored Procedure: " . $e->getMessage();
                header("Location: index.php");
                exit;
            }
            ?>

            <!--Per ogni utente che ha risposto, se ne esistono,
            mostra la risposta specificando l'email dell'utente-->
            <?php if (empty($utenti_che_hanno_risposto)) {
                echo "Non ci sono risposte";
            } else { ?>
                <?php foreach ($utenti_che_hanno_risposto as $utente_rispondente) { ?>
                    <?php
                    $email = $utente_rispondente['EmailUtente'];
                    ?>
                    <?php
                    ?>
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
                        } catch (PDOException $e) {
                            echo "Errore Stored Procedure: " . $e->getMessage();
                            header("Location: index.php");
                            exit;
                        }
                        echo 'Utente: ' . $email . ' Risposta: ' . $risposta['Testo'];
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
                            } catch (PDOException $e) {
                                echo "Errore Stored Procedure: " . $e->getMessage();
                                header("Location: index.php");
                                exit;
                            }
                            ?>

                            <!--Le opzioni vengono rappresentate con una radio, selezionate o deselezionate in base a quale opzione e' stata scelta (graficamente non si vede molto)-->
                            <input type="radio" name="opzione_selezionata_<?php echo $id_domanda ?>" checked disabled>
                            <label for="opzione_selezionata">
                            <?php echo 'Utente: ' . $email . ' Risposta: ' . $opzione_selezionata['Testo']; ?>
                            </label>
                    <?php } ?>
                <?php } ?>
            <?php } ?>
        </div>
    <?php } ?>
    <a href="premium_home.php">Torna alla home</a>
</body>

</html>