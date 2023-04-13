# Laravel Eloquent phpDoc Generator

[![codecov](https://codecov.io/gh/sethsandaru/eloquent-docs/branch/main/graph/badge.svg?token=7KWW0SKF9P)](https://codecov.io/gh/sethsandaru/eloquent-docs)
[![Latest Stable Version](http://poser.pugx.org/sethphat/eloquent-docs/v)](https://packagist.org/packages/sethphat/eloquent-docs)
[![Total Downloads](http://poser.pugx.org/sethphat/eloquent-docs/downloads)](https://packagist.org/packages/sethphat/eloquent-docs)
[![Build and test [MYSQL]](https://github.com/sethsandaru/eloquent-docs/actions/workflows/build_mysql.yaml/badge.svg?branch=main)](https://github.com/sethsandaru/eloquent-docs/actions/workflows/build_mysql.yaml)
[![Build and test [SQLite]](https://github.com/sethsandaru/eloquent-docs/actions/workflows/build_sqlite.yaml/badge.svg?branch=main)](https://github.com/sethsandaru/eloquent-docs/actions/workflows/build_sqlite.yaml)
[![Build and test [PostgreSQL]](https://github.com/sethsandaru/eloquent-docs/actions/workflows/build_postgresql.yaml/badge.svg?branch=main)](https://github.com/sethsandaru/eloquent-docs/actions/workflows/build_postgresql.yaml)
[![Build on specific PHP & Laravel versions](https://github.com/sethsandaru/eloquent-docs/actions/workflows/build_laravel.yaml/badge.svg)](https://github.com/sethsandaru/eloquent-docs/actions/workflows/build_laravel.yaml)

Quickly generate the phpDoc for your Eloquent Model. Make your Eloquent super friendly with IDEs (eg PHPStorm).

And maintaining the phpDoc of your models don't have to be a pain in the ass, should be:

- Fun
- Fast
- Reliable

And, welcome to Eloquent phpDoc Generator, which going to help you to achieve the 3 points above 🎉

## What will Eloquent phpDoc Generator will generate?
- Table name
- Table columns (with types)
- Model relationships
- Model attributes/accessors

## Available For / Requirements
- PHP 8.1 / 8.2
- Laravel 9 / 10

## Install
Install as dev-dependencies is enough, since you are only going to use this command on `local/development` env.

```bash
composer require sethphat/eloquent-docs --dev
```

Laravel auto-discovery will automatically do the magic for you.

## Use the command

```bash
php artisan eloquent:phpdoc "App\Models\User" # view only
php artisan eloquent:phpdoc "App\Models\User" --write # view & write to file
php artisan eloquent:phpdoc "App\Models\User" --short-class # new option - use short class instead of full namespace path

# from v1.2.0
php artisan eloquent:bulk-phpdoc "app/Models/*.php" # bulk generation (force write mode)
```

Result:

<details>

```bash
====== Start PHPDOC scope of App\Models\User
/**
* Table: users
*
* === Columns ===
* @property int $id
* @property string $name
* @property string $email
* @property \Carbon\Carbon|null|null $email_verified_at
* @property string $password
* @property string|null $remember_token
* @property \Carbon\Carbon|null $created_at
* @property \Carbon\Carbon|null $updated_at
*
* === Relationships ===
* @property-read \App\Models\Emails[]|\Illuminate\Database\Eloquent\Collection|null $emails
* @property-read \App\Models\UserDetails|null $userDetail
*
* === Accessors/Attributes ===
* @property-read string $full_name
* @property-read string $is_admin
* @property-read string $user_type
* @property-read int $total_salary
* @property-read mixed $levels
* @property-read mixed $first_name
* @property-read mixed $last_name
*/
====== End PHPDOC scope of App\Models\User
Wrote phpDoc scope to /<my-path>/app/Models/User.php
Thank you for using EloquentDocs!
```

</details>

Note: if you haven't installed `doctrine/dbal` as your dev-dependency, 
then once you trigger the command for the first time, it will help you to install the needful dependency

## Best practices
- Use `$casts` in your model, in order to help EloquentPhpDoc generate better types for you (array, Carbon,...)
- For `get*Attribute` accessor, always declare the return type

Note: Eloquent new `Attribute` class utilize the data via Closure, thus we can't declare any return type for any attributes.
For this case, EloquentPhpDoc will always return `mixed`

## Release logs
- v1.0.0
  - First version
  - View & Update phpDoc for a single Model at a time
- v1.1.0
  - `--short-class`
  - Fixed some issues
- v1.1.1
  - Fixed issue when generating a table that has `enum` column
- v1.1.2
  - Fixed issue when first-time install the library that made Laravel discovery went wrong.
- v1.1.3 & v1.1.4
  - Improved the indents
  - Supported Laravel 10
  - Deprecated Laravel 8
  - Deprecated PHP 8.0
- v1.2.0
  - New command to bulk generate from a given model path.
    - `php artisan eloquent:bulk-phpdoc "app/Models/*.php"`
  - Fixed an issue where accessors/attributes being generated as snake_case. Should be camelCase.

## Contribute to the library

Feel free to fork this library and sending a PR here.

Note: all the contributions need to follow PSR-12 and cover everything under unit testing.

## LICENSE

MIT License

## Made by

- [Seth Phat](https://github.com/sethsandaru)
- And contributors
