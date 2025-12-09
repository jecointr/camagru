<?php
require_once __DIR__ . '/../models/User.php';

class AuthController {
    
    public function register() {
        $error = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = htmlspecialchars($_POST['username']); // XSS Protection
            $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
            $password = $_POST['password'];

            // Validation simple du mot de passe (Complexité requise par le sujet)
            if (strlen($password) < 8 || !preg_match("/[A-Z]/", $password) || !preg_match("/[0-9]/", $password)) {
                $error = "Le mot de passe doit contenir 8 caractères, une majuscule et un chiffre.";
            } elseif ($username && $email && $password) {
                $userModel = new User();
                
                if ($userModel->userExists($username, $email)) {
                    $error = "Nom d'utilisateur ou email déjà utilisé.";
                } else {
                    $token = bin2hex(random_bytes(32)); // Token unique
                    if ($userModel->create($username, $email, $password, $token)) {
                        // Envoi du mail (Simulation pour l'instant)
                        $link = "http://localhost:8080/verify?token=$token";
                        $subject = "Confirmez votre compte Camagru";
                        $message = "Cliquez ici : " . $link;
                        // mail($email, $subject, $message); // Décommenter si SMTP configuré
                        
                        // Pour le dev, on l'affiche ou on le log
                        file_put_contents('php://stderr', "Lien validation: $link\n"); 
                        
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
}
?>
