<?php
require_once 'config.php';

// Redirect if not logged in
if (!isLoggedIn()) {
    redirect('login.php');
}

try {
    $conn = getDBConnection();
    
    // Get user's game sessions
    $stmt = $conn->prepare("
        SELECT session_id, start_time, end_time, generations, score 
        FROM game_sessions 
        WHERE user_id = ? 
        ORDER BY start_time DESC 
        LIMIT 10
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $sessions = $stmt->fetchAll();
    
    // Get user's total statistics
    $stmt = $conn->prepare("
        SELECT 
            COUNT(*) as total_sessions,
            SUM(generations) as total_generations,
            MAX(score) as highest_score,
            AVG(score) as average_score
        FROM game_sessions 
        WHERE user_id = ?
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $stats = $stmt->fetch();
    
} catch (PDOException $e) {
    $error = 'Database error: ' . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Game of Life</title>
    <link rel="stylesheet" href="styles.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php">Game of Life</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Game</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="dashboard.php">Dashboard</a>
                    </li>
                    <?php if (isAdmin()): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="admin.php">Admin Panel</a>
                        </li>
                    <?php endif; ?>
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Dashboard Content -->
    <div class="container mt-5">
        <h2>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h2>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <!-- Statistics Cards -->
        <div class="row mt-4">
            <div class="col-md-3">
                <div class="card dashboard-card">
                    <div class="card-body">
                        <h5 class="card-title">Total Sessions</h5>
                        <p class="card-text display-4"><?php echo $stats['total_sessions']; ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card dashboard-card">
                    <div class="card-body">
                        <h5 class="card-title">Total Generations</h5>
                        <p class="card-text display-4"><?php echo $stats['total_generations']; ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card dashboard-card">
                    <div class="card-body">
                        <h5 class="card-title">Highest Score</h5>
                        <p class="card-text display-4"><?php echo $stats['highest_score']; ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card dashboard-card">
                    <div class="card-body">
                        <h5 class="card-title">Average Score</h5>
                        <p class="card-text display-4"><?php echo round($stats['average_score'], 1); ?></p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Recent Sessions -->
        <div class="card mt-4">
            <div class="card-header">
                <h4>Recent Game Sessions</h4>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Start Time</th>
                                <th>End Time</th>
                                <th>Generations</th>
                                <th>Score</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($sessions as $session): ?>
                                <tr>
                                    <td><?php echo date('Y-m-d H:i:s', strtotime($session['start_time'])); ?></td>
                                    <td><?php echo $session['end_time'] ? date('Y-m-d H:i:s', strtotime($session['end_time'])) : 'In Progress'; ?></td>
                                    <td><?php echo $session['generations']; ?></td>
                                    <td><?php echo $session['score']; ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</body>
</html> 