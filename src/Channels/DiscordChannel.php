<?php

namespace Doppar\Notifier\Channels;

use Doppar\Notifier\Contracts\Notification;
use Doppar\Notifier\Channels\Contracts\ChannelDriver;

class DiscordChannel extends ChannelDriver
{
    /**
     * Send notification to Discord
     * 
     * @param mixed $notifiable
     * @param Notification $notification
     * @return void
     * @throws \RuntimeException
     */
    public function send($notifiable, Notification $notification): void
    {
        $webhookUrl = $notifiable->routeNotificationFor('discord');

        if (!$webhookUrl) {
            throw new \RuntimeException('No Discord webhook URL defined for notifiable entity.');
        }

        $content = $notification->content($notifiable);

        $payload = [
            'content' => $content['content'] ?? '',
            'username' => $content['username'] ?? config('notification.discord.username', 'Doppar Bot'),
            'avatar_url' => $content['avatar'] ?? null,
            'embeds' => $content['embeds'] ?? [],
        ];

        $this->postToDiscord($webhookUrl, $payload);
    }

    /**
     * Send payload to Discord webhook
     *
     * @param string $webhookUrl
     * @param array $payload
     * @return void
     * @throws \RuntimeException
     */
    protected function postToDiscord(string $webhookUrl, array $payload): void
    {
        $ch = curl_init($webhookUrl);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

        $result = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode < 200 || $httpCode >= 300) {
            throw new \RuntimeException("Discord notification failed with HTTP code: {$httpCode}");
        }
    }
}