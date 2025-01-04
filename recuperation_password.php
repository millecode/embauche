<?php
// Include PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require "PHPMailer/src/Exception.php";
require "PHPMailer/src/PHPMailer.php";
require "PHPMailer/src/SMTP.php";


$titre = "Récuperation du mot de passe";
require_once "header.php";
require_once "config.php";


// Variables for success and error messages
$success = "";
$error = "";


// Vérifier si l'utilisateur est déjà connecté
if (isset($_SESSION['user_id'])) {
    // Détruire la session
    session_unset(); // Supprime toutes les variables de session
    session_destroy(); // Détruit la session
    // Rediriger vers la page de connexion
    header("Location: login.php?message=deconnected");
    exit();
}



if ($_SERVER['REQUEST_METHOD'] === 'POST') {


    $email = trim($_POST['email']);

    // Vérifier si l'email existe dans la base de données
    $stmt = $conn->prepare("SELECT id, name, email FROM users WHERE email = :email");
    $stmt->execute(['email' => $email]);
    $user = $stmt->fetch();

    if ($user) {
        $token = bin2hex(random_bytes(32)); // Générer un token sécurisé
        $expiry = date('Y-m-d H:i:s', strtotime('+1 hour')); // Expiration dans 1h

        // Insérer le token dans la base de données
        $stmt = $conn->prepare("UPDATE users SET reset_token = :token, reset_token_expiry = :expiry WHERE id = :id");
        $stmt->execute(['token' => $token, 'expiry' => $expiry, 'id' => $user['id']]);

        $email = $user['email'];
        $name = $user['name'];
        // Send email with PHPMailer
        $mail = new PHPMailer(true);
        try {
            // Server settings
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com'; // Replace with your SMTP server
            $mail->SMTPAuth = true;
            $mail->Username = 'embauche.projet.i.h@gmail.com'; // Replace with your email
            $mail->Password = 'ylbf ftcs dpjj jgnx'; // Replace with your email password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            // Recipients
            $mail->setFrom('embauche.projet.i.h@gmail.com', 'Entreprise d\'embauche I&H.');
            $mail->addAddress($email, $name);

            // Content
            $mail->isHTML(true);
            $mail->Subject = 'Confirmer votre email.';
            $mail->Body    = "
                 <p>Bonjour, $name.<p>
                 <p>C'est avec plaisir de vous retrouver parmis nous. Veuillez confirmer votre email en cliquant sur le lien ci-dessous pour récupérer votre compte :</p>
                 <a href='http://localhost/projet_embauche/nouveau_password.php?token=$token'>Confirmer votre email</a>
             ";

            $mail->send();
        } catch (Exception $e) {
            $error = $lang[$current_lang]['email non envoyer'] . ". {$mail->ErrorInfo}";
        }

        $success = $lang[$current_lang]['Un lien de confirmation a été envoyé. Veuillez confirmer votre e-mail'] . '.';
    } else {
        $error = $lang[$current_lang]["l'email n'existe pas ou ne pas encore inscrit"] . ".";
    }
}
?>


<main>
    <section class="py-5 py-lg-8">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 offset-lg-2 col-md-12 col-12">
                    <div class="text-center">
                        <h1><?= $lang[$current_lang]["Récupération de votre compte"] ?></h1>
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
                            <form class="needs-validation mb-6" method="post">
                                <div class="mb-3">
                                    <label for="email" class="form-label"><?= $lang[$current_lang]["Votre email"] ?> :</label>
                                    <input type="email" class="form-control" id="email" name="email" required>
                                </div>
                                <div class="d-grid">
                                    <button class="btn btn-warning" type="submit" name="submit"><?= $lang[$current_lang]["Récuperer mon compte"] ?></button>
                                </div>
                            </form>



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