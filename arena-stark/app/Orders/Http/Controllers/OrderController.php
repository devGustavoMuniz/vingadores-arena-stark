<?php

namespace App\Orders\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Orders\Http\Requests\ReserveTicketRequest;
use App\Orders\Models\Order;
use App\Orders\Services\OrderService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class OrderController extends Controller
{
    public function __construct(
        private readonly OrderService $orderService,
    ) {}

    /**
     * Initiates a ticket reservation.
     * Atomically decrements stock in Redis and returns a TTL-bound token.
     */
    public function reserve(ReserveTicketRequest $request): RedirectResponse
    {
        try {
            $token = $this->orderService->reserve(
                eventId: $request->integer('event_id'),
                userId: $request->user()->id,
            );

            return redirect()
                ->route('orders.confirm-view', ['token' => $token])
                ->with('success', 'Ticket reserved! You have 10 minutes to complete your purchase.');
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Shows the confirmation page for a pending reservation.
     */
    public function confirmView(string $token): Response
    {
        return Inertia::render('orders/confirm', [
            'token' => $token,
        ]);
    }

    /**
     * Confirms the purchase: validates reservation → persists order → notifies user.
     */
    public function confirm(string $token): RedirectResponse
    {
        try {
            $order = $this->orderService->confirm(
                reservationToken: $token,
                userId: auth()->id(),
            );

            return redirect()
                ->route('orders.success', ['order' => $order->id])
                ->with('success', 'Purchase confirmed! Your ticket code is: '.$order->ticket->code);
        } catch (\RuntimeException $e) {
            return redirect()
                ->route('events.index')
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Shows the success page after a confirmed purchase.
     */
    public function success(int $orderId): Response
    {
        $order = Order::with(['event', 'ticket'])
            ->where('user_id', auth()->id())
            ->findOrFail($orderId);

        return Inertia::render('orders/success', [
            'order' => [
                'id' => $order->id,
                'event_name' => $order->event->name,
                'ticket_code' => $order->ticket->code,
                'amount' => $order->amount,
                'confirmed_at' => $order->updated_at->toIso8601String(),
            ],
        ]);
    }
}
