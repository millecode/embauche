<?php
$titre = "Candidature";
require_once "header.php";
require_once "config.php";



// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    // Si l'utilisateur n'est pas connecté, rediriger vers la page de connexion
    header("Location: login.php");
    exit(); // Arrêter l'exécution du script
}

// Vérifier le type d'utilisateur pour restreindre l'accès à cette page
if (isset($_SESSION['role']) && $_SESSION['role'] !== 'candidate') {
    // Si l'utilisateur n'est pas candidat, il sera rediriger vers la page login
    header("Location: login.php");
    exit();
}
$message = "";
$error = "";
$job_id = isset($_GET['job_id']) ? intval($_GET['job_id']) : null;

if (!$job_id) {
    // Si aucun job_id n'est passé, rediriger
    header('Location:index.php');
}

//Le job selectionner
$st = "SELECT jobs.title,jobs.job_type,jobs.created_at,categories.name as categorie FROM jobs left join categories ON categories.id = jobs.category_id WHERE jobs.id = :id";
$re = $conn->prepare($st);
$re->execute([':id' => $job_id]);
$jobb = $re->fetch(PDO::FETCH_ASSOC);


$user_id = $_SESSION['user_id'];
// Vérification si l'utilisateur est un candidat
$checkCandidateQuery = "SELECT id FROM candidates WHERE user_id = :user_id";
$stmt = $conn->prepare($checkCandidateQuery);
$stmt->bindValue(':user_id', $user_id);
$stmt->execute();
$candidate = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$candidate) {
    // L'utilisateur n'est pas un candidat
    $error = '<div class="alert alert-danger">Vous devez être inscrit en tant que candidat pour postuler à un job.</div>';
    $job_id = null; // Empêche l'affichage du formulaire
} else {
    $candidate_id = $candidate['id']; // ID du candidat à utiliser pour la candidature
}



// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $candidate_id = isset($_POST['candidate_id']) ? intval($_POST['candidate_id']) : null; // Peut être obtenu via la session
    $years_experience = isset($_POST['years_experience']) ? intval($_POST['years_experience']) : 0;
    $cv_file = $_FILES['cv_file'] ?? null;
    $cover_letter_file = $_FILES['cover_letter_file'] ?? null;

    // Vérification des champs obligatoires
    if (empty($job_id) || empty($candidate_id) || empty($years_experience) || empty($cv_file['name']) || empty($cover_letter_file['name'])) {
        $message = '<div class="alert alert-danger">Tous les champs sont obligatoires.</div>';
    } else {

        // Vérification si le candidat a déjà postulé
        $checkApplicationQuery = "SELECT id FROM applications WHERE job_id = :job_id AND candidate_id = :candidate_id";
        $stmt = $conn->prepare($checkApplicationQuery);
        $stmt->execute([
            ':job_id' => $job_id,
            ':candidate_id' => $candidate_id,
        ]);
        $existingApplication = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($existingApplication) {
            $message = '<div class="alert alert-danger">Vous avez déjà postulé pour ce job. Consultez votre <a href="espace_candidate.php"> espace candidat</a>.</div>';
        } else {


            // Vérification de la taille des fichiers
            $maxSize = 100 * 1024; // 100 Ko en octets
            if ($cv_file['size'] > $maxSize) {
                $error = '<div class="alert alert-danger">Le fichier CV dépasse la taille maximale autorisée de 100 Ko.</div>';
            } elseif ($cover_letter_file['size'] > $maxSize) {
                $error = '<div class="alert alert-danger">Le fichier Lettre de motivation dépasse la taille maximale autorisée de 100 Ko.</div>';
            } else {
                // Gestion de l'upload des fichiers
                $cv_file_path = 'uploads/cvs/' . uniqid() . '_' . basename($cv_file['name']);
                $cover_letter_file_path = 'uploads/letters/' . uniqid() . '_' . basename($cover_letter_file['name']);

                if (move_uploaded_file($cv_file['tmp_name'], $cv_file_path) && move_uploaded_file($cover_letter_file['tmp_name'], $cover_letter_file_path)) {
                    try {
                        // Insérer la candidature
                        $query = "INSERT INTO applications (job_id, candidate_id, cv_file, cover_letter_file, years_experience) 
                          VALUES (:job_id, :candidate_id, :cv_file, :cover_letter_file, :years_experience)";
                        $stmt = $conn->prepare($query);
                        $stmt->execute([
                            ':job_id' => $job_id,
                            ':candidate_id' => $candidate_id,
                            ':cv_file' => $cv_file_path,
                            ':cover_letter_file' => $cover_letter_file_path,
                            ':years_experience' => $years_experience,
                        ]);

                        $message = '<div class="alert alert-success">Votre candidature a été enregistrée avec succès.</div>';
                    } catch (PDOException $e) {
                        $message = '<div class="alert alert-danger">Erreur : ' . htmlspecialchars($e->getMessage()) . '</div>';
                    }
                } else {
                    $message = '<div class="alert alert-danger">Erreur lors de l\'upload des fichiers.</div>';
                }
            }
        }
    }
}
?>


<main>
    <section class="py-5">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 offset-lg-2 col-md-12 col-12">
                    <div class="text-center">
                        <h1>Candidature</h1>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!--Pageheader end-->



    <div class="container">
        <div class="row justify-content-center mb-6">
            <div class="col-12 col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="text-center text-danger">L'emplois selectionner</h5>
                    </div>
                    <div class="card-body">

                        <p> <Strong>Titre de l'emplois :</Strong> <?= $jobb['title'] ?></p>

                        <p>
                            <Strong>Categorie :</Strong> <?= $jobb['categorie'] ?>
                        </p>

                        <p>
                            <Strong>Type de jobs :</Strong> <?= $jobb['job_type'] ?>
                        </p>

                        <p>
                            <Strong>Date de mise en ligne :</Strong> <?= $jobb['created_at'] ?>
                        </p>

                    </div>
                </div>
            </div>


            <div class="col-12 col-md-6">
                <?= $message; ?>
                <div class="card shadow-sm mb-3">
                    <div class="card-header">Candidater en remplissant le formulaire ci-dessous</div>
                    <div class="card-body">
                        <?php if (!empty($error)): ?>
                            <?= $error ?>
                        <?php endif; ?>
                        <form method="post" enctype="multipart/form-data" class="mt-4">
                            <div class="mb-3">
                                <input type="hidden" class="form-control" id="job_id" name="job_id" value="<?= htmlspecialchars($job_id); ?>">
                            </div>

                            <div class="mb-3">

                                <input type="hidden" class="form-control" id="candidate_id" name="candidate_id" value="<?= $candidate_id ?>" required>
                            </div>

                            <div class="mb-3">
                                <label for="cv_file" class="form-label">Téléchargez votre CV</label>
                                <input type="file" class="form-control" id="cv_file" name="cv_file" accept=".pdf,.doc,.docx" required>
                            </div>

                            <div class="mb-3">
                                <label for="cover_letter_file" class="form-label">Téléchargez votre Lettre de Motivation</label>
                                <input type="file" class="form-control" id="cover_letter_file" name="cover_letter_file" accept=".pdf,.doc,.docx" required>
                            </div>

                            <div class="mb-3">
                                <label for="years_experience" class="form-label">Combien d'années d'Expérience pour ce poste</label>
                                <input type="number" class="form-control" id="years_experience" name="years_experience" min="0" required>
                            </div>

                            <button type="submit" class="btn btn-primary">Postuler</button>
                            <a href="index.php" class="btn btn-secondary">Retour aux Jobs</a>
                        </form>



                    </div>
                </div>


            </div>
        </div>

    </div>


</main>

<?php require_once "footer.php"; ?>