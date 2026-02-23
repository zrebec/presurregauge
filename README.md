# Pressure Gauge Application

## User guide
### Introduction

Monitoring blood pressure is a critical aspect of maintaining overall health and well-being. High blood pressure (hypertension) is a significant risk factor for heart disease, stroke, kidney disease, and other serious health conditions. Regular blood pressure monitoring allows individuals and healthcare providers to track trends, identify potential problems early, and make informed decisions about lifestyle changes or medical interventions. This application, the Pressure Gauge, is designed to simplify the process of recording and tracking your blood pressure readings.

### Features

The Pressure Gauge application allows you to record the following information:

*   **Date:** The date the blood pressure was measured. Defaults to the current date, but can be adjusted.
*   **Time:** The time the blood pressure was measured. Defaults to the current time, but can be adjusted.
*   **Systolic Pressure:** The systolic pressure reading (the higher number). This field is mandatory.
*   **Diastolic Pressure:** The diastolic pressure reading (the lower number). This field is mandatory.
*   **Heart Rate:** The heart rate (pulse) at the time of measurement. This field is optional.
*   **SpO2:** The bloon oxygen (SpO2) at the time of measurement. This field is optional.
*   **Note:** Free fild for your note (after excersise, watching TV, work with computer, walk with dogs, i feeel arrythmia)

### Usage

1.  Open the application in your web browser.
2.  The Date and Time fields will be pre-populated with the current date and time. You can adjust these values if needed.
3.  Enter your Systolic Pressure reading in the corresponding field.
4.  Enter your Diastolic Pressure reading in the corresponding field.
5.  If you know your heart rate, enter it in the Heart Rate field.
6.  If you know your SpO2, enter it in the same name field.
7.  Feel free if you want some note or not.
8.  Click the "Submit" button to record your readings.

### Important Disclaimer

This application is intended for personal record-keeping purposes only and should not be used as a substitute for professional medical advice. Always consult with a qualified healthcare provider for any health concerns or before making any decisions related to your health or treatment.

## Tech Stack

### Features

#### Frondtend and application
- Record systolic/diastolic pressure, heart rate, SpO2, and notes
- Auto-populated date and time (live clock, adjustable before saving)
- Chronological measurement history
- Mobile-optimized responsive design
- PWA support for home screen installation
- SQLite database (zero external dependencies)
- Simple seed-based write protection

#### Backend
- **Backend:** PHP 8+ with Twig templating
- **Database:** SQLite 3 (via PDO)
- **Frontend:** Vanilla HTML/CSS/JS
- **PWA:** Service worker with cache-first strategy

### Requirements

- PHP 8.0+
- Composer
- A web server (Apache/Nginx) with PHP support
- SQLite3 PHP extension (usually bundled)

### Installation

```bash
git clone https://github.com/zrebec/presurregauge.git
cd presurregauge
composer install
```

Configure your web server to point to the project root. The SQLite database will be created automatically on first run in the `data/` directory.

#### Seed Configuration

The app uses a SHA-1 hashed seed to protect form submissions. To set it up:

1. Open the database with any SQLite client
2. Insert your seed hash: `INSERT INTO config (key, value) VALUES ('seed', '<sha1_hash_of_your_password>');`
3. Use the plaintext password in the Seed field when submitting measurements

Without a configured seed, form submissions will be silently rejected.

### Database Schema

```sql
pressuregauge (
    id              INTEGER PRIMARY KEY AUTOINCREMENT,
    date            TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    systolicpressure INT NOT NULL,
    diastolicpressure INT NOT NULL,
    heartrate       INT DEFAULT NULL,
    spo2            INT DEFAULT NULL,
    note            TEXT DEFAULT NULL
)
```

A reference SQL file is available in `data/pressuregauge.sql` with the full schema and sample data.

### Project Structure

```
├── index.php              # Main application logic (routing, DB, rendering)
├── templates/
│   └── index.twig         # HTML template
├── style.css              # Responsive styles
├── sw.js                  # Service worker for PWA/caching
├── manifest.json          # PWA manifest
├── data/
│   └── pressuregauge.sql  # Reference schema and sample queries
├── composer.json           
└── favicon.*              # App icons (SVG, ICO, PNG variants)
```

## Roadmap

- [ ] SPA-style tab navigation (form / history / charts)
- [ ] Trend charts with Chart.js
- [ ] Predefined note tags (emoji-based quick select)
- [ ] CSV export for doctor visits
- [ ] Measurement editing and deletion
- [ ] Configurable alert thresholds
- [ ] Service worker cache invalidation strategy

## License

Personal project. No license specified.