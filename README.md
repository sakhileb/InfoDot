# InfoDot

InfoDot is a Laravel-based platform for entrepreneurs and professionals to share practical business solutions, ask questions, and collaborate in teams.

## Features

- Solutions hub with step-by-step solution authoring
- Questions and answers workflow with solved status
- Threaded comments and likes on questions and solutions
- User profiles with social connections (associates/followers)
- Real-time search across users, solutions, and questions
- Team management with invitations and member roles
- Jetstream authentication (register, login, email verification, password reset)
- Profile editing and protected dashboard routes
- Public marketing pages (about, features, FAQ, terms, contact)

## Tech Stack

- PHP 8.3
- Laravel 10
- Laravel Jetstream + Fortify + Sanctum
- Livewire 2
- Tailwind CSS
- Laravel Scout (with TNTSearch/Algolia support)
- BeyondCode Laravel WebSockets + Pusher integration

## Main Application Areas

- Solutions:
	- Browse solutions: `/solutions`
	- Create solution: `/solution/create`
	- View solution: `/solution/view/{id}`
- Questions:
	- Browse questions: `/questions`
	- Ask question: `/questions/ask`
	- View question: `/question/view/{qid}`
- Profile:
	- View user profile: `/user/profile/{id}`
	- Edit own profile: `/user/profile/edit`

Protected pages require authentication and verified email.

## Public Pages

- Landing page: `/`
- About: `/about`
- Features: `/features`
- FAQs: `/faqs`
- Terms: `/terms`
- Contact: `/contact`
- Complaints: `/complains`
- Search results: `/solution-results`

## Local Setup

1. Install dependencies:

```bash
composer install
npm install
```

2. Configure environment:

```bash
cp .env.example .env
php artisan key:generate
```

3. Run migrations:

```bash
php artisan migrate
```

4. Build assets:

```bash
npm run production
```

5. Start the app:

```bash
php artisan serve
```

## Testing

Run authentication-focused tests:

```bash
composer test:auth
```

Run full test suite:

```bash
php artisan test
```

## Documentation

For a deeper, full feature breakdown, see [README_FEATURES.md](README_FEATURES.md).

For deployment instructions, see [PRODUCTION_DEPLOYMENT.md](PRODUCTION_DEPLOYMENT.md).

## License

This project is licensed under the MIT License. See [LICENSE](LICENSE).

