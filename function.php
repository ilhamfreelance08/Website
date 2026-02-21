<?php
session_start();

// koneksi database
function koneksi() {
    $conn = mysqli_connect("localhost", "root", "", "kasir");

    if(!$conn){
        die("Koneksi gagal: " . mysqli_connect_error());
    }

    return $conn;
}

$conn = koneksi();


// ================= LOGIN =================
if(isset($_POST['login'])){
    $username = $_POST['username'];
    $password = $_POST['password'];

    $check = mysqli_query($conn, "SELECT * FROM user WHERE username='$username' AND password='$password'");

    if(!$check){
        die("Query error: " . mysqli_error($conn));
    }

    $hitung = mysqli_num_rows($check);

    if($hitung > 0){
        $_SESSION['login'] = true;
        header('location:index.php');
        exit;
    } else {
        echo "<script>
            alert('Username atau password salah');
            window.location.href='login.php';
        </script>";
    }
}


// ================= TAMBAH PRODUK =================
if(isset($_POST['tambahproduk'])){

    $namaproduk = $_POST['namaproduk'];
    $deskripsi  = $_POST['deskripsi'];
    $harga      = $_POST['harga'];
    $stok       = $_POST['stok'];

    $query = mysqli_query($conn, "INSERT INTO produk (namaproduk, deskripsi, harga, stok)
                                  VALUES ('$namaproduk','$deskripsi','$harga','$stok')");

    if(!$query){
        die("Error insert: " . mysqli_error($conn));
    }

    echo "<script>
        alert('Produk berhasil ditambahkan');
        window.location.href='stok.php';
    </script>";
}


if(isset($_POST['tambahpelanggan'])){

    $namapelanggan = $_POST['namapelanggan'];

    $query = mysqli_query($conn, "INSERT INTO pelanggan (namapelanggan)
                                  VALUES ('$namapelanggan')");

    if(!$query){
        die("Error insert: " . mysqli_error($conn));
    }

    echo "<script>
        alert('Produk berhasil ditambahkan');
        window.location.href='pelanggan.php';
    </script>";
}

if(isset($_POST['tambahpesanan'])){

    $idpelanggan = $_POST['idpelanggan'];

    $query = mysqli_query($conn, "INSERT INTO pesanan(idpelanggan)
                                  VALUES ('$idpelanggan')");

    if(!$query){
        die("Error insert: " . mysqli_error($conn));
    }

    echo "<script>
        alert('Produk berhasil ditambahkan');
        window.location.href='index.php';
    </script>";
}

//view
if(isset($_POST['addproduk'])){
    $idp = $_POST['idp'];
    $idproduk = $_POST['idproduk'];
    $qty = $_POST['qty'];

    if($idproduk == "" || $qty <= 0){
        echo "<script>alert('Produk dan jumlah harus diisi');window.location='view.php?idp=$idp';</script>";
        exit;
    }

    // cek stok
    $cekstok = mysqli_query($conn,"SELECT stok FROM produk WHERE idproduk='$idproduk'");
    $datastok = mysqli_fetch_array($cekstok);
    $stoksekarang = $datastok['stok'];

    if($stoksekarang >= $qty){

        // kurangi stok
        $stokbaru = $stoksekarang - $qty;
        mysqli_query($conn,"UPDATE produk SET stok='$stokbaru' WHERE idproduk='$idproduk'");

        // cek apakah produk sudah ada di pesanan
        $cekproduk = mysqli_query($conn,"SELECT * FROM detailpesanan 
        WHERE idpesanan='$idp' AND idproduk='$idproduk'");

        if(mysqli_num_rows($cekproduk) > 0){
            mysqli_query($conn,"UPDATE detailpesanan 
            SET qty = qty + $qty 
            WHERE idpesanan='$idp' AND idproduk='$idproduk'");
        } else {
            mysqli_query($conn,"INSERT INTO detailpesanan (idpesanan,idproduk,qty)
            VALUES ('$idp','$idproduk','$qty')");
        }

        header("location:view.php?idp=$idp");
        exit;

    } else {
        echo "<script>alert('Stok tidak cukup');window.location='view.php?idp=$idp';</script>";
    }
}

// RESET DATA PELANGGAN + PESANAN
if(isset($_POST['resetpelanggan'])){

    // hapus data detail pesanan dulu
    mysqli_query($conn, "DELETE FROM detailpesanan");

    // hapus pesanan
    mysqli_query($conn, "DELETE FROM pesanan");

    // hapus pelanggan
    mysqli_query($conn, "DELETE FROM pelanggan");

    // reset AUTO_INCREMENT
    mysqli_query($conn, "ALTER TABLE pelanggan AUTO_INCREMENT = 1");
    mysqli_query($conn, "ALTER TABLE pesanan AUTO_INCREMENT = 1");
    mysqli_query($conn, "ALTER TABLE detailpesanan AUTO_INCREMENT = 1");

    echo "<script>
        alert('Data pelanggan dan pesanan berhasil direset');
        window.location='pelanggan.php';
    </script>";
}

// TAMBAH STOK DARI AKSI
if(isset($_POST['tambahstok'])){
    $idproduk = $_POST['idproduk'];
    $stok_tambah = $_POST['stok_tambah'];

    mysqli_query($conn, "
    UPDATE produk 
    SET stok = stok + $stok_tambah 
    WHERE idproduk='$idproduk'
    ");

    echo "<script>
        alert('Stok berhasil ditambahkan');
        window.location='stok.php';
    </script>";
}

?>