-- Run in phpMyAdmin on database `ofbms` if demo logins fail (fixes bcrypt for README passwords).
USE ofbms;

UPDATE users SET password_hash = '$2y$10$JiwiQTsGg5DAzuvBCoKkmOPnbsiWbZZrNJjZ7ntHrrJ0csEhaAiDq' WHERE email = 'admin@wehliye.local';
UPDATE users SET password_hash = '$2y$10$0qTz0r5JluFsb9SM4G.sOeFEqZ/HBdnQkYCVxS0k7t6c6cHDb2qbe' WHERE email = 'agent@wehliye.local';
UPDATE users SET password_hash = '$2y$10$s1XToot1tY/OOIb4JZNHRO9xxWbdDtwDLRC6TxyATXTPms5Q9UP92' WHERE email = 'passenger@wehliye.local';
