<?php
// Start the session
session_start();

// Include database connection
require_once "config/database.php";

// Set the content type to JSON
header('Content-Type: application/json');

// Function to clean input data
function clean_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Check if the request is POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get username and password from POST data
    $username = clean_input($_POST['username']);
    $password = $_POST['password']; // Don't clean this as it might interfere with hashing
    
    // Validate input
    if (empty($username) || empty($password)) {
        echo json_encode([
            'success' => false,
            'message' => 'Username and password are required'
        ]);
        exit;
    }
    
    // Prepare SQL statement to prevent SQL injection
    $sql = "SELECT * FROM user WHERE username = ?";
    $stmt = mysqli_prepare($db, $sql);
    mysqli_stmt_bind_param($stmt, "s", $username);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if (mysqli_num_rows($result) == 1) {
        $user = mysqli_fetch_assoc($result);
        
        // Verify password (assuming your password is hashed with password_hash)
        if (password_verify($password, $user['password'])) {
            // Password is correct, set session variables
            $_SESSION['loggedin'] = true;
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['level'] = $user['level'];

            // Remember me functionality
            if (isset($_POST['remember']) && $_POST['remember'] == 'on') {
                // Set cookies that expire in 30 days
                setcookie("user_login", $user['username'], time() + (86400 * 30), "/");
                // Don't store plain password in cookie, use a token instead
                $token = bin2hex(random_bytes(16));
                
                // Update token in database
                $update_sql = "UPDATE user SET remember_token = ? WHERE id = ?";
                $update_stmt = mysqli_prepare($db, $update_sql);
                mysqli_stmt_bind_param($update_stmt, "si", $token, $user['id']);
                mysqli_stmt_execute($update_stmt);
                
                // Store token in cookie
                setcookie("remember_token", $token, time() + (86400 * 30), "/");
            }
            
            echo json_encode([
                'success' => true,
                'message' => 'Login successful',
                'redirect' => $user['level'] == 'admin' ? 'admin/dashboard.php' : 'user_ui.php'
            ]);
        } else {
            // Password is incorrect
            echo json_encode([
                'success' => false,
                'message' => 'Invalid username or password'
            ]);
        }
    } else {
        // Username not found
        echo json_encode([
            'success' => false,
            'message' => 'Invalid username or password'
        ]);
    }
    
    // Close the statement
    mysqli_stmt_close($stmt);
} else {
    // Not a POST request
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method'
    ]);
}

// Close the database connection
mysqli_close($db);
?>
