<?php
include 'connect.php';
include 'karyawan.php';

// mengambil data dari database
$query = "SELECT * FROM karyawan";
$result = $koneksi->query($query);

// menyimpan data dalam array
$karyawanData = array();
while ($row = $result->fetch_assoc()) {
    $karyawan = new Karyawan($row['nik'], $row['nama'], $row['upah_per_jam'], $row['jam_kerja'], $row['jam_lembur']);
    $karyawanData[] = $karyawan;
}

// memperbarui jam lembur
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    foreach ($karyawanData as $karyawan) {
        $newJamLembur = $_POST["jamLembur_" . $karyawan->nik];

        // Update data di database
        $updateQuery = "UPDATE karyawan SET jam_lembur = '$newJamLembur' WHERE nik = '{$karyawan->nik}'";
        $koneksi->query($updateQuery);

        // Update data jam lembur di objek Karyawan
        $karyawan->jam_lembur = $newJamLembur;
    }
}

// menghitung rekap total gaji tiap karyawan 
$rekapMingguan = array();

foreach ($karyawanData as $karyawan) {
    $rekapMingguan[$karyawan->nik] = array();

    // menghitung total gaji per minggu
    for ($mingguKe = 1; $mingguKe <= 4; $mingguKe++) {
        $totalGajiMingguan = $karyawan->hitungGaji();
        $rekapMingguan[$karyawan->nik][$mingguKe] = $totalGajiMingguan;

    // menambahkan jam lembur untuk minggu berikutnya
        $karyawan->jam_lembur += 5; 
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Gaji Karyawan</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h1>Sistem Gaji Karyawan</h1>

    <form method="post" action="<?php echo $_SERVER["PHP_SELF"]; ?>">
        <table>
            <tr>
                <th>Nomor Induk Karyawan</th>
                <th>Nama Karyawan</th>
                <th>Upah Per Jam</th>
                <th>Jam Kerja</th>
                <th>Upah Lembur</th>
                <th>Jam Lembur</th>
                <?php for ($mingguKe = 1; $mingguKe <= 4; $mingguKe++): ?>
                    <th>Minggu Ke-<?php echo $mingguKe; ?></th>
                <?php endfor; ?>
            </tr>
            <?php foreach ($karyawanData as $karyawan): ?>
                <tr>
                    <td><?php echo $karyawan->nik; ?></td>
                    <td><?php echo $karyawan->nama; ?></td>
                    <td><?php echo "Rp. " . number_format($karyawan->upah_per_jam, 0, ',', '.'); ?></td>
                    <td><?php echo $karyawan->jam_kerja; ?></td>
                    <td><?php echo "Rp. " . number_format($karyawan->hitungUpahLembur(), 0, ',', '.'); ?></td>
                    <td><input type="number" name="jamLembur_<?php echo $karyawan->nik; ?>" value="<?php echo $karyawan->jam_lembur; ?>"></td>
                    <?php for ($mingguKe = 1; $mingguKe <= 4; $mingguKe++): ?>
                        <td><?php echo "Rp. " . number_format($rekapMingguan[$karyawan->nik][$mingguKe], 0, ',', '.'); ?></td>
                    <?php endfor; ?>
                </tr>
            <?php endforeach; ?>
        </table>

        <button type="submit">Simpan Perubahan</button>
    </form>
</body>
</html>

<?php
// Tutup koneksi ke database
$koneksi->close();
?>