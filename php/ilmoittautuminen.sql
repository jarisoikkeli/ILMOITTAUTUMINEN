-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: 13.08.2025 klo 14:06
-- Palvelimen versio: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `ilmoittautuminen`
--

-- --------------------------------------------------------

--
-- Rakenne taululle `ilmoittautumiset`
--

CREATE TABLE `ilmoittautumiset` (
  `id` int(11) NOT NULL,
  `kilpailu_id` int(11) NOT NULL,
  `nimi` varchar(255) NOT NULL,
  `syntymaaika` date NOT NULL,
  `seura` varchar(255) DEFAULT NULL,
  `sahkoposti` varchar(255) NOT NULL,
  `muokkaus_token` varchar(64) NOT NULL,
  `muokkaus_token_luotu` datetime NOT NULL DEFAULT current_timestamp(),
  `kilpailunumero` int(11) DEFAULT NULL,
  `maksanut` tinyint(1) NOT NULL DEFAULT 0,
  `luotu` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Vedos taulusta `ilmoittautumiset`
--

INSERT INTO `ilmoittautumiset` (`id`, `kilpailu_id`, `nimi`, `syntymaaika`, `seura`, `sahkoposti`, `muokkaus_token`, `muokkaus_token_luotu`, `kilpailunumero`, `maksanut`, `luotu`) VALUES
(23, 5, 'Jari Soikkeli', '1970-01-11', 'Ultrasisu', 'jaris1970@gmail.com', '498b9e5833f22fb19a7939baae7bbf12eedb1a4824aab2f46a523836121bada9', '2025-08-12 09:48:51', 1, 0, '2025-08-11 09:48:05'),
(24, 5, 'Teppo Matti Viipottaja', '1999-01-21', 'Kesälahden Kiri', 'jaris1970@gmail.com', '3d07cafc8bff5aede403b3254e7ebd429a4a596a8a192954635fdc6027b71edf', '2025-08-12 09:48:51', 2, 0, '2025-08-11 09:52:55'),
(25, 5, 'Hannu Risku', '1969-02-11', 'Seinäjoen Kalske', 'jaris1970@gmail.com', 'ac8868da8befa1f9ce6270e4551feb68e2517997edcc259daaba56ffbe2de47b', '2025-08-12 09:48:51', 3, 1, '2025-08-11 10:00:01'),
(26, 5, 'Jari Soikkeli', '2000-02-11', 'Ultrasisu', 'jaris1970@gmail.com', 'cbac761a658f84ef0e63cf55d1b87b34c32989cca6a65832a9f8d50075e008d6', '2025-08-12 09:48:51', 4, 0, '2025-08-11 10:02:11'),
(27, 5, 'Jari Soikkeli', '2000-11-11', 'Ultrasisu', 'jaris1970@gmail.com', '15bd833c2550426d6ce50b1bb3cc26796517eec467e6de497e627e9ee430fadd', '2025-08-12 09:48:51', 5, 0, '2025-08-11 10:12:04'),
(28, 5, 'Imppa Liimatta', '1970-01-01', 'Joensuu', 'jaris1970@gmail.com', '05c8a1e475c79dc4d1572a0c440f11dc98455b5b589650f0ed2c304dd33004b3', '2025-08-12 09:48:51', 6, 1, '2025-08-11 10:30:11'),
(29, 5, 'Jari Soikkeli', '2000-11-11', 'Ultrasisu', 'jaris1970@gmail.com', '0d969a05939918ac9f955c67e06ed55b30d31bdc1ce16e0fcf03f5cba6cbdc4f', '2025-08-12 09:48:51', 7, 0, '2025-08-11 10:31:24'),
(35, 5, 'werwer', '2000-11-11', 'Ultrasisu', 'jaris1970@gmail.com', 'e2c20a78f53e5b69ebe4c7777fe26b7b0325854fa0444852dcfbee3f54a5d76d', '2025-08-12 09:48:51', 13, 0, '2025-08-11 10:49:56'),
(36, 5, 'hjkhjk', '2000-11-11', 'Ultrasisu', 'jaris1970@gmail.com', 'b495ef469b86af062fcda7ce5d27f001d8e7ed0399c3c535abd9bb22026e311a', '2025-08-12 09:48:51', 14, 0, '2025-08-11 10:50:41'),
(38, 5, 'ythryt rty rt', '0000-00-00', 'Ultrasisu', 'jaris1970@gmail.com', '5864afe5cf0d91745fc84d88eec5db7141d9df18bc96ecc8625a04b78716c595', '2025-08-12 09:48:51', 16, 0, '2025-08-11 11:07:04'),
(39, 5, 'fghfgh fdty fty ', '2000-02-11', 'Ultrasisu', 'jaris1970@gmail.com', '78e397c6fb45ce6240d118012ad35a97c3c7f703aec374858a65d959da80edfc', '2025-08-12 09:48:51', 17, 0, '2025-08-11 11:10:20'),
(40, 5, 'Esko Pesko', '2000-11-11', 'Joensuu', 'jaris1970@gmail.com', 'dd1df37be180844de583f7cb2575d1ac58d5646b1c96886d3afea5f2edd8dc4b', '2025-08-12 09:48:51', 18, 0, '2025-08-11 11:12:06'),
(42, 5, 'Köpi Kössi', '1999-12-21', 'SisuX', 'jari.soikkeli@edu.riveria.fi', '21ab3215db2a0629368f5de3696f5a4bd072e674c634386ecc61db31d47455e2', '2025-08-12 09:49:54', 20, 0, '2025-08-12 06:34:28'),
(43, 5, 'Jasse Passe', '2001-12-21', 'UltrasisuX', 'jari.soikkeli@edu.riveria.fi', 'e5caf065558d15a9d424928f6267283d89d57cd674af64dd387659dc3e143eb7', '2025-08-12 10:33:00', 21, 1, '2025-08-12 07:32:02'),
(45, 5, 'Heikki Leikki', '1977-12-21', 'SisuX', 'jari.soikkeli@edu.riveria.fi', 'd35fb69a1cd534a4dcc25a332b81d935f3601330be0db3627fec13576f81eaa2', '2025-08-12 14:22:26', 23, 0, '2025-08-12 11:19:37'),
(46, 5, 'Jari SoikkeliX', '2000-02-11', 'Ultrasisu', 'jari.soikkeli@edu.riveria.fi', '907c7f517d2c6270097f3b6a71c1810e49ff9466699397ee8e33045eb73e759e', '2025-08-13 09:51:40', 24, 1, '2025-08-13 06:50:14'),
(47, 5, 'Leena Liimatta', '2000-02-11', 'Sisu', 'jari.soikkeli@edu.riveria.fi', '111ef9a6f2742e9e9211a97e62a1ffef880bf52d42f918693ac47304ea4f2267', '2025-08-13 10:03:39', 25, 1, '2025-08-13 07:02:02'),
(48, 5, 'fgfg wrtw erty estry', '0200-02-11', 'Sisu', 'jari.soikkeli@edu.riveria.fi', 'd6366aee519b5103f0d672717a4366cc5c48829adad77a8517d7555087ac8445', '2025-08-13 10:06:49', 26, 1, '2025-08-13 07:05:51'),
(50, 5, 'Jari Kinnunen', '0200-11-11', 'Sisu', 'jaris1970@gmail.com', '7c058f9839c9e41761c06ca3f24111349d7468401a6c39da4d73244e2d9c7d64', '2025-08-13 10:21:35', 28, 0, '2025-08-13 07:21:35'),
(52, 5, 'ery sder MUOKATTU sdrg', '2000-02-21', 'Sisu', 'jari.soikkeli@edu.riveria.fi', '353fde9e67b53a570345aad814d1441d61c1b3df0ea4718bbbe1e1554a7f64f8', '2025-08-13 11:37:00', 30, 1, '2025-08-13 08:35:17'),
(55, 5, 'aefad sr', '2000-02-22', 'asdas', 'jaris1970@gmail.com', '421fd0f41f752ff4d3a2cb3645e23c77be70cea86ab2dcfed007474ac0c432be', '2025-08-13 12:17:14', 33, 1, '2025-08-13 09:17:14'),
(56, 6, 'Jari Soikkeli', '2000-11-11', 'Sisu', 'jari.soikkeli@edu.riveria.fi', '348cf0d5282c35a2ac0533c615eef527495bbb265c406989b125390d2771a9c9', '2025-08-13 12:21:40', 1, 0, '2025-08-13 09:21:40'),
(58, 6, 'Kaija Soikkeli', '2000-11-11', 'Sisu', 'jari.soikkeli@edu.riveria.fi', '4efdcc7a4e72cf037fb56c399c3d7443b4418fe673897ad1ca30c4027431c899', '2025-08-13 13:20:23', 2, 0, '2025-08-13 10:19:34'),
(59, 6, 'Jari Soikkeli', '2000-02-22', 'Sisu', 'jaris1970@gmail.com', '08c5af30316b1d8cef6f57ac5ac3d54618ea2229390d4248e5e4a3b6af14ea66', '2025-08-13 14:04:40', 3, 0, '2025-08-13 10:29:47'),
(60, 6, 'Jari Kinnunen', '2000-02-22', 'Sisu', 'jari.soikkeli@edu.riveria.fi', '0a1d05b44e8aa2b10f40b101afe8464ff3f9a6c8e0fdba755f4ffd1a0521574a', '2025-08-13 13:51:20', 4, 1, '2025-08-13 10:50:41'),
(62, 6, 'yuiy,', '2000-02-22', 'Sisu', 'jaris1970@gmail.com', 'e7bd05b25eea80fdc0b129e9a608cc0e16381cce359ac3261a82b8467fecab55', '2025-08-13 14:01:34', 5, 0, '2025-08-13 11:01:34'),
(64, 6, 'ftyj tyu tyu', '2000-02-11', 'Sisu', 'jaris1970@gmail.com', '1144ae82e71e0f51f40656648fb5477c1b5d4d765ebc8a3ecd8ecc695f7a537d', '2025-08-13 14:20:04', 6, 0, '2025-08-13 11:20:04'),
(66, 5, 'asdasd', '2000-11-11', 'Sisu', 'jaris1970@gmail.com', '4e5df33a5ace4270c4c22d4997324eeb9b95eed0ec8b1bbcc9d74931f658a2c5', '2025-08-13 14:34:41', 34, 0, '2025-08-13 11:34:41'),
(67, 5, 'wrwtrw', '2000-11-11', 'Ultrasisu', 'jaris1970@gmail.com', '722557a8ac777bb979ea3ca7c54f31a98ed43deee2349008cc04fe07bf311cf3', '2025-08-13 14:37:56', 35, 0, '2025-08-13 11:37:56'),
(68, 5, 'wrwtrw', '2000-11-11', 'Ultrasisu', 'jaris1970@gmail.com', 'ef300956bbbcd6a490ce67d0f8de420f9c1b7e89ff959ec03eb7bf2324e00955', '2025-08-13 14:38:39', 36, 0, '2025-08-13 11:38:39'),
(69, 5, 'erge', '2000-11-11', 'Ultrasisu', 'jaris1970@gmail.com', '5bd4434f9770b5753a5911289ad1f3bbe78b917a0c1f804d89b518f6dcd298e2', '2025-08-13 14:39:27', 37, 0, '2025-08-13 11:39:27'),
(70, 5, 'Jari Soikkeli', '2000-11-11', 'Ultrasisu', 'jari.soikkeli@edu.riveria.fi', '42c01a2ec5a8dcaf0c242e24c3790a21f360805dd34c33fb953ac26a00a0f50c', '2025-08-13 14:47:45', 38, 0, '2025-08-13 11:47:45'),
(71, 5, 'Jari Soikkeli MUOKATTU', '1970-11-21', 'Ultrasisu', 'jari.soikkeli@edu.riveria.fi', '81dd3f731acdbb0311477d51b997bc146bcd7132fc7bd54746b4094186d8aa42', '2025-08-13 14:58:02', 39, 0, '2025-08-13 11:54:41');

-- --------------------------------------------------------

--
-- Rakenne taululle `kilpailut`
--

CREATE TABLE `kilpailut` (
  `id` int(11) NOT NULL,
  `nimi` varchar(255) NOT NULL,
  `ajankohta` date NOT NULL,
  `ilmoittautuminen_alku` date NOT NULL,
  `ilmoittautuminen_loppu` date NOT NULL,
  `maksimi_osallistujat` int(11) DEFAULT NULL,
  `info` text DEFAULT NULL,
  `luotu` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Vedos taulusta `kilpailut`
--

INSERT INTO `kilpailut` (`id`, `nimi`, `ajankohta`, `ilmoittautuminen_alku`, `ilmoittautuminen_loppu`, `maksimi_osallistujat`, `info`, `luotu`) VALUES
(5, 'Testikisa 1 2025', '2025-12-01', '2025-08-01', '2025-11-30', 200, 'Maksa osallistumismaksu seuraavilla tiedoilla:\r\n\r\nSaaja: Ultrajuoksuseura Sisu ry\r\nTilinumero: FI41 5770 0520 3413 45\r\nViitenumero: 102571\r\nSumma: 40 €\r\n\r\nKun maksusi on vastaanotettu, nimesi lisätään ilmoittautuneiden listaan. \r\n', '2025-08-11 09:46:47'),
(6, 'Testikisa 2', '2025-12-12', '2025-08-12', '2025-12-11', 100, 'Maksa osallistumismaksu seuraavilla tiedoilla: Saaja: Ultrajuoksuseura Sisu ry Tilinumero: FI41 5770 0520 3413 45 Viitenumero: 102571 Summa: 40 € Kun maksusi on vastaanotettu, nimesi lisätään ilmoittautuneiden listaan. ', '2025-08-13 09:20:54');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `ilmoittautumiset`
--
ALTER TABLE `ilmoittautumiset`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_muokkaus_token` (`muokkaus_token`),
  ADD KEY `kilpailu_id` (`kilpailu_id`);

--
-- Indexes for table `kilpailut`
--
ALTER TABLE `kilpailut`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `ilmoittautumiset`
--
ALTER TABLE `ilmoittautumiset`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=72;

--
-- AUTO_INCREMENT for table `kilpailut`
--
ALTER TABLE `kilpailut`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Rajoitteet vedostauluille
--

--
-- Rajoitteet taululle `ilmoittautumiset`
--
ALTER TABLE `ilmoittautumiset`
  ADD CONSTRAINT `ilmoittautumiset_ibfk_1` FOREIGN KEY (`kilpailu_id`) REFERENCES `kilpailut` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
