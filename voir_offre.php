<?php
$titre = "Détails de l'offre";
require_once "header.php";
require_once "config.php";




// Récupérer l'ID de l'offre depuis l'URL
$job_id = isset($_GET['job_id']) ? $_GET['job_id'] : null;
if ($job_id === null) {
    header("Location: categories.php");
    exit;
}

// Récupérer les détails de l'offre d'emploi
$query = "SELECT jobs.*, categories.name AS category_name 
          FROM jobs 
          LEFT JOIN categories ON jobs.category_id = categories.id 
          WHERE jobs.id = ?";
$stmt = $conn->prepare($query);
$stmt->execute([$job_id]);
$job = $stmt->fetch();

// Si l'offre n'existe pas
if (!$job) {
    header("Location: categories.php");
    exit;
}

// Vérifier si le candidat a déjà postulé à cette offre
// Récupérer l'ID de l'utilisateur connecté (par exemple depuis la session)
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id']; // Assurez-vous que l'utilisateur est connecté

    // Préparer la requête SQL
    $req = $conn->prepare('
SELECT users.id AS user_id, users.name, users.email, candidates.id AS candidate_id, candidates.phone, candidates.skills, candidates.experience
FROM users
LEFT JOIN candidates ON users.id = candidates.user_id
WHERE users.id = :user_id
');

    // Lier la valeur du paramètre :user_id
    $req->bindValue(':user_id', $user_id, PDO::PARAM_INT);

    // Exécuter la requête
    $req->execute();

    // Récupérer les candidates
    $candidate = $req->fetch(PDO::FETCH_ASSOC);


    $query_check = "SELECT COUNT(*) FROM applications WHERE candidate_id = ? AND job_id = ?";
    $stmt_check = $conn->prepare($query_check);
    $stmt_check->execute([$candidate['candidate_id'], $job_id]);
    $has_applied = $stmt_check->fetchColumn() > 0;

    // Récupérer les offres similaires
    $query_similar = "SELECT jobs.*, categories.name as categorie 
    FROM jobs 
    LEFT JOIN categories ON categories.id = jobs.category_id 
    WHERE categories.id = ? LIMIT 5;";

    $stmt_similar = $conn->prepare($query_similar);
    $stmt_similar->execute([$job['category_id']]);
    $similar_jobs = $stmt_similar->fetchAll();
}

?>

<div class="container mt-5">

    <h1 class="text-center mb-3"><?= $lang[$current_lang]['Details de l\'offre'] ?></h1>
    <!-- Message si le candidat a déjà postulé -->
    <?php if (@$has_applied): ?>
        <div class="alert alert-warning" role="alert">
            <strong><?= $lang[$current_lang]['Vous avez déjà candidaté à cette offre'] ?>.</strong><?= $lang[$current_lang]['Voir votre'] ?> <a href="espace_candidate.php" class="alert-link"><?= $lang[$current_lang]['espace candidat'] ?></a>.
        </div>
    <?php endif; ?>

    <!-- Détails de l'offre d'emploi -->
    <div class="card">
        <div class="card-header bg-primary text-white">

            <h3 class="card-title"><?= $lang[$current_lang]['Titre de l\'offre'] ?> : <?php echo htmlspecialchars($job['title']); ?></h3>
            <span class="badge badge-info"><?= $lang[$current_lang]['Catégorie'] ?> : <?php echo htmlspecialchars($job['category_name']); ?></span>
        </div>
        <div class="card-body">
            <h5 class="card-title"><?= $lang[$current_lang]['Description du poste'] ?></h5>
            <p class="card-text"><?php echo nl2br(htmlspecialchars($job['description'])); ?></p>
            <p><strong><?= $lang[$current_lang]['Lieu'] ?> :</strong> <?php echo htmlspecialchars($job['location']); ?></p>
            <p><strong><?= $lang[$current_lang]['Publiée le'] ?> :</strong> <?php echo date('d M Y', strtotime($job['created_at'])); ?></p>

            <a href="candidature.php?job_id=<?= $job['id'] ?>" class="btn btn-sm btn-warning text-dark"><?= $lang[$current_lang]['Postuler'] ?></a>


        </div>
    </div>

    <!-- Offres similaires -->
    <div class="mt-4">
        <h4><?= $lang[$current_lang]['Offres similaires'] ?></h4>
        <div class="row">
            <?php if (empty($similar_jobs)): ?>
                <div class="alert alert-info"><?= $lang[$current_lang]['Aucune offre similaire trouvée'] ?>.</div>
            <?php else: ?>
                <?php foreach ($similar_jobs as $job): ?>
                    <div class="col-md-6 mb-4">
                        <div class="card my-3 mx-2 border border-dark">
                            <div class="card-body ">
                                <a href="voir_offre.php?job_id=<?= $job['id']; ?>">

                                    <h5 class="card-title"><?= htmlspecialchars($job['title']); ?></h5>
                                </a>
                                <div class="d-flex justify-content-between">
                                    <span class="badge bg-info mb-2"><?= htmlspecialchars($job['categorie']); ?></span>
                                    <span class="badge bg-primary mb-2 fs-7"><?= $lang[$current_lang]['Type d\'emplois'] ?> : <?= htmlspecialchars($job['job_type']); ?></span>
                                </div>
                                <h6 class="card-subtitle mb-2 text-muted"><?= $lang[$current_lang]['Publiée le'] ?> <?= htmlspecialchars(date("d/m/Y", strtotime($job['created_at']))); ?></h6>

                                <p class="card-text"><?= htmlspecialchars(substr($job['description'], 0, 100)) . '...'; ?></p>
                                <a href="candidature.php?job_id=<?= $job['id'] ?>" class="btn btn-sm btn-warning text-dark"><?= $lang[$current_lang]['Postuler'] ?></a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif ?>
        </div>
    </div>
</div>


<?php require_once "footer.php"; ?>