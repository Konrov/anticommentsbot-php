# anticommentsbot-php
## Конфиг
В файле config.php укажите:
- $BotToken - Токен бота от https://t.me/BotFather
- $ChatId - ID чата (можно узнать с помощью любого популярного чат-менеджер бота. (https://t.me/MissRose_bot - /id)
- $HookSecret - Случайный и секретный ключ, чтобы запросы к боту мог слать только телеграм. Никто его не должен знать кроме вас.

## Установка вебхука
- ТОКЕН - токен бота
- ССЫЛКА - полный путь до файла hook.php на вашем сайте
- КЛЮЧ - тот самый секретный HookSecret

В браузере перейдите по

`https://api.telegram.org/botТОКЕН/setWebhook?url=https://ССЫЛКА?hook_secret=КЛЮЧ`


Или введите следующее в unix терминале:

`curl -s "https://api.telegram.org/botТОКЕН/setWebhook?url=https://ССЫЛКА?hook_secret=КЛЮЧ"`

Пример: `https://api.telegram.org/bot1234678:ABCDE?setWebhook?url=https://site.com/mybot/hook.php?hook_secret=uwodbwo`
