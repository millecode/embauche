<?php

// Include PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require "PHPMailer/src/Exception.php";
require "PHPMailer/src/PHPMailer.php";
require "PHPMailer/src/SMTP.php";

$titre = "Modifications du mot de passe";
require_once "header.php";
require_once "config.php";


// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}


$user_id = $_SESSION['user_id'];

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $old_password = $_POST['old_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    $verification_code = bin2hex(random_bytes(16));
    $security_token = bin2hex(random_bytes(18));


    // Récupérer l'ancien mot de passe dans la base
    $query = $conn->prepare("SELECT name, email, password FROM users WHERE id = :id");
    $query->execute([':id' => $user_id]);
    $user = $query->fetch(PDO::FETCH_ASSOC);
    $name = $user['name'];
    $email = $user['email'];

    // Vérifier l'ancien mot de passe
    if (!password_verify($old_password, $user['password'])) {
        $error_message = 'L\'ancien mot de passe est incorrect.';
    } elseif ($new_password !== $confirm_password) {
        $error_message = 'Les nouveaux mots de passe ne correspondent pas.';
    } else {
        // Mettre à jour le mot de passe
        $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);
        $update_query = $conn->prepare("UPDATE users SET password = :password, email_verified=:mail, verification_code=:code, security_token=:token WHERE id = :id");
        $update_query->execute([':password' => $hashed_password, ':id' => $user_id, ':mail' => false, ':code' => $verification_code, ':token' => $security_token]);

        // Envoie d'email 
        $mail = new PHPMailer(true);
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
        $mail->Subject = 'Modification de votre mot de passe.';
        $mail->Body    = "
         <p>Bonjour, $name<p>
         <p>Votre mot de passe a été modifié avec succès. Veuillez confirmer votre email en cliquant sur le lien ci-dessous:</p>
         <a href='http://localhost/projet_embauche/login.php?code=$verification_code&token=$security_token'>Confirmer votre email</a>
     ";

        if ($mail->send()) {
            $success_message = 'Votre mot de passe a été mis à jour avec succès, et un e-mail de confirmation a été envoyé.';
        } else {
            $error_message = 'Mise à jour réussie, mais échec de l\'envoi de l\'e-mail de confirmation.';
        }
    }
}
?>




<main>
    <section class="py-5 py-lg-8">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 offset-lg-2 col-md-12 col-12">
                    <div class="text-center">
                        <h1>Modification du mot de passe.</h1>
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
                    <?php if (isset($success_message)) {
                        echo '<div class="alert alert-success">' . $success_message . '</div>';
                    } ?>
                    <?php if (isset($error_message)) {
                        echo '<div class="alert alert-danger">' . $error_message . '</div>';
                    } ?>

                    <div class="card">
                        <div class="card-body">
                            <form method="post">
                                <div class="mb-3">
                                    <label for="old_password" class="form-label">Ancien mot de passe</label>
                                    <input type="password" class="form-control" id="old_password" name="old_password" required>
                                </div>
                                <div class="mb-3">
                                    <label for="new_password" class="form-label">Nouveau mot de passe</label>
                                    <input type="password" class="form-control" id="new_password" name="new_password" required>
                                </div>
                                <div class="mb-3">
                                    <label for="confirm_password" class="form-label">Confirmer le nouveau mot de passe</label>
                                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                                </div>
                                <button type="submit" class="btn btn-primary">Mettre à jour</button>
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