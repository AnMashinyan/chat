Run git clone https://github.com/AnMashinyan/chat.git

Run composer install

Rename '.env.example' file to '.env'

Create database 'chat' with phpMyAdmin.

Create .env file and copy everything from .env.example

Write the db name in .env file

Run php artisan key:generate

Run php artisan migrate

Run php artisan optimize

Run php artisan serve.

Run php artisan websocket:init.
