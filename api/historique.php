<?php
/**
 * LabTV - API Historique
 * Recupere l'historique des connexions pour affichage dans l'interface web
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once '../vendor/autoload.php';

use MongoDB\Client;

define('DB_HOST', 'mongodb://localhost:27017');
define('DB_NAME', 'labtv_db');

try {
    $client = new Client(DB_HOST);
    $db = $client->selectDatabase(DB_NAME);
    $historiqueCollection = $db->selectCollection('historique');
} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Connexion MongoDB echouee'
    ]);
    exit;
}

try {
    // Recuperation des 100 dernieres connexions, triees par date decroissante
    $historique = $historiqueCollection->find(
        [],
        [
            'sort' => ['date_connexion' => -1],
            'limit' => 100
        ]
    )->toArray();
    
    $result = [];
    foreach ($historique as $entry) {
        $result[] = [
            'id_salle' => $entry['id_salle'],
            'nom_salle' => $entry['nom_salle'],
            'nom_tv' => $entry['nom_tv'],
            'date_connexion' => $entry['date_connexion'],
            'statut' => $entry['statut']
        ];
    }
    
    echo json_encode($result);
} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Erreur recuperation historique'
    ]);
}
?>