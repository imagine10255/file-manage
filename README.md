## FileManage for Laravel 5

Better Laravel Exception Handler

[![Latest Stable Version](https://poser.pugx.org/imagine10255/schema-build/version)](https://packagist.org/packages/imagine10255/schema-build)
[![Total Downloads](https://poser.pugx.org/imagine10255/schema-build/downloads)](https://packagist.org/packages/imagine10255/schema-build)
[![Latest Unstable Version](https://poser.pugx.org/imagine10255/schema-build/v/unstable)](//packagist.org/packages/imagine10255/schema-build)
[![License](https://poser.pugx.org/imagine10255/schema-build/license)](https://packagist.org/packages/imagine10255/schema-build)
[![Monthly Downloads](https://poser.pugx.org/imagine10255/schema-build/d/monthly)](https://packagist.org/packages/imagine10255/schema-build)
[![Daily Downloads](https://poser.pugx.org/imagine10255/schema-build/d/daily)](https://packagist.org/packages/imagine10255/schema-build)
[![composer.lock available](https://poser.pugx.org/imagine10255/schema-build/composerlock)](https://packagist.org/packages/imagine10255/schema-build)

## Features
- File upload management tool, with JQueryFileUpload
- Image Thumbnail processing
- Associated database

## Installing

To get the latest version of Laravel Exceptions, simply require the project using [Composer](https://getcomposer.org):

```bash
composer require Imagine10255/file-manage
```

Instead, you may of course manually update your require block and run `composer update` if you so choose:

```json
{
    "require": {
        "Imagine10255/file-manage": "^1.0"
    }
}
```

Include the service provider within `config/app.php`. The service povider is needed for the generator artisan command.

```php
'providers' => [
    ...
    Imagine10255\FileManage\FileManageServiceProvider::class,
    ...
];
```