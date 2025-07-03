***

# Expect

A utility class to check if a given input matches a specified type or condition.

This class provides a static method `new` that performs the type checking.

* Full name: `\venndev\vosaka\sync\mpsc\Expect`
* This class is marked as **final** and can't be subclassed
* This class is a **Final class**




## Methods


### new

Checks if the input matches the specified type or condition.

```php
public static new(mixed $input, mixed $type): bool
```

This method supports:
- Class instance checks
- Callable checks
- Primitive type checks (int, string, float, bool, array, object, callable, resource, null)

* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$input` | **mixed** | The input value to check |
| `$type` | **mixed** | The type or condition to check against |


**Return Value:**

Returns true if the input matches the type, false otherwise




***


***
> Automatically generated on 2025-07-03
