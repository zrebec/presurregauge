.mode table

CREATE TABLE IF NOT EXISTS config (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    key TEXT,
    value TEXT
);

CREATE TABLE IF NOT EXISTS pressuregauge (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    systolicpressure INT,
    diastolicpressure INT,
    heartrate INT DEFAULT NULL
);

/*
INSERT INTO pressuregauge (date, systolicpressure, diastolicpressure, heartrate) VALUES
    ('2025-03-01 12:00', 120, 80, 60),
    ('2025-03-02 13:01', 130, 77, 60),
    ('2025-04-01 14:02', 96, 65, NULL);

INSERT INTO config (key, value) VALUES ('version', '1.0');
INSERT INTO config (key, value) VALUES ('seed', '');
*/

SELECT  strftime('%d. %m. %Y %H:%M:%S', date) 'Date', 
        systolicpressure || '/' || diastolicpressure 'Pressure',
        heartrate 'Heart Rate'
FROM pressuregauge order by date desc;
