<?php

define("PORT", 0x2BAD);

$players = array();
$is_head = false;

function all_players_ready() {
    global $players;

    foreach($players as $player) {
        if($player && !$player->is_ready()) {
            return false;
        }
    }
    return true;
}

function find_player_id() {
    global $players;

    for($i=0; $i < count(players); $i++) {
        if(!players[$i])
            return i+1;
    }        
    $players[] = NULL;
    return count(players);
}

class Player extends Thread {
    function __construct($num, $sock) {
        $this->_id = num;
        $this->_score = 0;
        $this->_choice = NULL;
        $this->_sock = sock;
    }
    function is_ready() {
        return $this->_choice !== NULL;
    }
    function get_score() {
        return $this->_score;
    }
    
    public function run() {
        global $is_head;

        socket_write($this->_sock, pack('V', self._id));
        for(;;) {
            $data = socket_read($this->_sock, 2);
            if($data===false) {
                $players[$this->_id-1] = NULL;
                echo "- Player $this->_id left";
                return;
            }
            $this->_choice = unpack("v", data);
            $this->synchronized(function($thread){
                if(all_players_ready()) {
                    $is_head = rand(2) == 0;
                    $thread->notify();
                    echo "All ".count($players)." players played, got ".( $is_head ? 'HEAD':'TAIL');
                }
                else {
                    $thread->wait();
                }
            }, $this);
            if($this->_choice === $is_head) {
                $this->_score += 1;
            }
            $this->_choice = NULL;
            socket_write($this->_sock, pack('v', $is_head));
            socket_write($this->_sock, pack('V', count($players)));
            foreach($players as $player) {
                socket_write($this->_sock, pack('V', $player ? $player->get_score() : -1));
            }
        }
    }    
}

$sock_listen = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
socket_bind($sock_listen, '127.0.0.1', PORT);
socket_listen($sock_listen);
echo("Listening on port ".PORT);
for(;;) {
    $sock_service = socket_accept($socket);
    if($sock_service !== false) {
        $index = find_player_id();
        echo "- Player $index arrived";
        $players[index-1] = $player = Player(index, sock_service);
        $player->start();
    }    
}
