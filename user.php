<?php
// Define admin access constant
define('ADMIN_ACCESS', true);

// Cek akses admin
require_once 'config/database.php';

session_start();

// Fungsi untuk memeriksa akses admin
function requireAdmin() {
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
    .stats-cards {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }

    .stat-card {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        border-radius: 12px;
        padding: 20px;
        text-align: center;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        border-left: 5px solid #00227c;
        transition: var(--transition);
    }

    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.15);
    }

    .stat-number {
        font-size: 32px;
        font-weight: 800;
        color: #00227c;
        display: block;
    }

    .stat-label {
        color: #666;
        font-size: 14px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-top: 5px;
    }

    .users-table {
        background: white;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    }

    .table {
        margin: 0;
    }

    .table thead th {
        background: linear-gradient(135deg, #00227c 0%, #1e40af 100%);
        color: white;
        border: none;
        padding: 18px 15px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-size: 14px;
    }

    .table tbody td {
        padding: 15px;
        vertical-align: middle;
        border-bottom: 1px solid #f0f0f0;
    }

    .table tbody tr {
        transition: all 0.3s ease;
    }

    .table tbody tr:hover {
        background-color: #f8f9fa;
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    }

    .user-avatar {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        background: linear-gradient(135deg, #00227c 0%, #1e40af 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: 700;
        font-size: 18px;
    }

    .user-info h6 {
        margin: 0;
        font-weight: 700;
        color: #333;
    }

    .user-info small {
        color: #666;
    }

    .level-badge {
        padding: 6px 15px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .level-admin {
        background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
        color: white;
    }

    .level-user {
        background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
        color: white;
    }

    .action-btn {
        padding: 8px 12px;
        border-radius: 6px;
        text-decoration: none;
        font-size: 12px;
        font-weight: 600;
        transition: all 0.3s ease;
        margin-right: 5px;
        display: inline-block;
    }

    .btn-edit {
        background: linear-gradient(135deg, #ffc107 0%, #e0a800 100%);
        color: #333;
    }

    .btn-edit:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(255,193,7,0.3);
        color: #333;
    }

    .btn-delete {
        background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
        color: white;
    }

    .btn-delete:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(220,53,69,0.3);
        color: white;
    }

    .btn-add {
        background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
        color: white;
        padding: 12px 25px;
        border-radius: 8px;
        text-decoration: none;
        font-weight: 600;
        transition: all 0.3s ease;
        display: inline-block;
    }

    .btn-add:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(40,167,69,0.3);
        color: white;
    }

    @media (max-width: 768px) {
        .action-btn {
            display: block;
            margin-bottom: 5px;
            margin-right: 0;
            text-align: center;
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
                                        <div class="user-avatar">
                                            <?php echo strtoupper(substr($user['username'], 0, 1)); ?>
                                        </div>
                                        <div class="ms-3 user-info">
                                            <h6><?php echo htmlspecialchars($user['username']); ?></h6>
                                            <small>User ID: <?php echo $user['id']; ?></small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="level-badge <?php echo $user['level'] == 'admin' ? 'level-admin' : 'level-user'; ?>">
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
                                    <a href="user_delete.php?id=<?php echo $user['id']; ?>" class="action-btn btn-delete" onclick="return confirm('Yakin ingin menghapus user ini?')">
                                        <i class="fas fa-trash"></i> Delete
                                    </a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center" style="padding: 60px;">
                                    <i class="fas fa-users" style="font-size: 64px; color: #ccc; margin-bottom: 20px; display: block;"></i>
                                    <h5 style="color: #666; margin: 0;">Belum ada user</h5>
                                    <p style="color: #999; margin: 5px 0 0 0;">User akan muncul di sini setelah didaftarkan</p>
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
        button.addEventListener('click', function(e) {
            if (!confirm('Apakah Anda yakin ingin menghapus user ini? Tindakan ini tidak dapat dibatalkan.')) {
                e.preventDefault();
            } else {
                showLoading();
            }
        });
    });

    // Add loading animation for actions
    document.querySelectorAll('.action-btn').forEach(button => {
        button.addEventListener('click', function() {
            if (!this.classList.contains('btn-delete')) {
                showLoading();
            }
        });
    });
</script>

<?php include 'include/admin_footer.php'; ?>
