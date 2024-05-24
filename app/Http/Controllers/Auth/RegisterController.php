<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Stripe\Stripe;
use Stripe\PaymentIntent;
use Illuminate\Support\Facades\Log;

class RegisterController extends Controller
{
    use RegistersUsers;

    protected $redirectTo = '/home';

    public function __construct()
    {
        $this->middleware('guest');
    }

    public function showRegistrationForm(Request $request)
    {
        $this->generateCaptcha($request);
        return view('auth.register', ['captcha' => $request->session()->get('captcha')]);
    }

    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'captcha_result' => ['required'],
        ], [
            'name.required' => 'Name is required',
            'email.required' => 'Email is required',
            'email.email' => 'Please enter a valid email address',
            'email.unique' => 'This email is already registered',
            'password.required' => 'Password is required',
            'password.min' => 'Password must be at least 8 characters',
            'password.confirmed' => 'Password and confirm password do not match',
            'captcha_result.required' => 'Captcha result is required'
        ]);
    }

    protected function create(array $data)
    {
        return User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);
    }

    public function register(Request $request)
    {
        $validator = $this->validator($request->all());

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors(), 'captcha' => $this->generateCaptcha($request)]);
        }

        $expected = $request->session()->get('expected');
        if ($request->input('captcha_result') != $expected) {
            return response()->json(['success' => false, 'errors' => ['captcha_result' => 'Captcha is incorrect'], 'captcha' => $this->generateCaptcha($request)]);
        }

        $stripeSecret = config('services.stripe.secret');
        if (!$stripeSecret) {
            return response()->json(['success' => false, 'message' => 'Stripe API key not found in configuration']);
        }

        Stripe::setApiKey($stripeSecret);

        try {
            $paymentIntent = PaymentIntent::create([
                'amount' => 5000,
                'currency' => 'usd',
                'payment_method_types' => ['card'],
                'description' => 'Registration Fee',
                'shipping' => [
                    'name' => $request->name,
                    'address' => [
                        'line1' => 'Dummy Address Line 1',
                        'line2' => 'Dummy Address Line 2',
                        'postal_code' => '123456',
                        'city' => 'Dummy City',
                        'state' => 'Dummy State',
                        'country' => 'US'
                    ]
                ]
            ]);

            return response()->json([
                'success' => true,
                'clientSecret' => $paymentIntent->client_secret,
                'userData' => $request->all(),
            ]);
        } catch (\Exception $e) {
            Log::error('Payment failed: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Payment failed: ' . $e->getMessage()]);
        }
    }

    private function generateCaptcha(Request $request)
    {
        $num1 = rand(10, 99);
        $num2 = rand(10, 99);
        $operations = ['+', '-', '*', '/'];
        $operation = $operations[array_rand($operations)];
        $captcha = "$num1 $operation $num2";
        $expected = eval("return $captcha;");

        $request->session()->put('captcha', $captcha);
        $request->session()->put('expected', $expected);

        return $captcha;
    }

    public function regenerateCaptcha(Request $request)
    {
        $captcha = $this->generateCaptcha($request);
        return response()->json(['captcha' => $captcha]);
    }

    public function completeRegistration(Request $request)
    {
        try {
            $this->validator($request->all())->validate();

            $user = $this->create($request->all());
            $this->guard()->login($user);

            Wallet::create([
                'user_id' => $user->id,
                'balance' => 0,
            ]);

            return response()->json(['success' => true, 'message' => 'Registration completed successfully']);
        } catch (\Exception $e) {
            Log::error('User creation failed: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Registration failed: ' . $e->getMessage()]);
        }
    }
}
