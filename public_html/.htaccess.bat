<IfModule mod_deflate.c>
    AddOutputFilterByType DEFLATE text/plain
    AddOutputFilterByType DEFLATE text/html
    AddOutputFilterByType DEFLATE text/xml
    AddOutputFilterByType DEFLATE text/css
    AddOutputFilterByType DEFLATE application/xml
    AddOutputFilterByType DEFLATE application/xhtml+xml
    AddOutputFilterByType DEFLATE application/rss+xml
    AddOutputFilterByType DEFLATE application/javascript
    AddOutputFilterByType DEFLATE application/x-javascript
</IfModule>

<IfModule mod_rewrite.c>
    <IfModule mod_negotiation.c>
        Options -MultiViews -Indexes
    </IfModule>

    RewriteEngine On
    RewriteBase /

    # Protect .env files
    RewriteRule ^(.*)\.env$ - [F,L]

    # Handle Authorization Header
    RewriteCond %{HTTP:Authorization} .
    RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]

    # Redirect index.php directly to root
    RewriteRule ^index\.php(/.*)?$ / [L,R=301]

    # -----------------------
    # Installation folder
    # -----------------------
    RewriteCond %{REQUEST_URI} ^/install(/|$)
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule ^install(/.*)?$ install/$1 [L]

    # -----------------------
    # AdminCP
    # -----------------------
    RewriteCond %{REQUEST_URI} ^/admincp(/|$)
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule ^admincp(/.*)?$ public/web/admincp/$1 [L]

    # -----------------------
    # API / OAuth / Sitemap / Sharing
    # -----------------------
    RewriteCond %{REQUEST_URI} ^/(api|oauth|sitemap|sharing)(/|$)
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule ^(.*)$ public/index.php/$1 [L]

    # -----------------------
    # Storage folder
    # -----------------------
    RewriteCond %{REQUEST_URI} ^/storage(/|$)
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule ^storage/(.*)$ public/storage/$1 [L]

    # -----------------------
    # Frontend (public/web)
    # -----------------------
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule ^(.*)$ public/web/$1 [L]
</IfModule>
