# Real-Time Notifications Implementation Guide

## 🎯 Recommendation for Startups

**Best Option: Laravel Reverb** (Free, Self-Hosted, Official)
- ✅ 100% Free
- ✅ Official Laravel package
- ✅ No external dependencies
- ✅ Scales with your server
- ✅ Full control

**Alternative: Pusher Free Tier** (Easiest Setup)
- ✅ Free tier: 100 concurrent connections, 200k messages/day
- ✅ Zero server setup
- ✅ Perfect for MVP/startup phase
- ⚠️ Paid plans after free tier

---

## Option 1: Laravel Reverb (Recommended)

### Installation

```bash
# Install Reverb
composer require laravel/reverb

# Install Reverb assets
php artisan reverb:install

# Publish config
php artisan vendor:publish --tag=reverb-config
```

### Configuration

**1. Update `.env`:**
```env
REVERB_APP_ID=your-app-id
REVERB_APP_KEY=your-app-key
REVERB_APP_SECRET=your-app-secret
REVERB_HOST=localhost
REVERB_PORT=8080
REVERB_SCHEME=http

# For production
REVERB_HOST=your-domain.com
REVERB_PORT=443
REVERB_SCHEME=https
```

**2. Generate keys:**
```bash
php artisan reverb:install
# This will generate keys and add them to .env
```

**3. Update `config/broadcasting.php`:**
```php
'connections' => [
    'reverb' => [
        'driver' => 'reverb',
        'key' => env('REVERB_APP_KEY'),
        'secret' => env('REVERB_APP_SECRET'),
        'app_id' => env('REVERB_APP_ID'),
        'options' => [
            'host' => env('REVERB_HOST', '127.0.0.1'),
            'port' => env('REVERB_PORT', 8080),
            'scheme' => env('REVERB_SCHEME', 'http'),
        ],
    ],
],
```

**4. Update `.env` broadcasting:**
```env
BROADCAST_CONNECTION=reverb
```

### Backend Implementation

**1. Create Notification Event:**
```php
// app/Events/NotificationCreated.php
<?php

namespace App\Events;

use App\Models\Notification;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NotificationCreated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $notification;

    public function __construct(Notification $notification)
    {
        $this->notification = $notification;
    }

    public function broadcastOn(): Channel
    {
        return new Channel('user.' . $this->notification->user_id);
    }

    public function broadcastAs(): string
    {
        return 'notification.created';
    }

    public function broadcastWith(): array
    {
        return [
            'id' => $this->notification->id,
            'type' => $this->notification->type,
            'title' => $this->notification->title,
            'message' => $this->notification->message,
            'data' => $this->notification->data,
            'read' => $this->notification->read,
            'created_at' => $this->notification->created_at->toIso8601String(),
        ];
    }
}
```

**2. Update NotificationService to broadcast:**
```php
// In app/Services/NotificationService.php
use App\Events\NotificationCreated;

// After creating notification:
$notification = Notification::create([...]);

// Broadcast event
broadcast(new NotificationCreated($notification))->toOthers();

// Continue with email and push notifications...
```

**3. Create Broadcast Routes:**
```php
// routes/channels.php
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('user.{userId}', function ($user, $userId) {
    return (int) $user->id === (int) $userId;
});
```

### Frontend Implementation

**1. Install Laravel Echo & Pusher JS:**
```bash
npm install --save-dev laravel-echo pusher-js
```

**2. Create Echo instance:**
```javascript
// resources/js/echo.js
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

window.Echo = new Echo({
    broadcaster: 'reverb',
    key: import.meta.env.VITE_REVERB_APP_KEY,
    wsHost: import.meta.env.VITE_REVERB_HOST,
    wsPort: import.meta.env.VITE_REVERB_PORT ?? 80,
    wssPort: import.meta.env.VITE_REVERB_PORT ?? 443,
    forceTLS: (import.meta.env.VITE_REVERB_SCHEME ?? 'https') === 'https',
    enabledTransports: ['ws', 'wss'],
});
```

**3. Update `.env` (frontend):**
```env
VITE_REVERB_APP_KEY="${REVERB_APP_KEY}"
VITE_REVERB_HOST="${REVERB_HOST}"
VITE_REVERB_PORT="${REVERB_PORT}"
VITE_REVERB_SCHEME="${REVERB_SCHEME}"
```

**4. Import Echo in main.js:**
```javascript
// resources/js/app.js
import './echo';
```

**5. Create Real-time Notification Composable:**
```javascript
// resources/js/composables/useRealtimeNotifications.js
import { ref, onMounted, onBeforeUnmount } from 'vue'
import { useAuthStore } from '../stores/auth'

export function useRealtimeNotifications() {
    const authStore = useAuthStore()
    const notifications = ref([])
    const unreadCount = ref(0)

    const listen = () => {
        if (!authStore.isAuthenticated || !window.Echo) {
            return
        }

        const userId = authStore.user?.id
        if (!userId) return

        // Listen for new notifications
        window.Echo.private(`user.${userId}`)
            .notification((notification) => {
                // Add to notifications list
                notifications.value.unshift(notification)
                
                // Update unread count
                if (!notification.read) {
                    unreadCount.value++
                }

                // Show browser notification if enabled
                if (Notification.permission === 'granted') {
                    new Notification(notification.title, {
                        body: notification.message,
                        icon: '/favicon.ico',
                        data: notification.data,
                    })
                }
            })
    }

    const disconnect = () => {
        if (window.Echo && authStore.isAuthenticated) {
            const userId = authStore.user?.id
            if (userId) {
                window.Echo.leave(`user.${userId}`)
            }
        }
    }

    onMounted(() => {
        if (authStore.isAuthenticated) {
            listen()
        }
    })

    onBeforeUnmount(() => {
        disconnect()
    })

    return {
        notifications,
        unreadCount,
        listen,
        disconnect,
    }
}
```

**6. Update NotificationDropdown to use real-time:**
```javascript
// In NotificationDropdown.vue
import { useRealtimeNotifications } from '../composables/useRealtimeNotifications'

const { notifications: realtimeNotifications, unreadCount: realtimeUnreadCount } = useRealtimeNotifications()

// Merge real-time notifications with fetched ones
watch(realtimeNotifications, (newNotifs) => {
    if (newNotifs.length > 0) {
        notifications.value = [...newNotifs, ...notifications.value]
    }
})
```

### Running Reverb Server

**Development:**
```bash
php artisan reverb:start
```

**Production (with Supervisor):**
```ini
# /etc/supervisor/conf.d/reverb.conf
[program:reverb]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/artisan reverb:start
autostart=true
autorestart=true
user=www-data
numprocs=1
redirect_stderr=true
stdout_logfile=/path/to/logs/reverb.log
```

---

## Option 2: Pusher Free Tier (Alternative)

### Setup

**1. Sign up:** https://pusher.com (Free tier: 100 connections, 200k messages/day)

**2. Install:**
```bash
composer require pusher/pusher-php-server
npm install --save-dev laravel-echo pusher-js
```

**3. Update `.env`:**
```env
BROADCAST_CONNECTION=pusher

PUSHER_APP_ID=your-app-id
PUSHER_APP_KEY=your-app-key
PUSHER_APP_SECRET=your-app-secret
PUSHER_APP_CLUSTER=mt1
```

**4. Update `config/broadcasting.php`:**
```php
'pusher' => [
    'driver' => 'pusher',
    'key' => env('PUSHER_APP_KEY'),
    'secret' => env('PUSHER_APP_SECRET'),
    'app_id' => env('PUSHER_APP_ID'),
    'options' => [
        'cluster' => env('PUSHER_APP_CLUSTER'),
        'host' => env('PUSHER_HOST') ?: 'api-'.env('PUSHER_APP_CLUSTER', 'mt1').'.pusher.com',
        'port' => env('PUSHER_PORT', 443),
        'scheme' => env('PUSHER_SCHEME', 'https'),
    ],
],
```

**5. Frontend Echo config:**
```javascript
// resources/js/echo.js
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

window.Echo = new Echo({
    broadcaster: 'pusher',
    key: import.meta.env.VITE_PUSHER_APP_KEY,
    cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER,
    forceTLS: true
});
```

**6. Update `.env` (frontend):**
```env
VITE_PUSHER_APP_KEY="${PUSHER_APP_KEY}"
VITE_PUSHER_APP_CLUSTER="${PUSHER_APP_CLUSTER}"
```

**Backend code remains the same** - just change `BROADCAST_CONNECTION` in `.env`.

---

## Cost Comparison

| Solution | Cost | Connections | Messages/Day | Setup |
|----------|------|-------------|--------------|-------|
| **Laravel Reverb** | **FREE** | Unlimited | Unlimited | Medium |
| **Pusher Free** | **FREE** | 100 | 200,000 | Easy |
| Pusher Paid | $49/mo | 200 | Unlimited | Easy |

---

## Recommendation

**For Startup MVP:** Start with **Pusher Free Tier**
- Quick setup (30 minutes)
- No server management
- Free tier is generous for early stage
- Easy to migrate to Reverb later

**For Production/Scale:** Migrate to **Laravel Reverb**
- No per-connection costs
- Full control
- Better for high traffic
- Official Laravel solution

---

## Implementation Priority

1. ✅ **Phase 1:** Implement Pusher Free Tier (quick win)
2. ✅ **Phase 2:** Add real-time to NotificationDropdown
3. ✅ **Phase 3:** Add real-time badge updates
4. ✅ **Phase 4:** Migrate to Reverb when scaling

---

## Next Steps

1. Choose option (Pusher for quick start, Reverb for long-term)
2. Install packages
3. Create NotificationCreated event
4. Update NotificationService to broadcast
5. Set up frontend Echo
6. Integrate with NotificationDropdown
7. Test with multiple users

Would you like me to implement one of these options now?

