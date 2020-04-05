Shopware 6 plugin base
=============

This library contains abstractions that may be useful in all custom plugins.
It provides following features:
* Migration helper
  * Helper class for new mail types
  * More is comming soon 

Installation
------------

Require the composer package in your plugin:

```
composer require momocode/shopware-6-plugin-base
```

Add composer autoloader to plugin bootstrap class and let your plugin inherit the 
`Momocode\Shopware6Base\Plugin` abstraction

```php
<?php

namespace MyPlugin;

use Momocode\Shopware6Base\Plugin;

// Autload extra dependencies
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
}

class MyPlugin extends Plugin {}
```

With that, all `reverse` functions of your migrations will be called on plugin uninstall,
if they extend the `AbstractMigration`.

Migrations
----------
Here are some helper classes for some usual Shopware 6 migrations. At first 
create a migration for your plugin with the following command:

```
./bin/console database:create-migration -p YourPluginName --name MigrationDescription
```

In your plugins `Migration` folder there is a new migration file now. It extends the 
`Shopware\Core\Framework\Migration\MigrationStep` class. Now you can change the
extend to one of the following helper classes. 

Mail Type Migration
-------------------
If you want to add new mail template types, you can use the `MailTypeMigration`
class. If your migrations extends the `MailTypeMigration`, it needs only two functions.
Here is an example:

```php
<?php declare(strict_types=1);

namespace YourPlugin\Migration;

use Momocode\Shopware6Base\Migration\MailTypeMigration;
use Shopware\Core\Framework\Uuid\Uuid;

class Migration1586007577NewMailTypes extends MailTypeMigration
{

    public function getCreationTimestamp(): int
    {
        return 1586007577;
    }

    protected function getMailTypeMapping(): array
    {
        return [
            'your_technical_template_name' => [
                'id' => Uuid::randomHex(),
                'name' => 'Your english template description',
                'nameDe' => 'Deine deutsche Beschreibung',
                'availableEntities' => json_encode(['salesChannel' => 'sales_channel']),
            ],
        ];
    }
}
```

Now the helper class will call the `getMailTypeMapping` and create your mail types.
