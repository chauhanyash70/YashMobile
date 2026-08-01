---
name: laravel_db_migrations
description: Guidelines and commands for creating and running Laravel database migrations, models, and seeders.
---

# Laravel Database Migrations & Models Skill

Use this skill when you need to make changes to the database structure, run migrations, create seeders, or generate model definitions.

## Creating Migrations
- Use `php artisan make:migration create_xxx_table` to create new tables.
- Use `php artisan make:migration add_xxx_to_yyy_table --table=yyy` to modify existing tables.
- Ensure all migration `up` methods have a corresponding `down` method to revert changes.
- Define foreign key constraints explicitly with cascade rules where appropriate:
  ```php
  $table->foreignId('user_id')->constrained()->onDelete('cascade');
  ```

## Running Migrations
- Always run `php artisan migrate` to apply new migrations.
- In local development, if you need to reset the database and rerun all migrations along with seeders, run:
  ```bash
  php artisan migrate:fresh --seed
  ```
  *(Caution: This will destroy all existing data in the database)*

## Seeders and Factories
- Use seeders in `database/seeders/` for populating initial configuration or test data.
- Use factories in `database/factories/` for generating dummy data in test scenarios.
- Run a specific seeder with:
  ```bash
  php artisan db:seed --class=SeederClassName
  ```
