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
    <!--TODO:
    - Creazione di un invito ad un sondaggio verso un utente della piattaforma,
    - Inserimento di una nuova domanda-->
    <!--Idea: gestione inviti fatta con una lista di utenti da invitare, questi utenti sono presi con una select che filtra gli utenti per dominio di interesse == dominio sondaggio;
inoltre, sulla home ho la lista dei sondaggi, clicco su un sondaggio e vado ad una pagina con la lista degli utenti da invitare, con checkbox-->

    <!--CREAZIONE DI UN NUOVO SONDAGGIO-->
    <!--
        - Per Stato metto di default APERTO...appena lo creo è aperto,
        - DataCreazione imposto in automatico oggi,
        - Per ParolachiaveDominio uso una radio con la lista dei domini e ne seleziono uno,
        - Per il creante sono un utente quindi inserisco l'email di sessione e null per il CFAziendacreante-->

    <?php
    $sql = "CALL MostraDomini()";
    $mostra_domini = $pdo->prepare($sql);
    $mostra_domini->execute();
    $domini = $mostra_domini->fetchAll(PDO::FETCH_ASSOC);

    $mostra_domini->closeCursor();
    ?>
    <div class="space">
        <form action="script_php/crea_sondaggio.php" method="POST">
            <h2>Crea sondaggio</h2>
            <?php if (isset($_GET['error']) && ($_GET['error'] == 10)) {
                echo "Errore, riprova";
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
            <?php foreach ($domini as $dominio) : ?>
                <label>
                    <input type="radio" name="dominio" id="dominio" value="<?php echo $dominio['Parolachiave']; ?>">
                    <?php echo $dominio['Parolachiave']; ?>
                </label>
            <?php endforeach; ?>
            <input type="submit" name="crea" id="crea" value="Crea">
        </form>
    </div>

    <!--STATISTICHE (VISIBILI DA TUTTI GLI UTENTI)-->
    <!--inclue statistiche.php, si è optato per include in quanto le statistiche non sono fondamentali e se c'è un errore l'applicazione continua a funzionare, con require ci sarebbe un fatal error-->
    <?php
    include 'visualizza_statistiche.php';
    ?>

    <a href="logout.php">Effettua il logout</a>
</body>

</html>