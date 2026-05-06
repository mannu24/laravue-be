# Portfolio Feature — Detailed Specification

## Overview

A paid service that lets LaraVue users create personal portfolio websites accessible via subdomains (e.g., `manu.laravue.in`) or custom domains (e.g., `manuarora.com`). Users pick a template, fill in their profile/project data, and get a live portfolio site. Payments are handled via Razorpay with subscription plans (3-month, 6-month, yearly). Includes coupon codes, custom domain support, an admin panel for management, and template selection.

---

## 1. Subdomain Architecture

### How It Works
- Each portfolio is served at `{username}.laravue.in`
- The main app lives at `laravue.in` (and `www.laravue.in`)
- A wildcard DNS record (`*.laravue.in → server IP`) routes all subdomains to the same server
- A middleware detects whether the request is for the main app or a portfolio subdomain
- Portfolio subdomains serve a standalone, public-facing page (no navbar, no auth required to view)

### Hostinger Shared Hosting Constraints
- Wildcard subdomains must be configured via cPanel (Hostinger supports this)
- All subdomains point to the same `public/` directory
- The Laravel app handles routing based on the `Host` header
- No separate server processes — everything runs through the same PHP app

### Request Flow
```
Request arrives at server
  → PortfolioDomainMiddleware reads Host header

  Case 1: Host is "manu.laravue.in" (subdomain)
    → Extract "manu" from host
    → Look up portfolio by subdomain slug
    → If found + active subscription → render portfolio blade view
    → If found + expired subscription → render "subscription expired" page
    → If not found → 404

  Case 2: Host is "manuarora.com" (custom domain)
    → Look up portfolio_custom_domains where domain = host AND status = verified
    → If found → load linked portfolio
    → If portfolio has active subscription → render portfolio blade view
    → If expired → render "subscription expired" page
    → If domain not found or not verified → pass through (404 or main app)

  Case 3: Host is "laravue.in" or "www.laravue.in"
    → Pass through to main app (normal Laravel routing)
```

### Edge Cases
- **Reserved subdomains**: `www`, `api`, `admin`, `mail`, `ftp`, `cpanel`, `webmail`, `staging` — blocked from user registration
- **Username conflicts**: Portfolio subdomain slug must be unique; validated against existing usernames and reserved words
- **SSL**: Hostinger provides wildcard SSL for `*.laravue.in` — must be configured
- **Case sensitivity**: Subdomains are case-insensitive; always stored lowercase
- **Special characters**: Only alphanumeric and hyphens allowed; no underscores, dots, or spaces; 3-30 chars; cannot start/end with hyphen

---

## 1B. Custom Domain Support

### Overview
Pro and Annual plan users can connect their own domain (e.g., `manuarora.com`) to their portfolio instead of (or in addition to) the default `manu.laravue.in` subdomain. The subdomain always works as a fallback.

### How It Works

**User side:**
1. User enters their custom domain in the portfolio settings (e.g., `manuarora.com` or `portfolio.manuarora.com`)
2. The app shows step-by-step DNS instructions
3. User configures DNS at their domain registrar
4. User clicks "Verify Domain" in the dashboard
5. Backend checks DNS records; if correct, marks domain as verified
6. Portfolio is now accessible at both `manu.laravue.in` AND `manuarora.com`

**Server side:**
- The `PortfolioSubdomainMiddleware` is extended to also check the `Host` header against the `portfolio_custom_domains` table
- If the Host matches a verified custom domain → serve that portfolio
- If the Host matches a `*.laravue.in` subdomain → existing subdomain logic
- Otherwise → pass through to the main app

### DNS Configuration Instructions (Shown to User)

The portfolio settings page shows a clear instruction panel with copy-to-clipboard buttons:

**Option A — Root domain (e.g., `manuarora.com`):**
```
Type:  A Record
Host:  @
Value: {HOSTINGER_SERVER_IP}
TTL:   3600
```

**Option B — Subdomain (e.g., `portfolio.manuarora.com`):**
```
Type:  CNAME Record
Host:  portfolio
Value: laravue.in
TTL:   3600
```

**Important notes shown to user:**
- DNS changes can take up to 24-48 hours to propagate
- Remove any existing A/CNAME records for the same host before adding new ones
- If using Cloudflare, set the proxy status to "DNS only" (gray cloud) during setup; can enable proxy after verification

### Domain Affiliate Link

The custom domain settings page includes a prominent CTA for users who don't have a domain yet:

```
┌─────────────────────────────────────────────────────┐
│  🌐 Don't have a domain yet?                        │
│                                                      │
│  Get your custom domain starting at ₹149/year        │
│  from our recommended registrar.                     │
│                                                      │
│  [🔗 Buy a Domain on Hostinger →]                    │
│     (affiliate link)                                 │
│                                                      │
│  Use code LARAVUE for an extra discount!             │
└─────────────────────────────────────────────────────┘
```

- The affiliate link URL is stored in an env variable: `DOMAIN_AFFILIATE_URL`
- This keeps it configurable without code changes (can switch affiliate programs later)
- The affiliate coupon code is also configurable: `DOMAIN_AFFILIATE_COUPON`
- Shown on: custom domain settings page, plan comparison page (as a Pro/Annual perk), and the "subscription expired" page

### Domain Verification Flow

```
User enters domain → POST /api/v1/portfolio/custom-domain
  → Backend validates format (valid domain, not laravue.in itself, not IP address)
  → Creates portfolio_custom_domains record with status: "pending"
  → Returns DNS instructions + expected records

User clicks "Verify" → POST /api/v1/portfolio/custom-domain/verify
  → Backend performs DNS lookup:
     - For root domains: checks A record points to HOSTINGER_SERVER_IP
     - For subdomains: checks CNAME record points to laravue.in
  → If records match → status: "verified", domain goes live
  → If records don't match → status stays "pending", returns helpful error
     ("A record found but points to 1.2.3.4 instead of {expected_ip}")
```

### Automatic Re-verification
- A scheduled command `portfolio:verify-domains` runs every 6 hours
- Checks all "pending" domains that were added more than 1 hour ago
- Auto-verifies if DNS is now correct (user doesn't need to click verify again)
- After 7 days of pending status with no successful verification, sends a reminder email

### SSL for Custom Domains

**On Hostinger shared hosting:**
- Hostinger's AutoSSL (powered by Let's Encrypt) automatically provisions SSL for domains pointed to the server
- After DNS verification, SSL is typically available within minutes to a few hours
- No manual intervention needed — Hostinger's cPanel handles certificate issuance
- The app serves the portfolio over HTTPS; if SSL isn't ready yet, it falls back to HTTP with a note

**Edge case — SSL not yet provisioned:**
- If a user visits `https://manuarora.com` before SSL is ready, they'll get a browser warning
- The verification success page tells the user: "SSL certificate will be automatically provisioned within a few hours. Your portfolio will be accessible via HTTP immediately and HTTPS shortly after."

### Database Table

**portfolio_custom_domains**
```
id, portfolio_id, domain (unique), type (root/subdomain),
status (pending/verified/failed), verified_at, last_checked_at,
dns_error (text, nullable — stores last verification error message),
created_at, updated_at
```

### API Endpoints (Custom Domain)
```
POST   /api/v1/portfolio/custom-domain          — Add custom domain
POST   /api/v1/portfolio/custom-domain/verify    — Trigger verification
DELETE /api/v1/portfolio/custom-domain           — Remove custom domain
GET    /api/v1/portfolio/custom-domain/status    — Get domain status + DNS instructions
```

### Admin Endpoints (Custom Domain)
```
GET    /api/v1/admin/custom-domains              — List all custom domains (filterable by status)
POST   /api/v1/admin/custom-domains/{id}/verify  — Force re-verify a domain
DELETE /api/v1/admin/custom-domains/{id}         — Remove a custom domain
```

### Edge Cases
- **User adds domain they don't own**: Verification will fail since DNS won't point to our server. No harm done — the record stays pending and expires after 7 days of reminders.
- **Two users claim the same domain**: First-come-first-served. Domain column is unique. Second user gets "domain already in use" error.
- **User's subscription expires**: Custom domain stops working (same as subdomain). Domain record is preserved so it reconnects automatically on renewal.
- **User downgrades to Starter plan**: Custom domain feature is disabled. Domain record is preserved but marked inactive. Portfolio falls back to subdomain only. On upgrade, domain reactivates automatically.
- **Domain previously pointed elsewhere**: User must remove old DNS records first. Verification checks for exact match.
- **Cloudflare proxy**: If user has Cloudflare proxy enabled, A record lookup returns Cloudflare IPs instead of ours. Instructions tell user to disable proxy during setup. After verification, they can re-enable it (traffic will still reach us via Cloudflare).
- **www vs non-www**: If user adds `manuarora.com`, we recommend they also add a CNAME for `www.manuarora.com → manuarora.com` at their registrar. We don't manage the www redirect — that's on their DNS side.
- **Domain transfer**: If user transfers domain to a new registrar, DNS records may be lost. The re-verification cron will detect this and mark the domain as failed, sending the user a notification.

---

## 2. Subscription Plans & Pricing

### Plans

| Plan | Duration | Price (INR) | Features |
|------|----------|-------------|----------|
| Starter | 3 months | ₹299 | 1 template, 5 projects, basic analytics |
| Pro | 6 months | ₹499 | All templates, 15 projects, analytics, custom SEO, custom domain |
| Annual | 12 months | ₹799 | All templates, unlimited projects, analytics, SEO, custom domain, priority support, badge |

### Plan Rules
- Plans are non-recurring (one-time payment per period) — no auto-renewal
- Users can upgrade mid-plan (pay the difference prorated to remaining days)
- Users can renew before expiry (new period starts from current expiry date, not from today)
- When a plan expires, the portfolio goes offline (shows "subscription expired" page) but data is preserved for 90 days
- After 90 days of expiry with no renewal, data is soft-deleted (recoverable by admin for 30 more days)
- Users can switch templates at any time within their active plan
- Downgrading is not allowed mid-plan; user must wait for expiry and pick a new plan

### Grace Period
- 7-day grace period after expiry — portfolio stays live with a small banner "Subscription expiring soon"
- After grace period — portfolio goes offline

---

## 3. Razorpay Payment Integration

### Flow
```
User selects plan → clicks "Subscribe"
  → Frontend calls POST /api/v1/portfolio/orders (creates Razorpay order)
  → Backend creates a pending PortfolioOrder record
  → Backend calls Razorpay Orders API to create order
  → Returns order_id, amount, key_id to frontend
  → Frontend opens Razorpay checkout modal
  → User completes payment
  → Razorpay sends payment response to frontend
  → Frontend calls POST /api/v1/portfolio/orders/verify
  → Backend verifies signature using Razorpay secret
  → If valid → activates subscription, creates PortfolioSubscription record
  → If invalid → marks order as failed
```

### Webhook (Backup Verification)
- Register `POST /api/v1/webhooks/razorpay` as Razorpay webhook endpoint
- Handles `payment.captured`, `payment.failed`, `order.paid` events
- Idempotent — if subscription already activated via frontend verify, webhook is a no-op
- Webhook secret verified via `X-Razorpay-Signature` header

### Edge Cases
- **Double payment**: Idempotency check on order_id — if already paid, return success without creating duplicate subscription
- **Payment timeout**: Order expires after 30 minutes; user must create a new order
- **Partial payment**: Not supported; Razorpay handles full amount only
- **Refunds**: Admin can initiate refund via admin panel → calls Razorpay Refund API → marks subscription as refunded → portfolio goes offline
- **Currency**: INR only (Razorpay default for Indian merchants)
- **Failed payment retry**: User can retry by creating a new order; old failed order is kept for audit

---

## 4. Coupon Codes

### Structure

| Field | Type | Description |
|-------|------|-------------|
| code | string | Unique, uppercase, 4-20 chars (e.g., `LAUNCH50`) |
| discount_type | enum | `percentage` or `fixed` |
| discount_value | decimal | Percentage (1-100) or fixed amount in INR |
| max_uses | int/null | Total uses allowed (null = unlimited) |
| max_uses_per_user | int | Uses per user (default: 1) |
| used_count | int | Current total uses |
| min_order_amount | decimal/null | Minimum order amount to apply |
| applicable_plans | json/null | Array of plan slugs (null = all plans) |
| starts_at | datetime/null | When coupon becomes active |
| expires_at | datetime/null | When coupon expires |
| is_active | boolean | Admin can disable |

### Validation Rules
- Code must exist and be active
- Current date must be within starts_at and expires_at
- used_count < max_uses (if max_uses is set)
- User hasn't exceeded max_uses_per_user
- Order amount >= min_order_amount (if set)
- Plan is in applicable_plans (if set)
- Discount cannot exceed order amount (for fixed type)
- Percentage discount capped at 100%

### Edge Cases
- **Coupon applied but payment fails**: Coupon use is NOT counted until payment is verified
- **Coupon expires between apply and payment**: Re-validate at payment verification; if expired, still honor it (it was valid when order was created)
- **100% discount coupon**: Creates a "free" order — no Razorpay checkout needed; subscription activated immediately
- **Stacking**: No coupon stacking — only one coupon per order

---

## 5. Portfolio Data Model

### What Users Provide

**Personal Info** (collected via portfolio editor):
- Display name
- Professional title / tagline (e.g., "Full Stack Developer")
- Bio / about section (rich text, max 2000 chars)
- Profile photo (upload or use LaraVue profile photo)
- Resume/CV upload (PDF, max 5MB)
- Location (city, country — optional)
- Available for hire (boolean)

**Social Links**:
- GitHub, LinkedIn, Twitter/X, personal website, email
- Up to 8 custom links

**Skills/Technologies**:
- Select from existing LaraVue technologies + add custom ones
- Proficiency level (beginner, intermediate, advanced, expert) — optional

**Work Experience** (optional, up to 10 entries):
- Company name, role, start date, end date (or "present")
- Description (max 500 chars)

**Education** (optional, up to 5 entries):
- Institution, degree, field, start year, end year

**Portfolio Projects** (up to plan limit):
- Can link existing LaraVue projects OR create portfolio-only projects
- Portfolio-only projects: title, description, image, tech stack, live URL, source URL
- Display order (drag-and-drop reordering)

**Testimonials** (optional, up to 5):
- Author name, author role/company, content, author photo URL

**Custom Sections** (Pro/Annual only, up to 3):
- Section title, content (rich text)
- For things like "Certifications", "Publications", "Awards"

---

## 6. Templates

### Template System
- Templates are server-rendered Blade views (not Vue SPA) for SEO and fast loading
- Each template is a directory: `resources/views/portfolio/templates/{template-slug}/`
- Contains: `index.blade.php`, `assets/` (CSS, images)
- Templates receive a standardized `$portfolio` data object
- Users preview templates before selecting
- Template switching is instant (just changes a DB field)

### Initial Templates (3 at launch)

1. **Minimal** — Clean, white-space-heavy, typography-focused. Single page scroll.
2. **Developer** — Dark theme, terminal-inspired, code-block aesthetics. Sections with smooth scroll.
3. **Creative** — Colorful, card-based layout, animated transitions. Grid-based project showcase.

### Template Data Contract
Every template receives the same data shape:
```php
$portfolio = [
    'user' => [...],           // name, title, bio, photo, location, available_for_hire
    'social_links' => [...],   // array of {platform, url, icon}
    'skills' => [...],         // array of {name, proficiency}
    'experience' => [...],     // array of {company, role, dates, description}
    'education' => [...],      // array of {institution, degree, field, years}
    'projects' => [...],       // array of {title, description, image, tech, urls}
    'testimonials' => [...],   // array of {author, role, content, photo}
    'custom_sections' => [...],// array of {title, content}
    'template' => [...],       // template slug, settings
    'meta' => [...],           // SEO title, description, OG image
]
```

### Edge Cases
- **Template removed by admin**: Portfolios using it fall back to "Minimal" template
- **Template assets**: Served from `public/portfolio-templates/{slug}/` — cached aggressively
- **Custom colors**: Future feature (not in v1) — templates use their own color schemes

---

## 7. Database Schema

### New Tables

**portfolio_plans**
```
id, name, slug, duration_months, price, max_projects, features (json),
is_active, sort_order, created_at, updated_at
```

**portfolio_coupons**
```
id, code, discount_type (percentage/fixed), discount_value, max_uses,
max_uses_per_user, used_count, min_order_amount, applicable_plans (json),
starts_at, expires_at, is_active, created_at, updated_at
```

**portfolio_coupon_uses**
```
id, coupon_id, user_id, order_id, created_at
```

**portfolio_orders**
```
id, user_id, plan_id, coupon_id (nullable), amount, discount_amount,
final_amount, razorpay_order_id, razorpay_payment_id, razorpay_signature,
status (pending/paid/failed/refunded), paid_at, created_at, updated_at
```

**portfolio_subscriptions**
```
id, user_id, plan_id, order_id, starts_at, expires_at, grace_ends_at,
status (active/expired/cancelled/refunded), cancelled_at, created_at, updated_at
```

**portfolios**
```
id, user_id, subdomain (unique), template_slug, is_published, title,
tagline, bio, location_city, location_country, available_for_hire,
resume_path, meta_title, meta_description, og_image_path,
settings (json — template-specific overrides), created_at, updated_at
```

**portfolio_social_links**
```
id, portfolio_id, platform, url, sort_order, created_at, updated_at
```

**portfolio_skills**
```
id, portfolio_id, name, proficiency (beginner/intermediate/advanced/expert),
sort_order, created_at, updated_at
```

**portfolio_experiences**
```
id, portfolio_id, company, role, description, start_date, end_date,
is_current, sort_order, created_at, updated_at
```

**portfolio_educations**
```
id, portfolio_id, institution, degree, field, start_year, end_year,
sort_order, created_at, updated_at
```

**portfolio_projects**
```
id, portfolio_id, project_id (nullable — links to LaraVue project),
title, description, image_path, tech_stack (json), live_url, source_url,
sort_order, created_at, updated_at
```

**portfolio_testimonials**
```
id, portfolio_id, author_name, author_role, author_company,
content, author_photo_url, sort_order, created_at, updated_at
```

**portfolio_custom_sections**
```
id, portfolio_id, title, content, sort_order, created_at, updated_at
```

**portfolio_custom_domains**
```
id, portfolio_id, domain (unique), type (root/subdomain),
status (pending/verified/failed), verified_at, last_checked_at,
dns_error (text, nullable), created_at, updated_at
```

**portfolio_templates**
```
id, name, slug (unique), description, preview_image_path,
is_active, is_premium, sort_order, created_at, updated_at
```

---

## 8. API Endpoints

### Portfolio Management (auth required)
```
GET    /api/v1/portfolio                    — Get current user's portfolio (or null)
POST   /api/v1/portfolio                    — Create portfolio (pick subdomain)
PUT    /api/v1/portfolio                    — Update portfolio data
DELETE /api/v1/portfolio                    — Delete portfolio (soft delete)

PUT    /api/v1/portfolio/template           — Switch template
POST   /api/v1/portfolio/publish            — Publish portfolio
POST   /api/v1/portfolio/unpublish          — Unpublish portfolio
GET    /api/v1/portfolio/preview            — Preview portfolio (returns rendered HTML)

GET    /api/v1/portfolio/subdomain/check    — Check subdomain availability
```

### Portfolio Sections (auth required)
```
PUT    /api/v1/portfolio/social-links       — Bulk update social links
PUT    /api/v1/portfolio/skills             — Bulk update skills
PUT    /api/v1/portfolio/experience         — Bulk update experience
PUT    /api/v1/portfolio/education          — Bulk update education
PUT    /api/v1/portfolio/projects           — Bulk update projects (reorder, add, remove)
PUT    /api/v1/portfolio/testimonials       — Bulk update testimonials
PUT    /api/v1/portfolio/custom-sections    — Bulk update custom sections

POST   /api/v1/portfolio/upload/photo       — Upload profile photo
POST   /api/v1/portfolio/upload/resume      — Upload resume PDF
POST   /api/v1/portfolio/upload/project-image — Upload project image
```

### Plans & Payments (auth required)
```
GET    /api/v1/portfolio/plans              — List available plans
POST   /api/v1/portfolio/orders             — Create order (select plan, apply coupon)
POST   /api/v1/portfolio/orders/verify      — Verify payment after Razorpay checkout
GET    /api/v1/portfolio/subscription       — Get current subscription status
POST   /api/v1/portfolio/coupons/validate   — Validate a coupon code for a plan
```

### Webhook
```
POST   /api/v1/webhooks/razorpay           — Razorpay payment webhook
```

### Public Portfolio (no auth)
```
GET    {subdomain}.laravue.in              — Rendered portfolio page (Blade, not API)
```

### Admin Panel (admin auth required)
```
GET    /api/v1/admin/portfolios            — List all portfolios (paginated, filterable)
GET    /api/v1/admin/portfolios/{id}       — View portfolio details
PUT    /api/v1/admin/portfolios/{id}       — Update portfolio (admin override)
DELETE /api/v1/admin/portfolios/{id}       — Delete portfolio

GET    /api/v1/admin/subscriptions         — List all subscriptions
POST   /api/v1/admin/subscriptions/{id}/refund — Refund a subscription

GET    /api/v1/admin/plans                 — List plans
POST   /api/v1/admin/plans                 — Create plan
PUT    /api/v1/admin/plans/{id}            — Update plan
DELETE /api/v1/admin/plans/{id}            — Delete plan (soft)

GET    /api/v1/admin/coupons               — List coupons
POST   /api/v1/admin/coupons               — Create coupon
PUT    /api/v1/admin/coupons/{id}          — Update coupon
DELETE /api/v1/admin/coupons/{id}          — Delete coupon

GET    /api/v1/admin/templates             — List templates
POST   /api/v1/admin/templates             — Create template record
PUT    /api/v1/admin/templates/{id}        — Update template
DELETE /api/v1/admin/templates/{id}        — Delete template

GET    /api/v1/admin/orders                — List all orders
GET    /api/v1/admin/dashboard/stats       — Revenue, active portfolios, plan distribution
```

---

## 9. Admin Panel

### Implementation
- Admin panel is a set of Vue pages under `/admin/*` routes
- Protected by an `is_admin` boolean field on the users table (new migration)
- Admin middleware checks `auth:api` + `user.is_admin === true`
- No separate admin app — same Vue SPA with admin routes

### Admin Dashboard
- Total active portfolios
- Revenue this month / all time
- Plan distribution (pie chart)
- Recent orders
- Expiring subscriptions (next 7 days)

### Admin Capabilities
- CRUD plans, coupons, templates
- View/search all portfolios
- Force-unpublish a portfolio (content violation)
- Refund a subscription (triggers Razorpay refund)
- View all orders with payment status
- Export orders as CSV

---

## 10. Frontend Pages (Vue)

### New Routes
```
/portfolio                  — Portfolio dashboard (create or manage)
/portfolio/editor           — Portfolio editor (multi-step or tabbed form)
/portfolio/templates        — Template gallery with previews
/portfolio/plans            — Plan selection + payment
/portfolio/preview          — Live preview of portfolio
/portfolio/domain           — Custom domain settings (add, verify, DNS instructions, affiliate CTA)

/admin                      — Admin dashboard
/admin/portfolios           — Portfolio management
/admin/subscriptions        — Subscription management
/admin/plans                — Plan management
/admin/coupons              — Coupon management
/admin/templates            — Template management
/admin/orders               — Order history
/admin/custom-domains       — Custom domain management
```

### Portfolio Editor UX
- Tabbed interface: Profile → Skills → Experience → Education → Projects → Testimonials → Custom Sections → SEO → Domain
- Auto-save on tab switch (debounced)
- Live preview panel on the right (desktop) or toggle button (mobile)
- Drag-and-drop reordering for projects, skills, experience
- Image upload with crop/resize
- "Import from LaraVue profile" button to pre-fill data
- Domain tab shows: current subdomain URL, custom domain setup (Pro/Annual only), DNS instructions, verification status, and domain purchase affiliate CTA

---

## 11. Implementation Phases

### Phase 1 — Foundation (Week 1-2)
- Database migrations (all tables)
- Models, enums, relationships
- Admin middleware + is_admin migration
- Portfolio CRUD API
- Subdomain middleware
- Template system (Blade rendering)
- 3 starter templates

### Phase 2 — Payments (Week 3)
- Razorpay integration (orders, verification, webhooks)
- Plan management (seeder + admin CRUD)
- Coupon system
- Subscription lifecycle (activate, expire, grace period)

### Phase 3 — Frontend (Week 4-5)
- Portfolio editor (Vue, tabbed form)
- Template gallery + preview
- Plan selection + Razorpay checkout modal
- Portfolio dashboard

### Phase 4 — Admin Panel (Week 6)
- Admin dashboard with stats
- Portfolio, subscription, order management
- Plan + coupon CRUD
- Template management

### Phase 5 — Polish (Week 7)
- Scheduled commands (expire subscriptions, grace period cleanup)
- Email notifications (subscription created, expiring soon, expired)
- SEO meta tags for portfolio pages
- Performance (caching portfolio data, CDN for template assets)
- Edge case testing

---

## 12. Environment Variables (New)

```env
# Razorpay
RAZORPAY_KEY_ID=
RAZORPAY_KEY_SECRET=
RAZORPAY_WEBHOOK_SECRET=

# Portfolio
PORTFOLIO_DOMAIN=laravue.in
PORTFOLIO_SERVER_IP=                          # Hostinger server IP — used in DNS instructions for custom domains
PORTFOLIO_GRACE_PERIOD_DAYS=7
PORTFOLIO_DATA_RETENTION_DAYS=90

# Domain Affiliate (shown on custom domain setup page)
DOMAIN_AFFILIATE_URL=https://hostinger.com?ref=laravue    # Replace with actual affiliate link
DOMAIN_AFFILIATE_COUPON=LARAVUE                            # Coupon code shown to users
```

---

## 13. Security Considerations

- **Subdomain injection**: Validate subdomain strictly (alphanumeric + hyphens only, 3-30 chars)
- **XSS in portfolio content**: Sanitize all user HTML (bio, descriptions) with HTMLPurifier before rendering in Blade
- **File uploads**: Validate MIME types server-side; images (jpg, png, webp, max 2MB), resume (pdf, max 5MB)
- **Razorpay signature verification**: Always verify on backend; never trust frontend-only confirmation
- **Rate limiting**: Order creation (5/hour), coupon validation (10/minute)
- **Admin access**: Double-check `is_admin` on every admin endpoint; no role escalation via API
- **Portfolio data isolation**: Users can only access/edit their own portfolio; admin can access all
- **Coupon brute force**: Rate limit coupon validation; log failed attempts
- **Custom domain hijacking**: Domain verification via DNS ensures only the domain owner can connect it. The unique constraint prevents two users from claiming the same domain.
- **Domain squatting**: Users cannot add domains they don't control — verification will fail. Pending domains auto-expire after reminders.

---

## 14. Scheduled Commands

| Command | Schedule | Purpose |
|---------|----------|---------|
| `portfolio:expire-subscriptions` | Daily at midnight | Move expired subscriptions from active → expired; start grace period |
| `portfolio:end-grace-periods` | Daily at midnight | Unpublish portfolios past grace period |
| `portfolio:cleanup-data` | Weekly | Soft-delete portfolio data past retention period |
| `portfolio:send-expiry-reminders` | Daily at 9 AM | Email users whose subscription expires in 7, 3, 1 days |
| `portfolio:verify-domains` | Every 6 hours | Auto-verify pending custom domains; detect broken verified domains |
| `portfolio:remind-pending-domains` | Daily at 10 AM | Email users with pending domains older than 7 days |

---

## 15. Notifications & Emails

| Trigger | Email | In-App Notification |
|---------|-------|---------------------|
| Subscription activated | ✅ Welcome + portfolio URL | ✅ |
| Subscription expiring (7d, 3d, 1d) | ✅ Renewal reminder | ✅ |
| Subscription expired | ✅ Portfolio offline notice | ✅ |
| Payment failed | ✅ Retry payment link | ✅ |
| Refund processed | ✅ Refund confirmation | ✅ |
| Portfolio published | — | ✅ |
| Admin force-unpublished | ✅ Reason provided | ✅ |
| Custom domain verified | ✅ Domain is live | ✅ |
| Custom domain verification failed (7d) | ✅ DNS setup reminder | ✅ |
| Custom domain broken (was verified, DNS changed) | ✅ DNS issue alert | ✅ |
