<?php
ob_start();
$titre = "Nouveau mot de passe";
require_once "header.php";
require_once "config.php";

// Vérifier si l'utilisateur est déjà connecté
if (isset($_SESSION['user_id'])) {
    // Détruire la session
    session_unset(); // Supprime toutes les variables de session
    session_destroy(); // Détruit la session
    // Rediriger vers la page de connexion
    header("Location: login.php?message=deconnected");
    exit();
}

// Fonction pour valider le format du token
function validate_token($token)
{
    return preg_match('/^[a-f0-9]{64}$/', $token);
}

// Vérifier si un token est passé en GET
if (!isset($_GET['token']) || !validate_token($_GET['token'])) {
    $_SESSION['error'] = "Lien invalide ou corrompu.";
    header("Location: login.php");
    exit;
}

$error = '';
$success = '';


$token = $_GET['token'];

// Vérifier si le token existe dans la base
try {
    $stmt = $conn->prepare("
        SELECT id, email 
        FROM users 
        WHERE reset_token = :token 
    ");
    $stmt->execute(['token' => $token]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        $error = $lang[$current_lang]['Lien de réinitialisation expiré ou invalide'] . ".";
        header("Location: login.php");
        exit;
    }

    // Si le formulaire de réinitialisation est soumis
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];

        // Vérifier si les mots de passe correspondent
        if ($new_password !== $confirm_password) {
            $error = $lang[$current_lang]['Les mots de passe et sa confirmation ne sont pas identique'] . ".";
        } else {
            // Hacher le nouveau mot de passe
            $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);

            // Mettre à jour le mot de passe dans la base
            $update_stmt = $conn->prepare("
                UPDATE users 
                SET password = :password, reset_token = NULL, reset_token_expiry = NULL 
                WHERE id = :id
            ");
            $update_stmt->execute([
                'password' => $hashed_password,
                'id' => $user['id']
            ]);

            $_SESSION['success_nouveau_passe'] = $lang[$current_lang]['Votre mot de passe a été réinitialisé avec succès. Vous pouvez maintenant vous connecter'] . ".";
            header("Location: login.php");
            exit;
        }
    }
} catch (PDOException $e) {
    $_SESSION['error'] = "Erreur : " . htmlspecialchars($e->getMessage());
    header("Location: login.php");
    exit;
}


?>


<main>
    <section class="py-5 py-lg-8">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 offset-lg-2 col-md-12 col-12">
                    <div class="text-center">
                        <h1><?= $lang[$current_lang]['Nouveau mot de passe'] ?></h1>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!--Pageheader end-->


    <!--Sign up start-->
    <section>
        <div class="container">
            <div class="row justify-content-center mb-6">
                <div class="col-xl-6 col-lg-8 col-md-8 col-12">
                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php elseif ($success): ?>
                        <div class="alert alert-success"><?php echo $success; ?></div>
                    <?php endif; ?>
                    <div class="card shadow-sm mb-3">
                        <div class="card-body">
                            <form method="POST" action="">
                                <div class="mb-3">
                                    <label for="new_password" class="form-label"><?= $lang[$current_lang]['Nouveau mot de passe'] ?></label>
                                    <input type="password" class="form-control" id="new_password" name="new_password" required>
                                </div>
                                <div class="mb-3">
                                    <label for="confirm_password" class="form-label"><?= $lang[$current_lang]['Confirmer le mot de passe'] ?></label>
                                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                                </div>
                                <button type="submit" class="btn btn-primary w-100"><?= $lang[$current_lang]['Réinitialiser le mot de passe'] ?></button>
                            </form>

                        </div>
                    </div>
                </div>
            </div>

        </div>
    </section>
    <!--Sign up end-->

</main>

<?php require_once "footer.php"; ?>