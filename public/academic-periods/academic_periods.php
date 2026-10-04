<?php

session_start();

require '../../app/database/database.php';

$form_error = '';
$form_modal = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_subject'])) {
    $period_id = filter_input(INPUT_POST, 'period_id', FILTER_VALIDATE_INT);
    $name = trim($_POST['name'] ?? '');
    $credits = filter_input(INPUT_POST, 'credits', FILTER_VALIDATE_INT);
    $form_modal = 'createSubjectModal';

    if (!$period_id || $name === '' || $credits === false || $credits === null || $credits < 1) {
        $form_error = 'Please enter a subject name and a valid number of credits.';
    } else {
        $period_stmt = $pdo->prepare('SELECT 1 FROM academic_period WHERE id = ? LIMIT 1');
        $period_stmt->execute([$period_id]);

        if (!$period_stmt->fetchColumn()) {
            $form_error = 'The selected academic period does not exist.';
        } else {
            $duplicate_stmt = $pdo->prepare(
                'SELECT 1 FROM subjects WHERE academic_period_id = ? AND name = ? LIMIT 1'
            );
            $duplicate_stmt->execute([$period_id, $name]);

            if ($duplicate_stmt->fetchColumn()) {
                $form_error = 'A subject with this name already exists in this academic period.';
            } else {
                $insert_stmt = $pdo->prepare(
                    'INSERT INTO subjects (academic_period_id, name, credits) VALUES (?, ?, ?)'
                );
                $insert_stmt->execute([$period_id, $name, $credits]);

                header('Location: academic_periods.php');
                exit;
            }
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_period'])) {
    $form_modal = 'newPeriodModal';
    $name = trim($_POST['name'] ?? '');
    $start_date = $_POST['start_date'] ?? '';
    $end_date = $_POST['end_date'] ?? '';
    $start_date_object = DateTime::createFromFormat('!Y-m-d', $start_date);
    $end_date_object = DateTime::createFromFormat('!Y-m-d', $end_date);

    if ($name === '' || !$start_date_object || !$end_date_object) {
        $form_error = 'Please enter a name and valid dates.';
    } elseif ($start_date_object > $end_date_object) {
        $form_error = 'The start date must be before the end date.';
    } else {
        $user_id = $_SESSION['user_id'] ?? 1;
        $duplicate_stmt = $pdo->prepare('SELECT 1 FROM academic_period WHERE name = ? LIMIT 1');
        $duplicate_stmt->execute([$name]);

        if ($duplicate_stmt->fetchColumn()) {
            $form_error = 'An academic period with this name already exists.';
        } else {
            $today = new DateTime('today');
            $status = $today < $start_date_object
                ? 'NOT_STARTED'
                : ($today > $end_date_object ? 'FINISHED' : 'IN_PROGRESS');
            $insert_stmt = $pdo->prepare("
                INSERT INTO academic_period (user_id, name, status, start_date, end_date)
                VALUES (?, ?, ?, ?, ?)
            ");

            try {
                $insert_stmt->execute([$user_id, $name, $status, $start_date, $end_date]);
            } catch (PDOException $exception) {
                if ($exception->errorInfo[1] === 1062) {
                    $form_error = 'An academic period with this name already exists.';
                } else {
                    throw $exception;
                }
            }

            if ($form_error === '') {
                header('Location: academic_periods.php');
                exit;
            }
        }
    }
}

$summary_stmt = $pdo->query("
    SELECT
        p.id,
        p.name AS period_name,
        p.status,
        p.start_date,
        p.end_date,
        COUNT(s.id) AS num_subjects,
        COALESCE(SUM(s.credits), 0) AS total_credits
    FROM academic_period p
    LEFT JOIN subjects s
        ON s.academic_period_id = p.id
    GROUP BY
        p.id,
        p.name,
        p.status,
        p.start_date,
        p.end_date
    ORDER BY p.start_date DESC
");

$summary_rows = $summary_stmt->fetchAll(PDO::FETCH_ASSOC);

// ----------------------------------------------------------------
// Data for charts
// ----------------------------------------------------------------

$chart_labels = json_encode(
    array_column($summary_rows, 'period_name')
);

$chart_avg_grades = json_encode(
    array_fill(0, count($summary_rows), 0)
);

$chart_subjects = json_encode(
    array_map(
        fn($row) => (int) $row['num_subjects'],
        $summary_rows
    )
);

$chart_credits = json_encode(
    array_map(
        fn($row) => (int) $row['total_credits'],
        $summary_rows
    )
);

// ----------------------------------------------------------------
// All academic periods with their subjects
// ----------------------------------------------------------------

$periods_stmt = $pdo->query("
    SELECT
        id,
        name,
        status,
        start_date,
        end_date
    FROM academic_period
    ORDER BY start_date DESC
");

$periods = $periods_stmt->fetchAll(PDO::FETCH_ASSOC);

// ----------------------------------------------------------------
// Get subjects for each academic period
// ----------------------------------------------------------------

$sub_stmt = $pdo->prepare("
    SELECT
        id,
        name,
        credits
    FROM subjects
    WHERE academic_period_id = ?
    ORDER BY id ASC
");

foreach ($periods as &$period) {

    $sub_stmt->execute([$period['id']]);

    $period['subjects'] = $sub_stmt->fetchAll(PDO::FETCH_ASSOC);
}

unset($period);

// ----------------------------------------------------------------
// Logged-in username
// ----------------------------------------------------------------

$username = htmlspecialchars(
    $_SESSION['username'] ?? 'Gabriel'
);


// ----------------------------------------------------------------
// Delete academic period
// ----------------------------------------------------------------

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_period'])) {

    $period_id = filter_input(INPUT_POST, 'period_id', FILTER_VALIDATE_INT);

    if (!$period_id) {
        die('Invalid academic period.');
    }

    try {

        $pdo->beginTransaction();

        // Delete grades belonging to assessments in this period
        $stmt = $pdo->prepare("
            DELETE g
            FROM grades g
            INNER JOIN assessments a
                ON a.id = g.assesment_id
            INNER JOIN assessment_groups ag
                ON ag.id = a.assesment_group_id
            INNER JOIN assessment_components ac
                ON ac.id = ag.assesment_component_id
            INNER JOIN subjects s
                ON s.id = ac.subject_id
            WHERE s.academic_period_id = ?
        ");
        $stmt->execute([$period_id]);


        // Delete assessments
        $stmt = $pdo->prepare("
            DELETE a
            FROM assessments a
            INNER JOIN assessment_groups ag
                ON ag.id = a.assesment_group_id
            INNER JOIN assessment_components ac
                ON ac.id = ag.assesment_component_id
            INNER JOIN subjects s
                ON s.id = ac.subject_id
            WHERE s.academic_period_id = ?
        ");
        $stmt->execute([$period_id]);


        // Delete assessment groups
        $stmt = $pdo->prepare("
            DELETE ag
            FROM assessment_groups ag
            INNER JOIN assessment_components ac
                ON ac.id = ag.assesment_component_id
            INNER JOIN subjects s
                ON s.id = ac.subject_id
            WHERE s.academic_period_id = ?
        ");
        $stmt->execute([$period_id]);


        // Delete assessment components
        $stmt = $pdo->prepare("
            DELETE ac
            FROM assessment_components ac
            INNER JOIN subjects s
                ON s.id = ac.subject_id
            WHERE s.academic_period_id = ?
        ");
        $stmt->execute([$period_id]);


        // Delete goals associated with the period
        $stmt = $pdo->prepare("
            DELETE FROM goals
            WHERE academic_period_id = ?
        ");
        $stmt->execute([$period_id]);


        // Delete subjects
        $stmt = $pdo->prepare("
            DELETE FROM subjects
            WHERE academic_period_id = ?
        ");
        $stmt->execute([$period_id]);


        // Finally, delete the academic period
        $stmt = $pdo->prepare("
            DELETE FROM academic_period
            WHERE id = ?
        ");
        $stmt->execute([$period_id]);


        $pdo->commit();

        // Refresh the page
        header('Location: academic_periods.php');
        exit;

    } catch (PDOException $e) {

        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        die('Could not delete academic period.');
    }
}



?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Períodos Académicos — MisNotas</title>

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

    <nav class="mn-navbar navbar navbar-expand-xxl">
        <div class="container">
            <a href="../index.php" class="mn-brand d-flex align-items-center gap-2 text-decoration-none">
                <img src="../assets/images/logo_shadow.svg" alt="MisNotas logo" class="mn-brand-icon" />
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
                        <a href="../index.php" class="mn-nav-link">Dashboard</a>
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

    <!-- ==============================================================
     SUMMARY SECTION
     ============================================================== -->
    <section class="mn-section-summary">
        <h1 class="mn-section-title">Summary</h1>
        <p class="mn-section-subtitle">Here's a summary of all your academic periods.</p>

        <div class="row g-4">

            <!-- Average Grade Chart -->
            <div class="col-12 col-lg-4">
                <div class="mn-chart-card">
                    <p class="mn-chart-card-title">Average Grade</p>
                    <div class="mn-chart-inner">
                        <canvas id="chartAvgGrade"></canvas>
                    </div>
                </div>
            </div>

            <!-- Subject Count Chart -->
            <div class="col-12 col-lg-4">
                <div class="mn-chart-card">
                    <p class="mn-chart-card-title">Subject Count</p>
                    <div class="mn-chart-inner">
                        <canvas id="chartSubjectCount"></canvas>
                    </div>
                </div>
            </div>

            <!-- Credit Count Chart -->
            <div class="col-12 col-lg-4">
                <div class="mn-chart-card">
                    <p class="mn-chart-card-title">Credit Count</p>
                    <div class="mn-chart-inner">
                        <canvas id="chartCreditCount"></canvas>
                    </div>
                </div>
            </div>

        </div>
    </section>



    <section class="mn-section-periods">
        <div class="mn-section-header">
            <h1 class="mn-section-title">Academic Periods</h1>
            <button type="button" class="mn-new-academic-period-btn" data-bs-toggle="modal"
                data-bs-target="#newPeriodModal">
                New Academic Period
            </button>
        </div>

        <?php if (empty($periods)): ?>
            <p class="mn-empty-state">No academic periods found.</p>
        <?php else: ?>

            <div class="row g-4">
                <?php foreach ($periods as $period): ?>
                    <?php
                    $today = new DateTime('today');
                    $period_start = new DateTime($period['start_date']);
                    $period_end = new DateTime($period['end_date']);
                    $period_status = $today < $period_start
                        ? 'NOT_STARTED'
                        : ($today > $period_end ? 'FINISHED' : 'IN_PROGRESS');
                    $is_open = ($period_status === 'IN_PROGRESS');
                    $status_class = $period_status === 'FINISHED' ? 'closed' : strtolower(str_replace('_', '-', $period_status));
                    ?>
                    <div class="col-12 col-md-6">
                        <div class="mn-period-card">
                            <div class="mn-period-header">
                                <div>
                                    <h3 class="mn-period-name">
                                        <?php echo htmlspecialchars($period['name']); ?>
                                    </h3>
                                    <span class="mn-period-dates">
                                        <?php
                                        echo "Starts on: ";
                                        echo htmlspecialchars($period['start_date']);
                                        echo '<br/>';
                                        echo "Ends on: ";
                                        echo htmlspecialchars($period['end_date']);
                                        ?>
                                    </span>
                                </div>
                                <span class="mn-period-status <?php echo $status_class; ?>">
                                    Status:
                                    <?php

                                    echo $period_status === 'IN_PROGRESS'
                                        ? 'In Progress'
                                        : ($period_status === 'NOT_STARTED' ? 'Not Started' : 'Closed');

                                    ?>
                                </span>
                            </div>
                            <div class="mn-subject-list">
                                <?php if (empty($period['subjects'])): ?>
                                    <p class="mn-no-subjects">
                                        No subjects registered yet.
                                    </p>
                                <?php else: ?>
                                    <?php foreach ($period['subjects'] as $subject): ?>
                                        <div class="mn-subject-row align-items-center">
                                            <span class="mn-subject-name" title="<?php echo htmlspecialchars($subject['name']); ?>">
                                                <?php echo htmlspecialchars($subject['name']); ?>
                                            </span>
                                            <span class="mn-subject-divider" aria-hidden="true"></span>
                                            <span class="mn-subject-credits">
                                                <?php
                                                echo htmlspecialchars(
                                                    $subject['credits']
                                                );
                                                ?>
                                                credits
                                            </span>
                                            <a href="subject.php?id=<?php echo (int) $subject['id']; ?>"
                                                class="mn-show-subject-details-btn mt-2">
                                                Show Details
                                            </a>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                            <div class=".mn-period-card-buttons d-flex mt-2 justify-content-center align-items-center">
                            <button type="button" class="mn-delete-period-btn mt-2"
                                data-period-id="<?php echo $period['id']; ?>">
                                <i class="bi bi-trash-fill m-1"></i> Delete Period
                            </button>
                            <button type="button" class="mn-add-subject-btn mt-2" data-period-id="<?php echo $period['id']; ?>">
                                <i class="bi bi-plus m-1"></i> Add Subject
                            </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>


    <div class="modal fade mn-period-modal" id="newPeriodModal" tabindex="-1" aria-labelledby="newPeriodModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="newPeriodModalLabel">
                        New Academic Period
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                    </button>
                </div>
                <form method="POST" class="mn-period-form">
                    <div class="modal-body">
                        <?php if ($form_error !== ''): ?>
                            <div class="alert alert-danger" role="alert">
                                <?php echo htmlspecialchars($form_error); ?>
                            </div>
                        <?php endif; ?>
                        <div class="mb-3">
                            <label for="periodName" class="form-label">
                                Period name
                            </label>

                            <input type="text" class="form-control mn-modal-input" id="periodName" name="name"
                                placeholder="e.g. 2026-2" maxlength="50" required>
                        </div>
                        <div class="mb-3">
                            <label for="startDate" class="form-label">
                                Start date
                            </label>

                            <input type="text" class="form-control mn-modal-input mn-flatpickr" id="startDate"
                                name="start_date" placeholder="YYYY-MM-DD" autocomplete="off" required>
                        </div>
                        <div class="mb-3">
                            <label for="endDate" class="form-label">
                                End date
                            </label>

                            <input type="text" class="form-control mn-modal-input mn-flatpickr" id="endDate"
                                name="end_date" placeholder="YYYY-MM-DD" autocomplete="off" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary mn-modal-cancel" data-bs-dismiss="modal">
                            Cancel
                        </button>
                        <button type="submit" name="create_period" class="btn btn-primary mn-modal-submit">
                            Create Period
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Academic Period Modal -->
    <div class="modal fade mn-period-modal" id="deletePeriodModal" tabindex="-1"
        aria-labelledby="deletePeriodModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title" id="deletePeriodModalLabel">
                        Delete Academic Period
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <form method="POST">
                    <div class="modal-body">
                        <p>
                            Are you sure you want to delete this academic period?
                        </p>
                        <p class="text">
                            This will delete:
                        <ul>
                            <li>All subjects in this period</li>
                            <li>All assessment components, groups, and assessments</li>
                            <li>All grades associated with these assessments</li>
                            <li>All goals associated with this period</li>
                        </ul>
                        </p>
                        <input type="hidden" name="period_id" id="deletePeriodId">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary mn-modal-cancel" data-bs-dismiss="modal">
                            Cancel
                        </button>
                        <button type="submit" name="delete_period" class="mn-delete-period-btn btn-danger">
                            Delete Period
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>



    <!--- Create Subject modal -->
    <div class="modal fade mn-subject-modal" id="createSubjectModal" tabindex="-1"
        aria-labelledby="createSubjectModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="createSubjectModalLabel">
                        Create New Subject
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                    </button>
                </div>
                <form method="POST" class="mn-period-form">
                    <div class="modal-body">
                        <?php if ($form_error !== '' && $form_modal === 'createSubjectModal'): ?>
                            <div class="alert alert-danger" role="alert">
                                <?php echo htmlspecialchars($form_error); ?>
                            </div>
                        <?php endif; ?>
                        <input type="hidden" name="period_id" id="subjectPeriodId">
                        <div class="mb-3">
                            <label for="subjectName" class="form-label">
                                Subject name
                            </label>
                            <input type="text" class="form-control mn-modal-input" id="subjectName" name="name"
                                placeholder="e.g. Mathematics" maxlength="50" required>
                        </div>
                        <div class="mb-3">
                            <label for="subjectCredits" class="form-label">
                                Credits
                            </label>
                            <input type="number" class="form-control mn-modal-input" id="subjectCredits" name="credits"
                                min="1" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary mn-modal-cancel" data-bs-dismiss="modal">
                            Cancel
                        </button>
                        <button type="submit" name="create_subject" class="btn btn-primary mn-modal-submit">
                            Create Subject
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <footer class="mn-footer">
        <div class="container-xl">
            <div class="mn-footer-inner">

                <div class="mn-footer-brand">
                    <a href="#" class="d-flex align-items-center gap-2 text-decoration-none">
                        <img src="../assets/images/logo_footer.svg" alt="MisNotas logo" class="mn-brand-icon" />
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


    <!-- ==============================================================
     Scripts
     ============================================================== -->
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        crossorigin="anonymous"></script>

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js" crossorigin="anonymous"></script>

    <!-- Flatpickr -->
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

    <?php if ($form_error !== '' && $form_modal !== ''): ?>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                bootstrap.Modal.getOrCreateInstance(document.getElementById('<?php echo $form_modal; ?>')).show();
            });
        </script>
    <?php endif; ?>

    <script>
        (function () {
            'use strict';

            /* PHP data injected as JSON */
            const labels = <?php echo $chart_labels; ?>;
            const avgGrades = <?php echo $chart_avg_grades; ?>;
            const subjects = <?php echo $chart_subjects; ?>;
            const credits = <?php echo $chart_credits; ?>;

            /* Shared chart configuration */
            const chartPalette = {
                avg: {
                    line: '#FFE167',
                    fill: 'rgba(255, 225, 103, 0.15)'
                },
                subjects: {
                    line: '#65D6FF',
                    fill: 'rgba(101, 214, 255, 0.15)'
                },
                credits: {
                    line: '#8BFFB0',
                    fill: 'rgba(139, 255, 176, 0.15)'
                }
            };

            const sharedOptions = {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        display: true,
                        position: 'bottom',
                        labels: {
                            color: 'rgba(255, 255, 255, 0.82)',
                            font: { family: 'Plus Jakarta Sans, sans-serif', size: 11 },
                            usePointStyle: true,
                            pointStyle: 'circle',
                            padding: 12
                        }
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false,
                        backgroundColor: 'rgba(12, 14, 45, 0.95)',
                        titleColor: '#FFE167',
                        bodyColor: '#ffffff',
                        borderColor: 'rgba(255, 225, 103, 0.45)',
                        borderWidth: 1,
                        padding: 10
                    }
                },
                scales: {
                    x: {
                        ticks: {
                            color: 'rgba(255, 255, 255, 0.72)',
                            font: { family: 'Plus Jakarta Sans, sans-serif', size: 10 },
                            maxRotation: 45,
                            minRotation: 30
                        },
                        grid: { color: 'rgba(255, 255, 255, 0.09)' }
                    },
                    y: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0,
                            color: 'rgba(255, 255, 255, 0.72)',
                            font: { family: 'Plus Jakarta Sans, sans-serif', size: 10 }
                        },
                        grid: {
                            color: 'rgba(255, 255, 255, 0.10)',
                            borderDash: [4, 4]
                        }
                    }
                },
                elements: {
                    line: { tension: 0.35, borderWidth: 2.4 },
                    point: {
                        radius: 3.8,
                        hoverRadius: 6,
                        borderColor: '#ffffff',
                        borderWidth: 1.3
                    }
                }
            };

            function makeDataset(label, data, palette) {
                return {
                    label,
                    data,
                    borderColor: palette.line,
                    backgroundColor: palette.fill,
                    fill: true,
                    pointBackgroundColor: palette.line,
                    pointBorderColor: '#ffffff',
                    pointBorderWidth: 1.3,
                    pointRadius: 3.8,
                    pointHoverRadius: 6
                };
            }

            /* Render the three charts */
            new Chart(
                document.getElementById('chartAvgGrade'),
                {
                    type: 'line',
                    data: { labels, datasets: [makeDataset('Avg Grade', avgGrades, chartPalette.avg)] },
                    options: sharedOptions
                }
            );

            new Chart(
                document.getElementById('chartSubjectCount'),
                {
                    type: 'bar',
                    data: { labels, datasets: [makeDataset('Subjects', subjects, chartPalette.subjects)] },
                    options: sharedOptions
                }
            );

            new Chart(
                document.getElementById('chartCreditCount'),
                {
                    type: 'line',
                    data: { labels, datasets: [makeDataset('Credits', credits, chartPalette.credits)] },
                    options: sharedOptions
                }
            );

            if (typeof flatpickr === 'function') {
                const endPicker = flatpickr('#endDate', {
                    dateFormat: 'Y-m-d',
                    monthSelectorType: 'dropdown',
                    allowInput: false
                });

                flatpickr('#startDate', {
                    dateFormat: 'Y-m-d',
                    monthSelectorType: 'dropdown',
                    allowInput: false,
                    onChange: function (selectedDates) {
                        if (selectedDates.length > 0) {
                            endPicker.set('minDate', selectedDates[0]);
                        }
                    }
                });
            }
        }());
    </script>

    <script>
        document.querySelectorAll('.mn-add-subject-btn').forEach(button => {
            button.addEventListener('click', function () {
                document.getElementById('subjectPeriodId').value = this.dataset.periodId;
                bootstrap.Modal.getOrCreateInstance(
                    document.getElementById('createSubjectModal')
                ).show();
            });
        });

        document.querySelectorAll('.mn-delete-period-btn[data-period-id]').forEach(button => {

            button.addEventListener('click', function () {

                const periodId = this.dataset.periodId;

                document.getElementById('deletePeriodId').value = periodId;

                const modal = new bootstrap.Modal(
                    document.getElementById('deletePeriodModal')
                );

                modal.show();
            });

        });
    </script>

</body>

</html>