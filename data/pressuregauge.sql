.mode table

DROP TABLE IF EXISTS pressuregauge;
DROP TABLE IF EXISTS config;
    
CREATE TABLE config (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    key TEXT,
    value TEXT
);

CREATE TABLE IF NOT EXISTS pressuregauge (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    systolicpressure INT,
    diastolicpressure INT,
    heartrate INT DEFAULT NULL,
    spo2 INT DEFAULT NULL
);

/*
INSERT INTO pressuregauge (date, systolicpressure, diastolicpressure, heartrate, spo2) VALUES
    ('2025-03-01 12:00', 120, 80, 60, 92),
    ('2025-03-02 13:01', 130, 77, 60, 89),
    ('2025-04-01 14:02', 96, 65, NULL, 60),
    ('2025-03-02 13:01', 133, 85, 53, 87),
    ('2025-03-02 13:01', 111, 69, 85, 94);

INSERT INTO config (key, value) VALUES ('version', '1.1');
INSERT INTO config (key, value) VALUES ('seed', '');
*/

SELECT  strftime('%d. %m. %Y %H:%M:%S', date) 'Date', 
        systolicpressure || '/' || diastolicpressure 'Pressure',
        heartrate 'Heart Rate', spo2 'SpO2'
FROM pressuregauge order by date desc;
