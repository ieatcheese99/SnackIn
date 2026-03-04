<?php
// Define admin access constant
define('ADMIN_ACCESS', true);

// Cek akses admin
require_once 'config/database.php';

session_start();

// Fungsi untuk memeriksa akses admin
function requireAdmin()
{
    if (!isset($_SESSION['username']) || $_SESSION['level'] !== 'admin') {
        header("Location: login.php");
        exit();
    }
}

requireAdmin();

// Get all users
$query = "SELECT * FROM user ORDER BY id DESC";
$users = mysqli_query($db, $query);

$page_title = "User Management";
include 'include/admin_header.php';
?>

<style>
    /* Global Variables */
    :root {
        --primary-color: #00227c;
        --secondary-color: #001a5e;
        --accent-color: #f48c06;
        --white: #ffffff;
        --orange: #f69e22;
        --light-bg: #f8fafc;
        --text-dark: #1e293b;
        --text-muted: #64748b;
        --radius-md: 12px;
        --radius-lg: 20px;
        --shadow-sm: 0 4px 6px rgba(0, 0, 0, 0.05);
        --shadow-md: 0 10px 25px rgba(0, 0, 0, 0.08);
        --transition: 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        --success-color: #10b981;
        --warning-color: #f59e0b;
        --danger-color: #ef4444;
    }

    body {
        font-family: 'Inter', sans-serif;
        background-color: var(--light-bg);
        color: var(--text-dark);
    }

    .stats-cards {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 25px;
        margin-bottom: 30px;
    }

    .stat-card {
        background: var(--white);
        border-radius: var(--radius-lg);
        padding: 25px;
        text-align: center;
        box-shadow: var(--shadow-sm);
        border: 1px solid rgba(0, 0, 0, 0.05);
        transition: var(--transition);
        position: relative;
        overflow: hidden;
    }

    .stat-card::before {
        content: '';
        position: absolute;
        top: 0;
        right: 0;
        width: 100px;
        height: 100px;
        background: var(--primary-color);
        opacity: 0.05;
        border-radius: 50%;
        transform: translate(30px, -30px);
        transition: var(--transition);
    }

    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: var(--shadow-md);
    }

    .stat-card:hover::before {
        transform: translate(20px, -20px) scale(1.1);
        opacity: 0.08;
    }

    .stat-number {
        font-family: 'Outfit', sans-serif;
        font-size: 36px;
        font-weight: 800;
        color: var(--text-dark);
        display: block;
        margin-bottom: 5px;
        line-height: 1.1;
        letter-spacing: -1px;
    }

    .stat-label {
        color: var(--text-muted);
        font-size: 14px;
        text-transform: uppercase;
        font-weight: 600;
        letter-spacing: 0.5px;
    }

    .users-table {
        background: var(--white);
        border-radius: var(--radius-lg);
        overflow: hidden;
        box-shadow: var(--shadow-sm);
        border: 1px solid rgba(0, 0, 0, 0.05);
        padding: 30px;
    }

    .table {
        width: 100%;
        margin: 0;
        border-collapse: collapse;
    }

    .table thead th {
        background: var(--light-bg);
        color: var(--text-muted);
        border: none;
        border-bottom: 2px solid rgba(0, 0, 0, 0.05);
        padding: 16px 15px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-size: 13px;
        text-align: left;
    }

    .table tbody td {
        padding: 18px 15px;
        vertical-align: middle;
        border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        color: var(--text-dark);
        font-weight: 500;
        font-size: 14px;
    }

    .table tbody tr {
        transition: var(--transition);
    }

    .table tbody tr:hover {
        background-color: var(--light-bg);
    }

    .user-avatar {
        width: 45px;
        height: 45px;
        border-radius: 50%;
        background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--white);
        font-family: 'Outfit', sans-serif;
        font-weight: 700;
        font-size: 18px;
        box-shadow: 0 4px 10px rgba(0, 34, 124, 0.2);
    }

    .user-info h6 {
        margin: 0;
        font-weight: 700;
        font-size: 15px;
        color: var(--text-dark);
    }

    .user-info small {
        color: var(--text-muted);
        font-size: 12px;
    }

    .level-badge {
        padding: 6px 14px;
        border-radius: 50px;
        font-size: 12px;
        font-weight: 600;
        display: inline-block;
    }

    .level-admin {
        background: #fee2e2;
        color: #dc2626;
    }

    .level-user {
        background: #e0e7ff;
        color: #4f46e5;
    }

    .action-btn {
        padding: 8px 16px;
        border-radius: var(--radius-md);
        text-decoration: none;
        font-size: 13px;
        font-weight: 600;
        transition: var(--transition);
        margin-right: 8px;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    .btn-edit {
        background: #fef3c7;
        color: #d97706;
    }

    .btn-edit:hover {
        background: #fde68a;
        transform: translateY(-2px);
    }

    .btn-delete {
        background: #fee2e2;
        color: #dc2626;
    }

    .btn-delete:hover {
        background: #fecaca;
        transform: translateY(-2px);
    }

    .btn-add {
        background: linear-gradient(135deg, var(--accent-color), var(--orange));
        color: var(--white);
        padding: 12px 24px;
        border-radius: var(--radius-md);
        text-decoration: none;
        font-weight: 700;
        font-size: 14px;
        transition: var(--transition);
        display: inline-flex;
        align-items: center;
        gap: 8px;
        box-shadow: 0 4px 15px rgba(246, 158, 34, 0.25);
    }

    .btn-add:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(246, 158, 34, 0.35);
        color: var(--white);
    }

    @media (max-width: 768px) {
        .users-table {
            padding: 15px;
        }

        .action-btn {
            display: flex;
            justify-content: center;
            margin-bottom: 5px;
            margin-right: 0;
            width: 100%;
        }

        .table tbody td {
            padding: 12px 10px;
        }
    }
</style>

<!-- Page Header -->
<div class="page-header">
    <div class="container">
        <h1 class="page-title"><i class="fas fa-users"></i> User Management</h1>
        <p class="page-subtitle">Kelola pengguna dan hak akses sistem</p>
    </div>
</div>

<div class="container-fluid">
    <!-- Navigation Buttons -->
    <div class="nav-buttons">
        <a href="index.php" class="nav-btn">
            <i class="fas fa-home"></i> Dashboard
        </a>
        <a href="data_barang.php" class="nav-btn">
            <i class="fas fa-box"></i> Data Barang
        </a>
        <a href="kategori.php" class="nav-btn">
            <i class="fas fa-tags"></i> Kategori
        </a>
        <a href="pesanan.php" class="nav-btn">
            <i class="fas fa-shopping-cart"></i> Pesanan
        </a>
        <a href="user.php" class="nav-btn active">
            <i class="fas fa-users"></i> User
        </a>
        <a href="history_admin.php" class="nav-btn">
            <i class="fas fa-history"></i> History
        </a>
    </div>

    <div class="content-container content-animate">
        <!-- Statistics Cards -->
        <div class="stats-cards">
            <?php
            $total_users = mysqli_num_rows($users);
            $admin_count = mysqli_num_rows(mysqli_query($db, "SELECT * FROM user WHERE level = 'admin'"));
            $user_count = mysqli_num_rows(mysqli_query($db, "SELECT * FROM user WHERE level = 'user'"));
            ?>
            <div class="stat-card animate-item">
                <span class="stat-number"><?php echo $total_users; ?></span>
                <div class="stat-label">Total Users</div>
            </div>
            <div class="stat-card animate-item">
                <span class="stat-number"><?php echo $admin_count; ?></span>
                <div class="stat-label">Administrators</div>
            </div>
            <div class="stat-card animate-item">
                <span class="stat-number"><?php echo $user_count; ?></span>
                <div class="stat-label">Regular Users</div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="mb-4">
            <a href="user-tambah.php" class="btn-add">
                <i class="fas fa-plus-circle"></i> Tambah User Baru
            </a>
        </div>

        <!-- Users Table -->
        <div class="users-table animate-item">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th><i class="fas fa-hashtag"></i> ID</th>
                            <th><i class="fas fa-user"></i> User Info</th>
                            <th><i class="fas fa-shield-alt"></i> Level</th>
                            <th><i class="fas fa-calendar"></i> Joined</th>
                            <th><i class="fas fa-cogs"></i> Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        mysqli_data_seek($users, 0);
                        if (mysqli_num_rows($users) > 0):
                            ?>
                            <?php while ($user = mysqli_fetch_assoc($users)): ?>
                                <tr>
                                    <td><strong>#<?php echo $user['id']; ?></strong></td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="user-avatar" style="min-width: 45px; margin-right: 15px;">
                                                <?php echo strtoupper(substr($user['username'], 0, 1)); ?>
                                            </div>
                                            <div class="user-info">
                                                <h6><?php echo htmlspecialchars($user['username']); ?></h6>
                                                <small>User ID: <?php echo $user['id']; ?></small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span
                                            class="level-badge <?php echo $user['level'] == 'admin' ? 'level-admin' : 'level-user'; ?>">
                                            <i class="fas fa-<?php echo $user['level'] == 'admin' ? 'crown' : 'user'; ?>"></i>
                                            <?php echo ucfirst($user['level']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <i class="fas fa-calendar-alt text-muted"></i>
                                        <span class="text-muted">Recently</span>
                                    </td>
                                    <td>
                                        <a href="user_ubah.php?id=<?php echo $user['id']; ?>" class="action-btn btn-edit">
                                            <i class="fas fa-edit"></i> Edit
                                        </a>
                                        <?php if ($user['level'] !== 'admin'): ?>
                                            <a href="user_delete.php?id=<?php echo $user['id']; ?>" class="action-btn btn-delete"
                                                onclick="return confirm('Yakin ingin menghapus user ini?')">
                                                <i class="fas fa-trash"></i> Delete
                                            </a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center" style="padding: 60px;">
                                    <i class="fas fa-users"
                                        style="font-size: 64px; color: #ccc; margin-bottom: 20px; display: block;"></i>
                                    <h5 style="color: #666; margin: 0;">Belum ada user</h5>
                                    <p style="color: #999; margin: 5px 0 0 0;">User akan muncul di sini setelah didaftarkan
                                    </p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Confirm delete action
    document.querySelectorAll('.btn-delete').forEach(button => {
        button.addEventListener('click', function (e) {
            if (!confirm('Apakah Anda yakin ingin menghapus user ini? Tindakan ini tidak dapat dibatalkan.')) {
                e.preventDefault();
            } else {
                showLoading();
            }
        });
    });

    // Add loading animation for actions
    document.querySelectorAll('.action-btn').forEach(button => {
        button.addEventListener('click', function () {
            if (!this.classList.contains('btn-delete')) {
                showLoading();
            }
        });
    });
</script>

<?php include 'include/admin_footer.php'; ?>