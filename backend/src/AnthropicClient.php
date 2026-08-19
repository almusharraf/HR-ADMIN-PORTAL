<?php
declare(strict_types=1);

final class AnthropicClient
{
    private const API_URL = 'https://api.anthropic.com/v1/messages';

    public function __construct(private readonly string $apiKey)
    {
    }

    public static function isConfigured(): bool
    {
        return (bool) config('ANTHROPIC_API_KEY');
    }

    /**
     * @param array<int, array{role: string, content: mixed}> $messages
     */
    public function messages(string $model, array $messages, int $maxTokens = 1024, ?string $system = null): string
    {
        $payload = [
            'model' => $model,
            'max_tokens' => $maxTokens,
            'messages' => $messages,
        ];
        if ($system !== null) {
            $payload['system'] = $system;
        }

        $ch = curl_init(self::API_URL);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                'content-type: application/json',
                'x-api-key: ' . $this->apiKey,
                'anthropic-version: 2023-06-01',
            ],
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_TIMEOUT => 60,
        ]);

        $body = curl_exec($ch);
        if ($body === false) {
            $error = curl_error($ch);
            throw new RuntimeException("Anthropic request failed: {$error}");
        }
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        $decoded = json_decode((string) $body, true);
        if ($status !== 200 || !is_array($decoded)) {
            throw new RuntimeException("Anthropic API error (HTTP {$status}): {$body}");
        }

        $text = '';
        foreach ($decoded['content'] ?? [] as $block) {
            if (($block['type'] ?? '') === 'text') {
                $text .= $block['text'];
            }
        }

        return $text;
    }
}
