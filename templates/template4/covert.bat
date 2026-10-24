@echo off
setlocal

REM Input and Output files
set INPUT=2.mp4
set OUTPUT=2.webm

REM Convert MP4 to WebM (VP9 + Opus)
ffmpeg -i "%INPUT%" ^
-c:v libvpx-vp9 ^
-b:v 0 ^
-crf 30 ^
-c:a libopus ^
-b:a 128k ^
"%OUTPUT%"

echo.
echo Conversion complete!
pause