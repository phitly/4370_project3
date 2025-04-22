<?php
require_once 'config.php';

// Redirect if not admin
if (!isAdmin()) {
    redirect('index.php');
}

$error = '';
$success = '';

try {
    $conn = getDBConnection();
    
    // Handle user actions
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $action = $_POST['action'] ?? '';
        $user_id = $_POST['user_id'] ?? 0;
        
        switch ($action) {
            case 'delete_user':
                $stmt = $conn->prepare("DELETE FROM users WHERE id = ? AND id != ?");
                $stmt->execute([$user_id, $_SESSION['user_id']]);
                $success = 'User deleted successfully';
                break;
                
            case 'make_admin':
                $stmt = $conn->prepare("UPDATE users SET is_admin = TRUE WHERE id = ?");
                $stmt->execute([$user_id]);
                $success = 'User promoted to admin';
                break;
                
            case 'remove_admin':
                $stmt = $conn->prepare("UPDATE users SET is_admin = FALSE WHERE id = ? AND id != ?");
                $stmt->execute([$user_id, $_SESSION['user_id']]);
                $success = 'Admin privileges removed';
                break;
        }
    }
    
    // Get all users
    $stmt = $conn->prepare("
        SELECT id, username, email, created_at, is_admin
        FROM users
        ORDER BY created_at DESC
    ");
    $stmt->execute();
    $users = $stmt->fetchAll();
    
    // Get all game sessions
    $stmt = $conn->prepare("
        SELECT g.session_id, u.username, g.start_time, g.end_time, g.generations, g.score
        FROM game_sessions g
        JOIN users u ON g.user_id = u.id
        ORDER BY g.start_time DESC
        LIMIT 100
    ");
    $stmt->execute();
    $sessions = $stmt->fetchAll();
    
} catch (PDOException $e) {
    $error = 'Database error: ' . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Game of Life</title>
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
                        <a class="nav-link" href="dashboard.php">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="admin.php">Admin Panel</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Admin Panel Content -->
    <div class="container mt-5">
        <h2>Admin Panel</h2>
        
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>
        
        <!-- Users Management -->
        <div class="card mt-4">
            <div class="card-header">
                <h4>User Management</h4>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Username</th>
                                <th>Email</th>
                                <th>Joined</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $user): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($user['username']); ?></td>
                                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                                    <td><?php echo date('Y-m-d', strtotime($user['created_at'])); ?></td>
                                    <td><?php echo $user['is_admin'] ? 'Admin' : 'User'; ?></td>
                                    <td>
                                        <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                            <form method="POST" style="display: inline;">
                                                <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                                <?php if ($user['is_admin']): ?>
                                                    <input type="hidden" name="action" value="remove_admin">
                                                    <button type="submit" class="btn btn-warning btn-sm">Remove Admin</button>
                                                <?php else: ?>
                                                    <input type="hidden" name="action" value="make_admin">
                                                    <button type="submit" class="btn btn-success btn-sm">Make Admin</button>
                                                <?php endif; ?>
                                            </form>
                                            <form method="POST" style="display: inline;">
                                                <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                                <input type="hidden" name="action" value="delete_user">
                                                <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                                            </form>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <!-- Game Sessions -->
        <div class="card mt-4">
            <div class="card-header">
                <h4>Game Sessions</h4>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>User</th>
                                <th>Start Time</th>
                                <th>End Time</th>
                                <th>Generations</th>
                                <th>Score</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($sessions as $session): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($session['username']); ?></td>
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