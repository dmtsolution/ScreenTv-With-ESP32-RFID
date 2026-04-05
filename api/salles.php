<?php
/**
 * LabTV - API Gestion des Salles
 * Operations CRUD pour les salles et leurs badges RFID associes
 * Methodes supportees: GET, POST, PUT, DELETE
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require_once '../vendor/autoload.php';

use MongoDB\Client;

define('DB_HOST', 'mongodb://localhost:27017');
define('DB_NAME', 'labtv_db');

// Connexion a MongoDB
try {
    $client = new Client(DB_HOST);
    $db = $client->selectDatabase(DB_NAME);
    $sallesCollection = $db->selectCollection('salles');
} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Connexion MongoDB echouee'
    ]);
    exit;
}

date_default_timezone_set('Africa/Casablanca');
$method = $_SERVER['REQUEST_METHOD'];

// GET - Recuperer toutes les salles
if ($method === 'GET') {
    try {
        $salles = $sallesCollection->find()->toArray();
        $result = [];
        
        foreach ($salles as $salle) {
            $result[] = [
                'id' => $salle['id'],
                'nom_salle' => $salle['nom_salle'],
                'nom_tv' => $salle['nom_tv'],
                'uid_badge' => $salle['uid_badge'],
                'date_creation' => $salle['date_creation']
            ];
        }
        
        echo json_encode($result);
    } catch (Exception $e) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Erreur recuperation des salles'
        ]);
    }
    exit;
}

// POST - Ajouter une nouvelle salle
if ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    // Validation des champs obligatoires
    if (!isset($input['nom_salle']) || empty($input['nom_salle'])) {
        echo json_encode(['status' => 'error', 'message' => 'Nom salle manquant']);
        exit;
    }
    
    if (!isset($input['nom_tv']) || empty($input['nom_tv'])) {
        echo json_encode(['status' => 'error', 'message' => 'Nom TV manquant']);
        exit;
    }
    
    if (!isset($input['uid_badge']) || empty($input['uid_badge'])) {
        echo json_encode(['status' => 'error', 'message' => 'UID badge manquant']);
        exit;
    }
    
    // Verification UID unique
    $existing = $sallesCollection->findOne(['uid_badge' => $input['uid_badge']]);
    if ($existing) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Cet UID badge est deja associe a une salle'
        ]);
        exit;
    }
    
    // Calcul du prochain ID
    $lastSalle = $sallesCollection->findOne([], ['sort' => ['id' => -1]]);
    $nextId = $lastSalle ? $lastSalle['id'] + 1 : 1;
    
    // Insertion de la nouvelle salle
    try {
        $sallesCollection->insertOne([
            'id' => $nextId,
            'nom_salle' => $input['nom_salle'],
            'nom_tv' => $input['nom_tv'],
            'uid_badge' => $input['uid_badge'],
            'date_creation' => date('Y-m-d H:i:s')
        ]);
        
        echo json_encode([
            'status' => 'success',
            'id' => $nextId,
            'message' => 'Salle ajoutee avec succes'
        ]);
    } catch (Exception $e) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Erreur insertion: ' . $e->getMessage()
        ]);
    }
    exit;
}

// PUT - Modifier une salle existante
if ($method === 'PUT') {
    if (!isset($_GET['id'])) {
        echo json_encode(['status' => 'error', 'message' => 'ID salle manquant']);
        exit;
    }
    
    $id = intval($_GET['id']);
    $input = json_decode(file_get_contents('php://input'), true);
    
    $updateData = [];
    
    if (isset($input['nom_salle'])) {
        $updateData['nom_salle'] = $input['nom_salle'];
    }
    if (isset($input['nom_tv'])) {
        $updateData['nom_tv'] = $input['nom_tv'];
    }
    if (isset($input['uid_badge'])) {
        // Verification UID unique (excluant la salle courante)
        $existing = $sallesCollection->findOne([
            'uid_badge' => $input['uid_badge'],
            'id' => ['$ne' => $id]
        ]);
        if ($existing) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Cet UID badge est deja utilise'
            ]);
            exit;
        }
        $updateData['uid_badge'] = $input['uid_badge'];
    }
    
    if (empty($updateData)) {
        echo json_encode(['status' => 'error', 'message' => 'Aucune donnee a mettre a jour']);
        exit;
    }
    
    try {
        $result = $sallesCollection->updateOne(
            ['id' => $id],
            ['$set' => $updateData]
        );
        
        if ($result->getModifiedCount() > 0) {
            echo json_encode(['status' => 'success', 'message' => 'Salle modifiee']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Aucune modification effectuee']);
        }
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => 'Erreur mise a jour']);
    }
    exit;
}

// DELETE - Supprimer une salle
if ($method === 'DELETE') {
    if (!isset($_GET['id'])) {
        echo json_encode(['status' => 'error', 'message' => 'ID salle manquant']);
        exit;
    }
    
    $id = intval($_GET['id']);
    
    try {
        $result = $sallesCollection->deleteOne(['id' => $id]);
        
        if ($result->getDeletedCount() > 0) {
            echo json_encode(['status' => 'success', 'message' => 'Salle supprimee']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Salle non trouvee']);
        }
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => 'Erreur suppression']);
    }
    exit;
}

// Methode non supportee
echo json_encode(['status' => 'error', 'message' => 'Methode non supportee']);
?>