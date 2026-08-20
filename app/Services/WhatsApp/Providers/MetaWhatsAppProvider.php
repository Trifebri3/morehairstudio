<?php

namespace App\Services\WhatsApp\Providers;

use App\Services\WhatsApp\Contracts\WhatsAppProvider;
use Mohapinkepane\WhatsAppCloud\Facades\WhatsAppCloud;
use Mohapinkepane\WhatsAppCloud\Messages\TextMessage;
use Mohapinkepane\WhatsAppCloud\Messages\TemplateMessage;
use Mohapinkepane\WhatsAppCloud\Components\TemplateComponent;
use Mohapinkepane\WhatsAppCloud\Messages\MediaMessage;
use Mohapinkepane\WhatsAppCloud\Messages\RawPayloadMessage;
use Mohapinkepane\WhatsAppCloud\Exceptions\ApiException;
use Illuminate\Support\Facades\Log;

class MetaWhatsAppProvider implements WhatsAppProvider
{
    public function __construct(array $config)
    {
        // Settings are mapped dynamically in AppServiceProvider, so we can access configuration keys directly.
    }

    public function sendText(string $to, string $message): mixed
    {
        $cleanTo = preg_replace('/[^0-9]/', '', $to);

        if ($this->shouldSimulate()) {
            Log::info("Meta WhatsApp Cloud Simulation Outbound [Text] to {$cleanTo}: {$message}");
            return [
                'success' => true,
                'simulated' => true,
                'messages' => [['id' => 'simulated-' . uniqid()]]
            ];
        }

        try {
            $response = WhatsAppCloud::sendMessage($cleanTo, TextMessage::create($message));
            $response->send(); // Force execution within try-catch block

            if ($response->successful()) {
                return array_merge(['success' => true], $response->json());
            }

            Log::error("Meta WhatsApp Cloud API Error [Text] to {$cleanTo}: " . $response->body());
            return [
                'success' => false,
                'error' => $response->body(),
                'status' => $response->status()
            ];
        } catch (\Throwable $e) {
            Log::info("Meta WhatsApp Cloud Exception class: " . get_class($e));
            Log::info("Meta WhatsApp Cloud Exception message: " . $e->getMessage());
            
            if (isset($response) && $response instanceof \Mohapinkepane\WhatsAppCloud\Client\PendingSendResponse) {
                try {
                    $ref = new \ReflectionProperty($response, 'sent');
                    $ref->setAccessible(true);
                    $ref->setValue($response, true);
                } catch (\Throwable $ignored) {}
            }

            $error = $e->getMessage();
            $status = 500;

            if ($e instanceof ApiException) {
                $error = json_encode($e->context());
                $status = $e->statusCode();
            } elseif (isset($e->response) && is_object($e->response) && method_exists($e->response, 'body')) {
                $error = $e->response->body();
                $status = $e->response->status();
            } elseif (method_exists($e, 'getResponse') && $e->getResponse()) {
                $response = $e->getResponse();
                $body = $response->getBody();
                if (method_exists($body, 'isSeekable') && $body->isSeekable()) {
                    try {
                        $body->rewind();
                    } catch (\Throwable $ignored) {}
                }
                $error = (string) $body;
                $status = $response->getStatusCode();
            }

            return [
                'success' => false,
                'error' => $error,
                'status' => $status
            ];
        }
    }

    public function sendTemplate(string $to, string $template, array $parameters = []): mixed
    {
        $cleanTo = preg_replace('/[^0-9]/', '', $to);

        if ($this->shouldSimulate()) {
            Log::info("Meta WhatsApp Cloud Simulation Outbound [Template] to {$cleanTo} (Template: {$template})", $parameters);
            return [
                'success' => true,
                'simulated' => true,
                'messages' => [['id' => 'simulated-' . uniqid()]]
            ];
        }

        try {
            $lang = $parameters['language'] ?? 'id';
            $templateMsg = TemplateMessage::create($template, $lang);

            // Bind body values if present
            if (isset($parameters['body']) && is_array($parameters['body'])) {
                // Ensure all values are converted to string
                $stringValues = array_map(fn($v) => (string)$v, $parameters['body']);
                $templateMsg = $templateMsg->addComponent(TemplateComponent::textBody(...$stringValues));
            }

            // Bind quick reply buttons or other custom components if present in structured format
            if (isset($parameters['components']) && is_array($parameters['components'])) {
                foreach ($parameters['components'] as $componentData) {
                    if ($componentData instanceof TemplateComponent) {
                        $templateMsg = $templateMsg->addComponent($componentData);
                    }
                }
            }

            $response = WhatsAppCloud::sendMessage($cleanTo, $templateMsg);
            $response->send(); // Force execution within try-catch block

            if ($response->successful()) {
                return array_merge(['success' => true], $response->json());
            }

            Log::error("Meta WhatsApp Cloud API Error [Template] to {$cleanTo}: " . $response->body());
            return [
                'success' => false,
                'error' => $response->body(),
                'status' => $response->status()
            ];
        } catch (\Throwable $e) {
            Log::error("Meta WhatsApp Cloud Provider Error [Template] to {$cleanTo}: " . $e->getMessage());

            if (isset($response) && $response instanceof \Mohapinkepane\WhatsAppCloud\Client\PendingSendResponse) {
                try {
                    $ref = new \ReflectionProperty($response, 'sent');
                    $ref->setAccessible(true);
                    $ref->setValue($response, true);
                } catch (\Throwable $ignored) {}
            }

            $error = $e->getMessage();
            $status = 500;

            if ($e instanceof ApiException) {
                $error = json_encode($e->context());
                $status = $e->statusCode();
            } elseif (isset($e->response) && is_object($e->response) && method_exists($e->response, 'body')) {
                $error = $e->response->body();
                $status = $e->response->status();
            } elseif (method_exists($e, 'getResponse') && $e->getResponse()) {
                $response = $e->getResponse();
                $body = $response->getBody();
                if (method_exists($body, 'isSeekable') && $body->isSeekable()) {
                    try {
                        $body->rewind();
                    } catch (\Throwable $ignored) {}
                }
                $error = (string) $body;
                $status = $response->getStatusCode();
            }

            return [
                'success' => false,
                'error' => $error,
                'status' => $status
            ];
        }
    }

    public function sendMedia(string $to, string $mediaType, string $media): mixed
    {
        $cleanTo = preg_replace('/[^0-9]/', '', $to);

        if ($this->shouldSimulate()) {
            Log::info("Meta WhatsApp Cloud Simulation Outbound [Media] to {$cleanTo} ({$mediaType}: {$media})");
            return [
                'success' => true,
                'simulated' => true,
                'messages' => [['id' => 'simulated-' . uniqid()]]
            ];
        }

        try {
            $mediaMsg = MediaMessage::create($mediaType);
            
            // Check if input is a numeric ID or a URL link
            if (is_numeric($media)) {
                $mediaMsg = $mediaMsg->id($media);
            } else {
                $mediaMsg = $mediaMsg->url($media);
            }

            $response = WhatsAppCloud::sendMessage($cleanTo, $mediaMsg);
            $response->send(); // Force execution within try-catch block

            if ($response->successful()) {
                return array_merge(['success' => true], $response->json());
            }

            Log::error("Meta WhatsApp Cloud API Error [Media] to {$cleanTo}: " . $response->body());
            return [
                'success' => false,
                'error' => $response->body(),
                'status' => $response->status()
            ];
        } catch (\Throwable $e) {
            Log::error("Meta WhatsApp Cloud Provider Error [Media] to {$cleanTo}: " . $e->getMessage());

            if (isset($response) && $response instanceof \Mohapinkepane\WhatsAppCloud\Client\PendingSendResponse) {
                try {
                    $ref = new \ReflectionProperty($response, 'sent');
                    $ref->setAccessible(true);
                    $ref->setValue($response, true);
                } catch (\Throwable $ignored) {}
            }

            $error = $e->getMessage();
            $status = 500;

            if ($e instanceof ApiException) {
                $error = json_encode($e->context());
                $status = $e->statusCode();
            } elseif (isset($e->response) && is_object($e->response) && method_exists($e->response, 'body')) {
                $error = $e->response->body();
                $status = $e->response->status();
            } elseif (method_exists($e, 'getResponse') && $e->getResponse()) {
                $response = $e->getResponse();
                $body = $response->getBody();
                if (method_exists($body, 'isSeekable') && $body->isSeekable()) {
                    try {
                        $body->rewind();
                    } catch (\Throwable $ignored) {}
                }
                $error = (string) $body;
                $status = $response->getStatusCode();
            }

            return [
                'success' => false,
                'error' => $error,
                'status' => $status
            ];
        }
    }

    public function sendInteractive(string $to, array $payload): mixed
    {
        $cleanTo = preg_replace('/[^0-9]/', '', $to);

        if ($this->shouldSimulate()) {
            Log::info("Meta WhatsApp Cloud Simulation Outbound [Interactive] to {$cleanTo}", $payload);
            return [
                'success' => true,
                'simulated' => true,
                'messages' => [['id' => 'simulated-' . uniqid()]]
            ];
        }

        try {
            // Interactive payloads are passed as raw arrays matching Meta structure
            $response = WhatsAppCloud::sendMessage($cleanTo, new RawPayloadMessage($payload));
            $response->send(); // Force execution within try-catch block

            if ($response->successful()) {
                return array_merge(['success' => true], $response->json());
            }

            Log::error("Meta WhatsApp Cloud API Error [Interactive] to {$cleanTo}: " . $response->body());
            return [
                'success' => false,
                'error' => $response->body(),
                'status' => $response->status()
            ];
        } catch (\Throwable $e) {
            Log::error("Meta WhatsApp Cloud Provider Error [Interactive] to {$cleanTo}: " . $e->getMessage());

            if (isset($response) && $response instanceof \Mohapinkepane\WhatsAppCloud\Client\PendingSendResponse) {
                try {
                    $ref = new \ReflectionProperty($response, 'sent');
                    $ref->setAccessible(true);
                    $ref->setValue($response, true);
                } catch (\Throwable $ignored) {}
            }

            $error = $e->getMessage();
            $status = 500;

            if ($e instanceof ApiException) {
                $error = json_encode($e->context());
                $status = $e->statusCode();
            } elseif (isset($e->response) && is_object($e->response) && method_exists($e->response, 'body')) {
                $error = $e->response->body();
                $status = $e->response->status();
            } elseif (method_exists($e, 'getResponse') && $e->getResponse()) {
                $response = $e->getResponse();
                $body = $response->getBody();
                if (method_exists($body, 'isSeekable') && $body->isSeekable()) {
                    try {
                        $body->rewind();
                    } catch (\Throwable $ignored) {}
                }
                $error = (string) $body;
                $status = $response->getStatusCode();
            }

            return [
                'success' => false,
                'error' => $error,
                'status' => $status
            ];
        }
    }

    /**
     * Determine if we should simulate sending due to missing credentials.
     */
    protected function shouldSimulate(): bool
    {
        $accessToken = config('whatsapp-cloud.access_token');
        $phoneNumberId = config('whatsapp-cloud.phone_number_id');

        return empty($accessToken) || empty($phoneNumberId);
    }
}
