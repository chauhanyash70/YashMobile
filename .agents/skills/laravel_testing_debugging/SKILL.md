---
name: laravel_testing_debugging
description: Guidelines for running tests, checking logs, and debugging applications in a Laravel environment.
---

# Laravel Testing & Debugging Skill

Use this skill when diagnosing errors, reviewing application logs, or running test suites in the Laravel project.

## 1. Inspecting Application Logs
- Error logs are stored in `storage/logs/laravel.log`.
- To view the latest errors, you can run:
  ```powershell
  Get-Content -Tail 100 storage/logs/laravel.log
  ```
- Make sure permission issues on the `storage/` and `bootstrap/cache/` directories are resolved if Laravel complains about writing to log files.

## 2. Debugging Techniques
- **Eloquent Query Logging**: To debug queries being run, you can enable the query log:
  ```php
  DB::enableQueryLog();
  // your queries...
  dd(DB::getQueryLog());
  ```
- **Logging Exception Traces**: Always catch exceptions and log them properly:
  ```php
  try {
      // operation
  } catch (\Exception $e) {
      Log::error("Operation failed: " . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
  }
  ```

## 3. Running Automated Tests
- To run tests using PHPUnit:
  ```bash
  php artisan test
  ```
  or
  ```bash
  vendor/bin/phpunit
  ```
- To run a specific test class or method:
  ```bash
  php artisan test --filter=InvoiceTest
  ```
