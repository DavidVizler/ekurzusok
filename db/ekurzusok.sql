-- phpMyAdmin SQL Dump
-- version 5.2.1deb3
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Nov 11, 2024 at 06:49 PM
-- Server version: 10.11.8-MariaDB-0ubuntu0.24.04.1
-- PHP Version: 8.3.6

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `ekurzusok`
--

-- --------------------------------------------------------

--
-- Table structure for table `feladatleadas`
--

CREATE TABLE `feladatleadas` (
  `LeadasID` int(11) NOT NULL,
  `TartalomID` int(11) NOT NULL,
  `FelhasznaloID` int(11) NOT NULL,
  `Ertekeles` smallint(4) DEFAULT NULL,
  `SzovegesErtekeles` varchar(128) DEFAULT NULL,
  `Leadva` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_hungarian_ci;

-- --------------------------------------------------------

--
-- Table structure for table `felhasznalo`
--

CREATE TABLE `felhasznalo` (
  `FelhasznaloID` int(11) NOT NULL,
  `Email` varchar(128) NOT NULL,
  `VezetekNev` varchar(64) NOT NULL,
  `KeresztNev` varchar(64) NOT NULL,
  `Jelszo` char(60) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_hungarian_ci;

--
-- Dumping data for table `felhasznalo`
--

INSERT INTO `felhasznalo` (`FelhasznaloID`, `Email`, `VezetekNev`, `KeresztNev`, `Jelszo`) VALUES
(13, 'kispista@gmail.com', 'Kis', 'Pista', '$2y$10$mPo.I8wSPUi.W5QaydbKIeGuR1SyKMPrIXm71XFoJZ7sHQbh/bGjO'),
(18, 'nagyjanos@gmail.com', 'Nagy', 'Janos', '$2y$10$NC4f6M6WZoVCa3elo9Y7U.98UYdmBQRZUerMNVNcMkQkgIwLyHtNC'),
(24, 'kovacs.joska@gmail.com', 'Kovács', 'Jóska', '$2y$10$PBeTd4ju1mnCPUGRJbE9Wu6nP8hw4ZmgwrTg7rO2FCoLj7pZ5k7jC');

-- --------------------------------------------------------

--
-- Table structure for table `file`
--

CREATE TABLE `file` (
  `FileID` int(11) NOT NULL,
  `TartalomID` int(11) DEFAULT NULL,
  `LeadasID` int(11) DEFAULT NULL,
  `FileNev` varchar(128) NOT NULL,
  `Meret` smallint(6) NOT NULL COMMENT 'KB'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_hungarian_ci;

-- --------------------------------------------------------

--
-- Table structure for table `kurzus`
--

CREATE TABLE `kurzus` (
  `KurzusID` int(11) NOT NULL,
  `FelhasznaloID` int(11) NOT NULL,
  `KurzusNev` varchar(128) NOT NULL,
  `Kod` char(10) NOT NULL,
  `Leiras` varchar(512) NOT NULL,
  `Design` int(11) NOT NULL,
  `Archivalt` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_hungarian_ci;

-- --------------------------------------------------------

--
-- Table structure for table `kurzustag`
--

CREATE TABLE `kurzustag` (
  `ID` int(11) NOT NULL,
  `FelhasznaloID` int(11) NOT NULL,
  `KurzusID` int(11) NOT NULL,
  `Tanar` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_hungarian_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tartalom`
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_hungarian_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `feladatleadas`
--
ALTER TABLE `feladatleadas`
  ADD PRIMARY KEY (`LeadasID`),
  ADD KEY `FeladatID` (`TartalomID`,`FelhasznaloID`),
  ADD KEY `LeadoID` (`FelhasznaloID`);

--
-- Indexes for table `felhasznalo`
--
ALTER TABLE `felhasznalo`
  ADD PRIMARY KEY (`FelhasznaloID`);

--
-- Indexes for table `file`
--
ALTER TABLE `file`
  ADD PRIMARY KEY (`FileID`),
  ADD KEY `FeltoltesiHelyID` (`TartalomID`),
  ADD KEY `FeladatleadasID` (`LeadasID`);

--
-- Indexes for table `kurzus`
--
ALTER TABLE `kurzus`
  ADD PRIMARY KEY (`KurzusID`),
  ADD KEY `TulajdonosID` (`FelhasznaloID`);

--
-- Indexes for table `kurzustag`
--
ALTER TABLE `kurzustag`
  ADD PRIMARY KEY (`ID`),
  ADD KEY `FelhasznaloID` (`FelhasznaloID`,`KurzusID`),
  ADD KEY `KurzusID` (`KurzusID`);

--
-- Indexes for table `tartalom`
--
ALTER TABLE `tartalom`
  ADD PRIMARY KEY (`TartalomID`),
  ADD KEY `FeltoltoID` (`FelhasznaloID`,`KurzusID`),
  ADD KEY `KurzusID` (`KurzusID`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `feladatleadas`
--
ALTER TABLE `feladatleadas`
  MODIFY `LeadasID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `felhasznalo`
--
ALTER TABLE `felhasznalo`
  MODIFY `FelhasznaloID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `file`
--
ALTER TABLE `file`
  MODIFY `FileID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `kurzus`
--
ALTER TABLE `kurzus`
  MODIFY `KurzusID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `kurzustag`
--
ALTER TABLE `kurzustag`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tartalom`
--
ALTER TABLE `tartalom`
  MODIFY `TartalomID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `feladatleadas`
--
ALTER TABLE `feladatleadas`
  ADD CONSTRAINT `feladatleadas_ibfk_2` FOREIGN KEY (`FelhasznaloID`) REFERENCES `felhasznalo` (`FelhasznaloID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `feladatleadas_ibfk_3` FOREIGN KEY (`TartalomID`) REFERENCES `tartalom` (`TartalomID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `file`
--
ALTER TABLE `file`
  ADD CONSTRAINT `file_ibfk_1` FOREIGN KEY (`LeadasID`) REFERENCES `feladatleadas` (`LeadasID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `file_ibfk_2` FOREIGN KEY (`TartalomID`) REFERENCES `tartalom` (`TartalomID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `kurzus`
--
ALTER TABLE `kurzus`
  ADD CONSTRAINT `kurzus_ibfk_1` FOREIGN KEY (`FelhasznaloID`) REFERENCES `felhasznalo` (`FelhasznaloID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `kurzustag`
--
ALTER TABLE `kurzustag`
  ADD CONSTRAINT `kurzustag_ibfk_1` FOREIGN KEY (`FelhasznaloID`) REFERENCES `felhasznalo` (`FelhasznaloID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `kurzustag_ibfk_2` FOREIGN KEY (`KurzusID`) REFERENCES `kurzus` (`KurzusID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `tartalom`
--
ALTER TABLE `tartalom`
  ADD CONSTRAINT `tartalom_ibfk_1` FOREIGN KEY (`FelhasznaloID`) REFERENCES `felhasznalo` (`FelhasznaloID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `tartalom_ibfk_2` FOREIGN KEY (`KurzusID`) REFERENCES `kurzus` (`KurzusID`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
