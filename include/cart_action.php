<?php
// Pastikan session hanya dimulai sekali
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Koneksi ke database
if (file_exists("../config/database.php")) {
    require_once "../config/database.php";
} else {
    require_once "config/database.php";
}

// Inisialisasi session cart jika belum ada
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $action = isset($_POST['action']) ? $_POST['action'] : '';

    // Tambah ke keranjang
    if ($action == "add") {
        $id = $_POST['id'];
        $nama = $_POST['nama'];
        $harga = $_POST['harga'];
        $gambar = $_POST['gambar'];

        // Cek apakah produk sudah ada di cart
        $found = false;
        foreach ($_SESSION['cart'] as &$item) {
            if ($item['id'] == $id) {
                $item['jumlah'] += 1;
                $found = true;
                break;
            }
        }

        // Jika belum ada, tambahkan produk ke cart
        if (!$found) {
            $_SESSION['cart'][] = [
                'id' => $id,
                'nama' => $nama,
                'harga' => $harga,
                'gambar' => $gambar,
                'jumlah' => 1
            ];
        }

        echo json_encode(['success' => true]);
        exit;
    }

    // Hitung jumlah item dalam cart
    if ($action == "count") {
        $count = 0;
        foreach ($_SESSION['cart'] as $item) {
            $count += $item['jumlah'];
        }
        echo $count;
        exit;
    }

    // Tambah jumlah produk
    if ($action == "increase") {
        foreach ($_SESSION['cart'] as &$item) {
            if ($item['id'] == $_POST['id']) {
                $item['jumlah'] += 1;
                break;
            }
        }
        echo json_encode(['success' => true]);
        exit;
    }

    // Kurangi jumlah produk
    if ($action == "decrease") {
        foreach ($_SESSION['cart'] as &$item) {
            if ($item['id'] == $_POST['id'] && $item['jumlah'] > 1) {
                $item['jumlah'] -= 1;
                break;
            }
        }
        echo json_encode(['success' => true]);
        exit;
    }

    // Hapus produk dari cart
    if ($action == "remove") {
        $_SESSION['cart'] = array_values(array_filter($_SESSION['cart'], function ($item) {
            return $item['id'] != $_POST['id'];
        }));
        echo json_encode(['success' => true]);
        exit;
    }

    // Kosongkan keranjang
    if ($action == "clear") {
        $_SESSION['cart'] = [];
        echo json_encode(['success' => true]);
        exit;
    }

    // Proses checkout
    if ($action == "checkout") {
        try {
            $nama = $_POST['nama'];
            $alamat = $_POST['alamat'];
            $metode_pembayaran = $_POST['metode_pembayaran'];
            $total_harga = 0;
            $username = isset($_SESSION['username']) ? $_SESSION['username'] : $nama;

            // Pastikan ada produk dalam keranjang sebelum checkout
            if (empty($_SESSION['cart'])) {
                echo json_encode(['success' => false, 'error' => 'Keranjang kosong']);
                exit;
            }

            // Hitung total harga
            foreach ($_SESSION['cart'] as $item) {
                $total_harga += $item['harga'] * $item['jumlah'];
            }

            // Cek apakah tabel orders memiliki kolom status
            $check_status_column = "SHOW COLUMNS FROM orders LIKE 'status'";
            $status_column_exists = $db->query($check_status_column);

            // Jika kolom status tidak ada, tambahkan kolom status
            if ($status_column_exists && $status_column_exists->num_rows == 0) {
                $add_status_column = "ALTER TABLE orders ADD COLUMN status VARCHAR(20) DEFAULT 'pending' AFTER total_harga";
                $db->query($add_status_column);
            }

            // Cek apakah tabel orders memiliki kolom username
            $check_username_column = "SHOW COLUMNS FROM orders LIKE 'username'";
            $username_column_exists = $db->query($check_username_column);

            // Jika kolom username tidak ada, tambahkan kolom username
            if ($username_column_exists && $username_column_exists->num_rows == 0) {
                $add_username_column = "ALTER TABLE orders ADD COLUMN username VARCHAR(100) AFTER total_harga";
                $db->query($add_username_column);
            }

            // Simpan ke tabel orders
            $query = "INSERT INTO orders (nama, alamat, metode_pembayaran, total_harga";
            
            // Cek apakah kolom status ada
            if ($status_column_exists && $status_column_exists->num_rows > 0) {
                $query .= ", status";
            }
            
            // Cek apakah kolom username ada
            if ($username_column_exists && $username_column_exists->num_rows > 0) {
                $query .= ", username";
            }
            
            $query .= ", created_at) VALUES (?, ?, ?, ?";
            
            // Tambahkan parameter untuk status jika ada
            if ($status_column_exists && $status_column_exists->num_rows > 0) {
                $query .= ", ?";
            }
            
            // Tambahkan parameter untuk username jika ada
            if ($username_column_exists && $username_column_exists->num_rows > 0) {
                $query .= ", ?";
            }
            
            $query .= ", NOW())";
            
            $stmt = $db->prepare($query);
            
            // Buat array parameter
            $params = [$nama, $alamat, $metode_pembayaran, $total_harga];
            $types = "sssd"; // string, string, string, double
            
            // Tambahkan status jika ada
            if ($status_column_exists && $status_column_exists->num_rows > 0) {
                $params[] = "processing";
                $types .= "s";
            }
            
            // Tambahkan username jika ada
            if ($username_column_exists && $username_column_exists->num_rows > 0) {
                $params[] = $username;
                $types .= "s";
            }
            
            // Bind parameters
            $stmt->bind_param($types, ...$params);

            if ($stmt->execute()) {
                $order_id = $db->insert_id; // Ambil ID pesanan

                // Cek struktur tabel order_items
                $check_gambar_column = "SHOW COLUMNS FROM order_items LIKE 'gambar_produk'";
                $gambar_column_exists = $db->query($check_gambar_column);

                // Simpan detail pesanan ke tabel order_items
                foreach ($_SESSION['cart'] as $item) {
                    $produk_id = $item['id'];
                    $nama_produk = $item['nama'];
                    $harga = $item['harga'];
                    $jumlah = $item['jumlah'];
                    $subtotal = $harga * $jumlah;
                    
                    // Buat query berdasarkan struktur tabel
                    if ($gambar_column_exists && $gambar_column_exists->num_rows > 0) {
                        // Jika kolom gambar_produk ada
                        $query_detail = "INSERT INTO order_items (order_id, produk_id, nama_produk, harga, jumlah, subtotal, gambar_produk) 
                                        VALUES (?, ?, ?, ?, ?, ?, ?)";
                        $stmt_detail = $db->prepare($query_detail);
                        $gambar_produk = $item['gambar'];
                        $stmt_detail->bind_param("iisdids", $order_id, $produk_id, $nama_produk, $harga, $jumlah, $subtotal, $gambar_produk);
                    } else {
                        // Jika kolom gambar_produk tidak ada
                        $query_detail = "INSERT INTO order_items (order_id, produk_id, nama_produk, harga, jumlah, subtotal) 
                                        VALUES (?, ?, ?, ?, ?, ?)";
                        $stmt_detail = $db->prepare($query_detail);
                        $stmt_detail->bind_param("iisdid", $order_id, $produk_id, $nama_produk, $harga, $jumlah, $subtotal);
                    }

                    if (!$stmt_detail->execute()) {
                        throw new Exception("Gagal menyimpan detail pesanan: " . $db->error);
                    }
                }

                // Kosongkan keranjang setelah checkout
                $_SESSION['cart'] = [];
                echo json_encode(['success' => true, 'order_id' => $order_id]);
            } else {
                throw new Exception("Gagal menyimpan pesanan: " . $db->error);
            }
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        exit;
    }

    // Return cart data for other actions
    echo json_encode($_SESSION['cart']);
}
?>