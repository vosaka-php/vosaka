***

# Command

Command class for executing external processes asynchronously.

Provides a high-level interface for spawning and managing external processes
in an asynchronous manner. Supports configurable stdin, stdout, and stderr
redirection, process spawning, waiting for completion, and termination.

The class uses the Process class internally but provides a more convenient
fluent interface for common process operations. All operations return Result
objects that can be awaited using VOsaka's async runtime.

* Full name: `\venndev\vosaka\process\Command`
* This class is marked as **final** and can't be subclassed
* This class is a **Final class**



## Properties


### process



```php
private \venndev\vosaka\process\Process $process
```






***

### descriptorSpec



```php
private array $descriptorSpec
```






***

## Methods


### __construct

Constructor for Command.

```php
public __construct(string $command): mixed
```

Creates a new Command instance with the specified command string.
The command will be executed when spawn() is called. Descriptor
specifications can be configured before spawning using the fluent
interface methods.






**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$command` | **string** | The command string to execute |





***

### c

Create a new Command instance (factory method).

```php
public static c(string $command): self
```

Convenience factory method for creating Command instances.
The 'c' stands for 'create' and provides a shorter syntax
for command creation.

* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$command` | **string** | The command string to execute |


**Return Value:**

A new Command instance




***

### stdin

Configure stdin descriptor for the process.

```php
public stdin(array $descriptorSpec): self
```

Sets the stdin descriptor specification for the process. If no
descriptor specifications have been set yet, initializes them
with piped defaults. The descriptor spec should follow PHP's
proc_open() format.






**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$descriptorSpec` | **array** | Descriptor specification for stdin |


**Return Value:**

This Command instance for method chaining




***

### stdout

Configure stdout descriptor for the process.

```php
public stdout(array $descriptorSpec): self
```

Sets the stdout descriptor specification for the process. If no
descriptor specifications have been set yet, initializes them
with piped defaults. The descriptor spec should follow PHP's
proc_open() format.






**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$descriptorSpec` | **array** | Descriptor specification for stdout |


**Return Value:**

This Command instance for method chaining




***

### stderr

Configure stderr descriptor for the process.

```php
public stderr(array $descriptorSpec): self
```

Sets the stderr descriptor specification for the process. If no
descriptor specifications have been set yet, initializes them
with piped defaults. The descriptor spec should follow PHP's
proc_open() format.






**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$descriptorSpec` | **array** | Descriptor specification for stderr |


**Return Value:**

This Command instance for method chaining




***

### spawn

Spawn the process asynchronously.

```php
public spawn(): \venndev\vosaka\core\Result
```

Starts the external process with the configured command and descriptor
specifications. If no descriptors have been configured, uses piped
defaults for stdin, stdout, and stderr. Returns a Result that can be
awaited to get the Command instance back once the process is started.

The process runs asynchronously and doesn't block the event loop.
Use wait() to wait for the process to complete.







**Return Value:**

A Result containing this Command instance or an error




***

### wait

Wait for the process to complete asynchronously.

```php
public wait(): \venndev\vosaka\core\Result
```

Waits for the spawned process to finish execution and returns its
result. This method should be called after spawn() to wait for
the process completion. The operation is asynchronous and won't
block the event loop.







**Return Value:**

A Result containing the process result or an error




***

### kill

Kill the running process asynchronously.

```php
public kill(): \venndev\vosaka\core\Result
```

Terminates the spawned process if it's currently running. This is
useful for stopping long-running processes or cleaning up when
a process needs to be aborted. The operation is asynchronous and
returns a Result indicating success or failure.







**Return Value:**

A Result containing true on success or an error




***


## Inherited methods


### command



```php
public command(string $command): self
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$command` | **string** |  |





***

### arg



```php
public arg(string $arg): self
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$arg` | **string** |  |





***

### args



```php
public args(array $args): self
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$args` | **array** |  |





***


***
> Automatically generated on 2025-06-26
