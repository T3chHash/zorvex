<?php
declare(strict_types=1);

namespace Zorvex\Core;

class TelegramBot
{
    private string $token;
    private string $apiUrl;

    public function __construct(?string $token = null)
    {
        $this->token = $token ?? (string)zorvex_config('telegram.token');
        $this->apiUrl = "https://api.telegram.org/bot{$this->token}/";
    }

    public function request(string $method, array $params = []): array
    {
        $ch = curl_init($this->apiUrl . $method);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $params,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
        ]);

        $response = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            error_log("Telegram API Error [{$method}]: {$error}");
            return ['ok' => false, 'description' => $error];
        }

        $result = json_decode($response, true);
        return is_array($result) ? $result : ['ok' => false, 'raw' => $response];
    }

    public function sendMessage(
        int|string $chatId,
        string $text,
        ?array $replyMarkup = null,
        string $parseMode = 'HTML',
        bool $disablePreview = true
    ): array {
        $params = [
            'chat_id' => $chatId,
            'text' => $text,
            'parse_mode' => $parseMode,
            'disable_web_page_preview' => $disablePreview,
        ];

        if ($replyMarkup !== null) {
            $params['reply_markup'] = json_encode($replyMarkup);
        }

        return $this->request('sendMessage', $params);
    }

    public function editMessageText(
        int|string $chatId,
        int $messageId,
        string $text,
        ?array $replyMarkup = null,
        string $parseMode = 'HTML',
        bool $disablePreview = true
    ): array {
        $params = [
            'chat_id' => $chatId,
            'message_id' => $messageId,
            'text' => $text,
            'parse_mode' => $parseMode,
            'disable_web_page_preview' => $disablePreview,
        ];

        if ($replyMarkup !== null) {
            $params['reply_markup'] = json_encode($replyMarkup);
        }

        return $this->request('editMessageText', $params);
    }

    public function answerCallbackQuery(
        string $callbackQueryId,
        string $text = '',
        bool $showAlert = false
    ): array {
        return $this->request('answerCallbackQuery', [
            'callback_query_id' => $callbackQueryId,
            'text' => $text,
            'show_alert' => $showAlert,
        ]);
    }

    public function deleteMessage(int|string $chatId, int $messageId): array
    {
        return $this->request('deleteMessage', [
            'chat_id' => $chatId,
            'message_id' => $messageId,
        ]);
    }

    public function sendPhoto(
        int|string $chatId,
        string $photo,
        string $caption = '',
        ?array $replyMarkup = null,
        string $parseMode = 'HTML'
    ): array {
        $params = [
            'chat_id' => $chatId,
            'photo' => $photo,
            'caption' => $caption,
            'parse_mode' => $parseMode,
        ];

        if ($replyMarkup !== null) {
            $params['reply_markup'] = json_encode($replyMarkup);
        }

        return $this->request('sendPhoto', $params);
    }

    public function getChatMember(int|string $chatId, int $userId): array
    {
        return $this->request('getChatMember', [
            'chat_id' => $chatId,
            'user_id' => $userId,
        ]);
    }

    public function setWebhook(string $url, string $secretToken = ''): array
    {
        $params = ['url' => $url];
        if (!empty($secretToken)) {
            $params['secret_token'] = $secretToken;
        }
        return $this->request('setWebhook', $params);
    }
}
