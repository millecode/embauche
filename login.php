<?php
$titre = "Connexion";
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



$success = "";
$error = "";

// Vérifier si le lien contient les paramètres "code" et "token"
if (isset($_GET['code']) && isset($_GET['token'])) {
    $verification_code = htmlspecialchars($_GET['code']);
    $security_token = htmlspecialchars($_GET['token']);

    try {
        // Vérifier si un utilisateur correspond au code et au token
        $stmt = $conn->prepare("
            SELECT id, email_verified 
            FROM users 
            WHERE verification_code = :code 
            AND security_token = :token
        ");
        $stmt->execute([
            ':code' => $verification_code,
            ':token' => $security_token,
        ]);

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            if ($user['email_verified']) {
                // Si l'email est déjà vérifié
                $error = $lang[$current_lang]['Votre e-mail a déjà été confirmé. Vous pouvez maintenant vous connecter'] . ".";
            } else {
                // Mettre à jour la colonne email_verified pour l'utilisateur
                $update_stmt = $conn->prepare("
                    UPDATE users 
                    SET email_verified = true 
                    WHERE id = :id
                ");
                $update_stmt->execute([':id' => $user['id']]);

                $success = $lang[$current_lang]['Votre email a été confirmé avec succès. Vous pouvez maintenant vous connecter'] . '.';
            }
        } else {
            $error = $lang[$current_lang]['Lien invalide ou utilisateur introuvable. Veuillez vérifier votre email'] . ".";
        }
    } catch (PDOException $e) {
        $error = $lang[$current_lang]["Erreur lors de la confirmation de l'email"] . ": " . htmlspecialchars($e->getMessage());
    }
}



// Vérification du formulaire de connexion
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = htmlspecialchars($_POST['email']);
    $password = $_POST['password'];

    // Préparer la requête pour récupérer l'utilisateur par son email
    $stmt = $conn->prepare("SELECT * FROM Users WHERE email = :email");
    $stmt->execute([':email' => $email]);
    $user = $stmt->fetch();

    // Vérification si l'utilisateur existe et si le mot de passe est correct
    if ($user && password_verify($password, $user['password'])) {
        if ($user['status']) {
            // Vérification si l'email est vérifié
            if ($user['email_verified'] == 1) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['nom'] = $user['name'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['role'] = $user['role'];

                // Redirection vers la page d'accueil ou dashboard
                if ($_SESSION['role'] == 'admin') {
                    header("Location: espace_admin.php");
                } elseif ($_SESSION['role'] == 'candidate') {
                    header("Location: espace_candidate.php");
                } elseif ($_SESSION['role'] == 'recruiter') {
                    header("Location: espace_recruiter.php");
                }
                exit();
            } else {
                $error = $lang[$current_lang]["Veuillez confirmer votre email avant de vous connecter"] . ".";
            }
        } else {
            $error = $lang[$current_lang]["Votre compte à été désactiver"] . ".";
        }
    } else {
        $error = $lang[$current_lang]["Email ou mot de passe incorrect"] . ".";
    }
}
?>


<main>
    <section class="py-5 py-lg-8">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 offset-lg-2 col-md-12 col-12">
                    <div class="text-center">
                        <h1><?= $lang[$current_lang]['Connexion'] ?></h1>
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
                    <?php if (isset($_SESSION['success_nouveau_passe'])): ?>

                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <?= htmlspecialchars($_SESSION['success_nouveau_passe']); ?>
                            <?php unset($_SESSION['success_nouveau_passe']) ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif  ?>


                    <?php if (isset($_SESSION['error'])) : ?>
                        <div class="alert alert-danger">
                            <?= htmlspecialchars($_SESSION['error']); ?>
                        </div>
                    <?php endif; ?>
                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php elseif ($success): ?>
                        <div class="alert alert-success"><?php echo $success; ?></div>
                    <?php endif; ?>
                    <div class="card shadow-sm mb-3">
                        <div class="card-body">
                            <form class="needs-validation mb-6" method="post" action="login.php">
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email :</label>
                                    <input type="email" class="form-control" id="email" name="email" required>
                                </div>
                                <div class="mb-3">
                                    <label for="password" class="form-label"><?= $lang[$current_lang]['Mot de passe'] ?>:</label>
                                    <div class="password-field position-relative">
                                        <input type="password" class="form-control fakePassword" id="password" name="password" required="">
                                    </div>
                                </div>


                                <div class="d-grid">
                                    <button class="btn btn-warning" type="submit" name="submit"><?= $lang[$current_lang]['Se connecter'] ?></button>
                                </div>
                            </form>

                            <p><?= $lang[$current_lang]['Vous avez oublié votre mot de passe'] ?> ? <a href="recuperation_password.php"><?= $lang[$current_lang]['Récuperer votre compte'] ?></a></p>

                        </div>
                    </div>

                    <span>
                        <?= $lang[$current_lang]["Vous n'avez pas un compte"] ?> ?
                        <a href="register.php" class="text-primary"><?= $lang[$current_lang]["S'inscrire"] ?>.</a>
                    </span>
                </div>
            </div>

        </div>
    </section>
    <!--Sign up end-->

</main>

<?php require_once "footer.php"; ?>