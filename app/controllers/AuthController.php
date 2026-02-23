<?php

declare(strict_types=1);

require_once __DIR__ . '/../core/Helpers.php';
require_once __DIR__ . '/../core/Session.php';
require_once __DIR__ . '/../repositories/UserRepository.php';
require_once __DIR__ . '/../services/ValidationService.php';

final class AuthController
{
    private UserRepository $users;

    public function __construct()
    {
        $this->users = new UserRepository();
    }

    public function register(): void
    {
        $input = read_json_input();
        ValidationService::requireFields($input, ['name', 'email', 'password']);

        $name     = sanitize((string) $input['name']);
        $email    = strtolower(trim((string) $input['email']));
        $password = (string) $input['password'];

        ValidationService::requireEmail($email);
        ValidationService::requireMinLength($password, 6, 'Password');

        if ($this->users->findByEmail($email)) {
            json_error('Email already registered.', 409);
        }

        $id = $this->users->create($name, $email, password_hash($password, PASSWORD_DEFAULT));
        Session::login($id, $name, $email);

        json_success(['user' => ['id' => $id, 'name' => $name, 'email' => $email]], 'Registration successful.');
    }

    public function login(): void
    {
        $input = read_json_input();
        ValidationService::requireFields($input, ['email', 'password']);

        $email    = strtolower(trim((string) $input['email']));
        $password = (string) $input['password'];

        $user = $this->users->findByEmail($email);
        if (!$user || empty($user['password_hash']) || !password_verify($password, $user['password_hash'])) {
            if ($user && empty($user['password_hash']) && $user['auth_provider'] === 'google') {
                json_error('This account uses Google sign-in. Please use the Google button.', 401);
            }
            json_error('Invalid credentials.', 401);
        }

        Session::login((int) $user['id'], $user['name'], $user['email']);
        json_success(['user' => ['id' => (int) $user['id'], 'name' => $user['name'], 'email' => $user['email']]], 'Login successful.');
    }

    public function logout(): void
    {
        Session::logout();
        json_success([], 'Logged out.');
    }

    /**
     * Redirect user to Google's OAuth consent screen.
     */
    public function googleRedirect(): void
    {
        require_once __DIR__ . '/../core/Config.php';
        Session::start();

        $state = bin2hex(random_bytes(16));
        $_SESSION['oauth_state'] = $state;

        $params = http_build_query([
            'client_id'     => Config::googleClientId(),
            'redirect_uri'  => Config::googleRedirectUri(),
            'response_type' => 'code',
            'scope'         => 'openid email profile',
            'state'         => $state,
            'access_type'   => 'offline',
            'prompt'        => 'select_account',
        ]);

        header('Location: https://accounts.google.com/o/oauth2/v2/auth?' . $params);
        exit;
    }

    /**
     * Handle Google OAuth callback — exchange code for token, get profile, login/register.
     */
    public function googleCallback(): void
    {
        require_once __DIR__ . '/../core/Config.php';
        Session::start();

        // Validate state
        if (empty($_GET['state']) || empty($_SESSION['oauth_state']) || $_GET['state'] !== $_SESSION['oauth_state']) {
            header('Location: /hackathon/public/login.php?error=invalid_state');
            exit;
        }
        unset($_SESSION['oauth_state']);

        if (empty($_GET['code'])) {
            header('Location: /hackathon/public/login.php?error=no_code');
            exit;
        }

        // Exchange code for access token
        $tokenData = $this->exchangeGoogleCode($_GET['code']);
        if (!$tokenData || empty($tokenData['access_token'])) {
            header('Location: /hackathon/public/login.php?error=token_failed');
            exit;
        }

        // Get user profile from Google
        $profile = $this->getGoogleProfile($tokenData['access_token']);
        if (!$profile || empty($profile['sub'])) {
            header('Location: /hackathon/public/login.php?error=profile_failed');
            exit;
        }

        $googleId = (string) $profile['sub'];
        $email    = strtolower((string) ($profile['email'] ?? ''));
        $name     = (string) ($profile['name'] ?? 'User');
        $avatar   = (string) ($profile['picture'] ?? '');

        // Find or create user
        $user = $this->users->findByGoogleId($googleId);

        if (!$user) {
            // Check if email already exists (local account) — link it
            $user = $this->users->findByEmail($email);
            if ($user) {
                $this->users->linkGoogle((int) $user['id'], $googleId, $avatar);
                $user = $this->users->findById((int) $user['id']);
            } else {
                // Brand-new user via Google
                $id   = $this->users->createFromGoogle($name, $email, $googleId, $avatar);
                $user = $this->users->findById($id);
            }
        }

        Session::login((int) $user['id'], $user['name'], $user['email']);
        header('Location: /hackathon/public/dashboard.php');
        exit;
    }

    // ── Google helpers (pure PHP curl, no libraries) ─────────────

    private function exchangeGoogleCode(string $code): ?array
    {
        $ch = curl_init('https://oauth2.googleapis.com/token');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => http_build_query([
                'code'          => $code,
                'client_id'     => Config::googleClientId(),
                'client_secret' => Config::googleClientSecret(),
                'redirect_uri'  => Config::googleRedirectUri(),
                'grant_type'    => 'authorization_code',
            ]),
            CURLOPT_TIMEOUT => 15,
        ]);
        $resp = curl_exec($ch);
        curl_close($ch);
        return $resp ? json_decode($resp, true) : null;
    }

    private function getGoogleProfile(string $accessToken): ?array
    {
        $ch = curl_init('https://www.googleapis.com/oauth2/v3/userinfo');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => ['Authorization: Bearer ' . $accessToken],
            CURLOPT_TIMEOUT        => 10,
        ]);
        $resp = curl_exec($ch);
        curl_close($ch);
        return $resp ? json_decode($resp, true) : null;
    }
}
