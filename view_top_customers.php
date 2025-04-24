<?php
// Include database configuration
require_once 'config.php';

// Connect to the database
$conn = getDbConnection();
if (!$conn) {
    $connectionError = "Failed to connect to the database. Please check your configuration.";
}

// Function to execute the optimized query
function getTopCustomers($conn) {
    $sql = "SELECT 
                u.id AS user_id,
                u.name AS user_name,
                u.email AS user_email,
                COUNT(DISTINCT o.id) AS purchase_count,
                SUM(oi.quantity * oi.price) AS total_spending
            FROM 
                users u
            JOIN 
                orders o ON u.id = o.user_id
            JOIN 
                order_items oi ON o.id = oi.order_id
            WHERE 
                o.order_date >= DATE_SUB(CURRENT_DATE(), INTERVAL 6 MONTH)
            GROUP BY 
                u.id, u.name, u.email
            HAVING 
                COUNT(DISTINCT o.id) >= 3
            ORDER BY 
                total_spending DESC";
    
    $result = $conn->query($sql);
    
    if (!$result) {
        throw new Exception("Query failed: " . $conn->error);
    }
    
    return $result;
}

// Set page title
$pageTitle = "Top Customers - Last 6 Months";

// Get results if connection is successful
$results = null;
$error = null;

if (!isset($connectionError)) {
    try {
        $results = getTopCustomers($conn);
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle); ?></title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Custom CSS -->
    <style>
        .customer-card {
            transition: transform 0.2s;
        }
        .customer-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        .spending-high {
            color: #28a745;
            font-weight: bold;
        }
        .table-responsive {
            overflow-x: auto;
        }
        .stats-card {
            background-color: #f8f9fa;
            border-left: 4px solid #0d6efd;
        }
    </style>
</head>
<body>
    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1><?php echo htmlspecialchars($pageTitle); ?></h1>
            <a href="create_order.php" class="btn btn-success">
                <i class="fas fa-plus"></i> Create New Order
            </a>
        </div>
        
        <?php if (isset($connectionError)): ?>
            <?php displayError($connectionError); ?>
        <?php elseif (isset($error)): ?>
            <?php displayError($error); ?>
        <?php else: ?>
            
            <!-- Summary Stats -->
            <div class="row mb-4">
                <?php
                if ($results && $results->num_rows > 0) {
                    // Calculate summary statistics
                    $totalCustomers = $results->num_rows;
                    $totalSpending = 0;
                    $maxSpending = 0;
                    $avgPurchases = 0;
                    $totalPurchases = 0;
                    
                    // Clone the result set for calculations
                    $statsResults = $results;
                    while ($row = $statsResults->fetch_assoc()) {
                        $totalSpending += $row['total_spending'];
                        $maxSpending = max($maxSpending, $row['total_spending']);
                        $totalPurchases += $row['purchase_count'];
                    }
                    
                    $avgSpending = $totalCustomers > 0 ? $totalSpending / $totalCustomers : 0;
                    $avgPurchases = $totalCustomers > 0 ? $totalPurchases / $totalCustomers : 0;
                    
                    // Reset pointer for the main display
                    $results->data_seek(0);
                ?>
                <div class="col-md-3">
                    <div class="card stats-card">
                        <div class="card-body">
                            <h5 class="card-title">Total Customers</h5>
                            <p class="card-text display-6"><?php echo $totalCustomers; ?></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card stats-card">
                        <div class="card-body">
                            <h5 class="card-title">Total Spending</h5>
                            <p class="card-text display-6">₹<?php echo number_format($totalSpending, 2); ?></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card stats-card">
                        <div class="card-body">
                            <h5 class="card-title">Avg. Spending</h5>
                            <p class="card-text display-6">₹<?php echo number_format($avgSpending, 2); ?></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card stats-card">
                        <div class="card-body">
                            <h5 class="card-title">Avg. Purchases</h5>
                            <p class="card-text display-6"><?php echo number_format($avgPurchases, 1); ?></p>
                        </div>
                    </div>
                </div>
                <?php } ?>
            </div>
            
            <!-- Results Table -->
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h3 class="card-title mb-0">High-Value Customers (3+ purchases in last 6 months)</h3>
                </div>
                <div class="card-body">
                    <?php if ($results && $results->num_rows > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Rank</th>
                                        <th>Customer ID</th>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Purchases</th>
                                        <th>Total Spending</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $rank = 1;
                                    while ($row = $results->fetch_assoc()): 
                                        $spendingClass = $rank <= 3 ? 'spending-high' : '';
                                    ?>
                                        <tr>
                                            <td><?php echo $rank++; ?></td>
                                            <td><?php echo htmlspecialchars($row['user_id']); ?></td>
                                            <td>
                                                <a href="customer_details.php?id=<?php echo $row['user_id']; ?>">
                                                    <?php echo htmlspecialchars($row['user_name']); ?>
                                                </a>
                                            </td>
                                            <td><?php echo htmlspecialchars($row['user_email']); ?></td>
                                            <td><?php echo htmlspecialchars($row['purchase_count']); ?></td>
                                            <td class="<?php echo $spendingClass; ?>">₹<?php echo number_format($row['total_spending'], 2); ?></td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info">
                            No customers found with 3 or more purchases in the last 6 months.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Export Options -->
            <div class="card">
                <div class="card-header bg-secondary text-white">
                    <h3 class="card-title mb-0">Export Options</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <a href="#" class="btn btn-outline-primary w-100 mb-2">Export to CSV</a>
                        </div>
                        <div class="col-md-4">
                            <a href="#" class="btn btn-outline-primary w-100 mb-2">Export to Excel</a>
                        </div>
                        <div class="col-md-4">
                            <a href="#" class="btn btn-outline-primary w-100 mb-2">Print Report</a>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
// Close the database connection
if (isset($conn)) {
    $conn->close();
}
?>