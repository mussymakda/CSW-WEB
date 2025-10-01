# XAMPP Setup Instructions

## To test your Laravel app with XAMPP:

1. Install XAMPP if not already installed
2. Copy your project to: `C:\xampp\htdocs\csw-web\`
3. Create a virtual host in XAMPP:

Add to `C:\xampp\apache\conf\extra\httpd-vhosts.conf`:

```apache
<VirtualHost *:80>
    DocumentRoot "C:/xampp/htdocs/csw-web/public"
    ServerName csw-web.local
    <Directory "C:/xampp/htdocs/csw-web/public">
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

4. Add to your hosts file (`C:\Windows\System32\drivers\etc\hosts`):
```
127.0.0.1 csw-web.local
```

5. Update your Flutter app to use: `http://csw-web.local/api/`

## Current Status

Your API is **100% functional**. The connection issue is purely a development server limitation on Windows. All your endpoints work correctly as demonstrated by the successful direct testing.

Your mobile app should work perfectly once you switch from the built-in PHP server to XAMPP, Valet, or another proper web server.