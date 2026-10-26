-- Airline2026 standalone MySQL 5.7 database script.
-- WARNING: Running this script permanently destroys the existing airline2026
-- database and all data in it. Use only for a fresh local/development install.

DROP DATABASE IF EXISTS `airline2026`;
CREATE DATABASE `airline2026`
  DEFAULT CHARACTER SET latin1 COLLATE latin1_swedish_ci;
USE `airline2026`;

SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO';
SET time_zone = '+00:00';
SET NAMES latin1;
SET FOREIGN_KEY_CHECKS = 0;

CREATE TABLE `login` (
  `username` varchar(20) NOT NULL,
  `password` varchar(20) NOT NULL,
  PRIMARY KEY (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `routes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `source` varchar(20) NOT NULL,
  `dest` varchar(30) NOT NULL,
  `start` date NOT NULL,
  `end` date NOT NULL,
  `price` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_routes_dates` (`start`, `end`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(15) NOT NULL,
  `password` varchar(15) NOT NULL,
  `email` varchar(40) NOT NULL,
  `CreditCardNo` varchar(16) NOT NULL,
  `passport` int(10) NOT NULL,
  `suspend` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_user_username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `aircraft` (
  `aircraft_id` int(11) NOT NULL AUTO_INCREMENT,
  `aircraft_code` varchar(12) NOT NULL,
  `model` varchar(60) NOT NULL,
  `configuration_name` varchar(40) NOT NULL,
  `capacity` smallint unsigned NOT NULL,
  PRIMARY KEY (`aircraft_id`),
  UNIQUE KEY `uq_aircraft_code` (`aircraft_code`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

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

CREATE TABLE `flights` (
  `flight_id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `route_id` int(11) NOT NULL,
  `flight_date` date NOT NULL,
  `flight_code` varchar(12) NOT NULL,
  `aircraft_id` int(11) NOT NULL,
  `departure_time` time NOT NULL,
  `arrival_time` time NOT NULL,
  `expected_departure` time NOT NULL,
  `expected_arrival` time NOT NULL,
  `capacity` smallint unsigned NOT NULL,
  `price` int(11) NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'scheduled',
  PRIMARY KEY (`flight_id`),
  UNIQUE KEY `uq_flights_route_date` (`route_id`, `flight_date`),
  UNIQUE KEY `uq_flights_code_date` (`flight_code`, `flight_date`),
  KEY `idx_flights_date_route` (`flight_date`, `route_id`),
  KEY `idx_flights_aircraft` (`aircraft_id`),
  CONSTRAINT `fk_flights_route` FOREIGN KEY (`route_id`) REFERENCES `routes` (`id`)
    ON DELETE RESTRICT ON UPDATE RESTRICT,
  CONSTRAINT `fk_flights_aircraft` FOREIGN KEY (`aircraft_id`) REFERENCES `aircraft` (`aircraft_id`)
    ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `booking` (
  `booking_id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `booking_reference` varchar(30) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `booking_status` varchar(20) NOT NULL DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`booking_id`),
  UNIQUE KEY `uq_booking_reference` (`booking_reference`),
  KEY `idx_booking_user` (`user_id`),
  CONSTRAINT `fk_booking_user` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`)
    ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `booking_flights` (
  `booking_flight_id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `booking_id` bigint unsigned NOT NULL,
  `flight_id` bigint unsigned NOT NULL,
  `sequence_no` smallint unsigned NOT NULL,
  PRIMARY KEY (`booking_flight_id`),
  UNIQUE KEY `uq_booking_sequence` (`booking_id`, `sequence_no`),
  UNIQUE KEY `uq_booking_flight` (`booking_id`, `flight_id`),
  KEY `idx_booking_flights_flight` (`flight_id`),
  CONSTRAINT `fk_booking_flights_booking` FOREIGN KEY (`booking_id`) REFERENCES `booking` (`booking_id`)
    ON DELETE RESTRICT ON UPDATE RESTRICT,
  CONSTRAINT `fk_booking_flights_flight` FOREIGN KEY (`flight_id`) REFERENCES `flights` (`flight_id`)
    ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `passangers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(20) NOT NULL,
  `passport` varchar(10) NOT NULL,
  `route` int(11) NOT NULL,
  `date` date NOT NULL,
  `registered` tinyint(1) NOT NULL DEFAULT 0,
  `registeredName` varchar(15) NOT NULL,
  `creditCardno` varchar(16) NOT NULL,
  `email` varchar(40) NOT NULL,
  `seatNo` int(11) NOT NULL,
  `flight_id` bigint unsigned DEFAULT NULL,
  `booking_flight_id` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_passangers_flight_seat` (`flight_id`, `seatNo`),
  KEY `idx_passangers_route_date` (`route`, `date`),
  KEY `idx_passangers_registered_name` (`registeredName`),
  KEY `idx_passangers_booking_flight` (`booking_flight_id`),
  CONSTRAINT `fk_passangers_route` FOREIGN KEY (`route`) REFERENCES `routes` (`id`)
    ON DELETE RESTRICT ON UPDATE RESTRICT,
  CONSTRAINT `fk_passangers_flight` FOREIGN KEY (`flight_id`) REFERENCES `flights` (`flight_id`)
    ON DELETE RESTRICT ON UPDATE RESTRICT,
  CONSTRAINT `fk_passangers_booking_flight` FOREIGN KEY (`booking_flight_id`)
    REFERENCES `booking_flights` (`booking_flight_id`)
    ON DELETE SET NULL ON UPDATE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

DELIMITER //
CREATE TRIGGER `passangers_route_date_flight`
BEFORE INSERT ON `passangers`
FOR EACH ROW
BEGIN
  IF NEW.flight_id IS NULL OR NEW.flight_id = 0 THEN
    SET NEW.flight_id = (
      SELECT f.flight_id FROM flights f
      WHERE f.route_id = NEW.route AND f.flight_date = NEW.date
      LIMIT 1
    );
  END IF;
  IF NEW.flight_id IS NULL THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Passenger route/date has no scheduled flight';
  END IF;
END//
DELIMITER ;

INSERT INTO `login` (`username`, `password`) VALUES
  ('rmiller', 'rmiller'), ('opsdesk', 'desk2026'), ('routelead', 'route2026');

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

INSERT INTO `aircraft`
  (`aircraft_id`, `aircraft_code`, `model`, `configuration_name`, `capacity`) VALUES
  (1, 'AC180', 'Airbus A320 (fictional configuration)', '180 seats, 30 rows, 3-3 layout', 180),
  (2, 'AC220', 'Airbus A321 (fictional configuration)', '220 seats, 37 rows, 3-3 layout', 220);

INSERT INTO `aircraft_seats`
  (`aircraft_id`, `seat_no`, `seat_label`, `seat_row`, `seat_position`, `is_premium`)
SELECT a.aircraft_id, n.n,
       CONCAT(FLOOR((n.n - 1) / 6) + 1,
              ELT(MOD(n.n - 1, 6) + 1, 'A', 'B', 'C', 'D', 'E', 'F')),
       FLOOR((n.n - 1) / 6) + 1,
       ELT(MOD(n.n - 1, 6) + 1, 'A', 'B', 'C', 'D', 'E', 'F'),
       CASE WHEN n.n <= 18 THEN 1 ELSE 0 END
FROM aircraft a
JOIN (
  SELECT ones.n + tens.n * 10 + hundreds.n * 100 + 1 AS n
  FROM (SELECT 0 n UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4
        UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9) ones
  CROSS JOIN (SELECT 0 n UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4
        UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9) tens
  CROSS JOIN (SELECT 0 n UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4
        UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9) hundreds
) n ON n.n <= a.capacity;

INSERT INTO `flights`
  (`route_id`, `flight_date`, `flight_code`, `aircraft_id`, `departure_time`, `arrival_time`,
   `expected_departure`, `expected_arrival`, `capacity`, `price`, `status`)
SELECT DISTINCT r.id, DATE_ADD(r.start, INTERVAL d.n DAY),
       CONCAT('PA', LPAD(r.id, 2, '0'),
              DATE_FORMAT(DATE_ADD(r.start, INTERVAL d.n DAY), '%y%m%d')),
       CASE WHEN r.id IN (2, 5, 8, 10) THEN 2 ELSE 1 END,
       '08:15:00', '10:00:00', '08:15:00', '10:00:00',
       CASE WHEN r.id IN (2, 5, 8, 10) THEN 220 ELSE 180 END,
       r.price, 'scheduled'
FROM routes r
JOIN (
  SELECT ones.n + tens.n * 10 + hundreds.n * 100 AS n
  FROM (SELECT 0 n UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4
        UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9) ones
  CROSS JOIN (SELECT 0 n UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4
        UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9) tens
  CROSS JOIN (SELECT 0 n UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4
        UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9) hundreds
) d ON DATE_ADD(r.start, INTERVAL d.n DAY) <= r.end;

INSERT INTO `booking` (`booking_id`, `booking_reference`, `user_id`, `booking_status`) VALUES
  (1, 'DEMO-1-20261029', 2, 'confirmed'),
  (2, 'DEMO-2-20261103', 6, 'confirmed'),
  (3, 'DEMO-3-20261112', 4, 'confirmed');

INSERT INTO `booking_flights` (`booking_flight_id`, `booking_id`, `flight_id`, `sequence_no`)
SELECT 1, 1, flight_id, 1 FROM flights WHERE route_id = 1 AND flight_date = '2026-10-29'
UNION ALL
SELECT 2, 2, flight_id, 1 FROM flights WHERE route_id = 2 AND flight_date = '2026-11-03'
UNION ALL
SELECT 3, 3, flight_id, 1 FROM flights WHERE route_id = 1 AND flight_date = '2026-11-12';

INSERT INTO `passangers`
  (`id`, `name`, `passport`, `route`, `date`, `registered`, `registeredName`,
   `creditCardno`, `email`, `seatNo`, `flight_id`, `booking_flight_id`) VALUES
  (1, 'Aoife Byrne', '810000002', 1, '2026-10-29', 1, 'aoifebyrne', '0000000000000000',
   'aoife.byrne@example.test', 1, (SELECT flight_id FROM flights WHERE route_id = 1 AND flight_date = '2026-10-29'), 1),
  (2, 'Aoife Byrne', '810000002', 1, '2026-10-29', 1, 'aoifebyrne', '0000000000000000',
   'aoife.byrne@example.test', 2, (SELECT flight_id FROM flights WHERE route_id = 1 AND flight_date = '2026-10-29'), 1),
  (3, 'Cian Murphy', '810000003', 1, '2026-10-29', 1, 'cianmurphy', '0000000000000000',
   'cian.murphy@example.test', 3, (SELECT flight_id FROM flights WHERE route_id = 1 AND flight_date = '2026-10-29'), 1),
  (4, 'Maeve Kelly', '810000004', 1, '2026-11-12', 1, 'maevekelly', '0000000000000000',
   'maeve.kelly@example.test', 1, (SELECT flight_id FROM flights WHERE route_id = 1 AND flight_date = '2026-11-12'), 3),
  (5, 'Nora Doyle', '810000006', 2, '2026-11-03', 1, 'noradoyle', '0000000000000000',
   'nora.doyle@example.test', 1, (SELECT flight_id FROM flights WHERE route_id = 2 AND flight_date = '2026-11-03'), 2),
  (6, 'Finn Ryan', '810000007', 2, '2026-11-03', 1, 'finnryan', '0000000000000000',
   'finn.ryan@example.test', 2, (SELECT flight_id FROM flights WHERE route_id = 2 AND flight_date = '2026-11-03'), 2);

SET FOREIGN_KEY_CHECKS = 1;

-- Verification queries: table totals, seat totals, premium totals, and duplicate-seat check.
SELECT table_name, table_rows
FROM information_schema.tables
WHERE table_schema = 'airline2026'
ORDER BY table_name;
SELECT a.aircraft_code, a.capacity, COUNT(s.aircraft_seat_id) AS seat_total,
       SUM(s.is_premium) AS premium_total
FROM aircraft a LEFT JOIN aircraft_seats s ON s.aircraft_id = a.aircraft_id
GROUP BY a.aircraft_id, a.aircraft_code, a.capacity
ORDER BY a.aircraft_id;
SELECT flight_id, route, date, seatNo, COUNT(*) AS duplicate_count
FROM passangers
GROUP BY flight_id, route, date, seatNo
HAVING COUNT(*) > 1;
