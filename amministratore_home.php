<?php
require 'config_connessione.php'; // instaura la connessione con il db

//LOGIN: se vado su questa pagina senza accesso, reindirizza al login
if (!(empty($_SESSION["email"]))) {
    $email = $_SESSION["email"];
    // ora usa l'email passata tra una pagina e l'altra (salvato nella sessione) per fare una query sql
    try {
        $query_sql = "SELECT * FROM UtenteAmministratore JOIN Utente ON UtenteAmministratore.Email=Utente.Email WHERE UtenteAmministratore.Email = ?";
        $stmt = $pdo->prepare($query_sql);
        $stmt->execute([$email]);
        //estrazione della riga cosi' da poter usare i dati
        $dati_utente = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        echo "Errore Stored Procedure: " . $e->getMessage();
        header("Location: logout.php");
        exit;
    }

    //se sono semplice/premium e cambio l'url per andare nella home dell'utente amministratore, rimango sulla home semplice/premium
    if (isset($dati_utente['PAS'])) {
        if ($dati_utente["PAS"] === "SEMPLICE") {
            header("Location: semplice_home.php");
        } else if ($dati_utente["PAS"] === "PREMIUM") {
            header("Location: premium_home.php");
        }
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
        <link rel="stylesheet" href="stile_css/input_statistiche_aggregate.css">
        <link rel="stylesheet" href="stile_css/radio_seleziona_sondaggio.css">
        <link rel="stylesheet" href="stile_css/crea_sondaggio_inputs.css">
        <link rel="stylesheet" href="stile_css/crea_sondaggio_button.css">
        <link rel="stylesheet" href="stile_css/bottone_elimina_sondaggio.css">
        <link rel="stylesheet" href="stile_css/bottone_invita.css">
        <link rel="stylesheet" href="stile_css/bottone_logout.css">
        <link rel="stylesheet" href="stile_css/tabella_classifica_utenti.css">
        <link rel="stylesheet" href="stile_css/tabella_premi.css">
        <link rel="stylesheet" href="stile_css/bottone_diventa_premium.css">
        <link rel="stylesheet" href="stile_css/bottone_rispondi_visualizza_risposte.css">
        <link rel="stylesheet" href="stile_css/bottone_accetta_invito.css">
        <link rel="stylesheet" href="stile_css/bottone_rifiuta_invito.css">

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
        </style>
    </head>

<body>
    <header class="header">
        <h2>Ciao
            <?php echo $dati_utente["Nome"]; ?>
        </h2>
        <a href="funzioni_semplice_amministratore.php">Vai alle funzioni da utente semplice</a>
        <a href="visualizza_log.php">Visualizza log</a>
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

        <!--INSERIMENTO DI UN NUOVO DOMINIO-->
        <div class="space">
            <form action="script_php/inserimento_dominio.php" method="POST">
                <h2>Inserisci un nuovo dominio</h2>
                <?php if (isset($_GET['error']) && ($_GET['error'] == 20)) {
                    echo "Errore, riprova";
                } else if (isset($_GET['success']) && $_GET['success'] == 20) {
                    echo "Dominio inserito con successo";
                }
                ?>
                <label for="parola_chiave">Parola Chiave</label>
                <input type="text" name="parola_chiave" id="parola_chiave" required>
                <br>
                <label for="descrizione">Descrizione</label>
                <input type="text" name="descrizione" id="descrizione" required>
                <br>
                <input type="submit" name="inserisci" id="inserisci" value="Inserisci">
            </form>
        </div>

        <!--LISTA DOMINI-->
        <div class="space">
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
            <?php if (empty($domini)) {
                echo "Non ci sono domini";
            } else { ?>
                <ul>
                    <h2>Domini</h2>
                    <?php
                    foreach ($domini as $dominio) {
                        ?>
                        <li>
                            <?php echo $dominio['Parolachiave'] . ' ' . $dominio['Descrizione']; ?>
                        </li>
                        <?php
                    }
                    ?>
                </ul>
            <?php } ?>
        </div>

        <!--INSERIMENTO DI UN NUOVO PREMIO: due input, uno per chiedere l'altro di conferma, fa una insert in Premio-->
        <div class="space">
            <form action="script_php/inserimento_premio.php" method="POST" enctype="multipart/form-data">
                <h2>Inserisci un nuovo premio</h2>
                <?php if (isset($_GET['error'])) {
                    if ($_GET['error'] == 10) {
                        echo "Formato non corretto, accettati: PNG, JPEG";
                    } else if ($_GET['error'] == 11) {
                        echo "Non hai inserito la foto";
                    }
                } else if (isset($_GET['success']) && $_GET['success'] == 10) {
                    echo "Premio inserito con successo";
                }
                ?>
                <label for="nome">Nome</label>
                <input type="text" name="nome" id="nome" required>
                <br>
                <label for="descrizione">Descrizione</label>
                <input type="text" name="descrizione" id="descrizione" required>
                <br>
                <label for="foto">Foto</label>
                <input type="file" name="foto" id="foto" required>
                <br>
                <label for="punti_necessari">Punti necessari</label>
                <input type="number" min="0" name="punti_necessari" id="punti_necessari" required>
                <br>
                <input type="submit" name="inserisci" id="inserisci" value="Inserisci">
            </form>
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