<?php

namespace Doppar\Notifier\Channels;

use Phaseolies\Support\Facades\Log;
use Doppar\Notifier\Contracts\Notification;
use Doppar\Notifier\Channels\Contracts\ChannelDriver;

class SlackChannel extends ChannelDriver
{
    /**
     * Send notification to Slack
     * 
     * @param mixed $notifiable
     * @param Notification $notification
     * @return void
     * @throws \RuntimeException
     */
    public function send($notifiable, Notification $notification): void
    {
        $webhookUrl = $notifiable->routeNotificationFor('slack');

        if (!$webhookUrl) {
            throw new \RuntimeException('Slack webhook URL is missing.');
        }

        $content = $notification->content($notifiable);

        $payload = [
            'text' => $content['text'] ?? '',
            'username' => $content['username'] ?? 'Doppar Bot',
            'icon_emoji' => $content['icon'] ?? ':bell:'
        ];

        if (isset($content['attachments']) && is_array($content['attachments'])) {
            $payload['attachments'] = $content['attachments'];
        }

        if (isset($content['blocks']) && is_array($content['blocks'])) {
            $payload['blocks'] = $content['blocks'];
        }

        if (isset($content['channel'])) {
            $payload['channel'] = $content['channel'];
        }

        $this->postToSlack($webhookUrl, $payload);
    }

    /**
     * Send payload to Slack webhook
     *
     * @param string $webhookUrl
     * @param array $payload
     * @return void
     * @throws \RuntimeException
     */
    protected function postToSlack(string $webhookUrl, array $payload): void
    {
        $ch = curl_init($webhookUrl);

        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_TIMEOUT => 10,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);

        curl_close($ch);

        if ($httpCode !== 200 || trim($response) !== 'ok') {
            Log::error("Slack notification failed (HTTP {$httpCode}): " . ($response ?: $error));
        }
    }
}
