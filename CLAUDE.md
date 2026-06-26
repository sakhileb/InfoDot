# InfoDot — CLAUDE.md

**Project:** InfoDot — Ecosystem Hub for BluPin Incorporated / SK Digital  
**Stack target:** Laravel 12 · PHP 8.4 · Livewire 3 · PostgreSQL 16 · Reverb · Vite 7 · DaisyUI 5 · Alpine.js 3  
**Current stack:** Laravel 10.50 · PHP 8.3 · Livewire 2.12 · MySQL · Laravel Mix · BeyondCode WebSockets · DaisyUI 1 · Vue.js 2

---

## What InfoDot Is

InfoDot is the **hub** of the Dot ecosystem — a collection of 16 micro-platforms for seamless business transactions. Users log in once to InfoDot and access any Dot platform (Dot.Files, Dot.Agents, Dot.docs, Dot.Forms, Dot.Sheet, Dot.Engage, Dot.Press, Dot.Projects, Dot.Tasks, Dot.Finance, Dot.Emall, Dot.Auction, Dot.Ehail, Dot.Tutor, Dot.Design, Dot.Central) without re-authenticating.

**Core features already built:** Solutions hub · Q&A · threaded comments · polymorphic likes · user profiles · social graph · team management · real-time notifications · file storage · full-text search

**Ecosystem mechanism:** Laravel Sanctum tokens. InfoDot issues tokens; satellite apps verify them against the shared PostgreSQL database. Short-lived handoff tokens (5 min) are used for cross-platform SSO via `/auth/ecosystem` endpoints.

---

## Upgrade Phases

### Phase 1 — Stack Foundation (target: Weeks 1–3)
**Branch:** `upgrade/laravel-12`

Key tasks (in order):
1. `composer.json` → Laravel 12, Livewire 3, Jetstream 5, Sanctum 4, Reverb, Scout
2. Replace `webpack.mix.js` → `vite.config.js` · replace `mix()` → `@vite()` in all Blade layouts
3. Switch DB from MySQL → PostgreSQL · remove all `$table->fullText()` calls from migrations
4. Remove BeyondCode WebSockets · `php artisan reverb:install` · update Echo config in `bootstrap.js`
5. Install Laravel Scout · add `Searchable` trait to `User`, `Solution`, `Question`, `Comment` models
6. Upgrade DaisyUI 1 → 5 · update `tailwind.config.js` plugin import
7. Remove Vue.js 2 from `package.json` · rewrite any `.vue` files as Alpine.js or Livewire components

### Phase 2 — Livewire 2 → 3 Rewrite (target: Weeks 2–4)

Livewire 3 is NOT backwards compatible. Every component needs rewriting.

**Key changes:**
- `$this->emit('event')` → `$this->dispatch('event')`
- `protected $listeners` → `#[On('event')]` attribute on the method
- `wire:model` (real-time) → `wire:model.live`
- Public properties must be typed: `public $name` → `public string $name = ''`
- Computed properties → `#[Computed]` attribute
- `@livewire('search')` → `<livewire:search />`

**Components to rewrite:** Search · NavigationDropdown · FileBrowser · (audit all others)

### Phase 3 — Feature Completion (target: Weeks 4–6)
- Laravel Cashier (Stripe) — add `Billable` to User model
- AWS S3 via Flysystem — update `FILESYSTEM_DISK=s3` in production
- Sentry error monitoring — `php artisan sentry:publish`
- PHPStan level 5+ pass
- PHPUnit 11 — update `phpunit.xml` format
- ApexCharts 5 + Chart.js 4 + Leaflet 1.9 in `package.json`

### Phase 4 — Ecosystem Hub Layer (target: Weeks 6–8)
- `POST /api/ecosystem/token` endpoint → issues 24h Sanctum tokens
- `<livewire:dot-switcher />` component in header → links all 16 platforms
- `config/ecosystem.php` → URLs for all 16 Dot platforms
- Dashboard widget showing connected Dot platforms

### Phase 5 — Dot.Files Integration (target: Weeks 8–10)
- Point Dot.Files `.env` at shared InfoDot PostgreSQL instance
- Triage Dot.Files' 31 open PRs
- Add `/auth/ecosystem` endpoint to Dot.Files

---

## Key Patterns to Follow

### Ecosystem SSO (add to every satellite app)
```php
// routes/web.php
Route::get('/auth/ecosystem', [EcosystemAuthController::class, 'handle']);

// EcosystemAuthController
public function handle(Request $request): RedirectResponse
{
    $accessToken = PersonalAccessToken::findToken($request->query('token'));
    abort_if(!$accessToken || $accessToken->expires_at->isPast(), 403);
    $user = $accessToken->tokenable;
    $accessToken->delete(); // one-time use
    Auth::login($user);
    return redirect()->route('dashboard');
}
```

### Livewire 3 component pattern
```php
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

class ExampleComponent extends Component
{
    public string $query = '';

    #[Computed]
    public function results(): array
    {
        return Solution::search($this->query)->get()->toArray();
    }

    #[On('someEvent')]
    public function handleEvent(array $data): void
    {
        // handle it
    }
}
```

### Vite config (replaces webpack.mix.js)
```js
import { defineConfig } from 'vite'
import laravel from 'laravel-vite-plugin'

export default defineConfig({
    plugins: [laravel({ input: ['resources/css/app.css', 'resources/js/app.js'], refresh: true })],
})
```

### Reverb Echo config (bootstrap.js)
```js
window.Echo = new Echo({
    broadcaster: 'reverb',
    key: import.meta.env.VITE_REVERB_APP_KEY,
    wsHost: import.meta.env.VITE_REVERB_HOST ?? `ws.${location.hostname}`,
    forceTLS: (import.meta.env.VITE_REVERB_SCHEME ?? 'https') === 'https',
    enabledTransports: ['ws', 'wss'],
});
```

---

## Database

- **Driver:** PostgreSQL 16 (shared across all Dot platforms)
- **No FULLTEXT indexes** — replaced by Laravel Scout (TNTSearch dev / Meilisearch prod)
- **Shared tables:** `users`, `teams`, `team_user`, `personal_access_tokens`
- Each satellite keeps its own domain tables but points at the same DB instance

## Deployment Architecture

```
infodot.app              → this app (hub)
files.infodot.app        → Dot.Files
agents.infodot.app       → Dot.Agents
docs.infodot.app         → Dot.docs
[+ 13 more platforms]

Shared: PostgreSQL 16 · Redis · Reverb · AWS S3 · Meilisearch · Stripe
```

## Testing

- **Framework:** PHPUnit 11
- **Target coverage:** 70%+ before MVP
- **Key test areas:** auth flow · solution CRUD · Q&A flow · team invitations · search · file uploads · ecosystem token issuance
- Run tests: `php artisan test` or `./vendor/bin/phpunit`
- Static analysis: `./vendor/bin/phpstan analyse --level=5`

## Current Status

Phase 1 not yet started. The upgrade branch `upgrade/laravel-12` should be created first.

**Immediate next action:** `git checkout -b upgrade/laravel-12` then update `composer.json`.

---

## Dev Commands

```bash
php artisan serve              # Laravel on :8000
npm run dev                    # Vite on :5173
php artisan reverb:start       # WebSockets on :8080
php artisan queue:work         # Queue worker
php artisan migrate            # Run migrations
php artisan test               # Run test suite
./vendor/bin/phpstan analyse   # Static analysis
```

## Full Ecosystem Plan

See `docs/infodot-upgrade-plan.md` for the complete 16-platform ecosystem plan including per-platform build phases, stack alignment notes, and the full delivery calendar.
