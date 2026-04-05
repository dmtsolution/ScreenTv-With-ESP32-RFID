<?php
/**
 * LabTV - API Badge RFID
 * Recueille l'UID du badge et retourne le nom de la TV associee
 * Endpoint appele par l'ESP32
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once '../vendor/autoload.php';

use MongoDB\Client;

// Configuration de la base de donnees
define('DB_HOST', 'mongodb://localhost:27017');
define('DB_NAME', 'labtv_db');

// Connexion a MongoDB
try {
    $client = new Client(DB_HOST);
    $db = $client->selectDatabase(DB_NAME);
    $sallesCollection = $db->selectCollection('salles');
    $historiqueCollection = $db->selectCollection('historique');
    $erreursCollection = $db->selectCollection('erreurs');
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => 'Connexion MongoDB echouee'
    ]);
    exit;
}

// Verification du parametre UID
if (!isset($_GET['uid']) || empty($_GET['uid'])) {
    echo json_encode([
        'success' => false,
        'error' => 'UID manquant'
    ]);
    exit;
}

$uid = trim($_GET['uid']);
date_default_timezone_set('Africa/Casablanca');
$now = date('Y-m-d H:i:s');

// Recherche de la salle correspondant a l'UID
$salle = $sallesCollection->findOne(['uid_badge' => $uid]);

if ($salle) {
    // Badge reconnu - Enregistrement dans l'historique
    $historiqueCollection->insertOne([
        'id_salle' => $salle['id'],
        'nom_salle' => $salle['nom_salle'],
        'nom_tv' => $salle['nom_tv'],
        'uid_badge' => $uid,
        'date_connexion' => $now,
        'statut' => 'success'
    ]);
    
    // Appel au script Python via UDP (optionnel, pour compatibilite)
    // Le script Python original ecoute sur UDP port 5005
    $socket = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
    if ($socket) {
        $tvName = $salle['nom_tv'];
        socket_sendto($socket, $tvName, strlen($tvName), 0, '127.0.0.1', 5005);
        socket_close($socket);
    }
    
    // Reponse positive a l'ESP32
    echo json_encode([
        'success' => true,
        'tv_name' => $salle['nom_tv'],
        'salle' => $salle['nom_salle']
    ]);
} else {
    // Badge non reconnu - Enregistrement de l'erreur
    $erreursCollection->insertOne([
        'uid_badge' => $uid,
        'date_erreur' => $now,
        'type' => 'badge_non_reconnu'
    ]);
    
    // Reponse negative a l'ESP32
    echo json_encode([
        'success' => false,
        'error' => 'Badge non reconnu'
    ]);
}
?>