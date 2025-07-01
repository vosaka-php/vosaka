
***

# Documentation



This is an automatically generated documentation for **Documentation**.


## Namespaces


### \venndev\vosaka

#### Classes

| Class | Description |
|-------|-------------|
| [`VOsaka`](./classes/venndev/vosaka/VOsaka.md) | VOsaka - Main entry point for the asynchronous runtime system.|




### \venndev\vosaka\breaker

#### Classes

| Class | Description |
|-------|-------------|
| [`CBreaker`](./classes/venndev/vosaka/breaker/CBreaker.md) | |




### \venndev\vosaka\cleanup

#### Classes

| Class | Description |
|-------|-------------|
| [`GracefulShutdown`](./classes/venndev/vosaka/cleanup/GracefulShutdown.md) | |




### \venndev\vosaka\core

#### Classes

| Class | Description |
|-------|-------------|
| [`Constants`](./classes/venndev/vosaka/core/Constants.md) | |
| [`MemoryManager`](./classes/venndev/vosaka/core/MemoryManager.md) | |
| [`Result`](./classes/venndev/vosaka/core/Result.md) | Result class for handling asynchronous task results and transformations.|




### \venndev\vosaka\core\interfaces




#### Interfaces

| Interface | Description |
|-----------|-------------|
| [`Init`](./classes/venndev/vosaka/core/interfaces/Init.md) | |
| [`Time`](./classes/venndev/vosaka/core/interfaces/Time.md) | |



### \venndev\vosaka\fs

#### Classes

| Class | Description |
|-------|-------------|
| [`File`](./classes/venndev/vosaka/fs/File.md) | File class for asynchronous file operations.|
| [`Folder`](./classes/venndev/vosaka/fs/Folder.md) | Provides comprehensive directory manipulation functions with async/await patterns,<br />proper resource management, and graceful shutdown integration. All operations<br />that involve streams, temporary files, or long-running processes use GracefulShutdown<br />for proper cleanup.|




### \venndev\vosaka\fs\exceptions

#### Classes

| Class | Description |
|-------|-------------|
| [`DirectoryException`](./classes/venndev/vosaka/fs/exceptions/DirectoryException.md) | Exception thrown when directory-specific operations fail.|
| [`FileIOException`](./classes/venndev/vosaka/fs/exceptions/FileIOException.md) | Exception thrown when file input/output operations fail.|
| [`FileNotFoundException`](./classes/venndev/vosaka/fs/exceptions/FileNotFoundException.md) | Exception thrown when a file or directory is not found.|
| [`FilePermissionException`](./classes/venndev/vosaka/fs/exceptions/FilePermissionException.md) | Exception thrown when a file system operation fails due to permission issues.|
| [`FileSystemException`](./classes/venndev/vosaka/fs/exceptions/FileSystemException.md) | Base exception class for all file system related exceptions.|
| [`InvalidPathException`](./classes/venndev/vosaka/fs/exceptions/InvalidPathException.md) | Exception thrown when a file path is invalid or malformed.|
| [`LockException`](./classes/venndev/vosaka/fs/exceptions/LockException.md) | Exception thrown when file locking operations fail.|




### \venndev\vosaka\io

#### Classes

| Class | Description |
|-------|-------------|
| [`JoinHandle`](./classes/venndev/vosaka/io/JoinHandle.md) | JoinHandle class for tracking and waiting on asynchronous task completion.|




### \venndev\vosaka\net\DNS

#### Classes

| Class | Description |
|-------|-------------|
| [`DNSClient`](./classes/venndev/vosaka/net/DNS/DNSClient.md) | DNS Client for asynchronous DNS queries with support for UDP and TCP protocols|




### \venndev\vosaka\net\dns\exceptions

#### Classes

| Class | Description |
|-------|-------------|
| [`DNSCacheException`](./classes/venndev/vosaka/net/dns/exceptions/DNSCacheException.md) | DNS Cache Exception|
| [`DNSConfigurationException`](./classes/venndev/vosaka/net/dns/exceptions/DNSConfigurationException.md) | DNS Configuration Exception|
| [`DNSException`](./classes/venndev/vosaka/net/dns/exceptions/DNSException.md) | Base DNS Exception|
| [`DNSNetworkException`](./classes/venndev/vosaka/net/dns/exceptions/DNSNetworkException.md) | DNS Network Exception|
| [`DNSParseException`](./classes/venndev/vosaka/net/dns/exceptions/DNSParseException.md) | DNS Parse Exception|
| [`DNSQueryException`](./classes/venndev/vosaka/net/dns/exceptions/DNSQueryException.md) | DNS Query Exception|
| [`DNSSECException`](./classes/venndev/vosaka/net/dns/exceptions/DNSSECException.md) | DNSSEC Exception|
| [`DNSTimeoutException`](./classes/venndev/vosaka/net/dns/exceptions/DNSTimeoutException.md) | DNS Timeout Exception|




### \venndev\vosaka\net\dns\model

#### Classes

| Class | Description |
|-------|-------------|
| [`AddressRecord`](./classes/venndev/vosaka/net/dns/model/AddressRecord.md) | |
| [`MxRecord`](./classes/venndev/vosaka/net/dns/model/MxRecord.md) | |
| [`NameRecord`](./classes/venndev/vosaka/net/dns/model/NameRecord.md) | |
| [`RawRecord`](./classes/venndev/vosaka/net/dns/model/RawRecord.md) | |
| [`Record`](./classes/venndev/vosaka/net/dns/model/Record.md) | |
| [`SoaRecord`](./classes/venndev/vosaka/net/dns/model/SoaRecord.md) | |
| [`SrvRecord`](./classes/venndev/vosaka/net/dns/model/SrvRecord.md) | |
| [`TxtRecord`](./classes/venndev/vosaka/net/dns/model/TxtRecord.md) | |




### \venndev\vosaka\net\tcp

#### Classes

| Class | Description |
|-------|-------------|
| [`TCP`](./classes/venndev/vosaka/net/tcp/TCP.md) | TCP class for creating asynchronous TCP connections.|
| [`TCPListener`](./classes/venndev/vosaka/net/tcp/TCPListener.md) | |
| [`TCPReadHalf`](./classes/venndev/vosaka/net/tcp/TCPReadHalf.md) | TCPReadHalf represents the read-only half of a split TCP stream.|
| [`TCPSock`](./classes/venndev/vosaka/net/tcp/TCPSock.md) | |
| [`TCPStream`](./classes/venndev/vosaka/net/tcp/TCPStream.md) | TCPStream provides asynchronous TCP stream operations.|
| [`TCPWriteHalf`](./classes/venndev/vosaka/net/tcp/TCPWriteHalf.md) | TCPWriteHalf represents the write-only half of a split TCP stream.|




### \venndev\vosaka\net\udp

#### Classes

| Class | Description |
|-------|-------------|
| [`UDPSock`](./classes/venndev/vosaka/net/udp/UDPSock.md) | UDPSock provides asynchronous UDP socket operations.|




### \venndev\vosaka\net\unix

#### Classes

| Class | Description |
|-------|-------------|
| [`Unix`](./classes/venndev/vosaka/net/unix/Unix.md) | Unix class for creating asynchronous Unix domain socket connections.|
| [`UnixDatagram`](./classes/venndev/vosaka/net/unix/UnixDatagram.md) | Unix datagram socket for connectionless communication.|
| [`UnixListener`](./classes/venndev/vosaka/net/unix/UnixListener.md) | |
| [`UnixReadHalf`](./classes/venndev/vosaka/net/unix/UnixReadHalf.md) | Read half of a Unix domain socket stream.|
| [`UnixStream`](./classes/venndev/vosaka/net/unix/UnixStream.md) | |
| [`UnixWriteHalf`](./classes/venndev/vosaka/net/unix/UnixWriteHalf.md) | Write half of a Unix domain socket stream.|




### \venndev\vosaka\process

#### Classes

| Class | Description |
|-------|-------------|
| [`Command`](./classes/venndev/vosaka/process/Command.md) | Command class for executing external processes asynchronously.|
| [`ProcOC`](./classes/venndev/vosaka/process/ProcOC.md) | |
| [`Process`](./classes/venndev/vosaka/process/Process.md) | |
| [`Stdio`](./classes/venndev/vosaka/process/Stdio.md) | |




### \venndev\vosaka\runtime\eventloop

#### Classes

| Class | Description |
|-------|-------------|
| [`EventLoop`](./classes/venndev/vosaka/runtime/eventloop/EventLoop.md) | This class focuses on task execution and core event loop operations.|
| [`StreamHandler`](./classes/venndev/vosaka/runtime/eventloop/StreamHandler.md) | This class is responsible for managing read/write streams and signal handling.|




### \venndev\vosaka\runtime\eventloop\task

#### Classes

| Class | Description |
|-------|-------------|
| [`Task`](./classes/venndev/vosaka/runtime/eventloop/task/Task.md) | |
| [`TaskPool`](./classes/venndev/vosaka/runtime/eventloop/task/TaskPool.md) | |




### \venndev\vosaka\runtime\metrics

#### Classes

| Class | Description |
|-------|-------------|
| [`MRuntime`](./classes/venndev/vosaka/runtime/metrics/MRuntime.md) | |
| [`MTaskPool`](./classes/venndev/vosaka/runtime/metrics/MTaskPool.md) | |




### \venndev\vosaka\sync

#### Classes

| Class | Description |
|-------|-------------|
| [`CancelToken`](./classes/venndev/vosaka/sync/CancelToken.md) | CancelToken class for managing cancellation of asynchronous operations.|
| [`Channel`](./classes/venndev/vosaka/sync/Channel.md) | |
| [`Semaphore`](./classes/venndev/vosaka/sync/Semaphore.md) | Semaphore class for controlling access to shared resources in async contexts.|




### \venndev\vosaka\sync\mpsc

#### Classes

| Class | Description |
|-------|-------------|
| [`Expect`](./classes/venndev/vosaka/sync/mpsc/Expect.md) | |




### \venndev\vosaka\time

#### Classes

| Class | Description |
|-------|-------------|
| [`Interval`](./classes/venndev/vosaka/time/Interval.md) | Interval class for handling recurring asynchronous intervals in the event loop.|
| [`Repeat`](./classes/venndev/vosaka/time/Repeat.md) | Repeat class for executing recurring asynchronous operations.|
| [`Sleep`](./classes/venndev/vosaka/time/Sleep.md) | Sleep class for handling asynchronous sleep operations in the event loop.|




### \venndev\vosaka\utils

#### Classes

| Class | Description |
|-------|-------------|
| [`CallableUtil`](./classes/venndev/vosaka/utils/CallableUtil.md) | CallableUtil class for utility functions related to callable and generator handling.|
| [`Defer`](./classes/venndev/vosaka/utils/Defer.md) | Defer class for handling deferred execution of callbacks in the event loop.|
| [`GeneratorUtil`](./classes/venndev/vosaka/utils/GeneratorUtil.md) | GeneratorUtil class for utility functions related to generator handling.|
| [`MemUtil`](./classes/venndev/vosaka/utils/MemUtil.md) | MemUtil class for memory-related utility functions and conversions.|




### \venndev\vosaka\utils\string



#### Traits

| Trait | Description |
|-------|-------------|
| [`StrCmd`](./classes/venndev/vosaka/utils/string/StrCmd.md) | |




### \venndev\vosaka\utils\sync

#### Classes

| Class | Description |
|-------|-------------|
| [`CancelFuture`](./classes/venndev/vosaka/utils/sync/CancelFuture.md) | |




***
> Automatically generated on 2025-07-01
