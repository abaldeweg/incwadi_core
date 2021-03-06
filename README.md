# incwadi/core

incwadi is a book database to manage your books.

## How it was made

An article can be found here <https://medium.com/@A.Baldeweg/i-was-trying-new-things-accf33792e86>.

## Requirements

- PHP 7.4
- MySQL
- SSH Access
- PHP Composer
- Symfony Binary (dev only)

## Getting Started

Clone the repository:

```shell
git clone https://github.com/abaldeweg/incwadi_core.git
```

Create the file `.env.local` and `.env.test.local` with the following content. Please fit it to your needs.

```shell
DATABASE_URL=mysql://DB_USER:DB_PASSWORD@localhost:3306/DB_NAME
```

Then install the composer dependencies and create the database.

In `prod` run the following command.

```shell
bin/setup
```

If you are in `dev` run the following commands.

```shell
composer install
bin/console doctrine:database:create
bin/console doctrine:migrations:migrate -n
bin/console doctrine:fixtures:load -n
```

## Auth

To authenticate your users, you need to generate the SSL keys under `config/jwt/`.

```shell
openssl genrsa -out config/jwt/private.pem -aes256 4096
openssl rsa -pubout -in config/jwt/private.pem -out config/jwt/public.pem
```

## Apache Webserver

Point the web root to the `public` dir.

You also need this header for apache.

```apache
SetEnvIf Authorization "(.*)" HTTP_AUTHORIZATION=$1
```

More info on that: <https://github.com/lexik/LexikJWTAuthenticationBundle/blob/master/Resources/doc/index.md#important-note-for-apache-users>

Configure your webserver to redirect all requests to the `index.php` file.

```apache
<IfModule mod_rewrite.c>
  RewriteEngine On
  RewriteCond %{ENV:REDIRECT_STATUS} ^$
  RewriteRule ^index\.php(?:/(.*)|$) %{ENV:BASE}/$1 [R=301,L]
  RewriteCond %{REQUEST_FILENAME} -f
  RewriteRule ^ - [L]
  RewriteRule ^ %{ENV:BASE}/index.php [L]
</IfModule>
```

## Update

Just call the following command, if you are updating the production environment.

```shell
bin/setup
```

## Branches

Branches can only be created on the command line.

Find out what branches are existing and the corresponding id for a specific branch.

```shell
bin/console branch:list
```

Creating a new branch is straightforward. Replace `[NAME]` with your desired name.

```shell
bin/console branch:new [NAME]
```

## Users Management

Fetching a list with all users and their corresponding id:

```shell
bin/console user:list
```

Create a new user and replace `[NAME]` with the desired name of the user. Set `[ROLE]` that's either `ROLE_USER` or `ROLE_ADMIN`. The `[BRANCH]` is the id of the branch the user is supposed to be a part of.

```shell
bin/console user:new [NAME] [ROLE] [BRANCH]
```

You can of course delete a user. Replace `[ID]` with the id of the user.

```shell
bin/console user:delete [ID]
```

If the user has forgotten the password, you can reset it with this command. Replace `[ID]` with the id of the user.

```shell
bin/console user:reset-password [ID]
```

## Uploading Images

You can upload JPEG and PNG images up to 10240KB. All images are saved as JPEG with quality of 75%. The source file is only saved temporarily.
