// ======================================================== */
// GHOSTTRACK — DASHBOARD DYNAMIC ENGINE
// ======================================================== */

const projectDatabase = {
    "P-001": {
        name: "Road Rehabilitation Project",
        id: "P-001",
        ministry: "Ministry of Road Transport and Highways",
        agency: "Roads and Highways Department (RHD)",
        bids: [
            { contractor: "ABC Ltd.", amount: "8.50 Cr BDT (Within Budget)", tech: 85, fin: 88, status: "Selected", bg: "active-bg" },
            { contractor: "XYZ Ltd.", amount: "8.20 Cr BDT (Within Budget)", tech: 70, fin: 80, status: "Rejected", bg: "reject-bg" }
        ]
    },
    "PRJ-8801": {
        name: "Dhaka Mass Rapid Transit (MRT Line-6 Extension)",
        id: "PRJ-8801",
        ministry: "Ministry of Road Transport",
        agency: "Dhaka Mass Transit Company Limited (DMTCL)",
        bids: [
            { contractor: "Global Tech Infrastructure Ltd.", amount: "450 Cr BDT (Allocated: 500 Cr)", tech: 92, fin: 90, status: "Selected", bg: "active-bg" },
            { contractor: "Metro Rail Builders", amount: "520 Cr BDT (Over Budget)", tech: 78, fin: 82, status: "Rejected", bg: "reject-bg" }
        ]
    },
    "PRJ-8802": {
        name: "Padma Bridge Rail Link Project (PBRLP)",
        id: "PRJ-8802",
        ministry: "Ministry of Railways",
        agency: "Bangladesh Railway",
        bids: [
            { contractor: "Delta Engineering & Builders", amount: "280 Cr BDT (Allocated: 300 Cr)", tech: 88, fin: 85, status: "Selected", bg: "active-bg" },
            { contractor: "China Railway Group (CREC)", amount: "310 Cr BDT (Over Budget)", tech: 94, fin: 89, status: "Rejected", bg: "reject-bg" }
        ]
    },
    "PRJ-8803": {
        name: "Karnafuli Tunnel Approach Road",
        id: "PRJ-8803",
        ministry: "Ministry of Bridges",
        agency: "Bangladesh Bridge Authority",
        bids: [
            { contractor: "Chittagong Coastal Infra", amount: "115 Cr BDT (Allocated: 120 Cr)", tech: 82, fin: 84, status: "Selected", bg: "active-bg" }
        ]
    }
};

const imedFinancialDatabase = {
    "IMED-001": {
        allocBudget: "8.50 Cr BDT",
        finalBid: "8.20 Cr BDT (ABC Ltd.)",
        disbursed: "7.00 Cr BDT",
        invoiceStatus: "Status: 3 Invoices Verified & Paid",
        physicalProg: "42% Completed",
        fundUtil: "85% Disbursed (Mismatch Flagged!)",
        reason: "Fund utilized (85%) is higher than physical verified progress (42%).",
        contract: "C-001",
        severity: "High"
    },
    "IMED-002": {
        allocBudget: "32,500 Cr BDT",
        finalBid: "31,800 Cr BDT (Global Tech)",
        disbursed: "12,400 Cr BDT",
        invoiceStatus: "Status: Milestone #2 Under Audit",
        physicalProg: "38% Completed",
        fundUtil: "39% Disbursed (Balanced)",
        reason: "Duplicate invoice check triggered for milestone billing cycle #2.",
        contract: "C-002",
        severity: "Medium"
    },
    "IMED-003": {
        allocBudget: "39,240 Cr BDT",
        finalBid: "38,500 Cr BDT (Delta Eng.)",
        disbursed: "15,000 Cr BDT",
        invoiceStatus: "Status: All Clear & Disbursing",
        physicalProg: "55% Completed",
        fundUtil: "54% Disbursed (On Track)",
        reason: "No discrepancy detected. Project fund flow is perfectly synchronized.",
        contract: "C-003",
        severity: "Low"
    }
};

document.addEventListener("DOMContentLoaded", () => {
    const userRole = localStorage.getItem('ghosttrack_user_role') || 'procurement_officer';
    const userId = localStorage.getItem('ghosttrack_user_id') || 'OFF-102';

    let userName = "Rafiq Ahmed";
    if (userRole === 'reviewing_officer') userName = "Dr. Nazmul Hasan";
    else if (userRole === 'project_officer') userName = "Tanvir Rahman";
    else if (userRole === 'contractor') userName = "ABC Ltd. Vendor Rep";

    document.getElementById('displayRoleBadge').innerText = formatRoleName(userRole);
    document.getElementById('displayUserName').innerText = `Name: ${userName}`;
    document.getElementById('displayUserId').innerText = `ID: ${userId}`;

    applyRBAC(userRole);
});

function formatRoleName(role) {
    switch(role) {
        case 'project_officer': return 'Project Officer (Ministry)';
        case 'procurement_officer': return 'Procurement Officer';
        case 'reviewing_officer': return 'Reviewing Officer / IMED';
        case 'contractor': return 'Licensed Contractor';
        default: return role.toUpperCase();
    }
}

function applyRBAC(role) {
    const menuProj = document.getElementById('menuProjectOfficer');
    const menuProc = document.getElementById('menuProcurement');
    const menuRev = document.getElementById('menuReviewing');
    const menuContBids = document.getElementById('menuContractorBids');
    const menuProgress = document.getElementById('menuWorkProgress');
    const menuInvoices = document.getElementById('menuInvoices');

    if (menuProj) menuProj.style.display = 'none';
    if (menuProc) menuProc.style.display = 'none';
    if (menuRev) menuRev.style.display = 'none';
    if (menuContBids) menuContBids.style.display = 'none';
    if (menuProgress) menuProgress.style.display = 'none';
    if (menuInvoices) menuInvoices.style.display = 'none';

    if (role === 'project_officer') {
        if (menuProj) menuProj.style.display = 'block';
        switchTab('projectEntry');
    } else if (role === 'procurement_officer') {
        if (menuProc) menuProc.style.display = 'block';
        switchTab('procurementBids');
    } else if (role === 'reviewing_officer') {
        if (menuRev) menuRev.style.display = 'block';
        switchTab('discrepancies');
    } else if (role === 'contractor') {
        if (menuContBids) menuContBids.style.display = 'block';
        if (menuProgress) menuProgress.style.display = 'block';
        if (menuInvoices) menuInvoices.style.display = 'block';
        switchTab('contractorBids');
    } else {
        switchTab('overview');
    }
}

function switchTab(tabId) {
    const tabs = document.querySelectorAll('.content-tab');
    tabs.forEach(tab => tab.style.display = 'none');

    const target = document.getElementById(`tab-${tabId}`);
    if (target) target.style.display = 'block';

    const menuItems = document.querySelectorAll('.menu-item');
    menuItems.forEach(item => item.classList.remove('active'));

    const activeNav = document.getElementById(`nav-${tabId}`);
    if (activeNav) activeNav.classList.add('active');
}

function updateProcurementDetails() {
    const selectBox = document.getElementById('procurementProjectSelect');
    const selectedKey = selectBox.value;
    const projData = projectDatabase[selectedKey];

    if (projData) {
        document.getElementById('selProjName').innerText = projData.name;
        document.getElementById('selProjId').innerText = projData.id;
        document.getElementById('selProjMinistry').innerText = projData.ministry;
        document.getElementById('selProjAgency').innerText = projData.agency;

        const tbody = document.getElementById('procurementTableBody');
        tbody.innerHTML = '';

        projData.bids.forEach((bid, index) => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${index + 1}</td>
                <td>${bid.contractor}</td>
                <td><strong>${bid.amount}</strong></td>
                <td>${bid.tech}</td>
                <td>${bid.fin}</td>
                <td><span class="status-badge ${bid.bg}">${bid.status}</span></td>
            `;
            tbody.appendChild(row);
        });
    }
}

function updateImedFinancialView() {
    const selectBox = document.getElementById('imedProjectSelect');
    const key = selectBox.value;
    const data = imedFinancialDatabase[key];

    if (data) {
        document.getElementById('imedAllocBudget').innerText = data.allocBudget;
        document.getElementById('imedFinalBid').innerText = data.finalBid;
        document.getElementById('imedDisbursed').innerText = data.disbursed;
        document.getElementById('imedInvoiceStatus').innerText = data.invoiceStatus;
        document.getElementById('imedPhysicalProg').innerText = data.physicalProg;
        document.getElementById('imedFundUtil').innerText = data.fundUtil;
        document.getElementById('imedAlertReason').innerText = data.reason;
        document.getElementById('imedContractId').innerText = data.contract;
        
        const sevElem = document.getElementById('imedSeverity');
        sevElem.innerText = data.severity;
        sevElem.className = data.severity === 'High' ? 'text-red' : (data.severity === 'Medium' ? 'text-blue' : 'text-green');
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

function handleCreateNewProject(event) {
    event.preventDefault();
    const id = document.getElementById('newProjId').value;
    const title = document.getElementById('newProjTitle').value;
    const ministry = document.getElementById('newProjMinistry').value;
    const budget = document.getElementById('newProjBudget').value;
    const duration = document.getElementById('newProjDuration').value;

    const tableBody = document.getElementById('projectsTableBody');
    if (tableBody) {
        const newRow = document.createElement('tr');
        newRow.innerHTML = `
            <td>${id}</td>
            <td>${title}</td>
            <td>${ministry}</td>
            <td>${budget} Cr BDT</td>
            <td>${duration}</td>
            <td><span class="status-badge active-bg">Active / Registered</span></td>
        `;
        tableBody.appendChild(newRow);
    }

    toggleNewProjectModal(false);
    alert(`Project "${title}" successfully registered in database!`);
}

function handlePublishTender(event) {
    event.preventDefault();
    const refId = document.getElementById('tenderRefInput').value;
    const pkg = document.getElementById('tenderPkgInput').value;
    const projName = document.getElementById('tenderProjTarget').value;
    const deadline = document.getElementById('tenderDeadline').value;

    toggleNewTenderModal(false);
    alert(`Tender Package "${pkg}" (${refId}) for "${projName}" successfully published! Deadline: ${deadline}`);
}

function handleContractorBidSubmit(event) {
    event.preventDefault();
    const tenderInfo = document.getElementById('bidTenderSelect').value.split('|');
    const tenderId = tenderInfo[0];
    const tenderTitle = tenderInfo[1];
    const budget = tenderInfo[2];
    const bidAmount = document.getElementById('bidAmountInput').value;
    const subDate = document.getElementById('bidDateInput').value;

    const tableBody = document.getElementById('contractorBidsTableBody');
    if (tableBody) {
        const newRow = document.createElement('tr');
        newRow.innerHTML = `
            <td>${tenderId}</td>
            <td>${tenderTitle}</td>
            <td>${budget}</td>
            <td><strong>${bidAmount} Cr BDT</strong></td>
            <td>${subDate}</td>
            <td><span class="status-badge pending-bg">Under Evaluation</span></td>
        `;
        tableBody.appendChild(newRow);
    }

    toggleNewBidModal(false);
    alert(`Tender Bid for "${tenderTitle}" submitted successfully!`);
}

function handleLogout() {
    localStorage.clear();
    window.location.href = 'index.html';
}