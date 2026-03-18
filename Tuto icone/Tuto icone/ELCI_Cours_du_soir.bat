@echo off
REM === LANCEMENT DU PROJET ELCI ===

REM Chemin vers Laragon (à adapter si nécessaire)
set LARAGON_PATH=C:\laragon

REM Démarrer Laragon en arrière-plan
start "" /min "%LARAGON_PATH%\laragon.exe"

REM Attendre que Laragon démarre Apache (3 secondes)
timeout /t 3 /nobreak >nul

REM Ouvrir le projet dans le navigateur par défaut
start "" "http://elci_cours_soir.test"



REM Optionnel : Minimiser la fenêtre du .bat
exit