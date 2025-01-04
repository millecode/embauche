<?php
$titre = "Profile";
require_once "header.php";
require_once "config.php";

// Vérifier si l'utilisateur est déjà connecté
if (!isset($_SESSION['user_id'])) {
    // Détruire la session
    session_unset(); // Supprime toutes les variables de session
    session_destroy(); // Détruit la session
    // Rediriger vers la page de connexion
    header("Location: login.php?message=deconnected");
    exit();
}



// Récupérer l'ID de l'utilisateur depuis la session
$user_id = $_SESSION['user_id'];

// Récupérer les informations de l'utilisateur à partir de la base de données
$query = $conn->prepare('
    SELECT users.name, users.email, users.role
    FROM users
    WHERE users.id = :user_id
');
$query->bindValue(':user_id', $user_id);
$query->execute();
$user_info = $query->fetch();
?>


<div class="container">
    <h1 class="text-center my-2">Profile</h1>
    <div class="row justify-content-center">
        <!-- Informations de l'utilisateur -->
        <div class="col-md-6 mt-2">
            <div class="card">
                <div class="card-header">
                    <h4>Mes Informations</h4>
                </div>
                <div class="card-body">
                    <h5><strong>Nom:</strong> <?php echo htmlspecialchars($user_info['name']); ?></h5>
                    <h5><strong>Email:</strong> <?php echo htmlspecialchars($user_info['email']); ?></h5>
                    <h5><strong>Fonction :</strong> <?php echo htmlspecialchars($user_info['role']); ?></h5>
                </div>
            </div>
        </div>

        <!-- Boutons pour modifier les informations -->
        <div class="col-md-6 mt-2">
            <div class="card">

                <div class="card-body">
                    <a href="modifier_profil.php" class="btn btn-primary btn-custom">Modifier le compte</a>
                    <a href="modifier_mot_de_passe.php" class="btn btn-warning btn-custom">Modifier le mot de passe</a>
                </div>
            </div>
        </div>
    </div>
</div>
















<?php require_once "footer.php"; ?>