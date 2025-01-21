-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Gép: 127.0.0.1
-- Létrehozás ideje: 2025. Jan 13. 13:02
-- Kiszolgáló verziója: 10.4.32-MariaDB
-- PHP verzió: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Adatbázis: `ekurzusok`
--

-- --------------------------------------------------------

--
-- Tábla szerkezet ehhez a táblához `feladatleadas`
--

CREATE TABLE `feladatleadas` (
  `LeadasID` int(11) NOT NULL,
  `TartalomID` int(11) NOT NULL,
  `FelhasznaloID` int(11) NOT NULL,
  `Ertekeles` smallint(4) DEFAULT NULL,
  `SzovegesErtekeles` varchar(128) DEFAULT NULL,
  `Leadva` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci;

--
-- Eseményindítók `feladatleadas`
--
DELIMITER $$
CREATE TRIGGER `log_feladatleadas_delete` AFTER DELETE ON `feladatleadas` FOR EACH ROW INSERT INTO `log` (`tabla`, `metodus`) VALUES ("feladatleadas", "DELETE")
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `log_feladatleadas_insert` AFTER INSERT ON `feladatleadas` FOR EACH ROW INSERT INTO `log` (`tabla`, `metodus`) VALUES ("feladatleadas", "INSERT")
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `log_feladatleadas_update` AFTER UPDATE ON `feladatleadas` FOR EACH ROW INSERT INTO `log` (`tabla`, `metodus`) VALUES ("feladatleadas", "UPDATE")
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Tábla szerkezet ehhez a táblához `felhasznalo`
--

CREATE TABLE `felhasznalo` (
  `FelhasznaloID` int(11) NOT NULL,
  `Email` varchar(128) NOT NULL,
  `VezetekNev` varchar(64) NOT NULL,
  `KeresztNev` varchar(64) NOT NULL,
  `Jelszo` char(60) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci;

--
-- A tábla adatainak kiíratása `felhasznalo`
--

INSERT INTO `felhasznalo` (`FelhasznaloID`, `Email`, `VezetekNev`, `KeresztNev`, `Jelszo`) VALUES
(13, 'kispista@gmail.com', 'Kis', 'Pista', '$2y$10$mPo.I8wSPUi.W5QaydbKIeGuR1SyKMPrIXm71XFoJZ7sHQbh/bGjO'),
(18, 'nagyjanos@gmail.com', 'Nagy', 'Janos', '$2y$10$NC4f6M6WZoVCa3elo9Y7U.98UYdmBQRZUerMNVNcMkQkgIwLyHtNC'),
(24, 'kovacs.joska@gmail.com', 'Kovács', 'Jóska', '$2y$10$PBeTd4ju1mnCPUGRJbE9Wu6nP8hw4ZmgwrTg7rO2FCoLj7pZ5k7jC'),
(40, 'email@email.com', 'Teszt', 'Teszt', '$2y$10$.wT7ucWJ/StOwCr00QPRIOt2FuqyZ8e1avGKupFa7Jyy2.C5RWU2S'),
(47, 'teszt1@teszt.com', 'Teszt', 'Teszt', '$2y$10$HM2aWUi9uFjYwflUBXZ0meq.5VKXvXsKE/tnsaieVU5f8a5T4S976'),
(49, 'teszt@teszt.com', 'Teszt', 'Teszt', '$2y$10$D/UEcoXU9Le0/or10TmjTOR.9fUUmaHp/6LeMvWjAWslVYkHb83qG'),
(50, 'trigger3@email.com', 'Trigger', 'Teszt', '$2y$10$XnetB5OP3PLth5c83AZa9.F38Ro2DI8Sdhc5D3gcE2wGeCYsFlwPG');

--
-- Eseményindítók `felhasznalo`
--
DELIMITER $$
CREATE TRIGGER `log_felhasznalo_delete` AFTER DELETE ON `felhasznalo` FOR EACH ROW INSERT INTO `log` (`tabla`, `metodus`) VALUES ("felhasznalo", "DELETE")
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `log_felhasznalo_insert` AFTER INSERT ON `felhasznalo` FOR EACH ROW INSERT INTO `log` (`tabla`, `metodus`) VALUES ("felhasznalo", "INSERT")
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `log_felhasznalo_update` AFTER UPDATE ON `felhasznalo` FOR EACH ROW INSERT INTO `log` (`tabla`, `metodus`) VALUES ("felhasznalo", "UPDATE")
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Tábla szerkezet ehhez a táblához `file`
--

CREATE TABLE `file` (
  `FileID` int(11) NOT NULL,
  `TartalomID` int(11) DEFAULT NULL,
  `LeadasID` int(11) DEFAULT NULL,
  `FileNev` varchar(128) NOT NULL,
  `Meret` smallint(6) NOT NULL COMMENT 'KB'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci;

--
-- Eseményindítók `file`
--
DELIMITER $$
CREATE TRIGGER `log_file_delete` AFTER DELETE ON `file` FOR EACH ROW INSERT INTO `log` (`tabla`, `metodus`) VALUES ("file", "DELETE")
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `log_file_insert` AFTER INSERT ON `file` FOR EACH ROW INSERT INTO `log` (`tabla`, `metodus`) VALUES ("file", "INSERT")
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `log_file_update` AFTER UPDATE ON `file` FOR EACH ROW INSERT INTO `log` (`tabla`, `metodus`) VALUES ("file", "UPDATE")
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Tábla szerkezet ehhez a táblához `kurzus`
--

CREATE TABLE `kurzus` (
  `KurzusID` int(11) NOT NULL,
  `FelhasznaloID` int(11) NOT NULL,
  `KurzusNev` varchar(128) NOT NULL,
  `Kod` char(10) NOT NULL,
  `Leiras` varchar(512) NOT NULL,
  `Design` int(11) NOT NULL,
  `Archivalt` tinyint(1) NOT NULL DEFAULT 0,
  `Oktatok` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci;

--
-- A tábla adatainak kiíratása `kurzus`
--

INSERT INTO `kurzus` (`KurzusID`, `FelhasznaloID`, `KurzusNev`, `Kod`, `Leiras`, `Design`, `Archivalt`, `Oktatok`) VALUES
(4, 13, 'Teszt kurzus', 'snfiJZcukE', '...', 5, 0, 'Dávid'),
(5, 40, 'Teszt kurzus 2', 'KlG3FCHbLd', '2', 5, 1, ''),
(6, 40, 'Teszt 3', 'oEIhekyjRR', '3', 3, 0, ''),
(7, 40, 'Teszt', 'ffMXNPH161', 'Teszt', 2, 0, ''),
(8, 49, 'Teszt ...', 'bLIR2nUj5K', 'a', 2, 0, '');

--
-- Eseményindítók `kurzus`
--
DELIMITER $$
CREATE TRIGGER `log_kurzus_delete` AFTER DELETE ON `kurzus` FOR EACH ROW INSERT INTO `log` (`tabla`, `metodus`) VALUES ("kurzus", "DELETE")
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `log_kurzus_insert` AFTER INSERT ON `kurzus` FOR EACH ROW INSERT INTO `log` (`tabla`, `metodus`) VALUES ("kurzus", "INSERT")
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `log_kurzus_update` AFTER UPDATE ON `kurzus` FOR EACH ROW INSERT INTO `log` (`tabla`, `metodus`) VALUES ("kurzus", "UPDATE")
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Tábla szerkezet ehhez a táblához `kurzustag`
--

CREATE TABLE `kurzustag` (
  `ID` int(11) NOT NULL,
  `FelhasznaloID` int(11) NOT NULL,
  `KurzusID` int(11) NOT NULL,
  `Tanar` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci;

--
-- A tábla adatainak kiíratása `kurzustag`
--

INSERT INTO `kurzustag` (`ID`, `FelhasznaloID`, `KurzusID`, `Tanar`) VALUES
(1, 40, 5, 1),
(2, 40, 6, 1),
(3, 40, 7, 1),
(4, 49, 8, 1),
(5, 49, 4, 0),
(6, 49, 6, 0),
(7, 49, 7, 0);

--
-- Eseményindítók `kurzustag`
--
DELIMITER $$
CREATE TRIGGER `log_kurzustag_delete` AFTER DELETE ON `kurzustag` FOR EACH ROW INSERT INTO `log` (`tabla`, `metodus`) VALUES ("kurzustag", "DELETE")
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `log_kurzustag_insert` AFTER INSERT ON `kurzustag` FOR EACH ROW INSERT INTO `log` (`tabla`, `metodus`) VALUES ("kurzustag", "INSERT")
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `log_kurzustag_update` AFTER UPDATE ON `kurzustag` FOR EACH ROW INSERT INTO `log` (`tabla`, `metodus`) VALUES ("kurzustag", "UPDATE")
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Tábla szerkezet ehhez a táblához `log`
--

CREATE TABLE `log` (
  `id` int(11) NOT NULL,
  `tabla` enum('feladatleadas','felhasznalo','file','kurzus','kurzustag','tartalom') NOT NULL,
  `metodus` enum('INSERT','UPDATE','DELETE') NOT NULL,
  `ido` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci;

--
-- A tábla adatainak kiíratása `log`
--

INSERT INTO `log` (`id`, `tabla`, `metodus`, `ido`) VALUES
(1, 'felhasznalo', 'INSERT', '2025-01-13 11:43:28'),
(2, 'felhasznalo', 'UPDATE', '2025-01-13 11:51:07');

-- --------------------------------------------------------

--
-- Tábla szerkezet ehhez a táblához `tartalom`
--

CREATE TABLE `tartalom` (
  `TartalomID` int(11) NOT NULL,
  `FelhasznaloID` int(11) NOT NULL,
  `KurzusID` int(11) NOT NULL,
  `Cim` varchar(64) NOT NULL,
  `Leiras` varchar(512) NOT NULL,
  `Feladat` tinyint(1) NOT NULL,
  `MaxPont` smallint(6) DEFAULT NULL COMMENT 'Max 1000',
  `Hatarido` datetime DEFAULT NULL,
  `Modositva` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `Kiadva` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci;

--
-- A tábla adatainak kiíratása `tartalom`
--

INSERT INTO `tartalom` (`TartalomID`, `FelhasznaloID`, `KurzusID`, `Cim`, `Leiras`, `Feladat`, `MaxPont`, `Hatarido`, `Modositva`, `Kiadva`) VALUES
(4, 49, 8, 'teszt', 'teszt...', 1, 100, '2025-01-20 12:59:00', '2024-12-16 11:14:18', '2024-12-16 12:14:18');

--
-- Eseményindítók `tartalom`
--
DELIMITER $$
CREATE TRIGGER `log_tartalom_delete` AFTER DELETE ON `tartalom` FOR EACH ROW INSERT INTO `log` (`tabla`, `metodus`) VALUES ("tartalom", "DELETE")
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `log_tartalom_insert` AFTER INSERT ON `tartalom` FOR EACH ROW INSERT INTO `log` (`tabla`, `metodus`) VALUES ("tartalom", "INSERT")
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `log_tartalom_update` AFTER UPDATE ON `tartalom` FOR EACH ROW INSERT INTO `log` (`tabla`, `metodus`) VALUES ("tartalom", "UPDATE")
$$
DELIMITER ;

--
-- Indexek a kiírt táblákhoz
--

--
-- A tábla indexei `feladatleadas`
--
ALTER TABLE `feladatleadas`
  ADD PRIMARY KEY (`LeadasID`),
  ADD KEY `FeladatID` (`TartalomID`,`FelhasznaloID`),
  ADD KEY `LeadoID` (`FelhasznaloID`);

--
-- A tábla indexei `felhasznalo`
--
ALTER TABLE `felhasznalo`
  ADD PRIMARY KEY (`FelhasznaloID`);

--
-- A tábla indexei `file`
--
ALTER TABLE `file`
  ADD PRIMARY KEY (`FileID`),
  ADD KEY `FeltoltesiHelyID` (`TartalomID`),
  ADD KEY `FeladatleadasID` (`LeadasID`);

--
-- A tábla indexei `kurzus`
--
ALTER TABLE `kurzus`
  ADD PRIMARY KEY (`KurzusID`),
  ADD KEY `TulajdonosID` (`FelhasznaloID`);

--
-- A tábla indexei `kurzustag`
--
ALTER TABLE `kurzustag`
  ADD PRIMARY KEY (`ID`),
  ADD KEY `FelhasznaloID` (`FelhasznaloID`,`KurzusID`),
  ADD KEY `KurzusID` (`KurzusID`);

--
-- A tábla indexei `log`
--
ALTER TABLE `log`
  ADD PRIMARY KEY (`id`);

--
-- A tábla indexei `tartalom`
--
ALTER TABLE `tartalom`
  ADD PRIMARY KEY (`TartalomID`),
  ADD KEY `FeltoltoID` (`FelhasznaloID`,`KurzusID`),
  ADD KEY `KurzusID` (`KurzusID`);

--
-- A kiírt táblák AUTO_INCREMENT értéke
--

--
-- AUTO_INCREMENT a táblához `feladatleadas`
--
ALTER TABLE `feladatleadas`
  MODIFY `LeadasID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT a táblához `felhasznalo`
--
ALTER TABLE `felhasznalo`
  MODIFY `FelhasznaloID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=51;

--
-- AUTO_INCREMENT a táblához `file`
--
ALTER TABLE `file`
  MODIFY `FileID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT a táblához `kurzus`
--
ALTER TABLE `kurzus`
  MODIFY `KurzusID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT a táblához `kurzustag`
--
ALTER TABLE `kurzustag`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT a táblához `log`
--
ALTER TABLE `log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT a táblához `tartalom`
--
ALTER TABLE `tartalom`
  MODIFY `TartalomID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Megkötések a kiírt táblákhoz
--

--
-- Megkötések a táblához `feladatleadas`
--
ALTER TABLE `feladatleadas`
  ADD CONSTRAINT `feladatleadas_ibfk_2` FOREIGN KEY (`FelhasznaloID`) REFERENCES `felhasznalo` (`FelhasznaloID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `feladatleadas_ibfk_3` FOREIGN KEY (`TartalomID`) REFERENCES `tartalom` (`TartalomID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Megkötések a táblához `file`
--
ALTER TABLE `file`
  ADD CONSTRAINT `file_ibfk_1` FOREIGN KEY (`LeadasID`) REFERENCES `feladatleadas` (`LeadasID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `file_ibfk_2` FOREIGN KEY (`TartalomID`) REFERENCES `tartalom` (`TartalomID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Megkötések a táblához `kurzus`
--
ALTER TABLE `kurzus`
  ADD CONSTRAINT `kurzus_ibfk_1` FOREIGN KEY (`FelhasznaloID`) REFERENCES `felhasznalo` (`FelhasznaloID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Megkötések a táblához `kurzustag`
--
ALTER TABLE `kurzustag`
  ADD CONSTRAINT `kurzustag_ibfk_1` FOREIGN KEY (`FelhasznaloID`) REFERENCES `felhasznalo` (`FelhasznaloID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `kurzustag_ibfk_2` FOREIGN KEY (`KurzusID`) REFERENCES `kurzus` (`KurzusID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Megkötések a táblához `tartalom`
--
ALTER TABLE `tartalom`
  ADD CONSTRAINT `tartalom_ibfk_1` FOREIGN KEY (`FelhasznaloID`) REFERENCES `felhasznalo` (`FelhasznaloID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `tartalom_ibfk_2` FOREIGN KEY (`KurzusID`) REFERENCES `kurzus` (`KurzusID`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
