<?php

require_once __DIR__ . '/db.php';
require __DIR__ . '/../../../vendor/autoload.php';

use Kreait\Firebase\Factory;
use Kreait\Firebase\Exception\Auth\UserNotFound;

try {
    echo "========== CREATING SAMPLE STUDENT WITH FIREBASE ==========\n\n";
    
    $email = 'samplestudent@gmail.com';
    $password = 'student123';
    $schoolId = 19999999;
    
    // Initialize Firebase Admin SDK
    $firebaseSecret = getenv('FIREBASE_SECRET');
    if (!$firebaseSecret) {
        throw new Exception("FIREBASE_SECRET environment variable not set");
    }
    
    $factory = (new Factory)->withServiceAccount($firebaseSecret);
    $auth = $factory->createAuth();
    
    echo "🔐 Firebase Admin SDK initialized\n\n";
    
    // Check if email already exists in database
    $checkUser = $conn->prepare("SELECT id, firebase_uid FROM users WHERE email = ?");
    $checkUser->execute([$email]);
    $existingUser = $checkUser->fetch(PDO::FETCH_ASSOC);
    
    if ($existingUser) {
        echo "⚠️  Email exists in database. Attempting to delete and recreate...\n";
        $deleteUserId = $existingUser['id'];
        
        // Delete from members table
        $deleteMember = $conn->prepare("DELETE FROM members WHERE email = ?");
        $deleteMember->execute([$email]);
        echo "✓ Deleted from members table\n";
        
        // Delete from users table
        $deleteUser = $conn->prepare("DELETE FROM users WHERE id = ?");
        $deleteUser->execute([$deleteUserId]);
        echo "✓ Deleted from users table\n";
        
        // Try to delete from Firebase if firebase_uid exists
        if ($existingUser['firebase_uid']) {
            try {
                $auth->deleteUser($existingUser['firebase_uid']);
                echo "✓ Deleted from Firebase\n";
            } catch (UserNotFound $e) {
                echo "⚠️  Firebase user not found (already deleted)\n";
            }
        }
        echo "\n";
    }
    
    // Create Firebase user
    echo "📝 Creating Firebase user...\n";
    $firebaseUser = $auth->createUser([
        'email' => $email,
        'password' => $password,
        'displayName' => 'Sample Student'
    ]);
    $firebaseUid = $firebaseUser->uid;
    echo "✓ Firebase user created with UID: $firebaseUid\n\n";
    
    // Get max IDs for new records
    $userMaxResult = $conn->query('SELECT MAX(id) as max_id FROM users');
    $userMaxData = $userMaxResult->fetch(PDO::FETCH_ASSOC);
    $nextUserId = ($userMaxData['max_id'] ?? 0) + 1;
    
    $memberMaxResult = $conn->query('SELECT MAX(id) as max_id FROM members');
    $memberMaxData = $memberMaxResult->fetch(PDO::FETCH_ASSOC);
    $nextMemberId = ($memberMaxData['max_id'] ?? 0) + 1;
    
    echo "📋 IDs: User=$nextUserId, Member=$nextMemberId\n\n";
    
    // Hash password for MySQL (bcrypt)
    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
    
    // Insert User with Firebase UID
    $insertUser = $conn->prepare("
        INSERT INTO users (id, name, email, email_verified_at, firebase_uid, id_school_number, password, role, remember_token, created_at, updated_at)
        VALUES (?, ?, ?, NULL, ?, ?, ?, 'user', NULL, NOW(), NOW())
    ");
    
    $insertUser->execute([
        $nextUserId,
        'Sample Student',
        $email,
        $firebaseUid,
        $schoolId,
        $hashedPassword
    ]);
    
    echo "✓ User created in database (ID: $nextUserId)\n";
    echo "  - Email: $email\n";
    echo "  - Firebase UID: $firebaseUid\n\n";
    
    // Insert Member
    $insertMember = $conn->prepare("
        INSERT INTO members (id, first_name, last_name, suffix, id_school_number, email, birth_date, enrollment_date, program, year, is_paid, created_at, updated_at)
        VALUES (?, ?, ?, NULL, ?, ?, ?, ?, ?, 1, 0, NOW(), NOW())
    ");
    
    $insertMember->execute([
        $nextMemberId,
        'Sample',
        'Student',
        $schoolId,
        $email,
        '2005-01-15',
        '2024-09-01',
        'BSCS'
    ]);
    
    echo "✓ Member created in database (ID: $nextMemberId)\n\n";
    
    echo "========== LOGIN CREDENTIALS ==========\n";
    echo "Email: $email\n";
    echo "Password: $password\n";
    echo "School ID: $schoolId\n";
    echo "Role: Student (user)\n";
    echo "Firebase UID: $firebaseUid\n";
    echo "========================================\n";
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    if (isset($firebaseUser)) {
        // Try to cleanup Firebase user if database insert fails
        try {
            $auth->deleteUser($firebaseUser->uid);
            echo "   Cleaned up Firebase user\n";
        } catch (Exception $cleanupError) {
            echo "   Could not cleanup Firebase user: " . $cleanupError->getMessage() . "\n";
        }
    }
}
?>
