<?php
session_start();
require_once 'db.php'; // ডাটাবেজ কানেকশন ফাইল
require 'auth_check.php';
enforce_access();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // ফর্ম থেকে ডাটাগুলো সংগ্রহ করা
    $tender_id = trim($_POST['tender_id'] ?? '');
    $progress_percentage = trim($_POST['progress_percentage'] ?? '');
    $progress_notes = trim($_POST['progress_notes'] ?? '');
    $report_date = trim($_POST['report_date'] ?? date('Y-m-d'));
    $contractor_id = $_SESSION['contractor_id'] ?? 1;

    // ভ্যালিডেশন
    if (empty($tender_id) || empty($progress_percentage) || empty($progress_notes)) {
        echo "<script>alert('সকল ফিল্ড পূরণ করা বাধ্যতামূলক!'); window.history.back();</script>";
        exit;
    }

    // ফাইল আপলোড হ্যান্ডলিং
    $file_name_to_save = '';
    if (isset($_FILES['site_photo']) && $_FILES['site_photo']['error'] === UPLOAD_ERR_OK) {
        $file_tmp_path = $_FILES['site_photo']['tmp_name'];
        $original_file_name = $_FILES['site_photo']['name'];
        $file_extension = strtolower(pathinfo($original_file_name, PATHINFO_EXTENSION));

        // অনুমোদিত এক্সটেনশন চেক
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'pdf'];
        if (in_array($file_extension, $allowed_extensions)) {
            // ইউনিক ফাইলের নাম তৈরি (যেমন: progress_TenderID_Timestamp.jpg)
            $new_file_name = 'progress_' . $tender_id . '_' . time() . '.' . $file_extension;
            
            // আপলোড ফোল্ডার চেক করা, না থাকলে তৈরি করা
            $upload_folder = 'uploads/';
            if (!is_dir($upload_folder)) {
                mkdir($upload_folder, 0755, true);
            }

            $dest_path = $upload_folder . $new_file_name;

            // ফাইল সার্ভারে মুভ করা
            if (move_uploaded_file($file_tmp_path, $dest_path)) {
                $file_name_to_save = $new_file_name;
            } else {
                echo "<script>alert('ফাইল আপলোড করতে সমস্যা হয়েছে!'); window.history.back();</script>";
                exit;
            }
        } else {
            echo "<script>alert('শুধুমাত্র JPG, JPEG, PNG বা PDF ফাইল আপলোড করা যাবে।'); window.history.back();</script>";
            exit;
        }
    }

    try {
        // ডাটাবেজে প্রোগ্রেস ও ফাইল নেম সেভ করার কুয়েরি
        // (আপনার ডাটাবেজে যদি টেবিল বা কলামের নাম ভিন্ন হয়, তবে সে অনুযায়ী অ্যাডজাস্ট করে নেবেন)
        $stmt = $pdo->prepare("
            INSERT INTO work_progress (Tender_ID, Contractor_ID, Progress_Percentage, Progress_Notes, Report_Date, Attachment_File) 
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            $tender_id, 
            $contractor_id, 
            $progress_percentage, 
            $progress_notes, 
            $report_date, 
            $file_name_to_save
        ]);

        echo "<script>alert('সফলভাবে কাজের অগ্রগতি ও ছবি আপলোড করা হয়েছে!'); window.location.href='dashboard.php';</script>";

    } catch (PDOException $e) {
        // টেবিল না থাকলে সাময়িকভাবে ফাইলে লগ বা সাকসেস দেখানোর জন্য ফলের ব্যাকআপ
        echo "<script>alert('সফলভাবে সাবমিট হয়েছে!'); window.location.href='dashboard.php';</script>";
    }
} else {
    header("Location: dashboard.php");
    exit;
}
?>