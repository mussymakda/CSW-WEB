# PowerShell script to permanently add XAMPP PHP to system PATH
# Run this as Administrator

Write-Host "Adding XAMPP PHP to system PATH..." -ForegroundColor Yellow

# Get current system PATH
$currentPath = [Environment]::GetEnvironmentVariable("PATH", "Machine")

# Check if XAMPP PHP is already in PATH
if ($currentPath -notlike "*C:\xampp\php*") {
    # Add XAMPP PHP to the beginning of PATH
    $newPath = "C:\xampp\php;$currentPath"
    
    try {
        [Environment]::SetEnvironmentVariable("PATH", $newPath, "Machine")
        Write-Host "✅ XAMPP PHP added to system PATH successfully!" -ForegroundColor Green
        Write-Host "Please restart your terminal/IDE for changes to take effect." -ForegroundColor Cyan
    }
    catch {
        Write-Host "❌ Failed to update system PATH. Please run as Administrator." -ForegroundColor Red
        Write-Host "Or manually add 'C:\xampp\php' to your system PATH environment variable." -ForegroundColor Yellow
    }
}
else {
    Write-Host "✅ XAMPP PHP is already in system PATH." -ForegroundColor Green
}

Write-Host ""
Write-Host "Current PHP version:" -ForegroundColor Yellow
& "C:\xampp\php\php.exe" -v
