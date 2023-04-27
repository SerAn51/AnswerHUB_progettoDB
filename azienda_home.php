<?php
require 'config_connessione.php'; // instaura la connessione con il db

//utile per mostrare la lista di sondaggi
try {+0}
$mostra_sondaggi_creati = $pdo->prepare("SELECT * FROM Sondaggio WHERE CFAziendacreante = :cf_azienda");
$mostra_sondaggi_creati->bindParam(':cf_azienda', $_SESSION["cf_azienda"], PDO::PARAM_STR);
$mostra_sondaggi_creati->execute();
$sondaggi_creati = $mostra_sondaggi_creati->fetchAll(PDO::FETCH_ASSOC);
$mostra_sondaggi_creati->closeCursor();

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
    </style>


</head>

<body>
    <h1>Home azienda
        <?php echo $_SESSION['cf_azienda'] ?>
    </h1>

    <!--STATISTICHE AGGREGATE SONDAGGIO-->
    <div class="space">
        <h2>Statistiche aggregate sondaggio</h2>
        <?php foreach ($sondaggi_creati as $sondaggio_creato) { ?>
            <?php $codice_sondaggio = $sondaggio_creato['Codice']; ?>
            <form action="visualizza_statistiche_aggregate.php" method="POST">
                <label>
                    <?php echo $sondaggio_creato['Titolo']; ?>
                </label>

                <input type="hidden" name="codice_sondaggio" id="codice_sondaggio" value="<?php echo $codice_sondaggio ?>">
                <input type="submit" name="statistiche_aggregate" id="statistiche_aggregate"
                    value="Visualizza statistiche aggregate">
            </form>
        <?php } ?>
    </div>

    <!--CREAZIONE DI UN NUOVO SONDAGGIO-->
    <!--
        - Per Stato metto di default APERTO...appena lo creo è aperto,
        - DataCreazione imposto in automatico oggi,
        - Per ParolachiaveDominio uso una radio con la lista dei domini e ne seleziono uno,
        - Per il creante sono una azienda quindi inserisco il cf di sessione e null per il EmailUtentecreante
        - Mostra un messaggio di errore se sto creando un sondaggio di cui gia' esiste il nome-->
    <?php
    $mostra_domini = $pdo->prepare("CALL MostraDomini()");
    $mostra_domini->execute();
    $domini = $mostra_domini->fetchAll(PDO::FETCH_ASSOC);
    $mostra_domini->closeCursor();

    ?>
    <div class="space">
        <form action="script_php/crea_sondaggio.php" method="POST">
            <h2>Crea sondaggio</h2>
            <?php if (isset($_GET['error'])) {
                if ($_GET['error'] == 10) {
                    echo "Errore, titolo gia' presente";
                } else if ($_GET['error'] == 11) {
                    echo "Errore, imposta una successiva a quella odierna";
                }
            } else if (isset($_GET['success']) && $_GET['success'] == 10) {
                echo "Sondaggio creato con successo";
            }
            ?>
            <input type="Text" name="titolo" id="titolo" required>
            <label for="titolo">Titolo</label>
            <input type="number" min="1" name="max_utenti" id="max_utenti" required>
            <label for="max_utenti">Max utenti</label>
            <input type="date" name="data_chiusura" id="data_chiusura" required>
            <label for="data_chiusura">Data di chiusura</label>
            <?php foreach ($domini as $dominio): ?>
                <label>
                    <input type="radio" name="dominio" id="dominio" value="<?php echo $dominio['Parolachiave']; ?>">
                    <?php echo $dominio['Parolachiave']; ?>
                </label>
            <?php endforeach; ?>
            <input type="submit" name="crea" id="crea" value="Crea">
        </form>
    </div>


    <!--GESTIONE SONDAGGI: INVIO DI INVITI E INSERIMENTO DOMANDA-->
    <div class="space">
        <h2>Gestione sondaggi</h2>
        <?php foreach ($sondaggi_creati as $sondaggio_creato) { ?>
            <?php
            //utile per verificare che ci siano invitati al sondaggio
            $codice_sondaggio = $sondaggio_creato['Codice'];
            $check_inviti = $pdo->prepare("SELECT * FROM Invito WHERE CodiceSondaggio = :codice_sondaggio");
            $check_inviti->bindParam(':codice_sondaggio', $codice_sondaggio, PDO::PARAM_INT);
            $check_inviti->execute();
            $inviti = $check_inviti->fetchAll();
            $check_inviti->closeCursor();
            ?>
            <label>
                <a href="gestisci_domanda.php?cod_sondaggio=<?php echo $sondaggio_creato['Codice']; ?>"><?php echo $sondaggio_creato['Titolo']; ?></a>
            </label>

            <?php if (isset($_GET['error'])) {
                if ($_GET['error'] == 20) {
                    echo "Errore: questo sondaggio non ha domande, aggiungi delle domande";
                } else if ($_GET['error'] == 21) {
                    echo "Errore: una domanda e' senza opzioni";
                } else if ($_GET['error'] == 22) {
                    echo "Non ci sono utenti interessati al dominio di questo sondaggio";
                }
            } else if (isset($_GET['success']) && $_GET['success'] == 20) {
                echo "Sondaggio creato con successo";
            }
            ?>

            <?php
            // se e' stato invitato almeno un utente, non mostrare piu' il bottone per invitare (a differenza del poter eliminare il sondaggio, lasciare in questo caso la possibilità di invitare non farebbe altro che duplicare gli inviti per gli stessi utenti, inutile ed inconsistente)
            $utenti_invitati = false;
            // se non ci sono utenti invitati mostra il bottone per eliminare, se ci sono mostra i bottoni solo se nessuno ha ancora accettato l'invito
            $tutti_sospesi = true;

            // se la query restituisce almeno una riga, vuol dire che ho invitato almeno un utente quindi non posso piu' rimuovere sondaggi-->
            if (($inviti && count($inviti) > 0)) {
                $utenti_invitati = true;
                foreach ($inviti as $invito) {
                    if ($invito['Esito'] == "ACCETTATO") {
                        $tutti_sospesi = false;
                        break;
                    }
                }
            } ?>
            <?php
            // se non ci sono invitati la variabile booleana non e' stata modificata quindi posso effettuare inviti
            if (!$utenti_invitati) { ?>
                <form action="script_php/manda_inviti_automatico.php" method="POST">
                    <input type="hidden" name="codice_sondaggio" id="codice_sondaggio" value="<?php echo $codice_sondaggio; ?>">
                    <input type="submit" name="invita" id="invita" value="Invita">
                </form>
            <?php } ?>

            <?php
            // se non ci sono invitati la variabile booleana non e' stata modificata quindi posso eliminare i sondaggi,
            // se tutti gli invitati sono con Esito='Sospeso' oppure con Esito='Rifiutato' ho eseguito i controlli ma la variabile booleana non e' stata modificata, quindi posso eliminare il sondaggio
            if ($tutti_sospesi) {
                ?>
                <form action="script_php/elimina_sondaggio.php" method="POST">
                    <input type="hidden" name="codice_sondaggio" id="codice_sondaggio" value="<?php echo $codice_sondaggio; ?>">
                    <input type="submit" name="elimina" id="elimina" value="Elimina">
                </form>
            <?php } ?>


        <?php } ?>
    </div>

    <a href="logout.php">Effettua il logout</a>
</body>

</html>