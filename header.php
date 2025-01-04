<?php
session_start();
require 'lang.php'; // Charger les traductions
// Vérifiez si une langue est définie dans le formulaire
if (isset($_GET['lang'])) {
    $_SESSION['lang'] = $_GET['lang']; // Sauvegarder dans la session
}
// Par défaut, définir la langue sur "en"
if (!isset($_SESSION['lang'])) {
    $_SESSION['lang'] = 'fr';
}
$current_lang = $_SESSION['lang']; // Obtenir la langue actuelle
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link href="assets/css/colors.css" rel="stylesheet">

    <!-- Color modes -->
    <script src="assets/js/vendors/color-modes.js"></script>

    <!-- Libs CSS -->
    <link href="assets/libs/simplebar/dist/simplebar.min.css" rel="stylesheet">
    <link href="assets/libs/bootstrap-icons/font/bootstrap-icons.min.css" rel="stylesheet">

    <!-- Scroll Cue -->
    <link rel="stylesheet" href="assets/libs/scrollcue/scrollCue.css">

    <!-- Box icons -->
    <link rel="stylesheet" href="assets/fonts/css/boxicons.min.css">

    <!-- Theme CSS -->
    <link rel="stylesheet" href="assets/css/theme.min.css">
    <title><?= $titre ?></title>
</head>



<body>
    <!-- Navbar -->
    <header>
        <nav class="navbar navbar-expand-lg navbar-light w-100 bg-light text-light">
            <div class="container px-3">
                <a class="navbar-brand" href="index.php"><img src="images/LOGO.jpg" class="img-fluid rounded" width="40px" alt=""></a>
                <button class="navbar-toggler offcanvas-nav-btn" type="button">
                    <i class="bi bi-list"></i>
                </button>
                <div class="offcanvas offcanvas-start offcanvas-nav" style="width: 20rem">
                    <div class="offcanvas-header">
                        <a href="index.php" class="text-inverse"><img src="images/LOGO.jpg" class="img-fluid rounded" width="60px" alt=""></a>
                        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                    </div>
                    <div class="offcanvas-body pt-0 align-items-center">
                        <ul class="navbar-nav mx-auto align-items-lg-center">
                            <li class="nav-item  my-2">
                                <a class="nav-link text-dark fs-5" href="index.php"><?= $lang[$current_lang]['Accueil'] ?></a>
                            </li>

                            <li class="nav-item  my-2">
                                <a class="nav-link text-dark fs-5" href="categories.php"><?= $lang[$current_lang]['Liste des emplois'] ?></a></a>
                            </li>

                            <li class="nav-item  my-2">
                                <a class="nav-link text-dark fs-5" href="services.php"><?= $lang[$current_lang]['Services'] ?></a>
                            </li>

                            <li class="nav-item  my-2">
                                <a class="nav-link text-dark fs-5" href="propos.php"><?= $lang[$current_lang]['A propos'] ?></a>
                            </li>

                            <li class="nav-item  my-2">
                                <a class="nav-link text-dark fs-5" href="contact.php"><?= $lang[$current_lang]['Contactez-nous'] ?></a>
                            </li>



                            <?php if (isset($_SESSION['user_id'])): ?>

                                <li class="nav-item  my-2">
                                    <a class="nav-link text-dark fs-5" href="profile.php"><?= $lang[$current_lang]['Profile'] ?></a>
                                </li>
                            <?php endif ?>




                            <li class="nav-item  my-2">
                                <?php if (isset($_SESSION['user_id'])): ?>
                                    <?php if ($_SESSION['role'] == 'admin'): ?>
                                        <a class="btn btn-light" href="espace_admin.php"><?= $lang[$current_lang]['Espace membre'] ?></a>
                                    <?php endif ?>

                                    <?php if ($_SESSION['role'] == 'recruiter'): ?>
                                        <a class="btn btn-light" href="espace_recruiter.php"><?= $lang[$current_lang]['Espace membre'] ?></a>
                                    <?php endif ?>

                                    <?php if ($_SESSION['role'] == 'candidate'): ?>
                                        <a class="btn btn-light" href="espace_candidate.php"><?= $lang[$current_lang]['Espace membre'] ?></a>
                                    <?php endif ?>

                                <?php endif; ?>


                            </li>

                        </ul>
                        <div class="mt-3 mt-lg-0 d-flex align-items-center">
                            <?php if (isset($_SESSION['user_id'])): ?>
                                <a href="deconnexion.php" class="btn btn-danger mx-2 my-2"><?= $lang[$current_lang]['Deconnexion'] ?></a>
                            <?php else : ?>
                                <a href="login.php" class="btn btn-warning mx-2 my-2"><?= $lang[$current_lang]['Connexion'] ?></a>
                            <?php endif ?>
                            <form method="get" action="" class="d-inline">
                                <!-- Ajoute les paramètres existants -->
                                <?php
                                foreach ($_GET as $key => $value):
                                    if ($key != 'lang'): // Exclure le paramètre de langue existant
                                ?>
                                        <input type="hidden" name="<?php echo htmlspecialchars($key); ?>" value="<?php echo htmlspecialchars($value); ?>">
                                <?php
                                    endif;
                                endforeach;
                                ?>

                                <select name="lang" onchange="this.form.submit()" class="form-select form-select-sm w-auto">
                                    <option value="en" <?php echo ($_SESSION['lang'] == 'en') ? 'selected' : ''; ?>>🇬🇧 English</option>
                                    <option value="fr" <?php echo ($_SESSION['lang'] == 'fr') ? 'selected' : ''; ?>>🇫🇷 Français</option>
                                </select>
                            </form>

                        </div>
                    </div>
                </div>
            </div>
        </nav>
    </header>