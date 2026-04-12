CREATE DATABASE IF NOT EXISTS ofbms;
USE ofbms;

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(120) NOT NULL,
    email VARCHAR(120) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('admin','passenger','agent') NOT NULL DEFAULT 'passenger',
    loyalty_points INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS airports (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(8) NOT NULL UNIQUE,
    name VARCHAR(120) NOT NULL,
    city VARCHAR(80) NOT NULL,
    country VARCHAR(80) NOT NULL DEFAULT '',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS routes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    origin_airport_id INT NOT NULL,
    destination_airport_id INT NOT NULL,
    flight_no VARCHAR(20) NULL DEFAULT NULL,
    airline VARCHAR(120) NULL DEFAULT NULL,
    base_price DECIMAL(10,2) NULL DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_route (origin_airport_id, destination_airport_id),
    FOREIGN KEY (origin_airport_id) REFERENCES airports(id) ON DELETE RESTRICT,
    FOREIGN KEY (destination_airport_id) REFERENCES airports(id) ON DELETE RESTRICT
);

CREATE TABLE IF NOT EXISTS flights (
    id INT AUTO_INCREMENT PRIMARY KEY,
    route_id INT NULL,
    flight_no VARCHAR(20) NOT NULL UNIQUE,
    origin VARCHAR(80) NOT NULL,
    destination VARCHAR(80) NOT NULL,
    departure_time DATETIME NOT NULL,
    arrival_time DATETIME NOT NULL,
    aircraft VARCHAR(80) NOT NULL,
    capacity INT NOT NULL,
    base_price DECIMAL(10,2) NOT NULL,
    status ENUM('Scheduled','Boarding','Delayed','Cancelled','Completed','Landed') NOT NULL DEFAULT 'Scheduled',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (route_id) REFERENCES routes(id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pnr VARCHAR(20) NOT NULL UNIQUE,
    user_id INT NOT NULL,
    flight_id INT NOT NULL,
    seat_class ENUM('Economy', 'Business', 'FirstClass') NOT NULL DEFAULT 'Economy',
    seats INT NOT NULL DEFAULT 1,
    total_amount DECIMAL(10,2) NOT NULL,
    payment_method ENUM('CreditCard','PayPal','MobileMoney') NOT NULL,
    payment_status ENUM('Pending','Paid','Failed','Refunded') NOT NULL DEFAULT 'Pending',
    status ENUM('Confirmed','Cancelled','Rescheduled') NOT NULL DEFAULT 'Confirmed',
    refund_status ENUM('N/A','Pending','Processed') NOT NULL DEFAULT 'N/A',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (flight_id) REFERENCES flights(id)
);

INSERT INTO users (full_name, email, password_hash, role) VALUES
('Wehliye Admin', 'admin@wehliye.local', '$2y$10$JiwiQTsGg5DAzuvBCoKkmOPnbsiWbZZrNJjZ7ntHrrJ0csEhaAiDq', 'admin'),
('Wehliye Agent', 'agent@wehliye.local', '$2y$10$0qTz0r5JluFsb9SM4G.sOeFEqZ/HBdnQkYCVxS0k7t6c6cHDb2qbe', 'agent'),
('Wehliye Passenger', 'passenger@wehliye.local', '$2y$10$s1XToot1tY/OOIb4JZNHRO9xxWbdDtwDLRC6TxyATXTPms5Q9UP92', 'passenger')
ON DUPLICATE KEY UPDATE email = VALUES(email), password_hash = VALUES(password_hash), full_name = VALUES(full_name);

INSERT INTO airports (code, name, city, country) VALUES
('NBO', 'Jomo Kenyatta International', 'Nairobi', 'Kenya'),
('EBB', 'Entebbe International', 'Kampala', 'Uganda'),
('LOS', 'Murtala Muhammed International', 'Lagos', 'Nigeria'),
('ACC', 'Kotoka International', 'Accra', 'Ghana'),
('CAI', 'Cairo International', 'Cairo', 'Egypt'),
('DXB', 'Dubai International', 'Dubai', 'UAE')
ON DUPLICATE KEY UPDATE name = VALUES(name), city = VALUES(city), country = VALUES(country);

INSERT IGNORE INTO routes (origin_airport_id, destination_airport_id)
SELECT o.id, d.id FROM airports o CROSS JOIN airports d
WHERE (o.code, d.code) IN (('NBO','EBB'),('LOS','ACC'),('CAI','DXB'));

INSERT INTO flights (route_id, flight_no, origin, destination, departure_time, arrival_time, aircraft, capacity, base_price, status)
SELECT r.id, 'OF101', 'Nairobi', 'Kampala', '2026-04-12 08:00:00', '2026-04-12 09:20:00', 'Boeing 737', 180, 120.00, 'Scheduled'
FROM routes r
INNER JOIN airports o ON o.id = r.origin_airport_id AND o.code = 'NBO'
INNER JOIN airports d ON d.id = r.destination_airport_id AND d.code = 'EBB'
LIMIT 1
ON DUPLICATE KEY UPDATE route_id = VALUES(route_id), origin = VALUES(origin), destination = VALUES(destination);

INSERT INTO flights (route_id, flight_no, origin, destination, departure_time, arrival_time, aircraft, capacity, base_price, status)
SELECT r.id, 'OF230', 'Lagos', 'Accra', '2026-04-12 11:30:00', '2026-04-12 12:40:00', 'Airbus A320', 150, 95.00, 'Boarding'
FROM routes r
INNER JOIN airports o ON o.id = r.origin_airport_id AND o.code = 'LOS'
INNER JOIN airports d ON d.id = r.destination_airport_id AND d.code = 'ACC'
LIMIT 1
ON DUPLICATE KEY UPDATE route_id = VALUES(route_id), origin = VALUES(origin), destination = VALUES(destination);

INSERT INTO flights (route_id, flight_no, origin, destination, departure_time, arrival_time, aircraft, capacity, base_price, status)
SELECT r.id, 'OF415', 'Cairo', 'Dubai', '2026-04-13 16:00:00', '2026-04-13 19:10:00', 'Boeing 787', 240, 280.00, 'Scheduled'
FROM routes r
INNER JOIN airports o ON o.id = r.origin_airport_id AND o.code = 'CAI'
INNER JOIN airports d ON d.id = r.destination_airport_id AND d.code = 'DXB'
LIMIT 1
ON DUPLICATE KEY UPDATE route_id = VALUES(route_id), origin = VALUES(origin), destination = VALUES(destination);
