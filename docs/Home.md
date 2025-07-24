
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
| [`CBreaker`](./classes/venndev/vosaka/breaker/CBreaker.md) | Circuit Breaker implementation to prevent cascading failures in distributed systems.|




### \venndev\vosaka\cleanup

#### Classes

| Class | Description |
|-------|-------------|
| [`GracefulShutdown`](./classes/venndev/vosaka/cleanup/GracefulShutdown.md) | Main graceful shutdown orchestrator|




### \venndev\vosaka\cleanup\handler

#### Classes

| Class | Description |
|-------|-------------|
| [`CallbackHandler`](./classes/venndev/vosaka/cleanup/handler/CallbackHandler.md) | Handles cleanup callbacks|
| [`ChildProcessHandler`](./classes/venndev/vosaka/cleanup/handler/ChildProcessHandler.md) | Handles child process PID cleanup|
| [`PipeCleanupHandler`](./classes/venndev/vosaka/cleanup/handler/PipeCleanupHandler.md) | Handles pipe resource cleanup|
| [`ProcessCleanupHandler`](./classes/venndev/vosaka/cleanup/handler/ProcessCleanupHandler.md) | Handles process resource cleanup|
| [`SocketCleanupHandler`](./classes/venndev/vosaka/cleanup/handler/SocketCleanupHandler.md) | Handles socket resource cleanup|
| [`StateManager`](./classes/venndev/vosaka/cleanup/handler/StateManager.md) | Handles state persistence|
| [`TempFileHandler`](./classes/venndev/vosaka/cleanup/handler/TempFileHandler.md) | Handles temporary file cleanup|




### \venndev\vosaka\cleanup\interfaces




#### Interfaces

| Interface | Description |
|-----------|-------------|
| [`CleanupHandlerInterface`](./classes/venndev/vosaka/cleanup/interfaces/CleanupHandlerInterface.md) | Interface for cleanup handlers|



### \venndev\vosaka\cleanup\logger

#### Classes

| Class | Description |
|-------|-------------|
| [`FileLogger`](./classes/venndev/vosaka/cleanup/logger/FileLogger.md) | Simple file logger implementation|



#### Interfaces

| Interface | Description |
|-----------|-------------|
| [`LoggerInterface`](./classes/venndev/vosaka/cleanup/logger/LoggerInterface.md) | Logger interface|



### \venndev\vosaka\core

#### Classes

| Class | Description |
|-------|-------------|
| [`Constants`](./classes/venndev/vosaka/core/Constants.md) | |
| [`Defer`](./classes/venndev/vosaka/core/Defer.md) | Defer class for handling deferred execution of callbacks in the event loop.|
| [`Err`](./classes/venndev/vosaka/core/Err.md) | |
| [`Future`](./classes/venndev/vosaka/core/Future.md) | Future class for creating Result and Option instances|
| [`None`](./classes/venndev/vosaka/core/None.md) | Option type similar to|
| [`Ok`](./classes/venndev/vosaka/core/Ok.md) | |
| [`Result`](./classes/venndev/vosaka/core/Result.md) | Result class for handling asynchronous task results and transformations.|
| [`Some`](./classes/venndev/vosaka/core/Some.md) | Option type similar to|




### \venndev\vosaka\core\interfaces

#### Classes

| Class | Description |
|-------|-------------|
| [`Option`](./classes/venndev/vosaka/core/interfaces/Option.md) | Option type similar to|
| [`ResultType`](./classes/venndev/vosaka/core/interfaces/ResultType.md) | |



#### Interfaces

| Interface | Description |
|-----------|-------------|
| [`Init`](./classes/venndev/vosaka/core/interfaces/Init.md) | |
| [`Time`](./classes/venndev/vosaka/core/interfaces/Time.md) | |



### \venndev\vosaka\eventloop

#### Classes

| Class | Description |
|-------|-------------|
| [`EventLoop`](./classes/venndev/vosaka/eventloop/EventLoop.md) | |
| [`StreamHandler`](./classes/venndev/vosaka/eventloop/StreamHandler.md) | This class is responsible for managing read/write streams and signal handling.|




### \venndev\vosaka\eventloop\task

#### Classes

| Class | Description |
|-------|-------------|
| [`Task`](./classes/venndev/vosaka/eventloop/task/Task.md) | |
| [`TaskManager`](./classes/venndev/vosaka/eventloop/task/TaskManager.md) | Optimized TaskManager with batch processing and performance improvements|
| [`TaskPool`](./classes/venndev/vosaka/eventloop/task/TaskPool.md) | |




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




### \venndev\vosaka\metrics

#### Classes

| Class | Description |
|-------|-------------|
| [`MRuntime`](./classes/venndev/vosaka/metrics/MRuntime.md) | |
| [`MTaskPool`](./classes/venndev/vosaka/metrics/MTaskPool.md) | |




### \venndev\vosaka\net

#### Classes

| Class | Description |
|-------|-------------|
| [`AbstractConnection`](./classes/venndev/vosaka/net/AbstractConnection.md) | Base implementation for connections|
| [`EventLoopIntegration`](./classes/venndev/vosaka/net/EventLoopIntegration.md) | Event loop integration for sockets|
| [`SocketFactory`](./classes/venndev/vosaka/net/SocketFactory.md) | Socket factory for creating sockets with options|
| [`StreamBuffer`](./classes/venndev/vosaka/net/StreamBuffer.md) | Stream buffer for managing read/write data|




### \venndev\vosaka\net\contracts




#### Interfaces

| Interface | Description |
|-----------|-------------|
| [`AddressInterface`](./classes/venndev/vosaka/net/contracts/AddressInterface.md) | Base interface for network addresses|
| [`ConnectionInterface`](./classes/venndev/vosaka/net/contracts/ConnectionInterface.md) | Base interface for all network connections|
| [`DatagramInterface`](./classes/venndev/vosaka/net/contracts/DatagramInterface.md) | Interface for datagram (UDP) sockets|
| [`ServerInterface`](./classes/venndev/vosaka/net/contracts/ServerInterface.md) | Interface for server/listener sockets|
| [`SocketInterface`](./classes/venndev/vosaka/net/contracts/SocketInterface.md) | Low-level socket interface|
| [`StreamInterface`](./classes/venndev/vosaka/net/contracts/StreamInterface.md) | Extended interface for stream-based connections|



### \venndev\vosaka\net\dns

#### Classes

| Class | Description |
|-------|-------------|
| [`DNSQuery`](./classes/venndev/vosaka/net/dns/DNSQuery.md) | DNS Query builder|
| [`DNSRecord`](./classes/venndev/vosaka/net/dns/DNSRecord.md) | DNS Record representation|
| [`DNSResolver`](./classes/venndev/vosaka/net/dns/DNSResolver.md) | DNS Resolver|
| [`DNSResponse`](./classes/venndev/vosaka/net/dns/DNSResponse.md) | DNS Response parser|




### \venndev\vosaka\net\exceptions

#### Classes

| Class | Description |
|-------|-------------|
| [`BindException`](./classes/venndev/vosaka/net/exceptions/BindException.md) | Exception thrown when bind operations fail|
| [`BufferOverflowException`](./classes/venndev/vosaka/net/exceptions/BufferOverflowException.md) | Exception thrown when buffer overflow occurs|
| [`ConnectionException`](./classes/venndev/vosaka/net/exceptions/ConnectionException.md) | Exception thrown when connection operations fail|
| [`NetworkException`](./classes/venndev/vosaka/net/exceptions/NetworkException.md) | |
| [`ProtocolException`](./classes/venndev/vosaka/net/exceptions/ProtocolException.md) | Exception thrown for protocol-specific errors|
| [`TimeoutException`](./classes/venndev/vosaka/net/exceptions/TimeoutException.md) | Exception thrown when a timeout occurs|




### \venndev\vosaka\net\options

#### Classes

| Class | Description |
|-------|-------------|
| [`OptionsBuilder`](./classes/venndev/vosaka/net/options/OptionsBuilder.md) | Fluent options builder|
| [`ServerOptions`](./classes/venndev/vosaka/net/options/ServerOptions.md) | Server-specific options|
| [`SocketOptions`](./classes/venndev/vosaka/net/options/SocketOptions.md) | Socket options configuration|




### \venndev\vosaka\net\tcp

#### Classes

| Class | Description |
|-------|-------------|
| [`TCP`](./classes/venndev/vosaka/net/tcp/TCP.md) | TCP client/server factory|
| [`TCPAddress`](./classes/venndev/vosaka/net/tcp/TCPAddress.md) | |
| [`TCPConnection`](./classes/venndev/vosaka/net/tcp/TCPConnection.md) | TCP Connection implementation|
| [`TCPServer`](./classes/venndev/vosaka/net/tcp/TCPServer.md) | TCP Server implementation|




### \venndev\vosaka\net\udp

#### Classes

| Class | Description |
|-------|-------------|
| [`UDP`](./classes/venndev/vosaka/net/udp/UDP.md) | UDP factory|
| [`UDPSocket`](./classes/venndev/vosaka/net/udp/UDPSocket.md) | UDP Socket implementation|




### \venndev\vosaka\net\unix

#### Classes

| Class | Description |
|-------|-------------|
| [`UnixAddress`](./classes/venndev/vosaka/net/unix/UnixAddress.md) | Unix Socket Address implementation|
| [`UnixConnection`](./classes/venndev/vosaka/net/unix/UnixConnection.md) | Unix Socket Connection implementation|
| [`UnixServer`](./classes/venndev/vosaka/net/unix/UnixServer.md) | Unix Socket Server implementation|
| [`UnixSocket`](./classes/venndev/vosaka/net/unix/UnixSocket.md) | Unix socket factory|




### \venndev\vosaka\process

#### Classes

| Class | Description |
|-------|-------------|
| [`Command`](./classes/venndev/vosaka/process/Command.md) | Command class for executing external processes asynchronously.|
| [`ProcOC`](./classes/venndev/vosaka/process/ProcOC.md) | |
| [`Process`](./classes/venndev/vosaka/process/Process.md) | |
| [`Stdio`](./classes/venndev/vosaka/process/Stdio.md) | |




### \venndev\vosaka\sync

#### Classes

| Class | Description |
|-------|-------------|
| [`CancelToken`](./classes/venndev/vosaka/sync/CancelToken.md) | CancelToken class for managing cancellation of asynchronous operations.|
| [`Channel`](./classes/venndev/vosaka/sync/Channel.md) | A simple MPSC (Multiple Producer Single Consumer) channel implementation.|
| [`LoopGate`](./classes/venndev/vosaka/sync/LoopGate.md) | LoopGate is a simple synchronization primitive that allows<br />a task to proceed only after a specified number of ticks.|
| [`Mutex`](./classes/venndev/vosaka/sync/Mutex.md) | Returns Result&lt;MutexGuard, Error&gt; for lock operations<br />Uses RAII-style MutexGuard for automatic cleanup<br />Provides try_lock() that returns Option&lt;MutexGuard&gt;<br />Uses unwrap() and expect() for error handling|
| [`MutexGuard`](./classes/venndev/vosaka/sync/MutexGuard.md) | MutexGuard - RAII-style lock guard|
| [`RwLock`](./classes/venndev/vosaka/sync/RwLock.md) | RwLock - Reader-Writer Lock implementation using Generator|
| [`Semaphore`](./classes/venndev/vosaka/sync/Semaphore.md) | Semaphore class for controlling access to shared resources in async contexts.|




### \venndev\vosaka\sync\mpsc

#### Classes

| Class | Description |
|-------|-------------|
| [`Expect`](./classes/venndev/vosaka/sync/mpsc/Expect.md) | A utility class to check if a given input matches a specified type or condition.|




### \venndev\vosaka\sync\rwlock

#### Classes

| Class | Description |
|-------|-------------|
| [`ReadLockGuard`](./classes/venndev/vosaka/sync/rwlock/ReadLockGuard.md) | Read Lock Guard - automatically releases read lock when destroyed|
| [`WriteLockGuard`](./classes/venndev/vosaka/sync/rwlock/WriteLockGuard.md) | Write Lock Guard - automatically releases write lock when destroyed|




### \venndev\vosaka\task

#### Classes

| Class | Description |
|-------|-------------|
| [`JoinSet`](./classes/venndev/vosaka/task/JoinSet.md) | JoinSet - A collection of spawned tasks that can be awaited together.|
| [`JoinSetTask`](./classes/venndev/vosaka/task/JoinSetTask.md) | Internal class to track individual tasks in a JoinSet|
| [`Loopify`](./classes/venndev/vosaka/task/Loopify.md) | |




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
| [`GeneratorUtil`](./classes/venndev/vosaka/utils/GeneratorUtil.md) | GeneratorUtil class for utility functions related to generator handling.|
| [`MemUtil`](./classes/venndev/vosaka/utils/MemUtil.md) | MemUtil class for memory-related utility functions and conversions.|
| [`PlatformDetector`](./classes/venndev/vosaka/utils/PlatformDetector.md) | |


#### Traits

| Trait | Description |
|-------|-------------|
| [`FutureUtil`](./classes/venndev/vosaka/utils/FutureUtil.md) | |




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
> Automatically generated on 2025-07-24
