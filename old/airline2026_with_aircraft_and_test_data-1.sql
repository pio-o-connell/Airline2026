-- Airline2026: fresh development database with aircraft configurations, flight details,
-- seat maps, and fictional test bookings.
-- Compatible with MySQL 5.5/5.7.
--
-- WARNING: DESTRUCTIVE FRESH SETUP.
-- This script drops and recreates the Airline2026 tables listed below.
-- Use only when you are happy to replace the contents of those tables.
-- All names/contact details and card values are fictional test data.
-- Plain-text passwords are retained only for compatibility with the legacy app.
-- Do not use this schema or its credentials for a public/production deployment.

CREATE DATABASE IF NOT EXISTS `airline2026`
  DEFAULT CHARACTER SET latin1 COLLATE latin1_swedish_ci;
USE `airline2026`;

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";
SET NAMES latin1;

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS `passangers`;
DROP TABLE IF EXISTS `booking_flights`;
DROP TABLE IF EXISTS `booking`;
DROP TABLE IF EXISTS `flights`;
DROP TABLE IF EXISTS `aircraft_seats`;
DROP TABLE IF EXISTS `aircraft`;
DROP TABLE IF EXISTS `routes`;
DROP TABLE IF EXISTS `user`;
DROP TABLE IF EXISTS `login`;
SET FOREIGN_KEY_CHECKS = 1;

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
  `dest` varchar(30) NOT NULL,
  `start` date NOT NULL,
  `end` date NOT NULL,
  `price` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;

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

-- Aircraft configuration defines the actual seats. Seat numbers remain numeric
-- for compatibility with the legacy passangers.seatNo field. seat_label is for
-- the visual map (for example 1A, 1B). The first 18 seats are premium.
CREATE TABLE `aircraft` (
  `aircraft_id` int(11) NOT NULL AUTO_INCREMENT,
  `aircraft_code` varchar(12) NOT NULL,
  `model` varchar(60) NOT NULL,
  `configuration_name` varchar(40) NOT NULL,
  `capacity` smallint unsigned NOT NULL,
  PRIMARY KEY (`aircraft_id`),
  UNIQUE KEY `uq_aircraft_code` (`aircraft_code`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

INSERT INTO `aircraft`
  (`aircraft_id`, `aircraft_code`, `model`, `configuration_name`, `capacity`) VALUES
(1, 'AC180', 'Airbus A320 (fictional configuration)', '180 seats, 30 rows, 3-3 layout', 180),
(2, 'AC220', 'Airbus A321 (fictional configuration)', '220 seats, 37 rows, 3-3 layout', 220);

CREATE TABLE `aircraft_seats` (
  `aircraft_seat_id` int(11) NOT NULL AUTO_INCREMENT,
  `aircraft_id` int(11) NOT NULL,
  `seat_no` smallint unsigned NOT NULL,
  `seat_label` varchar(4) NOT NULL,
  `seat_row` smallint unsigned NOT NULL,
  `seat_position` char(1) NOT NULL,
  `is_premium` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`aircraft_seat_id`),
  UNIQUE KEY `uq_aircraft_seat_no` (`aircraft_id`, `seat_no`),
  UNIQUE KEY `uq_aircraft_seat_label` (`aircraft_id`, `seat_label`),
  KEY `idx_aircraft_seats_premium` (`aircraft_id`, `is_premium`),
  CONSTRAINT `fk_aircraft_seats_aircraft`
    FOREIGN KEY (`aircraft_id`) REFERENCES `aircraft` (`aircraft_id`)
    ON DELETE CASCADE ON UPDATE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- Generate 180 seats for AC180 and 220 seats for AC220.
-- The 3-3 map uses A/B/C and D/E/F across each row.
INSERT INTO `aircraft_seats`
  (`aircraft_id`, `seat_no`, `seat_label`, `seat_row`, `seat_position`, `is_premium`)
SELECT a.`aircraft_id`,
       nums.n,
       CONCAT(FLOOR((nums.n - 1) / 6) + 1,
              ELT(MOD(nums.n - 1, 6) + 1, 'A', 'B', 'C', 'D', 'E', 'F')),
       FLOOR((nums.n - 1) / 6) + 1,
       ELT(MOD(nums.n - 1, 6) + 1, 'A', 'B', 'C', 'D', 'E', 'F'),
       CASE WHEN nums.n <= 18 THEN 1 ELSE 0 END
FROM `aircraft` a
CROSS JOIN (
  SELECT ones.n + tens.n * 10 + hundreds.n * 100 + 1 AS n
  FROM (SELECT 0 n UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9) ones
  CROSS JOIN (SELECT 0 n UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9) tens
  CROSS JOIN (SELECT 0 n UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9) hundreds
) nums
WHERE nums.n <= a.`capacity`;

CREATE TABLE `flights` (
  `flight_id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `route_id` INT(11) NOT NULL,
  `flight_date` DATE NOT NULL,
  `flight_code` varchar(12) NOT NULL,
  `aircraft_id` int(11) NOT NULL,
  `departure_time` TIME DEFAULT NULL,
  `arrival_time` TIME DEFAULT NULL,
  `expected_departure` TIME DEFAULT NULL,
  `expected_arrival` TIME DEFAULT NULL,
  -- Retained for compatibility with legacy code; should match assigned aircraft capacity.
  `capacity` SMALLINT UNSIGNED NOT NULL,
  `price` INT(11) NOT NULL,
  `status` VARCHAR(20) NOT NULL DEFAULT 'scheduled',
  PRIMARY KEY (`flight_id`),
  UNIQUE KEY `uq_flights_route_date` (`route_id`, `flight_date`),
  KEY `idx_flights_date_route` (`flight_date`, `route_id`),
  KEY `idx_flights_code` (`flight_code`),
  KEY `idx_flights_aircraft` (`aircraft_id`),
  CONSTRAINT `fk_flights_route`
    FOREIGN KEY (`route_id`) REFERENCES `routes` (`id`)
    ON DELETE RESTRICT ON UPDATE RESTRICT,
  CONSTRAINT `fk_flights_aircraft`
    FOREIGN KEY (`aircraft_id`) REFERENCES `aircraft` (`aircraft_id`)
    ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- Demo timetable. These are fictional scheduled times, not real airline data.
-- Route-specific flight codes repeat on different dates, as airline flight
-- numbers normally do. Expected times initially match scheduled times.
INSERT INTO `flights`
  (`route_id`, `flight_date`, `flight_code`, `aircraft_id`,
   `departure_time`, `arrival_time`, `expected_departure`, `expected_arrival`,
   `capacity`, `price`, `status`)
SELECT r.`id`,
       DATE_ADD(r.`start`, INTERVAL nums.n DAY),
       CASE r.`id`
         WHEN 1 THEN 'PA101'
         WHEN 2 THEN 'PA202'
         WHEN 3 THEN 'PA303'
         WHEN 4 THEN 'PA404'
         WHEN 5 THEN 'PA505'
         WHEN 6 THEN 'PA606'
         WHEN 7 THEN 'PA707'
         WHEN 8 THEN 'PA808'
         WHEN 9 THEN 'PA909'
         WHEN 10 THEN 'PA110'
         WHEN 11 THEN 'PA211'
         ELSE 'PA312'
       END,
       CASE WHEN r.`id` IN (2, 5, 8, 10) THEN 2 ELSE 1 END,
       CASE r.`id`
         WHEN 1 THEN '08:15:00'
         WHEN 2 THEN '09:30:00'
         WHEN 3 THEN '11:00:00'
         WHEN 4 THEN '13:20:00'
         WHEN 5 THEN '15:10:00'
         WHEN 6 THEN '17:45:00'
         WHEN 7 THEN '07:40:00'
         WHEN 8 THEN '10:25:00'
         WHEN 9 THEN '12:50:00'
         WHEN 10 THEN '14:35:00'
         WHEN 11 THEN '16:05:00'
         ELSE '19:15:00'
       END,
       CASE r.`id`
         WHEN 1 THEN '09:35:00'
         WHEN 2 THEN '12:20:00'
         WHEN 3 THEN '12:20:00'
         WHEN 4 THEN '16:30:00'
         WHEN 5 THEN '17:50:00'
         WHEN 6 THEN '21:05:00'
         WHEN 7 THEN '08:55:00'
         WHEN 8 THEN '12:00:00'
         WHEN 9 THEN '16:20:00'
         WHEN 10 THEN '18:30:00'
         WHEN 11 THEN '19:45:00'
         ELSE '20:35:00'
       END,
       CASE r.`id`
         WHEN 1 THEN '08:15:00'
         WHEN 2 THEN '09:30:00'
         WHEN 3 THEN '11:00:00'
         WHEN 4 THEN '13:20:00'
         WHEN 5 THEN '15:10:00'
         WHEN 6 THEN '17:45:00'
         WHEN 7 THEN '07:40:00'
         WHEN 8 THEN '10:25:00'
         WHEN 9 THEN '12:50:00'
         WHEN 10 THEN '14:35:00'
         WHEN 11 THEN '16:05:00'
         ELSE '19:15:00'
       END,
       CASE r.`id`
         WHEN 1 THEN '09:35:00'
         WHEN 2 THEN '12:20:00'
         WHEN 3 THEN '12:20:00'
         WHEN 4 THEN '16:30:00'
         WHEN 5 THEN '17:50:00'
         WHEN 6 THEN '21:05:00'
         WHEN 7 THEN '08:55:00'
         WHEN 8 THEN '12:00:00'
         WHEN 9 THEN '16:20:00'
         WHEN 10 THEN '18:30:00'
         WHEN 11 THEN '19:45:00'
         ELSE '20:35:00'
       END,
       CASE WHEN r.`id` IN (2, 5, 8, 10) THEN 220 ELSE 180 END,
       r.`price`,
       'scheduled'
FROM `routes` r
CROSS JOIN (
  SELECT ones.n + tens.n * 10 + hundreds.n * 100 AS n
  FROM (SELECT 0 n UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9) ones
  CROSS JOIN (SELECT 0 n UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9) tens
  CROSS JOIN (SELECT 0 n UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9) hundreds
) nums
WHERE DATE_ADD(r.`start`, INTERVAL nums.n DAY) <= r.`end`;

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
  -- The legacy route/date/seat key is retained for now to reduce breakage.
  -- The PHP booking code must additionally validate seat availability by flight.
  UNIQUE KEY `uq_passangers_route_date_seat` (`route`, `date`, `seatNo`),
  KEY `idx_passangers_registered_name` (`registeredName`),
  KEY `idx_passangers_booking_flight` (`booking_flight_id`),
  CONSTRAINT `fk_passangers_route`
    FOREIGN KEY (`route`) REFERENCES `routes` (`id`)
    ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- Original-style passenger test data, with numeric seat numbers retained.
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

-- One demo booking per route/date. These records are fictional and only support testing.
INSERT INTO `booking` (`booking_reference`, `user_id`, `booking_status`)
SELECT CONCAT('DEMO-', p.`route`, '-', DATE_FORMAT(p.`date`, '%Y%m%d')),
       (SELECT u.`id`
        FROM `passangers` p2
        JOIN `user` u ON u.`username` = p2.`registeredName`
        WHERE p2.`route` = p.`route`
          AND p2.`date` = p.`date`
          AND p2.`registered` = 1
        ORDER BY p2.`id`
        LIMIT 1),
       'confirmed'
FROM `passangers` p
GROUP BY p.`route`, p.`date`;

INSERT INTO `booking_flights` (`booking_id`, `flight_id`, `sequence_no`)
SELECT b.`booking_id`, f.`flight_id`, 1
FROM `booking` b
JOIN `flights` f
  ON b.`booking_reference` = CONCAT('DEMO-', f.`route_id`, '-', DATE_FORMAT(f.`flight_date`, '%Y%m%d'));

UPDATE `passangers` p
JOIN `flights` f
  ON f.`route_id` = p.`route` AND f.`flight_date` = p.`date`
JOIN `booking_flights` bf ON bf.`flight_id` = f.`flight_id`
JOIN `booking` b ON b.`booking_id` = bf.`booking_id`
  AND b.`booking_reference` = CONCAT('DEMO-', p.`route`, '-', DATE_FORMAT(p.`date`, '%Y%m%d'))
SET p.`booking_flight_id` = bf.`booking_flight_id`;

ALTER TABLE `passangers`
  ADD CONSTRAINT `fk_passangers_booking_flight`
  FOREIGN KEY (`booking_flight_id`) REFERENCES `booking_flights` (`booking_flight_id`)
  ON DELETE RESTRICT ON UPDATE RESTRICT;

-- Verification queries: table totals, aircraft seat totals, and sample flight availability.
SELECT 'routes' AS table_name, COUNT(*) AS row_count FROM `routes`
UNION ALL SELECT 'user', COUNT(*) FROM `user`
UNION ALL SELECT 'login', COUNT(*) FROM `login`
UNION ALL SELECT 'aircraft', COUNT(*) FROM `aircraft`
UNION ALL SELECT 'aircraft_seats', COUNT(*) FROM `aircraft_seats`
UNION ALL SELECT 'passangers', COUNT(*) FROM `passangers`
UNION ALL SELECT 'flights', COUNT(*) FROM `flights`
UNION ALL SELECT 'booking', COUNT(*) FROM `booking`
UNION ALL SELECT 'booking_flights', COUNT(*) FROM `booking_flights`;

SELECT a.`aircraft_code`, a.`capacity`, COUNT(s.`aircraft_seat_id`) AS configured_seats,
       SUM(s.`is_premium`) AS premium_seats
FROM `aircraft` a
LEFT JOIN `aircraft_seats` s ON s.`aircraft_id` = a.`aircraft_id`
GROUP BY a.`aircraft_id`, a.`aircraft_code`, a.`capacity`;

SELECT f.`flight_code`, f.`flight_date`, f.`capacity`,
       COUNT(p.`id`) AS booked_passenger_seats,
       f.`capacity` - COUNT(p.`id`) AS seats_remaining
FROM `flights` f
LEFT JOIN `passangers` p ON p.`booking_flight_id` IN (
  SELECT bf.`booking_flight_id`
  FROM `booking_flights` bf
  WHERE bf.`flight_id` = f.`flight_id`
)
WHERE f.`flight_date` = '2026-10-29'
GROUP BY f.`flight_id`, f.`flight_code`, f.`flight_date`, f.`capacity`
ORDER BY f.`flight_code`;
