# datatable-bundle
symfony bundle to generate dataTable

[![PHP](https://img.shields.io/badge/PHP-7.0%2B-blue.svg)](https://secure.php.net/migration70)
[![Latest Stable Version](https://poser.pugx.org/marwen/datatable-bundle/v/stable)](https://packagist.org/packages/marwen/datatable-bundle)

# DataTables Symfony bundle

This bundle helps to generate [DataTables](http://www.datatables.net/) using [server-side processing](http://www.datatables.net/manual/server-side) mode.

## Requirements

PHP needs to be a minimum version of PHP 7.0.

Symfony must be of 2.7 or above.

## Installation

### Step 1: Download the Bundle

Open a command console, enter your project directory and execute the following command to download the latest stable version of this bundle:

```console
composer require marwen/datatable-bundle
```

This command requires you to have Composer installed globally, as explained in the [installation chapter](https://getcomposer.org/doc/00-intro.md) of the Composer documentation.

### Step 2: Enable the Bundle

If you are using Symfony 3 or below, enable the bundle by adding it to the list of registered bundles in the `app/AppKernel.php` file of your project:

```php
public function registerBundles()
{
    $bundles = [
        // ...
        new \DataTableBundle\MarwenDataTableBundle(),
    ];
}
```

## Usage

Please see the complete usage example [here](../../wiki).

