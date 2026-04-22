<?php

namespace App\Mail\Transport;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Symfony\Component\Mailer\Exception\TransportException;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\AbstractTransport;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;

class GraphTransport extends AbstractTransport
{
    private Client $client;

    public function __construct(private readonly array $config)
    {
        parent::__construct();
        $this->client = new Client([
            'timeout' => 20,
        ]);
    }

    protected function doSend(SentMessage $message): void
    {
        $original = $message->getOriginalMessage();

        if (! $original instanceof Email) {
            throw new TransportException('Graph mailer only supports Email messages.');
        }

        $tenantId = (string) ($this->config['tenant_id'] ?? '');
        $clientId = (string) ($this->config['client_id'] ?? '');
        $clientSecret = (string) ($this->config['client_secret'] ?? '');
        $fromAddress = (string) ($this->config['from'] ?? '');

        if ($tenantId === '' || $clientId === '' || $clientSecret === '' || $fromAddress === '') {
            throw new TransportException('Graph mailer configuration is incomplete.');
        }

        $token = $this->fetchAccessToken($tenantId, $clientId, $clientSecret);
        $subject = (string) $original->getSubject();
        $htmlBody = $original->getHtmlBody();
        $textBody = $original->getTextBody();
        $content = $htmlBody ?: ($textBody ?: '');
        $contentType = $htmlBody ? 'HTML' : 'Text';

        $payload = [
            'message' => [
                'subject' => $subject,
                'body' => [
                    'contentType' => $contentType,
                    'content' => $content,
                ],
                'toRecipients' => $this->formatRecipients($original->getTo()),
                'ccRecipients' => $this->formatRecipients($original->getCc()),
                'bccRecipients' => $this->formatRecipients($original->getBcc()),
            ],
            'saveToSentItems' => false,
        ];

        if (! empty($original->getFrom())) {
            $from = $original->getFrom()[0];
            $payload['message']['from'] = [
                'emailAddress' => [
                    'address' => $from->getAddress(),
                    'name' => $from->getName(),
                ],
            ];
        }

        try {
            $response = $this->client->post(
                "https://graph.microsoft.com/v1.0/users/{$fromAddress}/sendMail",
                [
                    'headers' => [
                        'Authorization' => "Bearer {$token}",
                        'Content-Type' => 'application/json',
                    ],
                    'json' => $payload,
                ]
            );
        } catch (GuzzleException $e) {
            throw new TransportException("Graph send failed: {$e->getMessage()}", 0, $e);
        }

        if ($response->getStatusCode() >= 300) {
            throw new TransportException('Graph send failed with status code '.$response->getStatusCode());
        }
    }

    public function __toString(): string
    {
        return 'graph';
    }

    private function fetchAccessToken(string $tenantId, string $clientId, string $clientSecret): string
    {
        try {
            $response = $this->client->post(
                "https://login.microsoftonline.com/{$tenantId}/oauth2/v2.0/token",
                [
                    'form_params' => [
                        'client_id' => $clientId,
                        'client_secret' => $clientSecret,
                        'scope' => 'https://graph.microsoft.com/.default',
                        'grant_type' => 'client_credentials',
                    ],
                ]
            );
        } catch (GuzzleException $e) {
            throw new TransportException("Unable to fetch Graph token: {$e->getMessage()}", 0, $e);
        }

        $body = json_decode((string) $response->getBody(), true);
        $token = $body['access_token'] ?? null;

        if (! is_string($token) || $token === '') {
            throw new TransportException('Unable to fetch Graph token: access_token missing.');
        }

        return $token;
    }

    /**
     * @param  Address[]  $recipients
     * @return array<int, array<string, array<string, string>>>
     */
    private function formatRecipients(array $recipients): array
    {
        $formatted = [];

        foreach ($recipients as $recipient) {
            $formatted[] = [
                'emailAddress' => [
                    'address' => $recipient->getAddress(),
                    'name' => $recipient->getName(),
                ],
            ];
        }

        return $formatted;
    }
}
