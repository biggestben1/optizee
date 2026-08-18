@echo off
echo ========================================
echo Removing Windows Firewall Rule for Herd
echo ========================================
echo.
echo This will remove the "Herd HTTP" firewall rule.
echo.
pause

netsh advfirewall firewall delete rule name="Herd HTTP" dir=in

if %ERRORLEVEL% EQU 0 (
    echo.
    echo ========================================
    echo SUCCESS! Firewall rule removed.
    echo ========================================
    echo.
) else (
    echo.
    echo ========================================
    echo ERROR: Failed to remove firewall rule.
    echo ========================================
    echo.
    echo Please run this file as Administrator:
    echo Right-click this file and select "Run as administrator"
    echo.
)

pause
















