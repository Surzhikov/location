# Location
Eloquent-style wrapper for DaData.ru geocoding (for Laravel)

## Example

```php
use \Surzhikov\Location\Location;
$locations = Location::where('address', 'like', 'Мосва, улица Ленинский просп дом 4')
    ->limit(10)
    ->where('level', '>=', 'city')
    ->get();
```

