<?php
$titre = "Nos services";
require_once "header.php";
?>


<main>
    <section class="py-5 py-lg-8">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 offset-lg-2 col-md-12 col-12">
                    <div class="text-center">
                        <h1 class=""><?= $lang[$current_lang]['Nos services'] ?></h1>
                        <p class="lead"><?= $lang[$current_lang]['Conçus pour Répondre à Vos Besoins Professionnels'] ?>.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!--Pageheader end-->



    <!--Service start-->
    <section class="mb-xl-9 my-4">
        <div class="container">
            <div class="row g-7">
                <div class="col-sm-6 col-md-4">
                    <div class="card p-2">
                        <div class="">
                            <div class="position-relative mb-7">
                                <img src="images/offres-emploi.jpg" alt="wide" class="rounded-3 img-fluid">

                            </div>
                        </div>
                        <div class="px-lg-4">
                            <h2 class="mb-3 h3"><?= $lang[$current_lang]['Publication d\'Offre d\'Emploi'] ?></h2>
                            <p class="mb-5">
                                <?= $lang[$current_lang]["Publiez vos offres d'emploi sur notre plateformes pour attirer les meilleurs talents"] ?>.
                            </p>

                        </div>
                    </div>
                </div>

                <div class="col-sm-6 col-md-4">
                    <div class="card p-2">
                        <div class="">
                            <div class="position-relative mb-7">
                                <img src="images/gestion_candidatures.png" alt="wide" class="rounded-3 img-fluid">

                            </div>
                        </div>
                        <div class="px-lg-4">
                            <h2 class="mb-3 h3"><?= $lang[$current_lang]["Gestion des Candidatures"] ?></h2>
                            <p class="mb-5">
                                <?= $lang[$current_lang]["Gérez facilement les candidatures et organisez-les en fonction de vos critères"] ?>.
                            </p>

                        </div>
                    </div>
                </div>


                <div class="col-sm-6 col-md-4">
                    <div class="card p-2">
                        <div class="">
                            <div class="position-relative mb-7">
                                <img src="images/suivi_candidats.webp" alt="wide" class="rounded-3 img-fluid">

                            </div>
                        </div>
                        <div class="px-lg-4">
                            <h2 class="mb-3 h3"><?= $lang[$current_lang]["Suivi des Candidats"] ?></h2>
                            <p class="mb-5">
                                <?= $lang[$current_lang]["Suivez chaque étape du processus de recrutement pour ne rien manquer"] ?>.
                            </p>

                        </div>
                    </div>
                </div>


                <div class="col-sm-6 col-md-4">
                    <div class="card p-2">
                        <div class="">
                            <div class="position-relative mb-7">
                                <img src="images/evaluation_competences.jpg" alt="wide" class="rounded-3 img-fluid">

                            </div>
                        </div>
                        <div class="px-lg-4">
                            <h2 class="mb-3 h3"><?= $lang[$current_lang]["Évaluation des Compétences"] ?></h2>
                            <p class="mb-5">
                                <?= $lang[$current_lang]["Évaluez les compétences des candidats pour garantir leur adéquation avec le poste"] ?>.
                            </p>

                        </div>
                    </div>
                </div>






            </div>
        </div>
    </section>
    <!--Service end-->
</main>

<?php require_once "footer.php"; ?>