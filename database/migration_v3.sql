-- OFBMS v3: route defaults (flight number, airline, base price) for admin workflow
-- Run: mysql -u root ofbms < migration_v3.sql

USE ofbms;

ALTER TABLE routes ADD COLUMN flight_no VARCHAR(20) NULL DEFAULT NULL;
ALTER TABLE routes ADD COLUMN airline VARCHAR(120) NULL DEFAULT NULL;
ALTER TABLE routes ADD COLUMN base_price DECIMAL(10,2) NULL DEFAULT NULL;
