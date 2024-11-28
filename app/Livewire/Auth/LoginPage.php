<?php

namespace App\Livewire\Auth;

use Livewire\Attributes\Title;
use Livewire\Component;

#[Title("Login Page")]
class LoginPage extends Component
{
    public $email;
    public $password;

    public function save()
    {
        $this->validate([
            'email' => 'required|email|exists:users,email|max:255',
            'password' => 'required|min:6|max:50',
        ]);

        if (!auth()->attempt(['email' => $this->email, 'password' => $this->password])) {
            session()->flash('error', 'Invalid email or password');
            return;
        }

        return redirect()->intended('/');
    }

    public function render()
    {
        return view('livewire.auth.login-page');
    }
}
