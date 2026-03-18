# Laravel 9 Prep: Framework-Coupled Package Isolation

## Goal
Make Laravel 9 preparation explicit by identifying the package-coupled touchpoints that must be preserved during the major-version jump.

This is backend-only prep work.
No frontend layout, component styling, or Blade visual structure should be changed as part of this phase.

## Packages That Are Coupled to the Current Framework Layer

### 1. facade/ignition
Current role:
- Dev-only exception page package for Laravel 8.

Isolation status:
- No application code references it directly.
- It is isolated to Composer/dev tooling.

Laravel 9 action:
- Replace with `spatie/laravel-ignition` during the Laravel 9 dependency jump.
- No runtime code migration should depend on app-level Ignition imports because none are used currently.

### 2. laravel/jetstream
Current role:
- Teams, profile/account management, invitation flow, roles, API token integration.

App touchpoints:
- [app/Providers/JetstreamServiceProvider.php](app/Providers/JetstreamServiceProvider.php)
- [app/Actions/Jetstream/CreateTeam.php](app/Actions/Jetstream/CreateTeam.php)
- [app/Actions/Jetstream/UpdateTeamName.php](app/Actions/Jetstream/UpdateTeamName.php)
- [app/Actions/Jetstream/AddTeamMember.php](app/Actions/Jetstream/AddTeamMember.php)
- [app/Actions/Jetstream/InviteTeamMember.php](app/Actions/Jetstream/InviteTeamMember.php)
- [app/Actions/Jetstream/RemoveTeamMember.php](app/Actions/Jetstream/RemoveTeamMember.php)
- [app/Actions/Jetstream/DeleteTeam.php](app/Actions/Jetstream/DeleteTeam.php)
- [app/Actions/Jetstream/DeleteUser.php](app/Actions/Jetstream/DeleteUser.php)
- [app/Models/Team.php](app/Models/Team.php)
- [app/Models/Membership.php](app/Models/Membership.php)
- [app/Models/TeamInvitation.php](app/Models/TeamInvitation.php)
- [app/Models/User.php](app/Models/User.php)
- [routes/jetstream.php](routes/jetstream.php)
- [resources/views/teams/show.blade.php](resources/views/teams/show.blade.php)
- [resources/views/teams/create.blade.php](resources/views/teams/create.blade.php)

Isolation objective:
- Keep framework coupling concentrated in provider/actions/models, not spread across unrelated business logic.
- Preserve role names, action bindings, and custom models through automated tests.

### 3. laravel/fortify
Current role:
- Auth pipeline and account lifecycle actions.

App touchpoints:
- [app/Providers/FortifyServiceProvider.php](app/Providers/FortifyServiceProvider.php)
- [app/Actions/Fortify/CreateNewUser.php](app/Actions/Fortify/CreateNewUser.php)
- [app/Actions/Fortify/UpdateUserProfileInformation.php](app/Actions/Fortify/UpdateUserProfileInformation.php)
- [app/Actions/Fortify/UpdateUserPassword.php](app/Actions/Fortify/UpdateUserPassword.php)
- [app/Actions/Fortify/ResetUserPassword.php](app/Actions/Fortify/ResetUserPassword.php)
- [app/Actions/Fortify/PasswordValidationRules.php](app/Actions/Fortify/PasswordValidationRules.php)

Isolation objective:
- Preserve contract bindings and login/two-factor rate limiter names.
- Avoid embedding Fortify assumptions outside the provider/actions layer.

### 4. laravel/sanctum
Current role:
- API token support on the user model and CSRF cookie endpoint.

App touchpoints:
- [app/Models/User.php](app/Models/User.php)
- [app/Http/Kernel.php](app/Http/Kernel.php)
- [routes/jetstream.php](routes/jetstream.php)

Isolation objective:
- Preserve trait-based integration on the user model.
- Keep Sanctum assumptions limited to auth/kernel/model boundaries.

### 5. livewire/livewire
Current role:
- Interactive questions, solutions, comments, search, associates components.

App touchpoints:
- [app/Http/Livewire/Question.php](app/Http/Livewire/Question.php)
- [app/Http/Livewire/QuestionList.php](app/Http/Livewire/QuestionList.php)
- [app/Http/Livewire/SolutionList.php](app/Http/Livewire/SolutionList.php)
- [app/Http/Livewire/Comments.php](app/Http/Livewire/Comments.php)
- [app/Http/Livewire/Comment.php](app/Http/Livewire/Comment.php)
- [app/Http/Livewire/Associates.php](app/Http/Livewire/Associates.php)
- [app/Http/Livewire/Search.php](app/Http/Livewire/Search.php)

Isolation objective:
- Keep Livewire-specific state and behavior inside component classes.
- Preserve Blade views and styling as-is during backend upgrade work.

### 6. laravel/scout + teamtnt driver
Current role:
- Search integration and TNTSearch driver.

App touchpoints:
- [app/Models/Obj.php](app/Models/Obj.php)
- [config/scout.php](config/scout.php)

Isolation objective:
- Preserve current search driver behavior while framework is upgraded.
- Keep SQLite fallback behavior working independently of Scout/TNT changes.

### 7. beyondcode/laravel-websockets
Current role:
- Current self-hosted websocket server and dashboard.

App touchpoints:
- [config/websockets.php](config/websockets.php)
- [config/broadcasting.php](config/broadcasting.php)
- [app/Support/Broadcasting/WebsocketStrategy.php](app/Support/Broadcasting/WebsocketStrategy.php)

Isolation objective:
- Use `WebsocketStrategy` as the seam for later replacement.
- Do not let package-specific assumptions spread beyond config/provider/support boundaries.

## Prep Work Completed
1. CI matrix added for PHP 8.3 and 8.4.
2. Broadcast/notification regression tests added.
3. Websocket strategy abstraction and tests added.
4. Framework-coupled provider/model behavior now being locked with regression tests.

## Laravel 9 PR Entry Criteria
1. All framework-coupled regression tests passing.
2. Ignition replacement plan ready.
3. CORS replacement explicitly deferred to Laravel 9 step.
4. No frontend visual/style changes included in prep PR.
