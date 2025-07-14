# Set XAMPP PHP as priority in PATH for this session
$env:PATH = "C:\xampp\php;$env:PATH"

Write-Host "XAMPP PHP Environment Setup" -ForegroundColor Green
Write-Host "PHP Version:" -ForegroundColor Yellow
php -v
Write-Host ""

# Check if intl extension is loaded
$intlLoaded = php -r "echo extension_loaded('intl') ? 'YES' : 'NO';"
Write-Host "intl extension loaded: $intlLoaded" -ForegroundColor $(if($intlLoaded -eq 'YES') {'Green'} else {'Red'})
Write-Host ""

Write-Host "Starting Laravel development server..." -ForegroundColor Cyan
php artisan serve
