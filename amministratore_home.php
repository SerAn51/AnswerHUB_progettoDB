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
        <style>
            body {
                background-color: #0F2849;
                font-family: 'Poppins', sans-serif;
                height: 100vh;
                display: grid;
                grid-template-columns: 1fr;
                /*Divido le colonne in 300px per la sidebar, 1 frazione per tutto il resto*/
                grid-template-rows: 10px 1fr 10px;
                /*Divido le righe in una da 60px che sarà l'header, e 1fr per il contenuto main*/
                grid-template-areas:
                    "side header"
                    "side main"
                    "side footer";

            }

            .header {
                background-color: #0F2849;
                grid-area: header;
            }

            .footer {
                background-color: #0F2849;
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
                grid-template-rows: 1fr;
                grid-template-areas:
                    "c1 c2 c3";
                gap: 10px;
            }

            .space {
                background-color: #ffffff;
                color: #0c2840;
                border-radius: 30px;
                /*border: 2px solid #f3f7f9;*/
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

            ul {
                list-style: none;
            }
        </style>
    </head>

<body>
    <header class="header">
        <h3>Ciao
            <?php echo $dati_utente["Nome"]; ?>
        </h3>
        <a href="logout.php">Logout</a>
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