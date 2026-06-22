Set WshShell = CreateObject("WScript.Shell")
' Run the reverb server without showing a window (0 = hide window)
WshShell.Run "php artisan reverb:start", 0
Set WshShell = Nothing
