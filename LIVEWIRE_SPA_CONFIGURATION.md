# Laravel Livewire SPA Configuration for MAMP/Apache

## Overview
This document explains how the Laravel Livewire SPA has been configured to run directly on Apache/MAMP at `http://localhost:8888/hospital/app/` without requiring `php artisan serve`.

## Configuration Files Updated

### 1. Root .htaccess (`/Applications/MAMP/htdocs/hospital/.htaccess`)

```apache
<IfModule mod_rewrite.c>
<IfModule mod_negotiation.c>
    Options -MultiViews
</IfModule>

RewriteEngine On

# Set the base for relative URLs
RewriteBase /hospital/

# Handle static assets first
RewriteCond %{REQUEST_URI} \.(css|js|png|jpg|jpeg|gif|ico|svg|woff|woff2|ttf|eot|pdf|zip)$ [NC]
RewriteCond %{REQUEST_FILENAME} !-f
RewriteRule ^(.*)$ public/$1 [L]

# Handle /app routes - redirect to Laravel routing (not physical app directory)
RewriteRule ^app(/.*)?$ public/index.php [QSA,L]

# Handle other requests to existing files/directories
RewriteCond %{REQUEST_FILENAME} -d [OR]
RewriteCond %{REQUEST_FILENAME} -f
RewriteRule ^ - [L]

# Send all other requests to public/index.php
RewriteCond %{REQUEST_FILENAME} !-d
RewriteCond %{REQUEST_FILENAME} !-f
RewriteRule ^(.*)$ public/index.php [QSA,L]
</IfModule>
```

### 2. App Directory .htaccess (`/Applications/MAMP/htdocs/hospital/app/.htaccess`)

```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    
    # Redirect all requests in /app/* back to the Laravel router
    # This ensures /hospital/app/* routes are handled by Laravel instead of serving directory contents
    RewriteRule ^(.*)$ ../public/index.php [QSA,L]
</IfModule>
```

**Important**: This file prevents Apache from serving the physical `app` directory contents and ensures all `/app/*` requests are routed through Laravel.

### 2. Livewire Configuration (`/Applications/MAMP/htdocs/hospital/config/livewire.php`)
```php
'asset_url' => '/hospital',
'app_url' => 'http://localhost:8888/hospital',
```

### 3. Environment Configuration (`/Applications/MAMP/htdocs/hospital/.env`)
```env
APP_URL=http://localhost:8888/hospital
LIVEWIRE_BASE_URL=http://localhost:8888/hospital/app
```

### 4. Root Index File (`/Applications/MAMP/htdocs/hospital/index.php`)
- Created to handle direct access and route to Laravel's public/index.php
- Ensures proper Laravel bootstrap when accessed via Apache

## URL Structure

### Primary URLs:
- **Application Root**: `http://localhost:8888/hospital/`
- **Livewire SPA Login**: `http://localhost:8888/hospital/app/login`
- **Livewire SPA Dashboard**: `http://localhost:8888/hospital/app/` (redirects to login if not authenticated)

### Route Mapping:
- `/hospital/app` → Redirects to `/hospital/app/login`
- `/hospital/app/login` → Login component
- `/hospital/app/dashboard` → Dashboard component (requires authentication)
- `/hospital/app/events/*` → Event management components (requires authentication)

## Authentication Flow

1. **Unauthenticated Access**: Any access to protected routes redirects to login
2. **Login Process**: Uses Admin guard with Administrator model
3. **Post-Login**: Redirects to `{LIVEWIRE_BASE_URL}/dashboard`
4. **Logout**: Clears session and redirects to login

## File Structure for Livewire SPA

```
/Applications/MAMP/htdocs/hospital/
├── .htaccess                              # Apache URL rewriting
├── index.php                             # Root handler for direct access
├── .env                                   # Environment configuration
├── config/livewire.php                    # Livewire asset configuration
├── app/Http/Livewire/
│   ├── Auth/Login.php                     # Login component
│   ├── Dashboard.php                      # Dashboard component
│   └── Events/                            # Event management components
├── resources/views/
│   ├── layouts/
│   │   ├── app.blade.php                  # Base layout for Livewire
│   │   └── admin.blade.php                # Admin dashboard layout
│   └── livewire/                          # Livewire component views
└── routes/web.php                         # Route definitions
```

## Testing the Setup

### 1. Access Test:
```bash
# Should redirect to login
curl -I http://localhost:8888/hospital/app/

# Should show login form
curl http://localhost:8888/hospital/app/login
```

### 2. Authentication Test:
- Visit: `http://localhost:8888/hospital/app/login`
- Use credentials: `admin@admin.com` / `password`
- Should redirect to dashboard after successful login

### 3. Asset Loading Test:
- Check browser network tab for 404 errors on CSS/JS files
- Livewire scripts should load from `/hospital/livewire/...`

## Troubleshooting

### Common Issues:

1. **404 on assets**: Check `.htaccess` rewrite rules and `asset_url` config
2. **Redirect loops**: Verify `APP_URL` and `LIVEWIRE_BASE_URL` in `.env`
3. **Livewire not loading**: Check `app_url` in livewire config
4. **Authentication issues**: Verify admin guard configuration

### Cache Clearing:
```bash
cd /Applications/MAMP/htdocs/hospital
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

### File Permissions:
Ensure Apache has read access to all Laravel files and write access to:
- `storage/`
- `bootstrap/cache/`

## Security Considerations

1. **Admin Authentication**: Uses separate guard from regular users
2. **Rate Limiting**: Login attempts are rate-limited
3. **Session Security**: Proper session invalidation on logout
4. **CSRF Protection**: Livewire handles CSRF automatically

## Production Deployment

For production deployment:
1. Set `APP_ENV=production` in `.env`
2. Set `APP_DEBUG=false`
3. Configure proper database credentials
4. Set up SSL/HTTPS
5. Configure proper caching (Redis/Memcached)
