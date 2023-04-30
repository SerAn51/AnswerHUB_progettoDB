<?php
require 'config_connessione.php'; // instaura la connessione con il db

try {
    $mostra_dati_azienda = $pdo->prepare("SELECT * FROM Azienda WHERE CF = :cf_azienda");
    $mostra_dati_azienda->bindParam(':cf_azienda', $_SESSION["cf_azienda"], PDO::PARAM_STR);
    $mostra_dati_azienda->execute();
    $dati_azienda = $mostra_dati_azienda->fetch(PDO::FETCH_ASSOC);
    $mostra_dati_azienda->closeCursor();
} catch (PDOException $e) {
    echo "Errore Stored Procedure: " . $e->getMessage();
    header("Location: logout.php");
    exit;
}

//utile per mostrare la lista di sondaggi
try {
    $mostra_sondaggi_creati = $pdo->prepare("SELECT * FROM Sondaggio WHERE CFAziendacreante = :cf_azienda");
    $mostra_sondaggi_creati->bindParam(':cf_azienda', $_SESSION["cf_azienda"], PDO::PARAM_STR);
    $mostra_sondaggi_creati->execute();
    $sondaggi_creati = $mostra_sondaggi_creati->fetchAll(PDO::FETCH_ASSOC);
    $mostra_sondaggi_creati->closeCursor();
} catch (PDOException $e) {
    echo "Errore Stored Procedure: " . $e->getMessage();
    header("Location: logout.php");
    exit;
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
    <link rel="stylesheet" href="stile_css/input_statistiche_aggregate.css">
    <link rel="stylesheet" href="stile_css/radio_seleziona_sondaggio.css">
    <link rel="stylesheet" href="stile_css/crea_sondaggio_inputs.css">
    <link rel="stylesheet" href="stile_css/crea_sondaggio_button.css">
    <link rel="stylesheet" href="stile_css/bottone_elimina_sondaggio.css">
    <link rel="stylesheet" href="stile_css/bottone_invita.css">
    <link rel="stylesheet" href="stile_css/bottone_logout.css">
    <link rel="stylesheet" href="stile_css/bottone_opzioni.css">
    <link rel="stylesheet" href="stile_css/tabella_classifica_utenti.css">
    <link rel="stylesheet" href="stile_css/tabella_premi.css">
    <link rel="stylesheet" href="stile_css/azienda_home.css">
    <link rel="stylesheet" href="stile_css/bottone_invita_automatico_azienda.css">
    <link rel="stylesheet" href="stile_css/bottone_elimina_sondaggio_azienda.css">

</head>

<body>
    <header class="header">
        <h2>Home
            <?php echo $dati_azienda["Nome"]; ?>
        </h2>
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
        <!--CREAZIONE DI UN NUOVO SONDAGGIO-->
        <!--
        - Per Stato metto di default APERTO...appena lo creo è aperto,
        - DataCreazione imposto in automatico oggi,
        - Per ParolachiaveDominio uso una radio con la lista dei domini e ne seleziono uno,
        - Per il creante sono una azienda quindi inserisco il cf di sessione e null per il EmailUtentecreante
        - Mostra un messaggio di errore se sto creando un sondaggio di cui gia' esiste il nome-->
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
                <br>
                <div class="input-group">
                    <input type="number" min="1" name="max_utenti" id="max_utenti" required autocomplete="off"
                        class="input">
                    <label class="user-label">Max utenti</label>
                </div>
                <br>
                <div class="input-group">
                    <input type="date" name="data_chiusura" id="data_chiusura" required autocomplete="off"
                        class="input">
                    <label class="user-label">Data di chiusura</label>
                </div>
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
                                            <label class="lista_scrollabile_orizzontalmente">
                                                <?php echo $dominio['Parolachiave']; ?>
                                            </label>
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
            </form>
        </div>

        <!--GESTIONE SONDAGGI: INVIO DI INVITI E INSERIMENTO DOMANDA-->
        <div class="space_domande">
            <ul>
                <h1>Gestione sondaggi</h1>
                <p>Passa il mouse sul sondaggio
                    <br> per inserire una nuova domanda
                </p>
                <h3>Elenco sondaggi:</h3>
                <?php if (isset($_GET['error'])) {
                    if ($_GET['error'] == 20) {
                        echo "Errore: il sondaggio selezionato non ha domande, aggiungi delle domande";
                    } else if ($_GET['error'] == 21) {
                        echo "Errore: una domanda e' senza opzioni";
                    } else if ($_GET['error'] == 22) {
                        echo "Non ci sono utenti interessati al dominio di questo sondaggio";
                    }
                } else if (isset($_GET['success']) && $_GET['success'] == 20) {
                    echo "Sondaggio creato con successo";
                }
                ?>
                <div class="lista_scrollabile">
                    <?php foreach ($sondaggi_creati as $sondaggio_creato) { ?>
                        <li>
                            <?php
                            //utile per verificare che ci siano invitati al sondaggio
                            $codice_sondaggio = $sondaggio_creato['Codice'];
                            try {
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

                            <a href="gestisci_domanda.php?cod_sondaggio=<?php echo $sondaggio_creato['Codice']; ?>">
                                <div class="sondaggio">
                                    <span class="tooltip">Gestisci domande</span>
                                    <span>
                                        <label class="lista_scrollabile_orizzontalmente">
                                            <?php echo $sondaggio_creato['Titolo']; ?>
                                        </label>
                                    </span>
                                </div>
                            </a>

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
                                    <input type="hidden" name="codice_sondaggio" id="codice_sondaggio"
                                        value="<?php echo $codice_sondaggio; ?>">
                                    <!--<input type="submit" name="invita" id="invita" value="Invita">-->
                                    <button type="submit" class="invita" name="invita" id="invita"
                                        value="<?php echo $info_invito["ID"]; ?>">
                                        <span class="invita_text">Invita</span>
                                        <span class="invita_icon"><svg xmlns="http://www.w3.org/2000/svg" width="24"
                                                viewBox="0 0 24 24" stroke-width="2" stroke-linejoin="round"
                                                stroke-linecap="round" stroke="currentColor" height="24" fill="none"
                                                class="svg">
                                                <line y2="19" y1="5" x2="12" x1="12"></line>
                                                <line y2="12" y1="12" x2="19" x1="5"></line>
                                            </svg></span>
                                    </button>
                                </form>
                            <?php } ?>

                            <?php
                            // se non ci sono invitati la variabile booleana non e' stata modificata quindi posso eliminare i sondaggi,
                            // se tutti gli invitati sono con Esito='Sospeso' oppure con Esito='Rifiutato' ho eseguito i controlli ma la variabile booleana non e' stata modificata, quindi posso eliminare il sondaggio
                            if ($tutti_sospesi) {
                                ?>
                                <form action="script_php/elimina_sondaggio.php" method="POST">
                                    <input type="hidden" name="codice_sondaggio" id="codice_sondaggio"
                                        value="<?php echo $codice_sondaggio; ?>">
                                    <!--<input type="submit" name="elimina" id="elimina" value="Elimina">-->
                                    <button type="submit" class="elimina" name="elimina" id="elimina"
                                        value="<?php echo $info_invito["ID"]; ?>">
                                        <span class="elimina_text">Elimina</span>
                                        <span class="elimina_icon"><svg xmlns="http://www.w3.org/2000/svg" width="24"
                                                viewBox="0 0 24 24" stroke-width="2" stroke-linejoin="round"
                                                stroke-linecap="round" stroke="currentColor" height="24" fill="none"
                                                class="svg">
                                                <line y2="12" y1="12" x2="19" x1="5"></line>
                                            </svg>
                                    </button>
                                </form>
                            <?php } ?>
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
    </main>

    <section class="footer">

    </section>
</body>

</html>