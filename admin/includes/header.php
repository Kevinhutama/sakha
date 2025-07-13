<!--  Header Start -->
<header class="app-header">
  <nav class="navbar navbar-expand-lg navbar-light">
    <ul class="navbar-nav">
      <li class="nav-item d-block d-xl-none">
        <a class="nav-link sidebartoggler " id="headerCollapse" href="javascript:void(0)">
          <i class="ti ti-menu-2"></i>
        </a>
      </li>
      <li class="nav-item dropdown">
        <?php
        // Get notification data if user is logged in
        $unreadCount = 0;
        $notifications = [];
        
        if (isLoggedIn()) {
            try {
                $database = new Database();
                $db = $database->getConnection();
                $notificationManager = new NotificationManager($db);
                
                $admin_id = $_SESSION['admin_id'];
                $unreadCount = $notificationManager->getUnreadCount($admin_id);
                $notifications = $notificationManager->getRecentNotifications($admin_id, 5);
            } catch (Exception $e) {
                error_log('Notification error: ' . $e->getMessage());
            }
        }
        ?>
        <a class="nav-link position-relative" href="javascript:void(0)" id="drop1" data-bs-toggle="dropdown" aria-expanded="false">
          <iconify-icon icon="solar:bell-linear" class="fs-6"></iconify-icon>
          <?php if ($unreadCount > 0): ?>
            <span class="position-absolute badge rounded-pill bg-danger" style="top: 10px; right: -8px; font-size: 0.65rem;">
              <?php echo $unreadCount > 9 ? '9+' : $unreadCount; ?>
              <span class="visually-hidden">unread notifications</span>
            </span>
          <?php endif; ?>
        </a>
        <div class="dropdown-menu dropdown-menu-animate-up dropdown-menu-end" aria-labelledby="drop1" style="min-width: 350px; right: 0; left: 0; transform: translateX(0)!important;">
          <div class="d-flex align-items-center justify-content-between px-3 py-2 border-bottom">
            <h6 class="mb-0 fw-semibold">Notifications</h6>
            <?php if ($unreadCount > 0): ?>
              <button class="btn btn-sm btn-outline-primary" onclick="markAllAsRead()" title="Mark all as read">
                <i class="ti ti-check fs-5"></i>
              </button>
            <?php endif; ?>
          </div>
          <div class="message-body" style="max-height: 400px; overflow-y: auto;">
            <?php if (empty($notifications)): ?>
              <div class="text-center py-3">
                <i class="ti ti-bell-off fs-1 text-muted"></i>
                <p class="text-muted mb-0">No notifications yet</p>
              </div>
            <?php else: ?>
              <?php foreach ($notifications as $notification): ?>
                <div class="notification-item px-3 py-2 <?php echo $notification['status'] === 'unread' ? 'bg-light' : ''; ?>" 
                     onclick="handleNotificationClick(<?php echo $notification['id']; ?>, '<?php echo $notification['action_url']; ?>')"
                     style="cursor: pointer; border-bottom: 1px solid #eee;">
                  <div class="d-flex align-items-start">
                    <div class="flex-shrink-0 me-3">
                      <i class="ti <?php echo $notificationManager->getTypeIcon($notification['type']); ?> 
                         text-<?php echo $notificationManager->getTypeColor($notification['type']); ?> fs-5"></i>
                    </div>
                    <div class="flex-grow-1">
                      <h6 class="mb-1 fs-3 <?php echo $notification['status'] === 'unread' ? 'fw-bold' : ''; ?>">
                        <?php echo htmlspecialchars($notification['title']); ?>
                      </h6>
                      <p class="mb-1 fs-2 text-muted">
                        <?php echo htmlspecialchars(substr($notification['message'], 0, 100)); ?>
                        <?php if (strlen($notification['message']) > 100): ?>...<?php endif; ?>
                      </p>
                      <small class="text-muted">
                        <?php echo date('M j, Y g:i A', strtotime($notification['created_at'])); ?>
                      </small>
                    </div>
                    <?php if ($notification['status'] === 'unread'): ?>
                      <div class="flex-shrink-0">
                        <span class="badge bg-primary rounded-pill">New</span>
                      </div>
                    <?php endif; ?>
                  </div>
                </div>
              <?php endforeach; ?>
              <?php if (count($notifications) >= 5): ?>
                <div class="text-center py-2">
                  <a href="notifications.php" class="btn btn-sm btn-outline-primary">
                    View All Notifications
                  </a>
                </div>
              <?php endif; ?>
            <?php endif; ?>
          </div>
        </div>
      </li>
    </ul>
    <div class="navbar-collapse justify-content-end px-0" id="navbarNav">
      <ul class="navbar-nav flex-row ms-auto align-items-center justify-content-end">
        <?php if (isLoggedIn()): ?>
          <li class="nav-item me-3">
            <span class="navbar-text text-muted">
              Welcome, <?php echo htmlspecialchars($_SESSION['admin_name'] ?? 'Admin'); ?>
            </span>
          </li>
        <?php endif; ?>
        <li class="nav-item dropdown">
          <a class="nav-link " href="javascript:void(0)" id="drop2" data-bs-toggle="dropdown"
            aria-expanded="false">
            <img src="assets/images/profile/user-1.jpg" alt="" width="35" height="35" class="rounded-circle">
          </a>
          <div class="dropdown-menu dropdown-menu-end dropdown-menu-animate-up" aria-labelledby="drop2">
            <div class="message-body">
              <?php if (isLoggedIn()): ?>
                <div class="px-3 py-2 border-bottom">
                  <p class="mb-1 fs-3 fw-semibold"><?php echo htmlspecialchars($_SESSION['admin_name'] ?? 'Admin'); ?></p>
                  <p class="mb-0 fs-2 text-muted"><?php echo htmlspecialchars($_SESSION['admin_email'] ?? ''); ?></p>
                  <small class="text-muted">Role: <?php echo ucfirst(htmlspecialchars($_SESSION['admin_role'] ?? 'admin')); ?></small>
                </div>
              <?php endif; ?>
              <a href="javascript:void(0)" class="d-flex align-items-center gap-2 dropdown-item">
                <i class="ti ti-user fs-6"></i>
                <p class="mb-0 fs-3">My Profile</p>
              </a>
              <a href="javascript:void(0)" class="d-flex align-items-center gap-2 dropdown-item">
                <i class="ti ti-mail fs-6"></i>
                <p class="mb-0 fs-3">My Account</p>
              </a>
              <a href="javascript:void(0)" class="d-flex align-items-center gap-2 dropdown-item">
                <i class="ti ti-list-check fs-6"></i>
                <p class="mb-0 fs-3">My Task</p>
              </a>
              <div class="dropdown-divider"></div>
              <a href="./logout.php" class="btn btn-outline-primary mx-3 mt-2 d-block">
                <i class="ti ti-logout fs-6 me-2"></i>
                Logout
              </a>
            </div>
          </div>
        </li>
      </ul>
    </div>
  </nav>
</header>
<!--  Header End -->

<script>
// Handle notification click
function handleNotificationClick(notificationId, actionUrl) {
    // Mark notification as read via AJAX
    fetch('notifications-handler.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'action=mark_read&notification_id=' + notificationId
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update notification count
            updateNotificationCount();
            
            // Remove 'New' badge and styling
            const notificationItem = document.querySelector(`[onclick*="${notificationId}"]`);
            if (notificationItem) {
                notificationItem.classList.remove('bg-light');
                const newBadge = notificationItem.querySelector('.badge');
                if (newBadge) newBadge.remove();
                const title = notificationItem.querySelector('h6');
                if (title) title.classList.remove('fw-bold');
            }
            
            // Navigate to action URL if provided
            if (actionUrl && actionUrl !== 'null') {
                window.location.href = actionUrl;
            }
        }
    })
    .catch(error => {
        console.error('Error marking notification as read:', error);
    });
}

// Mark all notifications as read
function markAllAsRead() {
    fetch('notifications-handler.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'action=mark_all_read'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Reload the page to refresh notification display
            window.location.reload();
        }
    })
    .catch(error => {
        console.error('Error marking all notifications as read:', error);
    });
}

// Update notification count badge
function updateNotificationCount() {
    fetch('notifications-handler.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'action=get_count'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const badge = document.querySelector('.badge.bg-danger');
            const markAllBtn = document.querySelector('button[onclick="markAllAsRead()"]');
            
            if (data.count > 0) {
                if (badge) {
                    badge.textContent = data.count > 9 ? '9+' : data.count;
                } else {
                    // Create badge if it doesn't exist
                    const bellIcon = document.querySelector('#drop1');
                    const newBadge = document.createElement('span');
                    newBadge.className = 'position-absolute badge rounded-pill bg-danger';
                    newBadge.style.top = '-2px';
                    newBadge.style.right = '-8px';
                    newBadge.style.fontSize = '0.65rem';
                    newBadge.innerHTML = `${data.count > 9 ? '9+' : data.count}<span class="visually-hidden">unread notifications</span>`;
                    bellIcon.appendChild(newBadge);
                }
            } else {
                if (badge) badge.remove();
                if (markAllBtn) markAllBtn.remove();
            }
        }
    })
    .catch(error => {
        console.error('Error updating notification count:', error);
    });
}
</script> 