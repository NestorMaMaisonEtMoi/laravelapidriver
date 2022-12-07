# NOTE:
Ce Driver API est développé par Nestor Mamaison Et Moi afin de récupérer des models directement à partir d'une API REST 

----------------------

# API Driver For Laravel 8

An Eloquent model and Query builder with support for Restful Api Server, using the original Laravel API. This library extends the original Laravel classes, so it uses exactly the same methods.


### Installation
---------------
Installation using composer:
```bash
composer require "nestormamaisonetmoi/laravelapidriver" : "*"
```

Ajout du service provider dans le fichier `config/app.php`:
```php
Nestor\LaravelApidriver\DatabaseServiceProvider::class
```

### Configuration
----------------
Change your default database connection name in `config/database.php`:

```php 
 OPTIONNEL
'default' => 'api'
```

Ajout de la configuration vers l'API REST'

```php
'api' => [
        'driver' => 'api',
        'host' => 'localhost/v1/',
        'database' => '',
        'prefix' => '',
]
```

Ajout de la configuration pour utiliser le driver dans le nouveau model. Sauf si le driver à été mis par défaut
```php
protected $connection = 'api';
```
### Usage
--------

Créer un nouveau Model qui étend la class du package Nestor\LaravelApiDriver\Model\Model

```php
use Nestor\LaravelApidriver\Model\Model;

class User extends Model
{

}
```

Using the original Eloquent API:

```php
$users = User::where('id', '<', 100)->take(3)->get();
$user = User::find(3);
$user->delete();
```
