<?php
////
// Group AntiComments Bot v0.2.1
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
    $text = urlencode($text);
    return curl("{$API_URL}/sendmessage?chat_id={$chat}&text={$text}");
}

function reply($chat, $text, $message_id) {
    global $API_URL;
    $text = urlencode($text);
    return curl("{$API_URL}/sendmessage?chat_id={$chat}&reply_to_message_id={$message_id}&text={$text}");
}

function deleteMessage($chat, $message_id) {
    global $API_URL;
    return curl("{$API_URL}/deletemessage?chat_id={$chat}&message_id={$message_id}");
}

function sendButtons($chat, $text, $keyboard, $message_id) {
    global $API_URL;
    $keyboard = urlencode($keyboard);
    $text = urlencode($text);
    return curl("{$API_URL}/sendMessage?chat_id={$chat}&reply_markup={$keyboard}&reply_to_message_id={$message_id}&text={$text}");
}

function leaveChat($chat) {
    global $API_URL;
    return curl("{$API_URL}/leavechat?chat_id={$chat}");
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

$chatId =             $update->message->chat->id;
$chatUsername =       $update->message->chat->username;
$chatTitle =          $update->message->chat->title;
$chatType =           $update->message->chat->type;

$messageText =        $update->message->text;
$messageId =          $update->message->message_id;

$senderId =           $update->message->from->id;
$senderFirstName =    $update->message->from->first_name;
$senderLastName =     $update->message->from->last_name;
$senderUsername =     $update->message->from->username;

// Bot should work only in the allowed chat
if ($chatId != $ALLOWED_CHAT) {
    if ($chatType != "private") {
        leaveChat($chatId);
        die();
    }
    die();
}

// If not supergroup (do not have @username)
if ($chatType != "supergroup") {
    sendMessage($chatId, $config->NotSupergroup);
    die();
}

## Keyboard
$chatLink = "https://t.me/{$chatUsername}";
$inlineButton = array("text" => $config->ButtonText, "url" => $chatLink);
$inlineKeyboard = [[$inlineButton]];
$keyboard = json_encode(array("inline_keyboard" => $inlineKeyboard));


if ($config->OnlyReplies == true && !isset($update->message->reply_to_message) && strpos($messageText, "/ping") !== 0) {
    die();
} 

if (!isMember($chatId, $senderId)) {
    $greeting = sprintf($config->GreetingText, $senderFirstName, $chatTitle);
    $botResponse = json_decode(sendButtons($chatId, $greeting, $keyboard, $messageId));
    deleteMessage($chatId, $messageId);
    $botMessageId = $botResponse->result->message_id;
    updateId($chatId, $botMessageId);
    die();
}

if (strpos($messageText, "/ping") === 0) {
    sendMessage($chatId, "Pong!");
    die();
}
