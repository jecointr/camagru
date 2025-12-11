<?php
require_once __DIR__ . '/../models/User.php';

class AuthController {

    // Méthode utilitaire pour vérifier le token
    private function checkCsrf() {
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            // On arrête tout si le token est invalide
            die("Erreur de sécurité (CSRF) : Session invalide ou tentative d'intrusion.");
        }
    }
    
    public function register() {
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
                        
                        // --- ENVOI DU MAIL ---
                        $link = "http://localhost:8080/verify?token=$token";
                        $subject = "Confirmez votre compte Camagru";
                        $message = "Bienvenue $username,\n\nCliquez sur ce lien pour activer votre compte :\n$link";
                        $headers = "From: no-reply@camagru.fr";

                        // 👇 ICI : J'ai enlevé les // pour activer l'envoi
                        mail($email, $subject, $message, $headers);
                        
                        // On garde le log au cas où MailHog bug, pour le debug
                        file_put_contents('php://stderr', "Email envoyé à $email avec le lien: $link\n"); 
                        
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

    public function profile() {
        if (!isset($_SESSION['user_id'])) { header('Location: /login'); exit; }
        
        $userModel = new User();
        $error = '';
        $success = '';

        // Traitement du formulaire
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->checkCsrf();
            
            $newUsername = htmlspecialchars($_POST['username']);
            $newEmail = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
            $newPass = !empty($_POST['password']) ? $_POST['password'] : null;

            if (!$newUsername || !$newEmail) {
                $error = "Champs obligatoires manquants.";
            } elseif ($newPass && (strlen($newPass) < 8 || !preg_match("/[A-Z]/", $newPass) || !preg_match("/[0-9]/", $newPass))) {
                $error = "Le nouveau mot de passe ne respecte pas les critères de sécurité.";
            } else {
                $res = $userModel->update($_SESSION['user_id'], $newUsername, $newEmail, $newPass);
                if ($res === "EXISTS") {
                    $error = "Ce nom d'utilisateur ou cet email est déjà pris.";
                } elseif ($res) {
                    $success = "Profil mis à jour avec succès !";
                    // Mise à jour de la session si le username a changé
                    $_SESSION['username'] = $newUsername;
                } else {
                    $error = "Erreur lors de la mise à jour.";
                }
            }
        }

        // Récupération des infos actuelles pour l'affichage
        $user = $userModel->getById($_SESSION['user_id']);
        require __DIR__ . '/../views/auth/profile.php';
    }
}
?>
