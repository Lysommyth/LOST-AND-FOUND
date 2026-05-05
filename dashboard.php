<?php
/*session_start();
require 'config/db.php';

// 1. Check if the student is logged in
// If this is enabled and you aren't logged in, you will be kicked to index.php
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

// 2. Fetch the logged-in student's details
$stmt = $pdo->prepare("SELECT username, full_name, course_year FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

// 3. Count available items for the badge
$countItemsStmt = $pdo->query("SELECT COUNT(*) FROM items WHERE status = 'available'");
$totalItems = $countItemsStmt->fetchColumn();

// 4. Count pending claims for the admin badge
$countClaimsStmt = $pdo->query("SELECT COUNT(*) FROM claims WHERE claim_status = 'pending'");
$pendingClaims = $countClaimsStmt->fetchColumn();

// 5. Fetch all found items for the dashboard feed
$itemStmt = $pdo->query("SELECT * FROM items ORDER BY created_at DESC");
$dbItems = $itemStmt->fetchAll(PDO::FETCH_ASSOC);*/
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

  
    </style>
    </head>
    <body>
  
<div class="app">
  <!-- SIDEBAR -->
  <aside class="sidebar">
  <div class="sidebar-logo">
    <div class="logo-badge">
  <div class="logo-icon">
    <!-- Reference the image relative to the htdocs folder -->
    <img src="/LOST AND FOUND/strathmorelogo.png" alt="SU Logo" style="height: 32px; width: auto;">
  </div>
  <div class="logo-text">Lost &amp; Found<span>Strathmore University</span></div>
  </div>

  </div>
  
  <nav class="nav-section">
    <div class="nav-label">Main</div>
    <div class="nav-item active"><span class="nav-icon">◈</span> Dashboard</div>
    <div class="nav-item" onclick="openReport()"><span class="nav-icon">＋</span> Report Item</div>
    
    <div class="nav-item">
      <span class="nav-icon">◉</span> Browse Items 
      <span class="nav-badge"><?php echo $totalItems; ?></span>
    </div>
    
    <div class="nav-item"><span class="nav-icon">♦</span> My Reports</div>
    
    <div class="nav-label">Admin</div>
    <div class="nav-item"><span class="nav-icon">⊞</span> Admin Panel</div>
    
    <div class="nav-item">
      <span class="nav-icon">↺</span> Claim Reviews 
      <?php if ($pendingClaims > 0): ?>
          <span class="nav-badge"><?php echo $pendingClaims; ?></span>
      <?php endif; ?>
    </div>
    
    <div class="nav-item"><span class="nav-icon">≡</span> Reports &amp; Logs</div>
    <div class="nav-label">Account</div>
    <a href="logout.php" style="text-decoration: none;">
        <div class="nav-item" style="color: #ff6b6b;">
            <span class="nav-icon">⏻</span> Logout
        </div>
    </a>
  </nav>

  <div class="sidebar-footer">
    <div class="user-mini">
      <div class="avatar-sm"><?php echo strtoupper(substr($user['username'], 0, 2)); ?></div>
      <div class="user-info-sm">
        <?php echo htmlspecialchars($user['full_name'] ?? $user['username']); ?>
        <span>Student · <?php echo htmlspecialchars($user['course_year'] ?? 'N/A'); ?></span>
      </div>
    </div>
  </div>
</aside>

  

</div>
   

</body>