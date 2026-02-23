<?php
// Autoload packages
require 'vendor/autoload.php';

// Display errors
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Start the session
session_start();

// Set the content type
header("Cache-Control: no-cache, must-revalidate");
header("Content-Type: text/html; charset=utf-8");

// Import Twig
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

// Inicializácia Twig
$loader = new FilesystemLoader('templates');
$twig = new Environment($loader);
$seed = '';

// Connect to the database using PDO
try {
    if (is_dir('../../database')) {
        $db = new PDO('sqlite:' . '../../database/pressuregauge.db');
    } else {
        $db = new PDO('sqlite:' . __DIR__ . '/data/pressuregauge.db');
    }
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $seed = $db->query("SELECT value FROM config where key = 'seed'")->fetchColumn();
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Handle the form submission  
$success_message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && sha1($_POST['seed']) === $seed && !$_GET['saved']) {
    $date = $_POST['date'];
    $time = $_POST['time'];
    $timestamp = date('Y-m-d H:i:s', strtotime($date . ' ' . $time));
    $systolicpressure = $_POST['systolicpressure'];
    $diastolicpressure = $_POST['diastolicpressure'];
    $heartrate = !empty($_POST['heartrate']) ? $_POST['heartrate'] : null;
    $spo2 = !empty($_POST['spo2']) ? $_POST['spo2'] : null;
    $note = !empty($_POST['note']) ? $_POST['note'] : null;

    try {
        // Insert the data using PDO prepared statement
        $stmt = $db->prepare('INSERT INTO pressuregauge (date, systolicpressure, diastolicpressure, heartrate, spo2, note) 
                            VALUES (:date, :systolicpressure, :diastolicpressure, :heartrate, :spo2, :note)');
        
        $stmt->bindParam(':date', $timestamp);
        $stmt->bindParam(':systolicpressure', $systolicpressure);
        $stmt->bindParam(':diastolicpressure', $diastolicpressure);
        $stmt->bindParam(':heartrate', $heartrate);
        $stmt->bindParam(':spo2', $spo2);
        $stmt->bindParam(':note', $note);
        $stmt->execute();
        // TODO: Can we check the sucessuflly save?

        // Redirect after successfull save
        header('Location: index.php?saved=1');
        // TODO: Is exit needed after header('Location')?
        exit;
    } catch(PDOException $e) {
        die("Error: " . $e->getMessage());
    }
} else if ($_GET['saved']) {
    // TODO: Is the ELSE -IF condition right?
    if($_GET['saved']) $success_message = "Pressure gauge data has been saved.";
}

// Get all records from the database
try {
    $query = $db->query("SELECT id, date, systolicpressure, diastolicpressure, heartrate, spo2, note FROM pressuregauge ORDER BY date DESC");
    $measurements = $query->fetchAll(PDO::FETCH_ASSOC);
    
    // Format dates
    foreach ($measurements as &$measurement) {
        $date = new DateTime($measurement['date']);
        $measurement['formatted_date'] = $date->format('d. m. Y H:i:s');
    }
} catch(PDOException $e) {
    die("Error: " . $e->getMessage());
}

// Aktuálny čas s presnosťou na sekundy
$current_time = date('H:i:s');

// Render the template with Twig
echo $twig->render('index.twig', [
    'measurements' => $measurements,
    'current_date' => date('Y-m-d'),
    'current_time' => $current_time,
    'success_message' => $success_message
]);
