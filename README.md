# CRUD Package
[![Latest Version](https://img.shields.io/github/v/release/hundaljasz/crud-package.svg?style=flat-square)](https://github.com/hundaljasz/crud-package/releases)

[![Issues](https://img.shields.io/github/issues/hundaljasz/crud-package.svg?style=flat-square)](https://github.com/hundaljasz/crud-package/issues)

[![Stars](https://img.shields.io/github/stars/hundaljasz/crud-package.svg?style=flat-square)](https://github.com/hundaljasz/crud-package/stargazers)

CRUD package is an package that makes your crud operations easy and hassle free.
- Easy to integrate with your existing project.
- use alias or provider to import the package.

## Installing CRUD Package

The recommended way to install CRUD Package is through
[Composer](https://getcomposer.org/).

```bash
composer require devjaskirat/crud
```

## Steps

import ```php 
devjaskirat\crud\CrudServiceProvider::class,
``` in config/app.php under providers 
or if you wish to make alias 
```php 
    'CRUD' => devjaskirat\crud\CrudServiceProvider::class,
```  in config/app.php under aliases.

after importing provider or alias you are ready to go.

