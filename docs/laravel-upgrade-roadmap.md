# Laravel 10/11+ Migration Roadmap (Dependency-Focused, No UI Redesign)

## Goal
Upgrade from the current Laravel 10 codebase to Laravel 11+ safely, with a staged dependency roadmap and regression gates.

This plan is intentionally backend-focused:
- No frontend redesign
- No visual/UI rework
- Keep Blade/Livewire views and user flows unchanged unless a framework API change forces a minimal compatibility patch

## Current Baseline (Observed)
- Laravel framework: 10.50.2
- Jetstream: 3.x
- Livewire: 2.x
- Sanctum: 3.x
- Scout: 10.x
- Fortify: 1.x
- PHP runtime: 8.3 (compatible with Laravel 10/11 requirements)
- Legacy advisory override currently present in composer config

## High-Risk Packages to Replace or Rework Early
These are the main blockers/risks before or during framework jumps:

1. beyondcode/laravel-websockets (abandoned)
- Risk: compatibility/security/maintenance risk across newer Laravel versions.
- Preferred path: replace with Laravel Reverb (Laravel 11+) or Pusher-hosted/compatible channel backend.

2. fruitcake/laravel-cors (abandoned)
- Risk: old package lifecycle.
- Path: rely on built-in Laravel CORS handling (already supported in newer versions).

3. facade/ignition (legacy)
- Path by version:
  - Laravel 9/10: move to spatie/laravel-ignition.
  - Laravel 11+: align with framework defaults and current collision/ignition guidance.

4. Jetstream + Livewire coupling
- Jetstream versions are tightly coupled to framework and Livewire major versions.
- Keep UI the same, but package versions must move in lockstep.

## Recommended Upgrade Path

### Phase 0: Hardening Before Framework Jump (Current branch)
Objective: reduce unknowns and make framework jumps mostly dependency-driven.

Tasks:
1. Ensure tests cover all critical flows already verified:
- Auth/login/register/reset
- Questions CRUD + likes/comments/solved
- Solutions CRUD + steps + search
- Associates follow/unfollow
- Team creation/switch basics

2. Add missing regression tests for:
- Broadcast-dependent paths (or confirm explicit non-blocking behavior when disabled)
- Notification pages and edge cases
- Search fallback behavior consistency

3. Remove abandoned dependencies where possible before framework jump:
- Remove fruitcake/laravel-cors usage in favor of framework CORS
- Decide websocket strategy (replace or isolate)

Exit criteria:
- Full tests green
- No runtime warnings in local logs for core flows
- Deprecated packages isolated or replaced plan finalized

---

### Phase 1: Laravel 8 -> Laravel 9
Objective: first major lift with minimal surface change.

Dependency targets:
1. laravel/framework: ^9.x
2. laravel/jetstream: latest 2.x line compatible with Laravel 9
3. livewire/livewire: keep on 2.x if compatible in this step (recommended for lower UI risk)
4. laravel/sanctum, fortify, scout, tinker: bump to Laravel 9-compatible majors
5. Replace facade/ignition with spatie/laravel-ignition

Code-level compatibility checks:
1. Exception handler/reporting signatures
2. Route service provider behavior and namespace assumptions
3. Mailer/notification deprecations
4. Carbon/date helper usage under newer components

Validation gates:
1. composer install/update without platform-ignore flags
2. php artisan migrate:fresh --seed passes
3. php artisan test fully passes
4. Smoke test all pages/routes from route:list

Exit criteria:
- Laravel 9 stable in production-like environment
- No UI changes except unavoidable compatibility edits

---

### Phase 2: Laravel 9 -> Laravel 10
Objective: align with modern LTS ecosystem.

Dependency targets:
1. laravel/framework: ^10.x
2. jetstream/fortify/sanctum/scout/livewire versions aligned to Laravel 10
3. phpunit stack aligned to supported version for Laravel 10

Key work:
1. Process middleware and bootstrap config changes (if any customizations conflict)
2. Queue/mail/cache driver configs validated
3. Team/Jetstream flows verified end-to-end

Validation gates:
1. All automated tests green
2. Zero fatal runtime errors in: login, profile, questions, solutions, search, team flows
3. Database migrations + seeders pass on SQLite and production DB type

Exit criteria:
- Laravel 10 deployed on staging with production data snapshot testing

---

### Phase 3: Laravel 10 -> Laravel 11+
Objective: modernize framework core while preserving behavior.

Dependency targets:
1. laravel/framework: ^11.x
2. jetstream/livewire combo upgraded to framework-compatible majors
3. Websocket strategy finalized:
- Prefer Laravel Reverb OR external hosted websocket service
- Remove abandoned websocket server package

Key work:
1. Adopt Laravel 11 app skeleton/bootstrapping changes incrementally
2. Validate auth/session/middleware registration patterns
3. Confirm scout/search driver behavior on upgraded stack

Validation gates:
1. CI green (tests + static checks)
2. Staging UAT across all critical flows
3. No frontend redesign changes introduced

Exit criteria:
- Advisory override removed
- No abandoned critical runtime package in production path

## Safe Execution Strategy

1. One major framework jump per PR
- PR-A: prep/removals/tests only
- PR-B: Laravel 9
- PR-C: Laravel 10
- PR-D: Laravel 11+

2. Add rollback checkpoints
- Tag releases before each major jump
- Keep DB backup/restore scripts ready

3. Keep runtime matrix explicit
- Dev: PHP 8.3/8.4
- CI: run tests on 8.3 and 8.4 during migration period

4. Keep migrations deterministic
- Continue using SQLite compatibility patterns where needed
- Validate against actual production database engine before release

## Dependency Roadmap Snapshot

1. Keep now (short term)
- Laravel 10 line stable; no frontend redesign changes in transition work

2. Move in Phase 1/2
- framework 8 -> 9 -> 10
- jetstream 2 -> 3+ (framework aligned)
- livewire 2.x kept as long as compatibility allows
- ignition package swap (facade -> spatie)

3. Move in Phase 3
- framework 10 -> 11+
- websocket package replacement (remove abandoned package)

## Definition of Done
Migration is complete when all are true:
1. Running on Laravel 11+ (or target agreed major)
2. No legacy advisory override needed
3. No abandoned runtime-critical dependency in use
4. Core tests pass and feature parity is preserved
5. No frontend redesign introduced

## Suggested First Implementation PR (Immediately Actionable)
1. Remove fruitcake/laravel-cors package usage where framework-native CORS can replace it.
2. Introduce websocket abstraction layer and choose Reverb/hosted backend path.
3. Expand tests for notifications + broadcasting edge cases.
4. Prepare Laravel 9 composer branch with only dependency and compatibility edits.

## Execution Log

### 2026-03-17 (Phase 0 Start)
Completed:
1. Dependency baseline modernization for current Laravel 8 branch completed and validated.
2. Full test suite passing on current branch after dependency refresh.
3. CI matrix added for PHP 8.3 and 8.4 with SQLite migrations + seeding + tests.
4. Broadcast/notification edge regression tests added and validated.
5. Websocket strategy abstraction added to isolate current websocket package assumptions.
6. Laravel 9 prep inventory created for framework-coupled packages and touchpoints.
7. Framework-coupled contract tests added for Jetstream/Fortify/model/provider integration seams.

Attempted and deferred:
1. Removing `fruitcake/laravel-cors` in Laravel 8 branch.
- Result: not viable in current branch because `Illuminate\\Http\\Middleware\\HandleCors` is unavailable in Laravel 8 in this codebase layout.
- Decision: keep current CORS package until Laravel 9+ migration step, then swap to framework-native CORS.

Next immediate tasks:
1. Prepare the Laravel 9 dependency transition branch using the new package-coupling baseline.
2. Replace `facade/ignition` with the Laravel 9-compatible Ignition package during the framework jump.
3. Revisit framework-native CORS replacement as part of the Laravel 9 dependency move.

### 2026-03-17 (Phase 1 Complete)
Completed:
1. Laravel framework upgraded from 8.83.29 to 9.52.21.
2. `facade/ignition` replaced with `spatie/laravel-ignition`.
3. Framework-coupled package set updated for Laravel 9 compatibility, including Jetstream 2.16.x, Collision 6.x, and the Laravel 9-compatible adjacency-list/CTE packages.
4. Mail sender defaults were normalized so Jetstream invitation flows remain valid under Symfony Mailer's stricter header checks.
5. Laravel 9 runtime validation passed: `php artisan --version`, `php artisan migrate:fresh --seed`, `php artisan route:list`, and `php artisan test`.

Validation outcome:
1. Full suite green on Laravel 9: 59 passed, 7 skipped, 1 risky.
2. No frontend layout or styling changes were introduced.

### 2026-03-17 (Phase 2 Started)
Completed:
1. Replaced abandoned `fruitcake/laravel-cors` usage with native Laravel CORS middleware (`Illuminate\\Http\\Middleware\\HandleCors`) and removed the package from dependencies.
2. Upgraded the framework-coupled package set to Laravel 10-compatible lines: Laravel 10.50.2, Jetstream 3.3.x, Sanctum 3.3.x, Scout 10.x, Collision 7.x, Ignition 2.x, PHPUnit 10.5.x, and compatible adjacency-list/CTE/TNTSearch packages.
3. Added Jetstream 3 compatibility aliases for legacy `x-jet-*` Blade components to preserve existing views without frontend redesign.
4. Added a compatibility mail view bridge at `resources/views/emails/team-invitation.blade.php` for team invitation mailable rendering.
5. Tidied the browser sessions regression test by adding an explicit assertion (`assertHasNoErrors`) so it is no longer risky.
6. Migrated PHPUnit configuration to the current schema.

Validation outcome:
1. Laravel runtime validation passed on Laravel 10: `php artisan --version` and `php artisan migrate:fresh --seed`.
2. Full suite green on Laravel 10: 60 passed, 7 skipped, 0 risky.
3. No frontend layout or styling changes were introduced.

Next immediate tasks:
1. Start Phase 3 planning for Laravel 11 with websocket backend replacement strategy.
2. Keep backend compatibility shims (legacy Jetstream aliases) until frontend templates are intentionally modernized in a dedicated non-design-changing refactor.
