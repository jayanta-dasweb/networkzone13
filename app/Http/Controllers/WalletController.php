<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Wallet;
use Stripe\Stripe;
use Stripe\PaymentIntent;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Models\Transaction;

class WalletController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $user = Auth::user();
        $wallet = Wallet::where('user_id', $user->id)->first();
        $transactions = Transaction::where('user_id', $user->id)->orderBy('created_at', 'desc')->get();

        return view('wallet.index', [
            'balance' => $wallet ? $wallet->balance : 0,
            'transactions' => $transactions
        ]);
    }

    public function addFunds(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:5',
            'stripeToken' => 'required',
        ]);

        Stripe::setApiKey(env('STRIPE_SECRET'));
        $amount = $request->input('amount');

        try {
            Log::info('Attempting to create PaymentIntent for amount: ' . $amount);

            // Create PaymentIntent with payment_method_data
            $paymentIntent = PaymentIntent::create([
                'amount' => $amount * 100,
                'currency' => 'usd',
                'payment_method_data' => [
                    'type' => 'card',
                    'card' => [
                        'token' => $request->stripeToken,
                    ],
                ],
                'description' => 'Add Funds',
                'shipping' => [
                    'name' => auth()->user()->name,
                    'address' => [
                        'line1' => 'Dummy Address Line 1',
                        'line2' => 'Dummy Address Line 2',
                        'postal_code' => '123456',
                        'city' => 'Dummy City',
                        'state' => 'Dummy State',
                        'country' => 'US', // Using US as the country outside India
                    ],
                ],
                'automatic_payment_methods' => [
                    'enabled' => true,
                ],
            ]);

            Log::info('PaymentIntent created successfully with clientSecret: ' . $paymentIntent->client_secret);

            return response()->json([
                'success' => true,
                'clientSecret' => $paymentIntent->client_secret,
            ]);
        } catch (\Exception $e) {
            Log::error('Payment initiation failed: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Payment initiation failed: ' . $e->getMessage()]);
        }
    }

    public function completePayment(Request $request)
    {
        $amount = $request->input('amount');
        $userId = auth()->id();

        try {
            Log::info('Completing payment for user: ' . $userId);

            // Update wallet balance
            $wallet = Wallet::firstOrCreate(['user_id' => $userId]);
            $wallet->balance += $amount;
            $wallet->save();

            Log::info('Wallet balance updated successfully for user: ' . $userId . ' New Balance: ' . $wallet->balance);

            // Record transaction
            Transaction::create([
                'user_id' => $userId,
                'description' => 'Added funds to wallet',
                'amount' => $amount,
            ]);

            Log::info('Transaction recorded successfully for user: ' . $userId . ' Amount: ' . $amount);

            return response()->json(['success' => true, 'message' => 'Funds added successfully']);
        } catch (\Exception $e) {
            Log::error('Error completing payment: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error completing payment: ' . $e->getMessage()]);
        }
    }
}
