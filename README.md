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

Import in config/app.php under providers 
```php 
    'providers' => [
        devjaskirat\crud\CrudServiceProvider::class
        ];
``` 

or if you wish to make alias 

```php 
'aliases' => [
    'CRUD' => devjaskirat\crud\CrudServiceProvider::class
    ];
```  
in config/app.php under aliases.

after importing provider or alias you are ready to go.

run migration command to create images table in your DB to upload images 
```bash
    php artisan migrate
```

in images table you have id of the record whose image you want to upload with table name to map the record with accurate table.

after running migration command run below command to create storage link in public directory.

```bash
    php artisan storage:link
```
## Example

After importing you can import the provider or alias in your controller.

```php
    use devjaskirat\crud\Http\Controllers\CrudService
```

or 

```php
    use CRUD;
```

Create the object

```php
public function __construct(CrudService $crud){
        $this->crud = $crud;
    }
```
in CRUD Paclage you have basic CRUD functions available to use 
for instance, to store data:-

## Store

```php
    /**
     * @param  mixed  $request(from user with data),$table(in which table you want to store data)
     * ,$imageFolder(optional if image is available, where you want to uplaod it)
     * ,$imageField(image field in the request, in which file is available)
     * ,$prefix(optional if you want to add a prefix to the image name)
     * @return mixed array will be returned with status, type, last inserted id, and message.
     */
    $this->crud->store($request,$table,$imageFolder,$imageField,$prefix); 
```

## Update 

```php
    /**
     * @param  mixed  $request(from user with data),$id(which record you want to update)
     * ,$table(in which table you want to update data)
     * ,$imageFolder(optional if image is available, where you want to uplaod it)
     * ,$imageField(image field in the request, in which file is available)
     * ,$prefix(optional if you want to add a prefix to the image name)
     * @return mixed array will be returned with status, type, id, and message.
     */
    $this->crud->update($request,$id,$table,$imageFolder,$imageField,$prefix);
```

## Delete

with Delete images will also get deleted from storage if available.

```php
    /**
     * @param  mixed $id(which record you want to delete),$table(from which table you want to delete data)
     * @return mixed array will be returned with status, type, id, and message.
     */
    $this->crud->delete($id,$table);
```

## DeleteImage

if you wish to delete an image.

```php
    /**
     * @param  mixed $id(which image you want to delete), id is from images table
     * @return mixed array will be returned with status, type, id, and message.
     */
    $this->crud->deleteImage($id);
```
