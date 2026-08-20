<?php

namespace App\Services\WhatsApp;

class WhatsAppMessageBuilder
{
    protected array $parameters = [];

    /**
     * Add a body text parameter component.
     */
    public function addText(string $text): self
    {
        $this->parameters[] = [
            'type' => 'text',
            'text' => $text
        ];
        return $this;
    }

    /**
     * Add currency components.
     */
    public function addCurrency(int $amount, string $code = 'IDR'): self
    {
        $this->parameters[] = [
            'type' => 'currency',
            'currency' => [
                'fallback_value' => 'Rp ' . number_format($amount, 0, ',', '.'),
                'code' => $code,
                'amount_1000' => $amount * 1000
            ]
        ];
        return $this;
    }

    /**
     * Format components for Meta template parameters body payload.
     */
    public function buildComponents(): array
    {
        if (empty($this->parameters)) {
            return [];
        }

        return [
            [
                'type' => 'body',
                'parameters' => $this->parameters
            ]
        ];
    }
}
