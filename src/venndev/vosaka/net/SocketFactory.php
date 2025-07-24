<?php

declare(strict_types=1);

namespace venndev\vosaka\net;

/**
 * Socket factory for creating sockets with options
 */
class SocketFactory
{
    /**
     * Create stream context with options
     */
    public static function createContext(array $options = []): mixed
    {
        $context = stream_context_create();

        // Socket options
        if ($options['reuseaddr'] ?? true) {
            stream_context_set_option($context, 'socket', 'so_reuseaddr', 1);
        }

        if ($options['reuseport'] ?? false) {
            stream_context_set_option($context, 'socket', 'so_reuseport', 1);
        }

        if ($options['nodelay'] ?? false) {
            stream_context_set_option($context, 'socket', 'tcp_nodelay', 1);
        }

        if ($options['keepalive'] ?? false) {
            stream_context_set_option($context, 'socket', 'so_keepalive', 1);
        }

        // Buffer sizes
        if (isset($options['sndbuf']) && $options['sndbuf'] > 0) {
            stream_context_set_option($context, 'socket', 'so_sndbuf', $options['sndbuf']);
        }

        if (isset($options['rcvbuf']) && $options['rcvbuf'] > 0) {
            stream_context_set_option($context, 'socket', 'so_rcvbuf', $options['rcvbuf']);
        }

        // SSL/TLS options
        if ($options['ssl'] ?? false) {
            stream_context_set_option($context, 'ssl', 'verify_peer', $options['verify_peer'] ?? false);
            stream_context_set_option($context, 'ssl', 'verify_peer_name', $options['verify_peer_name'] ?? false);
            stream_context_set_option($context, 'ssl', 'allow_self_signed', $options['allow_self_signed'] ?? true);

            if (isset($options['ssl_cert'])) {
                stream_context_set_option($context, 'ssl', 'local_cert', $options['ssl_cert']);
            }

            if (isset($options['ssl_key'])) {
                stream_context_set_option($context, 'ssl', 'local_pk', $options['ssl_key']);
            }

            if (isset($options['ssl_ca'])) {
                stream_context_set_option($context, 'ssl', 'cafile', $options['ssl_ca']);
            }
        }

        return $context;
    }

    /**
     * Apply socket options after creation
     */
    public static function applyOptions($socket, array $options): void
    {
        if (!is_resource($socket) || !function_exists('socket_import_stream')) {
            return;
        }

        $sock = @socket_import_stream($socket);
        if ($sock === false) {
            return;
        }

        // Apply low-level socket options
        if ($options['nodelay'] ?? false) {
            @socket_set_option($sock, SOL_TCP, TCP_NODELAY, 1);
        }

        if ($options['keepalive'] ?? false) {
            @socket_set_option($sock, SOL_SOCKET, SO_KEEPALIVE, 1);
        }

        if (isset($options['linger'])) {
            $linger = [
                'l_onoff' => $options['linger'] !== false ? 1 : 0,
                'l_linger' => is_int($options['linger']) ? $options['linger'] : 0
            ];
            @socket_set_option($sock, SOL_SOCKET, SO_LINGER, $linger);
        }
    }
}
