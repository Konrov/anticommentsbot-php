<?php
////
// Group AntiComments Bot v0.2.6
////

require_once "utils.php";

$utils = new Utils();

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

// Vars

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
if ($chatId != $ALLOWED_CHAT && $chatId != $config->AdminId) {
    if ($chatType != "private") {
        $utils->leaveChat($chatId);
        die();
    }
    die();
}

// If not supergroup (do not have @username)
if ($chatType != "supergroup" && $chatType != "private") {
    $utils->sendMessage($chatId, $config->NotSupergroup);
    die();
}

## Keyboard
$chatLink = "https://t.me/{$chatUsername}";
$inlineButton = array("text" => $config->ButtonText, "url" => $chatLink);
$inlineKeyboard = [[$inlineButton]];
$keyboard = json_encode(array("inline_keyboard" => $inlineKeyboard));

if ($config->OnlyReplies && $utils->isReply($update) != true) {
    if ($senderId != $config->AdminId) {
        die();
    }
}

if ($utils->isMember($chatId, $senderId) != true) {
    $greeting = sprintf($config->GreetingText, $senderFirstName, $chatTitle);
    $botResponse = $utils->sendButtons($chatId, $greeting, $keyboard, $messageId);
    $utils->deleteMessage($chatId, $messageId);
    $botMessageId = $botResponse->result->message_id;
    $utils->updateId($chatId, $botMessageId);
    die();
}

if (strpos($messageText, "/ping") === 0) {
    $utils->sendMessage($chatId, "Pong!");
    die();
} elseif (strpos($messageText, "/config") === 0 && $senderId == $config->AdminId) {
    $utils->configInfo();
    die();
}
