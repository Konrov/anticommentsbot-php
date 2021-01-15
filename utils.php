<?php
/**
 * Вспомогательные функции
 * и
 * инициализация конфига
 */

require_once 'config.php';

$config = new Config();

define("API", "https://api.telegram.org/bot" . $config->getBotToken());
define("ADMIN", $config->AdminId);

class Utils {

    public function sendMessage($chat, $text) : ?stdClass {
        $text = urlencode($text);
        return json_decode(curl(API."/sendmessage?chat_id={$chat}&text={$text}"));
    }
    
    public function reply($chat, $text, $message_id) : ?stdClass {
        $text = urlencode($text);
        return json_decode(curl(API."/sendmessage?chat_id={$chat}&reply_to_message_id={$message_id}&text={$text}"));
    }
    
    public function deleteMessage($chat, $message_id) : ?stdClass {
        return json_decode(curl(API."/deletemessage?chat_id={$chat}&message_id={$message_id}"));
    }
    
    public function sendButtons($chat, $text, $keyboard, $message_id) : ?stdClass {
        $keyboard = urlencode($keyboard);
        $text = urlencode($text);
        return json_decode(curl(API."/sendMessage?chat_id={$chat}&reply_markup={$keyboard}&reply_to_message_id={$message_id}&text={$text}"));
    }
    
    public function leaveChat($chat) : void {
        curl(API."/leavechat?chat_id={$chat}");
    }

    public function isReply($upd) : bool {
        return isset($upd->message->reply_to_message);
    }

    public function isMember($chat, $user) : bool {
        if (in_array($user, ['777000', '1087968824'], true)) {
            return true; // 777000 - TG Official account id, 1087968824 - Anon Admin
        }
        $response = json_decode(curl(API."/getchatmember?chat_id={$chat}&user_id={$user}"));
        $userStatus = $response->result->status;
        return $userStatus == "left" ? false : true;
    }

    public function configInfo() : void {
        $getMe = json_decode(curl(API."/getMe"));
        $getWebhook = json_decode(curl(API."/getWebhookInfo"));
        if ($getMe->ok && $getWebhook->ok) {
            $info = "Текущий конфиг бота: ";
            $info .= "\nID: " . $getMe->result->id;
            $info .= "\nИмя: " . $getMe->result->first_name;
            $info .= "\nID Admin: " . ADMIN;
            $info .= "\nДобавление в группы: " . ($getMe->result->can_join_groups ? "Разрешено ⚠️" : "Запрещено ✅");
            $info .= "\nДоступ к сообщениям: " . ($getMe->result->can_read_all_group_messages ? "Есть ✅" : "Нет ⚠️");
            $info .= "\nМакс. кол-во одновременных соединений: " . $getWebhook->result->max_connections;
            $info .= "\nПоследняя ошибка вебхука (дата): " . (isset($getWebhook->result->last_error_date) ? date("d/m/Y H:i:s", $getWebhook->result->last_error_date) : "Нет" );
            $info .= "\nСообщение последней ошибки: " . (isset($getWebhook->result->last_error_message) ? $getWebhook->result->last_error_message : "Нет" );
        }
        $this->sendMessage(ADMIN, $info);
        return;
    }

    // Update last message id in json
    public function updateId($chat, $message_id) {
        $lastIdsFile = getcwd() . '/last_ids.json';
        if (!file_exists($lastIdsFile)) {
            fopen('last_ids.json', 'w');
            $putData = [ "last" => "{$message_id}" ];
            file_put_contents('last_ids.json', json_encode($putData, true));
        } else {
            $lastIds = json_decode(file_get_contents($lastIdsFile));
            $this->deleteMessage($chat, $lastIds->last);
            $lastIds->last = $message_id;
            $newLast = json_encode($lastIds, true);
            file_put_contents($lastIdsFile, $newLast);
        }
        return;
    }

}
