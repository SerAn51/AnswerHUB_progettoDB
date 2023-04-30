<!--COLLEGAMENTO E SCOLLEGAMENTO AD UN DOMINIO DI INTERESSE-->
<div class="space">
    <form action="script_php/collega_domini.php" method="POST">
        <ul>
            <h1>Collega domini</h1>
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
            ?>
            <div class="lista_scrollabile">
                <?php foreach ($domini as $dominio) { ?>
                    <li>
                        <div class="testo">
                            <?php echo $dominio['Parolachiave'] . ' | ' . $dominio['Descrizione']; ?>
                        </div>
                        <input class="switch" type="checkbox" name="domini_selezionati[]"
                            value="<?php echo $dominio['Parolachiave']; ?>" <?php
                               // checkala solo se e' gia' tra i domini di interesse
                               if (is_array($domini_salvati_in_passato)) {
                                   foreach ($domini_salvati_in_passato as $dominio_salvato) {
                                       if ($dominio['Parolachiave'] === $dominio_salvato['ParolachiaveDominio']) {
                                           echo ' checked';
                                       }
                                   }
                               } ?>>
                    </li>
                <?php } ?>
            </div>

            <!--
            //Se la checkbox non è spuntata, controlla che sia presente tra i domini di interesse e, se lo e', elimina la riga.
            //ho una lista di tutti i domini, selezionandone alcuni ho un'altra lista...quindi sottraendo questa a quella con tutti, ottengo quelli non selezionati.
            //a questo punto posso prendere tutti quelli non selezionati e andarli a rimuovere dalla tabella se la parolachiave combacia.
            
            ?>
                    -->
        </ul>
        <button class="crea" type="submit" name="invia" id="invia">
            Collega domini
            <div class="arrow-wrapper">
                <div class="arrow"></div>

            </div>
        </button>
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
        <h1>Rispondi ai sondaggi</h1>
        <div class="lista_scrollabile">
            <?php foreach ($sondaggi_accettati as $sondaggio_accettato) { ?>
                <li>
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
                            <strong>Titolo:</strong>
                            <?php echo $sondaggio_accettato['Titolo']; ?>
                            <br>
                            <strong>Creatore:</strong>
                            <?php echo $creatore; ?>
                        </label>
                        <input type="hidden" name="codice_sondaggio" id="codice_sondaggio"
                            value="<?php echo $codice_sondaggio ?>">
                        <?php if ($sondaggio_completato) { // se e' true significa il sondaggio e' stato gia' completato?>
                            <button class="visualizza_rispondi_sondaggio" type="submit" name="visualizza_risposte"
                                id="visualizza_risposte">
                                <span>Visualizza risposte</span>
                                <svg width="34" height="34" viewBox="0 0 74 74" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <circle cx="37" cy="37" r="35.5" stroke="#f1f1f1" stroke-width="3"></circle>
                                    <path
                                        d="M25 35.5C24.1716 35.5 23.5 36.1716 23.5 37C23.5 37.8284 24.1716 38.5 25 38.5V35.5ZM49.0607 38.0607C49.6464 37.4749 49.6464 36.5251 49.0607 35.9393L39.5147 26.3934C38.9289 25.8076 37.9792 25.8076 37.3934 26.3934C36.8076 26.9792 36.8076 27.9289 37.3934 28.5147L45.8787 37L37.3934 45.4853C36.8076 46.0711 36.8076 47.0208 37.3934 47.6066C37.9792 48.1924 38.9289 48.1924 39.5147 47.6066L49.0607 38.0607ZM25 38.5L48 38.5V35.5L25 35.5V38.5Z"
                                        fill="#f1f1f1"></path>
                                </svg>
                            </button>
                        <?php } else { ?>
                            <button class="visualizza_rispondi_sondaggio" type="submit" name="rispondi" id="rispondi">
                                <span>Rispondi</span>
                                <svg width="34" height="34" viewBox="0 0 74 74" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <circle cx="37" cy="37" r="35.5" stroke="#f1f1f1" stroke-width="3"></circle>
                                    <path
                                        d="M25 35.5C24.1716 35.5 23.5 36.1716 23.5 37C23.5 37.8284 24.1716 38.5 25 38.5V35.5ZM49.0607 38.0607C49.6464 37.4749 49.6464 36.5251 49.0607 35.9393L39.5147 26.3934C38.9289 25.8076 37.9792 25.8076 37.3934 26.3934C36.8076 26.9792 36.8076 27.9289 37.3934 28.5147L45.8787 37L37.3934 45.4853C36.8076 46.0711 36.8076 47.0208 37.3934 47.6066C37.9792 48.1924 38.9289 48.1924 39.5147 47.6066L49.0607 38.0607ZM25 38.5L48 38.5V35.5L25 35.5V38.5Z"
                                        fill="#f1f1f1"></path>
                                </svg>
                            </button>
                        <?php } ?>
                    </form>
                </li>
            <?php } ?>
        </div>
    </ul>
</div>

<!--VISUALIZZAZIONE E ACCETTAZIONE/RIFIUTO DEGLI INVITI A PARTECIPARE AD UN SONDAGGIO-->
<!--Idea: ho una lista di inviti, ogni invito ha un bottone per accettare (verde con una spunta) ed un bottone per rifiutare (rosso con una x)
        NB: se in un secondo momento rimuovo un dominio di interesse, gli inviti ai sondaggi ricevuti quando ero ancora interessato rimangono e posso ancora rispondere-->
<div class="space">
    <ul>
        <h1>Inviti</h1>
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
        ?>
        <div class="lista_scrollabile">
            <?php foreach ($info_inviti as $info_invito) { ?>
                <li>
                    <label>
                        <strong>Esito: </strong>
                        <?php echo $info_invito["Esito"]; ?>
                        <br>
                        <strong>Sondaggio: </strong>
                        <?php echo $info_invito["Titolo"]; ?>
                        <br>
                        <strong>Dominio: </strong>
                        <?php echo $info_invito["ParolachiaveDominio"]; ?>
                        <br>
                        <strong>Data chiusura: </strong>
                        <?php echo $info_invito["DataChiusura"]; ?>
                    </label>
                    <?php if ($info_invito["Esito"] === "SOSPESO") { ?>
                        <!-- creo un form per il bottone accetta e uno per il bottone rifiuta, così da inviare il dato con POST -->
                        <form action="script_php/accetta_rifiuta_invito.php" method="POST" class="accetta_rifiuta_invito">
                            <button type="submit" class="accetta" name="invito_accettato" id="invito_accettato"
                                value="<?php echo $info_invito["ID"]; ?>">
                                <span class="accetta_text">Accetta</span>
                                <span class="accetta_icon"><svg xmlns="http://www.w3.org/2000/svg" width="24"
                                        viewBox="0 0 24 24" stroke-width="2" stroke-linejoin="round" stroke-linecap="round"
                                        stroke="currentColor" height="24" fill="none" class="svg">
                                        <line y2="19" y1="5" x2="12" x1="12"></line>
                                        <line y2="12" y1="12" x2="19" x1="5"></line>
                                    </svg></span>
                            </button>
                            <button type="submit" class="rifiuta" name="invito_rifiutato" id="invito_rifiutato"
                                value="<?php echo $info_invito["ID"]; ?>">
                                <span class="rifiuta_text">Rifiuta</span>
                                <span class="rifiuta_icon"><svg xmlns="http://www.w3.org/2000/svg" width="24"
                                        viewBox="0 0 24 24" stroke-width="2" stroke-linejoin="round" stroke-linecap="round"
                                        stroke="currentColor" height="24" fill="none" class="svg">
                                        <line y2="12" y1="12" x2="19" x1="5"></line>
                                    </svg>
                            </button>
                        </form>
                    <?php } ?>
                </li>
            <?php } ?>
        </div>
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