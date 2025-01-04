<?php
$titre = "Contactez-nous";
require_once "header.php";
require_once "config.php"; // Connexion à la base de données


$successMessage = '';
$errorMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $nom = trim($_POST['nom']);
  $phone = trim($_POST['phone']);
  $sujet = trim($_POST['sujet']);
  $email = trim($_POST['email']);
  $message = trim($_POST['message']);

  // Validation des champs
  if (empty($nom) || empty($email) || empty($phone) || empty($sujet) || empty($message)) {
    $errorMessage = $lang[$current_lang]['Tous les champs sont obligatoire'] . '.';
  } elseif (!ctype_digit($phone)) {
    $errorMessage = $lang[$current_lang]['Le numéro de téléphone est invalide'] . '.';
  } else {
    try {
      $stmt = $conn->prepare("INSERT INTO contacts (nom,email, phone, sujet, message) VALUES (?,?, ?, ?, ?)");
      $stmt->execute([$nom, $email, $phone, $sujet, $message]);
      $successMessage = $lang[$current_lang]['Votre message a été envoyé avec succès. Nous vous répondrons dans les plus brefs délais'] . '.';
    } catch (PDOException $e) {
      $errorMessage = $lang[$current_lang]["Erreur lors de l'envoi du message"] . ': ' . $e->getMessage();
    }
  }
}
?>

<div class="container mt-5">
  <h2 class="text-center"><?= $lang[$current_lang]['Contactez-nous'] ?></h2>
  <p class="text-center"><?= $lang[$current_lang]['Si vous avez des questions ou des préoccupations, veuillez remplir le formulaire ci-dessous ou nous contacter via nos différentes coordonnées'] ?>.</p>

  <div class="row">
    <div class="col-md-4">
      <h5 class="text-center"><?= $lang[$current_lang]['Nos différentes coordonnées'] ?></h5>
      <div class="row mt-4">
        <div class="card bg-light border border-dark text-center">
          <div class="card-body">
            <p><strong><?= $lang[$current_lang]['Adresse'] ?> :</strong> </p>
            <p>Djibouti, Djibouti ville</p>
          </div>
        </div>

        <div class="card mt-2 bg-light border border-dark text-center">
          <div class="card-body">
            <p><strong>Email :</strong> </p>
            <p><a href="mailto:embauche.projet.i.h@gmail.com">embauche.projet.i.h@gmail.com</a></p>
          </div>
        </div>

        <div class="card mt-2 bg-light border border-dark text-center">
          <div class="card-body">
            <p><strong><?= $lang[$current_lang]['Téléphone'] ?> :</strong> </p>
            <p><a href="tel:0025377277129">0025377277129</a></p>
          </div>
        </div>

      </div>
    </div>
    <div class="col-md-8">
      <!-- Affichage des messages d'alerte -->
      <?php if (!empty($successMessage)): ?>
        <div class="alert alert-success"><?= $successMessage; ?></div>
      <?php endif; ?>

      <?php if (!empty($errorMessage)): ?>
        <div class="alert alert-danger"><?= $errorMessage; ?></div>
      <?php endif; ?>

      <!-- Formulaire de contact -->
      <form method="POST" action="contact.php" class="mt-4 border border-dark p-3 rounded">
        <div class="row">
          <!-- Nom -->
          <div class="col-6 mb-3">
            <label for="nom" class="form-label"><?= $lang[$current_lang]['Nom'] ?></label>
            <input type="text" name="nom" id="nom" class="form-control border border-dark" placeholder="<?= $lang[$current_lang]['Entrez votre nom'] ?>" required>
          </div>

          <!-- Email -->
          <div class="col-6 mb-3">
            <label for="nom" class="form-label">Email</label>
            <input type="email" name="email" id="email" class="form-control border border-dark" placeholder="<?= $lang[$current_lang]['Entrez votre email'] ?>" required>
          </div>

          <!-- Téléphone et Sujet -->
          <div class="col-md-6 mb-3">
            <label for="phone" class="form-label"><?= $lang[$current_lang]['Téléphone'] ?></label>
            <input type="text" name="phone" id="phone" class="form-control border border-dark" placeholder="<?= $lang[$current_lang]['Entrez votre numéro de téléphone'] ?>" required>
          </div>
          <div class="col-md-6 mb-3">
            <label for="sujet" class="form-label"><?= $lang[$current_lang]['Sujet'] ?></label>
            <input type="text" name="sujet" id="sujet" class="form-control border border-dark" placeholder="<?= $lang[$current_lang]['Entrez le sujet'] ?>" required>
          </div>

          <!-- Message -->
          <div class="col-12 mb-3">
            <label for="message" class="form-label">Message</label>
            <textarea name="message" id="message" class="form-control border border-dark" rows="5" placeholder="<?= $lang[$current_lang]['Ecrivez votre message ici'] ?>" required></textarea>
          </div>

          <!-- Bouton d'envoi -->
          <div class="col-12 text-center">
            <button type="submit" class="btn btn-primary"><?= $lang[$current_lang]['Envoyer le message'] ?></button>
          </div>
        </div>
      </form>
    </div>
  </div>
</div>


<?php require_once "footer.php"; ?>