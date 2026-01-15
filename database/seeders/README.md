# Database Seeders

This directory contains database seeders for the InfoDot Laravel 11 application.

## Available Seeders

### UserSeeder
Creates test users for the application:
- 1 admin user (admin@infodot.test)
- 4 named test users (john@infodot.test, jane@infodot.test, bob@infodot.test, alice@infodot.test)
- 10 additional random users via factory

**Default password for all users:** `password`

### QuestionSeeder
Creates 10 sample questions covering various Laravel topics:
- Authentication
- Livewire vs Vue.js
- Database optimization
- API development
- Real-time notifications
- File uploads
- Testing strategies
- Search functionality
- Deployment
- Queue management

### AnswerSeeder
Creates 1-3 answers for each question with:
- Varied content using templates
- Random acceptance status (50% chance for first answer)
- Realistic timestamps

### SolutionSeeder
Creates 5 comprehensive step-by-step solutions:
1. Setting up Laravel 11 with Jetstream (4 steps)
2. Implementing Search with Laravel Scout (5 steps)
3. Optimizing Laravel Database Queries (5 steps)
4. Building a RESTful API with Laravel (6 steps)
5. Implementing Real-time Features with Laravel Reverb (5 steps)

### LikeSeeder
Creates likes/dislikes for:
- Questions (0-5 likes each)
- Answers (0-4 likes each)
- Solutions (0-6 likes each)

Random distribution of likes and dislikes.

### CommentSeeder
Creates comments for:
- Questions (0-3 comments each)
- Answers (0-2 comments each)
- Solutions (0-4 comments each)

Uses predefined comment templates for realistic content.

## Running Seeders

### Run All Seeders
```bash
php artisan db:seed
```

### Run Specific Seeder
```bash
php artisan db:seed --class=UserSeeder
php artisan db:seed --class=QuestionSeeder
php artisan db:seed --class=AnswerSeeder
php artisan db:seed --class=SolutionSeeder
php artisan db:seed --class=LikeSeeder
php artisan db:seed --class=CommentSeeder
```

### Fresh Migration with Seeding
```bash
php artisan migrate:fresh --seed
```

## Seeder Dependencies

The seeders have dependencies and should be run in this order:
1. **UserSeeder** (required first)
2. **QuestionSeeder** (requires UserSeeder)
3. **AnswerSeeder** (requires UserSeeder and QuestionSeeder)
4. **SolutionSeeder** (requires UserSeeder)
5. **LikeSeeder** (requires UserSeeder, QuestionSeeder, AnswerSeeder, SolutionSeeder)
6. **CommentSeeder** (requires UserSeeder, QuestionSeeder, AnswerSeeder, SolutionSeeder)

The `DatabaseSeeder` class automatically runs them in the correct order.

## Test Data Summary

After running all seeders, you will have:
- **15 users** (5 named + 10 random)
- **10 questions** with various tags
- **~20 answers** (1-3 per question)
- **5 solutions** with 4-6 steps each
- **~100 likes** distributed across questions, answers, and solutions
- **~50 comments** distributed across questions, answers, and solutions

## Customization

You can customize the seeders by:
- Modifying the number of records created
- Changing the content templates
- Adjusting the random ranges
- Adding new data patterns

## Notes

- All timestamps are randomized within the past 30 days for realistic data
- User passwords are hashed using bcrypt
- Email addresses use the `.test` TLD for testing
- All users have verified email addresses
- The seeders use database transactions for data integrity
