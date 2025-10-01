# Deploy Script for Windows (PowerShell)
# CSW Laravel App Deployment to public_html

param(
    [string]$ServerHost = "your-server.com",
    [string]$ServerUser = "your-username", 
    [string]$ServerPath = "/home/your-username/public_html",
    [string]$Domain = "your-domain.com",
    [switch]$DryRun
)

# Colors for output
$Green = "Green"
$Yellow = "Yellow"  
$Red = "Red"
$Blue = "Cyan"

function Write-Log {
    param([string]$Message)
    Write-Host "[$(Get-Date -Format 'yyyy-MM-dd HH:mm:ss')] $Message" -ForegroundColor $Green
}

function Write-Warning {
    param([string]$Message)
    Write-Host "[WARNING] $Message" -ForegroundColor $Yellow
}

function Write-Error {
    param([string]$Message)
    Write-Host "[ERROR] $Message" -ForegroundColor $Red
    exit 1
}

function Write-Info {
    param([string]$Message)
    Write-Host "[INFO] $Message" -ForegroundColor $Blue
}

# Check dependencies
function Test-Dependencies {
    Write-Log "Checking dependencies..."
    
    if (!(Get-Command composer -ErrorAction SilentlyContinue)) {
        Write-Error "Composer is required but not found in PATH"
    }
    
    if (!(Get-Command php -ErrorAction SilentlyContinue)) {
        Write-Error "PHP is required but not found in PATH"
    }
    
    if (!(Test-Path ".env")) {
        Write-Error ".env file not found. Please create one from .env.production"
    }
    
    Write-Log "All dependencies satisfied ✓"
}

# Validate configuration
function Test-Configuration {
    Write-Log "Validating configuration..."
    
    if ($ServerHost -eq "your-server.com") {
        Write-Error "Please update ServerHost parameter"
    }
    
    if ($ServerUser -eq "your-username") {
        Write-Error "Please update ServerUser parameter"
    }
    
    Write-Log "Configuration validated ✓"
}

# Prepare local files
function Initialize-LocalFiles {
    Write-Log "Preparing local files..."
    
    # Install/update composer dependencies
    & composer install --optimize-autoloader --no-dev --no-interaction
    
    if ($LASTEXITCODE -ne 0) {
        Write-Error "Composer install failed"
    }
    
    # Clear Laravel caches
    & php artisan config:clear
    & php artisan cache:clear  
    & php artisan view:clear
    & php artisan route:clear
    
    # Generate optimized files
    & php artisan config:cache
    & php artisan route:cache
    & php artisan view:cache
    
    Write-Log "Local files prepared ✓"
}

# Create deployment package
function New-DeploymentPackage {
    Write-Log "Creating deployment package..."
    
    $timestamp = Get-Date -Format "yyyyMMdd_HHmmss"
    $deployDir = ".\deploy_$timestamp"
    
    # Create deployment directory
    New-Item -ItemType Directory -Path $deployDir -Force | Out-Null
    New-Item -ItemType Directory -Path "$deployDir\laravel" -Force | Out-Null
    
    # Copy Laravel files (excluding public)
    $excludeItems = @(
        ".git", "node_modules", ".env.example", ".env.production", "tests",
        ".phpunit.result.cache", "deploy.sh", "deploy.ps1", "backups",
        ".DS_Store", "Thumbs.db", "*.log", ".vscode", ".idea",
        "README*.md", "CLAUDE.md", "COURSE-PROGRESS-README.md", 
        "FINAL-STATUS.md", "ONBOARDING-IMPLEMENTATION.md", 
        "PRODUCTION-READY.md", "VPS-DEPLOYMENT-GUIDE.md", 
        "XAMPP-SETUP.md", "PUBLIC-HTML-DEPLOYMENT.md",
        "setup-xampp-path.ps1", "start-dev.*", "start-laravel-xampp.*", 
        "test_*.php", "public"
    )
    
    Get-ChildItem -Path "." | Where-Object { 
        $item = $_
        -not ($excludeItems | Where-Object { $item.Name -like $_ })
    } | Copy-Item -Destination "$deployDir\laravel" -Recurse -Force
    
    # Copy public directory contents to root
    if (Test-Path ".\public") {
        Copy-Item -Path ".\public\*" -Destination $deployDir -Recurse -Force
    }
    
    # Update index.php paths
    $indexPath = "$deployDir\index.php"
    if (Test-Path $indexPath) {
        $indexContent = Get-Content $indexPath -Raw
        $indexContent = $indexContent -replace "__DIR__\.'/../vendor/autoload\.php'", "__DIR__.'/laravel/vendor/autoload.php'"
        $indexContent = $indexContent -replace "__DIR__\.'/../bootstrap/app\.php'", "__DIR__.'/laravel/bootstrap/app.php'"
        Set-Content -Path $indexPath -Value $indexContent
    }
    
    Write-Log "Deployment package created at: $deployDir ✓"
    return $deployDir
}

# Main deployment function
function Start-Deployment {
    Write-Log "Starting deployment of CSW App to $ServerHost"
    
    Test-Dependencies
    Test-Configuration
    
    if ($DryRun) {
        Write-Info "DRY RUN MODE - No files will be uploaded"
        Initialize-LocalFiles
        $deployDir = New-DeploymentPackage
        Write-Log "Dry run completed - deployment package ready at: $deployDir"
        Write-Info "You can manually upload the contents to your server"
        return
    }
    
    Initialize-LocalFiles
    $deployDir = New-DeploymentPackage
    
    Write-Info "Deployment package ready at: $deployDir"
    Write-Info "Please manually upload the contents to your server at: $ServerPath"
    Write-Info ""
    Write-Info "Upload Instructions:"
    Write-Info "1. Upload contents of '$deployDir\laravel\' to '$ServerPath/laravel/'"
    Write-Info "2. Upload contents of '$deployDir\' (except laravel folder) to '$ServerPath/'"
    Write-Info "3. Set permissions on storage and bootstrap/cache folders to 775"
    Write-Info "4. Run Laravel commands via SSH or hosting panel"
    Write-Info ""
    Write-Info "After upload, run these commands on your server:"
    Write-Info "cd $ServerPath/laravel"
    Write-Info "php artisan migrate --force"
    Write-Info "php artisan storage:link"
    Write-Info "chmod -R 775 storage/"
    Write-Info "chmod -R 775 bootstrap/cache/"
    
    Write-Log "🚀 Deployment package created successfully!"
    Write-Info "Your application will be available at: https://$Domain"
}

# Script execution
Write-Host ""
Write-Host "==============================================" -ForegroundColor $Blue
Write-Host "  CSW Laravel Deployment Script (Windows)" -ForegroundColor $Blue  
Write-Host "==============================================" -ForegroundColor $Blue
Write-Host ""

if ($DryRun) {
    Start-Deployment
    exit 0
}

Write-Warning "This will create a deployment package for:"
Write-Warning "Server: $ServerHost" 
Write-Warning "Path: $ServerPath"
Write-Warning "Domain: $Domain"
Write-Host ""
$confirmation = Read-Host "Do you want to continue? (y/N)"

if ($confirmation -eq "y" -or $confirmation -eq "Y") {
    Start-Deployment
} else {
    Write-Log "Deployment cancelled"
    exit 1
}