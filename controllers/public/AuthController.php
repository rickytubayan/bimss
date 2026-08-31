<?php
namespace Controllers\Public;

class AuthController extends \Controller {
    public function showLogin() {
        if (\Auth::check()) {
            $this->redirectToDashboard();
        }
        $this->viewPublic('auth/login', ['title' => 'Login']);
    }

    public function login() {
        $data = $this->getInput();
        $validator = new \Validator();
        $result = $validator->validate($data, [
            'email' => 'required|email',
            'password' => 'required|min:8',
        ]);

        if ($result !== true) {
            set_old_input($data);
            flash('error', 'Please check your input.');
            redirect(url('auth/login'));
        }

        if (\Auth::attempt($data['email'], $data['password'])) {
            flash('success', 'Welcome back!');
            $this->redirectToDashboard();
        }

        flash('error', 'Invalid credentials or account is not active.');
        redirect(url('auth/login'));
    }

    public function showOTP() {
        $this->viewPublic('auth/otp', ['title' => 'OTP Login']);
    }

    public function verifyOTP() {
        $data = $this->getInput();
        $validator = new \Validator();
        $result = $validator->validate($data, [
            'email' => 'required|email',
            'otp' => 'required|numeric|digits:6',
        ]);

        if ($result !== true || !\Auth::attemptOTP($data['email'], $data['otp'])) {
            flash('error', 'Invalid OTP or OTP has expired.');
            redirect(url('auth/otp'));
        }

        flash('success', 'Login successful!');
        $this->redirectToDashboard();
    }

    public function showRegister() {
        $this->viewPublic('auth/register', ['title' => 'Register']);
    }

    public function register() {
        $data = $this->getInput();
        $validator = new \Validator();
        $result = $validator->validate($data, [
            'username' => 'required|min:3|max:50',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8|confirmed',
        ]);

        if ($result !== true) {
            set_old_input($data);
            flash('error', 'Please check your input.');
            redirect(url('auth/register'));
        }

        $userModel = new \Models\User();
        $userId = $userModel->createUser(
            $data['username'],
            $data['email'],
            $data['password'],
            'resident',
            $data['first_name'] ?? '',
            $data['last_name'] ?? ''
        );

        flash('success', 'Registration successful! Please login.');
        redirect(url('auth/login'));
    }

    public function logout() {
        \Auth::logout();
        flash('success', 'You have been logged out.');
        redirect(url(''));
    }

    private function redirectToDashboard() {
        $role = \Auth::role();
        $adminRoles = ['captain', 'kagawad', 'secretary', 'treasurer', 'bhw', 'tanod', 'census', 'sk_chair'];
        if (in_array($role, $adminRoles)) {
            redirect(url('admin/dashboard'));
        } else {
            redirect(url('public/dashboard'));
        }
    }
}
