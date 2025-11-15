<?php
session_start();
include('dbcon.php');

$admin_id = $_SESSION['admin_id'] ?? null;
if (!$admin_id) {
    header("Location: admin_login.php");
    exit;
}

// Pagination
$records_per_page = 20;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $records_per_page;

// Filter options
$filter_type = $_GET['filter'] ?? 'all';
$search = $_GET['search'] ?? '';

// Build query based on filters
$where_clause = "WHERE 1=1";
if ($filter_type !== 'all') {
    $where_clause .= " AND action LIKE '%" . $conn->real_escape_string($filter_type) . "%'";
}
if ($search) {
    $where_clause .= " AND (username LIKE '%" . $conn->real_escape_string($search) . "%' OR action LIKE '%" . $conn->real_escape_string($search) . "%')";
}

// Get total count
$count_query = "SELECT COUNT(*) as total FROM activity_log $where_clause";
$count_result = $conn->query($count_query);
$total_records = $count_result->fetch_assoc()['total'];
$total_pages = ceil($total_records / $records_per_page);

// Fetch activity logs
$logs_query = "SELECT * FROM activity_log 
               $where_clause
               ORDER BY activity_log_id DESC 
               LIMIT $records_per_page OFFSET $offset";
$logs = $conn->query($logs_query);

// Get user login logs
$user_logs_query = "SELECT ul.*, u.firstname, u.lastname, u.user_type 
                    FROM user_log ul
                    LEFT JOIN users u ON ul.user_id = u.user_id
                    ORDER BY ul.user_log_id DESC 
                    LIMIT 10";
$user_logs = $conn->query($user_logs_query);

// Get statistics
$total_activities = $conn->query("SELECT COUNT(*) as count FROM activity_log")->fetch_assoc()['count'];
$total_logins = $conn->query("SELECT COUNT(*) as count FROM user_log")->fetch_assoc()['count'];
$today_activities = $conn->query("SELECT COUNT(*) as count FROM activity_log WHERE DATE(date) = CURDATE()")->fetch_assoc()['count'];

include('admin_layout.php');
?>

<style>
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
    margin-bottom: 1.5rem;
}

.stat-card {
    background: white;
    border-radius: 10px;
    padding: 1.2rem;
    box-shadow: 0 2px 6px rgba(0,0,0,0.08);
    transition: all 0.3s ease;
    border-left: 3px solid;
}

.stat-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.12);
}

.stat-card.blue { border-color: #667eea; }
.stat-card.green { border-color: #48bb78; }
.stat-card.orange { border-color: #ed8936; }

.stat-icon {
    width: 40px;
    height: 40px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
    margin-bottom: 0.8rem;
}

.stat-card.blue .stat-icon { background: #eef2ff; color: #667eea; }
.stat-card.green .stat-icon { background: #f0fff4; color: #48bb78; }
.stat-card.orange .stat-icon { background: #fffaf0; color: #ed8936; }

.stat-value {
    font-size: 1.8rem;
    font-weight: 700;
    color: #2d3748;
    margin-bottom: 0.25rem;
}

.stat-label {
    font-size: 0.85rem;
    color: #718096;
    font-weight: 500;
}

.log-section {
    background: white;
    border-radius: 10px;
    padding: 1.5rem;
    margin-bottom: 1.5rem;
    box-shadow: 0 2px 6px rgba(0,0,0,0.08);
}

.log-section h5 {
    color: #2d3748;
    margin-bottom: 1.5rem;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.filter-bar {
    display: flex;
    gap: 0.75rem;
    margin-bottom: 1.5rem;
    flex-wrap: wrap;
}

.filter-bar select,
.filter-bar input {
    padding: 0.5rem 1rem;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    font-size: 0.9rem;
}

.filter-bar input {
    flex: 1;
    min-width: 250px;
}

.filter-bar button {
    padding: 0.5rem 1.5rem;
    background: #667eea;
    color: white;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.3s ease;
}

.filter-bar button:hover {
    background: #5568d3;
}

table {
    width: 100%;
    border-collapse: collapse;
}

table thead {
    background: #f7fafc;
}

table th {
    padding: 0.75rem;
    text-align: left;
    font-weight: 600;
    color: #4a5568;
    font-size: 0.85rem;
    text-transform: uppercase;
    border-bottom: 2px solid #e2e8f0;
}

table td {
    padding: 0.75rem;
    color: #4a5568;
    font-size: 0.9rem;
    border-bottom: 1px solid #e2e8f0;
}

table tbody tr:hover {
    background: #f7fafc;
}

.action-badge {
    padding: 0.35rem 0.75rem;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 600;
}

.action-badge.add { background: #d4f4dd; color: #22863a; }
.action-badge.edit { background: #fef3c7; color: #92400e; }
.action-badge.delete { background: #fee2e2; color: #991b1b; }
.action-badge.login { background: #dbeafe; color: #1e40af; }

.user-badge {
    padding: 0.3rem 0.65rem;
    border-radius: 15px;
    font-size: 0.75rem;
    font-weight: 600;
}

.user-badge.admin { background: #fce4ec; color: #c2185b; }
.user-badge.teacher { background: #e3f2fd; color: #1976d2; }
.user-badge.student { background: #e8f5e9; color: #2e7d32; }

.pagination {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 0.5rem;
    margin-top: 1.5rem;
}

.pagination a,
.pagination span {
    padding: 0.5rem 0.75rem;
    border: 1px solid #e2e8f0;
    border-radius: 6px;
    text-decoration: none;
    color: #4a5568;
    transition: all 0.3s ease;
}

.pagination a:hover {
    background: #f7fafc;
    border-color: #cbd5e0;
}

.pagination .active {
    background: #667eea;
    color: white;
    border-color: #667eea;
}

.no-data {
    text-align: center;
    padding: 3rem;
    color: #718096;
}
</style>

<div class="page-header">
    <h4><i class="fas fa-history me-2"></i>Activity & System Logs</h4>
</div>

<!-- Statistics Cards -->
<div class="stats-grid">
    <div class="stat-card blue">
        <div class="stat-icon">
            <i class="fas fa-clipboard-list"></i>
        </div>
        <div class="stat-value"><?= $total_activities ?></div>
        <div class="stat-label">Total Activities</div>
    </div>

    <div class="stat-card green">
        <div class="stat-icon">
            <i class="fas fa-sign-in-alt"></i>
        </div>
        <div class="stat-value"><?= $total_logins ?></div>
        <div class="stat-label">Total Logins</div>
    </div>

    <div class="stat-card orange">
        <div class="stat-icon">
            <i class="fas fa-calendar-day"></i>
        </div>
        <div class="stat-value"><?= $today_activities ?></div>
        <div class="stat-label">Today's Activities</div>
    </div>
</div>

<!-- Activity Logs -->
<div class="log-section">
    <h5>
        <i class="fas fa-list-ul"></i>
        Activity Log
    </h5>
    
    <form method="GET" class="filter-bar">
        <select name="filter" class="form-select">
            <option value="all" <?= $filter_type === 'all' ? 'selected' : '' ?>>All Activities</option>
            <option value="Add" <?= $filter_type === 'Add' ? 'selected' : '' ?>>Add Actions</option>
            <option value="Edit" <?= $filter_type === 'Edit' ? 'selected' : '' ?>>Edit Actions</option>
            <option value="Delete" <?= $filter_type === 'Delete' ? 'selected' : '' ?>>Delete Actions</option>
        </select>
        <input type="text" name="search" placeholder="Search username or action..." 
               value="<?= htmlspecialchars($search) ?>">
        <button type="submit">
            <i class="fas fa-search me-1"></i> Filter
        </button>
    </form>

    <?php if ($logs->num_rows > 0): ?>
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Username</th>
                        <th>Action</th>
                        <th>Date & Time</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($log = $logs->fetch_assoc()): 
                        $action_type = 'add';
                        if (stripos($log['action'], 'edit') !== false || stripos($log['action'], 'update') !== false) {
                            $action_type = 'edit';
                        } elseif (stripos($log['action'], 'delete') !== false) {
                            $action_type = 'delete';
                        } elseif (stripos($log['action'], 'login') !== false) {
                            $action_type = 'login';
                        }
                    ?>
                        <tr>
                            <td><strong>#<?= $log['activity_log_id'] ?></strong></td>
                            <td><strong><?= htmlspecialchars($log['username']) ?></strong></td>
                            <td>
                                <span class="action-badge <?= $action_type ?>">
                                    <?= htmlspecialchars($log['action']) ?>
                                </span>
                            </td>
                            <td><?= date('M d, Y h:i A', strtotime($log['date'])) ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
            <div class="pagination">
                <?php if ($page > 1): ?>
                    <a href="?page=<?= $page - 1 ?>&filter=<?= $filter_type ?>&search=<?= urlencode($search) ?>">
                        <i class="fas fa-chevron-left"></i>
                    </a>
                <?php endif; ?>

                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <?php if ($i == $page): ?>
                        <span class="active"><?= $i ?></span>
                    <?php else: ?>
                        <a href="?page=<?= $i ?>&filter=<?= $filter_type ?>&search=<?= urlencode($search) ?>">
                            <?= $i ?>
                        </a>
                    <?php endif; ?>
                <?php endfor; ?>

                <?php if ($page < $total_pages): ?>
                    <a href="?page=<?= $page + 1 ?>&filter=<?= $filter_type ?>&search=<?= urlencode($search) ?>">
                        <i class="fas fa-chevron-right"></i>
                    </a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    <?php else: ?>
        <div class="no-data">
            <i class="fas fa-inbox fa-3x mb-3" style="color: #cbd5e0;"></i>
            <p>No activity logs found.</p>
        </div>
    <?php endif; ?>
</div>

<!-- Recent Login Logs -->
<div class="log-section">
    <h5>
        <i class="fas fa-sign-in-alt"></i>
        Recent User Logins
    </h5>
    
    <?php if ($user_logs->num_rows > 0): ?>
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Type</th>
                        <th>Login Time</th>
                        <th>Logout Time</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($ulog = $user_logs->fetch_assoc()): ?>
                        <tr>
                            <td>
                                <strong><?= htmlspecialchars($ulog['username']) ?></strong>
                                <?php if ($ulog['firstname']): ?>
                                    <br>
                                    <small style="color: #718096;">
                                        <?= htmlspecialchars($ulog['firstname'] . ' ' . $ulog['lastname']) ?>
                                    </small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="user-badge <?= htmlspecialchars($ulog['user_type']) ?>">
                                    <?= ucfirst(htmlspecialchars($ulog['user_type'])) ?>
                                </span>
                            </td>
                            <td><?= date('M d, Y h:i A', strtotime($ulog['login_date'])) ?></td>
                            <td>
                                <?php if ($ulog['logout_date']): ?>
                                    <?= date('M d, Y h:i A', strtotime($ulog['logout_date'])) ?>
                                <?php else: ?>
                                    <span style="color: #48bb78;">Active</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="no-data">
            <i class="fas fa-inbox fa-3x mb-3" style="color: #cbd5e0;"></i>
            <p>No login logs found.</p>
        </div>
    <?php endif; ?>
</div>

</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>