<?php

namespace App\Domains\POS\Services;

use App\Domains\POS\Models\PosTransaction;
use App\Domains\POS\Models\PosTransactionItem;
use App\Domains\POS\Models\Product;
use App\Domains\POS\Models\InventoryMovement;
use App\Domains\Customer\Models\Customer;
use App\Domains\Customer\Models\CustomerActivity;
use App\Domains\Booking\Models\Booking;
use App\Domains\Payment\Models\Payment;
use App\Domains\System\Services\AuditLogger;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class POSTransactionService
{
    /**
     * Checkout a POS transaction.
     */
    public static function checkout(array $data): PosTransaction
    {
        return DB::transaction(function () use ($data) {
            $outletId = $data['outlet_id'];
            $customerId = $data['customer_id'] ?? null;
            $bookingId = $data['booking_id'] ?? null;
            $staffId = $data['staff_id'] ?? null;
            $items = $data['items']; // array of [type => service/product, id => X, qty => Y, price => Z, discount => W]
            $discount = $data['discount'] ?? 0;
            $notes = $data['notes'] ?? null;
            $paymentMethod = $data['payment_method'];
            $txRef = $data['transaction_reference'] ?? null;

            // 1. Calculate totals
            $subtotal = 0;
            foreach ($items as $item) {
                $subtotal += ($item['price'] * $item['qty']) - $item['discount'];
            }
            $tax = round($subtotal * 0.10, 2); // 10% VAT
            $grandTotal = $subtotal - $discount + $tax;

            // Generate unique transaction number
            $datePrefix = Carbon::now()->format('Ymd');
            $randomSuffix = strtoupper(Str::random(4));
            $txNumber = "TX-{$datePrefix}-{$randomSuffix}";
            while (PosTransaction::where('transaction_number', $txNumber)->exists()) {
                $randomSuffix = strtoupper(Str::random(4));
                $txNumber = "TX-{$datePrefix}-{$randomSuffix}";
            }

            // 2. Create POS Transaction
            $transaction = PosTransaction::create([
                'transaction_number' => $txNumber,
                'outlet_id' => $outletId,
                'customer_id' => $customerId,
                'booking_id' => $bookingId,
                'staff_id' => $staffId,
                'subtotal' => $subtotal,
                'discount' => $discount,
                'tax' => $tax,
                'grand_total' => $grandTotal,
                'payment_status' => 'paid',
                'status' => 'completed',
                'payment_method' => $paymentMethod,
                'transaction_reference' => $txRef,
                'notes' => $notes,
                'completed_at' => Carbon::now()
            ]);

            // 3. Save items and update inventory
            foreach ($items as $item) {
                $itemSubtotal = ($item['price'] * $item['qty']) - $item['discount'];
                PosTransactionItem::create([
                    'transaction_id' => $transaction->id,
                    'item_type' => $item['type'],
                    'item_id' => $item['id'],
                    'quantity' => $item['qty'],
                    'unit_price' => $item['price'],
                    'discount' => $item['discount'],
                    'subtotal' => $itemSubtotal
                ]);

                if ($item['type'] === 'product') {
                    $product = Product::findOrFail($item['id']);
                    
                    // Log stock movement
                    InventoryMovement::create([
                        'product_id' => $product->id,
                        'type' => 'sale',
                        'quantity' => -$item['qty'],
                        'reference_type' => PosTransaction::class,
                        'reference_id' => $transaction->id,
                        'notes' => "POS Sale {$txNumber}"
                    ]);

                    // Deduct stock
                    $product->decrement('stock', $item['qty']);
                }
            }

            // 4. Link Payment
            Payment::create([
                'booking_id' => $bookingId,
                'transaction_id' => $transaction->id,
                'payment_method' => $paymentMethod,
                'transaction_reference' => $txRef,
                'amount' => $grandTotal,
                'status' => 'completed',
                'paid_at' => Carbon::now()
            ]);

            // 5. Update Booking Status (if checked-out booking)
            if ($bookingId) {
                $booking = Booking::findOrFail($bookingId);
                $booking->update(['status' => 'completed']);
                
                // Add status history
                DB::table('booking_status_histories')->insert([
                    'booking_id' => $booking->id,
                    'status' => 'completed',
                    'notes' => 'Completed via POS checkout.',
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now()
                ]);
            }

            // 6. Update CRM Customer Points & Activities
            if ($customerId) {
                $customer = Customer::findOrFail($customerId);
                
                // Points: 1 point per 10k IDR spent
                $earnedPoints = floor($grandTotal / 10000);
                if ($earnedPoints > 0) {
                    $customer->increment('loyalty_points', $earnedPoints);
                }

                // Log customer activities
                CustomerActivity::create([
                    'customer_id' => $customer->id,
                    'event_type' => 'transaction_created',
                    'event_date' => Carbon::now(),
                    'outlet_id' => $outletId,
                    'source' => 'pos',
                    'reference_type' => PosTransaction::class,
                    'reference_id' => $transaction->id,
                    'metadata' => [
                        'transaction_number' => $txNumber,
                        'grand_total' => $grandTotal,
                        'points_earned' => $earnedPoints
                    ]
                ]);
            }

            // 7. Audit log manual discounts
            if ($discount > 0) {
                AuditLogger::log(
                    'transaction_discount',
                    PosTransaction::class,
                    $transaction->id,
                    null,
                    ['discount' => $discount, 'reason' => 'Kasir applied cart discount']
                );
            }

            return $transaction;
        });
    }

    /**
     * Process a transaction refund.
     */
    public static function refund(PosTransaction $transaction): PosTransaction
    {
        return DB::transaction(function () use ($transaction) {
            if ($transaction->status === 'refunded') {
                throw new \Exception('Transaksi ini sudah di-refund sebelumnya.');
            }

            // Update status
            $transaction->update([
                'status' => 'refunded',
                'payment_status' => 'refunded'
            ]);

            // Reverse inventory movements
            foreach ($transaction->items as $item) {
                if ($item->item_type === 'product') {
                    $product = Product::findOrFail($item->item_id);
                    
                    InventoryMovement::create([
                        'product_id' => $product->id,
                        'type' => 'refund',
                        'quantity' => $item->quantity,
                        'reference_type' => PosTransaction::class,
                        'reference_id' => $transaction->id,
                        'notes' => "Refund Transaction {$transaction->transaction_number}"
                    ]);

                    $product->increment('stock', $item->quantity);
                }
            }

            // Update associated payments to refunded
            Payment::where('transaction_id', $transaction->id)->update(['status' => 'refunded']);

            // Deduct loyalty points and log refund activity
            if ($transaction->customer_id) {
                $customer = Customer::findOrFail($transaction->customer_id);
                $lostPoints = floor($transaction->grand_total / 10000);
                if ($lostPoints > 0) {
                    $customer->decrement('loyalty_points', min($lostPoints, $customer->loyalty_points));
                }

                CustomerActivity::create([
                    'customer_id' => $customer->id,
                    'event_type' => 'transaction_refunded',
                    'event_date' => Carbon::now(),
                    'outlet_id' => $transaction->outlet_id,
                    'source' => 'pos',
                    'reference_type' => PosTransaction::class,
                    'reference_id' => $transaction->id,
                    'metadata' => [
                        'transaction_number' => $transaction->transaction_number,
                        'grand_total' => $transaction->grand_total,
                        'points_lost' => $lostPoints
                    ]
                ]);
            }

            // Audit log refund authorization
            AuditLogger::log(
                'refund_authorized',
                PosTransaction::class,
                $transaction->id,
                ['status' => 'completed'],
                ['status' => 'refunded']
            );

            return $transaction;
        });
    }
}
