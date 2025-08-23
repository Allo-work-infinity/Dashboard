<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionPlan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Throwable;

class PaymentApiController extends Controller
{

    public function initKonnectPayment(Request $request)
    {
        $request->validate([
            'subscription_plan_id' => ['required', 'integer', 'exists:subscription_plans,id'],
            'token'                => ['required', 'string'],
            'description'          => ['nullable', 'string', 'max:255'],
        ]);

        $user = Auth::user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $plan = SubscriptionPlan::query()
            ->active()
            ->findOrFail($request->integer('subscription_plan_id'));

        // Amount in millimes (TND x 1000) as string
        $amountMillimes = (string) (int) round(((float) $plan->price) * 1000);

        $url      = config('services.konnect.init_url', 'https://api.sandbox.konnect.network/api/v2/payments/init-payment');
        $walletId = config('services.konnect.wallet_id');
        $apiKey   = config('services.konnect.api_key');

        if (!$walletId || !$apiKey) {
            return response()->json(['message' => 'Konnect credentials not configured'], 500);
        }

        $payload = [
            'receiverWalletId'        => $walletId,
            'token'                   => $request->string('token'),
            'amount'                  => $amountMillimes,
            'type'                    => 'immediate',
            'description'             => $request->string('description', 'Subscription: '.$plan->name),
            'acceptedPaymentMethods'  => ['bank_card'],
            'lifespan'                => 10,
            'checkoutForm'            => false,
            'addPaymentFeesToAmount'  => false,
            'firstName'               => (string) ($user->first_name ?? ''),
            'lastName'                => (string) ($user->last_name ?? ''),
            'phoneNumber'             => (string) ($user->phone ?? ''),
            'email'                   => (string) ($user->email ?? ''),
            'orderId'                 => 'plan_'.$plan->id.'_'.Str::uuid()->toString(),
            'webhook'                 => config('services.konnect.webhook_url', 'https://merchant.tech/api/notification_payment'),
            'silentWebhook'           => true,
            'successUrl'              => config('services.konnect.success_url', 'https://dev.konnect.network/gateway/payment-success'),
            'failUrl'                 => config('services.konnect.fail_url',    'https://dev.konnect.network/gateway/payment-failure'),
            'theme'                   => 'light',
        ];

        try {
            $response = Http::withHeaders([
                    'Content-Type' => 'application/json',
                    'x-api-key'    => $apiKey,
                ])
                ->timeout(20)
                ->post($url, $payload);

            if ($response->successful()) {
                return response()->json([
                    'response' => 'Success',
                    'data'     => $response->json(),
                ], 200);
            }

            return response()->json([
                'response' => 'Error',
                'status'   => $response->status(),
                'data'     => $response->json(),
            ], $response->status());
        } catch (Throwable $e) {
            return response()->json([
                'response' => 'Exception',
                'message'  => $e->getMessage(),
            ], 500);
        }
    }
}
