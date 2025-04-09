<?php

namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class TelegramService
{
    protected $client;
    protected $apiToken;
    protected $chatId;
    protected $baseUrl;

    public function __construct()
    {
        $this->apiToken = env('TELEGRAM_BOT_TOKEN', '');
        $this->chatId = env('TELEGRAM_CHAT_ID', '');
        $this->baseUrl = "https://api.telegram.org/bot{$this->apiToken}/";
        $this->client = new Client([
            'base_uri' => $this->baseUrl,
            'timeout' => 5.0,
        ]);
    }

    /**
     * Отправка сообщения
     * 
     * @param string $text Текст сообщения
     * @param array $options Дополнительные параметры
     * @return mixed
     */
    public function sendMessage($text, $options = [])
    {
        try {
            $params = array_merge([
                'chat_id' => $this->chatId,
                'text' => $text,
                'parse_mode' => 'HTML'
            ], $options);

            $response = $this->client->post('sendMessage', [
                'json' => $params
            ]);

            return json_decode($response->getBody()->getContents(), true);
        } catch (\Exception $e) {
            Log::error('Telegram API Error: ' . $e->getMessage());
            return null;
        }
    }
}
