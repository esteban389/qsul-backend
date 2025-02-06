Commands needed:
composer install
//give permissions to storage folder
php artisan migrate --seed
php artisan queue:work
php artisan storage:link