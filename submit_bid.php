<?php
session_start();
require_once 'db.php';
require_once 'audit_logger.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'contractor') {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tender_id       = intval($_POST['tender_id'] ?? 0);
    $contractor_id   = intval($_SESSION['user_id']); // অথবা কন্ট্রাক্টর টেবিল আইডি
    $proposed_amount = floatval($_POST['proposed_amount'] ?? 0);
    $submission_date = date('Y-m-d');

    if ($tender_id > 0 && $proposed_amount > 0) {
        try {
            $stmt = $pdo->prepare(
                "INSERT INTO bid (Tender_ID, Contractor_ID, Proposed_Amount, Submission_Date) 
                 VALUES (?, ?, ?, ?)"
            );
            $stmt->execute([$tender_id, $contractor_id, $proposed_amount, $submission_date]);
            
            $bid_id = $pdo->lastInsertId();

            // Audit Log
            log_activity(
                $_SESSION['user_id'], 
                'SUBMIT_BID', 
                'bid', 
                $bid_id, 
                null, 
                "Submitted bid amount: {$proposed_amount} for Tender ID: {$tender_id}"
            );

database: "Location: dashboard.php?success=bid_submitted");
            exit();

        } catch (PDOException $e) {
            die("Error: " . $e->getMessage());
        }
    }
}
?>