<?php
require 'config_connessione.php'; // instaura la connessione con il db

//LOGIN: se vado su questa pagina senza accesso, reindirizza al login
if (!(empty($_SESSION["email"]))) {
    $email = $_SESSION["email"];
    // ora usa l'email passata tra una pagina e l'altra (salvato nella sessione) per fare una query sql
    $query_sql = "SELECT * FROM UtentePremium JOIN Utente ON UtentePremium.Email=Utente.Email WHERE UtentePremium.Email = ?";
    $stmt = $pdo->prepare($query_sql);
    $stmt->execute([$email]);
    //estrazione della riga cosi' da poter usare i dati
    $dati_utente = $stmt->fetch(PDO::FETCH_ASSOC);

    //se sono semplice/amministratore e cambio l'url per andare nella home dell'utente premium, rimango sulla home semplice/amministratore
    if ($dati_utente["PAS"] === "SEMPLICE") {
        header("Location: semplice_home.php");
    } else if ($dati_utente["PAS"] === "AMMINISTRATORE") {
        header("Location: amministratore_home.php");
    }
} else {
    header("Location: login.php"); //inoltre, cosi' che se provo ad andare in index dall'url, mi rimanda al login
}

//utile per mostrare la lista di sondaggi
$mostra_sondaggi_creati = $pdo->prepare("SELECT * FROM Sondaggio WHERE EmailUtentecreante = :email");
$mostra_sondaggi_creati->bindParam(':email', $_SESSION["email"], PDO::PARAM_STR);
$mostra_sondaggi_creati->execute();
$sondaggi_creati = $mostra_sondaggi_creati->fetchAll(PDO::FETCH_ASSOC);
$mostra_sondaggi_creati->closeCursor();

//utile per verificare che ci siano invitati al sondaggio
$check_inviti = $pdo->prepare("SELECT * FROM Invito WHERE CodiceSondaggio = :codice_sondaggio");
$check_inviti->bindParam(':codice_sondaggio', $codice_sondaggio, PDO::PARAM_INT);
$check_inviti->execute();
$inviti = $check_inviti->fetchAll();
$check_inviti->closeCursor();
?>

<!DOCTYPE html>
<html lang="en">

<head>

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
        </style>
    </head>

<body>

    <!--GESTIONE SONDAGGI-->
    <div class="space">
        <h2>Gestisci sondaggi</h2>
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
            <!--TODO: rimuovi_sondaggio.php-->
            <!--Quando rimuovo da Sondaggio, deve rimuovere automaticamente anche da:
        - ComponenteSondaggioDomanda, Invito (se ci sono inviti e almeno uno non e' in sospeso, non mostra l'opzione, quindi la possibilità che rimuovendo un sondaggio vengano eliminati gli inviti non esiste)
    -->
            <form action="script_php/elimina_sondaggio.php" method="POST">

                <label>
                    <?php echo $sondaggio_creato['Titolo']; ?>
                </label>

                <?php
                $tutti_sospesi = true;
                // se non ci sono utenti invitati mostra il bottone per eliminare, se ci sono mostra i bottoni solo se nessuno ha ancora accettato l'invito
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
                    <input type="hidden" name="codice_sondaggio" id="codice_sondaggio" value="<?php echo $codice_sondaggio ?>">
                    <input type="submit" name="bottone" id="bottone" value="Rimuovi">
                <?php } ?>

            </form>
        <?php } ?>
    </div>

    <!--CREAZIONE DI UN NUOVO SONDAGGIO-->
    <!--
        - Per Stato metto di default APERTO...appena lo creo è aperto,
        - DataCreazione imposto in automatico oggi,
        - Per ParolachiaveDominio uso una radio con la lista dei domini e ne seleziono uno,
        - Per il creante sono un utente quindi inserisco l'email di sessione e null per il CFAziendacreante
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
            <?php if (isset($_GET['error']) && ($_GET['error'] == 10)) {
                echo "Errore, titolo gia' presente";
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

    <!--CREAZIONE DI UN INVITO AD UN SONDAGGIO VERSO UN UTENTE DELLA PIATTAFORMA-->
    <!--Idea: gestione inviti fatta con una lista di utenti da invitare, questi utenti sono presi con una select che filtra gli utenti per dominio di interesse == dominio sondaggio;
inoltre, sulla home ho la lista dei sondaggi, clicco su un sondaggio e vado ad una pagina con la lista degli utenti da invitare, con checkbox-->
    <div class="space">
        <h2>Invita utenti</h2>
        <?php foreach ($sondaggi_creati as $sondaggio_creato): ?>
            <label>
                <a href="inviti_sondaggio.php?cod_sondaggio=<?php echo $sondaggio_creato['Codice']; ?>"><?php echo $sondaggio_creato['Titolo']; ?></a>
            </label>
        <?php endforeach; ?>
    </div>

    <!--INSERIMENTO DI UNA NUOVA DOMANDA-->
    <!--Idee:
    1) gestito dalla home con tutti i campi da compilare tra cui un menu a tendina che ti fa selezionare il sondaggio in cui inserire la domanda
    2) seleziono il sondaggio dalla lista di sondaggi e gestitsco in una pagina a parte...in cui mostro anche tutte le altre domande per aiutare l'utente premium ad avere un'idea a 360 gradi del sondaggio-->
    <div class="space">
        <h2>Inserisci domanda</h2>
        <?php foreach ($sondaggi_creati as $sondaggio_creato): ?>
            <label>
                <a href="gestisci_domanda.php?cod_sondaggio=<?php echo $sondaggio_creato['Codice']; ?>"><?php echo $sondaggio_creato['Titolo']; ?></a>
            </label>
        <?php endforeach; ?>
    </div>

    <!--STATISTICHE (VISIBILI DA TUTTI GLI UTENTI)-->
    <!--inclue statistiche.php, si è optato per include in quanto le statistiche non sono fondamentali e se c'è un errore l'applicazione continua a funzionare, con require ci sarebbe un fatal error-->
    <?php
    include 'visualizza_statistiche.php';
    ?>

    <a href="logout.php">Effettua il logout</a>
</body>

</html>