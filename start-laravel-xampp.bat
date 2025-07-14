@echo off
echo Setting up XAMPP PHP environment...
set PATH=C:\xampp\php;%PATH%
echo PHP Version:
php -v
echo.
echo Starting Laravel development server...
php artisan serve
pause
