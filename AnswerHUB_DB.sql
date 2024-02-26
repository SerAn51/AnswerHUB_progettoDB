# Utile per gestire le foreign keys (update/delete)
set foreign_key_checks = 1;

# Utile per poter usare nel where una condizione che confronta campi non primary key
SET SQL_SAFE_UPDATES=0;

CREATE DATABASE IF NOT EXISTS AnswerHUB_DB;

# rimuove db pre-esistente con stesso nome
DROP DATABASE IF EXISTS AnswerHUB_DB;

# creazione database
CREATE DATABASE IF NOT EXISTS AnswerHUB_DB;

# seleiona database
USE AnswerHUB_DB;

# creazione tabelle
CREATE TABLE IF NOT EXISTS controllo_evento_log (
Data timestamp,
Messaggio varchar(255),
PRIMARY KEY (Data, Messaggio)
)
engine = innodb;

CREATE TABLE IF NOT EXISTS Utente (
Email varchar(255),
Pwd varchar(30),
Nome varchar(30),
Cognome varchar(30),
Annonascita timestamp,
Luogonascita varchar(50),
Totalebonus float,
PAS ENUM ('PREMIUM', 'AMMINISTRATORE', 'SEMPLICE'),
PRIMARY KEY (Email)
)
engine = innodb;

CREATE TABLE IF NOT EXISTS UtenteAmministratore (
Email varchar(255),
FOREIGN KEY (Email) REFERENCES Utente(Email) ON DELETE cascade ON UPDATE cascade,
PRIMARY KEY (Email)
)
engine = innodb;

CREATE TABLE IF NOT EXISTS UtentePremium (
Email varchar(255),
Datainizioabbonamento timestamp,
Datafineabbonamento timestamp,
Costo decimal,
numsondaggi integer DEFAULT 0,
FOREIGN KEY (Email) REFERENCES Utente(Email) ON DELETE cascade ON UPDATE cascade,
PRIMARY KEY (Email)
)
engine = innodb;

CREATE TABLE IF NOT EXISTS Azienda (
CF varchar(16), -- il CF di un'azienda e' di 16 caratteri
Pwd varchar(30),
Email varchar(255) UNIQUE,
Nome text,
Sede text,
PRIMARY KEY (CF)
)
engine = innodb;

CREATE TABLE IF NOT EXISTS Dominio (
Parolachiave varchar(255),
Descrizione text,
PRIMARY KEY (Parolachiave)
)
engine = innodb;

CREATE TABLE IF NOT EXISTS Sondaggio (
Codice integer AUTO_INCREMENT, #aggiunto
Titolo varchar(255),
Stato ENUM ('APERTO', 'CHIUSO'),
MaxUtenti integer,
DataCreazione timestamp, -- formato AAAA-MM-GG
DataChiusura timestamp,
ParolachiaveDominio varchar(255),
CFAziendacreante varchar(16),
EmailUtentecreante varchar(255),
FOREIGN KEY (ParolachiaveDominio) REFERENCES Dominio(Parolachiave) ON DELETE cascade ON UPDATE cascade,
FOREIGN KEY (CFAziendacreante) REFERENCES Azienda(CF) ON DELETE cascade ON UPDATE cascade,
FOREIGN KEY (EmailUtentecreante) REFERENCES UtentePremium(Email) ON DELETE cascade ON UPDATE cascade,
PRIMARY KEY (Codice)
)
engine = innodb;

CREATE TABLE IF NOT EXISTS Domanda (
ID integer auto_increment,
Testo varchar(3000),
Foto longblob, -- file fino a 4GB
Punteggio integer,
ApertaChiusa ENUM ('APERTA', 'CHIUSA'), -- aperta -> true = 1, chiusa -> false = 0
CFAziendainserente varchar(16),
EmailUtenteinserente varchar(255),
FOREIGN KEY (CFAziendainserente) REFERENCES Azienda(CF) ON DELETE cascade ON UPDATE cascade,
FOREIGN KEY (EmailUtenteinserente) REFERENCES UtentePremium(Email) ON DELETE cascade ON UPDATE cascade,
PRIMARY KEY (ID)
)
engine = innodb;

CREATE TABLE IF NOT EXISTS ComponenteSondaggioDomanda (
CodiceSondaggio integer,
IDDomanda integer,
PRIMARY KEY (CodiceSondaggio, IDDomanda),
FOREIGN KEY (CodiceSondaggio) REFERENCES Sondaggio(Codice) ON DELETE cascade ON UPDATE cascade,
FOREIGN KEY (IDDomanda) REFERENCES Domanda(ID) ON DELETE cascade ON UPDATE cascade
)
engine = innodb;

CREATE TABLE IF NOT EXISTS DomandaAperta (
ID integer,
MaxCaratteriRisposta integer,
FOREIGN KEY (ID) REFERENCES Domanda(ID) ON DELETE cascade ON UPDATE cascade,
PRIMARY KEY (ID)
)
engine = innodb;

CREATE TABLE IF NOT EXISTS DomandaChiusa (
ID integer,
FOREIGN KEY (ID) REFERENCES Domanda(ID) ON DELETE cascade ON UPDATE cascade,
PRIMARY KEY (ID)
)
engine = innodb;

CREATE TABLE IF NOT EXISTS Risposta (
ID integer AUTO_INCREMENT,
Testo text,
IDDomandaaperta integer,
EmailUtente varchar(255),
FOREIGN KEY (IDDomandaaperta) REFERENCES DomandaAperta(ID) ON DELETE cascade ON UPDATE cascade,
FOREIGN KEY (EmailUtente) REFERENCES Utente(Email) ON DELETE cascade ON UPDATE cascade,
PRIMARY KEY (ID)
)
engine = innodb;

CREATE TABLE IF NOT EXISTS Opzione (
IDDomandachiusa integer NOT NULL,
Numeroprogressivo integer NOT NULL, -- un trigger lo rende auto_increment rispetto a IDDomandachiusa
Testo text,
FOREIGN KEY (IDDomandachiusa) REFERENCES DomandaChiusa(ID) ON DELETE cascade ON UPDATE cascade,
PRIMARY KEY (IDDomandachiusa, Numeroprogressivo)
)
engine = InnoDB;

CREATE TABLE IF NOT EXISTS Interessato (
EmailUtente varchar(255),
ParolachiaveDominio varchar(255),
FOREIGN KEY (EmailUtente) REFERENCES Utente(Email) ON DELETE cascade ON UPDATE cascade,
FOREIGN KEY (ParolachiaveDominio) REFERENCES Dominio(Parolachiave) ON DELETE cascade ON UPDATE cascade,
PRIMARY KEY (EmailUtente, ParolachiaveDominio)
)
engine = innodb;

CREATE TABLE IF NOT EXISTS Invito (
ID integer AUTO_INCREMENT, #aggiunto
Esito ENUM ('SOSPESO', 'ACCETTATO', 'RIFIUTATO'),
EmailUtente varchar(255),
CodiceSondaggio integer,
CFAziendainvitante varchar(16),
EmailUtenteinvitante varchar(255),
FOREIGN KEY (EmailUtente) REFERENCES Utente(Email) ON DELETE cascade ON UPDATE cascade,
FOREIGN KEY (CodiceSondaggio) REFERENCES Sondaggio(Codice) ON DELETE cascade ON UPDATE cascade,
FOREIGN KEY (CFAziendainvitante) REFERENCES Azienda(CF) ON DELETE cascade ON UPDATE cascade,
FOREIGN KEY (EmailUtenteinvitante) REFERENCES UtentePremium(Email) ON DELETE cascade ON UPDATE cascade,
PRIMARY KEY (ID)
)
engine = innodb;

CREATE TABLE IF NOT EXISTS SelezionanteUtenteOpzione(
EmailUtente varchar(255),
IDDomandachiusaOpzione integer,
NumeroprogressivoOpzione integer,
FOREIGN KEY (EmailUtente) REFERENCES Utente(Email) ON DELETE cascade ON UPDATE cascade,
FOREIGN KEY (IDDomandachiusaOpzione, NumeroprogressivoOpzione) REFERENCES Opzione(IDDomandachiusa, Numeroprogressivo) ON DELETE cascade ON UPDATE cascade,
PRIMARY KEY (EmailUtente, NumeroprogressivoOpzione, IDDomandachiusaOpzione)
)
engine = InnoDB;

CREATE TABLE IF NOT EXISTS Premio (
Nome varchar(255),
Descrizione text,
Foto longblob,
Puntinecessari float,
EmailUtenteAmministratore varchar(255),
FOREIGN KEY (EmailUtenteAmministratore) REFERENCES UtenteAmministratore(Email) ON DELETE cascade ON UPDATE cascade,
PRIMARY KEY (Nome)
)
engine = innodb;

CREATE TABLE IF NOT EXISTS Vincente (
NomePremio varchar(255),
EmailUtente varchar(255),
FOREIGN KEY (NomePremio) REFERENCES Premio(Nome) ON DELETE cascade ON UPDATE cascade,
FOREIGN KEY (EmailUtente) REFERENCES Utente(Email) ON DELETE cascade ON UPDATE cascade,
PRIMARY KEY (NomePremio, EmailUtente)
)
engine = innodb;

# TRIGGER

# trigger per rendere auto_increment Opzione.Numeroprogressivo rispetto a Opzione.IDDomandachiusa
DELIMITER //
CREATE TRIGGER auto_increment_Numeroprogressivo
BEFORE INSERT ON Opzione
FOR EACH ROW
BEGIN
      DECLARE nNumprog INT;
      SELECT  COALESCE(MAX(Numeroprogressivo), 0) + 1 -- ritorna il primo valore non nullo tra max(numeroprogressivo e 0), e lo incrementa di 1
      INTO    nNumprog
      FROM    Opzione
      WHERE   IDDomandachiusa = NEW.IDDomandachiusa;
      SET NEW.Numeroprogressivo = nNumprog;
END
//
DELIMITER ;

# trigger per popolare automaticamente DomandaAperta/DomandaChiusa all inserimento di una nuova Domanda sulla base del campo boolean ApertaChiusa
DELIMITER //
CREATE TRIGGER PopolaDomandaApertaChiusa
AFTER INSERT ON Domanda
FOR EACH ROW
BEGIN
	IF (NEW.ApertaChiusa = 'APERTA') THEN
		INSERT INTO DomandaAperta(ID) VALUES (NEW.ID);
	END IF;
	IF (NEW.ApertaChiusa = 'CHIUSA') THEN
		INSERT INTO DomandaChiusa(ID) VALUES (NEW.ID);
	END IF;
END
//
DELIMITER ;

# trigger per popolare automaticamente UtentePremium/UtenteAmministratore all inserimento di un nuovo utente se è Premium/Amministratore
DELIMITER //
CREATE TRIGGER PopolaUtentePremiumAmministratore
AFTER INSERT ON Utente
FOR EACH ROW
BEGIN
	IF (NEW.PAS = 'PREMIUM') THEN
		INSERT INTO UtentePremium(Email, Datainizioabbonamento, Datafineabbonamento, Costo)
        VALUES (NEW.Email, NOW(), DATE_ADD(DATE_ADD(CURDATE(), INTERVAL 1 DAY), INTERVAL 1 YEAR), 10);
	END IF;
	IF (NEW.PAS = 'AMMINISTRATORE') THEN
		INSERT INTO UtenteAmministratore(Email) VALUES (NEW.Email);
	END IF;
END
//
DELIMITER ;

# trigger per popolare automaticamente UtentePremium/UtenteAmministratore se un utente semplice diventa in un secondo momento Premium/Amministratore
DELIMITER //
CREATE TRIGGER PopolaUtentePremiumAmministratoreDopoUpdate
AFTER UPDATE ON Utente
FOR EACH ROW
BEGIN
    IF (NEW.PAS = 'PREMIUM' AND OLD.PAS <> 'PREMIUM') THEN -- OLD.PAS messo per evitare che il trigger si attivi anche quando si attiva il trigger IncrementaTotaleBonus che fa un update su Invito ma l'utente era già premium quindi evita di fare una insert duplicata
        INSERT INTO UtentePremium(Email, Datainizioabbonamento, Datafineabbonamento, Costo)
        VALUES (NEW.Email, NOW(), DATE_ADD(DATE_ADD(CURDATE(), INTERVAL 1 DAY), INTERVAL 1 YEAR), 10);
    END IF;
    
    IF (NEW.PAS = 'AMMINISTRATORE' AND OLD.PAS <> 'AMMINISTRATORE') THEN
        INSERT INTO UtenteAmministratore(Email) VALUES (NEW.Email);
    END IF;
END;
//
DELIMITER ;

# trigger per rimuovere automaticamente UtentePremium/UtenteAmministratore se un utente Premium/Amministratore diventa Semplice in un secondo momento
DELIMITER //
CREATE TRIGGER RimuoviUtentePremiumAmministratore
AFTER UPDATE ON Utente
FOR EACH ROW
BEGIN
	IF (OLD.PAS = 'PREMIUM' AND NEW.PAS = 'SEMPLICE') THEN
		DELETE FROM UtentePremium WHERE (Email = NEW.Email);
	END IF;
	IF (OLD.PAS = 'AMMINISTRATORE' AND NEW.PAS = 'SEMPLICE') THEN
		DELETE FROM UtenteAmministratore WHERE (Email = NEW.Email);
	END IF;
END
//
DELIMITER ;

# EVENT per far tornare semplice un utente premium a cui scade l'abbonamento
# questo evento è complementare al trigger RimuoviUtentePremiumAmministratore
/*NB: questo comporta una serie di cose, a questo punto cosa succede ai sondaggi da lui creati quando era premium?
Diventa un utente semplice a tutti gli effetti. Se vuole controllare i sondaggi già fatti, deve riabbonarsi*/
DELIMITER //
CREATE EVENT ControlloDatafineabbonamento
ON SCHEDULE EVERY 1 DAY STARTS NOW()
DO
BEGIN
    -- Soluzione usata per evitare errore 1442, stessa tabella usata in una subquery

	-- creo una tabella temporanea per salvare gli indirizzi email degli utenti premium a cui è scaduta l'iscrizione
	CREATE TEMPORARY TABLE UtentiScaduti AS
	SELECT Email FROM UtentePremium WHERE DATE(Datafineabbonamento) = CURDATE();

	-- aggiorno il valore PAS degli utenti la cui mail corrisponde alle mail trovate prima (la tabella UtentePremium sarà "pulita" grazie al trigger RimuoviUtentePremiumAmministratore)
	UPDATE Utente SET PAS = 'SEMPLICE' WHERE Email IN (SELECT Email FROM UtentiScaduti);

	-- elimino la tabella temporanea
	DROP TABLE UtentiScaduti;
END
//
ALTER EVENT ControlloDatafineabbonamento ENABLE;
//
DELIMITER ;

/*
Utilizzare un trigger/evento per implementare l’operazione cambio di stato un sondaggio.
Un sondaggio diventa CHIUSO quando (i) la data di chiusura è anteriore alla data attuale
OPPURE (ii) è stato raggiunto un numero di utenti partecipanti (=che hanno accettato l’invito) pari a maxutenti.
*/
DELIMITER //
CREATE TRIGGER CambioStatoSondaggioMaxUtenti AFTER UPDATE ON Invito
FOR EACH ROW
BEGIN
   IF (NEW.Esito <> OLD.Esito) THEN
       UPDATE Sondaggio SET Stato = 'Chiuso' WHERE MaxUtenti = (SELECT COUNT(*) FROM Invito WHERE (Invito.Esito = 1) AND (Invito.CodiceSondaggio = Sondaggio.Codice));
   END IF;
END;

CREATE TRIGGER CambioStatoSondaggioData
BEFORE UPDATE ON Sondaggio
FOR EACH ROW
BEGIN
  IF (NEW.DataChiusura <= CURDATE()) THEN
    SET NEW.Stato = 'CHIUSO';
  END IF;
END;
//
DELIMITER ;

/*
Utilizzare un trigger per implementare l’operazione di assegnamento di un premio ad un utente,
quando il campo totalebonus dell’utente diventa maggiore del numero di punti richiesti dal premio stesso
*/
DELIMITER //
CREATE TRIGGER AssegnamentoPremio AFTER UPDATE ON Utente
FOR EACH ROW
BEGIN
    DECLARE done BOOLEAN DEFAULT FALSE;
    DECLARE NomePremio_locale varchar(255);
    DECLARE CurPremi CURSOR FOR SELECT Nome FROM Premio WHERE Puntinecessari <= NEW.Totalebonus;
    -- vengono salvati nel cursore CurPremi i nomi dei premi con il
    -- "Puntinecessari" inferiore al Totalebonus dell'utente
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;
    
    OPEN CurPremi;
    
    WHILE NOT done DO
        FETCH CurPremi INTO NomePremio_locale; -- for each
        IF (NomePremio_locale IS NOT NULL AND NOT done) THEN -- se c'e' un valore e non hai finito, allora inserisci in Vincente
            IF NOT EXISTS (SELECT * FROM Vincente WHERE NomePremio = NomePremio_locale AND EmailUtente = NEW.Email) THEN
            -- verifica se la combinazione di "NomePremio" e "EmailUtente" esiste già nella tabella "Vincente". Se non esiste, viene eseguito l'inserimento, altrimenti l'istruzione di inserimento viene ignorata.
            -- In questo modo si evita di inserire duplicati nella tabella "Vincente".
                INSERT INTO Vincente(NomePremio, EmailUtente)
                VALUES (NomePremio_locale, NEW.Email);
            END IF;
        END IF;
    END WHILE;
    
    CLOSE CurPremi;
END;
//
DELIMITER ;

/*Se viene inserito un premio e l'utente ha già i punti necessari, bisogna inserire il premio tra quelli vinti dall'utente*/
DELIMITER //
CREATE TRIGGER AssegnaPremioAppenaInserito AFTER INSERT ON Premio
FOR EACH ROW
BEGIN
    DECLARE done BOOLEAN DEFAULT FALSE;
    DECLARE EmailUtente VARCHAR(255);
    DECLARE TotalebonusUtente float;
    DECLARE cur CURSOR FOR SELECT Email, Totalebonus FROM Utente;
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;

    OPEN cur;

    read_loop: LOOP
        FETCH cur INTO EmailUtente, TotalebonusUtente;
        IF (NEW.Puntinecessari <= TotalebonusUtente AND NOT done) THEN
            INSERT INTO Vincente(NomePremio, EmailUtente)
            VALUES (NEW.Nome, EmailUtente);
        END IF;
        IF done THEN
            LEAVE read_loop;
        END IF;
    END LOOP;

    CLOSE cur;
END;
//
DELIMITER ;

/*Incrementa il totale bonus dell'utente di 0.5 quando accetta un sondaggio*/
DELIMITER //
CREATE TRIGGER IncrementaTotalebonus AFTER UPDATE ON Invito
FOR EACH ROW
BEGIN
    IF (NEW.Esito = 'ACCETTATO' AND OLD.Esito <> 'ACCETTATO') THEN
        UPDATE Utente SET Totalebonus = Totalebonus + 0.5 WHERE Email = NEW.EmailUtente; -- Email di utente comparata con Email di Invito (NEW)
    END IF;
END;
//
DELIMITER ;

/*
Utilizzare un trigger per incrementare di 1 unità il campo #numsondaggi quando
un utente premium inserisce un nuovo sondaggio.
*/
DELIMITER //
CREATE TRIGGER IncrementaNumsondaggi AFTER INSERT ON Sondaggio
FOR EACH ROW -- La clausola FOR EACH ROW specifica che il trigger deve essere eseguito per ogni riga che viene inserita nella tabella.
BEGIN
	UPDATE UtentePremium
	SET numsondaggi = numsondaggi + 1
	WHERE Email = NEW.EmailUtentecreante;
END;
//
DELIMITER ;

# STORED PROCEDURE
/*Registrazione utente*/
DELIMITER //
CREATE PROCEDURE InserisciUtente
(IN Email varchar(255), Pwd varchar(30), Nome varchar(30), Cognome varchar(30), Annonascita timestamp, Luogonascita varchar(50), PAS ENUM ('PREMIUM', 'AMMINISTRATORE', 'SEMPLICE'))
BEGIN
	INSERT INTO Utente(Email, Pwd, Nome, Cognome, Annonascita, Luogonascita, Totalebonus, PAS) VALUES
	(Email, Pwd, Nome, Cognome, Annonascita, Luogonascita, 0, PAS);
END
//
DELIMITER ;

/*Utente semplice diventa premium*/
DELIMITER //
CREATE PROCEDURE DiventaPremium
(IN Email_parametro varchar(255))
BEGIN
	UPDATE Utente
	SET PAS = 'PREMIUM'
	WHERE Email = Email_parametro;
END
//
DELIMITER ;

/*Utente semplice diventa amministratore*/
DELIMITER //
CREATE PROCEDURE DiventaAmministratore
(IN Email_parametro varchar(255))
BEGIN
	UPDATE Utente
	SET PAS = 'AMMINISTRATORE'
	WHERE Email = Email_parametro;
END
//
DELIMITER ;

/*Mostra i sondaggi*/
DELIMITER //
CREATE PROCEDURE MostraSondaggi
(EmailUtentecreante_parametro varchar(255), CFAziendacreante_parametro varchar(16))
BEGIN
	SELECT * FROM Sondaggio WHERE EmailUtentecreante = EmailUtentecreante_parametro OR CFAziendacreante = CFAziendacreante_parametro;
END
//
DELIMITER ;

/*Mostra domini*/
DELIMITER //
CREATE PROCEDURE MostraDomini
()
BEGIN
	SELECT * FROM Dominio;
END
//
DELIMITER ;

/*Inserisci interesse dell'utente per un dominio*/
DELIMITER //
CREATE PROCEDURE InserisciInteresse
(EmailUtente varchar(255), ParolachiaveDominio varchar(255))
BEGIN
	INSERT INTO Interessato(EmailUtente, ParolachiaveDominio) VALUES
	(EmailUtente, ParolachiaveDominio);
END
//
DELIMITER ;

/*Rimuovi interesse dell'utente per un dominio*/
DELIMITER //
CREATE PROCEDURE RimuoviInteresse
(EmailUtente_parametro varchar(255), ParolachiaveDominio_parametro varchar(255))
BEGIN
	DELETE FROM Interessato WHERE (EmailUtente = EmailUtente_parametro AND ParolachiaveDominio = ParolachiaveDominio_parametro);
END
//
DELIMITER ;

/*Mostra gli inviti dell'utente comprendendo le informazioni del sondaggio di riferimento*/
DELIMITER //
CREATE PROCEDURE MostraInvitiUtente
(EmailUtente_parametro varchar(255))
BEGIN
	SELECT Invito.ID, Invito.Esito, Sondaggio.Titolo, Sondaggio.DataCreazione, Sondaggio.DataChiusura, Sondaggio.ParolachiaveDominio
    FROM Invito JOIN Sondaggio ON Invito.CodiceSondaggio=Sondaggio.Codice
    WHERE Invito.EmailUtente = EmailUtente_parametro;
END
//
DELIMITER ;

/*Accetta/rifiuta invito: fa update dell'esito dell'invito settando a ACCETTATO o RIFIUTATO in base all'input della stored procedure*/
DELIMITER //
CREATE PROCEDURE AccettaRifiutaInvito
(Decisione boolean, /*1 -> true, 0 -> false*/ EmailUtente_parametro varchar(255), ID_parametro integer)
BEGIN
  
    IF (Decisione = 0) THEN
		UPDATE Invito
		SET Esito = "RIFIUTATO"
		WHERE EmailUtente = EmailUtente_parametro AND ID = ID_parametro;
	ELSEIF (Decisione = 1) THEN
		UPDATE Invito
		SET Esito = "ACCETTATO"
        WHERE EmailUtente = EmailUtente_parametro AND ID = ID_parametro;
	END IF;
    
END//
DELIMITER ;

/*Mostra la lista di sondaggi accettati dall'utente dato in input*/
DELIMITER //
CREATE PROCEDURE MostraSondaggiAccettati
(EmailUtente_parametro varchar(255))
BEGIN
	SELECT *
    FROM Invito JOIN Sondaggio ON Invito.CodiceSondaggio = Sondaggio.Codice
    WHERE EmailUtente = EmailUtente_parametro AND Esito = 'ACCETTATO';
END
//
DELIMITER ;

/*Inserisce la risposta alla domanda aperta*/
DELIMITER //
CREATE PROCEDURE InserisciRisposta
(Testo text, IDDomandaaperta integer, EmailUtente varchar(255))
BEGIN
	INSERT INTO Risposta(Testo, IDDomandaaperta, EmailUtente) VALUES
	(Testo, IDDomandaaperta, EmailUtente);
END
//
DELIMITER ;

/*Inserisce l'opzione selezionata per la domanda chiusa*/
DELIMITER //
CREATE PROCEDURE InserisciOpzioneRisposta
(EmailUtente varchar(255), IDDomandachiusaOpzione integer, NumeroprogressivoOpzione integer)
BEGIN
	INSERT INTO SelezionanteUtenteOpzione(EmailUtente, IDDomandachiusaOpzione, NumeroprogressivoOpzione) VALUES
	(EmailUtente, IDDomandachiusaOpzione, NumeroprogressivoOpzione);
END
//
DELIMITER ;

/*Mostra risposta in base all'utente dato in input e all'id della domanda dato in input*/
DELIMITER //
CREATE PROCEDURE MostraRispostaAperta
(EmailUtente_parametro varchar(255), IDDomandaaperta_parametro integer)
BEGIN
	SELECT * FROM Risposta WHERE EmailUtente = EmailUtente_parametro AND IDDomandaaperta = IDDomandaaperta_parametro;
END
//
DELIMITER ;

/*Mostra le risposte alla domanda aperta data in input*/
DELIMITER //
CREATE PROCEDURE MostraRisposte
(IDDomandaaperta_parametro integer)
BEGIN
	SELECT * FROM Risposta WHERE IDDomandaaperta = IDDomandaaperta_parametro;
END
//
DELIMITER ;

/*Mostra l'opzione selezionata dall'utente dato in input come risposta alla domanda il cui id e' dato in input*/
DELIMITER //
CREATE PROCEDURE MostraOpzioneSelezionata
(EmailUtente_parametro varchar(255), IDDomandachiusa_parametro integer)
BEGIN
	SELECT *
    FROM SelezionanteUtenteOpzione JOIN Opzione ON IDDomandachiusaOpzione = IDDomandachiusa AND NumeroprogressivoOpzione = Numeroprogressivo
    WHERE EmailUtente = EmailUtente_parametro AND IDDomandachiusa = IDDomandachiusa_parametro;
END
//
DELIMITER ;

/*Mostra le opzioni non selezionate dall'utente dato in input per la domanda chiusa data in input*/
DELIMITER //
CREATE PROCEDURE MostraOpzioniNonSelezionate(EmailUtente_parametro varchar(255), IDDomandachiusa_parametro integer)
BEGIN
    SELECT IDDomandachiusa, Numeroprogressivo, Testo
    FROM Opzione
    WHERE IDDomandachiusa = IDDomandachiusa_parametro

    AND (IDDomandachiusa, Numeroprogressivo) NOT IN (
        SELECT IDDomandachiusaOpzione, NumeroprogressivoOpzione
        FROM SelezionanteUtenteOpzione
        WHERE EmailUtente = EmailUtente_parametro AND IDDomandachiusa = IDDomandachiusa_parametro
    );
END//
DELIMITER ;

/*Mostra se l'utente dato in input ha risposto alle domande aperte per il sondaggio dato in input*/
DELIMITER //
CREATE PROCEDURE MostraRisposteDomandeAperteSondaggio
(EmailUtente_parametro varchar(255), CodiceSondaggio_parametro integer)
BEGIN
	SELECT *
    FROM Risposta JOIN ComponenteSondaggioDomanda ON IDDomandaaperta = IDDomanda
    WHERE EmailUtente = EmailUtente_parametro AND CodiceSondaggio = CodiceSondaggio_parametro;
END
//
DELIMITER ;

/*Mostra se ci sono risposte (opzioni selezionate per domande chiuse) per il sondaggio dato in input*/
DELIMITER //
CREATE PROCEDURE MostraOpzioniDomandeChiuseSondaggio
(EmailUtente_parametro varchar(255), CodiceSondaggio_parametro integer)
BEGIN
	SELECT *
    FROM SelezionanteUtenteOpzione JOIN ComponenteSondaggioDomanda ON IDDomandachiusaOpzione = IDDomanda
    WHERE EmailUtente = EmailUtente_parametro AND CodiceSondaggio = CodiceSondaggio_parametro;
END
//
DELIMITER ;

/*Mostra utenti che hanno risposto al sondaggio dato in input*/
DELIMITER //
CREATE PROCEDURE MostraUtentiCheHannoRisposto
(IDDomanda_parametro integer, CodiceSondaggio_parametro integer)
-- posso usare lo stesso iddomanda in quanto ogni domanda ha un id, e quindi una domanda aperta e chiusa con stesso id non possono esistere
BEGIN
	-- Qui prendo gli utenti che hanno risposto alle domande aperte del sondaggio (se ce ne sono)
	SELECT EmailUtente
	FROM Risposta 
	JOIN ComponenteSondaggioDomanda ON IDDomandaaperta = IDDomanda 
	WHERE IDDomandaaperta = IDDomanda_parametro AND CodiceSondaggio = CodiceSondaggio_parametro 
	UNION 
	-- Qui prendo gli utenti che hanno risposto alle domande chiuse del sondaggio (se ce ne sono)
	SELECT EmailUtente
	FROM SelezionanteUtenteOpzione 
	JOIN ComponenteSondaggioDomanda ON IDDomandachiusaOpzione = IDDomanda 
	WHERE IDDomandachiusaOpzione = IDDomanda_parametro AND CodiceSondaggio = CodiceSondaggio_parametro;
	-- Se questa query ritorna nessuna riga vuol dire che nessuno ha ancora risposto al sondaggio
END
//
DELIMITER ;

/*STATISTICHE*/

/*Visualizza utenti in ordine di punteggio max -> min*/
DELIMITER //
CREATE PROCEDURE VisualizzaClassifica
()
BEGIN
	SELECT Email, Totalebonus FROM Utente ORDER BY Totalebonus DESC;
END
//
DELIMITER ;

/*Visualizza tutti i premi*/
DELIMITER //
CREATE PROCEDURE VisualizzaPremi
()
BEGIN
	SELECT * FROM Premio ORDER BY Puntinecessari DESC;
END
//
DELIMITER ;

/*Inserisce un nuovo premio*/
DELIMITER //
CREATE PROCEDURE InserisciPremio
(Nome varchar(255), Descrizione text, Foto longblob, Puntinecessari float, EmailUtenteAmministratore varchar(255))
BEGIN
  
	INSERT INTO Premio(Nome, Descrizione, Foto, Puntinecessari, EmailUtenteAmministratore) VALUES
	(Nome, Descrizione, Foto, Puntinecessari, EmailUtenteAmministratore);

END//
DELIMITER ;

/*Inserisce un nuovo dominio*/
DELIMITER //
CREATE PROCEDURE InserisciDominio
(Parolachiave varchar(255), Descrizione text)
BEGIN
	INSERT INTO Dominio(Parolachiave, Descrizione) VALUES
	(Parolachiave, Descrizione);
END
//
DELIMITER ;

/*Crea un nuovo sondaggio*/
DELIMITER //
CREATE PROCEDURE CreaSondaggio
(Titolo varchar(255), Stato ENUM ('APERTO', 'CHIUSO'), MaxUtenti integer, DataCreazione timestamp, DataChiusura timestamp, ParolachiaveDominio varchar(255), CFAziendacreante varchar(16), EmailUtentecreante varchar(255))
BEGIN
	INSERT INTO Sondaggio(Titolo, Stato, MaxUtenti, DataCreazione, DataChiusura, ParolachiaveDominio, CFAziendacreante, EmailUtentecreante) VALUES
	(Titolo, Stato, MaxUtenti, DataCreazione, DataChiusura, ParolachiaveDominio, CFAziendacreante, EmailUtentecreante);
END
//
DELIMITER ;

/*Mostra gli utenti potenziali per essere invitati in automatico dall'azienda per lo specifico sondaggio*/
DELIMITER //
CREATE PROCEDURE MostraUtentiInteressati
(CodiceSondaggio_parametro integer)
BEGIN
	SELECT * FROM Sondaggio JOIN Interessato on Sondaggio.ParolachiaveDominio = Interessato.ParolachiaveDominio WHERE Sondaggio.Codice = CodiceSondaggio_parametro; 
END
//
DELIMITER ;

/*Mostra utenti interessati ad un determinato sondaggio in base al dominio di interesse e che non abbiano già l'invito per quel sondaggio*/
DELIMITER //
CREATE PROCEDURE MostraUtentiInteressatiSenzaInvito
(ParolachiaveDominio_parametro varchar(255), CodiceSondaggio_parametro integer) 
BEGIN 
    SELECT Utente.Email, Utente.Nome, Utente.Cognome, Utente.Annonascita, Utente.Luogonascita 
    FROM Utente JOIN Interessato ON Utente.Email=Interessato.EmailUtente JOIN Dominio ON Interessato.ParolachiaveDominio=Dominio.ParolaChiave JOIN Sondaggio ON Dominio.ParolaChiave=Sondaggio.ParolachiaveDominio 
    WHERE Dominio.ParolaChiave = ParolachiaveDominio_parametro AND Sondaggio.Codice = CodiceSondaggio_parametro 
    AND Utente.Email NOT IN 
        (SELECT EmailUtente 
         FROM Invito 
         WHERE CodiceSondaggio = CodiceSondaggio_parametro); 
END 
//
DELIMITER ;

/*Inserisci un invito per un utente*/
DELIMITER //
CREATE PROCEDURE InserisciInvito
(EmailUtente varchar(255), CodiceSondaggio integer, CFAziendainvitante varchar(16), EmailUtenteinvitante varchar(255))
BEGIN
	INSERT INTO Invito(Esito, EmailUtente, CodiceSondaggio, CFAziendainvitante, EmailUtenteinvitante) VALUES
	('SOSPESO', EmailUtente, CodiceSondaggio, CFAziendainvitante, EmailUtenteinvitante);
END
//
DELIMITER ;

/*Inserisce una nuova domanda, e se APERTA inserisce in DomandaAperta*/
DELIMITER //
CREATE PROCEDURE InserisciDomanda
(Testo varchar(3000), Foto longblob, Punteggio integer, ApertaChiusa ENUM ('APERTA', 'CHIUSA'), CFAziendainserente varchar(16), EmailUtenteinserente varchar(255), MaxCaratteriRisposta_parametro integer, CodiceSondaggio integer)
BEGIN
	DECLARE ultimoID integer;

	INSERT INTO Domanda(Testo, Foto, Punteggio, ApertaChiusa, CFAziendainserente, EmailUtenteinserente) VALUES
	(Testo, Foto, Punteggio, ApertaChiusa, CFAziendainserente, EmailUtenteinserente);
    
    SET ultimoID = LAST_INSERT_ID();
    
    INSERT INTO ComponenteSondaggioDomanda(CodiceSondaggio, IDDomanda) VALUES
    (CodiceSondaggio, ultimoID);
    
    IF (ApertaChiusa = "APERTA") THEN
    UPDATE DomandaAperta
    SET MaxCaratteriRisposta = MaxCaratteriRisposta_parametro
    WHERE ID = ultimoID;
    END IF;
END
//
DELIMITER ;

/*Mostra le domande in base al codice del sondaggio dato in input*/
DELIMITER //
CREATE PROCEDURE MostraDomande
(CodiceSondaggio_parametro integer)
BEGIN
	SELECT * FROM Domanda JOIN ComponenteSondaggioDomanda ON Domanda.ID = ComponenteSondaggioDomanda.IDDomanda WHERE CodiceSondaggio = CodiceSondaggio_parametro;
END
//
DELIMITER ;

/*Mostra le opzioni per il sondaggio dato in input*/
DELIMITER //
CREATE PROCEDURE MostraOpzioni
(IDDomandachiusa_parametro integer)
BEGIN
	SELECT * FROM Opzione WHERE IDDomandachiusa = IDDomandachiusa_parametro;
END
//
DELIMITER ;

/*Aggiungi un'opzione per la domanda data in input*/
DELIMITER //
CREATE PROCEDURE InserisciOpzione(IDDomandachiusa_parametro integer, Testo_parametro text)
BEGIN
  
	INSERT INTO Opzione (IDDomandachiusa, Testo) VALUES
    (IDDomandachiusa_parametro, Testo_parametro);

END//
DELIMITER ;

/*Elimina l'opzione data in input per la domanda data in input*/
# Nel momento in cui si rimuove, devo andare a ricalcolare i numeri progressivi delle opzioni in base all'id della domanda chiusa
DELIMITER //
CREATE PROCEDURE RimuoviOpzione(IDDomandachiusa_parametro INTEGER, Numeroprogressivo_parametro TEXT)
BEGIN
    -- Eseguo la rimozione dell'opzione con il parametro passato
    DELETE FROM Opzione WHERE IDDomandachiusa = IDDomandachiusa_parametro AND Numeroprogressivo = Numeroprogressivo_parametro;

    -- Aggiorno i progressivi delle opzioni rimanenti dopo la rimozione dell'opzione
    /*Decremento "Numeroprogressivo" di 1 per ogni riga che ha un "IDDomandachiusa"
	uguale a quello della riga eliminata e un "Numeroprogressivo" maggiore di quello della riga eliminata.
	In questo modo gli indici progressivi saranno automaticamente aggiornati ad ogni eliminazione,
	evitando "salti" tra le opzioni rimanenti*/
    UPDATE Opzione
    SET Numeroprogressivo = Numeroprogressivo - 1
    WHERE IDDomandachiusa = IDDomandachiusa_parametro AND Numeroprogressivo > Numeroprogressivo_parametro;
END//
DELIMITER ;

/*Conta il numero di risposte alla domanda aperta data in input*/
DELIMITER //
CREATE PROCEDURE ContaNumeroRisposteDomandaAperta
(IDDomandaaperta_parametro integer)
BEGIN
	SELECT COUNT(*) AS NumeroRisposte
	FROM Risposta
	WHERE IDDomandaaperta = IDDomandaaperta_parametro;
END
//
DELIMITER ;

/*Conta il numero di risposte alla domanda aperta data in input*/
DELIMITER //
CREATE PROCEDURE ContaNumeroRisposteDomandaChiusa
(IDDomandachiusaOpzione_parametro integer)
BEGIN
	SELECT COUNT(*) AS NumeroRisposte
	FROM SelezionanteUtenteOpzione
	WHERE IDDomandachiusaOpzione = IDDomandachiusaOpzione_parametro;
END
//
DELIMITER ;

/*Conta il numero di occorrenze per una specifica opzione di uno specifico sondaggio*/
DELIMITER //
CREATE PROCEDURE ContaNumeroOccorrenzeOpzione
(IDDomandachiusaOpzione_parametro integer, NumeroprogressivoOpzione_parametro integer)
BEGIN
	SELECT COUNT(*) AS NumeroOccorrenze
    FROM SelezionanteUtenteOpzione
    WHERE IDDomandachiusaOpzione = IDDomandachiusaOpzione_parametro AND NumeroprogressivoOpzione = NumeroprogressivoOpzione_parametro;
END
//
DELIMITER ;

/*Elimina la domanda data in input*/
DELIMITER //
CREATE PROCEDURE RimuoviDomanda(ID_parametro INTEGER)
BEGIN
	DELETE FROM Domanda WHERE (ID = ID_parametro);
END//
DELIMITER ;

/*Elimina il sondaggio dato in input*/
DELIMITER //
CREATE PROCEDURE EliminaSondaggio(Codice_parametro INTEGER)
BEGIN
	DELETE FROM Sondaggio WHERE (Codice = Codice_parametro);
END//
DELIMITER ;

/*Registrazione azienda*/
DELIMITER //
CREATE PROCEDURE InserisciAzienda(CF varchar(16), Pwd varchar(30), Email varchar(255), Nome text, Sede text)
BEGIN
	INSERT INTO Azienda (CF, Pwd, Email, Nome, Sede) VALUES
    (CF, Pwd, Email, Nome, Sede);
END//
DELIMITER ;