***

# OptionsBuilder

Fluent options builder



* Full name: `\venndev\vosaka\net\options\OptionsBuilder`




## Methods


### tcpClient

Create TCP client options

```php
public static tcpClient(): \venndev\vosaka\net\options\SocketOptions
```



* This method is **static**.








***

### tcpServer

Create TCP server options

```php
public static tcpServer(): \venndev\vosaka\net\options\ServerOptions
```



* This method is **static**.








***

### tlsClient

Create SSL/TLS client options

```php
public static tlsClient(): \venndev\vosaka\net\options\SocketOptions
```



* This method is **static**.








***

### tlsServer

Create SSL/TLS server options

```php
public static tlsServer(string $cert, string $key): \venndev\vosaka\net\options\ServerOptions
```



* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$cert` | **string** |  |
| `$key` | **string** |  |





***

### highPerformance

Create high-performance options

```php
public static highPerformance(): \venndev\vosaka\net\options\SocketOptions
```



* This method is **static**.








***

### lowLatency

Create low-latency options

```php
public static lowLatency(): \venndev\vosaka\net\options\SocketOptions
```



* This method is **static**.








***


***
> Automatically generated on 2025-07-24
