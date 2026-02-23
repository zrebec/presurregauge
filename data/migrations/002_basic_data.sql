/**** SAMPLE DATA FOR New instance of project ****/
INSERT INTO pressuregauge (date, systolicpressure, diastolicpressure, heartrate, spo2, note) VALUES
    ('2025-03-01 12:00', 120, 80, 60, 92, 'Všetko vyplnené') -- full,
    ('2025-03-02 13:01', 130, 77, 60, 89, NULL), -- Without note
    ('2025-04-01 14:02', 96, 65, NULL, 91, NULL), -- without bpm and note,
    ('2025-03-02 13:01', 133, 85, 105, NULL, NULL), -- arrythmia without spo2 and note
    ('2025-03-02 13:01', 111, 69, NULL, NULL, NULL), -- just pressure
    ('2025-03-02 13:01', 121, 76, NULL, NULL, 'Len tlak'); -- only pressure and note

INSERT INTO config (key, value) VALUES ('version', '2.0');
/*alpaca is the seed */
INSERT INTO config (key, value) VALUES ('seed', '8e715bf009320a70f9353613b0550167c2e57ede');