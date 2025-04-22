<?php
require_once 'config.php';

// Check if user is logged in
$isLoggedIn = isLoggedIn();
$isAdmin = isAdmin();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Conway's Game of Life</title>
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
                        <a class="nav-link active" href="index.php">Game</a>
                    </li>
                    <?php if ($isLoggedIn): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="dashboard.php">Dashboard</a>
                        </li>
                        <?php if ($isAdmin): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="admin.php">Admin Panel</a>
                            </li>
                        <?php endif; ?>
                        <li class="nav-item">
                            <a class="nav-link" href="logout.php">Logout</a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="login.php">Login</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="register.php">Register</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container game-container">
        <!-- Stats Panel -->
        <div class="stats-panel">
            <div class="row">
                <div class="col-md-6">
                    <h5>Generation: <span id="generation">0</span></h5>
                </div>
                <div class="col-md-6">
                    <h5>Population: <span id="population">0</span></h5>
                </div>
            </div>
        </div>

        <!-- Game Grid -->
        <div id="game-container"></div>

        <!-- Control Panel -->
        <div class="control-panel">
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="preset" class="form-label">Load Pattern</label>
                        <select id="preset" class="form-select">
                            <option value="0">Select a preset</option>
                            <option value="block">Block</option>
                            <option value="boat">Boat</option>
                            <option value="beehive">Beehive</option>
                            <option value="blinker">Blinker</option>
                            <option value="beacon">Beacon</option>
                            <option value="glider">Glider</option>
                            <option value="gosper">Gosper Glider Gun</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="speed-control">
                        <label for="speed" class="form-label">Animation Speed</label>
                        <input type="range" class="form-range" id="speed" min="1" max="10" value="5">
                    </div>
                </div>
            </div>
            <div class="row mt-3">
                <div class="col-12">
                    <button id="start" class="btn btn-primary btn-control">Start Game</button>
                    <button id="stop" class="btn btn-danger btn-control">Stop Game</button>
                    <button id="step" class="btn btn-info btn-control">Next Generation</button>
                    <button id="fastForward" class="btn btn-warning btn-control">Fast Forward 23</button>
                    <button id="reset" class="btn btn-secondary btn-control">Reset</button>
                </div>
            </div>
        </div>
    </div>

    <script src="game.js"></script>
</body>
</html> 