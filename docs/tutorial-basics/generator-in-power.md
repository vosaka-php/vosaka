---
sidebar_position: 1
---

# Generator power in PHP

### Example with 'not' Generator

```php title="src/not_generator.php"
function getRange ($max = 10) {
    $array = [];
    for ($i = 1; $i < $max; $i++) {
        $array[] = $i;
    }
    return $array;
}

foreach (getRange(PHP_INT_MAX) as $range) {
    echo "Dataset {$range} <br>";
}
```

**Result:**
```yml
Fatal error: Allowed memory size of 134217728 bytes exhausted (tried to allocate 134217736 bytes) in php-wasm run script on line 5
```

### Example with Generator

```php title="src/it_generator.php"
function getRange ($max = 10) {
    for ($i = 1; $i < $max; $i++) {
        yield $i;
    }
}

foreach (getRange(PHP_INT_MAX) as $range) {
    echo "Dataset {$range} <br>";
}
```

**Result:**
```yml
Dataset 1
Dataset 2
Dataset 3
Dataset 4
Dataset 5
Dataset 6
Dataset 7
Dataset 8
Dataset 9
...
```

:::tip Conclude
In many cases, we need to process large datasets—like system logs or massive arrays—without consuming excessive memory. Instead of loading everything at once, using generators allows us to retrieve only the data we need, making processing far more memory-efficient.
:::
