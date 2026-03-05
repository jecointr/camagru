<?php
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/ImageProcessor.php';

class AuthController {

    private function checkCsrf() {
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            die("Security error (CSRF): invalid session or intrusion attempt.");
        }
    }
    
    public function register() {
        if (isset($_SESSION['user_id'])) {
            header('Location: /');
            exit;
        }
        $error = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->checkCsrf();

            $username = trim($_POST['username']);
            $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
            $password = $_POST['password'];

            if (strlen($password) < 8 || !preg_match("/[A-Z]/", $password) || !preg_match("/[0-9]/", $password)) {
                $error = "Password must be at least 8 characters with one uppercase letter and one number.";
            } elseif ($username && $email && $password) {
                $userModel = new User();

                if ($userModel->userExists($username, $email)) {
                    $error = "Username or email already taken.";
                } else {
                    $token = bin2hex(random_bytes(32));
                    if ($userModel->create($username, $email, $password, $token)) {
                       
                        $appUrl = rtrim($_ENV['APP_URL'] ?? 'http://localhost:8080', '/');
                        $link = "$appUrl/verify?token=$token";
                        $subject = "Confirm your Camagru account";
                        $message = "Welcome $username,\n\nClick the link below to activate your account:\n$link";
                        $headers = "From: no-reply@camagru.fr";

                        mail($email, $subject, $message, $headers);
                        header('Location: /login?msg=registered');
                        exit;
                    } else {
                        $error = "An error occurred during registration.";
                    }
                }
            } else {
                $error = "Invalid data.";
            }
        }
        require __DIR__ . '/../views/auth/register.php';
    }

    public function login() {
        if (isset($_SESSION['user_id'])) {
            header('Location: /');
            exit;
        }
        $error = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->checkCsrf();

            $username = $_POST['username'];
            $password = $_POST['password'];
           
            $userModel = new User();
            $result = $userModel->login($username, $password);

            if ($result === "NOT_VERIFIED") {
                $error = "Please verify your email address before logging in.";
            } elseif ($result) {
                session_regenerate_id(true);
                $_SESSION['user_id'] = $result['id'];
                $_SESSION['username'] = $result['username'];
                $_SESSION['profile_pic'] = $result['profile_pic'];
                header('Location: /');
                exit;
            } else {
                $error = "Incorrect username or password.";
            }
        }
        require __DIR__ . '/../views/auth/login.php';
    }

    public function logout() {
        session_destroy();
        header('Location: /login');
        exit;
    }

    public function verify() {
        if (isset($_GET['token'])) {
            $userModel = new User();
            if ($userModel->verifyAccount($_GET['token'])) {
                header('Location: /login?msg=verified');
            } else {
                echo "Invalid or expired verification link.";
            }
        }
    }

    public function forgot() {
        $error = '';
        $success = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->checkCsrf();
            $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);

            if ($email) {
                $userModel = new User();
                $token = $userModel->setResetToken($email);

                if ($token) {
                    $appUrl = rtrim($_ENV['APP_URL'] ?? 'http://localhost:8080', '/');
                    $link = "$appUrl/reset?token=$token";
                    $subject = "Reset your Camagru password";
                    $message = "Hello,\n\nClick the link below to reset your password:\n$link\n\nThis link expires in 1 hour.";
                    $headers = "From: no-reply@camagru.fr";

                    mail($email, $subject, $message, $headers);
                }
                $success = "If this email exists, a reset link has been sent.";
            } else {
                $error = "Invalid email address.";
            }
        }
        require __DIR__ . '/../views/auth/forgot.php';
    }

    public function reset() {
        $error = '';
        $token = $_GET['token'] ?? null;
        $userModel = new User();

        $user = $userModel->verifyResetToken($token);

        if (!$user) {
            die("This reset link is invalid or has expired.");
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->checkCsrf();
            $password = $_POST['password'];
            $confirm = $_POST['password_confirm'];

            if ($password !== $confirm) {
                $error = "Passwords do not match.";
            } elseif (strlen($password) < 8 || !preg_match("/[A-Z]/", $password) || !preg_match("/[0-9]/", $password)) {
                $error = "Password must be at least 8 characters with one uppercase letter and one number.";
            } else {
                if ($userModel->resetPassword($token, $password)) {
                    header('Location: /login?msg=password_reset');
                    exit;
                } else {
                    $error = "An error occurred. Please try again.";
                }
            }
        }
        require __DIR__ . '/../views/auth/reset.php';
    }

    public function profile() {
        if (!isset($_SESSION['user_id'])) { header('Location: /login'); exit; }
       
        $userModel = new User();
        $error = '';
        $success = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->checkCsrf();
           
            $newUsername = trim($_POST['username']);
            $newEmail = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
            $newPass = !empty($_POST['password']) ? $_POST['password'] : null;
            $notificationActive = isset($_POST['notification_active']) ? 1 : 0;

            if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
                $processor = new ImageProcessor();
                $filename = $processor->uploadProfilePicture($_FILES['avatar']);
                
                if ($filename) {
                    $userModel->updateAvatar($_SESSION['user_id'], $filename);
                    $_SESSION['profile_pic'] = $filename;
                    $success .= "Profile picture updated. ";
                } else {
                    $error .= "Image upload failed (invalid format or file too large). ";
                }
            }

            if (!$newUsername || !$newEmail) {
                $error .= "Required fields are missing.";
            } elseif ($newPass && (strlen($newPass) < 8 || !preg_match("/[A-Z]/", $newPass) || !preg_match("/[0-9]/", $newPass))) {
                $error .= "New password must be at least 8 characters with one uppercase letter and one number.";
            } else {
                $userModel->updateNotification($_SESSION['user_id'], $notificationActive);
                $res = $userModel->update($_SESSION['user_id'], $newUsername, $newEmail, $newPass);
                
                if ($res === "EXISTS") {
                    $error .= "This username or email is already taken.";
                } elseif ($res) {
                    $success .= "Profile updated successfully!";
                    $_SESSION['username'] = $newUsername;
                } else {
                    $error .= "An error occurred while updating your profile.";
                }
            }
        }

        $user = $userModel->getById($_SESSION['user_id']);
        require __DIR__ . '/../views/auth/profile.php';
    }
}
?>