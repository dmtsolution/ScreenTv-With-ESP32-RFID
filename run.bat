@echo off
setlocal EnableExtensions EnableDelayedExpansion

echo ========================================
echo LabTV - Lancement automatique
echo ========================================
echo.

cd /d "%~dp0"

REM ================= INSTALLATION =================
if not exist ".install_done" (
    echo Installation en cours...

    if not exist "api" mkdir api
    if not exist "logs" mkdir logs

    echo Installation PHP...
    where composer >nul 2>&1
    if !errorlevel!==0 (
        composer require mongodb/mongodb --ignore-platform-req=ext-mongodb
    ) else (
        echo Installation de Composer...
        php -r "copy('https://getcomposer.org/installer','composer-setup.php');"
        php composer-setup.php
        php -r "unlink('composer-setup.php');"
        php composer.phar require mongodb/mongodb
    )

    echo Installation Python...
    if exist venv rmdir /s /q venv

    python -m venv venv

    if not exist "venv\Scripts\python.exe" (
        echo Installation Python via winget...
        winget install Python.Python.3.12 --accept-package-agreements
        python -m venv venv
    )

    venv\Scripts\python.exe -m pip install --upgrade pip
    venv\Scripts\pip.exe install pywinauto uiautomation pywin32

    if exist "arduino\TV_Cast.py" copy /Y "arduino\TV_Cast.py" "TV_Cast.py" >nul
    if exist "arduino\TV cast.py" copy /Y "arduino\TV cast.py" "TV_Cast.py" >nul

    echo OK > .install_done
    echo Installation terminee
    echo.
)

REM ================= DETECTION IP =================
set "PC_IP="
set "IP_FOUND=0"

echo Detection IP...

for /f "tokens=2 delims=:" %%a in ('ipconfig ^| findstr IPv4') do (
    set "IP=%%a"
    set "IP=!IP:~1!"

    echo !IP! | findstr "^10\." >nul
    if !errorlevel! == 0 (
        set "PC_IP=!IP!"
        set "IP_FOUND=1"
    )

    echo !IP! | findstr "^192\.168\." >nul
    if !errorlevel! == 0 (
        if "!PC_IP!"=="" set "PC_IP=!IP!"
        set "IP_FOUND=1"
    )
)

if "!IP_FOUND!"=="0" (
    echo Aucune IP detectee
    set /p PC_IP=Entrez IP:
)

echo IP utilisee: !PC_IP!
echo !PC_IP! > ip_config.txt

REM ================= SERVEURS =================
echo Lancement PHP...
start "PHP Server" cmd /k "php -S 0.0.0.0:8000"

timeout /t 2 >nul

echo Lancement Python...
if exist "TV_Cast.py" (
    start "Python LabTV" cmd /k "python TV_Cast.py"
) else if exist "arduino\TV_Cast.py" (
    start "Python LabTV" cmd /k "cd /d arduino && python TV_Cast.py"
) else if exist "arduino\TV cast.py" (
    start "Python LabTV" cmd /k "cd /d arduino && python \"TV cast.py\""
) else (
    echo ERREUR: Script Python introuvable
    echo Cherche dans: arduino/TV_Cast.py
)

echo.
echo ========================================
echo Serveurs lances
echo ========================================
echo.
echo Interface web: http://localhost:8000
echo IP pour ESP32: !PC_IP!
echo.
pause