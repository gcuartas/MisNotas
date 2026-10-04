<?php

require '../../app/database/database.php';

$subject_id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($subject_id <= 0) {
    header('Location: index.php');
    exit;
}


$stmt = $pdo->prepare("
    SELECT
        s.id,
        s.name,
        s.credits,
        s.academic_period_id,
        ap.name AS period_name
    FROM subjects s
    INNER JOIN academic_period ap
        ON s.academic_period_id = ap.id
    WHERE s.id = ?
");

$stmt->execute([$subject_id]);

$subject = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$subject) {
    header('Location: index.php');
    exit;
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>
        <?php echo htmlspecialchars($subject['name']); ?> - MisNotas
    </title>

    <!-- Bootstrap 5 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        crossorigin="anonymous">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="//cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- Google Fonts: Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,200..800;1,200..800&display=swap"
        rel="stylesheet">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">

    <link rel="stylesheet" href="../assets/css/styles.css">
    <link rel="icon" type="image/x-icon" href="../assets/images/favicon.svg">
</head>

<body>

    <!-- Navbar -->
    <nav class="mn-navbar">
        <div class="container-xl d-flex align-items-center justify-content-between">

            <a href="../index.php" class="mn-brand d-flex align-items-center gap-2 text-decoration-none">
                <img src="../assets/images/logo_shadow.svg" alt="MisNotas logo" class="mn-brand-icon">

                <span class="mn-brand-name">
                    MisNotas
                </span>
            </a>

            <ul class="mn-nav-links list-unstyled mb-0 d-flex gap-4">
                <li>
                    <a href="#" class="mn-nav-link">History</a>
                </li>

                <li>
                    <a href="index.php" class="mn-nav-link">
                        Academic Periods
                    </a>
                </li>

                <li>
                    <a href="#" class="mn-nav-link">Reports</a>
                </li>

                <li>
                    <a href="#" class="mn-nav-link">Gabriel</a>
                </li>
            </ul>

        </div>
    </nav>


    <main class="container-xl py-5">

        <!-- Back -->
        <a href="academic_periods.php" class="mn-back-link-btn">
            <i class="bi bi-arrow-left-circle-fill"></i>  Back to Academic Periods
        </a>


        <!-- Subject Header -->
        <section class="mn-subject-header mt-4">

            <div>
                <p class="mn-subject-period">
                    <?php echo htmlspecialchars($subject['period_name']); ?>
                </p>

                <h1 class="mn-subject-title">
                    <?php echo htmlspecialchars($subject['name']); ?>
                </h1>

                <p class="mn-subject-credits">
                    <?php echo (int) $subject['credits']; ?> credits
                </p>
            </div>

        </section>


        <!-- Grade Overview -->
        <section class="mn-section mt-5">

            <h2 class="mn-section-title">
                Grade Overview
            </h2>

            <div class="row g-4 mt-2">

                <div class="col-md-4">
                    <div class="mn-grade-card">
                        <span>Current Grade</span>
                        <strong>—</strong>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="mn-grade-card">
                        <span>Projected Grade</span>
                        <strong>—</strong>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="mn-grade-card">
                        <span>Goal</span>
                        <strong>—</strong>
                    </div>
                </div>

            </div>

        </section>


        <!-- Assessment Components -->
        <section class="mn-section mt-5">

            <div class="d-flex justify-content-between align-items-center">

                <h2 class="mn-section-title mb-0">
                    Assessment Components
                </h2>

                <button type="button" class="btn mn-primary-btn">
                    + Add Component
                </button>

            </div>


            <div class="mn-assessment-list mt-4">

                <!-- Placeholder for now -->
                <p class="mn-empty-state">
                    No assessment components registered yet.
                </p>

            </div>

        </section>


    </main>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>