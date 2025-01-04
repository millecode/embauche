<?php
// Include PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require "PHPMailer/src/Exception.php";
require "PHPMailer/src/PHPMailer.php";
require "PHPMailer/src/SMTP.php";

$titre = "Modification du profil";
require_once "header.php";
require_once "config.php";


// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Récupérer les informations actuelles de l'utilisateur
$query = $conn->prepare("SELECT name, email,role FROM users WHERE id = :id");
$query->execute([':id' => $user_id]);
$user = $query->fetch(PDO::FETCH_ASSOC);

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = htmlspecialchars(trim($_POST['name']));
    $email = htmlspecialchars(trim($_POST['email']));
    $role = htmlspecialchars(trim($_POST['role']));
    $verification_code = bin2hex(random_bytes(16));
    $security_token = bin2hex(random_bytes(18));

    // Mettre à jour les informations dans la base de données
    $update_query = $conn->prepare("UPDATE users SET name = :name, email = :email, role = :role, email_verified=:mail, verification_code=:code, security_token=:token WHERE id = :id");
    $update_query->execute([':name' => $name, ':email' => $email, ':id' => $user_id, ':role' => $role, ':mail' => false, ':code' => $verification_code, ':token' => $security_token]);

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
    $mail->Subject = 'Modification du profile.';
    $mail->Body    = "
         <p>Bonjour, $name.<p>
         <p>Vos informations personnelles ont été modifiées avec succès. Veuillez confirmer votre email en cliquant sur le lien ci-dessous:</p>
         <a href='http://localhost/projet_embauche/login.php?code=$verification_code&token=$security_token'>Confirmer votre email</a>
     ";

    if ($mail->send()) {
        $success_message = 'Vos informations ont été mises à jour avec succès, et un e-mail de confirmation a été envoyé.';
    } else {
        $error_message = 'Mise à jour réussie, mais échec de l\'envoi de l\'e-mail de confirmation.';
    }
}
?>


<main>
    <section class="py-5 py-lg-8">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 offset-lg-2 col-md-12 col-12">
                    <div class="text-center">
                        <h1>Modification du profile</h1>
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

                    <div class="card shadow-sm mb-3">
                        <div class="card-body">
                            <form method="post">
                                <div class="mb-3">
                                    <label for="name" class="form-label">Nom</label>
                                    <input type="text" class="form-control" id="name" name="name" value="<?= htmlspecialchars($user['name']) ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label for="email" class="form-label">E-mail</label>
                                    <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label for="role">Choisissez votre rôle :</label>
                                    <select class="form-select form-control" name="role" id="role">
                                        <option value="recruiter" <?php if ($user['role'] == "recruiter") echo "selected"; ?>>Recruiter</option>
                                        <option value="candidate" <?php if ($user['role'] == "candidate") echo "selected"; ?>>Candidate</option>
                                        <?php if ($_SESSION['role'] == "admin"): ?>
                                            <option value="admin" <?php if ($user['role'] == "admin") echo "selected"; ?>>Admin</option>
                                        <?php endif; ?>
                                    </select>
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