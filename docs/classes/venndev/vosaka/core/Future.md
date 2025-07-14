***

# Future

Future class for creating Result and Option instances



* Full name: `\venndev\vosaka\core\Future`
* This class is marked as **final** and can't be subclassed
* This class is a **Final class**




## Methods


### new

Create a new Result instance wrapping a Generator task

```php
public static new(\Generator $task): \venndev\vosaka\core\Result
```



* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$task` | **\Generator** | The generator task to wrap |


**Return Value:**

The Result instance




***

### ok

Create a successful Result with a value

```php
public static ok(mixed $value): \venndev\vosaka\core\Ok
```



* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$value` | **mixed** | The success value |


**Return Value:**

The successful result




***

### err

Create an error Result with an error

```php
public static err(mixed $error): \venndev\vosaka\core\Err
```



* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$error` | **mixed** | The error value |


**Return Value:**

The error result




***

### some

Create an Option with a value

```php
public static some(mixed $value): \venndev\vosaka\core\Some
```



* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$value` | **mixed** | The value to wrap |


**Return Value:**

The Some option




***

### none

Create an empty Option

```php
public static none(): \venndev\vosaka\core\None
```



* This method is **static**.





**Return Value:**

The None option




***


***
> Automatically generated on 2025-07-14
