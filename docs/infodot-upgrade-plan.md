# InfoDot — Upgrade & Ecosystem Hub Plan

**Platform:** BluPin Incorporated / SK Digital  
**Goal:** Upgrade InfoDot to the target stack, ship it as the ecosystem hub, then connect Dot.Files and the remaining Dot platforms.  
**Date:** June 2026

---

## What We're Working With

InfoDot is substantially built. The feature set is real and documented — Solutions hub, Q&A, threaded comments, polymorphic likes, user profiles, social graph, team management, real-time notifications, file storage, and full-text search. The problem is the underlying stack is two major versions behind the target.

**Current stack vs target:**

| Layer | Current | Target |
|---|---|---|
| Framework | Laravel 10.50 | Laravel 12.x |
| Language | PHP 8.3 | PHP 8.2+ (8.4 recommended) |
| Frontend reactivity | Livewire 2.12 | Livewire 3.x |
| Auth scaffolding | Jetstream 3.3 | Jetstream 5.x |
| Database | MySQL + full-text indexes | PostgreSQL 16+ |
| Asset build | Laravel Mix (webpack) | Vite 7.x |
| WebSockets | BeyondCode Laravel WebSockets | Laravel Reverb |
| Component library | DaisyUI 1.25 | DaisyUI 5.x |
| JS runtime | Vue.js 2 + Alpine.js 3 | Alpine.js 3 (Vue removed) |
| Real-time client | Pusher.js 7 | Laravel Echo + Pusher.js |
| Payments | Not present | Stripe via Laravel Cashier |
| Storage | Local / not configured | AWS S3 via Flysystem |
| Error monitoring | Not present | Sentry |
| Static analysis | Not present | PHPStan + Psalm |
| Testing | PHPUnit (older) | PHPUnit 11 |
| Charts | Not present | ApexCharts 5.x + Chart.js 4.x |
| Maps | Not present | Leaflet 1.9.x |

The most disruptive change is **Livewire 2 → 3** — it's a near-complete rewrite of the component layer. Every Livewire component needs to be rewritten, not just updated. Everything else is manageable package-by-package.

---

## The Ecosystem Strategy

InfoDot is the **hub**. Every other Dot platform (Dot.Files, ChartSense, mines, Dot.Press) plugs into InfoDot's identity and team layer. A user logs into InfoDot once. From there they access any Dot platform without logging in again.

The mechanism: **Laravel Sanctum API tokens**. InfoDot issues a token. Each satellite platform (Dot.Files, etc.) accepts that token via the `Authorization: Bearer` header. No separate logins. InfoDot's `teams` table becomes the shared team construct across the ecosystem.

The visual hook: a **Dot Switcher** — a persistent nav component in InfoDot's header that links out to each connected platform, passing the user's token as a URL fragment or cookie for seamless handoff.

---

## Phase 1 — Stack Foundation (Weeks 1–3)

**Goal:** Get InfoDot running cleanly on the target stack with all existing functionality intact. No new features. No ecosystem wiring yet. Just a clean upgrade.

**Branch:** `upgrade/laravel-12`

### 1a. Composer & PHP upgrade

```bash
# Update composer.json to target stack
composer require laravel/framework:^12.0
composer require laravel/jetstream:^5.0
composer require livewire/livewire:^3.0
composer require laravel/reverb:@beta        # replaces BeyondCode WebSockets
composer require laravel/sanctum:^4.0
composer require laravel/scout:^10.0
```

**Remove:**
```bash
composer remove beyondcode/laravel-websockets
```

Key `composer.json` changes:
- `php` constraint → `^8.2`
- `laravel/framework` → `^12.0`
- All Jetstream/Fortify/Sanctum versions bumped accordingly
- PHPUnit → `^11.0`
- Add `phpstan/phpstan` and `vimeo/psalm` for static analysis

### 1b. Vite migration (replaces Laravel Mix)

Remove `webpack.mix.js` and `webpack.config.js`. Create `vite.config.js`:

```js
import { defineConfig } from 'vite'
import laravel from 'laravel-vite-plugin'

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
    ],
})
```

In all Blade layouts replace:
```html
<!-- Old Mix -->
<link rel="stylesheet" href="{{ mix('css/app.css') }}">
<script src="{{ mix('js/app.js') }}" defer></script>

<!-- New Vite -->
@vite(['resources/css/app.css', 'resources/js/app.js'])
```

Update `package.json` — remove `laravel-mix`, add `vite` and `laravel-vite-plugin`:
```json
{
    "devDependencies": {
        "vite": "^7.0",
        "laravel-vite-plugin": "^1.0",
        "@tailwindcss/vite": "^4.0"
    }
}
```

### 1c. Database: MySQL → PostgreSQL

This is the most careful step. The schema itself is straightforward. The gotcha is **full-text search** — InfoDot currently uses MySQL FULLTEXT indexes which are not portable to PostgreSQL.

**Migration changes needed** — find every migration that uses:
```php
$table->fullText(['column_a', 'column_b']);
```
And replace with PostgreSQL-compatible full-text using Laravel Scout (see Phase 1e).

Update `.env`:
```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=infodot
DB_USERNAME=postgres
DB_PASSWORD=your_password
```

Update `config/database.php` default connection:
```php
'default' => env('DB_CONNECTION', 'pgsql'),
```

**Data type notes for PostgreSQL compatibility:**
- `json` columns → fine in PostgreSQL
- `longText` → fine (maps to `text`)
- `enum` → PostgreSQL handles enums differently; consider using string with validation instead
- `unsignedBigInteger` → fine (maps to `bigint`)
- Soft deletes → fine

### 1d. BeyondCode WebSockets → Laravel Reverb

Remove the BeyondCode WebSocket server and replace with Laravel Reverb (first-party, ships with Laravel 11+).

```bash
php artisan reverb:install
```

Update `.env`:
```env
BROADCAST_CONNECTION=reverb
REVERB_APP_ID=your-app-id
REVERB_APP_KEY=your-app-key
REVERB_APP_SECRET=your-app-secret
REVERB_HOST=localhost
REVERB_PORT=8080
REVERB_SCHEME=http
```

Update `config/broadcasting.php` — remove BeyondCode driver entry, ensure `reverb` is the default.

Replace any `websockets:serve` artisan references in your deployment scripts with:
```bash
php artisan reverb:start
```

Update `resources/js/bootstrap.js` — Echo config now points to Reverb:
```js
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;
window.Echo = new Echo({
    broadcaster: 'reverb',
    key: import.meta.env.VITE_REVERB_APP_KEY,
    wsHost: import.meta.env.VITE_REVERB_HOST ?? `ws.${location.hostname}`,
    wsPort: import.meta.env.VITE_REVERB_PORT ?? 80,
    wssPort: import.meta.env.VITE_REVERB_PORT ?? 443,
    forceTLS: (import.meta.env.VITE_REVERB_SCHEME ?? 'https') === 'https',
    enabledTransports: ['ws', 'wss'],
});
```

### 1e. Laravel Scout + PostgreSQL full-text search

Since MySQL FULLTEXT indexes are gone, we lean fully into Laravel Scout. For PostgreSQL, use the `teamtnt/laravel-scout-tntsearch-driver` or switch to **Meilisearch** (recommended for production — free self-hosted, fast, typo-tolerant).

For local dev, TNTSearch (file-based) is fine. For production, Meilisearch.

Add the `Searchable` trait to models that currently have fulltext indexes:
- `User` → searchable on `name`, `email`
- `Solution` → searchable on `solution_title`, `solution_description`, `tags`
- `Question` → searchable on `question`, `description`
- `Comment` → searchable on `body`

```php
// app/Models/Solution.php
use Laravel\Scout\Searchable;

class Solution extends Model
{
    use Searchable;

    public function toSearchableArray(): array
    {
        return [
            'solution_title'       => $this->solution_title,
            'solution_description' => $this->solution_description,
            'tags'                 => $this->tags,
        ];
    }
}
```

Remove all `$table->fullText(...)` calls from migrations — they'll fail on PostgreSQL and are now handled by Scout.

### 1f. DaisyUI 1 → 5

This is a breaking change — DaisyUI 5 renamed and restructured many components. You'll need to audit every Blade template for DaisyUI class names.

**Key renames in DaisyUI 5:**
- `btn-primary`, `btn-secondary` → same (safe)
- `alert-success`, `alert-error` → `alert` with `alert-success` variant (check)
- `card-body`, `card-title` → largely same
- `modal` → API changed significantly
- `dropdown` → class structure changed
- `navbar` → restructured

**Approach:** Do a global find for `daisyui` class names and compare against the DaisyUI 5 migration guide. Most layout classes are safe; interactive components (modal, drawer, dropdown) need the most attention.

Update `package.json`:
```json
"daisyui": "^5.0"
```

Update `tailwind.config.js` — DaisyUI 5 uses a different plugin import:
```js
import daisyui from 'daisyui'

export default {
    plugins: [daisyui],
    daisyui: {
        themes: ['light', 'dark'],
    },
}
```

### 1g. Vue.js 2 removal + Alpine.js 3 consolidation

Vue.js 2 is end-of-life. InfoDot's README shows it was used alongside Livewire — in most Livewire apps, Alpine.js handles the interactivity that Vue was doing. Since the target stack is Livewire 3 + Alpine.js 3 only, Vue can be removed entirely.

**Steps:**
1. Audit all `.vue` files and `<template>` blocks in Blade views
2. Rewrite any Vue components as Alpine.js `x-data` components or Livewire components
3. Remove `vue` and `@vue/compiler-sfc` from `package.json`

Alpine.js 3 should already be bundled by Livewire 3 (it pulls it in automatically), so you may not need to import it separately.

---

## Phase 2 — Livewire 2 → 3 Component Rewrite (Weeks 2–4)

This is the biggest chunk of work. **Livewire 3 is not a backwards-compatible upgrade** — the API changed significantly.

### What changed between Livewire 2 and 3

| Concept | Livewire 2 | Livewire 3 |
|---|---|---|
| Component registration | Manual `Livewire::component()` | Auto-discovered |
| Public properties | `public $name;` | `public string $name = '';` (typed) |
| Model binding | `wire:model` (deferred by default) | `wire:model` (deferred) / `wire:model.live` (real-time) |
| Events | `$this->emit('event')` | `$this->dispatch('event')` |
| Event listeners | `protected $listeners` | `#[On('event')]` attribute |
| JavaScript hooks | `@livewire` directives | `@livewire` + new JS hooks |
| Lazy loading | Not built in | `#[Lazy]` attribute |
| Locked properties | Not built in | `#[Locked]` attribute |
| Computed properties | `$this->getPropertyProperty()` | `#[Computed]` attribute |
| Testing | `Livewire::test()` | `Livewire::test()` (mostly same) |

### Components to rewrite in InfoDot

Based on the feature docs and file structure:

**`Search` component** — the real-time universal search
```php
// Livewire 2 (old)
class Search extends Component
{
    public $query = '';
    public $highlightIndex = 0;
    protected $updatedQuery = 'searchContent';

    public function searchContent() { ... }
    public function clearSearch() { ... }
    public function incrementHighlight() { ... }
    public function decrementHighlight() { ... }
}

// Livewire 3 (new)
class Search extends Component
{
    public string $query = '';
    public int $highlightIndex = 0;

    #[Computed]
    public function results(): array
    {
        if (strlen($this->query) < 2) return [];
        return [
            'solutions' => Solution::search($this->query)->get(),
            'questions' => Question::search($this->query)->get(),
            'users'     => User::search($this->query)->get(),
        ];
    }

    public function clearSearch(): void
    {
        $this->query = '';
        $this->highlightIndex = 0;
    }
}
```

**`NavigationDropdown` component** (team switcher)
```php
// Livewire 3
class NavigationDropdown extends Component
{
    public function switchTeam(int $teamId): void
    {
        $team = auth()->user()->teams()->findOrFail($teamId);
        auth()->user()->switchTeam($team);
        $this->redirect(route('dashboard'));
    }
}
```

**`FileBrowser` component** (file/folder browser — exists in InfoDot schema, overlaps with Dot.Files)
- Rewrite using Livewire 3's `wire:model.live` for reactive folder navigation
- Use `#[Computed]` for the folder contents query

**Event emissions — find and replace all:**
```php
// Old (find these)
$this->emit('eventName', $data);
$this->emitTo('ComponentName', 'event', $data);
$this->emitSelf('event');

// New (replace with)
$this->dispatch('eventName', data: $data);
$this->dispatch('eventName')->to('ComponentName');
$this->dispatch('eventName')->self();
```

**Listeners — find and replace all:**
```php
// Old
protected $listeners = ['eventName' => 'handleEvent'];

// New
#[On('eventName')]
public function handleEvent($data): void { ... }
```

### Blade template changes

Livewire 3 changes how components are included:
```html
<!-- Old (still works but deprecated) -->
@livewire('search')

<!-- New (preferred) -->
<livewire:search />
```

`wire:model` changes:
```html
<!-- Old: deferred by default, .defer was redundant -->
<input wire:model="query">

<!-- New: deferred by default, use .live for real-time -->
<input wire:model.live="query">         <!-- real-time (debounced) -->
<input wire:model.live.debounce.500ms="query"> <!-- debounced 500ms -->
<input wire:model="name">               <!-- still deferred (on form submit) -->
```

---

## Phase 3 — Feature Completion & Polish (Weeks 4–6)

Once the stack is upgraded and all components rewritten, fill the gaps between the documented feature set and what's actually wired up.

### 3a. Payments — Stripe via Laravel Cashier

InfoDot's roadmap mentions a marketplace for solutions and premium subscriptions. Wire Cashier now so the infrastructure is ready.

```bash
composer require laravel/cashier
php artisan vendor:publish --tag="cashier-migrations"
php artisan migrate
```

Add `Billable` trait to `User` model. Set up subscription plans and webhook handler. This doesn't need to be live at launch — just wired and dormant.

### 3b. File Storage — AWS S3

```env
FILESYSTEM_DISK=s3
AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=af-south-1
AWS_BUCKET=infodot-files
```

Update `config/filesystems.php` — set S3 as the cloud disk. The existing file upload logic should work without changes since it uses `Storage::disk()`.

### 3c. Sentry error monitoring

```bash
composer require sentry/sentry-laravel
php artisan sentry:publish --dsn=your-dsn
```

Add to `bootstrap/app.php`:
```php
->withExceptions(function (Exceptions $exceptions) {
    Integration::handles($exceptions);
})
```

### 3d. Static analysis pass

```bash
composer require --dev phpstan/phpstan larastan/larastan vimeo/psalm
```

Run `./vendor/bin/phpstan analyse` at level 5 initially, fix errors, work up to level 8 before MVP launch. This catches type errors that would surface in production.

### 3e. Charts & Maps

InfoDot's roadmap mentions an analytics dashboard. Add the chart libraries to `package.json`:
```json
"apexcharts": "^5.0",
"chart.js": "^4.0",
"leaflet": "^1.9.0",
"leaflet-draw": "^1.0"
```

These can be imported per-page using Vite's dynamic imports — don't bundle them globally.

### 3f. Test suite upgrade

PHPUnit 11 has some breaking changes from older versions. Update `phpunit.xml` to the new format:
```xml
<phpunit xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
         xsi:noNamespaceSchemaLocation="vendor/phpunit/phpunit/phpunit.xsd"
         bootstrap="vendor/autoload.php"
         colors="true">
    <testsuites>
        <testsuite name="Unit">
            <directory suffix="Test.php">./tests/Unit</directory>
        </testsuite>
        <testsuite name="Feature">
            <directory suffix="Test.php">./tests/Feature</directory>
        </testsuite>
    </testsuites>
    <source>
        <include>
            <directory suffix=".php">./app</directory>
        </include>
    </source>
</phpunit>
```

Expand beyond auth tests to cover: solution creation, Q&A flow, team invitations, search, and file uploads. Aim for 70%+ coverage before MVP.

---

## Phase 4 — Ecosystem Hub Layer (Weeks 6–8)

InfoDot now runs clean on the target stack. This phase wires it as the central hub for the Dot ecosystem.

### 4a. Shared auth API (Sanctum tokens for satellite apps)

InfoDot issues tokens. Satellite apps (Dot.Files, ChartSense, mines) consume them.

In InfoDot — create a dedicated token endpoint:
```php
// routes/api.php
Route::post('/ecosystem/token', [EcosystemTokenController::class, 'issue'])
    ->middleware('auth:sanctum');
```

```php
// app/Http/Controllers/EcosystemTokenController.php
class EcosystemTokenController extends Controller
{
    public function issue(Request $request): JsonResponse
    {
        $token = $request->user()->createToken(
            name: 'ecosystem-access',
            abilities: ['ecosystem:read', 'ecosystem:write'],
            expiresAt: now()->addHours(24),
        );

        return response()->json([
            'token'      => $token->plainTextToken,
            'expires_at' => now()->addHours(24)->toIso8601String(),
            'user'       => [
                'id'        => $request->user()->id,
                'name'      => $request->user()->name,
                'email'     => $request->user()->email,
                'teams'     => $request->user()->teams->pluck('name', 'id'),
                'team_id'   => $request->user()->currentTeam?->id,
            ],
        ]);
    }
}
```

**In each satellite app (e.g. Dot.Files):**
Add a middleware that accepts InfoDot tokens:
```php
// Sanctum config in Dot.Files points to InfoDot's DB or calls InfoDot's /api/user endpoint
// Simplest approach: shared PostgreSQL database between InfoDot and Dot.Files
// so Sanctum token lookups work natively.
```

The simplest production architecture: **shared database** for `personal_access_tokens` and `users`. Both InfoDot and Dot.Files point to the same PostgreSQL instance. Sanctum lookups work transparently.

### 4b. Dot Switcher navigation component

A `<livewire:dot-switcher />` component that lives in the InfoDot header. Shows all connected Dot platforms and deep-links with the user's active token.

```php
// app/Livewire/DotSwitcher.php
class DotSwitcher extends Component
{
    #[Computed]
    public function platforms(): array
    {
        return [
            ['name' => 'Dot.Files',   'url' => config('ecosystem.dotfiles_url'),   'icon' => 'folder'],
            ['name' => 'ChartSense',  'url' => config('ecosystem.chartsense_url'),  'icon' => 'chart-bar'],
            ['name' => 'mines',       'url' => config('ecosystem.mines_url'),       'icon' => 'building'],
            ['name' => 'Dot.Press',   'url' => config('ecosystem.dotpress_url'),    'icon' => 'presentation'],
        ];
    }

    public function launch(string $platform): void
    {
        $token = auth()->user()
            ->createToken('ecosystem-handoff', expiresAt: now()->addMinutes(5))
            ->plainTextToken;

        $url = collect($this->platforms)
            ->firstWhere('name', $platform)['url'];

        $this->redirect("{$url}/auth/ecosystem?token={$token}");
    }
}
```

In each satellite app, a `/auth/ecosystem` route accepts the short-lived handoff token, verifies it against InfoDot's tokens table, and sets a session.

### 4c. config/ecosystem.php

```php
// config/ecosystem.php
return [
    'dotfiles_url'   => env('ECOSYSTEM_DOTFILES_URL',   'https://files.infodot.app'),
    'chartsense_url' => env('ECOSYSTEM_CHARTSENSE_URL', 'https://charts.infodot.app'),
    'mines_url'      => env('ECOSYSTEM_MINES_URL',      'https://mines.infodot.app'),
    'dotpress_url'   => env('ECOSYSTEM_DOTPRESS_URL',   'https://press.infodot.app'),
];
```

### 4d. Ecosystem dashboard widget in InfoDot

Add a section to the InfoDot dashboard showing the user's connected Dot platforms and quick-access cards. This becomes the "mission control" view of the ecosystem.

---

## Phase 5 — Dot.Files Integration (Weeks 8–10)

Dot.Files is already on the target stack (Laravel 12 / PHP 8.4 / Livewire 3). The work here is integration, not upgrade.

### 5a. Point Dot.Files at the shared database

Dot.Files currently uses a separate database. Update its `.env` to point to InfoDot's PostgreSQL instance. Both apps now share:
- `users` table
- `teams` + `team_user` tables  
- `personal_access_tokens` table

Dot.Files keeps its own `objects`, `files`, and `folders` tables — these stay in the shared DB but are namespaced by `team_id`.

### 5b. Handle Dot.Files' 31 open PRs

Before the integration, do a PR triage pass:
1. Group PRs by type: feature / bugfix / dependency update / WIP
2. Close any that are superseded or stale
3. Merge clean bugfixes and dependency updates
4. Feature PRs: evaluate against Now/Next/Later — only merge what belongs in MVP

### 5c. Dot.Files ecosystem auth endpoint

Add `/auth/ecosystem` route to Dot.Files that accepts the InfoDot handoff token:
```php
// routes/web.php in Dot.Files
Route::get('/auth/ecosystem', [EcosystemAuthController::class, 'handle'])
    ->name('ecosystem.auth');
```

```php
// app/Http/Controllers/EcosystemAuthController.php
class EcosystemAuthController extends Controller
{
    public function handle(Request $request): RedirectResponse
    {
        $token = $request->query('token');
        
        // Verify token against shared personal_access_tokens table
        $accessToken = PersonalAccessToken::findToken($token);
        
        abort_if(!$accessToken || $accessToken->expires_at->isPast(), 403);
        
        $user = $accessToken->tokenable;
        $accessToken->delete(); // One-time use
        
        Auth::login($user);
        
        return redirect()->route('dashboard');
    }
}
```

---

## Deployment Architecture

```
infodot.app          → InfoDot (hub)
files.infodot.app    → Dot.Files
charts.infodot.app   → ChartSense (future)
mines.infodot.app    → mines (future)
press.infodot.app    → Dot.Press (future)

Shared:
- PostgreSQL 16 instance
- Redis (sessions, cache, queues)
- AWS S3 (file storage)
- Meilisearch (full-text search)
- Reverb (WebSocket server — runs on InfoDot, other apps connect to it)
```

**Process manager (production):**
```bash
# Supervisor processes
php artisan queue:work --sleep=3 --tries=3   # Queue worker
php artisan reverb:start                      # WebSocket server
php artisan schedule:run                      # Scheduler (via cron: * * * * *)
```

---

## Now / Next / Later — the sorted task board

### NOW (this cycle — Weeks 1–6)

| Task | Why it's Now | Done when |
|---|---|---|
| Create `upgrade/laravel-12` branch | Everything depends on this | Branch exists |
| Upgrade composer.json to Laravel 12 + Livewire 3 | Foundation | `composer install` clean |
| Switch Laravel Mix → Vite | Asset pipeline | `npm run dev` works |
| Update DB connection MySQL → PostgreSQL | Schema foundation | Migrations run clean |
| Remove BeyondCode, install Reverb | Real-time foundation | `reverb:start` works |
| Upgrade DaisyUI 1 → 5 | UI renders correctly | No broken component classes |
| Remove Vue.js 2, consolidate to Alpine.js | Simplify frontend | No Vue imports remain |
| Rewrite Search Livewire component (v2 → v3) | Core UX | Real-time search works |
| Rewrite NavigationDropdown component | Team switching | Team switcher works |
| Rewrite FileBrowser component | File management | File browser works |
| Replace all `$this->emit` with `$this->dispatch` | Livewire 3 compat | No emit() calls remain |
| Replace all `$listeners` with `#[On]` attributes | Livewire 3 compat | No $listeners remain |
| Replace full-text MySQL indexes with Scout | Search works on PostgreSQL | Scout search returns results |
| PHPUnit 11 upgrade + fix failing tests | Test suite passes | All tests green |
| PHPStan level 5 pass | Code quality | No level-5 errors |
| Sentry integration | Error visibility | Errors appear in Sentry |

### NEXT (once InfoDot MVP is live)

| Task | Why it's Next |
|---|---|
| Shared auth API (Sanctum ecosystem tokens) | Connects the ecosystem |
| Dot Switcher Livewire component | Ecosystem navigation |
| Dot.Files PR triage + merge | Cleans up Dot.Files |
| Dot.Files shared database integration | Single user identity |
| Dot.Files ecosystem auth endpoint | SSO for Dot.Files |
| Stripe / Cashier wiring (dormant) | Revenue infrastructure |
| S3 file storage config | Production file storage |
| Analytics dashboard (ApexCharts) | Business insight for users |

### LATER (after Dot.Files is live)

| Task | Trigger |
|---|---|
| ChartSense → Livewire 3 rewrite (currently Vite/separate frontend) | After Dot.Files ships |
| mines MVP completion | After InfoDot + Dot.Files stable |
| Dot.Press stack alignment (Vue → Livewire or stay Vue 3) | Architecture decision needed |
| Marketplace for solutions (Stripe subscriptions) | After 100+ active users |
| Mobile app (iOS/Android) | After web platform stable |
| Meilisearch production setup | When TNTSearch shows limits |

---

## The One Decision

The shared database approach (InfoDot + Dot.Files + future platforms all on one PostgreSQL instance) is the simplest path to ecosystem auth. The alternative is a central auth service (OAuth server). The shared DB is right for where you are now — you're building the ecosystem, not selling it to other developers. When you have external developers building on the Dot platform, revisit and add an OAuth layer. For now: one DB, Sanctum tokens, shared teams.

---

## Risk Register

| Risk | Likelihood | Mitigation |
|---|---|---|
| Livewire 3 rewrite takes longer than estimated | High | Plan for 2–3x the time. Rewrite one component at a time, keep v2 working on a separate branch until all components pass. |
| DaisyUI 5 class renames break UI | Medium | Audit all Blade templates before switching. Use browser screenshots to verify each page. |
| PostgreSQL FULLTEXT migration gaps | Medium | Scout handles it. Run `php artisan scout:import` for each model after migration. |
| Dot.Files PRs contain conflicting changes | Medium | Triage PRs before touching the shared DB. Resolve conflicts on feature branches. |
| Reverb WebSocket stability in production | Low | Laravel Reverb is first-party and well-tested. Keep Pusher.com as a fallback config. |

---

*Built by SK Digital / BluPin Incorporated*  
*Plan authored: June 2026*

---

---

# Part 2 — The 16 Dot Platforms: Full Ecosystem Build Plan

This section covers every Dot platform repository. Each one is analysed against its current state, given a clear ecosystem role, and assigned a phased build plan that feeds into the InfoDot hub.

---

## Platform Inventory at a Glance

| Platform | Description | Current State | Stack Status | Build Tier |
|---|---|---|---|---|
| **Dot.Files** | Cloud file manager | Laravel 12 / Livewire 3 / 54 commits / 31 open PRs | ✅ On target | 1 — Integrate |
| **Dot.Agents** | Enterprise AI workforce | Laravel 12 / Livewire 3 / 59 commits / 298 tests | ✅ On target | 1 — Integrate |
| **Dot.docs** | AI collaborative documents | Laravel 12 / Livewire 3 / 19 commits | ✅ On target | 1 — Complete + Integrate |
| **Dot.Forms** | AI form builder | Laravel 13 / Livewire 3 / 10 commits | ✅ On target | 1 — Complete + Integrate |
| **Dot.Sheet** | AI spreadsheet | Laravel 13 / Livewire 3 / 5 commits | ⚠️ Pusher → Reverb | 1 — Complete + Integrate |
| **Dot.Engage** | Chat, contracts, video | Laravel 13 / Livewire 3 / full scaffold | ✅ On target | 2 — Build + Integrate |
| **Dot.Press** | CMS / publishing | Vue-based / early | ⚠️ Vue → Livewire decision | 2 — Rewrite/Build |
| **Dot.Ehail** | e-Hailing for business | HTML/CSS placeholder | 🔴 Needs Laravel | 3 — Build from scratch |
| **Dot.Projects** | AI project management | HTML placeholder | 🔴 Needs Laravel | 3 — Build from scratch |
| **Dot.Tasks** | AI task management | HTML placeholder | 🔴 Needs Laravel | 3 — Build from scratch |
| **Dot.Tutor** | Tutoring marketplace | HTML/CSS placeholder | 🔴 Needs Laravel | 3 — Build from scratch |
| **Dot.Finance** | AI financial management | HTML/CSS placeholder | 🔴 Needs Laravel | 3 — Build from scratch |
| **Dot.Design** | AI design platform | HTML/CSS placeholder | 🔴 Needs Laravel | 3 — Build from scratch |
| **Dot.Central** | AI agents for specialist skills | HTML/CSS placeholder | 🔴 Needs Laravel | 3 — Build from scratch |
| **Dot.Auction** | Online auction platform | HTML/CSS placeholder | 🔴 Needs Laravel | 3 — Build from scratch |
| **Dot.Emall** | AI online e-mall | HTML/CSS placeholder | 🔴 Needs Laravel | 3 — Build from scratch |

**Three-tier classification:**
- **Tier 1** — Already scaffolded with substantial Laravel/Livewire code. Work is completion + ecosystem integration.
- **Tier 2** — Scaffold exists but stack needs decisions or rebuilding before feature work.
- **Tier 3** — HTML placeholder only. Full Laravel application to be built from scratch.

---

## Stack Alignment Note

Several repos (Dot.Forms, Dot.Sheet, Dot.Engage) were built against **Laravel 13** and **Vite 8** — one minor version ahead of the plan's Laravel 12 target. This is not a problem. Laravel 13 is a compatible increment with no breaking changes relative to 12. All new Tier 3 scaffolds should target **Laravel 12.x** (the stable LTS-aligned choice) or **Laravel 13.x** — stay consistent within each project. Do not mix versions within a single repo.

One alignment issue that **does** need fixing across all repos: **Pusher vs Reverb**. Dot.Sheet uses the Pusher SDK directly. All platforms must migrate to **Laravel Reverb** as the single WebSocket server, driven from InfoDot's infrastructure.

---

## Tier 1 Platforms — Complete & Integrate

These are production-grade applications that need feature completion, ecosystem wiring, and the shared PostgreSQL + Reverb alignment. No scaffolding work needed.

---

### Dot.Files

**Repo:** https://github.com/sakhileb/Dot.Files  
**Role in ecosystem:** Cloud file storage and organisation hub. Every other Dot platform can attach files through Dot.Files (documents from Dot.docs, forms attachments from Dot.Forms, project assets from Dot.Projects, etc.)  
**Current state:** Laravel 12 / PHP 8.4 / Livewire 3 — 54 commits, 31 open PRs. Fully on target stack. Most advanced satellite after Dot.Agents.

**Ecosystem integration tasks:**

| Phase | Task | Priority |
|---|---|---|
| Phase 5 | PR triage — close stale, merge clean fixes, defer feature PRs | NOW |
| Phase 5 | Point `.env` at shared InfoDot PostgreSQL instance | NOW |
| Phase 5 | Add `/auth/ecosystem` endpoint (EcosystemAuthController) | NOW |
| Phase 5 | Add Reverb connection config (if not already using it) | NOW |
| Phase 6 | Cross-platform file attach API (Dot.Projects, Dot.docs can link files) | NEXT |
| Phase 6 | Add to InfoDot DotSwitcher nav | NEXT |
| Phase 7 | Team-scoped storage quotas (Stripe subscription tier) | LATER |

**Key integration point:** `objects`, `files`, and `folders` tables stay in the shared DB, scoped by `team_id`. Other platforms reference them via a `file_id` foreign key into this table.

---

### Dot.Agents

**Repo:** https://github.com/sakhileb/Dot.Agents  
**Role in ecosystem:** The AI intelligence layer for the entire Dot ecosystem. Any platform can delegate tasks to an Agent. Dot.Projects uses agents for planning, Dot.Forms uses agents for form generation, Dot.Finance uses agents for financial analysis.  
**Current state:** Laravel 12 / PHP 8.4 / Livewire 3.8 / Tailwind 4.x — 59 commits, 298 tests passing, 573 assertions, 80%+ coverage. The most feature-complete repo in the ecosystem. Has multi-tenant architecture, agent marketplace, skill pipeline, approval governance, delusion detection, Digital Immune System, social commerce module.

**Stack note:** Currently uses SQLite (dev) / MySQL 8+ (prod). Must migrate to **PostgreSQL** for shared ecosystem DB alignment.

**Ecosystem integration tasks:**

| Phase | Task | Priority |
|---|---|---|
| Phase 6 | Migrate from MySQL to shared PostgreSQL instance | NOW (after InfoDot PG migration complete) |
| Phase 6 | Add `/auth/ecosystem` endpoint | NOW |
| Phase 6 | Connect Reverb for real-time agent status broadcasts to InfoDot hub | NOW |
| Phase 6 | Expose Agent REST API for cross-platform delegation | NEXT |
| Phase 7 | InfoDot hub widget showing active agents and live task feed | NEXT |
| Phase 7 | Per-platform agent marketplaces (Dot.Projects agent picker, etc.) | LATER |

**Agent delegation contract (cross-platform API):**
```php
// POST /api/v1/agent-task (in Dot.Agents)
// Accept requests from any Dot platform via ecosystem Sanctum token
Route::post('/agent-task', [AgentTaskController::class, 'dispatch'])
    ->middleware(['auth:sanctum', 'ability:ecosystem:write']);
```

This allows Dot.Projects to send: `{ agent_id: 3, task: "Break this project into milestones", context: { project_id: 42 } }` and get a structured response back.

**Priority note:** Dot.Agents is the highest-value satellite in the ecosystem. The AI backbone it provides will differentiate every other Dot platform. Get its PostgreSQL migration done immediately after InfoDot's.

---

### Dot.docs

**Repo:** https://github.com/sakhileb/Dot.docs  
**Role in ecosystem:** AI-powered collaborative document creation. Businesses use it for proposals, contracts, reports, and internal docs. Feeds directly into Dot.Engage (contracts), Dot.Projects (project docs), and Dot.Files (storage).  
**Current state:** Laravel 12 / PHP 8.3 / Livewire 3 / Reverb / TipTap editor — 19 commits. Fully on target stack. Features: TipTap rich editor, real-time collaboration (Reverb), AI writing assistant (GPT-4o), version history, comments + track changes, templates, PDF/Word/HTML/Markdown export, voice typing, offline mode, Jetstream Teams.

**Completion gaps to close:**

| Gap | Detail |
|---|---|
| PostgreSQL alignment | Currently supports MySQL/PostgreSQL — set PostgreSQL as default |
| Ecosystem auth endpoint | Add `/auth/ecosystem` route |
| Dot.Files integration | "Save to Dot.Files" action from within the editor |
| Dot.Engage bridge | "Convert to Contract" action that pushes a doc into Dot.Engage's contract system |
| DotSwitcher registration | Add Dot.docs URL to InfoDot's ecosystem config |

**Build phases:**

| Phase | Task | Priority |
|---|---|---|
| Phase 6 | Switch DB default to PostgreSQL, point at shared instance | NOW |
| Phase 6 | Add ecosystem auth endpoint | NOW |
| Phase 6 | Dot.Files "Save file" integration | NEXT |
| Phase 7 | Dot.Engage "Convert to Contract" integration | NEXT |
| Phase 7 | AI template generation from Dot.Agents (delegate to specialist agents) | LATER |

---

### Dot.Forms

**Repo:** https://github.com/sakhileb/Dot.Forms  
**Role in ecosystem:** The data collection layer. Client intake forms, survey collection, lead capture — all piped into the ecosystem. Dot.Projects uses Forms for project briefs; Dot.Engage uses them for contract intake; Dot.Agents uses form data as structured inputs.  
**Current state:** Laravel 13 / PHP 8.3+ / Livewire 3 / Vite 8 / SQLite default — 10 commits. Scaffolded with full feature set: drag-and-drop form builder, AI blueprint generation, public form publishing, CRM webhooks (HubSpot, Pipedrive, Zapier, Make), submission analytics, data retention settings.

**Completion gaps:**

| Gap | Detail |
|---|---|
| Switch to PostgreSQL | SQLite default is for dev only — shared PostgreSQL for production |
| Ecosystem auth endpoint | Add `/auth/ecosystem` |
| Form-to-Dot.Engage | Submit a form → auto-create a contract or project in another platform |
| Dot.Agents integration | Improve existing AI features by delegating to specialist agents from Dot.Agents |
| Missing features from tasklist | Check `tasklist.md` in repo for outstanding work |

**Build phases:**

| Phase | Task | Priority |
|---|---|---|
| Phase 6 | PostgreSQL as default, point at shared instance | NOW |
| Phase 6 | Ecosystem auth endpoint | NOW |
| Phase 6 | Audit and complete `tasklist.md` items | NOW |
| Phase 7 | Cross-platform webhooks: form submit → Dot.Projects creates project, etc. | NEXT |
| Phase 7 | Replace standalone AI layer with Dot.Agents delegation | LATER |

---

### Dot.Sheet

**Repo:** https://github.com/sakhileb/Dot.Sheet  
**Role in ecosystem:** AI-powered collaborative spreadsheets. Financial modelling for Dot.Finance, project timelines for Dot.Projects, auction bid tracking for Dot.Auction, inventory for Dot.Emall. The data analysis layer of the ecosystem.  
**Current state:** Laravel 13 / PHP 8.3+ / Livewire 3 / Vite 8 / Tailwind 3 — 5 commits but Phase 1 and Phase 2 complete per the docs. Full spreadsheet engine: 1,000×100 grid, 40+ formula functions, Chart.js charts, CSV/Excel import/export, real-time collaboration, AI formula generation, AI analysis panel, script editor, macro recording.

**Critical stack issue:** Dot.Sheet uses **Pusher** (not Reverb) for WebSockets. This must be migrated to Reverb before integration.

**WebSocket migration (Pusher → Reverb):**
```env
# Remove from .env:
PUSHER_APP_ID=
PUSHER_APP_KEY=
PUSHER_APP_SECRET=

# Add to .env:
BROADCAST_CONNECTION=reverb
REVERB_APP_ID=
REVERB_APP_KEY=
REVERB_APP_SECRET=
REVERB_HOST=
REVERB_PORT=8080
```

Update `resources/js/` Echo config to use `broadcaster: 'reverb'` (same change as InfoDot Phase 1d).

**Build phases:**

| Phase | Task | Priority |
|---|---|---|
| Phase 6 | Migrate Pusher → Reverb for real-time collaboration | NOW (blocking) |
| Phase 6 | Switch to shared PostgreSQL instance | NOW |
| Phase 6 | Add ecosystem auth endpoint | NOW |
| Phase 6 | Review and complete `TASK_LIST.md` items | NOW |
| Phase 7 | Dot.Finance "Open in Sheet" action | NEXT |
| Phase 7 | Dot.Projects Gantt view powered by Sheet data | NEXT |
| Phase 7 | Replace AI layer with Dot.Agents delegation | LATER |

---

## Tier 2 Platforms — Rebuild & Integrate

---

### Dot.Engage

**Repo:** https://github.com/sakhileb/Dot.Engage  
**Role in ecosystem:** The communication and legal layer. Real-time team chat, contract management with e-signature, and live video calling with in-call signing. Businesses close deals and sign contracts here. Feeds into Dot.Files for storage, Dot.docs for document creation.  
**Current state:** Laravel 13 / PHP 8.3+ / Livewire 3 / Reverb / Vite 8 — full Laravel scaffold with comprehensive README. Has migrations and app structure in place. Features documented: contract upload → sign → PDF delivery pipeline, 1:1 and group chat, video calls (Daily.co WebRTC), canvas e-signatures, team management via Jetstream.

**Build phases:**

| Phase | Task | Priority |
|---|---|---|
| Phase 7 | Verify all migrations run clean on PostgreSQL | NOW |
| Phase 7 | Add ecosystem auth endpoint | NOW |
| Phase 7 | Feature implementation: contracts, chat, video | NOW (this is the main build) |
| Phase 7 | Integrate Spatie Media Library for contract file management | NOW |
| Phase 7 | Daily.co WebRTC video integration | NOW |
| Phase 7 | Dot.docs bridge: "Create contract from document" | NEXT |
| Phase 7 | Dot.Files bridge: signed contract auto-archived to Dot.Files | NEXT |
| Phase 8 | Dot.Agents: AI contract analysis and risk flagging | LATER |

**Note on Daily.co:** This is a paid WebRTC service. Evaluate whether Dot.Engage should use Daily.co (simpler, paid) or a self-hosted open-source WebRTC stack (Jitsi/mediasoup) before building the video layer. Daily.co is the right call for MVP speed.

---

### Dot.Press

**Repo:** https://github.com/sakhileb/Dot.Press  
**Role in ecosystem:** CMS and publishing platform. Blog posts, marketing pages, press releases, and editorial content for businesses in the ecosystem. Connects to Dot.Engage for announcements and to InfoDot for publishing solutions/guides.  
**Current state:** Vue-based, early. Not on Livewire 3 stack.

**Architecture decision required:** Dot.Press needs a stack decision before any feature work:

**Option A — Rewrite to Laravel + Livewire 3** (recommended)  
Aligns with the entire ecosystem. Dot.Press becomes a standard Livewire app. The content editor uses TipTap (same as Dot.docs). Shared auth, shared DB, standard ecosystem integration.

**Option B — Keep Vue 3 (not Vue 2)**  
If the Vue version is upgraded to Vue 3 with a Vite build, it can still consume InfoDot's Sanctum API for auth. The downside: it's the only non-Livewire app in the ecosystem, adding maintenance surface.

**Recommendation: Option A.** The Vue prototype is early enough that a Livewire 3 rewrite is faster than aligning a separate Vue frontend architecture.

**Build phases:**

| Phase | Task | Priority |
|---|---|---|
| Phase 8 | Make architecture decision: Livewire 3 or Vue 3 | NOW |
| Phase 8 | Scaffold fresh Laravel 12 + Livewire 3 + Jetstream project | NOW |
| Phase 8 | TipTap content editor (reuse Dot.docs setup) | NOW |
| Phase 8 | Post/page/category models and CRUD | NOW |
| Phase 8 | SEO meta management (slug, meta description, OG tags) | NOW |
| Phase 8 | Ecosystem auth endpoint | NOW |
| Phase 8 | Media uploads → Dot.Files integration | NEXT |
| Phase 9 | Dot.Agents: AI content generation assistant | NEXT |
| Phase 9 | Public-facing CMS frontend (headless or Blade) | NEXT |

---

## Tier 3 Platforms — Build from Scratch

All 9 remaining platforms are HTML/CSS placeholders. The logos and landing page designs exist. The Laravel applications need to be built.

**Standard scaffold for each Tier 3 platform:**
```bash
laravel new Dot.[Name] --stack=livewire --teams
cd Dot.[Name]
composer require laravel/reverb
php artisan reverb:install
```

Each gets: Jetstream Teams, Sanctum, Livewire 3, Reverb, Vite, Tailwind 3, DaisyUI 5, and the `/auth/ecosystem` endpoint from day one.

---

### Dot.Projects

**Repo:** https://github.com/sakhileb/Dot.Projects  
**Role in ecosystem:** AI-driven project management. Businesses plan, run, and track projects here. Integrates with Dot.Tasks (task execution), Dot.Agents (AI planning), Dot.Files (project assets), Dot.Sheet (budget tracking), Dot.Forms (project intake forms), and Dot.Engage (client communication).  
**Current state:** HTML placeholder (index.html + logo only). No Laravel.

**Core feature set for MVP:**

- Project creation with name, description, client, budget, deadline
- Kanban board view (columns: Backlog → In Progress → Review → Done)
- Gantt chart view (Dot.Sheet-powered or native with ApexCharts)
- Team member assignment and role management via Jetstream Teams
- Real-time project status updates via Reverb
- AI milestone generator (delegate to Dot.Agents)
- Activity feed with Livewire real-time updates
- File attachments → Dot.Files bridge
- Time tracking per task
- Client-facing project portal (read-only view, public link)

**Build phases:**

| Phase | Task | Priority |
|---|---|---|
| Phase 9 | Laravel 12 + Livewire 3 + Jetstream scaffold | NOW |
| Phase 9 | Project, Milestone, Member models + CRUD | NOW |
| Phase 9 | Kanban board Livewire component with drag-and-drop | NOW |
| Phase 9 | Reverb real-time activity feed | NOW |
| Phase 9 | Ecosystem auth endpoint | NOW |
| Phase 10 | Gantt chart (ApexCharts timeline) | NEXT |
| Phase 10 | Dot.Tasks bridge: create tasks from project milestones | NEXT |
| Phase 10 | Dot.Agents: AI milestone + risk planning | NEXT |
| Phase 10 | Dot.Files: project asset browser | NEXT |
| Phase 11 | Client-facing project portal | LATER |
| Phase 11 | Dot.Sheet: project budget spreadsheet link | LATER |

---

### Dot.Tasks

**Repo:** https://github.com/sakhileb/Dot.Tasks  
**Role in ecosystem:** AI-driven task management. The execution layer under Dot.Projects. Individual tasks, personal to-dos, recurring automations. Feeds into Dot.Projects for reporting, and can receive tasks delegated by Dot.Agents.  
**Current state:** HTML placeholder only.

**Core feature set for MVP:**

- Task creation with title, description, assignee, due date, priority, tags
- Personal dashboard: My Tasks view
- Project task list (nested under Dot.Projects)
- Status workflow: Todo → In Progress → Blocked → Done
- Recurring tasks (daily/weekly/monthly)
- AI task breakdown: paste a goal, get a task list (Dot.Agents)
- Real-time status updates via Reverb
- Reminder notifications (email + in-app)
- Subtasks and checklists
- Time logging

**Build phases:**

| Phase | Task | Priority |
|---|---|---|
| Phase 9 | Laravel 12 + Livewire 3 + Jetstream scaffold | NOW |
| Phase 9 | Task, Subtask, Tag, Assignee models | NOW |
| Phase 9 | Personal task dashboard Livewire component | NOW |
| Phase 9 | Status Kanban view | NOW |
| Phase 9 | Ecosystem auth endpoint | NOW |
| Phase 10 | Dot.Projects integration: tasks linked to project milestones | NEXT |
| Phase 10 | Dot.Agents: AI task breakdown from goal description | NEXT |
| Phase 10 | Recurring task engine (scheduled Artisan command) | NEXT |
| Phase 11 | Time tracking + Dot.Sheet time report export | LATER |

**Note:** Dot.Projects and Dot.Tasks are tightly coupled — build them together in Phase 9 so the data model between them is correct from the start.

---

### Dot.Finance

**Repo:** https://github.com/sakhileb/Dot.Finance  
**Role in ecosystem:** AI financial management for personal and business use. Expense tracking, income recording, budgeting, financial reporting, tax estimates, and invoice management (feeds into InfoDot's Cashier layer). The financial backbone of the ecosystem for business owners.  
**Current state:** HTML/CSS placeholder only.

**Core feature set for MVP:**

- Income and expense recording with categories
- Bank account / wallet connections (manual entry first, Open Banking API later)
- Budget creation and tracking with visual progress (ApexCharts)
- Monthly P&L statement auto-generated
- Tax estimate calculator (South African VAT + income tax rates)
- Invoice creation and tracking (links to InfoDot Stripe/Cashier)
- AI financial insights: "You're spending 40% more on suppliers this quarter" (Dot.Agents)
- Dot.Sheet export: financial data as spreadsheet
- Multi-currency support
- Team-scoped: business finance vs personal finance modes

**Build phases:**

| Phase | Task | Priority |
|---|---|---|
| Phase 9 | Laravel 12 + Livewire 3 + Jetstream scaffold | NOW |
| Phase 9 | Transaction, Category, Account, Budget models | NOW |
| Phase 9 | Income/expense entry Livewire form | NOW |
| Phase 9 | Dashboard with ApexCharts monthly summary | NOW |
| Phase 9 | Ecosystem auth endpoint | NOW |
| Phase 10 | Tax estimate calculator (South Africa: VAT at 15%, provisional tax) | NEXT |
| Phase 10 | Dot.Agents: AI financial insights and anomaly detection | NEXT |
| Phase 10 | Dot.Sheet: export transactions as spreadsheet | NEXT |
| Phase 10 | Invoice creation module | NEXT |
| Phase 11 | Open Banking API integration (Plaid / Salt Edge for SA) | LATER |
| Phase 11 | InfoDot Stripe integration: subscription billing visibility | LATER |

---

### Dot.Emall

**Repo:** https://github.com/sakhileb/Dot.Emall  
**Role in ecosystem:** AI-powered online e-mall — a multi-vendor marketplace where businesses list products and customers browse and buy. The commerce layer of the ecosystem. Links to Dot.Finance (revenue tracking), Dot.Agents (AI product descriptions, pricing), Dot.Engage (buyer-seller messaging), and InfoDot (business discovery).  
**Current state:** HTML/CSS placeholder only.

**Core feature set for MVP:**

- Multi-vendor storefront (each business has a shop page)
- Product listings with AI-generated descriptions (Dot.Agents)
- Product categories, search, filters
- Shopping cart and checkout (Stripe via InfoDot's Cashier infrastructure)
- Order management for vendors (receive, process, dispatch)
- Order tracking for buyers
- Product reviews and ratings
- Seller dashboard with sales analytics (ApexCharts)
- AI pricing suggestions based on market data (Dot.Agents)
- Product images → Dot.Files storage
- Messaging between buyer and seller → Dot.Engage

**Build phases:**

| Phase | Task | Priority |
|---|---|---|
| Phase 10 | Laravel 12 + Livewire 3 + Jetstream scaffold | NOW |
| Phase 10 | Shop, Product, Category, Order, OrderItem models | NOW |
| Phase 10 | Storefront browse + search Livewire components | NOW |
| Phase 10 | Stripe checkout (Cashier, shared with InfoDot config) | NOW |
| Phase 10 | Ecosystem auth endpoint | NOW |
| Phase 11 | Vendor dashboard with order management | NEXT |
| Phase 11 | AI product description generator (Dot.Agents) | NEXT |
| Phase 11 | Buyer-seller messaging → Dot.Engage bridge | NEXT |
| Phase 11 | Product images → Dot.Files bridge | NEXT |
| Phase 12 | AI pricing recommendations | LATER |
| Phase 12 | Dot.Finance: revenue sync for vendor accounting | LATER |

---

### Dot.Auction

**Repo:** https://github.com/sakhileb/Dot.Auction  
**Role in ecosystem:** Online auction platform. Businesses and individuals list items for timed auctions. Real-time bidding powered by Reverb WebSockets — a natural fit. Links to Dot.Finance (payment settlement), Dot.Engage (post-auction communication), Dot.Files (item photos).  
**Current state:** HTML/CSS placeholder only.

**Core feature set for MVP:**

- Auction listing with item details, photos (Dot.Files), start/reserve price, and end time
- Real-time live bidding via Reverb (bid broadcast to all viewers instantly)
- Bid history and outbid notifications
- Countdown timer (client-side with Livewire + Alpine.js)
- Automatic auction closing when time expires (Laravel Scheduler)
- Winner notification (email + in-app)
- Payment processing at auction close (Stripe)
- Seller dashboard: active auctions, bid history, revenue
- Categories and search
- Watchlist (save auctions you're following)

**The real-time bidding system** is the core differentiator and the hardest technical piece — build it first as a "walking skeleton":

```php
// Reverb broadcast event
class BidPlaced implements ShouldBroadcast
{
    public function broadcastOn(): Channel
    {
        return new Channel("auction.{$this->auction->id}");
    }
}

// Livewire component for live bid display
class AuctionRoom extends Component
{
    public Auction $auction;
    public int $currentBid;

    #[On('echo:auction.{auction.id},BidPlaced')]
    public function bidPlaced(array $data): void
    {
        $this->currentBid = $data['amount'];
    }
}
```

**Build phases:**

| Phase | Task | Priority |
|---|---|---|
| Phase 10 | Laravel 12 + Livewire 3 + Jetstream scaffold | NOW |
| Phase 10 | Auction, Bid, Category models + migrations | NOW |
| Phase 10 | Walking skeleton: place bid → Reverb broadcast → live update | NOW (riskiest piece) |
| Phase 10 | Auction listing CRUD with countdown timer | NOW |
| Phase 10 | Ecosystem auth endpoint | NOW |
| Phase 11 | Automatic auction close (Laravel Scheduler) | NEXT |
| Phase 11 | Stripe payment on auction win | NEXT |
| Phase 11 | Item photos → Dot.Files | NEXT |
| Phase 11 | Dot.Finance: auction revenue tracking | NEXT |
| Phase 12 | Proxy bidding (auto-bid up to max) | LATER |
| Phase 12 | Dot.Agents: AI reserve price suggestions | LATER |

---

### Dot.Ehail

**Repo:** https://github.com/sakhileb/Dot.Ehail  
**Role in ecosystem:** Online e-hailing platform for business owners. Driver and passenger matching, trip booking, fare calculation, real-time vehicle tracking (Leaflet maps), and trip history. Differentiates from consumer apps by targeting business travel — corporate accounts, invoiced billing, route compliance.  
**Current state:** HTML/CSS placeholder with a landing page design.

**Core feature set for MVP:**

- Driver and passenger registration + profiles
- Vehicle listing and verification
- Trip booking: pickup, destination, vehicle class selection
- Fare estimation (distance × rate matrix)
- Real-time driver location tracking (Leaflet.js + Reverb location broadcasts)
- Trip status updates (Booked → Driver En Route → In Progress → Completed)
- Driver earnings dashboard
- Corporate account module (team billing via Stripe)
- Trip history and receipts (PDF via DomPDF)
- Rating system (driver rated by passenger, passenger rated by driver)

**Location tracking (the hardest piece — build first):**
```php
// Driver sends location every 5 seconds via Livewire action
public function updateLocation(float $lat, float $lng): void
{
    $this->driver->update(['lat' => $lat, 'lng' => $lng]);
    $this->dispatch('DriverLocationUpdated', lat: $lat, lng: $lng)
         ->to("trip.{$this->trip->id}");
}
```

**Build phases:**

| Phase | Task | Priority |
|---|---|---|
| Phase 10 | Laravel 12 + Livewire 3 + Jetstream scaffold | NOW |
| Phase 10 | Driver, Passenger, Vehicle, Trip, Fare models | NOW |
| Phase 10 | Walking skeleton: driver sends location → passenger sees it on map | NOW (riskiest piece) |
| Phase 10 | Trip booking flow (4-step wizard Livewire component) | NOW |
| Phase 10 | Leaflet.js map integration via Vite | NOW |
| Phase 10 | Ecosystem auth endpoint | NOW |
| Phase 11 | Fare calculation engine (zone/distance matrix) | NEXT |
| Phase 11 | Rating system | NEXT |
| Phase 11 | Stripe payment on trip completion | NEXT |
| Phase 11 | Dot.Finance: driver earnings sync | NEXT |
| Phase 12 | Corporate account + invoiced billing module | LATER |
| Phase 12 | Dot.Agents: AI route optimisation for drivers | LATER |

---

### Dot.Tutor

**Repo:** https://github.com/sakhileb/Dot.Tutor  
**Role in ecosystem:** Tutoring marketplace for high school and university students. Tutors list their subjects and rates; students book sessions. Bridges education with the business ecosystem — tutoring businesses manage bookings, payments, and resources through InfoDot.  
**Current state:** HTML/CSS placeholder only.

**Core feature set for MVP:**

- Tutor profiles (subjects, qualifications, hourly rate, availability)
- Student profiles and learning goals
- Session booking (calendar + availability system)
- Video tutoring sessions (Daily.co WebRTC, reuse Dot.Engage's integration)
- Payment processing (Stripe — session fee held until session complete)
- Session notes and resources (Dot.docs integration for shared session documents)
- Review and rating system
- Tutor dashboard: upcoming sessions, earnings, student progress
- Student dashboard: upcoming sessions, session history, notes access
- AI study plan generator (Dot.Agents)

**Build phases:**

| Phase | Task | Priority |
|---|---|---|
| Phase 10 | Laravel 12 + Livewire 3 + Jetstream scaffold | NOW |
| Phase 10 | Tutor, Student, Session, Availability, Subject models | NOW |
| Phase 10 | Tutor listing + search + filter Livewire components | NOW |
| Phase 10 | Calendar availability picker (Alpine.js) | NOW |
| Phase 10 | Ecosystem auth endpoint | NOW |
| Phase 11 | Stripe payment hold + release on session completion | NEXT |
| Phase 11 | Video session integration (Daily.co — reuse Dot.Engage code) | NEXT |
| Phase 11 | Dot.docs: shared session document/notes | NEXT |
| Phase 12 | Dot.Agents: AI personalised study plan generator | LATER |
| Phase 12 | Dot.Finance: tutor revenue tracking | LATER |

---

### Dot.Design

**Repo:** https://github.com/sakhileb/Dot.Design  
**Role in ecosystem:** AI design platform. Businesses create visual assets — social media graphics, presentations, logos, marketing materials — without a designer. Integrates with Dot.Press (graphics for content), Dot.Emall (product images), and InfoDot (solution thumbnails and profile banners).  
**Current state:** HTML/CSS placeholder only.

**Architecture note:** This is a canvas-based design tool. The frontend complexity is high — consider whether to build a native canvas editor (using Fabric.js or Konva.js) or integrate with an existing design API (Canva API, Bannerbear). For MVP: Bannerbear or similar API-first approach (AI generates a design → returns image). For V2: native canvas editor.

**Core feature set for MVP (API-first approach):**

- Design brief form: what do you need, what brand colours, what text
- AI image generation via OpenAI DALL-E 3 or Stable Diffusion API
- Design templates browser (categories: social, banner, logo, flyer)
- Template customisation with text and colour overlay (Fabric.js)
- Export as PNG/JPG/SVG/PDF
- Brand kit: save brand colours, fonts, logos for reuse
- Design history and versioning → Dot.Files storage
- Team collaboration on designs

**Build phases:**

| Phase | Task | Priority |
|---|---|---|
| Phase 10 | Laravel 12 + Livewire 3 scaffold | NOW |
| Phase 10 | Architecture decision: API-first (Bannerbear) vs native canvas (Fabric.js) | NOW |
| Phase 10 | Template browser + category system | NOW |
| Phase 10 | AI image generation endpoint (OpenAI DALL-E 3) | NOW |
| Phase 10 | Ecosystem auth endpoint | NOW |
| Phase 11 | Fabric.js canvas for text/colour customisation on templates | NEXT |
| Phase 11 | Brand kit model (colours, fonts, logos per team) | NEXT |
| Phase 11 | PNG/PDF export → Dot.Files storage | NEXT |
| Phase 12 | Dot.Agents: AI design brief interpreter and style guide | LATER |
| Phase 12 | Native full canvas editor (V2 — major build) | LATER |

---

### Dot.Central

**Repo:** https://github.com/sakhileb/Dot.Central  
**Role in ecosystem:** AI agents for specialist skills — a curated marketplace of domain-specific AI agents that any business can deploy. Differs from Dot.Agents (which is an enterprise AI workforce OS) — Dot.Central is the consumer-facing agent marketplace where businesses browse and buy access to specialist agents for specific tasks (legal review agent, HR policy agent, tax advice agent, etc.).  
**Current state:** HTML/CSS placeholder only.

**Relationship with Dot.Agents:** Dot.Central is the public storefront; Dot.Agents is the platform that runs the agents. A business discovers an agent in Dot.Central, pays for access, and Dot.Agents handles the deployment and execution. Think: Dot.Central = agent App Store; Dot.Agents = agent runtime.

**Core feature set for MVP:**

- Agent marketplace with categories (Legal, Finance, HR, Marketing, Operations, Education)
- Agent detail pages: what it does, example prompts, pricing
- Trial access (3 free queries) before purchase
- Subscription tiers for agent access (Stripe)
- Agent chat interface (redirects to Dot.Agents runtime)
- Business dashboard: active agent subscriptions and usage
- Agent rating and review system
- Featured agents and new arrival curation
- Search and filter

**Build phases:**

| Phase | Task | Priority |
|---|---|---|
| Phase 10 | Laravel 12 + Livewire 3 + Jetstream scaffold | NOW |
| Phase 10 | Agent, Category, Subscription models | NOW |
| Phase 10 | Marketplace browse + search + filter | NOW |
| Phase 10 | Stripe subscription for agent access | NOW |
| Phase 10 | Ecosystem auth endpoint | NOW |
| Phase 11 | Trial access (3-query limit before paywall) | NEXT |
| Phase 11 | Dot.Agents runtime bridge: subscription check → launch agent in Dot.Agents | NEXT |
| Phase 11 | Agent rating and review system | NEXT |
| Phase 12 | Agent creator portal (third-party developers publish agents) | LATER |
| Phase 12 | Revenue share model (InfoDot takes % of agent subscription) | LATER |

---

## Updated Ecosystem Config

With all 16 platforms mapped, update `config/ecosystem.php` in InfoDot:

```php
// config/ecosystem.php
return [
    // Tier 1 — Active (live after Phase 6)
    'dotfiles_url'   => env('ECOSYSTEM_DOTFILES_URL',   'https://files.infodot.app'),
    'dotagents_url'  => env('ECOSYSTEM_DOTAGENTS_URL',  'https://agents.infodot.app'),
    'dotdocs_url'    => env('ECOSYSTEM_DOTDOCS_URL',    'https://docs.infodot.app'),
    'dotforms_url'   => env('ECOSYSTEM_DOTFORMS_URL',   'https://forms.infodot.app'),
    'dotsheet_url'   => env('ECOSYSTEM_DOTSHEET_URL',   'https://sheet.infodot.app'),

    // Tier 2 — Building (live after Phase 7–8)
    'dotengage_url'  => env('ECOSYSTEM_DOTENGAGE_URL',  'https://engage.infodot.app'),
    'dotpress_url'   => env('ECOSYSTEM_DOTPRESS_URL',   'https://press.infodot.app'),

    // Tier 3 — Planned (live Phase 9–12)
    'dotprojects_url' => env('ECOSYSTEM_DOTPROJECTS_URL', 'https://projects.infodot.app'),
    'dottasks_url'    => env('ECOSYSTEM_DOTTASKS_URL',    'https://tasks.infodot.app'),
    'dotfinance_url'  => env('ECOSYSTEM_DOTFINANCE_URL',  'https://finance.infodot.app'),
    'dotemall_url'    => env('ECOSYSTEM_DOTEMALL_URL',    'https://mall.infodot.app'),
    'dotauction_url'  => env('ECOSYSTEM_DOTAUCTION_URL',  'https://auction.infodot.app'),
    'dotehail_url'    => env('ECOSYSTEM_DOTEHAIL_URL',    'https://ehail.infodot.app'),
    'dottutor_url'    => env('ECOSYSTEM_DOTTUTOR_URL',    'https://tutor.infodot.app'),
    'dotdesign_url'   => env('ECOSYSTEM_DOTDESIGN_URL',   'https://design.infodot.app'),
    'dotcentral_url'  => env('ECOSYSTEM_DOTCENTRAL_URL',  'https://central.infodot.app'),
];
```

The DotSwitcher Livewire component in Phase 4 should show only platforms that are `active` (have a URL set and have received their ecosystem auth endpoint). Inactive/upcoming platforms appear as "Coming Soon" cards.

---

## Updated Deployment Architecture

```
infodot.app              → InfoDot (hub)
files.infodot.app        → Dot.Files
agents.infodot.app       → Dot.Agents
docs.infodot.app         → Dot.docs
forms.infodot.app        → Dot.Forms
sheet.infodot.app        → Dot.Sheet
engage.infodot.app       → Dot.Engage
press.infodot.app        → Dot.Press
projects.infodot.app     → Dot.Projects
tasks.infodot.app        → Dot.Tasks
finance.infodot.app      → Dot.Finance
mall.infodot.app         → Dot.Emall
auction.infodot.app      → Dot.Auction
ehail.infodot.app        → Dot.Ehail
tutor.infodot.app        → Dot.Tutor
design.infodot.app       → Dot.Design
central.infodot.app      → Dot.Central

Shared infrastructure (all 16 platforms point here):
- PostgreSQL 16          → single source of truth for users + teams + tokens
- Redis                  → sessions, cache, queues
- Laravel Reverb         → single WebSocket server (run from InfoDot)
- AWS S3 / Flysystem     → file storage (Dot.Files manages the bucket)
- Meilisearch            → full-text search
- Stripe                 → payments (InfoDot's Cashier config shared)
- Sentry                 → error monitoring (one project per platform)
```

---

## Full Phased Delivery Calendar

| Phase | Weeks | Platforms | Key Deliverable |
|---|---|---|---|
| 1–3 | Wk 1–6 | InfoDot | Stack upgrade complete, Livewire 3 rewrite done |
| 4 | Wk 6–8 | InfoDot | Ecosystem hub layer live (Sanctum tokens, DotSwitcher) |
| 5 | Wk 8–10 | Dot.Files | PR triage, shared DB, ecosystem auth, live |
| 6 | Wk 10–13 | Dot.Agents, Dot.docs, Dot.Forms, Dot.Sheet | PG migration, Reverb alignment, ecosystem auth, features completed |
| 7 | Wk 13–17 | Dot.Engage | Full feature build: contracts, chat, video, e-signature |
| 8 | Wk 17–20 | Dot.Press | Livewire rewrite + CMS feature build |
| 9 | Wk 20–24 | Dot.Projects, Dot.Tasks | Built together (tight data model dependency) |
| 10 | Wk 24–30 | Dot.Finance, Dot.Emall, Dot.Auction, Dot.Ehail, Dot.Design, Dot.Central, Dot.Tutor | Parallel builds (can assign different developers per platform) |
| 11 | Wk 30–36 | All Tier 3 | Feature completion, cross-platform integrations |
| 12 | Wk 36+ | All platforms | Advanced AI integrations, Dot.Agents delegation, V2 features |

**Total estimated timeline:** 36 weeks (9 months) to all 16 platforms live with core feature sets. This assumes 1–2 developers building in parallel from Phase 9 onwards. Solo build would extend Phase 10–11 to 18+ months.

---

## The Ecosystem's Biggest Leverage Points

In order of impact on the overall ecosystem:

1. **InfoDot upgrade** (Phases 1–4) — everything depends on this being stable first
2. **Dot.Agents PostgreSQL migration** (Phase 6) — unlocks AI delegation for every other platform
3. **Dot.Projects + Dot.Tasks** (Phase 9) — most businesses' daily driver; generates daily active usage
4. **Dot.Emall** (Phase 10) — the revenue-generating commerce layer
5. **Dot.Ehail** (Phase 10) — high-frequency, location-based, drives mobile usage

Focus development effort here. The remaining platforms (Dot.Design, Dot.Press, Dot.Tutor, Dot.Central) are valuable but lower frequency — they amplify the ecosystem rather than anchor daily usage.

---

*Part 2 added: June 2026*  
*Total ecosystem: 1 hub (InfoDot) + 16 Dot platforms*
