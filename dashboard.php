<?php
require __DIR__ . '/includes/auth_check.php';
require __DIR__ . '/config/db.php';


// Fetch user
$stmt = $pdo->prepare("SELECT username, course_year, role FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();
if (!$user) { session_destroy(); header("Location: index.php"); exit(); }

// Stats
$totalItemsAll = (int)$pdo->query("SELECT COUNT(*) FROM items")->fetchColumn();
$unclaimed     = (int)$pdo->query("SELECT COUNT(*) FROM items WHERE status='available'")->fetchColumn();
$claimed       = (int)$pdo->query("SELECT COUNT(*) FROM items WHERE status='claimed'")->fetchColumn();
$pending       = (int)$pdo->query("SELECT COUNT(*) FROM claims WHERE claim_status='pending'")->fetchColumn();

// Items for grid (available only for students; all for admin)
if ($user['role'] === 'admin') {
    $dbItems = $pdo->query("SELECT item_id AS id, item_name, category, description, location_found, status, created_at FROM items ORDER BY created_at DESC")->fetchAll();
} else {
    $dbItems = $pdo->query("SELECT item_id AS id, item_name, category, description, location_found, status, created_at FROM items WHERE status='available' ORDER BY created_at DESC")->fetchAll();
}

// Notifications for current user
$stmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id=? AND is_read=0");
$stmt->execute([$_SESSION['user_id']]);
$unreadCount = (int)$stmt->fetchColumn();

$activePage = 'dashboard';
$rootPath   = '';
 include 'includes/sidebar.php'; 
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SU Lost & Found - Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        /* These variables must be defined for your colors to show */
    :root {
    --navy: #0B1F3A; --gold: #C9A227; --gold-light: #e8be4a;
    --white: #FFFFFF; --gray-bg: #F5F7FA; --gray-border: #E2E8F0;
    --text-main: #0B1F3A; --text-muted: #64748B; --text-light: #94A3B8;
    --radius: 10px; --radius-lg: 14px; --trans: 180ms ease-in-out;
    --badge-unclaimed: #FEF3C7; --badge-unclaimed-t: #92400E;
    --badge-claimed: #D1FAE5; --badge-claimed-t: #065F46;
    --badge-pending: #DBEAFE; --badge-pending-t: #1E40AF;
  }
  body { font-family: 'Inter', 'Poppins', system-ui, sans-serif; background: var(--gray-bg); color: var(--text-main); font-size: 14px; line-height: 1.5; }
  h2.sr-only { position: absolute; width: 1px; height: 1px; overflow: hidden; clip: rect(0,0,0,0); }

  .app { display: flex; height: 100vh; min-height: 600px; overflow: hidden; }

  /* SIDEBAR */
  .sidebar { width: 220px; min-width: 220px; background: var(--navy); display: flex; flex-direction: column; padding: 0; overflow-y: auto; }
  .sidebar-logo { padding: 20px 20px 16px; border-bottom: 1px solid rgba(255,255,255,0.08); }
  .logo-badge { display: flex; align-items: center; gap: 10px; }
  .logo-icon { width: 34px; height: 34px; background: var(--gold); border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 16px; font-weight: 700; color: var(--navy); flex-shrink: 0; }
  .logo-text { color: #fff; font-size: 13px; font-weight: 600; line-height: 1.3; }
  .logo-text span { color: var(--gold); font-size: 11px; font-weight: 400; display: block; }
  .nav-section { padding: 12px 0; flex: 1; }
  .nav-label { font-size: 10px; font-weight: 600; color: rgba(255,255,255,0.35); letter-spacing: 0.1em; text-transform: uppercase; padding: 8px 20px 4px; }
  .nav-item { display: flex; align-items: center; gap: 10px; padding: 9px 20px; cursor: pointer; color: rgba(255,255,255,0.65); font-size: 13px; font-weight: 400; transition: background var(--trans), color var(--trans); border-left: 3px solid transparent; }
  .nav-item:hover { background: rgba(255,255,255,0.06); color: #fff; }
  .nav-item.active { background: rgba(201,162,39,0.12); color: var(--gold); border-left-color: var(--gold); }
  .nav-icon { font-size: 15px; width: 18px; text-align: center; }
  .nav-badge { margin-left: auto; background: var(--gold); color: var(--navy); border-radius: 999px; font-size: 10px; font-weight: 700; padding: 1px 6px; }
  .sidebar-footer { padding: 14px 20px; border-top: 1px solid rgba(255,255,255,0.08); }
  .user-mini { display: flex; align-items: center; gap: 10px; cursor: pointer; padding: 6px 0; }
  .avatar-sm { width: 30px; height: 30px; border-radius: 50%; background: var(--gold); color: var(--navy); font-size: 11px; font-weight: 700; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
  .user-info-sm { color: rgba(255,255,255,0.8); font-size: 12px; }
  .user-info-sm span { display: block; color: rgba(255,255,255,0.4); font-size: 10px; }

  .main { flex: 1; display: flex; flex-direction: column; overflow: hidden; min-width: 0; }

   /* HEADER */
  .topbar { background: var(--white); border-bottom: 1px solid var(--gray-border); padding: 0 24px; height: 58px; display: flex; align-items: center; gap: 16px; flex-shrink: 0; }
  .search-wrap { flex: 1; max-width: 380px; position: relative; }
  .search-wrap input { width: 100%; background: var(--gray-bg); border: 1px solid var(--gray-border); border-radius: 8px; padding: 8px 12px 8px 34px; font-size: 13px; color: var(--text-main); outline: none; transition: border var(--trans); }
  .search-wrap input:focus { border-color: var(--gold); }
  .search-icon { position: absolute; left: 10px; top: 50%; transform: translateY(-50%); color: var(--text-light); font-size: 14px; pointer-events: none; }
  .topbar-actions { display: flex; align-items: center; gap: 12px; margin-left: auto; }
  .icon-btn { position: relative; width: 36px; height: 36px; border-radius: 8px; border: 1px solid var(--gray-border); background: transparent; cursor: pointer; display: flex; align-items: center; justify-content: center; font-size: 16px; color: var(--text-muted); transition: background var(--trans), border-color var(--trans); }
  .icon-btn:hover { background: var(--gray-bg); border-color: var(--gold); }
  .notif-dot { position: absolute; top: 6px; right: 6px; width: 7px; height: 7px; background: #EF4444; border-radius: 50%; border: 1.5px solid var(--white); }
  .avatar-btn { width: 36px; height: 36px; border-radius: 50%; background: var(--navy); color: var(--gold); font-size: 12px; font-weight: 700; display: flex; align-items: center; justify-content: center; cursor: pointer; border: 2px solid var(--gold); }
  .report-btn { background: var(--gold); color: var(--navy); border: none; border-radius: 8px; padding: 8px 14px; font-size: 12px; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 6px; transition: background var(--trans); white-space: nowrap; }
  .report-btn:hover { background: var(--gold-light); }

  /* CONTENT */
  .content { flex: 1; overflow-y: auto; padding: 22px 24px; }
  .page-header { margin-bottom: 18px; }
  .page-title { font-size: 18px; font-weight: 600; color: var(--text-main); }
  .page-sub { font-size: 12px; color: var(--text-muted); margin-top: 2px; }

   /* STATS */
  .stats-row { display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px; margin-bottom: 20px; }
  .stat-card { background: var(--white); border: 1px solid var(--gray-border); border-radius: var(--radius); padding: 14px 16px; }
  .stat-label { font-size: 11px; color: var(--text-muted); font-weight: 500; text-transform: uppercase; letter-spacing: 0.06em; margin-bottom: 6px; }
  .stat-value { font-size: 24px; font-weight: 700; color: var(--navy); line-height: 1; }
  .stat-meta { font-size: 11px; color: var(--text-light); margin-top: 4px; }
  .stat-accent { border-left: 3px solid var(--gold); }

   /* FILTERS */
  .filter-bar { background: var(--white); border: 1px solid var(--gray-border); border-radius: var(--radius); padding: 12px 16px; margin-bottom: 18px; display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }
  .filter-group { display: flex; align-items: center; gap: 6px; }
  .filter-label { font-size: 11px; font-weight: 600; color: var(--text-muted); white-space: nowrap; }
  .filter-select { background: var(--gray-bg); border: 1px solid var(--gray-border); border-radius: 6px; padding: 5px 10px; font-size: 12px; color: var(--text-main); outline: none; cursor: pointer; transition: border-color var(--trans); }
  .filter-select:focus, .filter-select:hover { border-color: var(--gold); }
  .sort-btn { margin-left: auto; background: var(--navy); color: var(--white); border: none; border-radius: 6px; padding: 5px 12px; font-size: 12px; font-weight: 500; cursor: pointer; display: flex; align-items: center; gap: 5px; transition: opacity var(--trans); }
  .sort-btn:hover { opacity: 0.85; }

    /* ITEMS GRID */
  .section-meta { display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px; }
  .section-title { font-size: 14px; font-weight: 600; color: var(--text-main); }
  .section-count { font-size: 12px; color: var(--text-muted); }
  .items-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 14px; }
  .item-card { background: var(--white); border: 1px solid var(--gray-border); border-radius: var(--radius-lg); overflow: hidden; cursor: pointer; transition: transform var(--trans), box-shadow var(--trans), border-color var(--trans); }
  .item-card:hover { transform: translateY(-2px); box-shadow: 0 8px 24px rgba(11,31,58,0.1); border-color: var(--gold); }
  .card-img { height: 130px; background: var(--gray-bg); display: flex; align-items: center; justify-content: center; font-size: 44px; position: relative; }
  .card-img.electronics { background: #EFF6FF; }
  .card-img.ids { background: #F0FDF4; }
  .card-img.accessories { background: #FFF7ED; }
  .card-img.clothing { background: #FDF4FF; }
  .card-img.bags { background: #F0F9FF; }
  .card-img.books { background: #FEFCE8; }
  .card-body { padding: 12px 14px; }
  .card-title { font-size: 13px; font-weight: 600; color: var(--text-main); margin-bottom: 4px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
  .card-meta { display: flex; align-items: center; gap: 6px; flex-wrap: wrap; margin-bottom: 8px; }
  .tag { font-size: 10px; font-weight: 500; padding: 2px 7px; border-radius: 999px; display: inline-flex; align-items: center; gap: 3px; }
  .tag-loc { background: #F1F5F9; color: #475569; }
  .tag-date { background: #F8FAFC; color: #94A3B8; border: 1px solid #E2E8F0; }
  .badge { font-size: 10px; font-weight: 600; padding: 2px 8px; border-radius: 999px; display: inline-block; }
  .badge-unclaimed { background: var(--badge-unclaimed); color: var(--badge-unclaimed-t); }
  .badge-claimed { background: var(--badge-claimed); color: var(--badge-claimed-t); }
  .badge-pending { background: var(--badge-pending); color: var(--badge-pending-t); }
  .card-footer { display: flex; align-items: center; justify-content: space-between; margin-top: 8px; }
  .view-btn { font-size: 11px; font-weight: 600; color: var(--navy); background: var(--gray-bg); border: 1px solid var(--gray-border); border-radius: 6px; padding: 4px 10px; cursor: pointer; transition: background var(--trans), border-color var(--trans), color var(--trans); }
  .view-btn:hover { background: var(--gold); border-color: var(--gold); color: var(--navy); }
  .claim-btn { font-size: 11px; font-weight: 600; color: var(--white); background: var(--navy); border: none; border-radius: 6px; padding: 4px 10px; cursor: pointer; transition: opacity var(--trans); }
  .claim-btn:hover { opacity: 0.8; }

    /* MODAL */
  .modal-overlay { display: none; position: fixed; inset: 0; background: rgba(11,31,58,0.55); z-index: 100; align-items: center; justify-content: center; }
  .modal-overlay.open { display: flex; }
  .modal { background: var(--white); border-radius: var(--radius-lg); width: 480px; max-width: 96vw; overflow: hidden; }
  .modal-img { height: 180px; display: flex; align-items: center; justify-content: center; font-size: 72px; background: #EFF6FF; }
  .modal-body { padding: 20px 22px; }
  .modal-header { display: flex; align-items: flex-start; justify-content: space-between; margin-bottom: 12px; }
  .modal-title { font-size: 16px; font-weight: 700; color: var(--navy); }
  .close-btn { width: 28px; height: 28px; border-radius: 6px; border: 1px solid var(--gray-border); background: transparent; cursor: pointer; font-size: 16px; color: var(--text-muted); display: flex; align-items: center; justify-content: center; }
  .close-btn:hover { background: var(--gray-bg); }
  .modal-row { display: flex; gap: 8px; margin-bottom: 8px; align-items: center; }
  .modal-key { font-size: 12px; color: var(--text-muted); min-width: 80px; }
  .modal-val { font-size: 13px; font-weight: 500; color: var(--text-main); }
  .modal-divider { height: 1px; background: var(--gray-border); margin: 14px 0; }
  .modal-actions { display: flex; gap: 10px; }
  .btn-primary { flex: 1; background: var(--gold); color: var(--navy); border: none; border-radius: 8px; padding: 10px; font-size: 13px; font-weight: 700; cursor: pointer; transition: background var(--trans); }
  .btn-primary:hover { background: var(--gold-light); }
  .btn-secondary { flex: 1; background: var(--gray-bg); color: var(--text-main); border: 1px solid var(--gray-border); border-radius: 8px; padding: 10px; font-size: 13px; font-weight: 600; cursor: pointer; }

   /* NOTIFICATION PANEL */
  .notif-panel { display: none; position: absolute; top: 58px; right: 60px; width: 290px; background: var(--white); border: 1px solid var(--gray-border); border-radius: var(--radius-lg); z-index: 50; box-shadow: 0 10px 30px rgba(11,31,58,0.12); }
  .notif-panel.open { display: block; }
  .notif-head { padding: 12px 14px; border-bottom: 1px solid var(--gray-border); font-size: 13px; font-weight: 600; display: flex; justify-content: space-between; align-items: center; }
  .notif-clear { font-size: 11px; color: var(--gold); cursor: pointer; font-weight: 400; }
  .notif-item { padding: 10px 14px; border-bottom: 1px solid var(--gray-border); display: flex; gap: 10px; align-items: flex-start; }
  .notif-dot2 { width: 8px; height: 8px; background: var(--gold); border-radius: 50%; margin-top: 4px; flex-shrink: 0; }
  .notif-text { font-size: 12px; color: var(--text-main); line-height: 1.4; }
  .notif-time { font-size: 10px; color: var(--text-light); margin-top: 2px; }
  

  
    </style>
    </head>
    <body>
  
<div class="app">

 <!-- MAIN AREA -->
<main class="main">
  <!-- TOPBAR -->
    <header class="topbar" style="position:relative;">
        <div class="search-wrap">
            <span class="search-icon">⌕</span>
            <input type="text" placeholder="Search items " id="searchInput" oninput="filterItems()">
        </div>
        
        <div class="topbar-actions">
            <button class="icon-btn" onclick="toggleNotif()" title="Notifications">
                🔔<span class="notif-dot"></span>
            </button>
            
            <div class="avatar-btn">
                <?php 
                    $nameParts = explode(' ', $user['username']);
                    echo strtoupper(substr($nameParts[0], 0, 1) . (isset($nameParts[1]) ? substr($nameParts[1], 0, 1) : '')); 
                ?>
            </div>
            
            <button class="report-btn" onclick="openReport()">＋ Report Item</button>
        </div>

        <div class="notif-panel" id="notifPanel">
            <div class="notif-head">Notifications <span class="notif-clear">Mark all read</span></div>
            <div class="notif-item">
                <div class="notif-dot2"></div>
                <div>
                    <div class="notif-text">Welcome back, <?php echo htmlspecialchars($user['username']); ?>!</div>
                    <div class="notif-time">Just now</div>
                </div>
            </div>
        </div>
    </header>

    <div class="content">
        <div class="page-header">
            <div class="page-title">Lost &amp; Found Dashboard</div>
            <div class="page-sub">
                There are currently <strong><?php echo $totalItems; ?></strong> available items on campus. 
                Keep checking to find your lost property!
            </div>
        </div>
    <!-- STATS -->
    <div class="stats-row">
  <div class="stat-card stat-accent">
    <div class="stat-label">Total Items</div>
    <div class="stat-value"><?= $totalItemsAll ?></div>
    <div class="stat-meta">This semester</div>
  </div>
  
  <div class="stat-card">
    <div class="stat-label">Unclaimed</div>
    <div class="stat-value" style="color:#D97706;"><?= $unclaimed ?></div>
    <div class="stat-meta">Awaiting owners</div>
  </div>
  
  <div class="stat-card">
    <div class="stat-label">Claimed</div>
    <div class="stat-value" style="color:#059669;"><?= $claimed ?></div>
    <div class="stat-meta">Successfully returned</div>
  </div>
  
  <div class="stat-card">
    <div class="stat-label">Pending Review</div>
    <div class="stat-value" style="color:#2563EB;"><?= $pending ?></div>
    <div class="stat-meta">Awaiting admin</div>
  </div>
</div>

 <!-- FILTERS -->
      <div class="filter-bar">
        <div class="filter-group">
          <span class="filter-label">Category</span>
          <select class="filter-select" id="catFilter" onchange="filterItems()">
            <option value="">All</option>
            <option value="Electronics">Electronics</option>
            <option value="ID/Cards">IDs &amp; Cards</option>
            <option value="Accessories">Accessories</option>
            <option value="Clothing">Clothing</option>
            <option value="Bags">Bags</option>
            <option value="Books">Books</option>
          </select>
        </div>
        <div class="filter-group">
          <span class="filter-label">Location</span>
          <select class="filter-select" id="locFilter" onchange="filterItems()">
            <option value="">All Locations</option>
            <option>Library</option>
            <option>Cafeteria</option>
            <option>Main Gate</option>
            <option>Block C Lecture</option>
            <option>Sports Complex</option>
            <option>Admin Block</option>
          </select>
        </div>
        <div class="filter-group">
          <span class="filter-label">Status</span>
          <select class="filter-select" id="statusFilter" onchange="filterItems()">
            <option value="">All Status</option>
            <option>Unclaimed</option>
            <option>Claimed</option>
            <option>Pending</option>
          </select>
        </div>
        <button class="sort-btn" onclick="toggleSort()">↕ <span id="sortLabel">Newest</span></button>
      </div>

      <!-- ITEMS GRID -->
      <div class="section-meta">
        <div class="section-title">Found Items</div>
        <div class="section-count" id="itemCount">Showing 9 items</div>
      </div>
      <div class="items-grid" id="itemsGrid"></div>
    </div>
  </main>
</div>

<!-- MODAL -->
<div class="modal-overlay" id="modalOverlay" onclick="closeModal(event)">
  <div class="modal" id="modal">
    <div class="modal-img" id="modalImg">💻</div>
    <div class="modal-body">
      <div class="modal-header">
        <div>
          <div class="modal-title" id="modalTitle">HP Laptop</div>
          <div style="margin-top:4px;" id="modalBadge"><span class="badge badge-unclaimed">Unclaimed</span></div>
        </div>
        <button class="close-btn" onclick="closeModalDirect()">✕</button>
      </div>
      <div class="modal-row"><span class="modal-key">Category</span><span class="modal-val" id="modalCat">Electronics</span></div>
      <div class="modal-row"><span class="modal-key">Location</span><span class="modal-val" id="modalLoc">Library, Floor 2</span></div>
      <div class="modal-row"><span class="modal-key">Date Found</span><span class="modal-val" id="modalDate">May 2, 2026</span></div>
      <div class="modal-row"><span class="modal-key">Item ID</span><span class="modal-val" id="modalId">#LF-0084</span></div>
      <div class="modal-row"><span class="modal-key">Description</span><span class="modal-val" id="modalDesc">Silver HP laptop found on study table. Charger included.</span></div>
      <div class="modal-divider"></div>
      <div class="modal-actions">
        <<button class="btn-primary" onclick="initiateClaim(${item.id})">Submit Claim ↗</button>button class="btn-primary" onclick="sendPrompt('How do I submit a claim for a lost laptop at Strathmore University Lost and Found?')">Submit Claim ↗</button>
        <button class="btn-secondary" onclick="closeModalDirect()">Close</button>
      </div>
    </div>
  </div>
</div>

<script>
// 1. DYNAMIC DATA SOURCE
// This line injects your HeidiSQL rows into JavaScript automatically
const ITEMS = <?php echo json_encode($dbItems); ?>;

let sortAsc = false;
let currentItems = [...ITEMS];

// Helper to handle status styling
function badgeClass(s) {
    const status = s.toLowerCase();
    if (status === 'available' || status === 'unclaimed') return 'badge-unclaimed';
    if (status === 'claimed') return 'badge-claimed';
    return 'badge-pending';
}

// Helper to handle category colors (matches your CSS classes)
function getBgClass(cat) {
    const mapping = {
        'Electronics': 'electronics',
        'ID/Cards': 'ids',
        'Bags': 'bags',
        'Accessories': 'accessories',
        'Books': 'books',
        'Clothing': 'clothing'
    };
    return mapping[cat] || 'default-bg';
}

// Helper for Emojis
function getEmoji(cat) {
    const icons = {
        'Electronics': '💻', 'ID/Cards': '🪪', 'Bags': '🎒', 
        'Accessories': '⌚', 'Books': '📚', 'Clothing': '🧥'
    };
    return icons[cat] || '📦';
}

function renderCard(it) {
    // We use it.id directly from the database row
    return `
    <div class="item-card" onclick="openModalById(${it.id})">
        <div class="card-img ${getBgClass(it.category)}">${getEmoji(it.category)}</div>
        <div class="card-body">
            <div class="card-title">${it.item_name}</div>
            <div class="card-meta">
                <span class="tag tag-loc">📍 ${it.location_found}</span>
                <span class="tag tag-date">${formatDate(it.created_at)}</span>
            </div>
            <span class="badge ${badgeClass(it.status)}">${it.status}</span>
            <div class="card-footer">
                <button class="view-btn" onclick="event.stopPropagation();openModalById(${it.id})">View Details</button>
                ${it.status === 'available' ? `<button class="claim-btn" onclick="event.stopPropagation();openModalById(${it.id})">Claim</button>` : ''}
            </div>
        </div>
    </div>`;
}

function filterItems() {
    const q = document.getElementById('searchInput').value.toLowerCase();
    const cat = document.getElementById('catFilter').value;
    const loc = document.getElementById('locFilter').value;
    const st = document.getElementById('statusFilter').value;

    currentItems = ITEMS.filter(it => {
        const matchesSearch = !q || 
            it.item_name.toLowerCase().includes(q) || 
            it.location_found.toLowerCase().includes(q) || 
            it.description.toLowerCase().includes(q);
        
        const matchesCat = !cat || it.category === cat;
        const matchesLoc = !loc || it.location_found === loc;
        const matchesStatus = !st || it.status.toLowerCase() === st.toLowerCase();

        return matchesSearch && matchesCat && matchesLoc && matchesStatus;
    });
    renderGrid();
}

function renderGrid() {
    const grid = document.getElementById('itemsGrid');
    grid.innerHTML = currentItems.length 
        ? currentItems.map(renderCard).join('') 
        : '<div style="grid-column:1/-1;text-align:center;padding:40px;color:var(--text-muted);font-size:13px;">No items found matching your filters.</div>';
    
    document.getElementById('itemCount').textContent = `Showing ${currentItems.length} item${currentItems.length !== 1 ? 's' : ''}`;
}

function toggleSort() {
    sortAsc = !sortAsc;
    currentItems.reverse(); // Since initial PHP fetch is usually DESC, reversing gives ASC
    document.getElementById('sortLabel').textContent = sortAsc ? 'Oldest' : 'Newest';
    renderGrid();
}

// Open modal using the database ID
function openModalById(id) {
    const it = ITEMS.find(item => item.id == id);
    if (!it) return;

    document.getElementById('modalImg').textContent = getEmoji(it.category);
    document.getElementById('modalImg').className = `modal-img ${getBgClass(it.category)}`;
    document.getElementById('modalTitle').textContent = it.item_name;
    document.getElementById('modalBadge').innerHTML = `<span class="badge ${badgeClass(it.status)}">${it.status}</span>`;
    document.getElementById('modalCat').textContent = it.category;
    document.getElementById('modalLoc').textContent = it.location_found;
    document.getElementById('modalDate').textContent = formatDate(it.created_at);
    document.getElementById('modalId').textContent = '#LF-' + String(it.id).padStart(4, '0');
    document.getElementById('modalDesc').textContent = it.description;
    document.getElementById('modalOverlay').classList.add('open');
}

function formatDate(dateString) {
    const options = { month: 'short', day: 'numeric', year: 'numeric' };
    return new Date(dateString).toLocaleDateString(undefined, options);
}

function closeModal(e) { if(e.target === document.getElementById('modalOverlay')) closeModalDirect(); }
function closeModalDirect() { document.getElementById('modalOverlay').classList.remove('open'); }

let notifOpen = false;
function toggleNotif() {
    notifOpen = !notifOpen;
    document.getElementById('notifPanel').classList.toggle('open', notifOpen);
}

function openReport() { 
    window.location.href = 'report_item.php'; 
}

// Initial Run
renderGrid();
</script>
   

</body>
