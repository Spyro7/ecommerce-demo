<?php

namespace App\Livewire\Auth;

use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Title('Reset Password')]
class ResetPasswordPage extends Component
{
    public $token;
    #[Url]
    public $email;
    public $password;
    public $password_confirmation;
    public function mount($token)
    {
        $this->token = $token;
    }

    public function save()
    {
        $this->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:6|confirmed',
        ]);

        $status = Password::reset(
            [
                'email' => $this->email,
                'password' => $this->password,
                'password_confirmation' => $this->password_confirmation,
                'token' => $this->token,
            ],
            function (User $user, string $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                ])->setRememberToken(Str::random(60));

                $user->save();

                event(new PasswordReset($user));
            }
        );
        if ($status === Password::PASSWORD_RESET) {
            session()->flash('success', 'Your password has been reset successfully.');
            return redirect('/login');
        } elseif ($status === Password::INVALID_USER) {
            session()->flash('error', 'No user found for this email address.');
        } elseif ($status === Password::INVALID_TOKEN) {
            session()->flash('error', 'The password reset token is invalid or has expired.');
        } else {
            session()->flash('error', 'Something went wrong. Please try again.');
        }


    }

    public function render()
    {
        return view('livewire.auth.reset-password-page');
    }
}
