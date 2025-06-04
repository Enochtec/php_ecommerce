<?php
require_once 'config.php';

if (!isLoggedIn()) {
    redirect('login.php');
}

$user_id = $_SESSION['user_id'];
$errors = [];
$success = '';

// Get user data
$stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = sanitize($_POST['first_name']);
    $last_name = sanitize($_POST['last_name']);
    $email = sanitize($_POST['email']);
    $phone = sanitize($_POST['phone']);
    $address = sanitize($_POST['address']);
    
    // Validation
    if (empty($first_name)) $errors[] = 'First name is required';
    if (empty($last_name)) $errors[] = 'Last name is required';
    if (empty($email)) $errors[] = 'Email is required';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Invalid email format';
    
    // Check if email is already taken by another user
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND user_id != ?");
    $stmt->execute([$email, $user_id]);
    if ($stmt->rowCount() > 0) {
        $errors[] = 'Email is already taken';
    }
    
    if (empty($errors)) {
        $stmt = $pdo->prepare("
            UPDATE users 
            SET first_name = ?, last_name = ?, email = ?, phone = ?, address = ?, updated_at = NOW() 
            WHERE user_id = ?
        ");
        if ($stmt->execute([$first_name, $last_name, $email, $phone, $address, $user_id])) {
            $success = 'Profile updated successfully!';
            
            // Update session data
            $_SESSION['email'] = $email;
        } else {
            $errors[] = 'Failed to update profile. Please try again.';
        }
    }
}

// Handle password change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $current_password = sanitize($_POST['current_password']);
    $new_password = sanitize($_POST['new_password']);
    $confirm_password = sanitize($_POST['confirm_password']);
    
    // Validation
    if (empty($current_password)) $errors[] = 'Current password is required';
    if (empty($new_password)) $errors[] = 'New password is required';
    if ($new_password !== $confirm_password) $errors[] = 'Passwords do not match';
    
    if (empty($errors)) {
        // Verify current password
        if (password_verify($current_password, $user['password'])) {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE user_id = ?");
            if ($stmt->execute([$hashed_password, $user_id])) {
                $success = 'Password changed successfully!';
            } else {
                $errors[] = 'Failed to change password. Please try again.';
            }
        } else {
            $errors[] = 'Current password is incorrect';
        }
    }
}
?>

<?php include 'includes/header.php'; ?>

<div class="container mx-auto px-4 py-8 max-w-6xl">
    <h2 class="text-2xl font-bold mb-6">My Profile</h2>
    
    <?php if (!empty($errors)): ?>
        <div class="mb-6">
            <?php foreach ($errors as $error): ?>
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-2" role="alert">
                    <?php echo $error; ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6" role="alert">
            <?php echo $success; ?>
        </div>
    <?php endif; ?>
    
    <div class="flex flex-col md:flex-row gap-6">
        <div class="w-full md:w-1/2">
            <div class="bg-white rounded-lg shadow-md mb-6 overflow-hidden">
                <div class="bg-gray-100 px-6 py-4 border-b">
                    <h3 class="font-semibold text-lg">Profile Information</h3>
                </div>
                <div class="p-6">
                    <form method="POST">
                        <div class="mb-4">
                            <label for="username" class="block text-gray-700 text-sm font-medium mb-2">Username</label>
                            <input type="text" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 bg-gray-100" 
                                   id="username" value="<?php echo htmlspecialchars($user['username']); ?>" disabled>
                        </div>
                        <div class="mb-4">
                            <label for="first_name" class="block text-gray-700 text-sm font-medium mb-2">First Name</label>
                            <input type="text" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" 
                                   id="first_name" name="first_name" value="<?php echo htmlspecialchars($user['first_name']); ?>" required>
                        </div>
                        <div class="mb-4">
                            <label for="last_name" class="block text-gray-700 text-sm font-medium mb-2">Last Name</label>
                            <input type="text" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" 
                                   id="last_name" name="last_name" value="<?php echo htmlspecialchars($user['last_name']); ?>" required>
                        </div>
                        <div class="mb-4">
                            <label for="email" class="block text-gray-700 text-sm font-medium mb-2">Email</label>
                            <input type="email" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" 
                                   id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                        </div>
                        <div class="mb-4">
                            <label for="phone" class="block text-gray-700 text-sm font-medium mb-2">Phone</label>
                            <input type="text" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" 
                                   id="phone" name="phone" value="<?php echo htmlspecialchars($user['phone']); ?>">
                        </div>
                        <div class="mb-4">
                            <label for="address" class="block text-gray-700 text-sm font-medium mb-2">Address</label>
                            <textarea class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" 
                                      id="address" name="address" rows="3"><?php echo htmlspecialchars($user['address']); ?></textarea>
                        </div>
                        <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                            Update Profile
                        </button>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="w-full md:w-1/2">
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <div class="bg-gray-100 px-6 py-4 border-b">
                    <h3 class="font-semibold text-lg">Change Password</h3>
                </div>
                <div class="p-6">
                    <form method="POST">
                        <div class="mb-4">
                            <label for="current_password" class="block text-gray-700 text-sm font-medium mb-2">Current Password</label>
                            <input type="password" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" 
                                   id="current_password" name="current_password" required>
                        </div>
                        <div class="mb-4">
                            <label for="new_password" class="block text-gray-700 text-sm font-medium mb-2">New Password</label>
                            <input type="password" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" 
                                   id="new_password" name="new_password" required>
                        </div>
                        <div class="mb-4">
                            <label for="confirm_password" class="block text-gray-700 text-sm font-medium mb-2">Confirm New Password</label>
                            <input type="password" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" 
                                   id="confirm_password" name="confirm_password" required>
                        </div>
                        <button type="submit" name="change_password" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                            Change Password
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>