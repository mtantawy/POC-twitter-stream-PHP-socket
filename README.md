# POC-twitter-stream-PHP-socket

Just a proof of concept of using PHP to open a socket to Twitter's Filtered Stream and keeping a script long-running to receive the stream.

I used basic ReactPHP libraries to abstract away code related to the event loop and focus on the socket connection and handling the actual stream.