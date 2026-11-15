<?php
session_start();
require_once 'db.php'; // Database connection
require 'auth_check.php';
enforce_access();

// PHP Session values
$active_role = trim($_SESSION['role'] ?? 'project_officer');
$active_user = $_SESSION['username'] ?? 'System User';
$active_name = $_SESSION['full_name'] ?? 'Authorized Official';
$current_contractor_id = $_SESSION['contractor_id'] ?? 1;

// Role wise Dashboard settings
$role_title = 'User';
$default_tab = 'overview';

if ($active_role === 'admin' || $active_role === 'ministry') {
    $role_title = 'System Administrator / Ministry';
    $default_tab = 'projectEntry';
} elseif ($active_role === 'project_officer') {
    $role_title = 'Project Officer';
    $default_tab = 'projectEntry';
} elseif ($active_role === 'procurement_officer') {
    $role_title = 'Procurement Officer';
    $default_tab = 'procurementBids';
} elseif ($active_role === 'reviewing_officer' || $active_role === 'auditor') {
    $role_title = 'Independent Auditor / IMED';
    $default_tab = 'discrepancies';
} elseif ($active_role === 'contractor') {
    $role_title = 'Contractor / Vendor';
    $default_tab = 'contractorBids';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GhostTrack — Secure Enterprise Dashboard</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Mono:wght@400;600&family=Manrope:wght@400;500;600;700&family=Michroma&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="dashboard.css">

    <!-- Sync Session Role Directly with Client Side JavaScript -->
    <script>
        sessionStorage.setItem('ghosttrack_user_role', "<?php echo htmlspecialchars($active_role ?? ''); ?>");
        sessionStorage.setItem('ghosttrack_user_id', "<?php echo htmlspecialchars($active_user ?? ''); ?>");
        sessionStorage.setItem('ghosttrack_user_name', "<?php echo htmlspecialchars($active_name ?? ''); ?>");
    </script>
    <style>
        /* Custom Accordion Tree Styles for Enterprise Audit Inspector */
        .project-tree-container { background: #fff; border: 1px solid #cbd5e1; border-radius: 10px; padding: 20px; margin-bottom: 25px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); }
        .project-tree-item { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; margin-bottom: 12px; overflow: hidden; transition: all 0.2s; }
        .project-tree-header { padding: 14px 18px; display: flex; justify-content: space-between; align-items: center; cursor: pointer; font-weight: 700; color: #0f172a; font-size: 15px; background: #f1f5f9; border-bottom: 1px solid #e2e8f0; }
        .project-tree-header:hover { background: #e2e8f0; }
        .tender-sub-list { background: #ffffff; padding: 12px 20px; display: none; }
        .tender-sub-item { padding: 10px 14px; margin: 8px 0; border-radius: 6px; background: #f0f9ff; border: 1px solid #bae6fd; cursor: pointer; display: flex; justify-content: space-between; align-items: center; font-size: 13px; color: #0369a1; font-weight: 600; transition: all 0.2s; }
        .tender-sub-item:hover { background: #e0f2fe; border-color: #7dd3fc; transform: translateX(4px); }
        
        /* Discrepancy & Audit Alert Badges */
        .alert-card-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-bottom: 25px; }
        .audit-stat-card { background: #fff; border: 1px solid #cbd5e1; border-radius: 10px; padding: 20px; border-left: 5px solid #0284c7; }
        .audit-stat-card.danger { border-left-color: #dc2626; }
        .audit-stat-card.warning { border-left-color: #d97706; }
        .audit-stat-card.success { border-left-color: #16a34a; }
        
        .lifecycle-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 15px; margin-bottom: 20px; font-size: 13px; background: #f8fafc; padding: 15px; border-radius: 8px; border: 1px solid #e2e8f0; }
        .lifecycle-card-box { background: #fff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 18px; box-shadow: 0 2px 4px rgba(0,0,0,0.02); }
        
        /* Advanced Fraud Detection Rule Cards */
        .fraud-rule-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 15px; margin-bottom: 25px; }
        .fraud-rule-card { background: #fff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 15px; border-left: 4px solid #8b5cf6; }

        /* Tender Package Breakdown Pill Styles */
        .tender-breakdown-pill { background: #f0fdf4; border: 1px solid #bbf7d0; padding: 8px 12px; border-radius: 6px; margin-top: 6px; display: flex; justify-content: space-between; align-items: center; font-size: 12px; color: #166534; font-weight: 600; }
    </style>
</head>
<body>

    <!-- DASHBOARD HEADER / TOPBAR -->
    <header class="dash-header">
        <div class="dash-logo-area">
            <svg class="logo-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/>
            </svg>
            <span>GHOSTTRACK · SECURE PORTAL</span>
        </div>
        <div class="dash-user-info">
            <span id="displayRoleBadge" class="role-badge">Loading...</span>
            <span id="displayUserName" class="user-name-text">Name: --</span>
            <span id="displayUserId" class="user-id-text">ID: --</span>
            <button onclick="handleLogout()" class="btn-logout">LOGOUT</button>
        </div>
    </header>

    <!-- MAIN DASHBOARD CONTAINER -->
    <main class="dash-container">
        
        <!-- SIDEBAR NAVIGATION MODULES -->
        <aside class="dash-sidebar">
            <div class="sidebar-title">MAIN MODULES</div>
            <ul class="sidebar-menu">
                <li><a href="#" id="nav-overview" class="menu-item active" onclick="switchTab('overview')">📊 Overview & Analytics</a></li>
                
                <!-- Role Specific Menu Items -->
                <li id="menuProjectOfficer" class="role-menu-item" style="display:none;">
                    <a href="#" id="nav-projectEntry" class="menu-item" onclick="switchTab('projectEntry')">📁 Ministry Projects & Budget</a>
                </li>
                
                <!-- Procurement Officer Menu Group -->
                <li id="menuProcurement" class="role-menu-item" style="display:none;">
                    <a href="#" id="nav-procurementBids" class="menu-item" onclick="switchTab('procurementBids')">📝 Procurement & Tender Evaluation</a>
                </li>
                <li id="menuViewBidsEval" class="role-menu-item" style="display:none;">
                    <a href="#" id="nav-viewBidsEval" class="menu-item" onclick="switchTab('viewBidsEval')">📁 View Bids & Evaluate</a>
                </li>

                <!-- Reviewing Officer / IMED Menu Group -->
                <li id="menuReviewing" class="role-menu-item" style="display:none;">
                    <a href="#" id="nav-discrepancies" class="menu-item" onclick="switchTab('discrepancies')">🚨 Alert & Officer Review (IMED)</a>
                </li>
                <li id="menuAuditLifecycle" class="role-menu-item" style="display:none;">
                    <a href="#" id="nav-auditLifecycle" class="menu-item" onclick="switchTab('auditLifecycle')">🔍 Project Audit & Lifecycle</a>
                </li>

                <li id="menuContractorBids" class="role-menu-item" style="display:none;">
                    <a href="#" id="nav-contractorBids" class="menu-item" onclick="switchTab('contractorBids')">🏗️ My Bids & Awarded Tenders</a>
                </li>
                <li id="menuWorkProgress" class="role-menu-item" style="display:none;">
                    <a href="#" id="nav-workProgress" class="menu-item" onclick="switchTab('workProgress')">📈 Work Progress Report</a>
                </li>
                <li id="menuInvoices" class="role-menu-item" style="display:none;">
                    <a href="#" id="nav-invoices" class="menu-item" onclick="switchTab('invoices')">💳 Invoices & Payments</a>
                </li>
            </ul>
        </aside>

        <!-- CONTENT VIEW AREA -->
        <section class="dash-content-area">
            
            <!-- TAB 1: OVERVIEW -->
            <div id="tab-overview" class="content-tab active">
                <div class="welcome-banner">
                    <h2>Welcome to National Infrastructure Telemetry</h2>
                    <p>Relational fund flow monitoring & discrepancy detection gateway across active mega projects.</p>
                </div>
                <div class="stats-cards-grid">
                    <div class="dash-card">
                        <h3>ACTIVE PROJECTS</h3>
                        <div class="card-val" id="overviewProjectCount">04</div>
                        <p>Padma Rail, MRT Line-6, Karnafuli Tunnel, Road Rehabilitation</p>
                    </div>
                    <div class="dash-card">
                        <h3>TOTAL ALLOCATED FUND</h3>
                        <div class="card-val text-blue">7.58L Cr</div>
                        <p>Synced with Ministry of Finance Treasury Gateways</p>
                    </div>
                    <div class="dash-card alert-border">
                        <h3>FLAGGED DISCREPANCIES</h3>
                        <div class="card-val text-red">03</div>
                        <p>Pending IMED Auditing & Officer Review</p>
                    </div>
                </div>
            </div>

          <?php
        try {
            $stmt_pending = $pdo->query("SELECT id, full_name, username, email, role, status FROM system_users WHERE status = 'Pending' ORDER BY id DESC");
            $pending_users = $stmt_pending->fetchAll(PDO::FETCH_ASSOC);

            if ($pending_users) {
                foreach ($pending_users as $pu) {
                    echo "<tr>";
                    echo "<td>#USR-" . htmlspecialchars($pu['id']) . "</td>";
                    echo "<td><strong>" . htmlspecialchars($pu['full_name']) . "</strong></td>";
                    echo "<td>" . htmlspecialchars($pu['username']) . "</td>";
                    echo "<td>" . htmlspecialchars($pu['email']) . "</td>";
                    echo "<td><span style='color:#0284c7; font-weight:600;'>" . ucfirst(htmlspecialchars($pu['role'])) . "</span></td>";
                    echo "<td><span class='status-badge pending-bg'>" . htmlspecialchars($pu['status']) . "</span></td>";
                    echo "<td>";
                    echo "<form action='approve_user.php' method='POST' style='display:inline;'>";
                    echo "<input type='hidden' name='user_id' value='" . $pu['id'] . "'>";
                    echo "<button type='submit' name='action' value='approve' style='background:#16a34a; color:#fff; border:none; padding:5px 10px; border-radius:4px; cursor:pointer; font-weight:600; margin-right:5px;'>Approve</button>";
                    echo "<button type='submit' name='action' value='reject' style='background:#dc2626; color:#fff; border:none; padding:5px 10px; border-radius:4px; cursor:pointer; font-weight:600;'>Reject</button>";
                    echo "</form>";
                    echo "</td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='7' style='text-align:center; color:#64748b;'>No pending contractor registration requests right now.</td></tr>";
            }
        } catch (PDOException $e) {
            echo "<tr><td colspan='7' style='text-align:center; color:red;'>Error loading pending approvals.</td></tr>";
        }
        ?>
        </tbody>
    </table>
</div>
<?php endif; ?>
            <!-- TAB 2: PROJECT OFFICER & MANAGEMENT -->
            <div id="tab-projectEntry" class="content-tab" style="display:none;">
                <div class="section-title-box main-flex-header">
                    <div>
                        <h2>Ministry Project & Budget Allocation</h2>
                        <p>Register new development phases, allocate initial funds, and map relational database tables with Scope of Work.</p>
                    </div>
                    <button class="btn-primary-blue" onclick="toggleNewProjectModal(true)">+ Add New Project</button>
                </div>
                <div class="data-table-wrapper">
                    <table class="corporate-table">
                        <thead>
                            <tr>
                                <th>Project ID</th>
                                <th>Project Name & Scope</th>
                                <th>Ministry / Agency</th>
                                <th>Allocated Budget</th>
                                <th>Created By Officer</th>
                                <th>Start - End Date</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody id="projectsTableBody">
                        <?php
                        try {
                            $stmt = $pdo->query("
                                SELECT p.*, o.Officer_Name AS Creator_Name, m.Ministry_Name 
                                FROM project p 
                                LEFT JOIN officer o ON p.Created_By_Officer_ID = o.Officer_ID
                                LEFT JOIN ministry m ON p.Ministry_ID = m.Ministry_ID
                                ORDER BY p.Project_ID DESC
                            ");
                            $projects = $stmt->fetchAll(PDO::FETCH_ASSOC);
                            if ($projects) {
                                foreach ($projects as $proj) {
                                    echo "<tr>";
                                    echo "<td>P-". htmlspecialchars($proj['Project_ID'] ?? '') . "</td>";
                                    echo "<td><strong>". htmlspecialchars($proj['Project_Name'] ?? '') . "</strong><br><small style='color:#0284c7;'>Scope: ". htmlspecialchars($proj['Scope_Of_Work'] ?? 'General Civil Works') . "</small></td>";
                                    echo "<td>". htmlspecialchars($proj['Ministry_Name'] ?? 'Ministry ID: '.$proj['Ministry_ID']) . "</td>";
                                    echo "<td>". number_format($proj['Approved_Budget'] ?? 0, 2) . " BDT</td>";
                                    echo "<td><span style='color: #0056b3; font-weight: 600;'>". htmlspecialchars($proj['Creator_Name'] ?? 'System Assigned') . "</span></td>";
                                    echo "<td>". htmlspecialchars($proj['Project_Start_Date'] ?? '') . " to " . htmlspecialchars($proj['Expected_End_Date'] ?? '') . "</td>";
                                    echo '<td><span class="status-badge active-bg">'. htmlspecialchars($proj['Project_Status'] ?? 'Active') . '</span></td>';
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='7' style='text-align:center;'>No projects found in database.</td></tr>";
                            }
                        } catch (PDOException $e) {
                            echo "<tr><td colspan='7' style='text-align:center; color:red;'>Error loading projects.</td></tr>";
                        }
                        ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- TAB 3: PROCUREMENT & TENDER EVALUATION -->
            <div id="tab-procurementBids" class="content-tab" style="display:none;">
                <div class="section-title-box main-flex-header">
                    <div>
                        <h2>Procurement & Tender Evaluation</h2>
                        <p>Review published tenders, evaluate submitted contractor bids, and select winning proposals.</p>
                    </div>
                    <button class="btn-primary-blue" style="padding: 6px 14px; font-size: 13px; border-radius: 6px; white-space: nowrap;" onclick="toggleNewTenderModal(true)">+ Publish New Tender</button>
                </div>
                
                <div class="data-table-wrapper mb-25">
                    <h3 style="margin-bottom: 15px; font-size: 16px; color: #1e293b;">Active Tenders Summary & Individual Package Amounts</h3>
                    <table class="corporate-table">
                        <thead>
                            <tr>
                                <th>Tender ID & Ref</th>
                                <th>Project & Work Category</th>
                                <th>Published By Officer</th>
                                <th>Tender Amount (BDT)</th>
                                <th>Total Bids Received</th>
                                <th>Tender Status</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php
                        try {
                            $stmt_tenders = $pdo->query("
                                SELECT t.Tender_ID, t.Tender_Reference, t.Tender_Status, t.Publication_Date, t.Submission_Deadline,
                                       COALESCE(t.Work_Category, 'Civil Works') as Work_Category,
                                       COALESCE(p.Project_Name, 'General Project') as Project_Name,
                                       COALESCE(p.Approved_Budget / 3, 50000000) as Tender_Amount,
                                       o.Officer_Name AS Publisher_Name
                                FROM tender t
                                LEFT JOIN project p ON t.Package_ID = p.Project_ID
                                LEFT JOIN officer o ON t.Published_By_Officer_ID = o.Officer_ID
                                ORDER BY t.Tender_ID DESC
                            ");
                            $tenders_list = $stmt_tenders->fetchAll(PDO::FETCH_ASSOC);

                            if ($tenders_list) {
                                foreach ($tenders_list as $t) {
                                    $tender_id = $t['Tender_ID'] ?? 1;
                                    $tender_amount = number_format($t['Tender_Amount'], 2);
                                    
                                    $stmt_bcount = $pdo->prepare("SELECT COUNT(*) FROM bid WHERE Tender_ID = ?");
                                    $stmt_bcount->execute([$tender_id]);
                                    $total_bids = $stmt_bcount->fetchColumn();

                                    echo "<tr>";
                                    echo "<td><strong>TND-" . htmlspecialchars($tender_id) . "</strong><br><small style='color:#64748b;'>Ref: " . htmlspecialchars($t['Tender_Reference'] ?? '') . "</small></td>";
                                    echo "<td><strong>" . htmlspecialchars($t['Project_Name']) . "</strong><br><span style='color: #0284c7; font-weight: 600;'>[" . htmlspecialchars($t['Work_Category']) . "]</span></td>";
                                    echo "<td><span style='color: #28a745; font-weight: 600;'>" . htmlspecialchars($t['Publisher_Name'] ?? 'Procurement Officer') . "</span></td>";
                                    echo "<td><strong style='color: #059669;'>" . $tender_amount . " BDT</strong></td>";
                                    echo "<td><span style='background:#e0f2fe; color:#0369a1; padding:4px 10px; border-radius:12px; font-weight:700;'>" . $total_bids . " Bids Submitted</span></td>";
                                    echo '<td><span class="status-badge ' . (($t['Tender_Status'] ?? '') === 'Open' ? 'pending-bg' : 'active-bg') . '">' . htmlspecialchars($t['Tender_Status'] ?? 'Open') . '</span></td>';
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='6' style='text-align:center;'>No tenders found.</td></tr>";
                            }
                        } catch (PDOException $e) {
                            echo "<tr><td colspan='6' style='text-align:center; color:red;'>Error loading tenders data.</td></tr>";
                        }
                        ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- TAB 3.1: VIEW BIDS & EVALUATE -->
            <div id="tab-viewBidsEval" class="content-tab" style="display:none;">
                <div class="section-title-box main-flex-header">
                    <div>
                        <h2>Bid Evaluation & Contractor Selection Portal</h2>
                        <p>Select a project and its corresponding tender package below to inspect company proposals, scores, and finalize awards.</p>
                    </div>
                </div>

                <div class="project-tree-container">
                    <label class="form-label" style="font-weight:700; color:#1e293b; margin-bottom:12px; display:block;">Select Project & Tender Package to Evaluate:</label>
                    <div id="projectAccordionTree">
                        <?php
                        try {
                            $stmt_proj_tree = $pdo->query("
                                SELECT DISTINCT p.Project_ID, p.Project_Name 
                                FROM project p 
                                JOIN tender t ON p.Project_ID = t.Package_ID 
                                ORDER BY p.Project_Name ASC
                            ");
                            $projects_tree = $stmt_proj_tree->fetchAll(PDO::FETCH_ASSOC);

                            if ($projects_tree) {
                                foreach ($projects_tree as $ptree) {
                                    $pid = $ptree['Project_ID'];
                                    $pname = htmlspecialchars($ptree['Project_Name']);

                                    $stmt_t_tree = $pdo->prepare("
                                        SELECT Tender_ID, Tender_Reference, Work_Category, Publication_Date, Submission_Deadline, Tender_Status
                                        FROM tender 
                                        WHERE Package_ID = ?
                                        ORDER BY Tender_ID DESC
                                    ");
                                    $stmt_t_tree->execute([$pid]);
                                    $tenders_tree = $stmt_t_tree->fetchAll(PDO::FETCH_ASSOC);
                                    $tender_count = count($tenders_tree);

                                    echo '<div class="project-tree-item">';
                                    echo '<div class="project-tree-header" onclick="toggleProjectAccordion(this.nextElementSibling)">';
                                    echo '<span>📁 Project: ' . $pname . ' <small style="color:#64748b; font-weight:normal;">(' . $tender_count . ' Tender Packages)</small></span>';
                                    echo '<span class="accordion-arrow">▼</span>';
                                    echo '</div>';

                                    echo '<div class="tender-sub-list">';
                                    foreach ($tenders_tree as $tt) {
                                        $tid = $tt['Tender_ID'];
                                        $tref = htmlspecialchars($tt['Tender_Reference']);
                                        $tcat = htmlspecialchars($tt['Work_Category'] ?? 'Civil Works');
                                        $tpub = $tt['Publication_Date'];
                                        $tdead = $tt['Submission_Deadline'];

                                        echo '<div class="tender-sub-item" onclick="loadTenderDetailsForEval(' . $tid . ', \'' . addslashes($pname) . '\', \'' . $tref . '\', \'' . $tcat . '\', \'' . $tpub . '\', \'' . $tdead . '\')">';
                                        echo '<span>📄 Tender #' . $tid . ' ── [ Cat: ' . $tcat . ' | Ref: ' . $tref . ' ]</span>';
                                        echo '<span style="color:#0284c7; font-weight:700;">Evaluate &rarr;</span>';
                                        echo '</div>';
                                    }
                                    echo '</div>';
                                    echo '</div>';
                                }
                            } else {
                                echo '<p style="color:#64748b; text-align:center;">No project tenders available for evaluation.</p>';
                            }
                        } catch (PDOException $e) {
                            echo '<p style="color:red; text-align:center;">Error loading project tree.</p>';
                        }
                        ?>
                    </div>
                </div>

                <div id="tenderEvaluationSection" style="display:none; background: #fff; padding: 25px; border-radius: 10px; border: 1px solid #cbd5e1;">
                    <div class="section-title-box main-flex-header" style="margin-bottom: 20px;">
                        <div>
                            <h2 id="evalSectionTitle" style="font-size: 18px; color: #0f172a;">Active Tender Bid Evaluation: Project Name</h2>
                            <p style="font-size: 13px; color: #64748b;">Inspect company proposals, technical scores, financial scores, and select winning contractor.</p>
                        </div>
                        <button class="btn-primary-blue" style="padding: 5px 12px; font-size: 12px;" onclick="alert('Award finalized and posted successfully!')">Finalize & Post Award</button>
                    </div>

                    <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 15px; margin-bottom: 20px;">
                        <h4 style="margin-bottom: 10px; font-size: 14px; color: #334155;">Tender Information</h4>
                        <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 15px; font-size: 13px;">
                            <div><strong>Tender Reference:</strong> <span id="infoRef">--</span></div>
                            <div><strong>Publication Date:</strong> <span id="infoPubDate">--</span></div>
                            <div><strong>Submission Deadline:</strong> <span id="infoDeadline">--</span></div>
                            <div><strong>Status:</strong> <span style="color:#059669; font-weight:600;">Published / Open</span></div>
                        </div>
                    </div>

                    <div class="data-table-wrapper">
                        <h4 style="margin-bottom: 10px; font-size: 14px; color: #334155;">Bid List (Contractor Proposals)</h4>
                        <table class="corporate-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Contractor Company Name</th>
                                    <th>Proposed Amount (BDT)</th>
                                    <th>Technical Score</th>
                                    <th>Financial Score</th>
                                    <th>Calculated Total Score</th>
                                    <th>Evaluation Result Status</th>
                                </tr>
                            </thead>
                            <tbody id="evalBidsTableBody"></tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- TAB 4: REVIEWING OFFICER / IMED (ALERT & OFFICER REVIEW PAGE WITH NEW FRAUD DETECTION RULES) -->
            <div id="tab-discrepancies" class="content-tab" style="display:none;">
                <div class="section-title-box main-flex-header">
                    <div>
                        <h2>Financial Discrepancy & Fraud Detection Logs (IMED)</h2>
                        <p>Review flagged audit discrepancies, investigate mismatched invoice claims, and take administrative actions.</p>
                    </div>
                </div>

                <!-- IMED Audit Stats Summary -->
                <div class="alert-card-grid">
                    <div class="audit-stat-card danger">
                        <h3 style="font-size:13px; color:#64748b; margin-bottom:5px;">CRITICAL DISCREPANCIES</h3>
                        <div style="font-size:24px; font-weight:700; color:#dc2626;">03 Cases</div>
                        <p style="font-size:12px; color:#64748b; margin-top:5px;">Mismatched invoice claims vs physical progress.</p>
                    </div>
                    <div class="audit-stat-card warning">
                        <h3 style="font-size:13px; color:#64748b; margin-bottom:5px;">PENDING AUDIT REVIEWS</h3>
                        <div style="font-size:24px; font-weight:700; color:#d97706;">05 Tenders</div>
                        <p style="font-size:12px; color:#64748b; margin-top:5px;">Requires independent verification report.</p>
                    </div>
                    <div class="audit-stat-card success">
                        <h3 style="font-size:13px; color:#64748b; margin-bottom:5px;">RESOLVED & VERIFIED</h3>
                        <div style="font-size:24px; font-weight:700; color:#16a34a;">12 Projects</div>
                        <p style="font-size:12px; color:#64748b; margin-top:5px;">Fund flow clear & verified by IMED.</p>
                    </div>
                </div>

                <!-- Advanced Automated Fraud Detection & Audit Rules Hub -->
                <div class="section-title-box" style="margin-top: 20px; margin-bottom: 12px;">
                    <h3>🛡️ Active Automated Fraud Detection & Compliance Rules</h3>
                    <p style="font-size: 13px; color: #64748b;">System algorithms continuously enforcing real-world anti-corruption checks.</p>
                </div>
                <div class="fraud-rule-grid">
                    <div class="fraud-rule-card">
                        <h4 style="font-size: 14px; color: #6d28d9; margin-bottom: 6px;">1. Cost Variance Threshold & Front-Loading</h4>
                        <p style="font-size: 12px; color: #475569;">Monitors if initial milestone claims exceed 40% of budget while physical progress is below 20%. Status: <strong style="color: #dc2626;">Enforced (3 Flagged)</strong></p>
                    </div>
                    <div class="fraud-rule-card">
                        <h4 style="font-size: 14px; color: #6d28d9; margin-bottom: 6px;">2. Bid Rigging & Syndication Detection</h4>
                        <p style="font-size: 12px; color: #475569;">Scans competitive bids for identical pricing patterns or rotational winning rings. Status: <strong style="color: #16a34a;">Clean / Verified</strong></p>
                    </div>
                    <div class="fraud-rule-card">
                        <h4 style="font-size: 14px; color: #6d28d9; margin-bottom: 6px;">3. Schedule & Milestone Lag Alert</h4>
                        <p style="font-size: 12px; color: #475569;">Triggers warnings when project timeline elapses > 50% with minimal site progress. Status: <strong style="color: #d97706;">2 Tenders Warned</strong></p>
                    </div>
                    <div class="fraud-rule-card">
                        <h4 style="font-size: 14px; color: #6d28d9; margin-bottom: 6px;">4. Automatic Treasury Disbursement Lock</h4>
                        <p style="font-size: 12px; color: #475569;">Instantly halts central bank treasury transfers when IMED officer flags active fraud. Status: <strong style="color: #0284c7;">Online & Active</strong></p>
                    </div>
                </div>

                <!-- Flagged Discrepancies & Automated Fraud Detection Logs -->
                <div class="data-table-wrapper mb-25">
                    <h3 style="margin-bottom: 15px; font-size: 16px; color: #1e293b;">🚨 Flagged Discrepancies & Automated Fraud Detection Logs</h3>
                    <table class="corporate-table">
                        <thead>
                            <tr>
                                <th>Alert ID & Project</th>
                                <th>Contractor / Vendor</th>
                                <th>Discrepancy Description / Issue</th>
                                <th>Severity Level</th>
                                <th>Reported Date</th>
                                <th>Review Officer Action & Resolution</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><strong>ALT-301</strong><br><small style='color:#0284c7;'>Padma Rail Link Project</small></td>
                                <td><strong>Toma Construction & Co.</strong></td>
                                <td><span style='color:#dc2626; font-weight:600;'>Invoice Claim Exceeds Milestone Progress!</span> Claimed 45% bill with only 18% physical site progress verified.</td>
                                <td><span style='background:#fee2e2; color:#991b1b; padding:4px 8px; border-radius:10px; font-weight:700; font-size:11px;'>High Risk</span></td>
                                <td>2026-11-06</td>
                                <td>
                                    <select class='dash-input' style='padding: 5px 8px; font-size: 12px; width: 160px; font-weight:600; color:#dc2626;' onchange='handleAuditAction(this, 301)'>
                                        <option value='Under Investigation' selected>⚠️ Under Investigation</option>
                                        <option value='Halt Disbursement'>🛑 Halt Treasury Payment</option>
                                        <option value='Resolved'>✓ Resolved & Cleared</option>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <td><strong>ALT-302</strong><br><small style='color:#0284c7;'>MRT Line-6 Extension</small></td>
                                <td><strong>China Railway Group Ltd.</strong></td>
                                <td><span style='color:#d97706; font-weight:600;'>Tender Bidding Anomaly Detected.</span> Price variation > 30% below official estimated government benchmark.</td>
                                <td><span style='background:#fef3c7; color:#92400e; padding:4px 8px; border-radius:10px; font-weight:700; font-size:11px;'>Medium Risk</span></td>
                                <td>2026-11-05</td>
                                <td>
                                    <select class='dash-input' style='padding: 5px 8px; font-size: 12px; width: 160px; font-weight:600;' onchange='handleAuditAction(this, 302)'>
                                        <option value='Under Investigation' selected>⚠️ Under Investigation</option>
                                        <option value='Halt Disbursement'>🛑 Halt Treasury Payment</option>
                                        <option value='Resolved'>✓ Resolved & Cleared</option>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <td><strong>ALT-303</strong><br><small style='color:#0284c7;'>Karnafuli Tunnel Approach</small></td>
                                <td><strong>Abdul Monem Ltd.</strong></td>
                                <td><span style='color:#dc2626; font-weight:600;'>Duplicate Invoice Submission.</span> Reference INV-2026-089 already processed in previous cycle.</td>
                                <td><span style='background:#fee2e2; color:#991b1b; padding:4px 8px; border-radius:10px; font-weight:700; font-size:11px;'>Critical Risk</span></td>
                                <td>2026-11-04</td>
                                <td>
                                    <select class='dash-input' style='padding: 5px 8px; font-size: 12px; width: 160px; font-weight:600; color:#dc2626;' onchange='handleAuditAction(this, 303)'>
                                        <option value='Under Investigation' selected>⚠️ Under Investigation</option>
                                        <option value='Halt Disbursement'>🛑 Halt Treasury Payment</option>
                                        <option value='Resolved'>✓ Resolved & Cleared</option>
                                    </select>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- TAB 4.1: PROJECT AUDIT & LIFECYCLE (ENHANCED ENTERPRISE REAL-WORLD AUDIT INSPECTOR WITH ALL TENDER AMOUNTS SHOWN IN OVERVIEW) -->
            <div id="tab-auditLifecycle" class="content-tab" style="display:none;">
                <div class="section-title-box main-flex-header">
                    <div>
                        <h2>Project Audit & Lifecycle Intelligence Hub</h2>
                        <p>Complete project-to-tender breakdown: Ministry allocations, individual tender amounts, contractor bids, and anomaly detection.</p>
                    </div>
                </div>

                <!-- ACCORDION TREE FOR AUDIT INSPECTION WITH ALL TENDER AMOUNTS -->
                <div class="project-tree-container">
                    <label class="form-label" style="font-weight:700; color:#1e293b; margin-bottom:12px; display:block;">📁 Select Project or Specific Tender Package for Deep Audit Telemetry:</label>
                    <div id="auditAccordionTree">
                        <?php
                        try {
                            $stmt_proj_tree_audit = $pdo->query("
                                SELECT DISTINCT p.Project_ID, p.Project_Name, p.Approved_Budget, p.Project_Start_Date, p.Expected_End_Date,
                                       COALESCE(o1.Officer_Name, 'Ministry Official') as Creator_Name,
                                       COALESCE(m.Ministry_Name, 'Ministry of Road Transport and Bridges') as Ministry_Name
                                FROM project p 
                                JOIN tender t ON p.Project_ID = t.Package_ID 
                                LEFT JOIN officer o1 ON p.Created_By_Officer_ID = o1.Officer_ID
                                LEFT JOIN ministry m ON p.Ministry_ID = m.Ministry_ID
                                ORDER BY p.Project_Name ASC
                            ");
                            $projects_tree_audit = $stmt_proj_tree_audit->fetchAll(PDO::FETCH_ASSOC);

                            if ($projects_tree_audit) {
                                foreach ($projects_tree_audit as $ptree) {
                                    $pid = $ptree['Project_ID'];
                                    $pname = htmlspecialchars($ptree['Project_Name']);
                                    $praw_budget = $ptree['Approved_Budget'] ?? 0;
                                    $pbudget = number_format($praw_budget, 2);
                                    $pstart = $ptree['Project_Start_Date'];
                                    $pend = $ptree['Expected_End_Date'];
                                    $pcreator = $ptree['Creator_Name'];
                                    $pmin = $ptree['Ministry_Name'];

                                    $stmt_t_tree = $pdo->prepare("
                                        SELECT t.Tender_ID, t.Tender_Reference, t.Work_Category, t.Submission_Deadline,
                                               COALESCE(o2.Officer_Name, 'Procurement Officer') as Publisher_Name,
                                               COALESCE(c.Contractor_Name, 'Toma Construction & Co.') as Contractor_Name,
                                               COALESCE(b.Proposed_Amount, ? / 3) as Tender_Amount
                                        FROM tender t
                                        LEFT JOIN officer o2 ON t.Published_By_Officer_ID = o2.Officer_ID
                                        LEFT JOIN bid b ON t.Tender_ID = b.Tender_ID
                                        LEFT JOIN contractor c ON b.Contractor_ID = c.Contractor_ID
                                        WHERE t.Package_ID = ?
                                        ORDER BY t.Tender_ID DESC
                                    ");
                                    $stmt_t_tree->execute([$praw_budget, $pid]);
                                    $tenders_tree = $stmt_t_tree->fetchAll(PDO::FETCH_ASSOC);
                                    $tender_count = count($tenders_tree);

                                    // Build array of tenders for JS json encoding
                                    $tenders_json_arr = [];
                                    foreach($tenders_tree as $tt) {
                                        $tenders_json_arr[] = [
                                            'Tender_ID' => $tt['Tender_ID'],
                                            'Tender_Reference' => $tt['Tender_Reference'],
                                            'Work_Category' => $tt['Work_Category'] ?? 'Civil Works',
                                            'Contractor_Name' => $tt['Contractor_Name'],
                                            'Tender_Amount' => number_format($tt['Tender_Amount'], 2),
                                            'Publisher_Name' => $tt['Publisher_Name']
                                        ];
                                    }

                                    echo '<div class="project-tree-item">';
                                    
                                    $proj_meta = htmlspecialchars(json_encode([
                                        'type' => 'project',
                                        'Project_ID' => $pid,
                                        'Project_Name' => $pname,
                                        'Ministry_Name' => $pmin,
                                        'Approved_Budget' => $pbudget,
                                        'Project_Start_Date' => $pstart,
                                        'Expected_End_Date' => $pend,
                                        'Creator_Name' => $pcreator,
                                        'Tender_Count' => $tender_count,
                                        'Tenders' => $tenders_json_arr
                                    ]), ENT_QUOTES, 'UTF-8');

                                    echo '<div class="project-tree-header">';
                                    echo '<span onclick=\'loadProjectAggregateAudit(' . $proj_meta . ')\' style="flex:1; cursor:pointer;">📁 Project P-' . $pid . ': ' . $pname . ' <small style="color:#0284c7; font-weight:600;">(Total Budget: ' . $pbudget . ' BDT | ' . $tender_count . ' Tenders)</small></span>';
                                    echo '<span class="accordion-arrow" onclick="toggleProjectAccordion(this.parentNode.nextElementSibling)" style="cursor:pointer; padding:0 10px;">▼</span>';
                                    echo '</div>';

                                    echo '<div class="tender-sub-list">';
                                    foreach ($tenders_tree as $tt) {
                                        $tid = $tt['Tender_ID'];
                                        $tref = htmlspecialchars($tt['Tender_Reference']);
                                        $tcat = htmlspecialchars($tt['Work_Category'] ?? 'Civil Works');
                                        $pub = $tt['Publisher_Name'];
                                        $con = $tt['Contractor_Name'];
                                        $tamt = number_format($tt['Tender_Amount'], 2);

                                        $tender_meta = htmlspecialchars(json_encode([
                                            'type' => 'tender',
                                            'Project_ID' => $pid,
                                            'Project_Name' => $pname,
                                            'Ministry_Name' => $pmin,
                                            'Approved_Budget' => $pbudget,
                                            'Project_Start_Date' => $pstart,
                                            'Expected_End_Date' => $pend,
                                            'Creator_Name' => $pcreator,
                                            'Tender_ID' => $tid,
                                            'Tender_Reference' => $tref,
                                            'Work_Category' => $tcat,
                                            'Publisher_Name' => $pub,
                                            'Contractor_Name' => $con,
                                            'Awarded_Amount' => $tamt
                                        ]), ENT_QUOTES, 'UTF-8');

                                        echo '<div class="tender-sub-item" onclick=\'loadTenderLifecycleDetails(' . $tender_meta . ')\'>';
                                        echo '<span>📄 Tender #' . $tid . ' ── [ Cat: ' . $tcat . ' | Contractor: <strong>' . $con . '</strong> ]</span>';
                                        echo '<span style="color:#059669; font-weight:700;">Tender Amount: ' . $tamt . ' BDT &rarr;</span>';
                                        echo '</div>';
                                    }
                                    echo '</div>';
                                    echo '</div>';
                                }
                            } else {
                                echo '<p style="color:#64748b; text-align:center;">No project tenders available for audit.</p>';
                            }
                        } catch (PDOException $e) {
                            echo '<p style="color:red; text-align:center;">Error loading audit tree.</p>';
                        }
                        ?>
                    </div>
                </div>

                <!-- DYNAMIC END-TO-END AUDIT VIEW BOX (NOW SHOWING ALL TENDER AMOUNTS DIRECTLY) -->
                <div id="auditLifecycleDetailBox" style="display:none; background:#fff; border:1px solid #cbd5e1; border-radius:10px; padding:25px; margin-bottom:25px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);">
                    <h3 id="auditBoxTitle" style="font-size:18px; color:#0f172a; margin-bottom:15px; border-bottom:2px solid #e2e8f0; padding-bottom:10px;">Audit Telemetry Report</h3>
                    
                    <div class="lifecycle-grid">
                        <div><strong>1. Ministry / Treasury Allocation:</strong> <br><span id="auditAllocBudget" style="color:#059669; font-weight:700; font-size:14px;">--</span></div>
                        <div><strong>2. Project Creator:</strong> <br><span id="auditCreator" style="color:#0056b3; font-weight:600; font-size:14px;">--</span></div>
                        <div>
                            <strong>3. Tender Packages & Amounts:</strong> 
                            <div id="auditPublisher" style="margin-top: 4px; font-weight:600; font-size:12px; color:#0369a1;">--</div>
                        </div>
                        <div><strong>4. Awarded Contractor:</strong> <br><span id="auditContractor" style="color:#0f172a; font-weight:700; font-size:14px;">--</span></div>
                        <div><strong>5. Project Timeline:</strong> <br><span id="auditTimeline" style="font-weight:600; font-size:13px;">--</span></div>
                        <div><strong>6. Audit Risk Status:</strong> <br><span id="auditRiskLevel" style="color:#dc2626; font-weight:700; font-size:13px;">High Risk (Discrepancy Flagged)</span></div>
                    </div>

                    <div style="display:grid; grid-template-columns: 1fr 1fr; gap:20px;">
                        <!-- Contractor Physical Progress & Photo -->
                        <div class="lifecycle-card-box" style="background:#f0f9ff; border-color:#bae6fd;">
                            <h4 style="font-size:15px; color:#0369a1; margin-bottom:10px; font-weight:700;">📈 Contractor Physical Progress & Site Photos</h4>
                            <div id="auditProgressContent" style="font-size:13px; color:#334155; line-height:1.6;">
                                <p><strong>Verified Site Progress:</strong> 35.0% Complete</p>
                                <p><strong>Milestone Notes:</strong> Foundation piling and initial structural pillars erected as per civil specifications.</p>
                                <p style="margin-top:10px;"><strong>Attached Site Photo:</strong> <a href="#" id="viewProgressPhotoLink" target="_blank" style="color:#0284c7; font-weight:700; text-decoration:underline;">📷 View Uploaded Progress Photo</a></p>
                            </div>
                        </div>

                        <!-- Contractor Invoices & Treasury Claims -->
                        <div class="lifecycle-card-box" style="background:#fef3c7; border-color:#fde68a;">
                            <h4 style="font-size:15px; color:#92400e; margin-bottom:10px; font-weight:700;">💳 Submitted Invoices & Treasury Claims</h4>
                            <div id="auditInvoiceContent" style="font-size:13px; color:#334155; line-height:1.6;">
                                <p><strong>Invoice Ref:</strong> INV-2026-089</p>
                                <p><strong>Claimed Bill Amount:</strong> <span id="auditClaimedAmount" style="font-weight:700;">45,00,00,000 BDT</span></p>
                                <p style="margin-top:8px;"><strong>Attached Invoice Bill:</strong> <a href="#" id="viewInvoicePhotoLink" target="_blank" style="color:#d97706; font-weight:700; text-decoration:underline;">📄 View Uploaded Invoice Bill / Scan</a></p>
                                <p style="color:#dc2626; font-weight:700; margin-top:8px;">⚠️ Discrepancy Found: Claimed bill (45%) exceeds verified physical progress (35%)!</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- TAB 5: CONTRACTOR PORTAL -->
            <div id="tab-contractorBids" class="content-tab" style="display:none;">
                <div class="section-title-box main-flex-header">
                    <div>
                        <h2>Available Tenders & My Submitted Bids</h2>
                        <p>Browse active published tenders, check if bids have been submitted, and submit your competitive financial bid.</p>
                    </div>
                    <button class="btn-primary-blue" style="padding: 6px 14px; font-size: 13px; border-radius: 6px; white-space: nowrap;" onclick="toggleNewBidModal(true)">+ Submit New Tender Bid</button>
                </div>
                
                <div class="data-table-wrapper mb-25">
                    <h3 style="margin-bottom: 15px; font-size: 16px; color: #1e293b;">Open Tenders & Bid Status (Active Competition Tracking)</h3>
                    <table class="corporate-table">
                        <thead>
                            <tr>
                                <th>Tender ID</th>
                                <th>Project & Category</th>
                                <th>Published By Officer</th>
                                <th>Bid Status in Tender</th>
                                <th>Tender Package Amount</th>
                                <th>Reference & Deadline</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php
                        try {
                            $stmt_open = $pdo->prepare("
                                SELECT t.Tender_ID, t.Tender_Reference, t.Submission_Deadline,
                                       COALESCE(t.Work_Category, 'Civil Works') as Work_Category,
                                       COALESCE(p.Project_Name, 'General Project') as Project_Name,
                                       COALESCE(p.Approved_Budget / 3, 50000000) as Tender_Amount,
                                       p.Scope_Of_Work,
                                       o.Officer_Name AS Publisher_Name,
                                       (SELECT COUNT(*) FROM bid WHERE bid.Tender_ID = t.Tender_ID) as Total_Bids_In_Tender,
                                       (SELECT COUNT(*) FROM bid WHERE bid.Tender_ID = t.Tender_ID AND bid.Contractor_ID = ?) as My_Bid_Count
                                FROM tender t
                                LEFT JOIN project p ON t.Package_ID = p.Project_ID
                                LEFT JOIN officer o ON t.Published_By_Officer_ID = o.Officer_ID
                                WHERE t.Tender_Status = 'Open'
                                ORDER BY t.Tender_ID DESC
                            ");
                            
                            $stmt_open->execute([$current_contractor_id]);
                            $open_tenders = $stmt_open->fetchAll(PDO::FETCH_ASSOC);

                            if ($open_tenders) {
                                foreach ($open_tenders as $ot) {
                                    $budget_text = number_format($ot['Tender_Amount'], 2) . " BDT";
                                    $publisher = !empty($ot['Publisher_Name']) ? $ot['Publisher_Name'] : 'Engr. Farhana Ahmed';
                                    $scope = !empty($ot['Scope_Of_Work']) ? $ot['Scope_Of_Work'] : 'General Construction & Infrastructure';
                                    
                                    if ($ot['My_Bid_Count'] > 0) {
                                        $bid_status_badge = '<span style="background:#d1fae5; color:#065f46; padding:4px 8px; border-radius:10px; font-weight:600; font-size:11px;">✓ You Already Bid</span>';
                                    } elseif ($ot['Total_Bids_In_Tender'] > 0) {
                                        $bid_status_badge = '<span style="background:#fef3c7; color:#92400e; padding:4px 8px; border-radius:10px; font-weight:600; font-size:11px;">⚠️ ' . $ot['Total_Bids_In_Tender'] . ' Bid(s) Received</span>';
                                    } else {
                                        $bid_status_badge = '<span style="background:#f1f5f9; color:#64748b; padding:4px 8px; border-radius:10px; font-weight:600; font-size:11px;">No Bids Yet</span>';
                                    }

                                    echo "<tr>";
                                    echo "<td><strong>TND-" . htmlspecialchars($ot['Tender_ID'] ?? '') . "</strong></td>";
                                    echo "<td><strong>" . htmlspecialchars($ot['Project_Name'] ?? '') . "</strong><br><span style='color: #0284c7; font-weight: 600;'>[" . htmlspecialchars($ot['Work_Category'] ?? '') . "]</span><br><small style='color:#64748b;'>Scope: " . htmlspecialchars($scope) . "</small></td>";
                                    echo "<td><span style='color: #28a745; font-weight: 600;'>" . htmlspecialchars($publisher) . "</span></td>";
                                    echo "<td>" . $bid_status_badge . "</td>";
                                    echo "<td><strong style='color: #059669;'>" . $budget_text . "</strong></td>";
                                    echo "<td>Ref: " . htmlspecialchars($ot['Tender_Reference'] ?? '') . "<br><small style='color: #64748b;'>Deadline: " . htmlspecialchars($ot['Submission_Deadline'] ?? '') . "</small></td>";
                                    echo "<td><button class='btn-primary-blue' style='padding: 5px 12px; font-weight: 600;' onclick='openBidModalForTender(" . $ot['Tender_ID'] . ")'>Apply / Bid Now</button></td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='7' style='text-align:center;'>No open tenders available right now.</td></tr>";
                            }
                        } catch (PDOException $e) {
                            echo "<tr><td colspan='7' style='text-align:center; color:red;'>Error loading open tenders.</td></tr>";
                        }
                        ?>
                        </tbody>
                    </table>
                </div>

                <div class="section-title-box mt-30">
                    <h2>My Submitted Bids History</h2>
                </div>
                <div class="data-table-wrapper">
                    <table class="corporate-table">
                        <thead>
                            <tr>
                                <th>Bid ID</th>
                                <th>Tender ID & Project</th>
                                <th>Contractor Company Name</th>
                                <th>Proposed Amount</th>
                                <th>Submission Date</th>
                                <th>Bid Status</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php
                        try {
                            $stmt_mybids = $pdo->prepare("
                                SELECT b.Bid_ID, b.Tender_ID, b.Proposed_Amount, b.Submission_Date,
                                       COALESCE(t.Tender_Reference, 'N/A') as Tender_Reference,
                                       COALESCE(p.Project_Name, 'General Project') as Project_Name,
                                       c.Contractor_Name
                                FROM bid b
                                LEFT JOIN tender t ON b.Tender_ID = t.Tender_ID
                                LEFT JOIN project p ON t.Package_ID = p.Project_ID
                                LEFT JOIN contractor c ON b.Contractor_ID = c.Contractor_ID
                                WHERE b.Contractor_ID = ?
                                ORDER BY b.Bid_ID DESC
                            ");
                            $stmt_mybids->execute([$current_contractor_id]);
                            $my_bids = $stmt_mybids->fetchAll(PDO::FETCH_ASSOC);

                            if ($my_bids) {
                                foreach ($my_bids as $mb) {
                                    $proj_title = $mb['Project_Name'] ? $mb['Project_Name'] : "General Project";
                                    $contractor_title = $mb['Contractor_Name'] ? $mb['Contractor_Name'] : 'Toma Construction & Co. Ltd.';
                                    echo "<tr>";
                                    echo "<td>BID-" . htmlspecialchars($mb['Bid_ID'] ?? '') . "</td>";
                                    echo "<td>Tender #" . htmlspecialchars($mb['Tender_ID'] ?? '') . " - " . htmlspecialchars($proj_title) . "</td>";
                                    echo "<td><strong style='color:#0f172a;'>" . htmlspecialchars($contractor_title) . "</strong></td>";
                                    echo "<td><strong>" . number_format($mb['Proposed_Amount'] ?? 0, 2) . " BDT</strong></td>";
                                    echo "<td>" . htmlspecialchars($mb['Submission_Date'] ?? '') . "</td>";
                                    echo '<td><span class="status-badge pending-bg">Under Evaluation</span></td>';
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='6' style='text-align:center;'>You have not submitted any bids yet.</td></tr>";
                            }
                        } catch (PDOException $e) {
                            echo "<tr><td colspan='6' style='text-align:center; color:red;'>Error loading your bids.</td></tr>";
                        }
                        ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- TAB 6: WORK PROGRESS REPORT -->
            <div id="tab-workProgress" class="content-tab" style="display:none;">
                <div class="section-title-box main-flex-header">
                    <div>
                        <h2>Work Progress Report & Site Media (Contractor Entry)</h2>
                        <p>Submit physical site progress percentage, progress notes, and upload site inspection photos for active contracts.</p>
                    </div>
                </div>
                <div class="modal-card" style="max-width: 100%; box-shadow: none; border: 1px solid #e2e8f0; padding: 25px; margin-bottom: 25px;">
                    <form action="submit_progress.php" method="POST" enctype="multipart/form-data">
                        <div class="form-grid-2">
                            <div class="form-group">
                                <label>Select Contract / Awarded Tender ID</label>
                                <select name="tender_id" class="dash-input" required>
                                    <option value="">-- Choose Awarded Tender --</option>
                                    <?php
                                    try {
                                        $stmt_prog_t = $pdo->query("SELECT t.Tender_ID, p.Project_Name FROM tender t LEFT JOIN project p ON t.Package_ID = p.Project_ID ORDER BY t.Tender_ID DESC");
                                        while ($pt = $stmt_prog_t->fetch(PDO::FETCH_ASSOC)) {
                                            echo '<option value="' . $pt['Tender_ID'] . '">Tender #' . $pt['Tender_ID'] . ' - ' . htmlspecialchars($pt['Project_Name']) . '</option>';
                                        }
                                    } catch (PDOException $e) {
                                        echo '<option value="">Error loading tenders</option>';
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Physical Progress Percentage (%)</label>
                                <input type="number" step="0.1" min="0" max="100" name="progress_percentage" class="dash-input" placeholder="e.g. 45.5" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Milestone & Work Description Notes</label>
                            <textarea name="progress_notes" class="dash-input" rows="3" placeholder="Describe completed civil/electrical milestones during this reporting period..." required></textarea>
                        </div>
                        <div class="form-grid-2">
                            <div class="form-group">
                                <label>Upload Site Progress Photo / Document</label>
                                <input type="file" name="site_photo" class="dash-input" accept="image/*,.pdf" required>
                            </div>
                            <div class="form-group">
                                <label>Reporting Date</label>
                                <input type="date" name="report_date" class="dash-input" value="<?php echo date('Y-m-d'); ?>" required>
                            </div>
                        </div>
                        <div style="margin-top: 15px; text-align: right;">
                            <button type="submit" class="btn-primary-blue">Submit Progress Report & Photos</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- TAB 7: INVOICES & PAYMENTS -->
            <div id="tab-invoices" class="content-tab" style="display:none;">
                <div class="section-title-box main-flex-header">
                    <div>
                        <h2>Invoice Submission & Payment Tracking</h2>
                        <p>Upload milestone invoices, enter financial claim details, and attach official invoice bills or scanned documents.</p>
                    </div>
                </div>
                <div class="modal-card" style="max-width: 100%; box-shadow: none; border: 1px solid #e2e8f0; padding: 25px; margin-bottom: 25px;">
                    <form action="submit_invoice.php" method="POST" enctype="multipart/form-data">
                        <div class="form-grid-2">
                            <div class="form-group">
                                <label>Select Tender / Contract ID</label>
                                <select name="tender_id" class="dash-input" required>
                                    <option value="">-- Choose Contract --</option>
                                    <?php
                                    try {
                                        $stmt_inv_t = $pdo->query("SELECT t.Tender_ID, p.Project_Name FROM tender t LEFT JOIN project p ON t.Package_ID = p.Project_ID ORDER BY t.Tender_ID DESC");
                                        while ($it = $stmt_inv_t->fetch(PDO::FETCH_ASSOC)) {
                                            echo '<option value="' . $it['Tender_ID'] . '">Tender #' . $it['Tender_ID'] . ' - ' . htmlspecialchars($it['Project_Name']) . '</option>';
                                        }
                                    } catch (PDOException $e) {
                                        echo '<option value="">Error loading tenders</option>';
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Invoice Reference Number</label>
                                <input type="text" name="invoice_number" class="dash-input" placeholder="e.g. INV-2026-089" required>
                            </div>
                        </div>
                        <div class="form-grid-2">
                            <div class="form-group">
                                <label>Claimed Amount (BDT)</label>
                                <input type="number" step="0.01" name="invoice_amount" class="dash-input" placeholder="e.g. 15000000.00" required>
                            </div>
                            <div class="form-group">
                                <label>Upload Official Invoice Bill Scan / Photo</label>
                                <input type="file" name="invoice_file" class="dash-input" accept="image/*,.pdf" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Payment & Banking Details / Notes</label>
                            <textarea name="payment_notes" class="dash-input" rows="2" placeholder="Bank account details, routing number, or treasury disbursement notes..." required></textarea>
                        </div>
                        <div style="margin-top: 15px; text-align: right;">
                            <button type="submit" class="btn-primary-blue">Submit Official Invoice & Bill Scan</button>
                        </div>
                    </form>
                </div>
            </div>

        </section>
    </main>

    <!-- NEW PROJECT MODAL -->
    <div id="newProjectModal" class="modal-overlay">
        <div class="modal-card">
            <div class="modal-header">
                <h3>Register New Ministry Project</h3>
                <button class="close-btn" onclick="toggleNewProjectModal(false)">&times;</button>
            </div>
            <form action="save_project.php" method="POST">
                <div class="modal-body">
                    <div class="form-grid-2">
                        <div class="form-group">
                            <label>Project ID / Code</label>
                            <input type="text" id="newProjId" name="newProjId" class="dash-input" placeholder="e.g. P-009" required>
                        </div>
                        <div class="form-group">
                            <label>Ministry / Department ID</label>
                            <input type="number" id="newProjMinistry" name="newProjMinistry" class="dash-input" placeholder="e.g. 1" value="1" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Project Title / Name</label>
                        <input type="text" id="newProjTitle" name="project_name" class="dash-input" placeholder="e.g. Sylhet Highway Expansion" required>
                    </div>
                    <div class="form-group">
                        <label>Scope of Work (Details)</label>
                        <textarea name="scope_of_work" class="dash-input" rows="2" placeholder="Enter project scope of work..." required></textarea>
                    </div>
                    <div class="form-grid-2">
                        <div class="form-group">
                            <label>Allocated Budget (BDT)</label>
                            <input type="number" step="0.01" id="newProjBudget" name="approved_budget" class="dash-input" placeholder="e.g. 150000000" required>
                        </div>
                        <div class="form-group">
                            <label>Project Status</label>
                            <select name="project_status" class="dash-input">
                                <option value="Ongoing">Ongoing</option>
                                <option value="Active">Active</option>
                                <option value="Under Audit">Under Audit</option>
                                <option value="Disbursing">Disbursing</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-grid-2">
                        <div class="form-group">
                            <label>Start Date</label>
                            <input type="date" id="newProjStartDate" name="project_start_date" class="dash-input" value="2026-11-01" required>
                        </div>
                        <div class="form-group">
                            <label>Expected End Date</label>
                            <input type="date" id="newProjEndDate" name="expected_end_date" class="dash-input" value="2028-11-01" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-secondary-line" onclick="toggleNewProjectModal(false)">Cancel</button>
                    <button type="submit" class="btn-primary-blue">Register & Save Project</button>
                </div>
            </form>
        </div>
    </div>

    <!-- NEW TENDER PUBLISH MODAL -->
    <div id="newTenderModal" class="modal-overlay">
        <div class="modal-card">
            <div class="modal-header">
                <h3>Publish New Tender Package</h3>
                <button class="close-btn" onclick="toggleNewTenderModal(false)">&times;</button>
            </div>
            <form action="publish_tender.php" method="POST">
                <div class="modal-body">
                    <div class="form-grid-2">
                        <div class="form-group">
                            <label>Tender Reference ID</label>
                            <input type="text" id="tenderRefInput" name="tender_reference" class="dash-input" placeholder="e.g. T-REF-905" required>
                        </div>
                        <div class="form-group">
                            <label>Select Project for Tender</label>
                            <select name="package_id" class="dash-input" required>
                                <option value="">-- Choose Project --</option>
                                <?php
                                try {
                                    $stmt_modal_proj = $pdo->query("SELECT Project_ID, Project_Name FROM project ORDER BY Project_ID DESC");
                                    while ($mp = $stmt_modal_proj->fetch(PDO::FETCH_ASSOC)) {
                                        echo '<option value="' . $mp['Project_ID'] . '">';
                                        echo 'P-' . htmlspecialchars($mp['Project_ID'] ?? '') . ': ' . htmlspecialchars($mp['Project_Name'] ?? '');
                                        echo '</option>';
                                    }
                                } catch (PDOException $e) {
                                    echo '<option value="">Error loading projects</option>';
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-grid-2">
                        <div class="form-group">
                            <label>Work Category / Package Type</label>
                            <select name="work_category" class="dash-input" required>
                                <option value="Civil Works">Civil Works</option>
                                <option value="Electrical">Electrical</option>
                                <option value="Signal System">Signal System</option>
                                <option value="Consulting">Consulting</option>
                                <option value="Mechanical Works">Mechanical Works</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Publication Date</label>
                            <input type="date" id="tenderPubDate" name="publication_date" class="dash-input" value="2026-11-01" required>
                        </div>
                    </div>
                    <div class="form-grid-2">
                        <div class="form-group">
                            <label>Submission Deadline</label>
                            <input type="date" id="tenderDeadline" name="submission_deadline" class="dash-input" value="2026-12-01" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-secondary-line" onclick="toggleNewTenderModal(false)">Cancel</button>
                    <button type="submit" class="btn-primary-blue">Publish Tender</button>
                </div>
            </form>
        </div>
    </div>

    <!-- NEW TENDER BID MODAL -->
    <div id="newBidModal" class="modal-overlay">
        <div class="modal-card">
            <div class="modal-header">
                <h3>Submit Tender Bid</h3>
                <button class="close-btn" onclick="toggleNewBidModal(false)">&times;</button>
            </div>
            <form action="submit_bid.php" method="POST">
                <div class="modal-body">
                    <div class="form-group">
                        <label>Select Tender / Package</label>
                        <select id="tenderIdInput" name="tender_id" class="dash-input" required>
                            <option value="">-- Choose Open Tender --</option>
                            <?php
                            try {
                                $stmt_b_tenders = $pdo->query("SELECT t.Tender_ID, t.Tender_Reference, p.Project_Name FROM tender t LEFT JOIN project p ON t.Package_ID = p.Project_ID WHERE t.Tender_Status='Open' ORDER BY t.Tender_ID DESC");
                                while ($bt = $stmt_b_tenders->fetch(PDO::FETCH_ASSOC)) {
                                    echo '<option value="' . $bt['Tender_ID'] . '">Tender #' . $bt['Tender_ID'] . ': ' . htmlspecialchars($bt['Project_Name'] ?? '') . ' (Ref: ' . htmlspecialchars($bt['Tender_Reference'] ?? '') . ')</option>';
                                }
                            } catch (PDOException $e) {
                                echo '<option value="">Error loading open tenders</option>';
                            }
                            ?>
                        </select>
                    </div>
                    <div class="form-grid-2">
                        <div class="form-group">
                            <label>My Bidding Amount (BDT)</label>
                            <input type="number" step="0.01" id="bidAmountInput" name="proposed_amount" class="dash-input" placeholder="e.g. 85000000" required>
                        </div>
                        <div class="form-group">
                            <label>Submission Date</label>
                            <input type="date" id="bidDateInput" name="submission_date" class="dash-input" value="<?php echo date('Y-m-d'); ?>" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-secondary-line" onclick="toggleNewBidModal(false)">Cancel</button>
                    <button type="submit" class="btn-primary-blue">Submit Official Bid</button>
                </div>
            </form>
        </div>
    </div>

    <!-- JavaScript for Accordion Tree, Evaluation & Interactive Project/Tender Audit Inspection -->
    <script>
        const allBidsData = [
            <?php
            try {
                $stmt_all_eval = $pdo->query("
                    SELECT b.Bid_ID, b.Tender_ID, b.Proposed_Amount, c.Contractor_Name
                    FROM bid b
                    LEFT JOIN contractor c ON b.Contractor_ID = c.Contractor_ID
                ");
                while ($ed = $stmt_all_eval->fetch(PDO::FETCH_ASSOC)) {
                    $tech = 80 + ($ed['Bid_ID'] % 15);
                    $fin = 75 + ($ed['Bid_ID'] % 20);
                    $total = $tech + $fin;
                    echo "{tenderId: " . $ed['Tender_ID'] . ", contractor: '" . addslashes($ed['Contractor_Name'] ?? 'Contractor') . "', amount: '" . number_format($ed['Proposed_Amount'], 2) . "', tech: " . $tech . ", fin: " . $fin . ", total: " . $total . "},\n";
                }
            } catch (PDOException $e) {}
            ?>
        ];

        function toggleProjectAccordion(subListElem) {
            if (subListElem.style.display === 'block') {
                subListElem.style.display = 'none';
            } else {
                subListElem.style.display = 'block';
            }
        }

        function loadTenderDetailsForEval(tenderId, projectName, ref, category, pubDate, deadline) {
            const section = document.getElementById('tenderEvaluationSection');
            section.style.display = 'block';

            document.getElementById('evalSectionTitle').innerText = "Active Tender Bid Evaluation: " + projectName + " (Tender #" + tenderId + " - " + category + ")";
            document.getElementById('infoRef').innerText = ref;
            document.getElementById('infoPubDate').innerText = pubDate;
            document.getElementById('infoDeadline').innerText = deadline;

            const tbody = document.getElementById('evalBidsTableBody');
            tbody.innerHTML = '';

            const filteredBids = allBidsData.filter(b => b.tenderId == tenderId);

            if (filteredBids.length > 0) {
                filteredBids.forEach((b, index) => {
                    let tr = document.createElement('tr');
                    tr.innerHTML = `
                        <td>${index + 1}</td>
                        <td><strong>${b.contractor}</strong></td>
                        <td><strong>${b.amount} BDT</strong></td>
                        <td><span style="background:#f1f5f9; padding:3px 8px; border-radius:6px; font-weight:600;">${b.tech} / 100</span></td>
                        <td><span style="background:#f1f5f9; padding:3px 8px; border-radius:6px; font-weight:600;">${b.fin} / 100</span></td>
                        <td><strong style="color:#0284c7;">${b.total}</strong></td>
                        <td>
                            <select class='dash-input' style='padding: 5px 8px; font-size: 12px; width: 160px; font-weight:600;'>
                                <option value='Under Evaluation' selected>Under Evaluation</option>
                                <option value='Selected'>✓ Selected / Winner</option>
                                <option value='Rejected'>✗ Not Selected / Rejected</option>
                            </select>
                        </td>
                    `;
                    tbody.appendChild(tr);
                });
            } else {
                let tr = document.createElement('tr');
                tr.innerHTML = `<td colspan='7' style='text-align:center; color:#64748b; font-style:italic;'>No bids submitted for this tender yet.</td>`;
                tbody.appendChild(tr);
            }

            section.scrollIntoView({ behavior: 'smooth' });
        }

        // Clicking Project Summary View (Renders all tender packages & their individual amounts in one place)
        function loadProjectAggregateAudit(info) {
            const box = document.getElementById('auditLifecycleDetailBox');
            box.style.display = 'block';

            document.getElementById('auditBoxTitle').innerText = "📁 Project Aggregate Overview: " + info.Project_Name + " (ID: P-" + info.Project_ID + ")";
            document.getElementById('auditAllocBudget').innerText = info.Approved_Budget + " BDT (" + info.Ministry_Name + ")";
            document.getElementById('auditCreator').innerText = info.Creator_Name;
            
            // Render all tender packages with individual amounts inside the overview box
            let tendersHtml = `<div style="font-weight:600; color:#0369a1; margin-bottom:4px;">Managed across ${info.Tender_Count} Active Tender Packages:</div>`;
            if (info.Tenders && info.Tenders.length > 0) {
                info.Tenders.forEach(t => {
                    tendersHtml += `
                        <div class="tender-breakdown-pill">
                            <span>📄 Tender #${t.Tender_ID} [${t.Work_Category}] — ${t.Contractor_Name}</span>
                            <span style="color:#059669; font-weight:700;">${t.Tender_Amount} BDT</span>
                        </div>
                    `;
                });
            } else {
                tendersHtml += `<span style="color:#64748b;">No active tender packages found.</span>`;
            }
            document.getElementById('auditPublisher').innerHTML = tendersHtml;

            document.getElementById('auditContractor').innerText = "Multiple Contracted Vendors";
            document.getElementById('auditTimeline').innerText = info.Project_Start_Date + " to " + info.Expected_End_Date;
            document.getElementById('auditRiskLevel').innerText = "Moderate (Contains " + info.Tender_Count + " Packages)";

            document.getElementById('auditProgressContent').innerHTML = `
                <p><strong>Project Scope:</strong> Major National Infrastructure Development</p>
                <p><strong>Total Sub-Packages:</strong> ${info.Tender_Count} Tender Packages published.</p>
                <p style="margin-top:10px; color:#0284c7; font-weight:600;">ℹ️ Click on individual tender packages below for deep inspection.</p>
            `;

            document.getElementById('auditInvoiceContent').innerHTML = `
                <p><strong>Ministry Allocation Summary:</strong> ${info.Approved_Budget} BDT</p>
                <p><strong>Treasury Gateways:</strong> Synchronized & Verified</p>
                <p style="margin-top:10px; color:#16a34a; font-weight:600;">✓ Fund flow and disbursement active.</p>
            `;

            box.scrollIntoView({ behavior: 'smooth' });
        }

        // Clicking Specific Tender View
        function loadTenderLifecycleDetails(info) {
            const box = document.getElementById('auditLifecycleDetailBox');
            box.style.display = 'block';

            document.getElementById('auditBoxTitle').innerText = "📄 Tender Package Audit Trail: " + info.Project_Name + " (Tender #" + info.Tender_ID + " - Ref: " + info.Tender_Reference + ")";
            document.getElementById('auditAllocBudget').innerText = info.Awarded_Amount + " BDT (Individual Tender Package Budget)";
            document.getElementById('auditCreator').innerText = info.Creator_Name;
            document.getElementById('auditPublisher').innerHTML = `<span style="color:#28a745; font-weight:700;">Publisher: ${info.Publisher_Name}</span><br><span style="color:#059669; font-weight:700;">Package Amount: ${info.Awarded_Amount} BDT</span>`;
            document.getElementById('auditContractor').innerText = info.Contractor_Name + " (Awarded Amount: " + info.Awarded_Amount + " BDT)";
            document.getElementById('auditTimeline').innerText = info.Project_Start_Date + " to " + info.Expected_End_Date;
            document.getElementById('auditRiskLevel').innerHTML = "<span style='color:#dc2626;'>High Risk (Invoice Claim > Physical Progress)</span>";

            document.getElementById('auditProgressContent').innerHTML = `
                <p><strong>Verified Site Progress:</strong> 35.0% Complete</p>
                <p><strong>Work Category:</strong> ${info.Work_Category}</p>
                <p><strong>Milestone Notes:</strong> Foundation piling and structural work underway as per specs.</p>
                <p style="margin-top:10px;"><strong>Attached Site Photo:</strong> <a href="uploads/progress_${info.Tender_ID}.jpg" target="_blank" style="color:#0284c7; font-weight:700; text-decoration:underline;">📷 View Uploaded Progress Photo</a></p>
            `;

            document.getElementById('auditInvoiceContent').innerHTML = `
                <p><strong>Invoice Ref:</strong> INV-2026-${info.Tender_ID}</p>
                <p><strong>Claimed Bill Amount:</strong> <span style="font-weight:700; color:#92400e;">${info.Awarded_Amount} BDT</span></p>
                <p style="margin-top:6px;"><strong>Attached Invoice Bill:</strong> <a href="uploads/invoice_${info.Tender_ID}.jpg" target="_blank" style="color:#d97706; font-weight:700; text-decoration:underline;">📄 View Uploaded Invoice Bill / Scan</a></p>
                <p style="color:#dc2626; font-weight:700; margin-top:5px;">⚠️ Discrepancy Found: Claimed bill exceeds verified milestone progress!</p>
            `;

            box.scrollIntoView({ behavior: 'smooth' });
        }

        function handleAuditAction(selectElem, alertId) {
            const val = selectElem.value;
            if (val === 'Halt Disbursement') {
                alert('🚨 Warning: Treasury disbursement successfully HALTED for Alert #' + alertId + '! Investigation notice dispatched to Ministry.');
                selectElem.style.color = '#dc2626';
            } else if (val === 'Resolved') {
                alert('✓ Discrepancy Alert #' + alertId + ' marked as Resolved and Verified by IMED.');
                selectElem.style.color = '#16a34a';
            } else {
                alert('⚠️ Alert #' + alertId + ' is currently Under Active Investigation.');
                selectElem.style.color = '#d97706';
            }
        }
    </script>

    <!-- PHP Session to JS Bridge & Navigation Handlers (Updated for Admin Full Access) -->
    <script>
        const userRole = "<?php echo htmlspecialchars($active_role ?? ''); ?>";
        const defaultTab = "<?php echo htmlspecialchars($default_tab ?? 'overview'); ?>";
        document.addEventListener("DOMContentLoaded", () => {
            const roleBadge = document.getElementById('displayRoleBadge');
            if (roleBadge) roleBadge.innerText = "<?php echo htmlspecialchars($role_title ?? ''); ?>";
            const userName = document.getElementById('displayUserName');
            if (userName) userName.innerText = "Name: " + "<?php echo htmlspecialchars($active_name ?? ''); ?>";
            const userId = document.getElementById('displayUserId');
            if (userId) userId.innerText = "ID: " + "<?php echo htmlspecialchars($active_user ?? ''); ?>";
            
            document.querySelectorAll('.role-menu-item').forEach(m => m.style.display = 'none');
            document.querySelectorAll('.content-tab').forEach(tab => tab.style.display = 'none');
            
            // Admin gets access to all modules for full system monitoring
            if (userRole === 'admin') {
                document.querySelectorAll('.role-menu-item').forEach(m => m.style.display = 'block');
            } else if (userRole === 'project_officer' || userRole === 'ministry') {
                const m = document.getElementById('menuProjectOfficer');
                if (m) m.style.display = 'block';
            } else if (userRole === 'procurement_officer') {
                ['menuProcurement', 'menuViewBidsEval'].forEach(id => {
                    const m = document.getElementById(id);
                    if (m) m.style.display = 'block';
                });
            } else if (userRole === 'reviewing_officer' || userRole === 'auditor') {
                ['menuReviewing', 'menuAuditLifecycle'].forEach(id => {
                    const m = document.getElementById(id);
                    if (m) m.style.display = 'block';
                });
            } else if (userRole === 'contractor') {
                ['menuContractorBids', 'menuWorkProgress', 'menuInvoices'].forEach(id => {
                    const m = document.getElementById(id);
                    if (m) m.style.display = 'block';
                });
            }
            const targetTab = document.getElementById('tab-' + defaultTab);
            if (targetTab) targetTab.style.display = 'block';
            const activeNav = document.getElementById('nav-' + defaultTab);
            if (activeNav) activeNav.classList.add('active');
        });

        function switchTab(tabId) {
            document.querySelectorAll('.content-tab').forEach(tab => tab.style.display = 'none');
            const target = document.getElementById('tab-' + tabId);
            if (target) target.style.display = 'block';
            document.querySelectorAll('.menu-item').forEach(item => item.classList.remove('active'));
            const activeNav = document.getElementById('nav-' + tabId);
            if (activeNav) activeNav.classList.add('active');
        }

        function openBidModalForTender(tenderId) {
            const modal = document.getElementById('newBidModal');
            if (modal) {
                modal.style.display = 'flex';
                const selectElem = document.getElementById('tenderIdInput');
                if (selectElem) {
                    selectElem.value = tenderId;
                }
            }
        }

        function toggleNewProjectModal(show) {
            const modal = document.getElementById('newProjectModal');
            if (modal) modal.style.display = show ? 'flex' : 'none';
        }

        function toggleNewTenderModal(show) {
            const modal = document.getElementById('newTenderModal');
            if (modal) modal.style.display = show ? 'flex' : 'none';
        }

        function toggleNewBidModal(show) {
            const modal = document.getElementById('newBidModal');
            if (modal) modal.style.display = show ? 'flex' : 'none';
        }

        function handleLogout() {
            sessionStorage.clear();
            window.location.href = 'logout.php';
        }
    </script>
    <script src="dashboard.js"></script>
</body>
</html>