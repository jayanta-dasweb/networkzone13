<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class LoginController extends Controller
{
    use AuthenticatesUsers;

    protected $redirectTo = '/home';

    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    public function showLoginForm(Request $request)
    {
        $this->generateCaptcha($request);
        Log::info('Captcha generated: ' . $request->session()->get('captcha'));
        Log::info('Expected result: ' . $request->session()->get('expected'));
        return view('auth.login', ['captcha' => $request->session()->get('captcha')]);
    }

    protected function validator(array $data)
    {
        return Validator::make($data, [
            'email' => ['required', 'string', 'email', 'max:255'],
            'password' => ['required', 'string'],
            'captcha_result' => ['required'],
        ]);
    }

    public function login(Request $request)
{
    $this->validator($request->all())->validate();

    $email = $request->input('email');
    $password = $request->input('password');
    $captchaResult = $request->input('captcha_result');

    $user = Auth::getProvider()->retrieveByCredentials(['email' => $email]);

    if (!$user) {
        $this->generateCaptcha($request);
        return back()->withErrors(['email' => 'The provided email is not registered'])->withInput();
    }

    if (!Auth::getProvider()->validateCredentials($user, ['password' => $password])) {
        $this->generateCaptcha($request);
        return back()->withErrors(['password' => 'The provided password is incorrect'])->withInput();
    }

    $expected = $request->session()->get('expected');
    Log::info('Captcha input: ' . $captchaResult);
    Log::info('Expected result from session: ' . $expected);

    if ($captchaResult != $expected) {
        $this->generateCaptcha($request);
        return back()->withErrors(['captcha_result' => 'Captcha is incorrect'])->withInput();
    }

    if ($this->attemptLogin($request)) {
        return $this->sendLoginResponse($request);
    } else {
        $this->generateCaptcha($request);
        return $this->sendFailedLoginResponse($request);
    }
}


    private function generateCaptcha(Request $request)
    {
        $num1 = rand(10, 99);
        $num2 = rand(10, 99);
        $operations = ['+', '-', '*', '/'];
        $operation = $operations[array_rand($operations)];
        $captcha = "$num1 $operation $num2";
        $expected = $this->evaluateMathExpression($num1, $num2, $operation);

        $request->session()->put('captcha', $captcha);
        $request->session()->put('expected', $expected);

        Log::info('Generated captcha: ' . $captcha);
        Log::info('Expected result stored in session: ' . $expected);

        return $captcha;
    }

    private function evaluateMathExpression($num1, $num2, $operation)
    {
        switch ($operation) {
            case '+':
                return $num1 + $num2;
            case '-':
                return $num1 - $num2;
            case '*':
                return $num1 * $num2;
            case '/':
                return $num2 != 0 ? $num1 / $num2 : 0; // Avoid division by zero
            default:
                return 0;
        }
    }

    public function regenerateCaptcha(Request $request)
    {
        $captcha = $this->generateCaptcha($request);
        return response()->json(['captcha' => $captcha]);
    }
}
