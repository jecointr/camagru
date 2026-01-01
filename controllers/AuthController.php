<?php
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/ImageProcessor.php';

class AuthController {

    private function checkCsrf() {
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            die("Erreur de sécurité (CSRF) : Session invalide ou tentative d'intrusion.");
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

            $username = htmlspecialchars($_POST['username']); 
            $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
            $password = $_POST['password'];

            if (strlen($password) < 8 || !preg_match("/[A-Z]/", $password) || !preg_match("/[0-9]/", $password)) {
                $error = "Le mot de passe doit contenir 8 caractères, une majuscule et un chiffre.";
            } elseif ($username && $email && $password) {
                $userModel = new User();
               
                if ($userModel->userExists($username, $email)) {
                    $error = "Nom d'utilisateur ou email déjà utilisé.";
                } else {
                    $token = bin2hex(random_bytes(32));
                    if ($userModel->create($username, $email, $password, $token)) {
                       
                        $link = "http://localhost:8080/verify?token=$token";
                        $subject = "Confirmez votre compte Camagru";
                        $message = "Bienvenue $username,\n\nCliquez sur ce lien pour activer votre compte :\n$link";
                        $headers = "From: no-reply@camagru.fr";

                        mail($email, $subject, $message, $headers);
                        
                        file_put_contents('php://stderr', "Email inscription envoyé à $email\n"); 
                       
                        header('Location: /login?msg=registered');
                        exit;
                    } else {
                        $error = "Erreur lors de l'inscription.";
                    }
                }
            } else {
                $error = "Données invalides.";
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
                $error = "Veuillez vérifier votre email avant de vous connecter.";
            } elseif ($result) {
                $_SESSION['user_id'] = $result['id'];
                $_SESSION['username'] = $result['username'];
                header('Location: /');
                exit;
            } else {
                $error = "Identifiants incorrects.";
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
                echo "Lien invalide ou expiré.";
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
                    $link = "http://localhost:8080/reset?token=$token";
                    $subject = "Reinitialisation de votre mot de passe";
                    $message = "Bonjour,\n\nPour changer votre mot de passe, cliquez ici :\n$link\n\nCe lien expire dans 1 heure.";
                    $headers = "From: no-reply@camagru.fr";

                    mail($email, $subject, $message, $headers);
                    file_put_contents('php://stderr', "Email reset envoyé à $email\nLink: $link\n");
                }
                $success = "Si cet email existe, un lien de réinitialisation a été envoyé.";
            } else {
                $error = "Email invalide.";
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
            die("Ce lien de réinitialisation est invalide ou a expiré.");
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->checkCsrf();
            $password = $_POST['password'];
            $confirm = $_POST['password_confirm'];

            if ($password !== $confirm) {
                $error = "Les mots de passe ne correspondent pas.";
            } elseif (strlen($password) < 8 || !preg_match("/[A-Z]/", $password) || !preg_match("/[0-9]/", $password)) {
                $error = "Le mot de passe doit contenir 8 caractères, une majuscule et un chiffre.";
            } else {
                if ($userModel->resetPassword($token, $password)) {
                    header('Location: /login?msg=password_reset');
                    exit;
                } else {
                    $error = "Une erreur est survenue.";
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
           
            $newUsername = htmlspecialchars($_POST['username']);
            $newEmail = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
            $newPass = !empty($_POST['password']) ? $_POST['password'] : null;

            if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
                $processor = new ImageProcessor();
                $filename = $processor->uploadProfilePicture($_FILES['avatar']);
                
                if ($filename) {
                    $userModel->updateAvatar($_SESSION['user_id'], $filename);
                    $_SESSION['profile_pic'] = $filename;
                    $success .= "Photo de profil mise à jour. ";
                } else {
                    $error .= "Erreur upload image (Format invalide ou trop lourd). ";
                }
            }

            if (!$newUsername || !$newEmail) {
                $error .= "Champs obligatoires manquants.";
            } elseif ($newPass && (strlen($newPass) < 8 || !preg_match("/[A-Z]/", $newPass) || !preg_match("/[0-9]/", $newPass))) {
                $error .= "Le nouveau mot de passe ne respecte pas les critères.";
            } else {
                $res = $userModel->update($_SESSION['user_id'], $newUsername, $newEmail, $newPass);
                
                if ($res === "EXISTS") {
                    $error .= "Ce pseudo ou email est déjà pris.";
                } elseif ($res) {
                    $success .= "Informations mises à jour !";
                    $_SESSION['username'] = $newUsername;
                } else {
                    $error .= "Erreur lors de la mise à jour.";
                }
            }
        }

        $user = $userModel->getById($_SESSION['user_id']);
        require __DIR__ . '/../views/auth/profile.php';
    }
}
?>