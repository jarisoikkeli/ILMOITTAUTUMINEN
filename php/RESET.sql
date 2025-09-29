-- ==============================
-- RESET: Ilmoittautumisjärjestelmän tietokanta
-- Tämä skripti tyhjentää kannan ja luo sen uudelleen testidatalla
-- ==============================

-- Luo tietokanta jos ei ole olemassa
CREATE DATABASE IF NOT EXISTS ilmoittautuminen
  DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;

USE ilmoittautuminen;

-- Poista ensin ilmoittautumiset (lapsitaulu)
DROP TABLE IF EXISTS ilmoittautumiset;

-- Sitten kilpailut (vanhempi)
DROP TABLE IF EXISTS kilpailut;

-- ------------------------------
-- Taulu: kilpailut
-- ------------------------------
CREATE TABLE kilpailut (
  id INT(11) NOT NULL AUTO_INCREMENT,
  nimi VARCHAR(255) NOT NULL,
  ajankohta DATE NOT NULL,
  ilmoittautuminen_alku DATE NOT NULL,
  ilmoittautuminen_loppu DATE NOT NULL,
  maksimi_osallistujat INT(11) DEFAULT NULL,
  info TEXT,
  luotu TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------
-- Taulu: ilmoittautumiset
-- ------------------------------
CREATE TABLE ilmoittautumiset (
  id INT(11) NOT NULL AUTO_INCREMENT,
  kilpailu_id INT(11) NOT NULL,
  nimi VARCHAR(255) NOT NULL,
  syntymaaika DATE NOT NULL,
  seura VARCHAR(255) DEFAULT NULL,
  sahkoposti VARCHAR(255) NOT NULL,
  muokkaus_token VARCHAR(64) NOT NULL,
  kilpailunumero INT(11) DEFAULT NULL,
  maksanut TINYINT(1) NOT NULL DEFAULT 0, -- 0 = ei maksanut, 1 = maksanut
  luotu TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  FOREIGN KEY (kilpailu_id) REFERENCES kilpailut(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------
-- Testidata kilpailut-tauluun
-- ------------------------------
INSERT INTO kilpailut (nimi, ajankohta, ilmoittautuminen_alku, ilmoittautuminen_loppu, maksimi_osallistujat, info)
VALUES
('SBU 10h', '2025-08-09', '2025-07-01', '2025-08-05', 200, '10 tunnin kisa. Reitti kulkee kauniissa maisemissa.'),
('Maraton 2025', '2025-09-15', '2025-07-15', '2025-09-10', 300, 'Klassinen maratonkisa kaupungin ympäri.');

-- ------------------------------
-- Testidata ilmoittautumiset-tauluun
-- ------------------------------
INSERT INTO ilmoittautumiset (kilpailu_id, nimi, syntymaaika, seura, sahkoposti, muokkaus_token, kilpailunumero, maksanut)
VALUES
(1, 'Matti Meikäläinen', '1980-05-12', 'Joensuun Juoksijat', 'matti@example.com', 'token123', 1, 0),
(1, 'Maija Mallikas', '1992-11-03', 'Helsingin Hölkkärit', 'maija@example.com', 'token456', 2, 1),
(2, 'Jari Soikkeli', '1970-02-11', 'Janakkalan Jana', 'jaris1970@gmail.com', 'token789', 1, 0);
