<?php
require_once 'db.php';

function log_activity($user_id, $action_type, $entity_type = null, $entity_id = null, $old_val = null, $new_val = null) {
    global $pdo;
    
    try {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        $stmt = $pdo->prepare(
            "INSERT INTO audit_log (User_ID, Action_Type, Target_Entity_Type, Target_Entity_ID, Old_Value, New_Value, IP_Address, Timestamp) 
             VALUES (?, ?, ?, ?, ?, ?, ?, NOW())"
        );
        $stmt->execute([$user_id, $action_type, $entity_type, $entity_id, $old_val, $new_val, $ip]);
    } catch (PDOException $e) {
        // Audit log fail korle jeno main application crash na kare
        error_log("Audit log failed: " . $e->getMessage());
    }
}
?>