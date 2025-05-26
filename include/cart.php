<?php
session_start();
$cart = isset($_SESSION['cart']) ? $_SESSION['cart'] : [];
$subtotal = array_sum(array_map(function ($item) {
    return $item['harga'] * $item['jumlah'];
}, $cart));

// Hitung biaya admin 5%
$biaya_admin = $subtotal * 0.05;
$totalHarga = $subtotal + $biaya_admin;
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Keranjang Belanja - Snack In</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        /* [Previous CSS styles remain the same] */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f9f9f9;
        }

        .main-header {
            background-color: #00227c;
            color: white;
            padding: 15px 0;
            position: sticky;
            top: 0;
            z-index: 1000;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .main-header .container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 24px;
            font-weight: 700;
        }

        .logo img {
            height: 40px;
            width: auto;
        }

        .cart-section {
            padding: 40px 0;
            min-height: calc(100vh - 300px);
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        .cart-title {
            color: #00227c;
            text-align: center;
            margin-bottom: 30px;
            font-weight: 700;
            font-size: 32px;
        }

        .cart-table {
            width: 100%;
            border-collapse: collapse;
            background-color: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .cart-table th {
            background-color: #00227c;
            color: white;
            padding: 15px;
            text-align: center;
            font-weight: 600;
        }

        .cart-table td {
            padding: 15px;
            text-align: center;
            border-bottom: 1px solid #eee;
            vertical-align: middle;
        }

        .cart-img {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 8px;
        }

        .cart-summary {
            margin-top: 30px;
            background-color: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .btn {
            display: inline-block;
            padding: 10px 20px;
            background-color: #00227c;
            color: white;
            border-radius: 5px;
            font-weight: 500;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
            text-decoration: none;
        }

        .btn-orange {
            background-color: #f69e22;
        }

        .btn:hover {
            opacity: 0.9;
            transform: translateY(-2px);
        }

        .quantity-btn {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background-color: #f5f5f5;
            border: 1px solid #ddd;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .quantity-btn:hover {
            background-color: #00227c;
            color: white;
        }

        .cart-remove {
            background-color: #ff6b6b;
            color: white;
            border: none;
            padding: 8px 12px;
            border-radius: 5px;
            cursor: pointer;
        }

        .modal-content {
            border-radius: 10px;
        }

        .modal-header {
            background-color: #00227c;
            color: white;
        }

        .form-control, .form-select {
            padding: 12px;
            border-radius: 8px;
            border: 1px solid #ddd;
            margin-bottom: 20px;
        }

        .payment-info {
            background-color: #f9f9f9;
            border-radius: 8px;
            padding: 15px;
            margin-top: 20px;
        }

        @media (max-width: 768px) {
            .cart-table {
                font-size: 14px;
            }
            
            .cart-img {
                width: 60px;
                height: 60px;
            }
        }
    </style>
</head>

<body>
    <!-- Main Header -->
    <header class="main-header">
        <div class="container">
            <a href="../user_ui.php" class="logo">
                <img src="../assets/img/Logo Bisnis Bengkel Otomotif (3).png" alt="Snack In Logo">
                <span>SNACK IN</span>
            </a>
            <div class="header-actions">
                <a href="../user_ui.php" class="btn">Kembali ke Beranda</a>
            </div>
        </div>
    </header>

    <!-- Cart Section -->
    <section class="cart-section">
        <div class="container">
            <h1 class="cart-title">Keranjang Belanja</h1>

            <?php if (empty($cart)) { ?>
                <div class="text-center">
                    <h3>Keranjang Anda Kosong</h3>
                    <p>Silakan tambahkan produk ke keranjang terlebih dahulu.</p>
                    <a href="../user_ui.php" class="btn btn-orange">Belanja Sekarang</a>
                </div>
            <?php } else { ?>
                <div class="row">
                    <div class="col-lg-8">
                        <div class="table-responsive">
                            <table class="cart-table">
                                <thead>
                                    <tr>
                                        <th>Produk</th>
                                        <th>Nama</th>
                                        <th>Harga</th>
                                        <th>Jumlah</th>
                                        <th>Total</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($cart as $item) { ?>
                                        <tr>
                                            <td>
                                                <img src="../<?php echo $item['gambar']; ?>" alt="<?php echo $item['nama']; ?>" class="cart-img">
                                            </td>
                                            <td><?php echo $item['nama']; ?></td>
                                            <td>Rp <?php echo number_format($item['harga'], 0, ',', '.'); ?></td>
                                            <td>
                                                <button class="quantity-btn decrease" data-id="<?php echo $item['id']; ?>">-</button>
                                                <span style="margin: 0 10px;"><?php echo $item['jumlah']; ?></span>
                                                <button class="quantity-btn increase" data-id="<?php echo $item['id']; ?>">+</button>
                                            </td>
                                            <td>Rp <?php echo number_format($item['harga'] * $item['jumlah'], 0, ',', '.'); ?></td>
                                            <td>
                                                <button class="cart-remove remove-item" data-id="<?php echo $item['id']; ?>">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="cart-summary">
                            <h3>Ringkasan Pesanan</h3>
                            <div class="d-flex justify-content-between">
                                <span>Subtotal</span>
                                <span>Rp <?php echo number_format($subtotal, 0, ',', '.'); ?></span>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span>Biaya Admin (5%)</span>
                                <span>Rp <?php echo number_format($biaya_admin, 0, ',', '.'); ?></span>
                            </div>
                            <hr>
                            <div class="d-flex justify-content-between">
                                <strong>Total</strong>
                                <strong>Rp <?php echo number_format($totalHarga, 0, ',', '.'); ?></strong>
                            </div>

                            <button class="btn btn-orange w-100 mt-4" data-bs-toggle="modal" data-bs-target="#checkoutModal">
                                Checkout Sekarang
                            </button>
                        </div>
                    </div>
                </div>
            <?php } ?>
        </div>
    </section>

    <!-- Modal Checkout -->
    <div class="modal fade" id="checkoutModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Form Checkout</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="checkout-form">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="nama" class="form-label">Nama Lengkap</label>
                            <input type="text" class="form-control" name="nama" id="nama" required 
                                   value="<?php echo isset($_SESSION['username']) ? $_SESSION['username'] : ''; ?>">
                        </div>
                        <div class="mb-3">
                            <label for="alamat" class="form-label">Alamat Pengiriman</label>
                            <textarea class="form-control" name="alamat" id="alamat" rows="3" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="metode_pembayaran" class="form-label">Metode Pembayaran</label>
                            <select name="metode_pembayaran" id="metode_pembayaran" class="form-select" required>
                                <option value="">-- Pilih Metode Pembayaran --</option>
                                <option value="Transfer Bank">Transfer Bank</option>
                                <option value="Qris">QRIS</option>
                                <option value="Cash">Cash on Delivery (COD)</option>
                            </select>
                        </div>

                        <div id="info-pembayaran" class="payment-info" style="display: none;"></div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-orange">Bayar Sekarang</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        $(document).ready(function() {
            // Remove item from cart
            $(".remove-item").click(function() {
                var id = $(this).data("id");
                $.post("../include/cart_action.php", {
                    action: "remove",
                    id: id
                }, function(response) {
                    location.reload();
                });
            });

            // Increase item quantity
            $(".increase").click(function() {
                var id = $(this).data("id");
                $.post("../include/cart_action.php", {
                    action: "increase",
                    id: id
                }, function(response) {
                    location.reload();
                });
            });

            // Decrease item quantity
            $(".decrease").click(function() {
                var id = $(this).data("id");
                $.post("../include/cart_action.php", {
                    action: "decrease",
                    id: id
                }, function(response) {
                    location.reload();
                });
            });

            // Checkout form submission
            $("#checkout-form").submit(function(event) {
                event.preventDefault();
                
                $.post("../include/cart_action.php", $(this).serialize() + "&action=checkout", function(response) {
                    try {
                        var result = JSON.parse(response);
                        if(result.success) {
                            alert("Pesanan berhasil dibuat! ID Pesanan: " + result.order_id);
                            window.location.href = "../history.php";
                        } else {
                            alert("Terjadi kesalahan: " + result.error);
                        }
                    } catch(e) {
                        console.error("Error:", response);
                        alert("Terjadi kesalahan saat memproses pesanan.");
                    }
                });
            });

            // Payment method change
            $("#metode_pembayaran").on("change", function() {
                let metode = $(this).val();
                let infoContainer = $("#info-pembayaran");

                if (metode === "Qris") {
                    infoContainer.html(`
                        <h4>Pembayaran via QRIS</h4>
                        <p>Scan QR code menggunakan aplikasi e-wallet Anda.</p>
                    `).show();
                } else if (metode === "Transfer Bank") {
                    infoContainer.html(`
                        <h4>Transfer Bank</h4>
                        <p><strong>Bank BCA: 1234567890</strong></p>
                        <p>a.n. SNACK IN</p>
                    `).show();
                } else if (metode === "Cash") {
                    infoContainer.html(`
                        <h4>Cash on Delivery (COD)</h4>
                        <p>Bayar saat pesanan tiba.</p>
                    `).show();
                } else {
                    infoContainer.hide();
                }
            });
        });
    </script>
</body>

</html>
