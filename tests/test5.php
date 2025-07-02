<?php

require "../vendor/autoload.php";

use venndev\vosaka\VOsaka;
use venndev\vosaka\utils\Defer;

function streamDownload(
    string $url,
    string $output,
    int $chunkSize = 8192
): Generator {
    $context = stream_context_create([
        "http" => [
            "method" => "GET",
            "header" => "User-Agent: VOsakaDownloader\r\n",
        ],
    ]);

    $read = fopen($url, "rb", false, $context);
    if (!$read) {
        throw new RuntimeException("Cannot open URL: $url");
    }

    $write = fopen($output, "wb");
    if (!$write) {
        fclose($read);
        throw new RuntimeException("Cannot open output file: $output");
    }

    yield Defer::new(function () use ($read, $write) {
        fclose($read);
        fclose($write);
    });

    while (!feof($read)) {
        $chunk = fread($read, $chunkSize);
        if ($chunk === false) {
            throw new RuntimeException("Failed reading chunk");
        }
        fwrite($write, $chunk);
        yield; // simulate async step
    }
}

VOsaka::spawn(function () {
    $start = microtime(true);

    yield from streamDownload(
        "https://vscode.download.prss.microsoft.com/dbazure/download/stable/dfaf44141ea9deb3b4096f7cd6d24e00c147a4b1/VSCodeUserSetup-x64-1.101.0.exe",
        "downloaded_vosaka.exe"
    );

    $end = microtime(true);
    echo "VOsaka Time: " . number_format($end - $start, 6) . " seconds\n";
});
VOsaka::run();
