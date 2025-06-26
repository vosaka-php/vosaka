
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
| [`Folder`](./classes/venndev/vosaka/fs/Folder.md) | |




### \venndev\vosaka\io

#### Classes

| Class | Description |
|-------|-------------|
| [`JoinHandle`](./classes/venndev/vosaka/io/JoinHandle.md) | JoinHandle class for tracking and waiting on asynchronous task completion.|




### \venndev\vosaka\net\tcp

#### Classes

| Class | Description |
|-------|-------------|
| [`TCP`](./classes/venndev/vosaka/net/tcp/TCP.md) | TCP class for creating asynchronous TCP connections.|
| [`TCPListener`](./classes/venndev/vosaka/net/tcp/TCPListener.md) | |
| [`TCPReadHalf`](./classes/venndev/vosaka/net/tcp/TCPReadHalf.md) | |
| [`TCPSock`](./classes/venndev/vosaka/net/tcp/TCPSock.md) | |
| [`TCPStream`](./classes/venndev/vosaka/net/tcp/TCPStream.md) | |
| [`TCPWriteHalf`](./classes/venndev/vosaka/net/tcp/TCPWriteHalf.md) | |
| [`UDPSock`](./classes/venndev/vosaka/net/tcp/UDPSock.md) | |




### \venndev\vosaka\net\unix

#### Classes

| Class | Description |
|-------|-------------|
| [`UnixDatagram`](./classes/venndev/vosaka/net/unix/UnixDatagram.md) | |
| [`UnixListener`](./classes/venndev/vosaka/net/unix/UnixListener.md) | |
| [`UnixSock`](./classes/venndev/vosaka/net/unix/UnixSock.md) | |
| [`UnixStream`](./classes/venndev/vosaka/net/unix/UnixStream.md) | |




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
| [`EventLoop`](./classes/venndev/vosaka/runtime/eventloop/EventLoop.md) | EventLoop class manages the asynchronous task execution runtime.|




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
> Automatically generated on 2025-06-26
