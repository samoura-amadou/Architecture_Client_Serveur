<?php

/* Lancement de test de serveur: */ 


echo "Bienvenu sur le serveur de l'attelier 1:"; 

function request($verb, $url, $content)
{

    /* Initialisation de client*/

    $client = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
    if (socket_connect($client, "127.0.0.1", 5000) === false) {
        fprintf(STDERR, socket_strerror(socket_last_error($client)));
        return false;
    }
    socket_write($client, "$verb /" . urlencode($url) . " HTTP/1.1\r\n");
    socket_write($client, "Content-Type: text/plain\r\n");
    socket_write($client, "Content-Length: " . strlen($content) . "\r\n\r\n");
    socket_write($client, "$content\r\n");
    for (;;) {
        $s = @socket_read($client, 4096, PHP_NORMAL_READ);
        if ($s === false) {
            break;
        }
        echo $s;
    }
    socket_close($client);
    return true;
}

for (;;) {
    $content = readline("Key/Text (Q to quit) :");
    if ($content === false || in_array($content, ['Q', 'q'])) {
        break;
    }
    $items = explode('/', $content);
    if (count($items) > 1) {
        request("POST", $items[0], $items[1]);
        request("GET", "", "");
    }
}
