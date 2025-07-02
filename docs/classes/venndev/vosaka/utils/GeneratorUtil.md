***

# GeneratorUtil

GeneratorUtil class for utility functions related to generator handling.

This utility class provides helper methods for working with PHP generators
in a safe and reliable manner. It includes functions for safely extracting
return values from generators and handling edge cases that can occur when
working with generator objects in async contexts.

The class is particularly useful in the VOsaka async runtime where generators
are heavily used for implementing coroutines and async operations.

* Full name: `\venndev\vosaka\utils\GeneratorUtil`
* This class is marked as **final** and can't be subclassed
* This class is a **Final class**




## Methods


### getReturnSafe

Safely get the return value from a generator.

```php
public static getReturnSafe(\Generator $value): mixed
```

Attempts to retrieve the return value from a completed generator using
Generator::getReturn(). If an exception occurs (such as when the generator
hasn't completed or doesn't have a return value), returns null instead
of propagating the exception.

This method is useful for extracting return values from generators in
contexts where exceptions would be problematic or when you want to
provide a default value for generators that don't explicitly return
anything.

* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$value` | **\Generator** | The generator to extract the return value from |


**Return Value:**

The generator's return value, or null if extraction fails




***


***
> Automatically generated on 2025-07-02
