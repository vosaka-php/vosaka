***

# SocketOptions

Socket options configuration



* Full name: `\venndev\vosaka\net\options\SocketOptions`



## Properties


### options



```php
public array $options
```






***

## Methods


### setReuseAddr



```php
public setReuseAddr(bool $enable): self
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$enable` | **bool** |  |





***

### setReusePort



```php
public setReusePort(bool $enable): self
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$enable` | **bool** |  |





***

### setNoDelay



```php
public setNoDelay(bool $enable): self
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$enable` | **bool** |  |





***

### setKeepAlive



```php
public setKeepAlive(bool $enable): self
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$enable` | **bool** |  |





***

### setLinger



```php
public setLinger(bool|int $linger): self
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$linger` | **bool&#124;int** |  |





***

### setSendBufferSize



```php
public setSendBufferSize(int $size): self
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$size` | **int** |  |





***

### setReceiveBufferSize



```php
public setReceiveBufferSize(int $size): self
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$size` | **int** |  |





***

### setTimeout



```php
public setTimeout(float $seconds): self
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$seconds` | **float** |  |





***

### enableSsl



```php
public enableSsl(bool $enable = true): self
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$enable` | **bool** |  |





***

### setSslCertificate



```php
public setSslCertificate(string $path): self
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$path` | **string** |  |





***

### setSslKey



```php
public setSslKey(string $path): self
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$path` | **string** |  |





***

### setSslCa



```php
public setSslCa(string $path): self
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$path` | **string** |  |





***

### setVerifyPeer



```php
public setVerifyPeer(bool $verify): self
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$verify` | **bool** |  |





***

### setAllowSelfSigned



```php
public setAllowSelfSigned(bool $allow): self
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$allow` | **bool** |  |





***

### toArray



```php
public toArray(): array
```












***

### create



```php
public static create(): self
```



* This method is **static**.








***


***
> Automatically generated on 2025-07-24
