-- =============================================================================
-- airline2026.sql
-- Test data refresh for the legacy Airline-2014 PHP application
-- Database: airline2026
--
-- WARNING: Re-importing this script will DROP and recreate the four application
-- tables (login, routes, user, passangers) inside the airline2026 database.
-- It does NOT touch any other database (including airline2013).
--
-- Administrator test credentials (present in both login and user tables):
--   Username: rmiller
--   Password: rmiller
--
-- Additional login accounts:
--   opsdesk / desk2026
--   routelead / route2026
--
-- Sample customer accounts (see comments near the INSERT statements):
--   jmurphy / pass1234
--   skelly / skyfly26
--   etc.
-- =============================================================================

CREATE DATABASE IF NOT EXISTS airline2026
  DEFAULT CHARACTER SET utf8
  DEFAULT COLLATE utf8_general_ci;

USE airline2026;

-- Drop tables in safe order (passangers first because of logical dependency)
DROP TABLE IF EXISTS `passangers`;
DROP TABLE IF EXISTS `routes`;
DROP TABLE IF EXISTS `user`;
DROP TABLE IF EXISTS `login`;

-- -----------------------------------------------------------------------------
-- Table: login
-- -----------------------------------------------------------------------------
CREATE TABLE `login` (
  `username` VARCHAR(20) NOT NULL,
  `password` VARCHAR(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- -----------------------------------------------------------------------------
-- Table: routes
-- -----------------------------------------------------------------------------
CREATE TABLE `routes` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `source` VARCHAR(20) DEFAULT NULL,
  `dest` VARCHAR(20) DEFAULT NULL,
  `start` DATE DEFAULT NULL,
  `end` DATE DEFAULT NULL,
  `price` INT DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- -----------------------------------------------------------------------------
-- Table: user
-- -----------------------------------------------------------------------------
CREATE TABLE `user` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `username` VARCHAR(15) DEFAULT NULL,
  `password` VARCHAR(15) DEFAULT NULL,
  `email` VARCHAR(40) DEFAULT NULL,
  `CreditCardNo` VARCHAR(16) DEFAULT NULL,
  `passport` INT(10) DEFAULT NULL,
  `suspend` TINYINT(1) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- -----------------------------------------------------------------------------
-- Table: passangers  (original spelling preserved)
-- -----------------------------------------------------------------------------
CREATE TABLE `passangers` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(20) DEFAULT NULL,
  `passport` VARCHAR(10) DEFAULT NULL,
  `route` INT DEFAULT NULL,
  `date` DATE DEFAULT NULL,
  `registered` TINYINT(1) DEFAULT NULL,
  `registeredName` VARCHAR(15) DEFAULT NULL,
  `creditCardno` VARCHAR(16) DEFAULT NULL,
  `email` VARCHAR(40) DEFAULT NULL,
  `seatNo` INT DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- =============================================================================
-- DATA: login accounts
-- =============================================================================
INSERT INTO `login` (`username`, `password`) VALUES
('rmiller', 'rmiller'),
('opsdesk', 'desk2026'),
('routelead', 'route2026');

-- =============================================================================
-- DATA: routes (22 routes – Domestic Ireland, UK/Europe, North America)
-- Dates cover late 2026 through mid-2027
-- =============================================================================
INSERT INTO `routes` (`id`, `source`, `dest`, `start`, `end`, `price`) VALUES
-- Domestic Ireland (approx €45–€85)
(1,  'Dublin',   'Cork',      '2026-11-01', '2027-05-31', 59),
(2,  'Dublin',   'Shannon',   '2026-11-01', '2027-05-31', 65),
(3,  'Dublin',   'Knock',     '2026-11-15', '2027-04-30', 55),
(4,  'Cork',     'Dublin',    '2026-11-01', '2027-05-31', 59),
(5,  'Shannon',  'Dublin',    '2026-11-01', '2027-05-31', 65),
(6,  'Knock',    'Dublin',    '2026-11-15', '2027-04-30', 55),

-- UK and Europe (approx €70–€180)
(7,  'Dublin',   'London',    '2026-11-01', '2027-06-30', 89),
(8,  'Dublin',   'Paris',     '2026-11-10', '2027-06-15', 119),
(9,  'Dublin',   'Amsterdam', '2026-11-05', '2027-05-31', 105),
(10, 'Dublin',   'Madrid',    '2026-12-01', '2027-06-30', 145),
(11, 'Dublin',   'Berlin',    '2026-11-20', '2027-05-20', 135),
(12, 'Cork',     'Rome',      '2026-12-01', '2027-04-30', 165),
(13, 'London',   'Dublin',    '2026-11-01', '2027-06-30', 89),
(14, 'Paris',    'Dublin',    '2026-11-10', '2027-06-15', 119),
(15, 'Amsterdam','Dublin',    '2026-11-05', '2027-05-31', 105),

-- North America (approx €250–€650)
(16, 'Dublin',   'New York',  '2026-11-15', '2027-06-30', 420),
(17, 'Dublin',   'Boston',    '2026-12-01', '2027-05-31', 390),
(18, 'Dublin',   'Chicago',   '2026-12-10', '2027-06-15', 455),
(19, 'Dublin',   'Los Angeles','2027-01-05', '2027-06-30', 580),
(20, 'Dublin',   'Toronto',   '2026-11-20', '2027-05-31', 375),
(21, 'Shannon',  'New York',  '2026-12-01', '2027-06-15', 410),
(22, 'Shannon',  'Boston',    '2027-01-10', '2027-05-20', 385);

-- =============================================================================
-- DATA: user accounts
-- rmiller (admin) + 15 fictional customers
-- =============================================================================
INSERT INTO `user` (`id`, `username`, `password`, `email`, `CreditCardNo`, `passport`, `suspend`) VALUES
(1,  'rmiller',    'rmiller',    'rmiller@airline.test',      '0000000000000000', 1000000001, 0),
(2,  'jmurphy',    'pass1234',   'jmurphy@example.test',      '0000000000001111', 1000000002, 0),
(3,  'skelly',     'skyfly26',   'skelly@example.test',       '0000000000002222', 1000000003, 0),
(4,  'aobrien',    'aob2026',    'aobrien@example.test',      '0000000000003333', 1000000004, 0),
(5,  'cmacdonald', 'cmacpass',   'cmacdonald@example.test',   '0000000000004444', 1000000005, 0),
(6,  'pdoyle',     'pdoyle99',   'pdoyle@example.test',       '0000000000005555', 1000000006, 0),
(7,  'eflanagan',  'eflan26',    'eflanagan@example.test',    '0000000000006666', 1000000007, 0),
(8,  'tbrown',     'tbrown77',   'tbrown@example.test',       '0000000000007777', 1000000008, 0),
(9,  'lwalsh',     'lwalsh12',   'lwalsh@example.test',       '0000000000008888', 1000000009, 1),  -- suspended
(10, 'mryan',      'mryan2026',  'mryan@example.test',        '0000000000009999', 1000000010, 0),
(11, 'ckelly',     'ckelly88',   'ckelly@example.test',       '0000000000010000', 1000000011, 0),
(12, 'nbyrne',     'nbyrne55',   'nbyrne@example.test',       '0000000000011111', 1000000012, 0),
(13, 'sfox',       'sfoxpass',   'sfox@example.test',         '0000000000012222', 1000000013, 0),
(14, 'hquinn',     'hquinn33',   'hquinn@example.test',       '0000000000013333', 1000000014, 0),
(15, 'dcasey',     'dcasey26',   'dcasey@example.test',       '0000000000014444', 1000000015, 0),
(16, 'rpower',     'rpower99',   'rpower@example.test',       '0000000000015555', 1000000016, 0);

-- =============================================================================
-- DATA: passangers (42 bookings)
-- Mix of registered (registered=1) and guest (registered=0, registeredName='Guest')
-- Seat numbers 1-20 only; never more than 20 per route+date; no duplicate seats
-- All booking dates fall inside the corresponding route start/end window
-- =============================================================================
INSERT INTO `passangers` (`id`, `name`, `passport`, `route`, `date`, `registered`, `registeredName`, `creditCardno`, `email`, `seatNo`) VALUES
-- Route 1 Dublin-Cork 2026-12-05 (3 passengers)
(1,  'John Murphy',     'P1000002', 1,  '2026-12-05', 1, 'jmurphy',    '0000000000001111', 'jmurphy@example.test',     1),
(2,  'Sarah Kelly',     'P1000003', 1,  '2026-12-05', 1, 'skelly',     '0000000000002222', 'skelly@example.test',      2),
(3,  'Guest Traveller', 'G9000001', 1,  '2026-12-05', 0, 'Guest',      '0000000000090001', 'guest1@example.test',      3),

-- Route 7 Dublin-London 2026-12-12 (4 passengers)
(4,  'Aoife OBrien',    'P1000004', 7,  '2026-12-12', 1, 'aobrien',    '0000000000003333', 'aobrien@example.test',     1),
(5,  'Conor Macdonald', 'P1000005', 7,  '2026-12-12', 1, 'cmacdonald', '0000000000004444', 'cmacdonald@example.test',  2),
(6,  'Patrick Doyle',   'P1000006', 7,  '2026-12-12', 1, 'pdoyle',     '0000000000005555', 'pdoyle@example.test',      3),
(7,  'Guest Flyer',     'G9000002', 7,  '2026-12-12', 0, 'Guest',      '0000000000090002', 'guest2@example.test',      4),

-- Route 16 Dublin-New York 2027-01-20 (3 passengers)
(8,  'Emma Flanagan',   'P1000007', 16, '2027-01-20', 1, 'eflanagan',  '0000000000006666', 'eflanagan@example.test',   1),
(9,  'Tom Brown',       'P1000008', 16, '2027-01-20', 1, 'tbrown',     '0000000000007777', 'tbrown@example.test',      2),
(10, 'Guest Passenger', 'G9000003', 16, '2027-01-20', 0, 'Guest',      '0000000000090003', 'guest3@example.test',      5),

-- Route 8 Dublin-Paris 2027-02-14 (2 passengers)
(11, 'Michael Ryan',    'P1000010', 8,  '2027-02-14', 1, 'mryan',      '0000000000009999', 'mryan@example.test',       1),
(12, 'Ciara Kelly',     'P1000011', 8,  '2027-02-14', 1, 'ckelly',     '0000000000010000', 'ckelly@example.test',      2),

-- Route 2 Dublin-Shannon 2026-11-28 (2 passengers)
(13, 'Niamh Byrne',     'P1000012', 2,  '2026-11-28', 1, 'nbyrne',     '0000000000011111', 'nbyrne@example.test',      1),
(14, 'Guest Traveller', 'G9000004', 2,  '2026-11-28', 0, 'Guest',      '0000000000090004', 'guest4@example.test',      2),

-- Route 17 Dublin-Boston 2027-03-05 (3 passengers)
(15, 'Sean Fox',        'P1000013', 17, '2027-03-05', 1, 'sfox',       '0000000000012222', 'sfox@example.test',        1),
(16, 'Hannah Quinn',    'P1000014', 17, '2027-03-05', 1, 'hquinn',     '0000000000013333', 'hquinn@example.test',      2),
(17, 'David Casey',     'P1000015', 17, '2027-03-05', 1, 'dcasey',     '0000000000014444', 'dcasey@example.test',      3),

-- Route 4 Cork-Dublin 2026-12-18 (2 passengers)
(18, 'Rory Power',      'P1000016', 4,  '2026-12-18', 1, 'rpower',     '0000000000015555', 'rpower@example.test',      1),
(19, 'John Murphy',     'P1000002', 4,  '2026-12-18', 1, 'jmurphy',    '0000000000001111', 'jmurphy@example.test',     2),

-- Route 9 Dublin-Amsterdam 2027-01-10 (3 passengers)
(20, 'Sarah Kelly',     'P1000003', 9,  '2027-01-10', 1, 'skelly',     '0000000000002222', 'skelly@example.test',      1),
(21, 'Aoife OBrien',    'P1000004', 9,  '2027-01-10', 1, 'aobrien',    '0000000000003333', 'aobrien@example.test',     2),
(22, 'Guest Flyer',     'G9000005', 9,  '2027-01-10', 0, 'Guest',      '0000000000090005', 'guest5@example.test',      3),

-- Route 13 London-Dublin 2027-02-20 (2 passengers)
(23, 'Conor Macdonald', 'P1000005', 13, '2027-02-20', 1, 'cmacdonald', '0000000000004444', 'cmacdonald@example.test',  1),
(24, 'Patrick Doyle',   'P1000006', 13, '2027-02-20', 1, 'pdoyle',     '0000000000005555', 'pdoyle@example.test',      2),

-- Route 20 Dublin-Toronto 2027-04-02 (3 passengers)
(25, 'Emma Flanagan',   'P1000007', 20, '2027-04-02', 1, 'eflanagan',  '0000000000006666', 'eflanagan@example.test',   1),
(26, 'Tom Brown',       'P1000008', 20, '2027-04-02', 1, 'tbrown',     '0000000000007777', 'tbrown@example.test',      2),
(27, 'Guest Traveller', 'G9000006', 20, '2027-04-02', 0, 'Guest',      '0000000000090006', 'guest6@example.test',      4),

-- Route 3 Dublin-Knock 2027-03-12 (2 passengers)
(28, 'Michael Ryan',    'P1000010', 3,  '2027-03-12', 1, 'mryan',      '0000000000009999', 'mryan@example.test',       1),
(29, 'Ciara Kelly',     'P1000011', 3,  '2027-03-12', 1, 'ckelly',     '0000000000010000', 'ckelly@example.test',      2),

-- Route 21 Shannon-New York 2027-02-28 (3 passengers)
(30, 'Niamh Byrne',     'P1000012', 21, '2027-02-28', 1, 'nbyrne',     '0000000000011111', 'nbyrne@example.test',      1),
(31, 'Sean Fox',        'P1000013', 21, '2027-02-28', 1, 'sfox',       '0000000000012222', 'sfox@example.test',        2),
(32, 'Guest Passenger', 'G9000007', 21, '2027-02-28', 0, 'Guest',      '0000000000090007', 'guest7@example.test',      3),

-- Route 10 Dublin-Madrid 2027-05-10 (2 passengers)
(33, 'Hannah Quinn',    'P1000014', 10, '2027-05-10', 1, 'hquinn',     '0000000000013333', 'hquinn@example.test',      1),
(34, 'David Casey',     'P1000015', 10, '2027-05-10', 1, 'dcasey',     '0000000000014444', 'dcasey@example.test',      2),

-- Route 12 Cork-Rome 2027-03-22 (2 passengers)
(35, 'Rory Power',      'P1000016', 12, '2027-03-22', 1, 'rpower',     '0000000000015555', 'rpower@example.test',      1),
(36, 'John Murphy',     'P1000002', 12, '2027-03-22', 1, 'jmurphy',    '0000000000001111', 'jmurphy@example.test',     2),

-- Route 18 Dublin-Chicago 2027-04-18 (3 passengers)
(37, 'Sarah Kelly',     'P1000003', 18, '2027-04-18', 1, 'skelly',     '0000000000002222', 'skelly@example.test',      1),
(38, 'Aoife OBrien',    'P1000004', 18, '2027-04-18', 1, 'aobrien',    '0000000000003333', 'aobrien@example.test',     2),
(39, 'Guest Flyer',     'G9000008', 18, '2027-04-18', 0, 'Guest',      '0000000000090008', 'guest8@example.test',      3),

-- Route 5 Shannon-Dublin 2027-01-15 (2 passengers)
(40, 'Conor Macdonald', 'P1000005', 5,  '2027-01-15', 1, 'cmacdonald', '0000000000004444', 'cmacdonald@example.test',  1),
(41, 'Patrick Doyle',   'P1000006', 5,  '2027-01-15', 1, 'pdoyle',     '0000000000005555', 'pdoyle@example.test',      2),

-- Route 19 Dublin-Los Angeles 2027-05-25 (1 passenger)
(42, 'Emma Flanagan',   'P1000007', 19, '2027-05-25', 1, 'eflanagan',  '0000000000006666', 'eflanagan@example.test',   1);

-- =============================================================================
-- End of airline2026.sql
-- Summary:
--   Routes          : 22
--   User accounts   : 16 (including rmiller)
--   Login accounts  : 3
--   Passenger bookings: 42
-- Administrator: rmiller / rmiller
-- =============================================================================
