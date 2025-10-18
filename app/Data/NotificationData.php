<?php

namespace App\Data;

use App\Enums\NotificationTypeEnum;

class NotificationData
{
    public function __construct(
        public ?string $title = null,
        public ?string $body = null,
        public ?NotificationTypeEnum $type = NotificationTypeEnum::INFO,
        public ?int $receiver_id = null,
        public ?int $sender_id = null,
        public bool $is_read = false,
        public ?string $uri = null,
        public ?string $summary = null,
    ) {
    }

    public static function fromRequest($request): self
    {
        return new self(
            title: $request->input('title'),
            body: $request->input('body'),
            type: $request->input('type'),
            receiver_id: $request->input('receiver_id'),
            sender_id: $request->input('sender_id'),
            is_read: $request->input('is_read', false),
            uri: $request->input('uri'),
            summary: $request->input('summary'),
        );
    }

    public static function fromArray(array $data)
    {
        return new self(
            title: $data['title'] ?? null,
            body: $data['body'] ?? null,
            type: $data['type'] ?? NotificationTypeEnum::class,
            receiver_id: $data['receiver_id'] ?? null,
            sender_id: $data['sender_id'] ?? null,
            is_read: $data['is_read'] ?? false,
            uri: $data['uri'] ?? null,
            summary: $data['summary'] ?? null,
        );
    }

    public function toArray()
    {
        return [
            'title' => $this->title,
            'body' => $this->body,
            'type' => $this->type,
            'receiver_id' => $this->receiver_id,
            'sender_id' => $this->sender_id,
            'is_read' => $this->is_read,
            'uri' => $this->uri,
            'summary' => $this->summary,
        ];
    }
}
