<?php
define('PORT', 0x2BAD);
define('SERVER', "127.0.0.1");

$sock = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
if (@socket_connect($sock, SERVER, PORT) === false) {
    fprintf(STDERR, socket_strerror(socket_last_error($sock))."\n");
    return false;
}
$num = unpack('V', socket_read($sock, 4));
echo "You're player $num";
for(;;) {
    $content = strtolower(readline("H:Head, T:Tail or Q:Quit :"));
    if($content == "q") {
        break;
    }        
    elseif(in_array($content, ['h', 't'])) {
        socket_write($sock, pack('v', $content == 'h'));
        $is_head = unpack('v', socket_read($sock, 2));
        $score_num = unpack('V', socket_read($sock, 4));
        echo "It was ".($is_head ? 'HEAD':'TAIL').", here are the scores :";
        for($i=1; $i<=score_num; $i++) {
            $score = unpack('V', socket_read($sock, 4));
            echo "- Player $i".($i == $num ? ' (you)':'')." : ".($score >= 0  ? $score:'-');
        }
    }  
}
socket_close($sock);
