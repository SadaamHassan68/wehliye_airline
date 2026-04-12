-- Wehliye OFBMS v2: airports, routes, flight.route_id, status includes Landed
-- Run: mysql -u root ofbms < migration_v2.sql
-- If a step errors (column already exists), continue with the next statements.

USE ofbms;

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
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_route (origin_airport_id, destination_airport_id),
    FOREIGN KEY (origin_airport_id) REFERENCES airports(id) ON DELETE RESTRICT,
    FOREIGN KEY (destination_airport_id) REFERENCES airports(id) ON DELETE RESTRICT
);

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

ALTER TABLE flights ADD COLUMN route_id INT NULL AFTER id;

ALTER TABLE flights ADD CONSTRAINT fk_flights_route FOREIGN KEY (route_id) REFERENCES routes(id) ON DELETE SET NULL;

UPDATE flights f
INNER JOIN airports o ON (o.city = f.origin OR o.name = f.origin)
INNER JOIN airports d ON (d.city = f.destination OR d.name = f.destination)
INNER JOIN routes r ON r.origin_airport_id = o.id AND r.destination_airport_id = d.id
SET f.route_id = r.id
WHERE f.route_id IS NULL;

ALTER TABLE flights MODIFY COLUMN status ENUM('Scheduled','Boarding','Delayed','Cancelled','Completed','Landed') NOT NULL DEFAULT 'Scheduled';
