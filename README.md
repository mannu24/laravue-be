# Laravue

A full-stack developer community platform built with **Laravel 13** and **Vue 3**. Laravue combines a Q&A forum, social feed, project showcase, GitHub integration, and a full gamification engine into a single cohesive application for developers.

---

## Table of Contents

- [Tech Stack](#tech-stack)
- [Features](#features)
- [Architecture Overview](#architecture-overview)
- [Prerequisites](#prerequisites)
- [Installation](#installation)
- [Environment Configuration](#environment-configuration)
- [Database Setup](#database-setup)
- [Running the Application](#running-the-application)
- [API Reference](#api-reference)
- [Frontend Structure](#frontend-structure)
- [Gamification System](#gamification-system)
- [AI-Powered Q&A](#ai-powered-qa)
- [Real-Time Features](#real-time-features)
- [GitHub Integration](#github-integration)
- [Scheduled Jobs](#scheduled-jobs)
- [Project Structure](#project-structure)

---

## Tech Stack

### Backend
| Technology | Purpose |
|---|---|
| PHP 8.3+ | Runtime |
| Laravel 13 | Framework |
| Laravel Passport 13 | OAuth2 API authentication |
| Laravel Reverb | Self-hosted WebSocket server |
| Spatie Media Library | File/image uploads |
| Spatie Sluggable | URL-friendly slugs |
| MySQL | Primary database |

### Frontend
| Technology | Purpose |
|---|---|
| Vue 3 | UI framework (Composition API) |
| Pinia | State management with persistence |
| Vue Router | Client-side routing |
| Tailwind CSS | Utility-first styling |
| Vite | Build tool |
| Laravel Echo + Pusher.js | Real-time WebSocket client |
| md-editor-v3 | Markdown editor |
| Radix Vue / Reka UI | Headless UI primitives |
| Lucide Vue | Icon library |

---

## Features

### Authentication
- Email/password registration and login
- OTP-based passwordless login via email
- OAuth2 token management via Passport
- Token auto-expiration at midnight

### Social Feed
- Create, edit, and delete posts with rich text
- Like/unlike posts and comments
- Nested comment threads
- @mention users with autocomplete suggestions
- Tag system for categorization
- Infinite scroll pagination

### Q&A Forum
- Ask questions with markdown support
- Answer questions with nested reply threads
- Upvote questions and answers
- Accept/verify answers (question owner)
- AI-powered answer suggestions (Gemini / OpenAI)
- AI-generated title and tag suggestions
- Question search and filtering by tags
- Bookmark questions for later

### Project Showcase
- Create projects with full markdown descriptions
- Project status workflow: draft → pending review → published
- Technology tagging and category assignment
- Version management and changelogs
- Reviews and star ratings
- Upvoting and crowdfunding
- Featured and trending project listings
- Sellable projects with pricing (open/closed source)

### GitHub Integration
- Connect GitHub account via OAuth2
- Browse and select repositories
- Import repositories as projects with auto-detection of:
  - Project metadata from README
  - Technologies from repository languages
  - Description extraction
- Disconnect GitHub at any time

### Social Networking
- Follow/unfollow users
- User profiles with bio, social links, and avatar
- Profile visit tracking
- Activity feed (posts, questions, answers, follows)
- Global search across users, questions, posts, and projects

### Notifications
- In-app notification center with read/unread status
- Real-time delivery via WebSocket (Reverb)
- Web Push notifications (VAPID)
- Email notifications
- Notification types: likes, comments, answers, follows, mentions, XP earned, level ups, badge unlocks
- Bulk read/delete management

### User Settings
- Notification preferences (email, push, in-app)
- Push subscription management
- Social links manager (GitHub, LinkedIn, Twitter, website, etc.)

---

## Architecture Overview

```
┌─────────────────────────────────────────────────────┐
│                    Vue 3 SPA                        │
│  Pinia Stores │ Vue Router │ Composables │ Echo     │
└──────────────────────┬──────────────────────────────┘
                       │ REST API (JSON)
                       ▼
┌─────────────────────────────────────────────────────┐
│                  Laravel 13 API                     │
│  Controllers │ Services │ Repositories │ Events     │
├─────────────────────────────────────────────────────┤
│  Passport OAuth2  │  Reverb WebSocket  │  Queues   │
└──────────┬────────────────┬─────────────┬───────────┘
           │                │             │
     ┌─────▼─────┐   ┌─────▼─────┐  ┌────▼────┐
     │   MySQL   │   │  Reverb   │  │  Queue  │
     │ (40+ tbl) │   │ (WS srv)  │  │ (DB)    │
     └───────────┘   └───────────┘  └─────────┘
```

The application follows a service-layer architecture:
- **Controllers** handle HTTP requests and delegate to services
- **Services** contain business logic (Gamification, QA, GitHub, Notifications, etc.)
- **Repositories** handle data access patterns
- **Events & Listeners** decouple gamification side-effects (XP, badges, notifications)
- **Enums** enforce type safety for statuses, tiers, and categories

---

## Prerequisites

- PHP 8.3+
- Composer 2.x
- Node.js 18+ and npm
- MySQL 8.0+
- (Optional) Redis for caching/queues
- (Optional) Reverb for real-time features

---

## Installation

```bash
# Clone the repository
git clone <repository-url> laravue
cd laravue

# Install PHP dependencies
composer install

# Install Node dependencies
npm install

# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate

# Generate Passport encryption keys
php artisan passport:keys
```

---

## Environment Configuration

Key variables to configure in `.env`:

```dotenv
# Application
APP_NAME=Laravue
APP_URL=http://localhost

# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=laravue
DB_USERNAME=root
DB_PASSWORD=

# Queue (database-driven by default)
QUEUE_CONNECTION=database

# Broadcasting (for real-time features)
BROADCAST_CONNECTION=reverb
REVERB_APP_ID=your-app-id
REVERB_APP_KEY=your-app-key
REVERB_APP_SECRET=your-app-secret
REVERB_SERVER_HOST=127.0.0.1
REVERB_SERVER_PORT=8080

# Frontend broadcasting config
VITE_BROADCAST_DRIVER=reverb
VITE_REVERB_APP_KEY="${REVERB_APP_KEY}"
VITE_REVERB_HOST="${REVERB_SERVER_HOST}"
VITE_REVERB_PORT="${REVERB_SERVER_PORT}"
VITE_REVERB_SCHEME=http

# AI Q&A (optional)
AI_QNA_ENABLED=false
AI_GEMINI_KEY=
AI_OPENAI_KEY=
AI_DEFAULT_MODEL=gemini

# GitHub Integration (optional)
GITHUB_CLIENT_ID=
GITHUB_CLIENT_SECRET=
GITHUB_REDIRECT_URI=

# Mail
MAIL_MAILER=log
MAIL_FROM_ADDRESS="hello@example.com"

# Push Notifications (optional)
VAPID_PUBLIC_KEY=
VAPID_PRIVATE_KEY=
```

---

## Database Setup

```bash
# Run migrations
php artisan migrate

# Seed initial data (levels, badges, tasks, sample projects, technologies)
php artisan db:seed

# Create Passport personal access client (included in seeder, or run manually)
php artisan passport:client --personal --name="API Token"
```

The seeder creates:
- 20 levels across 5 tiers (Beginner → Legend)
- 29 badges across 6 categories
- 13 tasks (6 daily, 7 weekly)
- 30 technologies (Laravel, Vue.js, React, Docker, etc.)
- 10 sample users with 6 sample projects

---

## Running the Application

```bash
# Option 1: All services at once (recommended for development)
composer run dev

# This starts concurrently:
#   - php artisan serve        (API server)
#   - php artisan queue:listen (Queue worker)
#   - php artisan pail         (Log viewer)
#   - npm run dev              (Vite dev server)

# Option 2: Run services individually
php artisan serve              # Backend at http://localhost:8000
npm run dev                    # Vite dev server
php artisan queue:listen       # Queue worker
php artisan reverb:start       # WebSocket server (for real-time features)
```

Build for production:
```bash
npm run build
```

---

## API Reference

All API endpoints are prefixed with `/api/v1`. Authentication uses Bearer tokens via Passport.

### Authentication
| Method | Endpoint | Auth | Description |
|---|---|---|---|
| POST | `/register` | No | Register new user |
| POST | `/login` | No | Login with email/password |
| POST | `/auth/otp` | No | Request/verify OTP |
| POST | `/logout` | Yes | Revoke token |

### Users & Profiles
| Method | Endpoint | Auth | Description |
|---|---|---|---|
| GET | `/user` | Yes | Get authenticated user |
| POST | `/user` | Yes | Update profile |
| GET | `/users/{username}` | No | Get public user profile |
| POST | `/users/{username}/follow` | Yes | Toggle follow |
| GET | `/users/{username}/followers` | Yes | List followers |
| GET | `/users/{username}/following` | Yes | List following |

### Social Feed
| Method | Endpoint | Auth | Description |
|---|---|---|---|
| POST | `/feed` | No | Get paginated feed |
| POST | `/posts` | Yes | Create post |
| PUT | `/posts/{id}` | Yes | Update post |
| DELETE | `/posts/{id}` | Yes | Delete post |
| GET | `/posts/{post_code}` | No | Get single post |
| GET | `/posts/like-unlike/{post_code}` | Yes | Toggle like |
| POST | `/post/comment` | Yes | Add comment |
| DELETE | `/post/comment/{id}` | Yes | Delete comment |
| GET | `/posts/mention-suggestions` | Yes | User mention autocomplete |

### Q&A
| Method | Endpoint | Auth | Description |
|---|---|---|---|
| POST | `/questions-feed` | No | List questions (paginated) |
| GET | `/questions/{id}` | No | Get question detail |
| POST | `/questions` | Yes | Create question |
| PUT | `/questions/{id}` | Yes | Update question |
| DELETE | `/questions/{id}` | Yes | Delete question |
| POST | `/questions/{id}/toggle-upvote` | Yes | Toggle upvote |
| POST | `/questions/{question}/answers` | Yes | Post answer |
| POST | `/answers/{answer}/upvote` | Yes | Upvote answer |
| GET | `/answers/{answer}/replies` | Yes | Get reply thread |
| POST | `/answers/{answer}/replies` | Yes | Post reply |

### AI Q&A
| Method | Endpoint | Auth | Description |
|---|---|---|---|
| POST | `/questions/ai-suggest-meta` | No | AI-suggest title & tags |
| POST | `/questions/ai-analyze` | No | AI-analyze question content |
| POST | `/questions/{id}/ai-answer` | No | Stream AI answer |
| POST | `/ai-answers/{id}/validate` | No | Validate AI answer |

### Projects
| Method | Endpoint | Auth | Description |
|---|---|---|---|
| GET | `/projects` | No | List projects |
| GET | `/projects/featured` | No | Featured projects |
| GET | `/projects/trending` | No | Trending projects |
| GET | `/projects/{project}` | No | Project detail |
| POST | `/projects` | Yes | Create project |
| PUT | `/projects/{id}` | Yes | Update project |
| DELETE | `/projects/{id}` | Yes | Delete project |
| POST | `/projects/{project}/publish` | Yes | Publish project |
| POST | `/projects/{project}/upvote` | Yes | Toggle upvote |
| POST | `/projects/{project}/fund` | Yes | Fund project |
| POST | `/projects/{project}/reviews` | Yes | Create review |
| POST | `/projects/{project}/versions` | Yes | Create version |
| GET | `/projects/technologies` | No | List technologies |
| GET | `/projects/categories` | No | List categories |

### GitHub
| Method | Endpoint | Auth | Description |
|---|---|---|---|
| GET | `/github/authorize` | Yes | Start OAuth flow |
| GET | `/github/callback` | No | OAuth callback |
| GET | `/github/status` | Yes | Connection status |
| GET | `/github/repositories` | Yes | List user repos |
| GET | `/github/repositories/{owner}/{repo}` | Yes | Get repo detail |
| POST | `/github/import` | Yes | Import repo as project |
| DELETE | `/github/disconnect` | Yes | Disconnect GitHub |

### Gamification
| Method | Endpoint | Auth | Description |
|---|---|---|---|
| GET | `/tasks/daily/{user}` | Yes | Get daily tasks |
| GET | `/tasks/weekly/{user}` | Yes | Get weekly tasks |
| POST | `/tasks/complete` | Yes | Complete a task |
| POST | `/tasks/assign` | Yes | Assign task to user |
| POST | `/tasks/auto-complete` | Yes | Auto-complete detected tasks |

### Bookmarks
| Method | Endpoint | Auth | Description |
|---|---|---|---|
| GET | `/bookmarks` | Yes | List bookmarks |
| POST | `/bookmarks/toggle` | Yes | Toggle bookmark |
| GET | `/bookmarks/check` | Yes | Check bookmark status |
| GET | `/bookmarks/count` | Yes | Get bookmark count |
| POST | `/bookmarks/batch-check` | Yes | Batch check bookmarks |

### Notifications
| Method | Endpoint | Auth | Description |
|---|---|---|---|
| GET | `/notifications` | Yes | List notifications |
| GET | `/notifications/unread-count` | Yes | Unread count |
| POST | `/notifications/{id}/read` | Yes | Mark as read |
| POST | `/notifications/read-all` | Yes | Mark all as read |
| DELETE | `/notifications/{id}` | Yes | Delete notification |
| POST | `/notifications/bulk-delete` | Yes | Bulk delete |

### Settings & Push
| Method | Endpoint | Auth | Description |
|---|---|---|---|
| GET | `/settings/notifications` | Yes | Get notification prefs |
| PUT | `/settings/notifications` | Yes | Update notification prefs |
| POST | `/push-subscriptions` | Yes | Subscribe to push |
| DELETE | `/push-subscriptions` | Yes | Unsubscribe |
| GET | `/push-subscriptions/vapid-key` | No | Get VAPID public key |

### Search
| Method | Endpoint | Auth | Description |
|---|---|---|---|
| GET | `/search` | No | Global search |
| GET | `/search/tag-suggestions` | No | Tag autocomplete |
| GET | `/search/user-suggestions` | No | User autocomplete |

### Other
| Method | Endpoint | Auth | Description |
|---|---|---|---|
| GET | `/global-data` | Yes | App-wide shared data |
| GET | `/activities` | No | Activity feed |
| GET | `/app-config` | No | Feature flags |
| GET | `/social-links/types` | Yes | Social link types |

---

## Frontend Structure

### Pages (30 views)
| Route | View | Auth | Description |
|---|---|---|---|
| `/` | Home | Public | Landing page |
| `/about` | About | Public | About page |
| `/contact` | Contact | Public | Contact form |
| `/login` | Login | Guest | Login form |
| `/signup` | Signup | Guest | Registration form |
| `/dashboard` | Profile | Auth | User dashboard |
| `/profile/update` | UpdateProfile | Auth | Edit profile |
| `/@:username` | UserProfile | Public | Public user profile |
| `/feed` | Feed | Public | Social feed |
| `/feed/post_:code` | PostDetail | Public | Single post |
| `/qna` | Qna | Public | Q&A listing |
| `/qna/ask` | QuestionAsk | Auth | Ask question |
| `/qna/ask/:slug` | EditAskedQuestion | Auth | Edit question |
| `/qna/:slug` | QuestionDetail | Public | Question detail |
| `/projects` | Projects | Public | Project listing |
| `/projects/create` | AddProject | Public | Create project |
| `/projects/:slug` | ProjectDetail | Public | Project detail |
| `/projects/:slug/edit` | EditProject | Auth | Edit project |
| `/badges` | BadgesView | Public | All badges |
| `/levels` | LevelsView | Public | Level progression |
| `/tasks` | TasksView | Auth | Daily/weekly tasks |
| `/notifications` | Notifications | Auth | Notification center |
| `/settings` | Settings | Auth | User settings |
| `/search` | SearchResults | Public | Search results |

### Pinia Stores (12)
| Store | Purpose |
|---|---|
| `auth` | Authentication state, token, user data (persisted to localStorage) |
| `userStore` | User profile and gamification summary |
| `questionStore` | Q&A question data and pagination |
| `answerStore` | Answer data and operations |
| `taskStore` | Daily/weekly task tracking |
| `xpStore` | XP logs and summary |
| `badgeStore` | Badge collection data |
| `levelStore` | Level progression info |
| `globalData` | Shared app-wide data (tags, technologies, etc.) |
| `featureFlags` | Feature toggles (e.g., AI Q&A enabled) |
| `theme` | Dark mode toggle |
| `toastStore` | Toast notification queue |

### Composables (18)
| Composable | Purpose |
|---|---|
| `useApi` | Axios wrapper with auth headers |
| `useGamification` | XP calculations, badge formatting, level progress |
| `useGamificationRealtime` | Real-time gamification event handling |
| `useRealtimeNotifications` | WebSocket notification listener |
| `usePushNotifications` | Web Push API integration |
| `useAutoTaskCompletion` | Automatic task detection on route navigation |
| `useAiAnswer` | AI answer streaming and display |
| `useBookmark` | Bookmark toggle logic |
| `useFollow` | Follow/unfollow logic |
| `useMentions` | @mention autocomplete |
| `useConfirmDialog` | Confirmation dialog state |
| `useToast` | Toast notification triggers |
| `useModal` | Modal state management |
| `usePagination` | Infinite scroll pagination |
| `useAchievementPopups` | Achievement unlock popups |
| `useAnimation` | Animation utilities |
| `useNavbarSearch` | Search bar state |
| `useRecentSearches` | Search history (localStorage) |

### Component Library
```
components/
├── elements/          # Reusable primitives (Modal, TagInput, FileUpload, InfiniteScroll, MarkDownEditor)
├── feed/              # PostCard, PostForm, sidebar widgets
├── gamification/      # BadgeItem, BadgeList, LevelCard, LevelUpModal, TaskItem, TaskList,
│                        XpProgressBar, StreakCounter, AchievementPopups, GamificationEffects
├── github/            # GitHubConnectButton, GitHubImportModal, RepositorySelector, ImportPreview
├── notifications/     # NotificationDropdown
├── profile/           # ProfileCard, ProfileHeader, ProfileStats, SocialLinks, SocialLinksManager,
│                        AchievementsTimeline, XpHistoryChart, LevelJourney, BookmarksScroll
├── projects/          # ProjectCard, ProjectForm, ProjectFilters, ProjectReviewCard,
│                        ProjectReviewForm, RatingDisplay, StatusBadge, VersionCard, CategorySelector
├── qa/                # QuestionCard, QuestionForm, QuestionList, QuestionSearchBar, AnswerItem,
│                        AnswerList, AiAnswerBlock, AiAnswerPanel, VerifiedAnswerBadge, ScopeGuard
├── search/            # SearchDropdown
├── ui/                # Badge, Button, Card, Modal, Tabs, ConfirmDialog, EmptyState, LoadingSpinner,
│                        ToastContainer, ToastItem + shadcn-vue primitives (alert-dialog, avatar,
│                        badge, breadcrumb, button, card, dialog, dropdown-menu, input, label,
│                        navigation-menu, pin-input, select, skeleton, switch, tabs, textarea, toast)
├── Navbar.vue
├── FollowButton.vue
├── CommentSection.vue
├── CookieConsent.vue
├── NotificationConsentDialog.vue
└── ProjectFilters.vue
```

---

## Gamification System

The gamification engine is event-driven and processes XP, levels, badges, and tasks in a unified pipeline.

### XP Events
| Event | Description |
|---|---|
| `question_created` | User asked a question |
| `answer_created` | User posted an answer |
| `answer_verified` | User's answer was accepted |
| `daily_task_completed` | Completed a daily task |
| `weekly_task_completed` | Completed a weekly task |
| `profile_completed` | Filled out full profile |
| `streak_milestone` | Hit a streak milestone |

### Level Tiers (20 levels)
| Tier | Levels | XP Range |
|---|---|---|
| Beginner | 1–5 | 0 – 1,500 |
| Intermediate | 6–10 | 2,200 – 6,600 |
| Advanced | 11–15 | 8,200 – 16,600 |
| Expert | 16–19 | 19,200 – 28,200 |
| Legend | 20 | 30,000+ |

### Badge Categories (29 badges)
| Category | Examples |
|---|---|
| Participation | First Login, First Question, First Answer, Profile Complete |
| Consistency | Streak 3/7/14/30/60/100 days |
| Quality | 1/5/10/20 Verified Answers, 20/50 Upvoted Answers |
| Contribution | 10/25/50 Questions Asked, 10/25/50 Answers Given, Community Helper |
| Rare | Level 10/15/20, Perfect Week, Perfect Month |
| Event | Early Adopter |

### Tasks
- **6 Daily Tasks**: Login, Earn 20 XP, Answer 1 Question, Ask 1 Question, Complete a Task, Visit a Profile
- **7 Weekly Tasks**: Earn 200 XP, Complete 10 Daily Tasks, Answer 5 Questions, Ask 3 Questions, Get 1 Verified Answer, Maintain 7-Day Streak, Visit 10 Profiles

### Event Flow
```
User Action → GamificationService.awardXpAndProcess()
  ├── XpService: Award XP, recalculate level
  ├── TaskService: Check/complete matching tasks
  ├── BadgeService: Check/unlock earned badges
  └── Events dispatched:
       ├── UserEarnedXp → Real-time notification + log
       ├── UserLeveledUp → Real-time notification + log
       ├── UserUnlockedBadge → Real-time notification + log
       └── UserCompletedTask → Real-time notification + log
```

### Backend Services
| Service | Responsibility |
|---|---|
| `GamificationService` | Orchestrates XP award → level check → badge check → task check |
| `XpService` | Awards XP, recalculates user level |
| `LevelService` | Determines level from XP thresholds |
| `BadgeService` | Evaluates badge criteria and awards |
| `TaskService` | Generates, assigns, and completes tasks |
| `AchievementsPipeline` | Chains achievement checks |

---

## AI-Powered Q&A

When `AI_QNA_ENABLED=true`, the platform provides AI assistance for the Q&A system.

### Capabilities
- **Title & Tag Suggestions**: AI analyzes question body and suggests a title + relevant tags
- **Content Analysis**: Checks if a question is relevant to allowed topics (Laravel, Vue.js, PHP, JavaScript, etc.)
- **AI Answer Generation**: Streams an AI-generated answer for any question
- **Answer Validation**: Users can validate/rate AI answers

### AI Providers
1. **Google Gemini** (primary) — uses `gemini-2.0-flash` model
2. **OpenAI** (fallback) — uses `gpt-4o-mini` model

### Scope Guard
The AI system includes a scope guard that restricts questions to configured topics defined in `config/ai.php`:
```
laravel, vue.js, inertia.js, tailwind css, php, javascript, mysql, postgresql, redis, docker
```

---

## Real-Time Features

Real-time functionality is powered by Laravel Reverb (self-hosted WebSocket server) with Laravel Echo on the frontend.

### Broadcasting Channels
| Channel | Type | Purpose |
|---|---|---|
| `user.{userId}` | Private | User-specific notifications |
| `App.Models.User.{id}` | Private | Model-level broadcasts |

### Broadcast Events
- `notification.created` — New notification for a user
- Gamification events (XP earned, level up, badge unlock, task complete)

### Frontend Integration
- `useRealtimeNotifications` composable manages WebSocket connection lifecycle
- Auto-reconnect with exponential backoff (max 5 attempts)
- Falls back gracefully when Echo is not configured
- Supports both Reverb (self-hosted) and Pusher (managed) backends

---

## GitHub Integration

### OAuth Flow
1. User clicks "Connect GitHub" → redirected to GitHub OAuth
2. GitHub redirects back with auth code → backend exchanges for access token
3. Token stored encrypted in `user_github_tokens` table
4. User can browse repos, select one, and import as a project

### Import Pipeline
```
GitHubController.importRepository()
  → GitHubApiService: Fetch repo metadata + languages
  → ReadmeParserService: Parse README for description
  → GitHubImportService: Create Project + attach technologies
```

---

## Scheduled Jobs

Defined in `routes/console.php`:

| Schedule | Command | Description |
|---|---|---|
| Daily 00:00 UTC | `gamification:reset-daily-tasks` | Reset all daily tasks |
| Monday 00:00 UTC | `gamification:reset-weekly-tasks` | Reset all weekly tasks |
| Daily 01:00 UTC | `gamification:check-streaks` | Update user streak counts |

Run the scheduler:
```bash
php artisan schedule:work   # Development
# or add to crontab for production:
# * * * * * cd /path-to-project && php artisan schedule:run >> /dev/null 2>&1
```

---

## Project Structure

```
app/
├── Ai/Agents/              # AI agent classes (QnaAssistant)
├── Console/Commands/        # Artisan commands (streaks, task resets, scraper)
├── Enum/                    # Legacy enums (ProjectTypes, PaymentStatusTypes, ContactStatusTypes)
├── Enums/                   # PHP 8.1 enums (AnswerStatus, BadgeType, LevelTier, TaskFrequency,
│                              TaskStatus, XpEventType)
├── Events/Gamification/     # Domain events (UserEarnedXp, UserLeveledUp, UserUnlockedBadge,
│                              UserCompletedTask, UserAnsweredVerified)
├── Http/
│   ├── Controllers/v1/Api/  # API controllers (19 controllers)
│   ├── Requests/            # Form request validation (Auth, Gamification, QA, User)
│   ├── Resources/v1/        # API resources (Answer, Badge, Level, Question, Tag, Task, User, Xp)
│   └── Traits/              # HttpResponse trait
├── Jobs/                    # SendNotificationEmail
├── Listeners/Gamification/  # DispatchRealTimeNotification, LogAchievementEvent, QueueAchievementEffects
├── Mail/                    # NotificationMail, OtpMail
├── Models/                  # 40+ Eloquent models
├── Policies/                # AnswerPolicy, SocialLinkPolicy
├── Providers/               # AppServiceProvider, BroadcastServiceProvider, EventServiceProvider
├── Repositories/            # AnswerRepository
└── Services/
    ├── Achievements/        # AchievementsPipeline
    ├── Gamification/        # GamificationService, XpService, LevelService, BadgeService, TaskService
    ├── GitHub/              # GitHubOAuthService, GitHubApiService, GitHubImportService, ReadmeParserService
    ├── QA/                  # QuestionService, AnswerService
    ├── User/                # UserService
    ├── AiQnaService.php
    ├── BookmarkService.php
    ├── NotificationService.php
    ├── ProjectService.php
    ├── PushNotificationService.php
    ├── QuestionService.php
    └── TagService.php

resources/js/
├── app.js                   # Vue app bootstrap
├── echo.js                  # Laravel Echo configuration
├── routes.js                # Vue Router definitions
├── functions.js             # Global helper functions
├── bootstrap.js             # Axios defaults
├── components/              # 80+ Vue components (see Frontend Structure)
├── composables/             # 18 composables
├── stores/                  # 12 Pinia stores
└── views/                   # 30 page views

config/
├── ai.php                   # AI provider keys, allowed topics, feature flag
├── auth.php                 # Passport guard configuration
├── broadcasting.php         # Reverb/Pusher configuration
├── cache.php                # Database cache with serializable_classes
├── database.php             # MySQL/MariaDB/PostgreSQL connections
└── session.php              # Session cookie configuration

database/
├── migrations/              # 38 migration files
├── seeders/                 # DatabaseSeeder, LevelSeeder, BadgeSeeder, TaskSeeder, UserSeeder
└── factories/               # UserFactory
```

---

## License

MIT
