-- PHP Airlines / Airline2014
-- Fresh fictional development dataset based on the original January 2014 schema.
-- MySQL 5.5/5.7 compatible. Intended for local testing only.
--
-- WARNING: This script replaces the four tables in airline2013.
-- Back up any data you need before importing.
-- All people and contact details are fictional; card numbers are dummy values.
-- Plain-text passwords are retained only for compatibility with the legacy app.
-- Never use these credentials or this schema for a public/production deployment.

CREATE DATABASE IF NOT EXISTS `airline2013`
  DEFAULT CHARACTER SET latin1 COLLATE latin1_swedish_ci;
USE `airline2013`;

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";
SET NAMES latin1;

DROP TABLE IF EXISTS `passangers`;
DROP TABLE IF EXISTS `routes`;
DROP TABLE IF EXISTS `user`;
DROP TABLE IF EXISTS `login`;

-- Preserve the original table and column names/types.
CREATE TABLE IF NOT EXISTS `login` (
  `username` varchar(20) NOT NULL,
  `password` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

INSERT INTO `login` (`username`, `password`) VALUES
('rmiller', 'rmiller'),
('opsdesk', 'desk2026'),
('routelead', 'route2026');

CREATE TABLE IF NOT EXISTS `routes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `source` varchar(20) NOT NULL,
  `dest` varchar(20) NOT NULL,
  `start` date NOT NULL,
  `end` date NOT NULL,
  `price` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;

-- Route availability windows and one-way fares in euro.
INSERT INTO `routes` (`id`, `source`, `dest`, `start`, `end`, `price`) VALUES
(1, 'dublin', 'heathrow', '2026-10-24', '2027-06-30', 89),
(2, 'cork', 'amsterdam', '2026-10-24', '2027-06-30', 104),
(3, 'shannon', 'manchester', '2026-10-24', '2027-05-31', 79),
(4, 'dublin', 'berlin', '2026-10-24', '2027-06-30', 119),
(5, 'cork', 'brussels', '2026-10-24', '2027-04-30', 82),
(6, 'dublin', 'lisbon', '2026-10-24', '2027-06-30', 139),
(7, 'knock', 'edinburgh', '2026-10-24', '2027-03-31', 74),
(8, 'cork', 'charles de gaulle', '2026-10-24', '2027-06-30', 99),
(9, 'dublin', 'barcelona', '2026-10-24', '2027-06-30', 129),
(10, 'shannon', 'faro', '2026-10-24', '2027-05-31', 109),
(11, 'dublin', 'rome', '2026-10-24', '2027-06-30', 145),
(12, 'cork', 'heathrow', '2026-10-24', '2027-06-30', 94);

CREATE TABLE IF NOT EXISTS `user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(15) NOT NULL,
  `password` varchar(15) NOT NULL,
  `email` varchar(40) NOT NULL,
  `CreditCardNo` varchar(16) NOT NULL,
  `passport` int(10) NOT NULL,
  `suspend` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;

-- rmiller is intentionally preserved; all other accounts are new.
-- Card values use a dummy 16-digit pattern, not real payment details.
INSERT INTO `user`
  (`id`, `username`, `password`, `email`, `CreditCardNo`, `passport`, `suspend`) VALUES
(1, 'rmiller', 'rmiller', 'rmiller@example.test', '0000000000000000', 810000001, 0),
(2, 'aoifebyrne', 'aoife2026', 'aoife.byrne@example.test', '0000000000000000', 810000002, 0),
(3, 'cianmurphy', 'cian2026', 'cian.murphy@example.test', '0000000000000000', 810000003, 0),
(4, 'maevekelly', 'maeve2026', 'maeve.kelly@example.test', '0000000000000000', 810000004, 0),
(5, 'seanwalsh', 'sean2026', 'sean.walsh@example.test', '0000000000000000', 810000005, 0),
(6, 'noradoyle', 'nora2026', 'nora.doyle@example.test', '0000000000000000', 810000006, 0),
(7, 'finnryan', 'finn2026', 'finn.ryan@example.test', '0000000000000000', 810000007, 0),
(8, 'orlahayes', 'orla2026', 'orla.hayes@example.test', '0000000000000000', 810000008, 0),
(9, 'liamburke', 'liam2026', 'liam.burke@example.test', '0000000000000000', 810000009, 0),
(10, 'saoirsefox', 'saoirse2026', 'saoirse.fox@example.test', '0000000000000000', 810000010, 0),
(11, 'eoinnolan', 'eoin2026', 'eoin.nolan@example.test', '0000000000000000', 810000011, 1),
(12, 'gracequinn', 'grace2026', 'grace.quinn@example.test', '0000000000000000', 810000012, 0),
(13, 'lucamoretti', 'luca2026', 'luca.moretti@example.test', '0000000000000000', 810000013, 0),
(14, 'emmaclarke', 'emma2026', 'emma.clarke@example.test', '0000000000000000', 810000014, 0);

CREATE TABLE IF NOT EXISTS `passangers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(20) NOT NULL,
  `passport` varchar(10) NOT NULL,
  `route` int(11) NOT NULL,
  `date` date NOT NULL,
  `registered` tinyint(1) NOT NULL,
  `registeredName` varchar(15) NOT NULL,
  `creditCardno` varchar(16) NOT NULL,
  `email` varchar(40) NOT NULL,
  `seatNo` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;

-- Seat numbers are unique within each route/date and stay within the legacy
-- application's 20-seat capacity. Registered bookings match user accounts.
-- Guest bookings use registered=0 and registeredName='Guest'.
INSERT INTO `passangers`
  (`id`, `name`, `passport`, `route`, `date`, `registered`, `registeredName`, `creditCardno`, `email`, `seatNo`) VALUES
(1, 'Aoife Byrne', '810000002', 1, '2026-10-29', 1, 'aoifebyrne', '0000000000000000', 'aoife.byrne@example.test', 1),
(2, 'Aoife Byrne', '810000002', 1, '2026-10-29', 1, 'aoifebyrne', '0000000000000000', 'aoife.byrne@example.test', 2),
(3, 'Cian Murphy', '810000003', 1, '2026-10-29', 1, 'cianmurphy', '0000000000000000', 'cian.murphy@example.test', 3),
(4, 'Guest Traveller', '820000001', 1, '2026-10-29', 0, 'Guest', '0000000000000000', 'guest01@example.test', 4),
(5, 'Maeve Kelly', '810000004', 1, '2026-11-12', 1, 'maevekelly', '0000000000000000', 'maeve.kelly@example.test', 1),
(6, 'Sean Walsh', '810000005', 1, '2026-11-12', 1, 'seanwalsh', '0000000000000000', 'sean.walsh@example.test', 2),
(7, 'Nora Doyle', '810000006', 2, '2026-11-03', 1, 'noradoyle', '0000000000000000', 'nora.doyle@example.test', 1),
(8, 'Nora Doyle', '810000006', 2, '2026-11-03', 1, 'noradoyle', '0000000000000000', 'nora.doyle@example.test', 2),
(9, 'Finn Ryan', '810000007', 2, '2026-11-03', 1, 'finnryan', '0000000000000000', 'finn.ryan@example.test', 3),
(10, 'Guest Traveller', '820000002', 2, '2026-11-21', 0, 'Guest', '0000000000000000', 'guest02@example.test', 1),
(11, 'Orla Hayes', '810000008', 3, '2026-11-06', 1, 'orlahayes', '0000000000000000', 'orla.hayes@example.test', 1),
(12, 'Liam Burke', '810000009', 3, '2026-11-06', 1, 'liamburke', '0000000000000000', 'liam.burke@example.test', 2),
(13, 'Saoirse Fox', '810000010', 4, '2026-11-18', 1, 'saoirsefox', '0000000000000000', 'saoirse.fox@example.test', 1),
(14, 'Eoin Nolan', '810000011', 4, '2026-11-18', 1, 'eoinnolan', '0000000000000000', 'eoin.nolan@example.test', 2),
(15, 'Grace Quinn', '810000012', 4, '2026-11-18', 1, 'gracequinn', '0000000000000000', 'grace.quinn@example.test', 3),
(16, 'Guest Traveller', '820000003', 4, '2026-11-18', 0, 'Guest', '0000000000000000', 'guest03@example.test', 4),
(17, 'Aoife Byrne', '810000002', 5, '2026-12-02', 1, 'aoifebyrne', '0000000000000000', 'aoife.byrne@example.test', 1),
(18, 'Cian Murphy', '810000003', 5, '2026-12-02', 1, 'cianmurphy', '0000000000000000', 'cian.murphy@example.test', 2),
(19, 'Luca Moretti', '810000013', 6, '2026-12-10', 1, 'lucamoretti', '0000000000000000', 'luca.moretti@example.test', 1),
(20, 'Emma Clarke', '810000014', 6, '2026-12-10', 1, 'emmaclarke', '0000000000000000', 'emma.clarke@example.test', 2),
(21, 'Nora Doyle', '810000006', 6, '2027-01-08', 1, 'noradoyle', '0000000000000000', 'nora.doyle@example.test', 1),
(22, 'Finn Ryan', '810000007', 7, '2026-11-25', 1, 'finnryan', '0000000000000000', 'finn.ryan@example.test', 1),
(23, 'Orla Hayes', '810000008', 7, '2026-11-25', 1, 'orlahayes', '0000000000000000', 'orla.hayes@example.test', 2),
(24, 'Guest Traveller', '820000004', 7, '2026-11-25', 0, 'Guest', '0000000000000000', 'guest04@example.test', 3),
(25, 'Liam Burke', '810000009', 8, '2026-12-16', 1, 'liamburke', '0000000000000000', 'liam.burke@example.test', 1),
(26, 'Saoirse Fox', '810000010', 8, '2026-12-16', 1, 'saoirsefox', '0000000000000000', 'saoirse.fox@example.test', 2),
(27, 'Grace Quinn', '810000012', 9, '2027-01-14', 1, 'gracequinn', '0000000000000000', 'grace.quinn@example.test', 1),
(28, 'Aoife Byrne', '810000002', 9, '2027-01-14', 1, 'aoifebyrne', '0000000000000000', 'aoife.byrne@example.test', 2),
(29, 'Cian Murphy', '810000003', 10, '2027-02-05', 1, 'cianmurphy', '0000000000000000', 'cian.murphy@example.test', 1),
(30, 'Guest Traveller', '820000005', 10, '2027-02-05', 0, 'Guest', '0000000000000000', 'guest05@example.test', 2),
(31, 'Luca Moretti', '810000013', 11, '2027-02-18', 1, 'lucamoretti', '0000000000000000', 'luca.moretti@example.test', 1),
(32, 'Emma Clarke', '810000014', 11, '2027-02-18', 1, 'emmaclarke', '0000000000000000', 'emma.clarke@example.test', 2),
(33, 'Sean Walsh', '810000005', 12, '2027-03-03', 1, 'seanwalsh', '0000000000000000', 'sean.walsh@example.test', 1),
(34, 'Maeve Kelly', '810000004', 12, '2027-03-03', 1, 'maevekelly', '0000000000000000', 'maeve.kelly@example.test', 2);
