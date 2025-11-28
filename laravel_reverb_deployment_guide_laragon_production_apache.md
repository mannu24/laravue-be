# Laravel Reverb Complete Deployment Guide

A practical, battle-tested guide for configuring **Laravel Reverb** for:
- ✅ Local development with **Laragon (Windows + Apache + SSL)**
- ✅ Production deployment on **AWS / Ubuntu + Apache + SSL**

This guide covers architecture, environment variables, Apache configuration, SSL, modules, Supervisor, Echo setup, and common pitfalls.

---

## 🧠 Architecture Overview

Laravel Reverb has TWO communication layers:

### 1. Internal (Server → Reverb)
- Laravel PHP sends events to Reverb server
- Always local
- Always HTTP

```
Laravel → http://127.0.0.1:8080 (Reverb internal)
```

### 2. External (Browser → Reverb)
- WebSocket traffic from users
- Goes via domain + SSL
- Always HTTPS / WSS

```
Browser → wss://your-domain/app → Reverb
```

DO NOT confuse these two layers.

---

# ✅ ENVIRONMENT VARIABLES

## Shared (Local + Production)

```env
BROADCAST_DRIVER=reverb

REVERB_APP_ID=your_id
REVERB_APP_KEY=your_key
REVERB_APP_SECRET=your_secret
```

---

## Production .env

```env
REVERB_SERVER_HOST=127.0.0.1
REVERB_SERVER_PORT=8080

REVERB_HOST=yourdomain.com
REVERB_PORT=443
REVERB_SCHEME=https
REVERB_TLS=true
```

## Local (Laragon) .env

```env
REVERB_SERVER_HOST=127.0.0.1
REVERB_SERVER_PORT=8080

REVERB_HOST=laravel.test
REVERB_PORT=443
REVERB_SCHEME=https
REVERB_TLS=true
```

---

# ✅ config/broadcasting.php (CRITICAL)

```php
'reverb' => [
    'driver' => 'reverb',
    'key' => env('REVERB_APP_KEY'),
    'secret' => env('REVERB_APP_SECRET'),
    'app_id' => env('REVERB_APP_ID'),
    'options' => [
        'host' => env('REVERB_SERVER_HOST', '127.0.0.1'),
        'port' => env('REVERB_SERVER_PORT', 8080),
        'scheme' => 'http',
        'useTLS' => false,
    ],
],
```

❗ NEVER use HTTPS for server-to-reverb communication.

---

# ✅ APACHE CONFIGURATION (PRODUCTION)

### Required Modules

```bash
sudo a2enmod proxy
sudo a2enmod proxy_http
sudo a2enmod proxy_wstunnel
sudo a2enmod rewrite
sudo a2enmod ssl
sudo systemctl restart apache2
```

### VirtualHost SSL Conf

```apache
<VirtualHost *:443>
 ServerName yourdomain.com
 DocumentRoot /var/www/project/public

 ProxyPreserveHost On
 SSLProxyEngine On

 RewriteEngine On
 RewriteCond %{HTTP:Upgrade} =websocket [NC]
 RewriteCond %{HTTP:Connection} upgrade [NC]
 RewriteRule /(.*) ws://127.0.0.1:8080/$1 [P,L]

 ProxyPass /app ws://127.0.0.1:8080/app
 ProxyPassReverse /app ws://127.0.0.1:8080/app

 SSLCertificateFile /etc/letsencrypt/live/domain/fullchain.pem
 SSLCertificateKeyFile /etc/letsencrypt/live/domain/privkey.pem
</VirtualHost>
```

✅ DO NOT proxy /broadcasting/auth

---

# ✅ APACHE CONFIGURATION (LARAGON - WINDOWS)

### Enable Apache Modules

Edit:
```
C:\laragon\bin\apache\httpd-2.x\conf\httpd.conf
```
Enable:

```apache
LoadModule proxy_module modules/mod_proxy.so
LoadModule proxy_http_module modules/mod_proxy_http.so
LoadModule proxy_wstunnel_module modules/mod_proxy_wstunnel.so
LoadModule rewrite_module modules/mod_rewrite.so
```
Restart Laragon.

### Laragon SSL VirtualHost

```apache
<VirtualHost *:443>
 Define ROOT "C:/laragon/www/project/public"
 Define SITE "project.test"

 DocumentRoot "${ROOT}"
 ServerName ${SITE}

 SSLEngine on
 SSLCertificateFile C:/laragon/etc/ssl/laragon.crt
 SSLCertificateKeyFile C:/laragon/etc/ssl/laragon.key

 ProxyPreserveHost On
 SSLProxyEngine On

 RewriteEngine On
 RewriteCond %{HTTP:Upgrade} =websocket [NC]
 RewriteCond %{HTTP:Connection} upgrade [NC]
 RewriteRule /(.*) ws://127.0.0.1:8080/$1 [P,L]

 ProxyPass /app ws://127.0.0.1:8080/app
 ProxyPassReverse /app ws://127.0.0.1:8080/app
</VirtualHost>
```

---

# ✅ SUPERVISOR (PRODUCTION)

```ini
[program:reverb]
command=php /var/www/project/artisan reverb:start
autostart=true
autorestart=true
user=www-data
redirect_stderr=true
stdout_logfile=/var/log/reverb.log
```

Reload:
```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start reverb
```

---

# ✅ Echo Frontend Setup

```js
window.Echo = new Echo({
  broadcaster: 'reverb',
  key: import.meta.env.VITE_REVERB_APP_KEY,
  wsHost: import.meta.env.VITE_REVERB_HOST,
  wsPort: import.meta.env.VITE_REVERB_PORT,
  wssPort: import.meta.env.VITE_REVERB_PORT,
  forceTLS: true,
  enabledTransports: ['ws', 'wss'],
})
```

---

# ✅ BroadcastServiceProvider

```php
Broadcast::routes([
  'middleware' => ['auth:api']
]);
require base_path('routes/channels.php');
```

---

# ✅ Commands Cheat Sheet

```bash
php artisan reverb:start
php artisan queue:work
php artisan route:list | grep broadcasting
php artisan config:clear
php artisan cache:clear
```

---

# 🧪 Debug Commands

```js
window.Echo.connector.pusher.connection.state
```

Expected: `connected`

---

# ❗ Common Mistakes

| Issue | Cause |
|------|-------|
419 error | Laravel hitting HTTPS instead of local Reverb |
404 broadcasting | Apache proxy catching auth route |
Connected but no events | Events blocked by CSRF |

---

# ✅ Verified Flow

```
Interaction
  → Laravel
  → Reverb
  → Browser
```

---

## 🎉 You now have a enterprise-grade Reverb setup

This configuration supports:
- ✅ Scalable real-time broadcasting
- ✅ Secure SSL websocket channels
- ✅ Production-ready architecture

Need Nginx version? Kubernetes setup? Horizontal scaling? Just ask 🚀

