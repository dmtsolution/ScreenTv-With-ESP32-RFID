LABTV – GUIDE D’INSTALLATION ET D’EXPLOITATION
================================================

PROJET
------
LabTv permet de connecter automatiquement un PC Windows à un écran TV
(Miracast / projection sans fil) via la lecture d’un badge RFID.

Le script écoute sur le réseau (UDP port 5005).
Lorsqu’un badge RFID est détecté, il déclenche automatiquement
la connexion à un écran spécifique via Win + K.

------------------------------------------------------------

FONCTIONNEMENT TECHNIQUE
------------------------

1) Le script écoute sur le port UDP 5005
2) Il attend un message réseau
3) Si le message reçu correspond à :
   - "SfcTv"
   - "AtRobotTv"
4) Il ouvre le menu Windows de projection (Win + K)
5) Il sélectionne automatiquement l’écran
6) Il valide la connexion

IMPORTANT :
Le PC doit avoir le Wi-Fi activé et compatible Miracast.

------------------------------------------------------------

PRÉREQUIS
----------

• Windows 10 ou 11
• Python version officielle (python.org)
• pyautogui installé
• PC compatible projection sans fil
• Session Windows ouverte
• Écran NON verrouillé

NE PAS utiliser la version Python Microsoft Store.

------------------------------------------------------------

INSTALLATION DE PYTHON
-----------------------

1) Télécharger depuis :
   https://www.python.org/downloads/

2) Installer Python
   ✓ Cocher "Add Python to PATH"

3) Vérifier :

   python --version

------------------------------------------------------------

INSTALLATION DES DÉPENDANCES
-----------------------------

Ouvrir l’invite de commande :

   python -m pip install pyautogui

Vérification :

   python -c "import pyautogui; print('OK')"

------------------------------------------------------------

INSTALLATION DU SCRIPT
-----------------------

1) Copier LabTv.py dans :

   C:\ScriptsPy\LabTv.py

2) Tester :

   python C:\ScriptsPy\LabTv.py

Si le message apparaît :

   En attente du badge RFID...

Alors le script fonctionne.

------------------------------------------------------------

CONFIGURATION AU DÉMARRAGE AUTOMATIQUE (SANS FENÊTRE)
------------------------------------------------------

1) Localiser pythonw.exe
   (même dossier que python.exe)

2) Créer un raccourci :

   "CHEMIN_VERS_pythonw.exe" "C:\ScriptsPy\LabTv.py"

3) Propriétés du raccourci :

   Démarrer dans :

   C:\ScriptsPy

4) Ouvrir :

   Win + R → shell:startup

5) Placer le raccourci dans ce dossier

Le script démarrera automatiquement à chaque ouverture de session.

------------------------------------------------------------

PORT RÉSEAU UTILISÉ
--------------------

UDP : 5005
IP : 0.0.0.0 (écoute toutes interfaces)

Si un pare-feu bloque le port :

Autoriser Python dans le pare-feu Windows.

------------------------------------------------------------

CONDITIONS IMPORTANTES
----------------------

• L’utilisateur doit être connecté
• L’écran ne doit pas être verrouillé
• Le PC ne doit pas être en veille
• Le menu Win + K doit fonctionner manuellement
• Le nom exact de l’écran doit correspondre

------------------------------------------------------------

SÉCURITÉ
--------

Le script accepte toute commande UDP reçue sur le réseau.

Recommandations :

• Utiliser uniquement sur réseau local sécurisé
• Ne pas exposer le port 5005 sur Internet
• Ajouter une authentification si déploiement élargi

------------------------------------------------------------

DÉPANNAGE
----------

Erreur : ModuleNotFoundError
→ python -m pip install pyautogui

Erreur : écran ne se connecte pas
→ Vérifier que Miracast fonctionne manuellement (Win + K)

Erreur : aucune réaction
→ Vérifier que le port UDP 5005 n’est pas bloqué

------------------------------------------------------------

PROJET LabTv
Plateforme : Windows
Langage : Python
Type : Automatisation réseau + projection écran