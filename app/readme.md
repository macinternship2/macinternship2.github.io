# AccessLocator Web App

Before developing code on the project, you should install the following dependencies:

- MySQL 5.7+
- PHP 7+
- composer (https://getcomposer.org/download/)
- npm
- gulp

WAMP, LAMP, MAMP, or XXAMP is recommended to cover the PHP and MySQL dependencies.  It also usually comes with PHPMyAdmin which is helpful for working with the database.


## Database:
- Create a database called hhaccessibility in MySQL and run the MySQL server on port 3306.
- Have a root user with an empty password.
- The root user should have permissions to create, alter any tables and manipulate data in the hhaccessibility database.

## Commands:
The following commands should be run from the hhaccessibility.github.io/app directory since that is where the gulpfile and artisan scripts exist.

- php artisan migrate
	This should create tables.

- php artisan db:seed
	This should insert data into the tables.

- gulp
	This should convert SASS to CSS and minify JavaScript.

- ./vendor/bin/phpunit in Linux/Unix/Mac or "vendor/bin/phpunit.bat" in Windows
	This should run all automated tests and they should all pass.

- php artisan serve
	This should run the application on port 8000.

## File Structure:
### Views, HTML, and CSS
Views are in the resources/views.  Individual pages are in resources/views/pages.

CSS is generated off of SASS files(*.scss).  The main source file for css/app.css is at resources/assets/sass/app.scss.

### Models
Database table classes for use with the Eloquent ORM are in the app folder.  That's hhaccessibility.github.io/app/app to avoid confusion.

### Controllers and Routes
Routes are defined in the routes directory.

Controller classes are defined in app\Http\Controllers.

### Tests
Automated test scripts are in the tests folder.

### Database design, migrations and data seeding
Database migrations create the database tables.  The migration scripts are in database/migrations.

Data is "seeded" from scripts in database/seeds/DatabaseSeeder.php which loads data from database/seeds/data.
