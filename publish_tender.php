<?php
session_start();
require_once 'db.php'; // Database connection

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // ফর্ম থেকে আসা ডেটাগুলো রিসিভ করা
    $package_id          = $_POST['package_id'] ?? '';
    $tender_reference    = $_POST['tender_reference'] ?? '';
    $publication_date    = $_POST['publication_date'] ?? date('Y-m-d');
    $submission_deadline = $_POST['submission_deadline'] ?? date('Y-m-d');
    $tender_status       = 'Open'; // নতুন পাবলিশ হওয়া টেন্ডার সবসময় Open থাকবে
    $work_category       = $_POST['work_category'] ?? 'Civil Works';

    // সেশন থেকে প্রকিউরমেন্ট অফিসারের আইডি নেওয়া (না থাকলে ডিফল্ট ২ নম্বর অফিসার)
    $publisher_officer_id = $_SESSION['officer_id'] ?? $_SESSION['user_id'] ?? 2;

    if (!empty($package_id) && !empty($tender_reference)) {
        try {
            // টেন্ডার টেবিলে ডেটা ইনসার্ট করা এবং Published_By_Officer_ID যুক্ত করা
            $stmt = $pdo->prepare("
                INSERT INTO tender (
                    Package_ID, Tender_Reference, Publication_Date, 
                    Submission_Deadline, Tender_Status, Work_Category, Published_By_Officer_ID
                ) VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $package_id, 
                $tender_reference, 
                $publication_date, 
                $submission_deadline, 
                $tender_status, 
                $work_category,
                $publisher_officer_id
            ]);

            header("Location: dashboard.php?success=tender_published");
            exit();
        } catch (PDOException $e) {
            echo "Error publishing tender: " . htmlspecialchars($e->getMessage());
        }
    } else {
        echo "Please fill in all required tender fields.";
    }
}
?>