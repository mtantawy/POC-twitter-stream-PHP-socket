<?php

use React\EventLoop\Factory;
use React\Stream\DuplexResourceStream;

require __DIR__ . '/vendor/autoload.php';

$host = 'api.twitter.com';
$path = '/labs/1/tweets/stream/filter?format=detailed&expansions=author_id';
// https://developer.twitter.com/en/docs/basics/authentication/overview/application-only
$token = '';

// fyi, this is by default blocking
$resource = stream_socket_client(
	'tls://' . $host . ':443',
	$errNo,
	$errStr
);

if (false === $resource) {
	echo "Did not get a resource, $errNo ($errStr)";
    exit(1);
}

$loop = Factory::create();
// We need DuplexResourceStream because we need to send Headers manually after conencting the socket
$stream = new DuplexResourceStream($resource, $loop);

$stream->on('data', function ($chunk) {
	// Ignore first line because it contains content length
	// Trim the rest as we receive newlines
	$preparedChunk = trim(mb_substr($chunk, mb_stripos($chunk, "\n")));
	try {
		$object = json_decode($preparedChunk, false, 10, JSON_THROW_ON_ERROR);
	} catch (JsonException $e) {
		// When there is nothing to stream, a keep-alive is sent which is usually empty lines
		// We can skip it earlier, or silent the JsonException here
		// echo $e->getMessage();
	}
});

$stream->on('close', function () {
    echo '[CLOSED]' . PHP_EOL;
});

$stream->on('error', function (Exception $e) {
	echo 'Error: ' . $e->getMessage() . PHP_EOL;
});

// We have to manually write to the socket the HTTP Headers
$stream->write("GET $path HTTP/1.1\r\nHost: $host\r\nAuthorization: Bearer $token\r\n\r\n");

$loop->run();
