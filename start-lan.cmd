@echo off
powershell.exe -NoProfile -ExecutionPolicy Bypass -File "%~dp0lan-preview.ps1"
set "BISU_LAN_EXIT_CODE=%ERRORLEVEL%"
pause
exit /b %BISU_LAN_EXIT_CODE%
