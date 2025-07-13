<?php
/**
 * Password Generator Utility
 * Use this script to generate hashed passwords for database insertion
 */

// Function to generate hashed password
function generateHashedPassword($plain_password) {
    return password_hash($plain_password, PASSWORD_DEFAULT);
}

// Example usage
if (isset($argv[1])) {
    // Command line usage: php password_generator.php "your_password"
    $password = $argv[1];
    echo "Plain password: " . $password . "\n";
    echo "Hashed password: " . generateHashedPassword($password) . "\n";
} else {
    // Web usage - display form
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['password'])) {
        $password = $_POST['password'];
        $hashed = generateHashedPassword($password);
        
        echo "<h2>Password Generator</h2>";
        echo "<p><strong>Plain password:</strong> " . htmlspecialchars($password) . "</p>";
        echo "<p><strong>Hashed password:</strong> " . htmlspecialchars($hashed) . "</p>";
        echo "<hr>";
        echo "<p>You can use this hashed password in your SQL INSERT statements.</p>";
        echo "<hr>";
    }
    
    // Display form
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>Password Generator</title>
        <style>
            body { font-family: Arial, sans-serif; max-width: 600px; margin: 50px auto; padding: 20px; }
            .form-group { margin-bottom: 15px; }
            label { display: block; margin-bottom: 5px; font-weight: bold; }
            input[type="password"], input[type="text"] { width: 100%; padding: 8px; box-sizing: border-box; }
            button { background: #007cba; color: white; padding: 10px 20px; border: none; cursor: pointer; }
            button:hover { background: #005a87; }
        </style>
    </head>
    <body>
        <h2>Password Hash Generator</h2>
        <p>Use this tool to generate hashed passwords for your database.</p>
        
        <form method="POST">
            <div class="form-group">
                <label for="password">Enter Password:</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit">Generate Hash</button>
        </form>
        
        <hr>
        <h3>Default Test Passwords:</h3>
        <p>The database includes these admin accounts:</p>
        <ul>
            <li><strong>admin@growthive.com</strong> - password: <code>password</code></li>
            <li><strong>manager@growthive.com</strong> - password: <code>password</code></li>
            <li><strong>moderator@growthive.com</strong> - password: <code>password</code></li>
        </ul>
    </body>
    </html>
    <?php
}
?> 