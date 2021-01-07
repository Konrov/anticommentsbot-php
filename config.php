<?php

class Config {
    private $BotToken; // $BotToken = "12345:abcde";
    private $ChatId; // $ChatId = "12345678";
    private $HookSecret; // $HookSecret = "hqTIWGKi8cCiyuSt2pWj";
    public $GreetingText = "Привет, %s!\nКомментарии доступны только участникам чата %s :)";
    public $ButtonText = "Присоединиться";
    public $NotSupergroup = "Данный чат не является супергруппой. Пожалуйста, исправьте это или исключите меня.";

    public function getBotToken(){
        return $this->BotToken;
    }

    public function getChatId(){
        return $this->ChatId;
    }
    
    public function getHookSecret(){
        return $this->HookSecret;
    }
}

?>
