-- Upewnienie się, że baza danych testowa istnieje
CREATE DATABASE IF NOT EXISTS mydatabase_test;

-- Nadanie użytkownikowi 'user' wszystkich uprawnień do bazy testowej
-- Użytkownik 'user' i hasło 'pass' są zdefiniowane przez MYSQL_USER/MYSQL_PASSWORD w docker-compose
GRANT ALL PRIVILEGES ON mydatabase_test.* TO 'user'@'%';

-- Zastosowanie zmian uprawnień
FLUSH PRIVILEGES;