<?php

class Config {
    private $BotToken; // $BotToken = "12345:abcde";
    private $ChatId; // $ChatId = "12345678";
    private $HookSecret; // $HookSecret = "hqTIWGKi8cCiyuSt2pWj";

    public function getBotToken(){
        return $this->BotToken;
    }

    public function setBotToken($token){
        $this->BotToken = $token;
    }

    public function getChatId(){
        return $this->ChatId;
    }

    public function setChatId($chat_id){
        $this->ChatId = $chat_id;
    }
    
    public function getHookSecret(){
        return $this->HookSecret;
    }
}

?>
