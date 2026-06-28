<?php

namespace App\Http\Controllers;

use App\Services\Payments\StripeWebhookService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Stripe\Exception\SignatureVerificationException;
use UnexpectedValueException;

class StripeWebhookController extends Controller
{
    public function __invoke(Request $request, StripeWebhookService $webhookService)
    {
        try {
            $webhookService->handle(
                $request->getContent(),
                (string) $request->header('Stripe-Signature', ''),
            );
        } catch (SignatureVerificationException|UnexpectedValueException $exception) {
            Log::warning('Stripe webhook rejected.', [
                'reason' => $exception::class,
                'message' => $exception->getMessage(),
            ]);

            return response('Invalid Stripe webhook signature.', 400);
        }

        return response('Webhook received.', 200);
    }
}
