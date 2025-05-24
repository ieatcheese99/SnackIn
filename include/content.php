<!--

<section class="container py-5">
    <div class="row text-center pt-3">
        <div class="col-lg-6 m-auto">
            <h1 class="h1"><b>Best-selling product of the month.</b></h1>
            <p>
            </p>
        </div>
    </div>
    <div class="row">
        <div class="col-12 col-md-4 p-5 mt-3">
            <a href="#"><img src="./assets/img/pngtree-creamy-oreo-milkshake-png-image_13066217-Photoroom.png" class="rounded-circle img-fluid border"></a>
            <h5 class="text-center mt-3 mb-3">Oreo Milkshake</h5>
            <p class="text-center"><a class="btn btn-success">Go Shop</a></p>
        </div>
        <div class="col-12 col-md-4 p-5 mt-3">
            <a href="#"><img src="./assets/img/pngtree-a-glass-of-iced-tea-isolated-on-transparent-background-ai-generated-png-image_11904777-Photoroom.png" class="rounded-circle img-fluid border"></a>
            <h2 class="h5 text-center mt-3 mb-3">Es segar</h2>
            <p class="text-center"><a class="btn btn-success">Go Shop</a></p>
        </div>
        <div class="col-12 col-md-4 p-5 mt-3">
            <a href="#"><img src="./assets/img/1c170f803582a8a3718df5543b206aad-Photoroom.png" class="rounded-circle img-fluid border"></a>
            <h2 class="h5 text-center mt-3 mb-3">Basreng</h2>
            <p class="text-center"><a class="btn btn-success">Go Shop</a></p>
        </div>
    </div>
</section>


<section class="container py-5">
    <h2 class="text-center">Daftar Produk</h2>
    <div class="row">
        <?php while ($row = mysqli_fetch_assoc($queryProduk)) { ?>
            <div class="col-12 col-md-4 mb-4">
                <div class="produk-card">
                    <img src="<?php echo $row['gambar']; ?>" alt="...">
                    <h3><?php echo $row['nama']; ?></h3>
                    <p>Rp <?php echo number_format($row['harga'], 0, ',', '.'); ?></p>
                    <p><?php echo substr($row['deskripsi'], 0, 50); ?>...</p>
                    <button class="btn btn-success add-to-cart"
                        data-id="<?php echo $row['id']; ?>"
                        data-nama="<?php echo $row['nama']; ?>"
                        data-harga="<?php echo $row['harga']; ?>"
                        data-gambar="<?php echo $row['gambar']; ?>">
                        Tambah ke Keranjang
                    </button>
                </div>
            </div>
        <?php } ?>
    </div>
</section>


<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function() {
        $(".add-to-cart").click(function() {
            var id = $(this).data("id");
            var nama = $(this).data("nama");
            var harga = $(this).data("harga");
            var gambar = $(this).data("gambar");

            $.ajax({
                url: "include/cart_action.php",
                type: "POST",
                data: {
                    action: "add",
                    id: id,
                    nama: nama,
                    harga: harga,
                    gambar: gambar
                },
                success: function(response) {
                    updateCartCount();
                }
            });
        });

        function updateCartCount() {
            $.ajax({
                url: "include/cart_action.php",
                type: "POST",
                data: {
                    action: "count"
                },
                success: function(count) {
                    $(".cart-count").text(count);
                }
            });
        }

        updateCartCount();
    });
</script>


<style>
    .container {
        max-width: 1000px;
    }

    .produk-card {
        width: 100%;
        border: 1px solid #ddd;
        padding: 15px;
        border-radius: 8px;
        text-align: center;
        background: #fff;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        transition: transform 0.2s ease-in-out;
    }

    .produk-card:hover {
        transform: scale(1.05);
    }

    .produk-card img {
        width: 100%;
        height: 200px;
        object-fit: cover;
        border-radius: 8px;
    }

    .produk-img {
        width: 180px;
        height: 180px;
        object-fit: cover;
    }
</style>
!->