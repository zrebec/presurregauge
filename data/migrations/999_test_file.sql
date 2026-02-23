SELECT  strftime('%d. %m. %Y %H:%M:%S', date) 'Date', 
        systolicpressure || '/' || diastolicpressure 'Pressure',
        heartrate 'Heart Rate', spo2 'SpO2',
        note 'Note'
FROM pressuregauge order by date desc;