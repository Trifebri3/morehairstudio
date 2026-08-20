<?php

namespace App\Domains\WhatsApp\Services;

use App\Domains\WhatsApp\Models\WhatsAppCampaign;
use App\Domains\WhatsApp\Models\WhatsAppCampaignRecipient;
use App\Domains\WhatsApp\Models\WhatsAppTemplate;
use App\Domains\Customer\Models\Customer;
use App\Domains\System\Services\CommunicationService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class CampaignService
{
    /**
     * Create snapshot of recipients for a campaign.
     */
    public static function createRecipientSnapshot(WhatsAppCampaign $campaign): void
    {
        // 1. Fetch eligible customers based on filter
        $customers = self::getEligibleCustomers($campaign->recipient_type, $campaign->filters ?? []);

        // 2. Insert into snapshot (idempotent checks)
        foreach ($customers as $customer) {
            WhatsAppCampaignRecipient::firstOrCreate([
                'campaign_id' => $campaign->id,
                'customer_id' => $customer->id
            ], [
                'status' => 'PENDING'
            ]);
        }
    }

    /**
     * Process and dispatch campaign queue.
     */
    public static function executeCampaign(WhatsAppCampaign $campaign): void
    {
        $campaign->update(['status' => 'PROCESSING']);

        try {
            $template = DB::table('whatsapp_templates')
                ->where('template_name', $campaign->template_name)
                ->where('is_active', true)
                ->first();

            if (!$template) {
                throw new \Exception("Campaign template not found or inactive.");
            }

            $recipients = WhatsAppCampaignRecipient::where('campaign_id', $campaign->id)
                ->where('status', 'PENDING')
                ->with('customer')
                ->get();

            foreach ($recipients as $rec) {
                $customer = $rec->customer;

                // Opt-out guard: skip if customer opted out of marketing
                if (!$customer->whatsapp_marketing_opt_in) {
                    $rec->update([
                        'status' => 'SKIPPED',
                        'error_message' => 'Customer opted out of marketing.'
                    ]);
                    continue;
                }

                // Resolve message variables
                $messageBody = self::resolveVariables($template->body, $customer);

                // Send message
                $result = CommunicationService::sendWhatsApp($customer->phone, $messageBody);

                if ($result['success'] ?? false) {
                    $rec->update([
                        'status' => 'SENT',
                        'sent_at' => Carbon::now()
                    ]);
                } else {
                    $rec->update([
                        'status' => 'FAILED',
                        'error_message' => $result['error'] ?? 'API delivery failure.'
                    ]);
                }
            }

            $campaign->update(['status' => 'COMPLETED']);
        } catch (\Exception $e) {
            Log::error("Campaign execution failed: " . $e->getMessage());
            $campaign->update(['status' => 'FAILED']);
        }
    }

    /**
     * Resolve eligible customers based on recipient type.
     */
    public static function getEligibleCustomers(string $type, array $filters = []): \Illuminate\Support\Collection
    {
        $query = Customer::query();

        switch ($type) {
            case 'individual':
                if (!empty($filters['customer_id'])) {
                    $query->where('id', $filters['customer_id']);
                }
                break;
            case 'segment':
                if (!empty($filters['status'])) {
                    $query->where('status', $filters['status']); // e.g. active, inactive, lost
                }
                break;
            case 'filtered':
                if (!empty($filters['birthday_month'])) {
                    // Birthday month comparison (e.g. format: 08 for August)
                    $query->whereRaw("strftime('%m', dob) = ?", [$filters['birthday_month']]);
                }
                if (!empty($filters['birthday_today'])) {
                    $query->whereRaw("strftime('%m-%d', dob) = ?", [Carbon::now()->format('m-d')]);
                }
                if (!empty($filters['min_points'])) {
                    $query->where('loyalty_points', '>=', intval($filters['min_points']));
                }
                break;
            case 'all':
            default:
                break;
        }

        return $query->get();
    }

    /**
     * Resolve basic customer variables in body.
     */
    protected static function resolveVariables(string $text, Customer $customer): string
    {
        $replacements = [
            '{{customer_name}}' => $customer->name ?? 'Guest',
            '{{loyalty_points}}' => $customer->loyalty_points ?? 0,
        ];
        return str_replace(array_keys($replacements), array_values($replacements), $text);
    }
}
