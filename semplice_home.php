<?php
require 'config_connessione.php'; // instaura la connessione con il db

$email = $_SESSION["email"];
// ora usa l'email passata tra una pagina e l'altra (salvato nella sessione) per fare una query sql
$query_sql = "SELECT * FROM Utente WHERE Email = ?";
$stmt = $pdo->prepare($query_sql);
$stmt->execute([$email]);
//estrazione della riga cosi' da poter usare i dati
$dati_utente = $stmt->fetch(PDO::FETCH_ASSOC);

//se sono amministratore/premium e cambio l'url per andare nella home dell'utente semplice, rimango sulla home amministratore/premium
if ($dati_utente["PAS"] === "AMMINISTRATORE") {
    header("Location: amministratore_home.php");
} else if ($dati_utente["PAS"] === "PREMIUM") {
    header("Location: premium_home.php");
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AnswerHUB | Home</title>

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

        /*Mostra l'inputbox per inserire il codice amministratore*/
        #inputbox_codice_amm {
            display: none;
        }

        #checkbox_codice_amm:checked~#inputbox_codice_amm {
            display: block;
            transition: .5s;
        }

        /*Gestione messaggi*/
        .accettato,
        .rifiutato {
            margin: 5px;
            width: 6%;
            border: 0;
            border-radius: 10px;
            cursor: pointer;
        }

        .accetta {
            background-color: green;
        }

        .rifiuta {
            background-color: darkred;
        }
    </style>
</head>

<body>
    <h1>Ciao
        <?php echo $dati_utente["Nome"]; ?>
    </h1>
    <h2>
        Diventa utente premium:
        <form action="script_php/diventa_premium.php" method="POST">
            <input type="submit" name="diventa_premium" id="diventa_premium"
                value="Abbonati per diventare un utente premium">
        </form>
    </h2>
    <h2>
        Spunta per registrarti come amministratore:
        <form action="script_php/diventa_amministratore.php" method="POST">
            <?php
            if ((isset($_GET['error'])) && ($_GET['error'] == 10)) {
                echo "Codice errato";
            }
            ?>
            <!--checkbox per inserire codice amministratore-->
            <label name="label_checkbox_codice_amm" id="label_checkbox_codice_amm" for="checkbox_codice_amm">
                <input type="checkbox" name="checkbox_codice_amm" id="checkbox_codice_amm">
                <!--input box Codice amministratore-->
                <div name="inputbox_codice_amm" id="inputbox_codice_amm" class="inputbox">
                    <input type="text" name="codice_amm" id="codice_amm">
                    <label for="codice_amm">Codice fornito</label>
                    <input type="submit" name="diventa_amministratore" id="diventa_ammnistratore"
                        value="Abbonati per diventare un utente premium">
                </div>
        </form>
    </h2>
    <!--COLLEGAMENTO E SCOLLEGAMENTO AD UN DOMINIO DI INTERESSE-->
    <div class="space">
        <form action="script_php/collega_domini.php" method="POST">
            <h2>Collega domini</h2>
            <ul>
                <?php
                //array con i domini selezionati dall'utente
                $prep_query_interessato = $pdo->prepare('SELECT * FROM Interessato WHERE EmailUtente = :email');
                $prep_query_interessato->bindParam(':email', $_SESSION["email"], PDO::PARAM_STR);
                $prep_query_interessato->execute();
                $domini_salvati_in_passato = $prep_query_interessato->fetchAll(PDO::FETCH_ASSOC);

                $prep_query_interessato->closeCursor();

                //lista di tutti i domini
                $sql = "CALL MostraDomini()";
                $mostra_domini = $pdo->prepare($sql);
                $mostra_domini->execute();
                $domini = $mostra_domini->fetchAll(PDO::FETCH_ASSOC);

                $mostra_domini->closeCursor();

                //TODO: non usare gli echo, vedi premium_home.php, stampa delle radio
                foreach ($domini as $dominio) {
                    echo '<li><label class="switch"><input type="checkbox" name="domini_selezionati[]" value="' . $dominio["Parolachiave"] . '"';
                    //checkala solo se e' gia' tra i domini di interesse
                    if (is_array($domini_salvati_in_passato)) {
                        foreach ($domini_salvati_in_passato as $dominio_salvato) {
                            if ($dominio["Parolachiave"] === $dominio_salvato["ParolachiaveDominio"]) {
                                echo 'checked';
                            }
                        }
                    }
                    echo '>' . $dominio["Parolachiave"] . ' ' . $dominio["Descrizione"] . '<span class="slider"></span></label></li>';
                }
                //Se la checkbox non è spuntata, controlla che sia presente tra i domini di interesse e, se lo e', elimina la riga.
                //ho una lista di tutti i domini, selezionandone alcuni ho un'altra lista...quindi sottraendo questa a quella con tutti, ottengo quelli non selezionati.
                //a questo punto posso prendere tutti quelli non selezionati e andarli a rimuovere dalla tabella se la parolachiave combacia.
                
                ?>
            </ul>
            <input type="submit" name="invia" id="invia" value="Collega domini">
        </form>
    </div>

    <!--INSERIMENTO PER LE RISPOSTE DI UN SONDAGGIO O VISUALIZZAZIONE RISPOSTE SONDAGGIO COMPLETATO-->
    <!--Idea: lista sondaggi accettati, cliccabili, che rimandano alla pagina con la lista di domande a cui rispondere.-->
    <div class="space">
        <h2>Rispondi ai sondaggi</h2>
        <?php
        $mostra_sondaggi_accettati = $pdo->prepare("CALL MostraSondaggiAccettati(:param1)");
        $mostra_sondaggi_accettati->bindParam(':param1', $email, PDO::PARAM_STR);
        $mostra_sondaggi_accettati->execute();
        $sondaggi_accettati = $mostra_sondaggi_accettati->fetchAll(PDO::FETCH_ASSOC);
        $mostra_sondaggi_accettati->closeCursor();
        ?>

        <?php foreach ($sondaggi_accettati as $sondaggio_accettato) { ?>
            <form action="rispondi_visualizza_sondaggio.php" method="POST">
                <?php $codice_sondaggio = $sondaggio_accettato['Codice'];

                $risposte_domande_aperte = $pdo->prepare("CALL MostraRisposteDomandeAperteSondaggio(:param1, :param2)");
                $risposte_domande_aperte->bindParam(':param1', $email, PDO::PARAM_STR);
                $risposte_domande_aperte->bindParam(':param2', $codice_sondaggio, PDO::PARAM_INT);
                $risposte_domande_aperte->execute();
                $risposte_domande_aperte->closeCursor();

                $opzioni_domande_chiuse = $pdo->prepare("CALL MostraOpzioniDomandeChiuseSondaggio(:param1, :param2)");
                $opzioni_domande_chiuse->bindParam(':param1', $email, PDO::PARAM_STR);
                $opzioni_domande_chiuse->bindParam(':param2', $codice_sondaggio, PDO::PARAM_INT);
                $opzioni_domande_chiuse->execute();
                $opzioni_domande_chiuse->closeCursor();

                $sondaggio_completato = true;
                // se entrambe le query ritornano una tabella vuota vuol dire che ancora non ho risposto al sondaggio
                if (($risposte_domande_aperte->rowCount() === 0) && ($opzioni_domande_chiuse->rowCount() === 0)) {
                    $sondaggio_completato = false;
                }
                ?>

                <label <?php echo $sondaggio_completato == true ? 'for="visualizza_risposte"' : 'for="rispondi"'; ?>>
                    Titolo:
                    <?php echo $sondaggio_accettato['Titolo']; ?>
                    Creatore:
                    <?php echo $sondaggio_accettato['Nome']; ?>
                    <?php echo $sondaggio_accettato['EmailUtentecreante']; ?>
                </label>
                <input type="hidden" name="codice_sondaggio" id="codice_sondaggio" value="<?php echo $codice_sondaggio ?>">
                <?php if ($sondaggio_completato) { // se e' true significa il sondaggio e' stato gia' completato?>
                    <input type="submit" name="visualizza_risposte" id="visualizza_risposte" value="Visualizza risposte">
                <?php } else { ?>
                    <input type="submit" name="rispondi" id="rispondi" value="Rispondi">
                <?php } ?>
            </form>
        <?php } ?>
    </div>

    <!--VISUALIZZAZIONE E ACCETTAZIONE/RIFIUTO DEGLI INVITI A PARTECIPARE AD UN SONDAGGIO-->
    <!--Idea: ho una lista di inviti, ogni invito ha un bottone per accettare (verde con una spunta) ed un bottone per rifiutare (rosso con una x)
        NB: se in un secondo momento rimuovo un dominio di interesse, gli inviti ai sondaggi ricevuti quando ero ancora interessato rimangono e posso ancora rispondere-->
    <div class="space">
        <h2>Inviti</h2>
        <ul>
            <?php
            //lista di tutti gli inviti dell'utente
            $mostra_inviti_utente = $pdo->prepare("CALL MostraInvitiUtente(:param1)");
            $mostra_inviti_utente->bindParam(':param1', $email, PDO::PARAM_STR);
            $mostra_inviti_utente->execute();
            $info_inviti = $mostra_inviti_utente->fetchAll(PDO::FETCH_ASSOC);
            $mostra_inviti_utente->closeCursor();

            foreach ($info_inviti as $info_invito) {
                echo '<li> ';
                if ($info_invito["Esito"] === "SOSPESO") {
                    //creo un form per il bottone accetta e uno per il bottone rifiuta, così da inviare il dato con POST
                    echo '<form action="script_php/accetta_rifiuta_invito.php" method="POST">
                    <input type="submit" class="accetta" name="invito_accettato" id="invito_accettato" value="' . $info_invito["ID"] . '">
                    <input type="submit" class="rifiuta" name="invito_rifiutato" id="invito_rifiutato" value="' . $info_invito["ID"] . '">
                    </form>';
                }
                echo $info_invito["ID"] . ' ' . $info_invito["Esito"] . ' ' . $info_invito["Titolo"];
                echo ' ' . $info_invito["DataCreazione"] . ' ' . $info_invito["DataChiusura"] . ' ' . $info_invito["ParolachiaveDominio"] . '</label></li>';
            }
            ?>
    </div>

    <!--VISUALIZZAZIONE DEI PREMI CONSEGUITI-->
    <div class="space">
        <h2>Premi conseguiti</h2>
        <ul>
            <?php
            //array con tutti i premi vinti dall'utente di sessione
            $prep_query_premi_vinti = $pdo->prepare('SELECT * FROM Vincente JOIN Premio ON Vincente.NomePremio=Premio.Nome WHERE Vincente.EmailUtente = :email');
            $prep_query_premi_vinti->bindParam(':email', $_SESSION["email"], PDO::PARAM_STR);
            $prep_query_premi_vinti->execute();
            $premi_vinti = $prep_query_premi_vinti->fetchAll(PDO::FETCH_ASSOC);

            $prep_query_premi_vinti->closeCursor();

            foreach ($premi_vinti as $premio_vinto) {
                echo '<li><label name="premio_vinto" value="' . $premio_vinto["NomePremio"] . '"';
                echo '>' . $premio_vinto["NomePremio"] . ' ' . $premio_vinto["Descrizione"] . '</li>';
            }
            ?>
        </ul>
    </div>

    <!--STATISTICHE (VISIBILI DA TUTTI GLI UTENTI)-->
    <!--inclue statistiche.php, si è optato per include in quanto le statistiche non sono fondamentali e se c'è un errore l'applicazione continua a funzionare, con require ci sarebbe un fatal error-->
    <?php
    include 'visualizza_statistiche.php';
    ?>

    <a href="logout.php">Effettua il logout</a>
</body>

</html>