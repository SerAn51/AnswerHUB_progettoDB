<?php
require 'config_connessione.php'; // instaura la connessione con il db

require 'config_conn_mongodb.php'; // instaura la connessione con mongodb, creando db e collezione se non esiste
use MongoDB\BSON\UTCDateTime;

//con accesso fatto, se provo a cambiare url e mettere registration.php reindirizza a index.php
if (!(empty($_SESSION["email"]))) {
    header("Location: index.php");
}

function inserisciUtente($pdo, $email, $password, $nome, $cognome, $data_nascita, $luogo_nascita, $tipo_utente, $collezione_log)
{
    try {
        $sql = "CALL InserisciUtente(:param1, :param2, :param3, :param4, :param5, :param6, :param7)";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':param1', $email, PDO::PARAM_STR);
        $stmt->bindParam(':param2', $password, PDO::PARAM_STR);
        $stmt->bindParam(':param3', $nome, PDO::PARAM_STR);
        $stmt->bindParam(':param4', $cognome, PDO::PARAM_STR);
        $stmt->bindParam(':param5', $data_nascita, PDO::PARAM_STR);
        $stmt->bindParam(':param6', $luogo_nascita, PDO::PARAM_STR);
        $stmt->bindParam(':param7', $tipo_utente, PDO::PARAM_STR);
        $stmt->execute();
    } catch (PDOException $e) {
        echo "Errore Stored Procedure: " . $e->getMessage();
        header("Location: index.php");
        exit;
    }
    // Informazione da inserire nella collezione di log
    $informazione_log = array(
        "data" => new MongoDB\BSON\UTCDateTime(),
        "azione" => "Inserimento utente",
        "dettagli" => array(
            "email" => $email,
            "nome" => $nome,
            "cognome" => $cognome,
            "data_nascita" => $data_nascita,
            "luogo_nascita" => $luogo_nascita,
            "tipo_utente" => $tipo_utente
        )
    );

    // Inserimento dell'informazione nella collezione di log
    $collezione_log->insertOne($informazione_log);
}

if (isset($_POST["submit"])) { // se submit avviene con successo
    // prendi tutti i dati del form e fai una query di inserimento nel db
    $nome = $_POST["nome"];
    $cognome = $_POST["cognome"];
    $data_nascita = $_POST["data_nascita"];
    $luogo_nascita = $_POST["luogo_nascita"];
    $email = $_POST["email"];
    $password = $_POST["password"];
    $conferma_password = $_POST["conferma_password"];
    //echo $nome . " " . $cognome . " " . $data_nascita . ' ' . $luogo_nascita . ' ' . $email . " " . $password . " " . $conferma_password;

    //inserisco i dati nel database
    if (isset($password) && isset($conferma_password)) {
        if ($password === $conferma_password) { //controllo che la password e la conferma siano uguali

            //controlla che l'utente già non sia registrato
            try {
                $query_sql = "SELECT * FROM Utente WHERE Email = ?";
                $stmt = $pdo->prepare($query_sql);
                $stmt->execute([$email]);
                //estrazione della riga e inserimento in db
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
            } catch (PDOException $e) {
                echo "Errore Stored Procedure: " . $e->getMessage();
                header("Location: index.php");
                exit;
            }
            //se e' null l'utente non esiste, quindi lo inserisco
            if (!(isset($row['Email']))) { //isset() verifica se una variabile è stata impostata e se il suo valore non è NULL. True se la variabile è stata impostata e ha un valore diverso da NULL, false in caso contrario.

                //imposta tipo utente a seconda che il codice amministratore ci sia e sia corretto
                if (isset($_POST['checkbox_codice_amm']) && $_POST['checkbox_codice_amm'] == 'on') { // La checkbox è stata selezionata
                    $codice_amministratore = $_POST["codice_amm"];
                    if (isset($codice_amministratore) && ($codice_amministratore == 66)) { //che bello Star Wars
                        $tipo_utente = "AMMINISTRATORE";
                        inserisciUtente($pdo, $email, $password, $nome, $cognome, $data_nascita, $luogo_nascita, $tipo_utente);
                        $messaggio = "Registrazione avvenuta con successo";
                        $tipo_messaggio = "successo";
                    } else {
                        $messaggio = "Hai sbagliato il codice per iscriverti come amministratore";
                        $tipo_messaggio = "errore";
                    }
                } else { // La checkbox non è stata selezionata
                    $tipo_utente = "SEMPLICE";
                    inserisciUtente($pdo, $email, $password, $nome, $cognome, $data_nascita, $luogo_nascita, $tipo_utente, $collezione_log);
                    $messaggio = "Registrazione avvenuta con successo";
                    $tipo_messaggio = "successo";
                }
            } else {
                $messaggio = "Utente gia' esistente";
                $tipo_messaggio = "errore";
            }
        } else {
            $messaggio = "Le password non corrispondono";
            $tipo_messaggio = "errore";
        }
    } else {
        $messaggio = "Errore";
        $tipo_messaggio = "errore";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AnswerHUB | Registrazione</title>

    <link rel="icon" type="image/png" href="images/favicon.png">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="stile_css/registrazione.css">

</head>
<!--
<body>
    <h2>Registration</h2>
    <form action="" method="POST">
        <label for="nome">Nome: </label>
        <input class="campo_inserimento" type="text" name="nome" id="nome" required> <br>

        <label for="cognome">Cognome: </label>
        <input class="campo_inserimento" type="text" name="cognome" id="cognome" required> <br>

        <label for="email">Email: </label>
        <input class="campo_inserimento" type="email" name="email" id="email" required> <br>

        <label for="password">Password: </label>
        <input class="campo_inserimento" type="password" name="password" id="password" required> <br>

        <label for="conferma_password">Conferma password: </label>
        <input class="campo_inserimento" type="password" name="conferma_password" id="conferma_password" required> <br>

        <input class="submit" type="submit" name="submit" id="submit" value="invia">
    </form>
    <br>
    <a href="login.php">Login</a>
</body>
    -->

<body>
    <section>
        <div class="login-box">
            <img src="images/logo.png" alt="Logo AnswerHUB" type="image/png" class="img-logo">
            <div class="form-box">
                <div class="form-value">
                    <form action="" method="POST">
                        <h2>Registrazione</h2>
                        <!--Eventuale messaggio-->
                        <div class="message <?php echo $tipo_messaggio; ?>"><?php echo isset($messaggio) ? $messaggio : ''; ?></div>
                        <!--input box Nome-->
                        <div class="inputbox">
                            <input type="text" name="nome" id="nome" required>
                            <label for="nome">Nome<label>
                        </div>
                        <!--input box Cognome-->
                        <div class="inputbox">
                            <input type="text" name="cognome" id="cognome" required>
                            <label for="cognome">Cognome</label>
                        </div>
                        <!--input box DataNascita-->
                        <div class="inputbox">
                            <input type="date" name="data_nascita" id="data_nascita" required>
                            <label for="data_nascita">Data di nascita</label>
                        </div>
                        <!--input box LuogoNascita-->
                        <div class="inputbox">
                            <input type="text" name="luogo_nascita" id="luogo_nascita" required>
                            <label for="luogo_nascita">Luogo di nascita</label>
                        </div>
                        <!--input box Email-->
                        <div class="inputbox">
                            <ion-icon name="mail-outline"></ion-icon>
                            <input type="email" name="email" id="email" required>
                            <label for="email">Email</label>
                        </div>
                        <!--input box Password-->
                        <div class="inputbox">
                            <ion-icon name="lock-closed-outline"></ion-icon>
                            <input type="password" name="password" id="password" required>
                            <label for="passowrd">Password</label>
                        </div>
                        <!--input box Conferma Password-->
                        <div class="inputbox">
                            <ion-icon name="lock-closed-outline"></ion-icon>
                            <input type="password" name="conferma_password" id="conferma_password" required>
                            <label for="conferma_password">Conferma Password</label>
                        </div>
                        <!--checkbox per inserire codice amministratore-->
                        <label name="label_checkbox_codice_amm" id="label_checkbox_codice_amm"
                            for="checkbox_codice_amm">Spunta per registrarti come amministratore</label>
                        <input type="checkbox" name="checkbox_codice_amm" id="checkbox_codice_amm">
                        <!--input box Codice amministratore-->
                        <div name="inputbox_codice_amm" id="inputbox_codice_amm" class="inputbox">
                            <input type="text" name="codice_amm" id="codice_amm">
                            <label for="codice_amm">Codice fornito</label>
                        </div>
                        <!--bottone registrati-->
                        <button class="submit" type="submit" name="submit" id="submit"> Registrati
                            <svg viewBox="0 0 16 16" class="bi bi-arrow-right" height="20" width="20"
                                xmlns="http://www.w3.org/2000/svg" style="background-color: #00f2fe" fill="#0c2840">
                                <path
                                    d="M1 8a.5.5 0 0 1 .5-.5h11.793l-3.147-3.146a.5.5 0 0 1 .708-.708l4 4a.5.5 0 0 1 0 .708l-4 4a.5.5 0 0 1-.708-.708L13.293 8.5H1.5A.5.5 0 0 1 1 8z"
                                    fill-rule="evenodd"></path>
                            </svg>
                        </button>
                        <!--Vai al login-->
                        <div class="access">
                            <p>Hai gia' un account? <a href="login.php">Accedi</a></p>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>
    <!--Le icone-->
    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
</body>

</html>