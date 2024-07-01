<?php
require 'config.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch user details
$stmt = $pdo->prepare('SELECT username, email, created_at FROM users WHERE user_id = ?');
$stmt->execute([$user_id]);
$user = $stmt->fetch();

// Fetch borrowed items
$stmt = $pdo->prepare('SELECT b.*, i.item_name, r.returned_at 
                       FROM borrowings b 
                       JOIN items i ON b.item_id = i.item_id 
                       LEFT JOIN returns r ON b.borrowing_id = r.borrowing_id 
                       WHERE b.user_id = ?');
$stmt->execute([$user_id]);
$borrowed_items = $stmt->fetchAll();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Details</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/5.0.0-alpha1/css/bootstrap.min.css" integrity="sha384-r4NyP46KrjDleawBgD5tp8Y7UzmLA05oM1iAEQ17CSuDqnUK2+k9luXQOfXJCJ4I" crossorigin="anonymous">
    <style>
        .sidebar {
            position: fixed;
            top: 0;
            bottom: 0;
            left: 0;
            z-index: 100;
            padding: 0;
            box-shadow: inset -1px 0 0 rgba(0, 0, 0, .1);
            z-index: 99;
            background-color: #f8f9fa;
        }

        .sidebar img {
            display: block;
            margin: 10px auto 20px;
            width: 100%;
            max-width: 150px;
            height: auto;
        }

        @media (max-width: 767.98px) {
            .sidebar {
                top: 11.5rem;
                padding: 0;
            }
        }

        .navbar {
            box-shadow: inset 0 -1px 0 rgba(0, 0, 0, .1);
        }

        @media (min-width: 767.98px) {
            .navbar {
                top: 0;
                position: sticky;
                z-index: 999;
            }
        }

        .sidebar .nav-link {
            color: #333;
        }

        .sidebar .nav-link.active {
            color: #0d6efd;
        }

        .main-content {
            margin-left: 240px;
            padding: 15px;
        }

        .stat-card {
            padding: 20px;
            margin: 10px;
            background-color: #f8f9fa;
            border: 1px solid #ddd;
            border-radius: 5px;
            text-align: center;
            font-size: 1.2em;
            flex: 1;
            min-width: 150px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        .stat-card h2 {
            margin: 0;
            font-size: 2.5em;
        }

        .stat-card p {
            margin: 0;
            font-size: 1.5em;
            color: #666;
        }

        .stat-container {
            display: flex;
            justify-content: space-around;
            flex-wrap: nowrap;
            margin-bottom: 20px;
            overflow-x: auto;
        }

        .chart-container {
            margin: 0 auto;
            width: 80%;
            height: 30vh;
        }

        .chart-container canvas {
            max-width: 100%;
            height: 100%;
        }
    </style>
</head>
<body>
    <nav id="sidebar" class="col-md-3 col-lg-2 d-md-block sidebar collapse">
        <img src="sport_logo.jpg" alt="Logo">
        <div class="position-sticky">
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a class="nav-link" href="dashboard.php">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-home">
                            <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                            <polyline points="9 22 9 12 15 12 15 22"></polyline>
                        </svg>
                        <span class="ml-2">Dashboard</span>
                    </a>
                </li>
                
                <li class="nav-item">
                    <a class="nav-link" href="borrowing.php">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-file">
                            <path d="M13 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9z"></path>
                            <polyline points="13 2 13 9 20 9"></polyline>
                        </svg>
                        <span class="ml-2">Borrowing</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="returning.php">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-shopping-cart">
                            <circle cx="9" cy="21" r="1"></circle>
                            <circle cx="20" cy="21" r="1"></circle>
                            <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
                        </svg>
                        <span class="ml-2">Returning</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" href="user.php">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-users">
                            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                            <circle cx="9" cy="7" r="4"></circle>
                            <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                            <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                        </svg>
                        <span class="ml-2">Users</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="login.php">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-log-out">
                            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                            <polyline points="16 17 21 12 16 7"></polyline>
                            <line x1="21" y1="12" x2="9" y2="12"></line>
                        </svg>
                        <span class="ml-2">Logout</span>
                    </a>
                </li>
            </ul>
        </div>
    </nav>

    <div class="main-content">
        <div class="container mt-5">
            <h1>User Details</h1>
            <div class="mb-3">
                <label for="username" class="form-label">Username</label>
                <input type="text" class="form-control" id="username" value="<?php echo htmlspecialchars($user['username']); ?>" readonly>
            </div>
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control" id="email" value="<?php echo htmlspecialchars($user['email']); ?>" readonly>
            </div>
            <div class="mb-3">
                <label for="created_at" class="form-label">Registered On</label>
                <input type="text" class="form-control" id="created_at" value="<?php echo htmlspecialchars($user['created_at']); ?>" readonly>
            </div>

            <h2>Borrowed Items</h2>
            <table class="table">
                <thead>
                    <tr>
                        <th scope="col">Item Name</th>
                        <th scope="col">Borrowed Date</th>
                        <th scope="col">Due Date</th>
                        <th scope="col">Returned Date</th> 
                        <th scope="col">Status</th>
                        
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($borrowed_items)): ?>
                        <tr>
                            <td colspan="5">No borrowings found.</td> 
                        </tr>
                    <?php else: ?>
                        <?php foreach ($borrowed_items as $item): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($item['item_name']); ?></td>
                                <td><?php echo htmlspecialchars($item['borrowed_at']); ?></td>
                                <td><?php echo htmlspecialchars($item['due_date']); ?></td>
                                <td><?php echo htmlspecialchars($item['returned_at']); ?></td>
                                <td><?php echo htmlspecialchars($item['status']); ?></td>
                                
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js" integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js" integrity="sha384-pzjw8f+ua7Kw1TIq6t2KfKRe8F5NCSxVf4Jp53ULvoTx9WjEG6vPpcQfXc8+zOVe" crossorigin="anonymous"></script>
    <script src="https://stackpath.amazonaws.com/bootstrap/5.0.0-alpha1/js/bootstrap.min.js" integrity="sha384-o+RDsa0A1yQ6LZtXtIKqlY2+6aJ+gkiwAqbuK4Q1jFhAUVL+0cOzZBqwoqSfi2IQ" crossorigin="anonymous"></script>
</body>
</html>
