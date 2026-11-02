<?php
namespace Controllers\Public;

class ProfileController extends \Controller {
    public function index() {
        $user = \Auth::user();
        if (!$user) {
            redirect(url('auth/login'));
        }

        $userModel = new \Models\User();

        $this->viewPublic('profile/index', [
            'title' => 'My Profile',
            'user' => $user,
            'userModel' => $userModel,
        ]);
    }

    public function updatePassword() {
        $user = \Auth::user();
        if (!$user) {
            redirect(url('auth/login'));
        }

        $input = $this->getInput();

        $current = $input['current_password'] ?? '';
        $password = $input['new_password'] ?? '';
        $confirm = $input['confirm_password'] ?? '';

        if ($current === '') {
            flash('error', 'Please enter your current password.');
            redirect(url('public/profile'));
        }
        if (!password_verify($current, $user['password_hash'])) {
            flash('error', 'Your current password is incorrect.');
            redirect(url('public/profile'));
        }
        if (strlen($password) < 8) {
            flash('error', 'New password must be at least 8 characters.');
            redirect(url('public/profile'));
        }
        if ($password !== $confirm) {
            flash('error', 'New passwords do not match.');
            redirect(url('public/profile'));
        }

        $hash = password_hash($password, PASSWORD_BCRYPT);
        $this->db->prepare("UPDATE users SET password_hash = ?, updated_at = NOW() WHERE id = ?")
            ->execute([$hash, $user['id']]);

        $this->db->prepare("INSERT INTO audit_logs (user_id, action, table_name, record_id, ip_address, user_agent, created_at) VALUES (?, 'change_password', 'users', ?, ?, ?, NOW())")
            ->execute([$user['id'], $user['id'], get_client_ip(), $_SERVER['HTTP_USER_AGENT'] ?? '']);

        flash('success', 'Your password has been updated.');
        redirect(url('public/profile'));
    }

    public function qrCode() {
        $this->viewPublic('home/index', ['title' => 'Under Construction']);
    }

    public function __call($name, $args) {
        $this->viewPublic('home/index', ['title' => 'Under Construction']);
    }
}
