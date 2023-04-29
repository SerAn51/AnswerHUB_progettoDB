<!--COLLEGAMENTO E SCOLLEGAMENTO AD UN DOMINIO DI INTERESSE-->
<div class="space">
    <form action="script_php/collega_domini.php" method="POST">
        <h2>Collega domini</h2>
        <ul>
            <?php
            //array con i domini selezionati dall'utente
            try {
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
            } catch (PDOException $e) {
                echo "Errore Stored Procedure: " . $e->getMessage();
                header("Location: logout.php");
                exit;
            }

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
    <?php
    try {
        $mostra_sondaggi_accettati = $pdo->prepare("CALL MostraSondaggiAccettati(:param1)");
        $mostra_sondaggi_accettati->bindParam(':param1', $email, PDO::PARAM_STR);
        $mostra_sondaggi_accettati->execute();
        $sondaggi_accettati = $mostra_sondaggi_accettati->fetchAll(PDO::FETCH_ASSOC);
        $mostra_sondaggi_accettati->closeCursor();
    } catch (PDOException $e) {
        echo "Errore Stored Procedure: " . $e->getMessage();
        header("Location: logout.php");
        exit;
    }
    ?>

    <ul>
        <h2>Rispondi ai sondaggi</h2>
        <?php foreach ($sondaggi_accettati as $sondaggio_accettato) { ?>
            <form action="rispondi_visualizza_sondaggio.php" method="POST">
                <?php $codice_sondaggio = $sondaggio_accettato['Codice'];

                $creatore = $sondaggio_accettato['EmailUtentecreante']; // di base impostiamo come creatore la la mail dell'utente...
                // ...se, pero', e' null; allora il sondaggio è stato creato da un'azienda (lo controlliamo per sicurezza),
                // dunque impostiamo come creatore il nome dell'azienda usando il CF che conosciamo per ricavarne il nome
                try {
                    if (!isset($sondaggio_accettato['EmailUtentecreante']) && isset($sondaggio_accettato['CFAziendacreante'])) {
                        $mostra_dati_azienda = $pdo->prepare("SELECT * FROM Azienda WHERE CF = :cf_azienda");
                        $mostra_dati_azienda->bindParam(':cf_azienda', $sondaggio_accettato['CFAziendacreante'], PDO::PARAM_STR);
                        $mostra_dati_azienda->execute();
                        $dati_azienda = $mostra_dati_azienda->fetch(PDO::FETCH_ASSOC);
                        $mostra_dati_azienda->closeCursor();
                        $creatore = $dati_azienda['Nome'];
                    }
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
                } catch (PDOException $e) {
                    echo "Errore Stored Procedure: " . $e->getMessage();
                    header("Location: logout.php");
                    exit;
                }
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
                    <?php echo $creatore; ?>
                </label>
                <input type="hidden" name="codice_sondaggio" id="codice_sondaggio" value="<?php echo $codice_sondaggio ?>">
                <?php if ($sondaggio_completato) { // se e' true significa il sondaggio e' stato gia' completato?>
                    <input type="submit" name="visualizza_risposte" id="visualizza_risposte" value="Visualizza risposte">
                <?php } else { ?>
                    <input type="submit" name="rispondi" id="rispondi" value="Rispondi">
                <?php } ?>
            </form>
        <?php } ?>
    </ul>
</div>

<!--VISUALIZZAZIONE E ACCETTAZIONE/RIFIUTO DEGLI INVITI A PARTECIPARE AD UN SONDAGGIO-->
<!--Idea: ho una lista di inviti, ogni invito ha un bottone per accettare (verde con una spunta) ed un bottone per rifiutare (rosso con una x)
        NB: se in un secondo momento rimuovo un dominio di interesse, gli inviti ai sondaggi ricevuti quando ero ancora interessato rimangono e posso ancora rispondere-->
<div class="space">
    <h2>Inviti</h2>
    <ul>
        <?php
        //lista di tutti gli inviti dell'utente
        try {
            $mostra_inviti_utente = $pdo->prepare("CALL MostraInvitiUtente(:param1)");
            $mostra_inviti_utente->bindParam(':param1', $email, PDO::PARAM_STR);
            $mostra_inviti_utente->execute();
            $info_inviti = $mostra_inviti_utente->fetchAll(PDO::FETCH_ASSOC);
            $mostra_inviti_utente->closeCursor();
        } catch (PDOException $e) {
            echo "Errore Stored Procedure: " . $e->getMessage();
            header("Location: logout.php");
            exit;
        }

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
        try {
            $prep_query_premi_vinti = $pdo->prepare('SELECT * FROM Vincente JOIN Premio ON Vincente.NomePremio=Premio.Nome WHERE Vincente.EmailUtente = :email');
            $prep_query_premi_vinti->bindParam(':email', $_SESSION["email"], PDO::PARAM_STR);
            $prep_query_premi_vinti->execute();
            $premi_vinti = $prep_query_premi_vinti->fetchAll(PDO::FETCH_ASSOC);
            $prep_query_premi_vinti->closeCursor();
        } catch (PDOException $e) {
            echo "Errore Stored Procedure: " . $e->getMessage();
            header("Location: logout.php");
            exit;
        }

        foreach ($premi_vinti as $premio_vinto) {
            echo '<li><label name="premio_vinto" value="' . $premio_vinto["NomePremio"] . '"';
            echo '>' . $premio_vinto["NomePremio"] . ' ' . $premio_vinto["Descrizione"] . '</li>';
        }
        ?>
    </ul>
</div>