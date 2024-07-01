<?php
require 'config.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

// ฟังก์ชันการค้นหาอุปกรณ์
$search_query = "";
if (isset($_POST['search'])) {
    $search_query = $_POST['search_query'];
    $items = $pdo->prepare('SELECT * FROM items WHERE item_name LIKE ?');
    $items->execute(["%$search_query%"]);
} else {
    $items = $pdo->query('SELECT * FROM items')->fetchAll();
}

if (isset($_GET['id'])) {
    $item_id = $_GET['id'];

    $stmt = $pdo->prepare('DELETE FROM items WHERE item_id = ?');
    $stmt->execute([$item_id]);

    header('Location: admin_manage_items.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Items</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/5.0.0-alpha1/css/bootstrap.min.css">
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

        .search-bar-container {
            display: flex;
            justify-content: flex-end;
            width: 100%;
            margin-bottom: 20px;
        }

        .search-bar form {
            display: flex;
            align-items: center;
        }

        .search-bar input {
            flex: 1;
            margin-right: 10px; /* ระยะห่างเล็กน้อยระหว่างกล่อง search และปุ่ม */
        }

        .add-item-button {
            margin-top: 20px;
        }
    </style>
</head>
<body>
<nav id="sidebar" class="col-md-3 col-lg-2 d-md-block sidebar collapse">
        <img src="sport_logo.jpg" alt="Logo">
        <div class="position-sticky">
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a class="nav-link" href="admin_dashboard.php">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-home">
                            <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                            <polyline points="9 22 9 12 15 12 15 22"></polyline>
                        </svg>
                        <span class="ml-2">Dashboard</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link " href="admin_user_management.php">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-users">
                            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                            <circle cx="9" cy="7" r="4"></circle>
                            <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                            <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                        </svg>
                        <span class="ml-2">User Management</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" href="admin_manage_items.php">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-package">
                            <polyline points="21 8 21 21 3 21 3 8"></polyline>
                            <rect x="1" y="3" width="22" height="5"></rect>
                            <line x1="10" y1="12" x2="14" y2="12"></line>
                        </svg>
                        <span class="ml-2">Manage Items</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="admin_manage_borrowings.php">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-file">
                            <path d="M13 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9z"></path>
                            <polyline points="13 2 13 9 20 9"></polyline>
                        </svg>
                        <span class="ml-2">Manage Borrowings</span>
                    </a>
                </li>
                <li class="nav-item">
                <a class="nav-link" href="admin_history.php">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-clock">
                        <circle cx="12" cy="12" r="10"></circle>
                        <polyline points="12 6 12 12 16 14"></polyline>
                    </svg>
                    <span class="ml-2">History</span>
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
        <div class="container mt-5  ">
            <h1>Manage Items</h1>
            <div class="search-bar mb-3">
                <form method="post" action="">
                    <input type="text" name="search_query" class="form-control" placeholder="Search items..." value="<?php echo htmlspecialchars($search_query); ?>">
                    <button type="submit" name="search" class="btn btn-primary">Search</button>
                </form>
            </div>
            <div class="table-container">
                <a href="admin_add_item.php" class="btn btn-success">Add New Item</a>
                <table class="table table-striped text-center">
                    <thead>
                        <tr>
                            <th>Item ID</th>
                            <th>Item Name</th>
                            <th>Quantity</th>
                            <th>Available</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($items as $item): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($item['item_id']); ?></td>
                            <td><?php echo htmlspecialchars($item['item_name']); ?></td>
                            <td><?php echo htmlspecialchars($item['quantity']); ?></td>
                            <td><?php echo htmlspecialchars($item['available']); ?></td>
                            <td class="actions">
                                <a href="admin_edit_item.php?id=<?php echo $item['item_id']; ?>" class="btn btn-primary">Edit</a>
                                <a href="admin_manage_items.php?id=<?php echo $item['item_id']; ?>" class="btn btn-danger">Delete</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
        </div>
    </div>
</body>
</html>
