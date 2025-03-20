.mode table

DROP TABLE IF EXISTS pressuregauge;

CREATE TABLE pressuregauge (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    systolicpressure INT,
    diastolicpressure INT,
    heartrate INT DEFAULT NULL
);

INSERT INTO pressuregauge (date, systolicpressure, diastolicpressure, heartrate) VALUES
    ('2025-03-01 12:00', 120, 80, 60),
    ('2025-03-02 13:01', 130, 77, 60),
    ('2025-04-01 14:02', 96, 65, NULL);

SELECT  strftime('%d. %m. %Y %H:%M:%S', date) 'Date', 
        systolicpressure || '/' || diastolicpressure 'Pressure',
        heartrate 'Heart Rate'
FROM pressuregauge order by date desc;
