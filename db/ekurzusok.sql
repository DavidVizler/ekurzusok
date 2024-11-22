-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Gép: 127.0.0.1
-- Létrehozás ideje: 2024. Nov 22. 10:08
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
(24, 'kovacs.joska@gmail.com', 'Kovács', 'Jóska', '$2y$10$PBeTd4ju1mnCPUGRJbE9Wu6nP8hw4ZmgwrTg7rO2FCoLj7pZ5k7jC');

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

-- --------------------------------------------------------

--
-- Tábla szerkezet ehhez a táblához `kurzus`
--

CREATE TABLE `kurzus` (
  `KurzusID` int(11) NOT NULL,
  `FelhasznaloID` int(11) NOT NULL,
  `KurzusNev` varchar(128) NOT NULL,
  `Oktatok` varchar(50) NOT NULL,
  `Kod` char(10) NOT NULL,
  `Leiras` varchar(512) NOT NULL,
  `Design` int(11) NOT NULL,
  `Archivalt` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci;

--
-- A tábla adatainak kiíratása `kurzus`
--

INSERT INTO `kurzus` (`KurzusID`, `FelhasznaloID`, `KurzusNev`, `Oktatok`, `Kod`, `Leiras`, `Design`, `Archivalt`) VALUES
(4, 13, 'Földrajz - 11/c', 'Nagy Pista', 'sP8M1qe73m', 'Terem: 17-es', 1, 0);

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
  MODIFY `FelhasznaloID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT a táblához `file`
--
ALTER TABLE `file`
  MODIFY `FileID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT a táblához `kurzus`
--
ALTER TABLE `kurzus`
  MODIFY `KurzusID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT a táblához `kurzustag`
--
ALTER TABLE `kurzustag`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT a táblához `tartalom`
--
ALTER TABLE `tartalom`
  MODIFY `TartalomID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

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
