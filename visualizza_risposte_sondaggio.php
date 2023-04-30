<?php

require 'config_connessione.php'; // instaura la connessione con il db

$codice_sondaggio = $_GET['cod_sondaggio'];

// controllo per evitare che si cambi url e si faccia l'accesso ad un sondaggio di un altro utente premium, al massimo se cambio url per il get del codice posso mettere il codice di un sondaggio da me (utente premium) gestito:
try {
    $check_sondaggio = $pdo->prepare("SELECT * FROM Sondaggio WHERE EmailUtentecreante = :email AND Codice = :codice");
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

    <link rel="stylesheet" href="stile_css/visualizza_risposte_sondaggio.css">
    <link rel="stylesheet" href="stile_css/bottone_logout.css">
    <link rel="stylesheet" href="stile_css/crea_sondaggio_inputs.css">
    <link rel="stylesheet" href="stile_css/upload_file.css">
    <link rel="stylesheet" href="stile_css/crea_sondaggio_button.css">
    <link rel="stylesheet" href="stile_css/bottone_opzioni.css">
    <link rel="stylesheet" href="stile_css/non_bottone_domanda_aperta.css">
    <link rel="stylesheet" href="stile_css/checkbox_invita_utente.css">

    <style>
        .lista_scrollabile {
            line-height: 1.5em;
            margin: 0 auto;
            max-width: 400px;
            max-height: 300px;
            overflow: hidden;
            border-color: #091d3e;
            overflow-y: auto;
            overflow-x: auto;
        }

        .lista_scrollabile::-webkit-scrollbar-vertical {
            width: 5px;
            height: 100%;
        }

        .lista_scrollabile::-webkit-scrollbar-thumb-vertical {
            background-color: #091d3e;
            border-radius: 30px;
        }

        .lista_scrollabile::-webkit-scrollbar-horizontal {
            width: 100%;
            height: 5px;
        }

        .lista_scrollabile::-webkit-scrollbar-thumb-horizontal {
            background-color: #091d3e;
            border-radius: 30px;
        }

        .lista_scrollabile li {
            padding: 0px 20px 40px 20px;
            margin: 0;
        }

        .lista_scrollabile li a {
            text-decoration: none;
        }

        .lista_scrollabile_orizzontalmente {
            margin-top: 0;
            margin-left: 0;
            height: auto;
            width: 600px;
            white-space: nowrap;
            overflow-y: hidden;
            overflow-x: scroll;
            text-align: left;
        }

        .lista_scrollabile_orizzontalmente::-webkit-scrollbar {
            width: 100%;
            height: 5px;
        }

        .lista_scrollabile_orizzontalmente::-webkit-scrollbar-thumb {
            background-color: #091d3e;
            border-radius: 30px;
        }

        .lista_scrollabile_orizzontalmente ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .lista_scrollabile_orizzontalmente li {
            padding: 10px;
            margin: 0;
        }

        .lista_scrollabile_orizzontalmente li a {
            text-decoration: none;
        }
    </style>
</head>

<body>

    <header class="header">
        <h1 class="lista_scrollabile_orizzontalmente">
            Risposte sondaggio
            <?php echo $sondaggio['Titolo']; ?>
        </h1>
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
        <!--Per ogni domanda mostra email utente e risposta-->
        <?php foreach ($domande_sondaggio as $domanda_sondaggio) { ?>
            <div class="space">

                <div class="titolo_immagine">
                    <!--Mostra domanda-->
                    <?php $id_domanda = $domanda_sondaggio['ID']; ?>
                    <div class="lista_scrollabile">
                        <h2>
                            <?php echo $domanda_sondaggio['Testo'] ?>
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
                    <?php } else {
                        echo "Nessuna immagine";
                    } ?>
                </div>

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

                <div class="risposte_utenti">
                    <ul>
                        <h2>Risposte utenti:</h2>
                        <!--Per ogni utente che ha risposto, se ne esistono,
            mostra la risposta specificando l'email dell'utente-->
                        <?php if (empty($utenti_che_hanno_risposto)) {
                            echo "Non ci sono risposte";
                        } else { ?>
                            <div class="lista_scrollabile">
                                <?php foreach ($utenti_che_hanno_risposto as $utente_rispondente) { ?>
                                    <?php
                                    $email = $utente_rispondente['EmailUtente'];
                                    ?>
                                    <li>
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
                                            echo '<strong>Utente:</strong> ' . $email;
                                            ?>
                                            <br>
                                            <?php
                                            echo '<strong>Risposta:</strong> ' . $risposta['Testo'];
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
                                                <?php
                                                echo '<strong>Utente:</strong> ' . $email;
                                                ?>
                                                <br>
                                                <?php
                                                echo '<strong>Risposta:</strong> ' . $opzione_selezionata['Testo']; ?>
                                        <?php } ?>

                                    </li>
                                <?php } ?>
                            </div>

                        <?php } ?>
                    </ul>
                </div>

            </div>
        <?php } ?>
    </main>

    <section class="footer">
    </section>
</body>

</html>