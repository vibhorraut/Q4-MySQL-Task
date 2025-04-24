<?php
// Include database configuration
require_once 'config.php';

// Connect to the database
$conn = getDbConnection();
if (!$conn) {
    $connectionError = "Failed to connect to the database. Please check your configuration.";
}

// Initialize variables
$users = [];
$products = [];
$message = '';
$messageType = '';

// Get user ID from URL parameter or form submission
$selectedUserId = isset($_POST['user_id']) ? intval($_POST['user_id']) : 
                 (isset($_GET['user_id']) ? intval($_GET['user_id']) : 0);

// Function to get all users
function getAllUsers($conn) {
    $sql = "SELECT id, name, email FROM users ORDER BY name";
    $result = $conn->query($sql);
    
    if (!$result) {
        throw new Exception("Failed to fetch users: " . $conn->error);
    }
    
    return $result;
}

// Function to get all products
function getAllProducts($conn) {
    $sql = "SELECT id, name, price FROM products ORDER BY name";
    $result = $conn->query($sql);
    
    if (!$result) {
        throw new Exception("Failed to fetch products: " . $conn->error);
    }
    
    return $result;
}

// Function to create a new order
function createOrder($conn, $userId, $items) {
    // Start transaction
    $conn->begin_transaction();
    
    try {
        // Insert order record
        $stmt = $conn->prepare("INSERT INTO orders (user_id, order_date) VALUES (?, NOW())");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        
        if ($stmt->affected_rows !== 1) {
            throw new Exception("Failed to create order");
        }
        
        $orderId = $conn->insert_id;
        $stmt->close();
        
        // Insert order items
        $stmt = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
        
        foreach ($items as $item) {
            $stmt->bind_param("iiid", $orderId, $item['product_id'], $item['quantity'], $item['price']);
            $stmt->execute();
            
            if ($stmt->affected_rows !== 1) {
                throw new Exception("Failed to add item to order");
            }
        }
        
        $stmt->close();
        
        // Commit transaction
        $conn->commit();
        return $orderId;
    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        throw $e;
    }
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {
    try {
        // Validate user ID
        if (empty($_POST['user_id'])) {
            throw new Exception("Please select a customer");
        }
        
        $userId = intval($_POST['user_id']);
        
        // Validate items
        if (empty($_POST['product']) || !is_array($_POST['product'])) {
            throw new Exception("Please add at least one product to the order");
        }
        
        // Prepare order items
        $items = [];
        $productIds = $_POST['product'];
        $quantities = $_POST['quantity'];
        $prices = $_POST['price'];
        
        foreach ($productIds as $index => $productId) {
            if (empty($productId)) continue;
            
            $quantity = isset($quantities[$index]) ? intval($quantities[$index]) : 0;
            $price = isset($prices[$index]) ? floatval($prices[$index]) : 0;
            
            if ($quantity <= 0) {
                throw new Exception("Quantity must be greater than zero");
            }
            
            if ($price <= 0) {
                throw new Exception("Price must be greater than zero");
            }
            
            $items[] = [
                'product_id' => intval($productId),
                'quantity' => $quantity,
                'price' => $price
            ];
        }
        
        if (empty($items)) {
            throw new Exception("Please add at least one product to the order");
        }
        
        // Create the order
        $orderId = createOrder($conn, $userId, $items);
        
        // Set success message
        $message = "Order #$orderId has been created successfully!";
        $messageType = "success";
        
        // Reset form
        $selectedUserId = 0;
    } catch (Exception $e) {
        $message = $e->getMessage();
        $messageType = "danger";
    }
}

// Fetch users and products if connection is successful
if (!isset($connectionError)) {
    try {
        $usersResult = getAllUsers($conn);
        while ($row = $usersResult->fetch_assoc()) {
            $users[] = $row;
        }
        
        // Fetch products from the database
        $productsResult = getAllProducts($conn);
        while ($row = $productsResult->fetch_assoc()) {
            $products[] = $row;
        }
        
        // If no products were found, use sample data as fallback
        if (empty($products)) {
            $products = [
                ['id' => 1, 'name' => 'Laptop', 'price' => 999.99],
                ['id' => 2, 'name' => 'Smartphone', 'price' => 499.99],
                ['id' => 3, 'name' => 'Headphones', 'price' => 99.99],
                ['id' => 4, 'name' => 'Tablet', 'price' => 349.99],
                ['id' => 5, 'name' => 'Smartwatch', 'price' => 199.99]
            ];
        }
    } catch (Exception $e) {
        $connectionError = $e->getMessage();
    }
}

// Set page title
$pageTitle = "Create New Order";
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
        .order-form {
            background-color: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
        }
        .product-row {
            background-color: #fff;
            border-radius: 5px;
            padding: 15px;
            margin-bottom: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        .remove-product {
            color: #dc3545;
            cursor: pointer;
        }
        .add-product-btn {
            margin-bottom: 20px;
        }
        .order-summary {
            background-color: #e9ecef;
            border-radius: 5px;
            padding: 15px;
            margin-top: 20px;
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
        <?php else: ?>
            
            <?php if (!empty($message)): ?>
                <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
                    <?php echo htmlspecialchars($message); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h3 class="card-title mb-0">Order Details</h3>
                </div>
                <div class="card-body">
                    <form id="orderForm" method="post" action="">
                        <!-- Customer Selection -->
                        <div class="mb-4">
                            <label for="user_id" class="form-label">Select Customer</label>
                            <select class="form-select" id="user_id" name="user_id" required>
                                <option value="">-- Select a Customer --</option>
                                <?php foreach ($users as $user): ?>
                                    <option value="<?php echo $user['id']; ?>" <?php echo ($selectedUserId == $user['id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($user['name']); ?> (<?php echo htmlspecialchars($user['email']); ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <h4 class="mb-3">Order Items</h4>
                        
                        <!-- Product Items Container -->
                        <div id="productItems">
                            <!-- Initial product row (template) -->
                            <div class="product-row">
                                <div class="row">
                                    <div class="col-md-5 mb-2">
                                        <label class="form-label">Product</label>
                                        <select class="form-select product-select" name="product[]" required>
                                            <option value="">-- Select a Product --</option>
                                            <?php foreach ($products as $product): ?>
                                                <option value="<?php echo $product['id']; ?>" data-price="<?php echo $product['price']; ?>">
                                                    <?php echo htmlspecialchars($product['name']); ?> - ₹<?php echo number_format($product['price'], 2); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-2 mb-2">
                                        <label class="form-label">Quantity</label>
                                        <input type="number" class="form-control quantity-input" name="quantity[]" min="1" value="1" required>
                                    </div>
                                    <div class="col-md-3 mb-2">
                                        <label class="form-label">Price</label>
                                        <div class="input-group">
                                            <span class="input-group-text">₹</span>
                                            <input type="number" class="form-control price-input" name="price[]" step="0.01" min="0.01" required>
                                        </div>
                                    </div>
                                    <div class="col-md-2 mb-2 d-flex align-items-end">
                                        <button type="button" class="btn btn-danger remove-product-btn">
                                            <i class="fas fa-trash"></i> Remove
                                        </button>
                                    </div>
                                </div>
                                <div class="row mt-2">
                                    <div class="col-md-12">
                                        <div class="subtotal text-end">
                                            Subtotal: <span class="item-subtotal">₹0.00</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Add Product Button -->
                        <button type="button" class="btn btn-success add-product-btn" id="addProductBtn">
                            <i class="fas fa-plus"></i> Add Another Product
                        </button>
                        
                        <!-- Order Summary -->
                        <div class="order-summary">
                            <div class="row">
                                <div class="col-md-6">
                                    <h5>Order Summary</h5>
                                </div>
                                <div class="col-md-6 text-end">
                                    <h5>Total: ₹<span id="orderTotal">0.00</span></h5>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Submit Button -->
                        <div class="text-end mt-4">
                            <button type="submit" name="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-check"></i> Create Order
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Product Row Template (hidden) -->
    <template id="productRowTemplate">
        <div class="product-row">
            <div class="row">
                <div class="col-md-5 mb-2">
                    <label class="form-label">Product</label>
                    <select class="form-select product-select" name="product[]" required>
                        <option value="">-- Select a Product --</option>
                        <?php foreach ($products as $product): ?>
                            <option value="<?php echo $product['id']; ?>" data-price="<?php echo $product['price']; ?>">
                                <?php echo htmlspecialchars($product['name']); ?> - ₹<?php echo number_format($product['price'], 2); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2 mb-2">
                    <label class="form-label">Quantity</label>
                    <input type="number" class="form-control quantity-input" name="quantity[]" min="1" value="1" required>
                </div>
                <div class="col-md-3 mb-2">
                    <label class="form-label">Price</label>
                    <div class="input-group">
                        <span class="input-group-text">₹</span>
                        <input type="number" class="form-control price-input" name="price[]" step="0.01" min="0.01" required>
                    </div>
                </div>
                <div class="col-md-2 mb-2 d-flex align-items-end">
                    <button type="button" class="btn btn-danger remove-product-btn">
                        <i class="fas fa-trash"></i> Remove
                    </button>
                </div>
            </div>
            <div class="row mt-2">
                <div class="col-md-12">
                    <div class="subtotal text-end">
                        Subtotal: <span class="item-subtotal">₹0.00</span>
                    </div>
                </div>
            </div>
        </div>
    </template>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JavaScript -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize the first product row
            initProductRow(document.querySelector('.product-row'));
            
            // Add Product Button
            document.getElementById('addProductBtn').addEventListener('click', function() {
                const template = document.getElementById('productRowTemplate');
                const clone = template.content.cloneNode(true);
                document.getElementById('productItems').appendChild(clone);
                
                // Initialize the new product row
                initProductRow(document.querySelector('#productItems .product-row:last-child'));
                
                // Update order total
                updateOrderTotal();
            });
            
            // Form submission validation
            document.getElementById('orderForm').addEventListener('submit', function(e) {
                const productSelects = document.querySelectorAll('.product-select');
                const selectedProducts = new Set();
                let hasDuplicates = false;
                
                productSelects.forEach(select => {
                    const value = select.value;
                    if (value && selectedProducts.has(value)) {
                        hasDuplicates = true;
                    }
                    selectedProducts.add(value);
                });
                
                if (hasDuplicates) {
                    alert('Please avoid selecting the same product multiple times. Instead, adjust the quantity.');
                    e.preventDefault();
                }
            });
        });
        
        // Initialize a product row with event listeners
        function initProductRow(row) {
            const productSelect = row.querySelector('.product-select');
            const quantityInput = row.querySelector('.quantity-input');
            const priceInput = row.querySelector('.price-input');
            const removeBtn = row.querySelector('.remove-product-btn');
            const subtotalSpan = row.querySelector('.item-subtotal');
            
            // Set initial price when product is selected
            productSelect.addEventListener('change', function() {
                const selectedOption = this.options[this.selectedIndex];
                if (selectedOption.value) {
                    const price = selectedOption.getAttribute('data-price');
                    priceInput.value = price;
                    updateSubtotal(row);
                } else {
                    priceInput.value = '';
                    subtotalSpan.textContent = '₹0.00';
                }
                updateOrderTotal();
            });
            
            // Update subtotal when quantity or price changes
            quantityInput.addEventListener('input', function() {
                updateSubtotal(row);
                updateOrderTotal();
            });
            
            priceInput.addEventListener('input', function() {
                updateSubtotal(row);
                updateOrderTotal();
            });
            
            // Remove product row
            removeBtn.addEventListener('click', function() {
                // Don't remove if it's the only product row
                const productRows = document.querySelectorAll('.product-row');
                if (productRows.length > 1) {
                    row.remove();
                    updateOrderTotal();
                } else {
                    alert('At least one product is required.');
                }
            });
        }
        
        // Update subtotal for a product row
        function updateSubtotal(row) {
            const quantity = parseFloat(row.querySelector('.quantity-input').value) || 0;
            const price = parseFloat(row.querySelector('.price-input').value) || 0;
            const subtotal = quantity * price;
            row.querySelector('.item-subtotal').textContent = '₹' + subtotal.toFixed(2);
        }
        
        // Update order total
        function updateOrderTotal() {
            const subtotals = document.querySelectorAll('.item-subtotal');
            let total = 0;
            
            subtotals.forEach(function(subtotalSpan) {
                const subtotalText = subtotalSpan.textContent.replace('₹', '');
                const subtotal = parseFloat(subtotalText) || 0;
                total += subtotal;
            });
            
            document.getElementById('orderTotal').textContent = total.toFixed(2);
        }
    </script>
</body>
</html>

<?php
// Close the database connection
if (isset($conn)) {
    $conn->close();
}
?>