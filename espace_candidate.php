<?php
$titre = "Espace Candidat";
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
    // Si l'utilisateur n'est pas candidate, il sera rediriger vers la page d'accueil
    header("Location: index.php");
    exit();
}

// Récupérer l'ID de l'utilisateur connecté
$user_id = $_SESSION['user_id'];

// Vérifier si l'utilisateur existe dans la table `candidat`
$query = "SELECT * FROM candidates WHERE user_id = :user_id";
$stmt = $conn->prepare($query);
$stmt->execute(['user_id' => $user_id]);
$candidat = $stmt->fetch();

$candidate_id = $candidat['id'];


// Traitement du formulaire d'enregistrement du candidat
$message = "";
$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $skills = htmlspecialchars($_POST['skills']);
    $experience = htmlspecialchars($_POST['experience']);
    $phone = htmlspecialchars($_POST['phone']);

    if (empty($skills) || empty($experience) || empty($phone)) {
        $message = '<div class="alert alert-danger">Tous les champs sont obligatoires.</div>';
    } else {
        try {
            // Insérer les informations du candidat
            $insertCandidateQuery = "INSERT INTO candidates (user_id, skills, experience,phone) 
                                     VALUES (:user_id, :skills, :experience,:phone)";
            $stmt = $conn->prepare($insertCandidateQuery);
            $stmt->execute([
                ':user_id' => $user_id,
                ':skills' => $skills,
                ':experience' => $experience,
                ':phone' => $phone
            ]);

            $message = '<div class="alert alert-success">Votre enregistrement en tant que candidat a été effectué avec succès.</div>';
            $is_candidate_registered = true;
        } catch (PDOException $e) {
            $message = '<div class="alert alert-danger">Erreur : ' . htmlspecialchars($e->getMessage()) . '</div>';
        }
    }
}

// Récupérer les statistiques des candidatures pour le candidat connecté.
// Récupérer les statistiques des candidatures
$total_stmt = $conn->prepare("
SELECT 
    COUNT(*) AS total_applications,
    COALESCE(SUM(CASE WHEN status = 'accepter' THEN 1 ELSE 0 END), 0) AS accepter,
    COALESCE(SUM(CASE WHEN status = 'refuser' THEN 1 ELSE 0 END), 0) AS refuser,
    COALESCE(SUM(CASE WHEN status = 'en cours' THEN 1 ELSE 0 END), 0) AS encours
FROM applications
WHERE candidate_id = :candidate_id
");
$total_stmt->bindValue(':candidate_id', $candidate_id);
$total_stmt->execute();
$stats = $total_stmt->fetch(PDO::FETCH_ASSOC);





// Récupérer la liste des candidatures du candidat
//Partie 1: Limites de pagination
$limit = 10; // Nombre de candidatures par page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

//Partie 2 : Récupérer les valeurs de la recherche et du filtre
$search_term = isset($_GET['search_term']) ? "%" . $_GET['search_term'] . "%" : "%";
$status = isset($_GET['status']) ? $_GET['status'] : '%'; // '%' pour ne pas filtrer si aucun statut sélectionné


// 2. Requête pour récupérer les candidatures filtrées et paginées
$query = "
SELECT a.id, a.status, j.title, a.cv_file, a.cover_letter_file, a.years_experience, a.applied_at
FROM applications a
JOIN jobs j ON a.job_id = j.id
WHERE a.candidate_id = :candidate_id
AND j.title LIKE :search_term
AND a.status LIKE :status
LIMIT :limit OFFSET :offset
";

$stmt = $conn->prepare($query);
$stmt->bindParam(':candidate_id', $candidate_id, PDO::PARAM_INT);
$stmt->bindParam(':search_term', $search_term, PDO::PARAM_STR);
$stmt->bindParam(':status', $status, PDO::PARAM_STR);
$stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
$stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();

$candidatures = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 3. Récupérer le total de candidatures pour la pagination
$countQuery = "
SELECT COUNT(*) 
FROM applications a
JOIN jobs j ON a.job_id = j.id
WHERE a.candidate_id = :candidate_id
AND j.title LIKE :search_term
AND a.status LIKE :status
";

$stmt = $conn->prepare($countQuery);
$stmt->bindParam(':candidate_id', $candidate_id, PDO::PARAM_INT);
$stmt->bindParam(':search_term', $search_term, PDO::PARAM_STR);
$stmt->bindParam(':status', $status, PDO::PARAM_STR);
$stmt->execute();
$total_apps = $stmt->fetchColumn();
$total_pages = ceil($total_apps / $limit);


?>


<main>
    <?php if (!$candidat): ?>
        <div class="container">

            <div class="card p-3 my-5">
                <div class="alert alert-info" role="alert">
                    Veuillez compléter votre profil pour accéder à votre espace candidat.
                </div>
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php elseif ($message): ?>
                    <?php echo $message; ?>
                <?php endif; ?>
                <form action="espace_candidate.php" method="post">
                    <input type="hidden" name="user_id" value="<?= htmlspecialchars($user_id) ?>">
                    <div class="mb-3">
                        <label for="skills" class="form-label">Votre competence</label>
                        <input type="text" class="form-control" id="skills" name="skills" required>
                    </div>
                    <div class="mb-3">
                        <label for="experience" class="form-label">Votre Experience actuel</label>
                        <input type="text" class="form-control" id="experience" name="experience" required>
                    </div>

                    <div class="mb-3">
                        <label for="phone" class="form-label">Votre Téléphone</label>
                        <input type="number" class="form-control" id="phone" name="phone" required>
                    </div>

                    <button type="submit" class="btn btn-primary">Enregistrer</button>
                </form>
            </div>
        </div>

    <?php else: ?>
        <div class="text-center pt-lg-8">
            <h3 class="text-center">Bienvenue dans votre espace candidat.</h3>
        </div>


        <div class="container">
            <div class="row">
                <!-- Total des candidatures -->
                <div class="col-md-3">
                    <div class="card alert alert-info mb-3">
                        <div class="card-body">
                            <h5 class="card-title">Total des candidatures</h5>
                            <p class="card-text fs-1 text-center">
                                <?= htmlspecialchars($stats['total_applications'] ?? 0); ?>
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Candidatures acceptées -->
                <div class="col-md-3">
                    <div class="card alert alert-success mb-3">
                        <div class="card-body">
                            <h5 class="card-title">Candidatures acceptées</h5>
                            <p class="card-text fs-1 text-center">
                                <?= htmlspecialchars($stats['accepter'] ?? 0); ?>
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Candidatures en cours -->
                <div class="col-md-3">
                    <div class="card alert alert-warning mb-3">
                        <div class="card-body">
                            <h5 class="card-title">Candidatures en cours</h5>
                            <p class="card-text fs-1 text-center">
                                <?= htmlspecialchars($stats['encours'] ?? 0); ?>
                            </p>
                        </div>
                    </div>
                </div>


                <!-- Candidatures refusées -->
                <div class="col-md-3">
                    <div class="card alert alert-danger mb-3">
                        <div class="card-body">
                            <h5 class="card-title">Candidatures refusées</h5>
                            <p class="card-text fs-1 text-center">
                                <?= htmlspecialchars($stats['refuser'] ?? 0); ?>
                            </p>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <div class="container">
            <h2 class="mt-5">Mes Candidatures</h2>
            <!-- Formulaire de recherche et filtre par statut -->
            <form method="GET" class="mb-3">
                <div class="row">
                    <div class="col-md-4 mt-2">
                        <input type="text" name="search_term" class="form-control" placeholder="Rechercher par titre de l'offre" value="<?= isset($_GET['search_term']) ? htmlspecialchars($_GET['search_term']) : '' ?>">
                    </div>
                    <div class="col-md-4 mt-2">
                        <select name="status" class="form-control form-select">
                            <option value="%" <?= (isset($_GET['status']) && $_GET['status'] == '%') ? 'selected' : '' ?>>Tous les statuts</option>
                            <option value="en cours" <?= (isset($_GET['status']) && $_GET['status'] == 'en cours') ? 'selected' : '' ?>>En cours</option>
                            <option value="accpeter" <?= (isset($_GET['status']) && $_GET['status'] == 'accpeter') ? 'selected' : '' ?>>Acceptée</option>
                            <option value="refuser" <?= (isset($_GET['status']) && $_GET['status'] == 'refuser') ? 'selected' : '' ?>>Refusée</option>
                        </select>
                    </div>
                    <div class="col-md-4 mt-2">
                        <button type="submit" class="btn btn-primary form-control">Rechercher</button>
                    </div>
                </div>
            </form>
            <?php if (empty($candidatures)): ?>
                <div class="alert alert-info">Aucun resultat trouvée.</div>
            <?php else: ?>

                <div class="table-responsive">
                    <table class="table mt-4">
                        <thead class="bg-light text-center">
                            <tr>
                                <th>Offre d'Emploi</th>
                                <th>CV</th>
                                <th>Lettre de Motivation</th>
                                <th>Statut</th>
                                <th>Date de Candidature</th>
                            </tr>
                        </thead>
                        <tbody class="text-center">
                            <?php foreach ($candidatures as $candidature): ?>
                                <tr>
                                    <td><a href="voir_offre.php?<?= $candidature['id'] ?>"><?= htmlspecialchars($candidature['title']); ?></a></td>
                                    <td><a href="<?= htmlspecialchars($candidature['cover_letter_file']); ?>" target="_blank">Voir le lettre</a></td>
                                    <td><a href="<?= htmlspecialchars($candidature['cv_file']); ?>" target="_blank">Voir le CV</a></td>
                                    <td>
                                        <?php if ($candidature['status'] == "refuser") : ?>
                                            <span class="badge bg-danger"><?= ucfirst(htmlspecialchars($candidature['status'])); ?></span>
                                        <?php elseif ($candidature['status'] == "accepter") : ?>
                                            <span class="badge bg-success"><?= ucfirst(htmlspecialchars($candidature['status'])); ?></span>
                                        <?php elseif ($candidature['status'] == "en cours") : ?>
                                            <span class="badge bg-info"><?= ucfirst(htmlspecialchars($candidature['status'])); ?></span>
                                        <?php endif ?>
                                    </td>
                                    <td><?= htmlspecialchars($candidature['applied_at']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <!-- Pagination -->
                <nav aria-label="Page navigation">
                    <ul class="pagination">
                        <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                            <a class="page-link" href="?page=<?= max(1, $page - 1) ?>&search_term=<?= urlencode($search_term) ?>&status=<?= $status ?>">Précédent</a>
                        </li>
                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                                <a class="page-link" href="?page=<?= $i ?>&search_term=<?= urlencode($search_term) ?>&status=<?= $status ?>"><?= $i ?></a>
                            </li>
                        <?php endfor; ?>
                        <li class="page-item <?= $page >= $total_pages ? 'disabled' : '' ?>">
                            <a class="page-link" href="?page=<?= min($total_pages, $page + 1) ?>&search_term=<?= urlencode($search_term) ?>&status=<?= $status ?>">Suivant</a>
                        </li>
                    </ul>
                </nav>
            <?php endif; ?>
        </div>


    <?php endif ?>

</main>

<?php require_once "footer.php"; ?>