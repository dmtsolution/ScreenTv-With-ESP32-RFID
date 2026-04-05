LABTV – GUIDE D'INSTALLATION ET D'EXPLOITATION
================================================

PROJET
------
LabTV permet de connecter automatiquement un PC Windows à un ecran TV
(Miracast / projection sans fil) via la lecture d'un badge RFID.

Le systeme complet comprend :
- Un serveur PHP pour la gestion des salles
- Une base MongoDB pour stocker les associations badge -> TV
- Un script Python qui ecoute sur UDP port 5005
- Un ESP32 avec lecteur RFID qui envoie les badges au serveur

------------------------------------------------------------

FONCTIONNEMENT TECHNIQUE
------------------------

1) L'ESP32 lit le badge RFID
2) L'ESP32 envoie l'UID au serveur PHP (HTTP)
3) Le serveur PHP cherche dans MongoDB le nom de la TV associee
4) Le serveur PHP envoie une commande UDP au script Python
5) Le script Python ouvre le menu Windows de projection (Win + K)
6) Le script Python selectionne automatiquement l'ecran
7) Le script Python valide la connexion

IMPORTANT :
Le PC doit avoir le Wi-Fi active et compatible Miracast.

------------------------------------------------------------

PREREQUIS
---------

• Windows 10 ou 11
• Python version officielle (python.org)
• PHP 8.x avec extension MongoDB
• MongoDB Community Edition
• Composer (gestionnaire de dependances PHP)
• ESP32 avec lecteur RFID RC522
• PC compatible projection sans fil
• Session Windows ouverte
• Ecran NON verrouille

------------------------------------------------------------

INSTALLATION RAPIDE
-------------------

1) Installer MongoDB :
   https://www.mongodb.com/try/download/community

2) Installer PHP 8.x :
   https://windows.php.net/download/
   (choisir Thread Safe x64)

3) Installer Composer :
   https://getcomposer.org/download/

4) Installer Python :
   https://www.python.org/downloads/
   Cocher "Add Python to PATH"

5) Creer la base de donnees MongoDB :
   mongosh
   use labtv_db
   db.createCollection('salles')
   db.createCollection('historique')
   db.createCollection('erreurs')

6) Telecharger le projet LabTV

7) Executer run.bat dans le dossier du projet

------------------------------------------------------------

STRUCTURE DU PROJET
-------------------

ScreenTv-With-ESP32-RFID/
├── arduino/
│   ├── ESP32_ConnectTV.ino    (code ESP32)
│   └── TV_Cast.py              (script Python)
├── api/
│   ├── badge.php               (API pour ESP32)
│   ├── salles.php              (CRUD salles)
│   └── historique.php          (API historique)
├── logs/
├── run.bat                     (lancement automatique)
├── index.html                  (interface web)
└── ip_config.txt               (IP du PC)

------------------------------------------------------------

CONFIGURATION DE L'ESP32
------------------------

1) Ouvrir arduino/ESP32_ConnectTV.ino dans Arduino IDE

2) Modifier le WiFi si necessaire :
   const char* ssid = "ssidici";
   const char* password = "motdepasseici";

3) Modifier l'IP du serveur PHP :
   const char* serverUrl = "http://IP_DU_PC:8000/api/badge.php";

4) Selectionner la carte : ESP32 Dev Module

5) Televerser le code sur l'ESP32

------------------------------------------------------------

GESTION DES SALLES (INTERFACE WEB)
----------------------------------

1) Ouvrir le navigateur : http://localhost:8000

2) Ajouter une salle :
   - Nom de la salle (ex: Laboratoire Info)
   - Nom de la TV (ex: SfcTv)
   - UID du badge (lu par l'ESP32)

3) Lister les salles :
   - Voir toutes les salles enregistrees
   - Supprimer une salle (avec confirmation)

4) Historique :
   - Voir toutes les connexions effectuees
   - Statut (succes / echec)

------------------------------------------------------------

DEMARRAGE DU SYSTEME
--------------------

1) Ouvrir un terminal dans le dossier du projet

2) Executer :
   run.bat

3) Verifier que les fenetres suivantes s'ouvrent :
   - PHP Server (serveur web sur port 8000)
   - Python LabTV (ecoute UDP sur port 5005)

4) Ouvrir l'interface web : http://localhost:8000

------------------------------------------------------------

PORT RESEAU UTILISE
--------------------

UDP : 5005 (communication PHP -> Python)
HTTP : 8000 (communication ESP32 -> PHP, interface web)

Si un pare-feu bloque les ports :

Autoriser PHP et Python dans le pare-feu Windows.

------------------------------------------------------------

CONDITIONS IMPORTANTES
----------------------

• L'utilisateur doit etre connecte
• L'ecran ne doit pas etre verrouille
• Le PC ne doit pas etre en veille
• Le menu Win + K doit fonctionner manuellement
• Le nom exact de l'ecran doit correspondre
• L'ESP32 et le PC doivent etre sur le meme reseau WiFi

------------------------------------------------------------

SECURITE
--------

Le script accepte toute commande UDP recue sur le reseau local.

Recommandations :

• Utiliser uniquement sur reseau local securise
• Ne pas exposer le port 5005 sur Internet
• Changer les identifiants WiFi par defaut

------------------------------------------------------------

DEPANNAGE
---------

Erreur : ModuleNotFoundError (Python)
→ run.bat installe automatiquement les dependances
→ Ou manuellement : pip install pywinauto uiautomation pywin32

Erreur : Connexion MongoDB echouee
→ Verifier que MongoDB est installe et demarre
→ Executer mongod dans un terminal

Erreur : Ecran ne se connecte pas
→ Verifier que Miracast fonctionne manuellement (Win + K)
→ Verifier le nom exact de la TV

Erreur : ESP32 ne se connecte pas
→ Verifier le WiFi (SSID et mot de passe)
→ Verifier l'IP du serveur dans le code Arduino

Erreur : Aucune reaction au badge
→ Verifier la console serie de l'ESP32 (115200 bauds)
→ Verifier que le serveur PHP est demarre

------------------------------------------------------------

LOGS ET DEBUG
-------------

• Console ESP32 : voir les UID des badges lus
• Fenetre Python LabTV : voir les commandes UDP recues
• Fenetre PHP Server : voir les requetes HTTP
• Interface web : journal des evenements

------------------------------------------------------------

PROJET LabTV
Plateforme : Windows + ESP32
Langages : PHP, Python, C++ (Arduino)
Base de donnees : MongoDB
Type : Automatisation reseau + projection ecran + RFID
