@echo off
echo ========================================
echo Adding Windows Firewall Rule for Herd
echo ========================================
echo.
echo This will allow incoming connections on port 80
echo so your mobile devices can access the site.
echo.
pause

netsh advfirewall firewall add rule name="Herd HTTP" dir=in action=allow protocol=TCP localport=80

if %ERRORLEVEL% EQU 0 (
    echo.
    echo ========================================
    echo SUCCESS! Firewall rule added.
    echo ========================================
    echo.
    echo You can now access your site from mobile devices at:
    echo http://192.168.0.116
    echo.
) else (
    echo.
    echo ========================================
    echo ERROR: Failed to add firewall rule.
    echo ========================================
    echo.
    echo Please run this file as Administrator:
    echo Right-click this file and select "Run as administrator"
    echo.
)

pause
















