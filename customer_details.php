<?php
// Include database configuration
require_once 'config.php';

// Connect to the database
$conn = getDbConnection();
if (!$conn) {
    $connectionError = "Failed to connect to the database. Please check your configuration.";
}

// Get customer ID from URL parameter
$customerId = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Function to get customer details
function getCustomerDetails($conn, $customerId) {
    $sql = "SELECT 
                u.id AS user_id,
                u.name AS user_name,
                u.email AS user_email,
                u.created_at,
                COUNT(DISTINCT o.id) AS purchase_count,
                SUM(oi.quantity * oi.price) AS total_spending,
                MIN(o.order_date) AS first_order_date,
                MAX(o.order_date) AS last_order_date,
                AVG(oi.quantity * oi.price) AS avg_order_value
            FROM 
                users u
            JOIN 
                orders o ON u.id = o.user_id
            JOIN 
                order_items oi ON o.id = oi.order_id
            WHERE 
                u.id = ?
            GROUP BY 
                u.id, u.name, u.email, u.created_at";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $customerId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        return null;
    }
    
    return $result->fetch_assoc();
}

// Function to get customer's recent orders
function getCustomerOrders($conn, $customerId, $limit = 10) {
    $sql = "SELECT 
                o.id AS order_id,
                o.order_date,
                COUNT(oi.id) AS item_count,
                SUM(oi.quantity) AS total_items,
                SUM(oi.quantity * oi.price) AS order_total
            FROM 
                orders o
            JOIN 
                order_items oi ON o.id = oi.order_id
            WHERE 
                o.user_id = ?
            GROUP BY 
                o.id, o.order_date
            ORDER BY 
                o.order_date DESC
            LIMIT ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $customerId, $limit);
    $stmt->execute();
    return $stmt->get_result();
}

// Get customer data if connection is successful
$customerData = null;
$customerOrders = null;
$error = null;

if (!isset($connectionError) && $customerId > 0) {
    try {
        $customerData = getCustomerDetails($conn, $customerId);
        if ($customerData) {
            $customerOrders = getCustomerOrders($conn, $customerId);
        } else {
            $error = "Customer not found";
        }
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

// Set page title
$pageTitle = $customerData ? "Customer Details: " . htmlspecialchars($customerData['user_name']) : "Customer Details";
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
        .customer-header {
            background-color: #f8f9fa;
            border-left: 4px solid #0d6efd;
            padding: 20px;
            margin-bottom: 20px;
        }
        .stat-card {
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            transition: transform 0.2s;
        }
        .stat-card:hover {
            transform: translateY(-5px);
        }
        .order-row {
            cursor: pointer;
            transition: background-color 0.2s;
        }
        .order-row:hover {
            background-color: #f1f1f1;
        }
        .back-link {
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="container mt-4">
        <a href="view_top_customers.php" class="back-link mb-3 d-inline-block">
            <i class="fas fa-arrow-left"></i> Back to Customer List
        </a>
        
        <h1 class="mb-4"><?php echo htmlspecialchars($pageTitle); ?></h1>
        
        <?php if (isset($connectionError)): ?>
            <?php displayError($connectionError); ?>
        <?php elseif (isset($error)): ?>
            <?php displayError($error); ?>
        <?php elseif ($customerId === 0): ?>
            <div class="alert alert-warning">
                No customer ID provided. Please select a customer from the <a href="view_top_customers.php">customer list</a>.
            </div>
        <?php elseif ($customerData): ?>
            
            <!-- Customer Header -->
            <div class="customer-header">
                <div class="row">
                    <div class="col-md-6">
                        <h2><?php echo htmlspecialchars($customerData['user_name']); ?></h2>
                        <p><i class="fas fa-envelope"></i> <?php echo htmlspecialchars($customerData['user_email']); ?></p>
                        <p><i class="fas fa-user-clock"></i> Customer since: <?php echo date('F j, Y', strtotime($customerData['created_at'])); ?></p>
                    </div>
                    <div class="col-md-6 text-md-end">
                        <h3>Customer ID: <?php echo htmlspecialchars($customerData['user_id']); ?></h3>
                        <p><i class="fas fa-shopping-bag"></i> Total Orders: <?php echo htmlspecialchars($customerData['purchase_count']); ?></p>
                        <p><i class="fas fa-calendar-alt"></i> Last Order: <?php echo date('F j, Y', strtotime($customerData['last_order_date'])); ?></p>
                        <a href="create_order.php?user_id=<?php echo $customerData['user_id']; ?>" class="btn btn-success mt-2">
                            <i class="fas fa-plus"></i> Create Order for This Customer
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- Customer Stats -->
            <div class="row mb-4">
                <div class="col-md-3 mb-3">
                    <div class="card stat-card bg-primary text-white h-100">
                        <div class="card-body text-center">
                            <h5 class="card-title">Total Spending</h5>
                            <p class="card-text display-6">₹<?php echo number_format($customerData['total_spending'], 2); ?></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card stat-card bg-success text-white h-100">
                        <div class="card-body text-center">
                            <h5 class="card-title">Avg. Order Value</h5>
                            <p class="card-text display-6">₹<?php echo number_format($customerData['avg_order_value'], 2); ?></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card stat-card bg-info text-white h-100">
                        <div class="card-body text-center">
                            <h5 class="card-title">First Purchase</h5>
                            <p class="card-text"><?php echo date('M j, Y', strtotime($customerData['first_order_date'])); ?></p>
                            <p class="card-text">
                                <?php 
                                $days = round((time() - strtotime($customerData['first_order_date'])) / (60 * 60 * 24));
                                echo $days . ' days ago';
                                ?>
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card stat-card bg-warning h-100">
                        <div class="card-body text-center">
                            <h5 class="card-title">Customer Age</h5>
                            <p class="card-text">
                                <?php 
                                $days = round((time() - strtotime($customerData['created_at'])) / (60 * 60 * 24));
                                echo $days . ' days';
                                ?>
                            </p>
                            <p class="card-text">
                                <?php echo round($days / 30) . ' months'; ?>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Recent Orders -->
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h3 class="card-title mb-0">Recent Orders</h3>
                </div>
                <div class="card-body">
                    <?php if ($customerOrders && $customerOrders->num_rows > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Order ID</th>
                                        <th>Date</th>
                                        <th>Items</th>
                                        <th>Quantity</th>
                                        <th>Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($order = $customerOrders->fetch_assoc()): ?>
                                        <tr class="order-row" data-order-id="<?php echo $order['order_id']; ?>">
                                            <td><?php echo htmlspecialchars($order['order_id']); ?></td>
                                            <td><?php echo date('M j, Y', strtotime($order['order_date'])); ?></td>
                                            <td><?php echo htmlspecialchars($order['item_count']); ?> products</td>
                                            <td><?php echo htmlspecialchars($order['total_items']); ?> items</td>
                                            <td>₹<?php echo number_format($order['order_total'], 2); ?></td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info">
                            No orders found for this customer.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Customer Insights -->
            <div class="card">
                <div class="card-header bg-secondary text-white">
                    <h3 class="card-title mb-0">Customer Insights</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h4>Purchase Frequency</h4>
                            <p>
                                <?php
                                $daysSinceFirst = max(1, round((time() - strtotime($customerData['first_order_date'])) / (60 * 60 * 24)));
                                $frequency = $daysSinceFirst / max(1, $customerData['purchase_count']);
                                echo 'Orders approximately every ' . round($frequency) . ' days';
                                ?>
                            </p>
                            
                            <h4 class="mt-4">Customer Value</h4>
                            <p>
                                <?php
                                $monthsSinceFirst = max(1, $daysSinceFirst / 30);
                                $monthlyValue = $customerData['total_spending'] / $monthsSinceFirst;
                                echo 'Average monthly spending: ₹' . number_format($monthlyValue, 2);
                                ?>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <h4>Recent Activity</h4>
                            <p>
                                <?php
                                $daysSinceLast = round((time() - strtotime($customerData['last_order_date'])) / (60 * 60 * 24));
                                echo 'Last purchase: ' . $daysSinceLast . ' days ago';
                                
                                // Determine activity status
                                if ($daysSinceLast <= 30) {
                                    echo '<span class="badge bg-success ms-2">Active</span>';
                                } elseif ($daysSinceLast <= 90) {
                                    echo '<span class="badge bg-warning ms-2">At Risk</span>';
                                } else {
                                    echo '<span class="badge bg-danger ms-2">Inactive</span>';
                                }
                                ?>
                            </p>
                            
                            <h4 class="mt-4">Recommended Actions</h4>
                            <ul>
                                <?php if ($daysSinceLast > 60): ?>
                                    <li>Send re-engagement email</li>
                                <?php endif; ?>
                                
                                <?php if ($customerData['purchase_count'] >= 5): ?>
                                    <li>Offer loyalty program enrollment</li>
                                <?php endif; ?>
                                
                                <?php if ($customerData['avg_order_value'] > 100): ?>
                                    <li>Target for premium product offers</li>
                                <?php endif; ?>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JavaScript -->
    <script>
        // Add click handler for order rows
        document.querySelectorAll('.order-row').forEach(row => {
            row.addEventListener('click', function() {
                const orderId = this.getAttribute('data-order-id');
                // In a real application, this would navigate to an order details page
                alert('Order details for Order #' + orderId + ' would open here');
            });
        });
    </script>
</body>
</html>

<?php
// Close the database connection
if (isset($conn)) {
    $conn->close();
}
?>