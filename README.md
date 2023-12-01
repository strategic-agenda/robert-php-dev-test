# Project uses Node v18, php 7, MySQL

# Installation steps

1. cd /backend
2. Create config.php file with your values
3. Create MySQL schema
4. Import DB dump from /backend/database/schema.sql
5. Run composer install
6. Run php -S localhost:8000
7. cd ..
8. npm install
9. npm run dev
10. Navigate to http://localhost:3000

To run tests
1. cd /backend
2. Run vendor/bin/phpunit tests