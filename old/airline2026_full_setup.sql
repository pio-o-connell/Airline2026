-- PHP Airlines / Airline2026
-- Fresh fictional Airline2026 development dataset based on the original 2014 schema, extended with scheduled flights and bookings.
-- MySQL 5.5/5.7 compatible. Intended for local testing only.
--
-- WARNING: This script DROPS and recreates all Airline2026 tables listed below.
-- Use only for a fresh/test database. Existing data in those tables will be lost.
-- All people and contact details are fictional; card numbers are dummy values.
-- Plain-text passwords are retained only for compatibility with the legacy app.
-- Never use these credentials or this schema for a public/production deployment.

CREATE DATABASE IF NOT EXISTS `airline2026`
  DEFAULT CHARACTER SET latin1 COLLATE latin1_swedish_ci;
USE `airline2026`;

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";
SET NAMES latin1;

DROP TABLE IF EXISTS `passangers`;
DROP TABLE IF EXISTS `booking_flights`;
DROP TABLE IF EXISTS `booking`;
DROP TABLE IF EXISTS `flights`;
DROP TABLE IF EXISTS `routes`;
DROP TABLE IF EXISTS `user`;
DROP TABLE IF EXISTS `login`;

-- Preserve the original table and column names/types.
CREATE TABLE `login` (
  `username` varchar(20) NOT NULL,
  `password` varchar(20) NOT NULL,
  PRIMARY KEY (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

INSERT INTO `login` (`username`, `password`) VALUES
('rmiller', 'rmiller'),
('opsdesk', 'desk2026'),
('routelead', 'route2026');

CREATE TABLE `routes` (
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

CREATE TABLE `user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(15) NOT NULL,
  `password` varchar(15) NOT NULL,
  `email` varchar(40) NOT NULL,
  `CreditCardNo` varchar(16) NOT NULL,
  `passport` int(10) NOT NULL,
  `suspend` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_user_username` (`username`)
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

CREATE TABLE `passangers` (
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
  `booking_flight_id` BIGINT UNSIGNED DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_passangers_route_date_seat` (`route`, `date`, `seatNo`),
  KEY `idx_passangers_registered_name` (`registeredName`),
  KEY `idx_passangers_booking_flight` (`booking_flight_id`),
  CONSTRAINT `fk_passangers_route`
    FOREIGN KEY (`route`) REFERENCES `routes` (`id`)
    ON DELETE RESTRICT ON UPDATE RESTRICT
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

-- ---------------------------------------------------------------------------
-- Airline2026 extension: scheduled flights and complete bookings
-- ---------------------------------------------------------------------------

-- The legacy routes table contains date ranges but no timetable. This test
-- dataset therefore creates one placeholder flight per route per operating
-- date. Departure/arrival times remain NULL rather than being invented.
CREATE TABLE `flights` (
  `flight_id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `route_id` INT(11) NOT NULL,
  `flight_date` DATE NOT NULL,
  `departure_time` TIME DEFAULT NULL,
  `arrival_time` TIME DEFAULT NULL,
  `capacity` SMALLINT UNSIGNED NOT NULL DEFAULT 20,
  `price` INT(11) NOT NULL,
  `status` VARCHAR(20) NOT NULL DEFAULT 'scheduled',
  PRIMARY KEY (`flight_id`),
  UNIQUE KEY `uq_flights_route_date` (`route_id`, `flight_date`),
  KEY `idx_flights_date_route` (`flight_date`, `route_id`),
  CONSTRAINT `fk_flights_route`
    FOREIGN KEY (`route_id`) REFERENCES `routes` (`id`)
    ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `booking` (
  `booking_id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `booking_reference` VARCHAR(30) NOT NULL,
  `user_id` INT(11) DEFAULT NULL,
  `booking_status` VARCHAR(20) NOT NULL DEFAULT 'pending',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`booking_id`),
  UNIQUE KEY `uq_booking_reference` (`booking_reference`),
  KEY `idx_booking_user` (`user_id`),
  CONSTRAINT `fk_booking_user`
    FOREIGN KEY (`user_id`) REFERENCES `user` (`id`)
    ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `booking_flights` (
  `booking_flight_id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `booking_id` BIGINT UNSIGNED NOT NULL,
  `flight_id` BIGINT UNSIGNED NOT NULL,
  `sequence_no` SMALLINT UNSIGNED NOT NULL,
  PRIMARY KEY (`booking_flight_id`),
  UNIQUE KEY `uq_booking_sequence` (`booking_id`, `sequence_no`),
  KEY `idx_booking_flights_flight` (`flight_id`),
  CONSTRAINT `fk_booking_flights_booking`
    FOREIGN KEY (`booking_id`) REFERENCES `booking` (`booking_id`)
    ON DELETE RESTRICT ON UPDATE RESTRICT,
  CONSTRAINT `fk_booking_flights_flight`
    FOREIGN KEY (`flight_id`) REFERENCES `flights` (`flight_id`)
    ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- Create one placeholder flight for every date in each route's operating
-- window. A 0..999 number set is ample for the current route date ranges.
INSERT INTO `flights`
  (`route_id`, `flight_date`, `departure_time`, `arrival_time`, `capacity`, `price`, `status`)
SELECT r.`id`, DATE_ADD(r.`start`, INTERVAL numbers.n DAY), NULL, NULL, 20, r.`price`, 'scheduled'
FROM `routes` AS r
CROSS JOIN (
  SELECT ones.n + tens.n * 10 + hundreds.n * 100 AS n
  FROM (SELECT 0 n UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9) ones
  CROSS JOIN (SELECT 0 n UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9) tens
  CROSS JOIN (SELECT 0 n UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9) hundreds
) AS numbers
WHERE DATE_ADD(r.`start`, INTERVAL numbers.n DAY) <= r.`end`;

-- Seed representative demo bookings for each route/date represented in the
-- passenger sample. The first registered passenger on that route/date is the
-- lead account; if all passengers are guests, user_id remains NULL.
-- These are fictional test bookings, not real customer transactions.
INSERT INTO `booking` (`booking_reference`, `user_id`, `booking_status`)
SELECT
  CONCAT('DEMO-', p.`route`, '-', DATE_FORMAT(p.`date`, '%Y%m%d')),
  (
    SELECT u.`id`
    FROM `passangers` p2
    JOIN `user` u ON u.`username` = p2.`registeredName`
    WHERE p2.`route` = p.`route`
      AND p2.`date` = p.`date`
      AND p2.`registered` = 1
    ORDER BY p2.`id`
    LIMIT 1
  ),
  'confirmed'
FROM `passangers` p
GROUP BY p.`route`, p.`date`;

-- Each seeded booking has one flight leg. This reflects the legacy sample's
-- single-route passenger records; future return/multi-city bookings can have
-- sequence_no 1, 2, 3, ... through the application.
INSERT INTO `booking_flights` (`booking_id`, `flight_id`, `sequence_no`)
SELECT b.`booking_id`, f.`flight_id`, 1
FROM `booking` b
JOIN `flights` f
  ON b.`booking_reference` = CONCAT('DEMO-', f.`route_id`, '-', DATE_FORMAT(f.`flight_date`, '%Y%m%d'));

-- Connect each existing passenger/seat record to its matching seeded leg.
UPDATE `passangers` p
JOIN `flights` f
  ON f.`route_id` = p.`route` AND f.`flight_date` = p.`date`
JOIN `booking_flights` bf ON bf.`flight_id` = f.`flight_id`
JOIN `booking` b ON b.`booking_id` = bf.`booking_id`
  AND b.`booking_reference` = CONCAT('DEMO-', p.`route`, '-', DATE_FORMAT(p.`date`, '%Y%m%d'))
SET p.`booking_flight_id` = bf.`booking_flight_id`;

-- Add the passenger-to-booking-leg foreign key after the seed links exist.
ALTER TABLE `passangers`
  ADD CONSTRAINT `fk_passangers_booking_flight`
  FOREIGN KEY (`booking_flight_id`) REFERENCES `booking_flights` (`booking_flight_id`)
  ON DELETE RESTRICT ON UPDATE RESTRICT;

-- Final verification counts (shown in the MySQL output).
SELECT 'routes' AS table_name, COUNT(*) AS row_count FROM `routes`
UNION ALL SELECT 'user', COUNT(*) FROM `user`
UNION ALL SELECT 'login', COUNT(*) FROM `login`
UNION ALL SELECT 'passangers', COUNT(*) FROM `passangers`
UNION ALL SELECT 'flights', COUNT(*) FROM `flights`
UNION ALL SELECT 'booking', COUNT(*) FROM `booking`
UNION ALL SELECT 'booking_flights', COUNT(*) FROM `booking_flights`;
