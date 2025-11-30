# financial-module

## Installation
```
composer require vicky-project/financial
```

## Traits
Add trait in your User model
```php
<?php

use Modules\Financial\Traits\HasWallets;

class User extends Model {
use HasWallets;
}
```
