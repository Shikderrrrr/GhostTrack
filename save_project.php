<?php
session_start();
require_once 'db.php'; // Database connection

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // ফর্ম থেকে আসা ডেটাগুলো রিসিভ করা
    $project_name       = $_POST['project_name'] ?? '';
    $ministry_id        = $_POST['newProjMinistry'] ?? $_POST['ministry_id'] ?? 1;
    $agency_id          = $_POST['agency_id'] ?? 1; // ডিফল্ট এজেন্সি আইডি ১ ধরা হলো
    $approved_budget    = $_POST['approved_budget'] ?? 0;
    $project_start_date = $_POST['project_start_date'] ?? date('Y-m-d');
    $approval_date      = date('Y-m-d'); // বর্তমান তারিখ অনুমোদন তারিখ হিসেবে
    $expected_end_date  = $_POST['expected_end_date'] ?? date('Y-m-d');
    $project_status     = $_POST['project_status'] ?? 'Ongoing';
    $scope_of_work      = $_POST['scope_of_work'] ?? ''; // কাজের বিবরণ / স্কোপ

    // সেশন থেকে লগইন করা অফিসারের আইডি নেওয়া (না থাকলে ডিফল্ট ১ নম্বর অফিসার)
    $created_by_officer = $_SESSION['officer_id'] ?? $_SESSION['user_id'] ?? 1;

    if (!empty($project_name) && !empty($approved_budget)) {
        try {
            $stmt = $pdo->prepare("
                INSERT INTO project (
                    Project_Name, Ministry_ID, Agency_ID, Approved_Budget, 
                    Project_Start_Date, Approval_Date, Expected_End_Date, 
                    Project_Status, Scope_Of_Work, Created_By_Officer_ID
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $project_name, 
                $ministry_id, 
                $agency_id, 
                $approved_budget, 
                $project_start_date, 
                $approval_date, 
                $expected_end_date, 
                $project_status, 
                $scope_of_work, 
                $created_by_officer
            ]);

            header("Location: dashboard.php?success=project_added");
            exit();
        } catch (PDOException $e) {
            echo "Error saving project: " . htmlspecialchars($e->getMessage());
        }
    } else {
        echo "Please fill in all required project fields.";
    }
}
?>