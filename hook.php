<?php
////
// Group AntiComments Bot v0.1
// Authors: @azeronde, @duvewo
////

require_once "config.php";

$config = new Config();

// Validate hook secret string
if (!isset($_GET['hook_secret']) || $_GET['hook_secret'] != $config->getHookSecret()) {
    http_response_code(403);
    die('Forbidden');
}

// Main curl func
function curl($url) {
    $ch = curl_init();
    $opt = array(CURLOPT_URL => $url, CURLOPT_RETURNTRANSFER => true);
    curl_setopt_array($ch, $opt);
    $response = curl_exec($ch);
    curl_close($ch);
    return $response;
}

// API Methods

function sendMessage($chat, $text) {
    global $API_URL;
    return curl("{$API_URL}/sendmessage?chat_id={$chat}&text=" . urlencode($text));
}

function reply($chat, $text, $message_id) {
    global $API_URL;
    return curl("{$API_URL}/sendmessage?chat_id={$chat}&reply_to_message_id={$message_id}&text=" . urlencode($text));
}

function deleteMessage($chat, $message_id) {
    global $API_URL;
    return curl("{$API_URL}/deletemessage?chat_id={$chat}&message_id={$message_id}");
}

function sendButtons($chat, $text, $keyboard, $message_id) {
    global $API_URL;
    $keyboard = urlencode($keyboard);
    return curl("{$API_URL}/sendMessage?chat_id={$chat}&reply_markup={$keyboard}&reply_to_message_id={$message_id}&text=" . urlencode($text));
}

function isMember($chat, $user) : bool {
    global $API_URL;
    if ($user == "777000" || $user == "1087968824") {
        return true; // 777000 - TG Official account id, 1087968824 - Anon Admin
    }
    $response = json_decode(curl("{$API_URL}/getchatmember?chat_id={$chat}&user_id={$user}"));
    $userStatus = $response->result->status;
    return $userStatus == "left" ? false : true;
}

// Update last message id in json
function updateId($chat, $message_id) {
    $last_ids_file = getcwd() . '/last_ids.json';
    if (!file_exists($last_ids_file)) {
        fopen('last_ids.json', 'w');
        $putData = [ "last" => "{$message_id}" ];
        file_put_contents('last_ids.json', json_encode($putData, true));
    } else {
        $last_ids = json_decode(file_get_contents($last_ids_file));
        deleteMessage($chat, $last_ids->last);
        $last_ids->last = $message_id;
        $new_last = json_encode($last_ids, true);
        file_put_contents($last_ids_file, $new_last);
    }
    return;
}

// Vars
$TOKEN = $config->getBotToken();
$API_URL = "https://api.telegram.org/bot{$TOKEN}";

$update = json_decode(file_get_contents("php://input"));

$ALLOWED_CHAT = $config->getChatId();

$chatId =         $update->message->chat->id;
$chatUsername =   $update->message->chat->username;
$chatName =       $update->message->chat->title;
$chatType =       $update->message->chat->type;

$messageText =    $update->message->text;
$messageId =      $update->message->message_id;

$senderId =       $update->message->from->id;
$senderFname =    $update->message->from->first_name;
$senderUsername = $update->message->from->username;

// Bot should work only in the allowed chat
if ($chatId != $ALLOWED_CHAT) {
    die();
}

// If not supergroup (do not have @username)
if ($chatType != "supergroup") {
    sendMessage($chatId, "This chat is not a supergroup. Please make the chat a supergroup or kick me.");
    die();
}

## Keyboard
$chatLink = "https://t.me/{$chatUsername}";
$inlineButton = array("text" => "Присоединиться", "url" => $chatLink);
$inlineKeyboard = [[$inlineButton]];
$keyboard = json_encode(array("inline_keyboard" => $inlineKeyboard));

////
// Main
////

if (!isMember($chatId, $senderId)) {
    $botResponse = json_decode(sendButtons($chatId, "Привет {$senderFname} :)\nКомментарии доступны только участникам чата {$chatName}!", $keyboard, $messageId));
    deleteMessage($chatId, $messageId);
    $botMessageId = $botResponse->result->message_id;
    updateId($chatId, $botMessageId);
    die();
}

// Ping pong
if (strpos($messageText, "/ping") === 0) {
    sendMessage($chatId, "Pong!");
    exit;
}

?>
