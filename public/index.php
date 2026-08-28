<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>MisNotas – Not just a grade tracker.</title>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" />
    <link rel="icon" type="image/x-icon" href="assets/images/favicon.svg">
    <link rel="stylesheet" href="assets/css/styles.css" />

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,200..800;1,200..800&display=swap"
        rel="stylesheet">


</head>

<body>
    
    <nav class="mn-navbar navbar navbar-expand-xxl">
        <div class="container">
            <a href="#" class="mn-brand d-flex align-items-center gap-2 text-decoration-none">
                <img src="assets/images/logo_shadow.svg" alt="MisNotas logo" class="mn-brand-icon" />
                <span class="mn-brand-name">MisNotas</span>
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class=" mn-nav-links navbar-nav ms-auto">
                    <li class="nav-item ">
                        <a href="#" class="mn-nav-link">History</a>
                    </li>
                    <li class="nav-item">
                        <a href="academic-periods/academic_periods.php" class="mn-nav-link">Academic Periods</a>
                    </li>
                    <li class="nav-item">
                        <a href="#" class="mn-nav-link">Reports</a>
                    </li>
                    <li class="nav-item">
                        <a href="#" class="mn-nav-link">Gabriel</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <main class="mn-main">
        <div class="container-xl">

            <section class="mn-greeting mb-4">
                <h1 class="mn-greeting-title">Good afternoon, Gabriel</h1>
                <p class="mn-greeting-sub">Here's how you're doing this academic period.</p>
            </section>

            <section class="mn-stats mb-5">
                <div class="row g-3">
                    <div class="col-12 col-md-4">
                        <div class="mn-card mn-stat-card">
                            <span class="mn-stat-label">Current Average</span>
                            <span class="mn-stat-value">4.72</span>
                        </div>
                    </div>
                    <div class="col-12 col-md-4">
                        <div class="mn-card mn-stat-card">
                            <span class="mn-stat-label">Subject Count</span>
                            <span class="mn-stat-value">5</span>
                        </div>
                    </div>
                    <div class="col-12 col-md-4">
                        <div class="mn-card mn-stat-card">
                            <span class="mn-stat-label">Subjects At Risk</span>
                            <span class="mn-stat-value">1</span>
                        </div>
                    </div>
                </div>
            </section>


            <section class="mn-subjects mb-5">
                <h2 class="mn-section-title">My Subjects</h2>
                <p class="mn-section-period">Current Academic Period: 2026-2</p>

                <div class="mn-card mn-subjects-card mt-3">

                    <div class="mn-subject-row">
                        <div class="mn-subject-left">
                            <span class="mn-subject-name">Matemáticas Aplicadas II</span>
                            <div class="d-flex align-items-center gap-2 mt-1">
                                <span class="mn-grade-label">Current Grade:</span>
                                <span class="mn-grade-value">4.5</span>
                            </div>
                            <div class="mn-progress-bar mt-2">
                                <div class="mn-progress-fill" style="width: 90%"></div>
                            </div>
                        </div>
                        <div class="mn-subject-right">
                            <span class="mn-goal-label">Goal: <strong>4.7</strong></span>
                            <span class="mn-status mn-status--track">On Track</span>
                        </div>
                    </div>

                    <hr class="mn-divider" />

                    <div class="mn-subject-row">
                        <div class="mn-subject-left">
                            <span class="mn-subject-name">Algoritmos y Programación II</span>
                            <div class="d-flex align-items-center gap-2 mt-1">
                                <span class="mn-grade-label">Current Grade:</span>
                                <span class="mn-grade-value">4.6</span>
                            </div>
                            <div class="mn-progress-bar mt-2">
                                <div class="mn-progress-fill" style="width: 92%"></div>
                            </div>
                        </div>
                        <div class="mn-subject-right">
                            <span class="mn-goal-label">Goal: <strong>4.8</strong></span>
                            <span class="mn-status mn-status--track">On Track</span>
                        </div>
                    </div>

                    <hr class="mn-divider" />


                    <div class="mn-subject-row">
                        <div class="mn-subject-left">
                            <span class="mn-subject-name">Ingeniería de Software II</span>
                            <div class="d-flex align-items-center gap-2 mt-1">
                                <span class="mn-grade-label">Current Grade:</span>
                                <span class="mn-grade-value">4.7</span>
                            </div>
                            <div class="mn-progress-bar mt-2">
                                <div class="mn-progress-fill" style="width: 100%"></div>
                            </div>
                        </div>
                        <div class="mn-subject-right">
                            <span class="mn-goal-label">Goal: <strong>4.6</strong></span>
                            <span class="mn-status mn-status--achieved">Achieved!</span>
                        </div>
                    </div>

                </div>
            </section>


            <section class="mn-goals mb-5">
                <h2 class="mn-section-title">My Academic Goals</h2>
                <p class="mn-section-period">Current Academic Period: 2026-2</p>

                <div class="row g-3 mt-2">
                    <div class="col-12 col-md-4">
                        <div class="mn-card mn-goal-card">
                            <span class="mn-goal-tier">Lista de Honor</span>
                            <span class="mn-goal-desc">Target grade</span>
                            <span class="mn-goal-target">4.0</span>
                            <span class="mn-goal-desc mt-2">Projected Final Grade</span>
                            <span class="mn-goal-projected">4.62</span>
                            <div class="mn-progress-bar mt-3">
                                <div class="mn-progress-fill" style="width: 100%"></div>
                            </div>
                            <span class="mn-goal-congrats">Lista de Honor is 100% complete. Congratulations!</span>
                        </div>
                    </div>

                    <div class="col-12 col-md-4">
                        <div class="mn-card mn-goal-card">
                            <span class="mn-goal-tier">Lista de Honor</span>
                            <span class="mn-goal-desc">Target grade</span>
                            <span class="mn-goal-target">4.0</span>
                            <span class="mn-goal-desc mt-2">Projected Final Grade</span>
                            <span class="mn-goal-projected">4.62</span>
                            <div class="mn-progress-bar mt-3">
                                <div class="mn-progress-fill" style="width: 100%"></div>
                            </div>
                            <span class="mn-goal-congrats">Lista de Honor is 100% complete. Congratulations!</span>
                        </div>
                    </div>

                    <div class="col-12 col-md-4">
                        <div class="mn-card mn-goal-card">
                            <span class="mn-goal-tier">Lista de Honor</span>
                            <span class="mn-goal-desc">Target grade</span>
                            <span class="mn-goal-target">4.0</span>
                            <span class="mn-goal-desc mt-2">Projected Final Grade</span>
                            <span class="mn-goal-projected">4.62</span>
                            <div class="mn-progress-bar mt-3">
                                <div class="mn-progress-fill" style="width: 100%"></div>
                            </div>
                            <span class="mn-goal-congrats">Lista de Honor is 100% complete. Congratulations!</span>
                        </div>
                    </div>

                </div>
            </section>

        </div>
    </main>

    <footer class="mn-footer">
        <div class="container-xl">
            <div class="mn-footer-inner">

                <div class="mn-footer-brand">
                    <a href="#" class="d-flex align-items-center gap-2 text-decoration-none">
                        <img src="assets/images/logo_footer.svg" alt="MisNotas logo" class="mn-brand-icon" />
                        <div>
                            <div class="mn-brand-name">MisNotas</div>
                            <div class="mn-footer-tagline">Not just a grade tracker.</div>
                        </div>
                    </a>
                </div>

                <div class="mn-footer-contact">
                    <p class="mn-contact-heading">CONTACT US</p>
                    <p class="mn-contact-line">
                        <span class="mn-contact-key">Phone number:</span>
                        <a class="phones-and-emails" href="tel:+573128085440">+57 312 808 5440</a> <br />
                        <a class="phones-and-emails" href="tel:+573178862778">+57 317 886 2778</a>
                    </p>
                    <p class="mn-contact-line">
                        <span class="mn-contact-key">Support e-mail:</span>
                        <a class="phones-and-emails"
                            href="mailto:gabriel.cuartasbarrios@gmail.com">gabriel.cuartasbarrios@gmail.com</a> <br />
                        <a class="phones-and-emails" href="mailto:santiagomanu41@gmail.com">santiagomanu41@gmail.com</a>
                    </p>
                </div>

            </div>

            <div class="mn-footer-copy">
                © 2026 MisNotas. All rights reserved.
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>