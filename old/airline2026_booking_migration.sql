
-- Airline2026: additive booking schema migration
-- Target database: airline2026
-- Take a database backup before applying.
-- Run once, after confirming these new tables do not already exist.

USE airline2026;

-- 1. Individual scheduled departures

CREATE TABLE flights (
    flight_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    route_id INT(11) NOT NULL,
    flight_date DATE NOT NULL,
    departure_time TIME DEFAULT NULL,
    arrival_time TIME DEFAULT NULL,
    capacity SMALLINT UNSIGNED NOT NULL DEFAULT 20,
    price INT(11) NOT NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'scheduled',

    PRIMARY KEY (flight_id),
    INDEX idx_flights_route_date (route_id, flight_date),

    CONSTRAINT fk_flights_route
        FOREIGN KEY (route_id)
        REFERENCES routes (id)
        ON DELETE RESTRICT
        ON UPDATE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


-- 2. One record for the complete booking.
-- user_id is NULL for guest bookings.

CREATE TABLE booking (
    booking_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    booking_reference VARCHAR(30) NOT NULL,
    user_id INT(11) DEFAULT NULL,
    booking_status VARCHAR(20) NOT NULL DEFAULT 'pending',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (booking_id),
    UNIQUE KEY uq_booking_reference (booking_reference),
    INDEX idx_booking_user (user_id),

    CONSTRAINT fk_booking_user
        FOREIGN KEY (user_id)
        REFERENCES `user` (id)
        ON DELETE RESTRICT
        ON UPDATE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


-- 3. Ordered flight legs belonging to each booking

CREATE TABLE booking_flights (
    booking_flight_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    booking_id BIGINT UNSIGNED NOT NULL,
    flight_id BIGINT UNSIGNED NOT NULL,
    sequence_no SMALLINT UNSIGNED NOT NULL,

    PRIMARY KEY (booking_flight_id),
    UNIQUE KEY uq_booking_sequence (booking_id, sequence_no),
    INDEX idx_booking_flights_flight (flight_id),

    CONSTRAINT fk_booking_flights_booking
        FOREIGN KEY (booking_id)
        REFERENCES booking (booking_id)
        ON DELETE RESTRICT
        ON UPDATE RESTRICT,

    CONSTRAINT fk_booking_flights_flight
        FOREIGN KEY (flight_id)
        REFERENCES flights (flight_id)
        ON DELETE RESTRICT
        ON UPDATE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


-- 4. Link existing passenger/seat records to a booking leg.
-- Existing rows receive NULL and are retained.

ALTER TABLE passangers
    ADD COLUMN booking_flight_id BIGINT UNSIGNED DEFAULT NULL,
    ADD INDEX idx_passangers_booking_flight (booking_flight_id),
    ADD CONSTRAINT fk_passangers_booking_flight
        FOREIGN KEY (booking_flight_id)
        REFERENCES booking_flights (booking_flight_id)
        ON DELETE RESTRICT
        ON UPDATE RESTRICT;
