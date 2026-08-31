<?php
namespace Models;

class User extends \Model {
    protected $table = 'users';
    protected $fillable = [
        'username', 'email', 'password_hash', 'first_name', 'last_name',
        'role', 'status', 'email_verified_at', 'last_login_at'
    ];

    public function findByEmail($email) {
        return $this->findOneWhere('email', $email);
    }

    public function findByUsername($username) {
        return $this->findOneWhere('username', $username);
    }

    public function createUser($username, $email, $password, $role = 'resident', $firstName = '', $lastName = '') {
        return $this->create([
            'username' => $username,
            'email' => $email,
            'password_hash' => password_hash($password, PASSWORD_BCRYPT),
            'first_name' => $firstName,
            'last_name' => $lastName,
            'role' => $role,
            'status' => 'active',
        ]);
    }

    public function getRoleName($role) {
        $roles = [
            'captain' => 'Punong Barangay',
            'kagawad' => 'Barangay Kagawad',
            'secretary' => 'Barangay Secretary',
            'treasurer' => 'Barangay Treasurer',
            'bhw' => 'Barangay Health Worker',
            'tanod' => 'Barangay Tanod',
            'census' => 'Census Officer',
            'sk_chair' => 'SK Chairman',
            'resident' => 'Resident',
        ];
        return $roles[$role] ?? $role;
    }

    public function getAdminRoles() {
        return ['captain', 'kagawad', 'secretary', 'treasurer', 'bhw', 'tanod', 'census', 'sk_chair'];
    }
}
