DROP TABLE IF EXISTS pressuregauge;
DROP TABLE IF EXISTS config;

CREATE TABLE config (
    key TEXT PRIMARY KEY,
    value TEXT
);

CREATE TABLE IF NOT EXISTS pressuregauge (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    systolicpressure INT,
    diastolicpressure INT,
    heartrate INT DEFAULT NULL,
    spo2 INT DEFAULT NULL,
    note TEXT DEFAULT NULL
);