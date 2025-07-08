***

# CancelToken

CancelToken class for managing cancellation of asynchronous operations.

Provides a mechanism for signaling cancellation to asynchronous tasks and
operations. Tasks can check for cancellation status and respond appropriately
by cleaning up resources and terminating early. Supports both simple
cancellation and cancellation with custom values.

The class maintains a static registry of all tokens to ensure cancellation
state is preserved across async boundaries. Each token has a unique ID and
can be cancelled from any context.

* Full name: `\venndev\vosaka\sync\CancelToken`
* This class is marked as **final** and can't be subclassed
* This class is a **Final class**



## Properties


### isCancelled



```php
private bool $isCancelled
```






***

### cancelledValue



```php
private mixed $cancelledValue
```






***

### nextId



```php
private static int $nextId
```



* This property is **static**.


***

### id



```php
private int $id
```






***

### tokens

Static registry of all active cancel tokens.

```php
private static array&lt;int,\venndev\vosaka\sync\CancelToken&gt; $tokens
```



* This property is **static**.


***

## Methods


### __construct

Constructor for CancelToken.

```php
public __construct(): mixed
```

Creates a new cancel token with a unique ID and registers it in the
static token registry. The token starts in a non-cancelled state.










***

### new

Create a new CancelToken instance.

```php
public static new(): \venndev\vosaka\sync\CancelToken
```

This static method is a factory for creating new CancelToken objects.
It initializes the token and registers it in the static registry.

* This method is **static**.





**Return Value:**

A new instance of CancelToken




***

### cancel

Cancel the token without a specific value.

```php
public cancel(): void
```

Marks this token as cancelled, which will cause any operations checking
this token to detect the cancellation and respond appropriately. The
cancellation state is persisted in the static registry.










***

### cancelWithValue

Cancel the token with a specific cancellation value.

```php
public cancelWithValue(mixed $value): void
```

Similar to cancel() but allows specifying a custom value that can be
retrieved by operations checking for cancellation. This is useful for
providing context about why the cancellation occurred or passing
data along with the cancellation signal.






**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$value` | **mixed** | The value to associate with the cancellation |





***

### cancelFurture

Create a cancellation future that can be yielded in generators.

```php
public cancelFurture(): \Generator
```

Returns a generator that yields a CancelFuture object. This can be
used in async operations to create cancellation points where the
operation can be terminated early if cancellation is requested.

Note: There appears to be a typo in the method name - it should likely
be "cancelFuture" rather than "cancelFurture".







**Return Value:**

A generator that yields a CancelFuture




***

### isCancelled

Check if this token has been cancelled.

```php
public isCancelled(): bool
```

Returns true if the token has been cancelled via cancel() or
cancelWithValue(), false otherwise. This method checks the current
state from the static registry to ensure accuracy across async
boundaries.







**Return Value:**

True if the token is cancelled, false otherwise




***

### close

Close and cleanup the cancel token.

```php
public close(): void
```

Removes this token from the static registry and cleans up its resources.
After calling close(), the token should not be used anymore. This is
important for preventing memory leaks in long-running applications.










***

### save

Save the current token state to the static registry.

```php
private save(): void
```

Internal method that updates the token's state in the static registry.
This ensures that cancellation state is preserved and accessible
across different async contexts and execution boundaries.










***


***
> Automatically generated on 2025-07-08
