<?php
declare(strict_types=1);

namespace Zorvex\Core;

class Request
{
    private array $update;
    private ?array $message = null;
    private ?array $callbackQuery = null;
    private ?int $userId = null;
    private ?int $chatId = null;
    private ?string $text = null;
    private ?string $callbackData = null;
    private ?int $messageId = null;
    private ?string $photoFileId = null;

    public function __construct(array $update)
    {
        $this->update = $update;

        if (isset($update['message'])) {
            $this->message = $update['message'];
            $this->chatId = (int)$this->message['chat']['id'];
            $this->userId = (int)$this->message['from']['id'];
            $this->text = trim($this->message['text'] ?? $this->message['caption'] ?? '');
            $this->messageId = (int)$this->message['message_id'];

            if (!empty($this->message['photo'])) {
                $photos = $this->message['photo'];
                $lastPhoto = end($photos);
                $this->photoFileId = $lastPhoto['file_id'] ?? null;
            }
        } elseif (isset($update['callback_query'])) {
            $this->callbackQuery = $update['callback_query'];
            $this->userId = (int)$this->callbackQuery['from']['id'];
            $this->chatId = (int)$this->callbackQuery['message']['chat']['id'];
            $this->callbackData = trim($this->callbackQuery['data'] ?? '');
            $this->messageId = (int)$this->callbackQuery['message']['message_id'];
        }
    }

    public function getUpdate(): array
    {
        return $this->update;
    }

    public function isCallbackQuery(): bool
    {
        return $this->callbackQuery !== null;
    }

    public function isMessage(): bool
    {
        return $this->message !== null;
    }

    public function getUserId(): ?int
    {
        return $this->userId;
    }

    public function getChatId(): ?int
    {
        return $this->chatId;
    }

    public function getText(): ?string
    {
        return $this->text;
    }

    public function getCallbackData(): ?string
    {
        return $this->callbackData;
    }

    public function getCallbackQueryId(): ?string
    {
        return $this->callbackQuery['id'] ?? null;
    }

    public function getMessageId(): ?int
    {
        return $this->messageId;
    }

    public function getPhotoFileId(): ?string
    {
        return $this->photoFileId;
    }

    public function getUserInfo(): array
    {
        $from = $this->message['from'] ?? $this->callbackQuery['from'] ?? [];
        return [
            'id' => $from['id'] ?? 0,
            'username' => $from['username'] ?? null,
            'first_name' => $from['first_name'] ?? 'کاربر',
        ];
    }
}
