<?php
require 'config_connessione.php'; // instaura la connessione con il db

try {
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
} catch (PDOException $e) {
    echo "Errore Stored Procedure: " . $e->getMessage();
    header("Location: logout.php");
    exit;
}

try {
    //utile per mostrare la lista di sondaggi
    $mostra_sondaggi_creati = $pdo->prepare("SELECT * FROM Sondaggio WHERE EmailUtentecreante = :email");
    $mostra_sondaggi_creati->bindParam(':email', $_SESSION["email"], PDO::PARAM_STR);
    $mostra_sondaggi_creati->execute();
    $sondaggi_creati = $mostra_sondaggi_creati->fetchAll(PDO::FETCH_ASSOC);
    $mostra_sondaggi_creati->closeCursor();
} catch (PDOException $e) {
    echo "Errore Stored Procedure: " . $e->getMessage();
    header("Location: logout.php");
    exit;
}

//utile per verificare che ci siano invitati al sondaggio
/*
$check_inviti = $pdo->prepare("SELECT * FROM Invito WHERE CodiceSondaggio = :codice_sondaggio");
$check_inviti->bindParam(':codice_sondaggio', $codice_sondaggio, PDO::PARAM_INT);
$check_inviti->execute();
$inviti = $check_inviti->fetchAll();
$check_inviti->closeCursor();
var_dump($codice_sondaggio);
*/
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
        <link rel="stylesheet" href="stile_css/input_statistiche_aggregate.css">
        <link rel="stylesheet" href="stile_css/radio_seleziona_sondaggio.css">
        <link rel="stylesheet" href="stile_css/crea_sondaggio_inputs.css">
        <link rel="stylesheet" href="stile_css/crea_sondaggio_button.css">
        <link rel="stylesheet" href="stile_css/bottone_elimina_sondaggio.css">
        <link rel="stylesheet" href="stile_css/bottone_invita.css">
        <link rel="stylesheet" href="stile_css/bottone_logout.css">
        <link rel="stylesheet" href="stile_css/tabella_classifica_utenti.css">
        <link rel="stylesheet" href="stile_css/tabella_premi.css">

        <style>
            body {
                text-align: center;

                background-color: #091d3e;
                font-family: 'Poppins', sans-serif;
                height: 100vh;
                display: grid;
                grid-template-columns: 1fr;
                /*Divido le colonne in 300px per la sidebar, 1 frazione per tutto il resto*/
                grid-template-rows: 100px 1fr 10px;
                /*Divido le righe in una da 60px che sarà l'header, e 1fr per il contenuto main*/
                grid-template-areas:
                    "header"
                    "main"
                    "footer";
            }

            .header {
                border-radius: 30px;
                background-color: #f1f1fa;
                grid-area: header;

                padding: 10px;
                margin-left: 20px;
                margin-right: 20px;
                margin-top: 20px;
                margin-bottom: 10px;

                display: flex;
                justify-content: space-between;
                align-items: center;

            }

            .header a {
                text-decoration: none;
                margin-right: 20px;
            }

            header h2 {
                color: #091d3e;
                margin-left: 20px;
            }

            .footer {
                background-color: #091d3e;
                grid-area: footer;
            }

            .main {
                border-radius: 30px;
                background-color: #f1f1fa;

                padding: 20px;
                margin: 20px;
                grid-area: main;

                display: grid;
                grid-template-columns: 1fr 1fr 1fr;
                /*Divido il main in 3 colonne*/
                grid-template-rows: 1fr 1fr 1fr;
                grid-template-areas:
                    "c1 c2 c3"
                    "c4 c5 c6";
                gap: 10px;
            }

            .space {
                background-color: #ffffff;
                color: #091d3e;
                border-radius: 30px;
                /*border: 2px solid #0F2849;*/
                box-shadow: 0 0 50px #ccc;
                display: flex;
                justify-content: center;
                text-align: center;
                width: auto;
                padding: 10px;
                margin: 20px;
            }

            .space:nth-child(1) {
                grid-area: c1;
            }

            .space:nth-child(2) {
                grid-area: c2;
            }

            .space:nth-child(3) {
                grid-area: c3;
            }

            .space:nth-child(4) {
                grid-area: c4;
            }

            .space:nth-child(5) {
                grid-area: c5;
            }

            .space:nth-child(6) {
                grid-area: c6;
            }

            ul {
                list-style: none;
                margin: 0;
                padding: 0;
            }

            .lista_scrollabile {
                height: 60vh;
                overflow-y: scroll;
                overflow-x: hidden;
            }

            .lista_scrollabile::-webkit-scrollbar {
                width: 5px;
                height: 100%;
            }

            .lista_scrollabile::-webkit-scrollbar-thumb {
                background-color: #091d3e;
                border-radius: 30px;
            }

            .lista_scrollabile ul {
                list-style: none;
                padding: 0;
                margin: 0;
            }

            .lista_scrollabile li {
                padding: 20px;
                margin: 0;
                border-color: #091d3e;
                border-radius: 30px;
            }

            .lista_scrollabile li a {
                text-decoration: none;
            }

            .dominio_lista_scrollabile {
                height: 20vh;
                overflow-y: scroll;
                overflow-x: hidden;
            }

            .dominio_lista_scrollabile::-webkit-scrollbar {
                width: 5px;
                height: 100%;
            }

            .dominio_lista_scrollabile::-webkit-scrollbar-thumb {
                background-color: #091d3e;
                border-radius: 30px;
            }

            .dominio_lista_scrollabile ul {
                list-style: none;
                padding: 0;
                margin: 0;
            }

            .dominio_lista_scrollabile .dominio_list_item {
                padding: 5px;
                margin: 0;
                border-color: #091d3e;
                border-radius: 30px;
            }

            .dominio_lista_scrollabile li a {
                text-decoration: none;
            }

            form {
                display: flex;
                flex-direction: column;
                /* Opzionale: allinea gli elementi in verticale */
                align-items: center;
                /* Allinea gli elementi in orizzontale */
                justify-content: center;
                /* Allinea gli elementi in verticale */
            }

            input,
            label,
            button {
                margin-top: 0.30rem;
                margin-bottom: 0.30rem;
            }

            .item {
                display: flex;
                align-items: center;
                /* Allinea verticalmente */
                gap: 10px;
                /* Aggiunge uno spazio tra i due elementi */
            }

            .item .delete_button {
                margin-left: auto;
                /* Sposta il pulsante a destra */
            }

            .item .titolo {
                text-transform: uppercase;
            }

            .space~#premi img {
                width: 70%;
            }
        </style>
    </head>

<body>

    <header class="header">
        <h2>Ciao
            <?php echo $dati_utente["Nome"]; ?>
        </h2>
        <a href="funzioni_semplice_premium.php">Vai alle funzioni da utente semplice</a>
        <a href="logout.php">
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

        <?php
        try {
            $mostra_domini = $pdo->prepare("CALL MostraDomini()");
            $mostra_domini->execute();
            $domini = $mostra_domini->fetchAll(PDO::FETCH_ASSOC);
            $mostra_domini->closeCursor();
        } catch (PDOException $e) {
            echo "Errore Stored Procedure: " . $e->getMessage();
            header("Location: logout.php");
            exit;
        }
        ?>

        <!--CREAZIONE DI UN NUOVO SONDAGGIO-->
        <!--
        - Per Stato metto di default APERTO...appena lo creo è aperto,
        - DataCreazione imposto in automatico oggi,
        - Per ParolachiaveDominio uso una radio con la lista dei domini e ne seleziono uno,
        - Per il creante sono un utente quindi inserisco l'email di sessione e null per il CFAziendacreante
        - Mostra un messaggio di errore se sto creando un sondaggio di cui gia' esiste il nome-->
        <div class="space">
            <form action="script_php/crea_sondaggio.php" method="POST">
                <h1>Crea sondaggio</h1>
                <?php if (isset($_GET['error'])) {
                    if ($_GET['error'] == 10) {
                        echo "Errore, titolo gia' presente";
                    } else if ($_GET['error'] == 20) {
                        echo "Errore, imposta una successiva a quella odierna";
                    }
                } else if (isset($_GET['success']) && $_GET['success'] == 10) {
                    echo "Sondaggio creato con successo";
                }
                ?>
                <div class="input-group">
                    <input type="Text" name="titolo" id="titolo" required autocomplete="off" class="input">
                    <label class="user-label">Titolo</label>
                </div>
                <!--
                <label for="titolo">Titolo</label>
                <input type="Text" name="titolo" id="titolo" required>
                -->
                <br>
                <div class="input-group">
                    <input type="number" min="1" name="max_utenti" id="max_utenti" required autocomplete="off"
                        class="input">
                    <label class="user-label">Max utenti</label>
                </div>
                <!--
                <label for="max_utenti">Max utenti</label>
                <input type="number" min="1" name="max_utenti" id="max_utenti" required>
                -->
                <br>
                <div class="input-group">
                    <input type="date" name="data_chiusura" id="data_chiusura" required autocomplete="off"
                        class="input">
                    <label class="user-label">Data di chiusura</label>
                </div>
                <!--
                <label for="data_chiusura">Data di chiusura</label>
                <input type="date" name="data_chiusura" id="data_chiusura" required>
                -->
                <br>

                <ul>
                    <h4>Seleziona dominio:</h4>
                    <div class="dominio_lista_scrollabile">
                        <div class="container">
                            <?php foreach ($domini as $dominio) { ?>
                                <li class="dominio_list_item">
                                    <label>
                                        <input type="radio" id="dominio" name="dominio"
                                            value="<?php echo $dominio['Parolachiave']; ?>">
                                        <span>
                                            <?php echo $dominio['Parolachiave']; ?>
                                        </span>
                                    </label>
                                </li>

                            <?php } ?>
                        </div>
                    </div>
                </ul>
        <br>
        <button class="crea" type="submit" name="crea" id="crea">
            Crea
            <div class="arrow-wrapper">
                <div class="arrow"></div>

            </div>
        </button>
        <!--<input type="submit" name="crea" id="crea" value="Crea">-->
        </form>
        </div>

        <!--INSERIMENTO DI UNA NUOVA DOMANDA-->
        <!--Idee:
    1) gestito dalla home con tutti i campi da compilare tra cui un menu a tendina che ti fa selezionare il sondaggio in cui inserire la domanda
    2) seleziono il sondaggio dalla lista di sondaggi e gestitsco in una pagina a parte...in cui mostro anche tutte le altre domande per aiutare l'utente premium ad avere un'idea a 360 gradi del sondaggio-->
        <div class="space">
            <ul>
                <h1>Inserisci domanda</h1>
                <h3>Elenco sondaggi:</h3>
                <div class="lista_scrollabile">
                    <?php foreach ($sondaggi_creati as $sondaggio_creato) { ?>
                        <li>
                            <a href="gestisci_domanda.php?cod_sondaggio=<?php echo $sondaggio_creato['Codice']; ?>">
                                <button class="link_sondaggio_button">
                                    <span class="circle" aria-hidden="true">
                                        <span class="icon arrow"></span>
                                    </span>
                                    <span class="link_sondaggio_button-text">
                                        <?php echo $sondaggio_creato['Titolo']; ?>
                                    </span>
                                </button>
                            </a>
                            <!--
                        <label>
                            <a href="gestisci_domanda.php?cod_sondaggio=<?php echo $sondaggio_creato['Codice']; ?>"><?php echo $sondaggio_creato['Titolo']; ?></a>
                        </label>
                        -->
                        </li>
                    <?php } ?>
                </div>
            </ul>
        </div>

        <!--CREAZIONE DI UN INVITO AD UN SONDAGGIO VERSO UN UTENTE DELLA PIATTAFORMA-->
        <!--Idea: gestione inviti fatta con una lista di utenti da invitare, questi utenti sono presi con una select che filtra gli utenti per dominio di interesse == dominio sondaggio;
inoltre, sulla home ho la lista dei sondaggi, clicco su un sondaggio e vado ad una pagina con la lista degli utenti da invitare, con checkbox-->
        <div class="space">
            <ul>
                <h1>Invita utenti</h1>
                <h3>Elenco sondaggi:</h3>
                <div class="lista_scrollabile">
                    <?php foreach ($sondaggi_creati as $sondaggio_creato) { ?>
                        <li>
                            <a href="inviti_sondaggio.php?cod_sondaggio=<?php echo $sondaggio_creato['Codice']; ?>">
                                <button class="link_sondaggio_button">
                                    <span class="circle" aria-hidden="true">
                                        <span class="icon arrow"></span>
                                    </span>
                                    <span class="link_sondaggio_button-text">
                                        <?php echo $sondaggio_creato['Titolo']; ?>
                                    </span>
                                </button>
                            </a>
                            <!--
                        <label>
                            <a href="inviti_sondaggio.php?cod_sondaggio=<?php //echo $sondaggio_creato['Codice']; ?>"><?php //echo $sondaggio_creato['Titolo']; ?></a>
                        </label>
                    -->
                        </li>
                    <?php } ?>
                </div>
            </ul>
        </div>

        <!--ELIMINA SONDAGGIO-->
        <div class="space">
            <ul>
                <h1>Elimina sondaggio</h1>
                <h3>Elenco sondaggi:</h3>
                <div class="lista_scrollabile">
                    <?php foreach ($sondaggi_creati as $sondaggio_creato) { ?>
                        <form action="script_php/elimina_sondaggio.php" method="POST">
                            <?php
                            try {
                                //utile per verificare che ci siano invitati al sondaggio
                                $codice_sondaggio = $sondaggio_creato['Codice'];
                                $check_inviti = $pdo->prepare("SELECT * FROM Invito WHERE CodiceSondaggio = :codice_sondaggio");
                                $check_inviti->bindParam(':codice_sondaggio', $codice_sondaggio, PDO::PARAM_INT);
                                $check_inviti->execute();
                                $inviti = $check_inviti->fetchAll();
                                $check_inviti->closeCursor();
                            } catch (PDOException $e) {
                                echo "Errore Stored Procedure: " . $e->getMessage();
                                header("Location: logout.php");
                                exit;
                            }
                            ?>
                            <!--Quando rimuovo da Sondaggio, deve rimuovere automaticamente anche da:
                        - ComponenteSondaggioDomanda
                        - Invito (se ci sono inviti e almeno uno non e' in sospeso, non mostra l'opzione, quindi la possibilità che rimuovendo un sondaggio vengano eliminati gli inviti non esiste)-->
                            <li class="item">
                                <label class="titolo">
                                    <!--<form action="script_php/elimina_sondaggio.php" method="POST">-->
                                    <?php echo $sondaggio_creato['Titolo']; ?>
                                </label>

                                <?php
                                // se non ci sono utenti invitati mostra il bottone per eliminare, se ci sono mostra i bottoni solo se nessuno ha ancora accettato l'invito
                                $tutti_sospesi = true;

                                // se la query restituisce almeno una riga, vuol dire che ho invitato almeno un utente quindi non posso piu' rimuovere sondaggi-->
                                if (($inviti && count($inviti) > 0)) {
                                    foreach ($inviti as $invito) {
                                        if ($invito['Esito'] == "ACCETTATO") {
                                            $tutti_sospesi = false;
                                            break;
                                        }
                                    }
                                } ?>
                                <?php
                                // se non ci sono invitati la variabile booleana non e' stata modificata quindi posso eliminare i sondaggi,
                                // se tutti gli invitati sono con Esito='Sospeso' oppure con Esito='Rifiutato' ho eseguito i controlli ma la variabile booleana non e' stata modificata, quindi posso eliminare il sondaggio
                                if ($tutti_sospesi) {
                                    ?>
                                    <input type="hidden" name="codice_sondaggio" id="codice_sondaggio"
                                        value="<?php echo $codice_sondaggio ?>">

                                    <button class="delete_button" type="submit" name="elimina" id="elimina">
                                        <span class="lable">X</span>
                                    </button>
                                    <!--<input type="submit" name="elimina" id="elimina" value="Elimina">-->
                                <?php } ?>
                            </li>
                        </form>
                    <?php } ?>
                </div>
            </ul>
        </div>

        <!--VISUALIZZA LE RISPOSTE DI UN SINDAGGIO-->
        <div class="space">
            <ul>
                <h1>Visualizza risposte sondaggio</h1>
                <h3>Elenco sondaggi:</h3>
                <div class="lista_scrollabile">
                    <?php foreach ($sondaggi_creati as $sondaggio_creato) { ?>

                        <li>
                            <a
                                href="visualizza_risposte_sondaggio.php?cod_sondaggio=<?php echo $sondaggio_creato['Codice']; ?>">
                                <button class="link_sondaggio_button">
                                    <span class="circle" aria-hidden="true">
                                        <span class="icon arrow"></span>
                                    </span>
                                    <span class="link_sondaggio_button-text">
                                        <?php echo $sondaggio_creato['Titolo']; ?>
                                    </span>
                                </button>
                            </a>
                            <!--
                        <label>
                            <a
                                href="visualizza_risposte_sondaggio.php?cod_sondaggio=<?php echo $sondaggio_creato['Codice']; ?>"><?php echo $sondaggio_creato['Titolo']; ?></a>
                        </label>
                        -->
                        </li>

                    <?php } ?>
                </div>
            </ul>
        </div>

        <!--STATISTICHE AGGREGATE SONDAGGIO-->
        <div class="space">
            <ul>
                <h1>Statistiche aggregate sondaggio</h1>
                <h3>Elenco sondaggi:</h3>
                <div class="lista_scrollabile">
                    <?php foreach ($sondaggi_creati as $sondaggio_creato) { ?>
                        <li>
                            <?php $codice_sondaggio = $sondaggio_creato['Codice']; ?>
                            <form action="visualizza_statistiche_aggregate.php" method="POST">
                                <button class="link_sondaggio_button" type="submit" name="statistiche_aggregate"
                                    id="statistiche_aggregate">
                                    <span class="circle" aria-hidden="true">
                                        <span class="icon arrow"></span>
                                    </span>
                                    <span class="link_sondaggio_button-text">
                                        <?php echo $sondaggio_creato['Titolo']; ?>
                                    </span>
                                </button>
                                <input type="hidden" name="codice_sondaggio" id="codice_sondaggio"
                                    value="<?php echo $codice_sondaggio ?>">
                            </form>
                        </li>


                    <?php } ?>
                </div>
            </ul>
        </div>

        <!--STATISTICHE (VISIBILI DA TUTTI GLI UTENTI)-->
        <!--inclue statistiche.php, si è optato per include in quanto le statistiche non sono fondamentali e se c'è un errore l'applicazione continua a funzionare, con require ci sarebbe un fatal error-->
        <?php
        include 'visualizza_statistiche.php';
        ?>
    </main>

    <section class="footer">

    </section>
</body>

</html>