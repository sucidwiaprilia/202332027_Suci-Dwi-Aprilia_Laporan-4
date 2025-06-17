<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Penilaian Mahasiswa</title>

    <!-- Bootstrap 5.3.6 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-4Q6Gf2aSP4eDXB8Miphtr37CMZZQ5oXLH2yaXMJ2w8e2ZtHTl7GptT4jmndRuHDT" crossorigin="anonymous">

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-j1CDi7MgGQ12Z7Qab0qlWQ/Qqz24Gc6BM0thvEMVjHnfYGF0rmFCozFSxQBxwHKO" crossorigin="anonymous"></script>
</head>

<body>

<?php
// Variabel default
$nama = $nim = "";
$absen = $tugas = $uts = $uas = "";
$errors = false;
$error_messages = [];
$show_result = false;
$grade = "";
$status = "";
$nilai_akhir = 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = trim($_POST['nama'] ?? '');
    $nim = trim($_POST['nim'] ?? '');
    $absen = trim($_POST['absen'] ?? '');
    $tugas = trim($_POST['tugas'] ?? '');
    $uts = trim($_POST['uts'] ?? '');
    $uas = trim($_POST['uas'] ?? '');

    // Validasi kolom kosong
    $fields_required = [
        'Nama' => $nama,
        'NIM' => $nim,
        'Nilai Kehadiran' => $absen,
        'Nilai Tugas' => $tugas,
        'Nilai UTS' => $uts,
        'Nilai UAS' => $uas,
    ];

    $empty_fields = [];
    foreach ($fields_required as $label => $value) {
        if ($value === '') {
            $empty_fields[] = $label;
        }
    }

    if (!empty($empty_fields)) {
        $errors = true;
        if (count($empty_fields) === count($fields_required)) {
            $error_messages[] = "Semua kolom harus diisi!";
        } elseif (count($empty_fields) === 1) {
            $error_messages[] = "Kolom {$empty_fields[0]} belum terisi!";
        } else {
            $last = array_pop($empty_fields);
            $error_messages[] = "Kolom " . implode(', ', $empty_fields) . " dan $last belum terisi!";
        }
    } else {
        // Validasi input numerik (0-100)
        foreach (['Nilai Kehadiran' => $absen, 'Nilai Tugas' => $tugas, 'Nilai UTS' => $uts, 'Nilai UAS' => $uas] as $label => $val) {
            if (!is_numeric($val) || $val < 0 || $val > 100) {
                $errors = true;
                $error_messages[] = "Kolom {$label} harus berupa angka antara 0 sampai 100!";
            }
        }
    }

    if (!$errors) {
        // Hitung nilai akhir berbobot
        $nilai_akhir = $absen * 0.10 + $tugas * 0.20 + $uts * 0.30 + $uas * 0.40;

        // Tentukan grade berdasarkan nilai akhir
        if ($nilai_akhir >= 85) {
            $grade = 'A';
        } elseif ($nilai_akhir >= 70) {
            $grade = 'B';
        } elseif ($nilai_akhir >= 55) {
            $grade = 'C';
        } elseif ($nilai_akhir >= 40) {
            $grade = 'D';
        } else {
            $grade = 'E';
        }

        // Tentukan status kelulusan
        if ($absen < 70) {
            $status = 'TIDAK LULUS';
        } else {
            if ($nilai_akhir >= 60 && $tugas >= 40 && $uts >= 40 && $uas >= 40) {
                $status = 'LULUS';
            } else {
                $status = 'TIDAK LULUS';
            }
        }

        $show_result = true;
    }
}
?>

    <div class="container my-4">
        <div class="card">
            <div class="card-header bg-primary text-white py-2 text-center">
                <h4>Form Penilaian Mahasiswa</h4>
            </div>
            <div class="card-body">
                <form method="post" action="">
                    <?php
                    function inputField($id, $label, $value, $type='text', $min=null, $max=null) {
                        $minAttr = ($min !== null) ? "min=\"$min\"" : "";
                        $maxAttr = ($max !== null) ? "max=\"$max\"" : "";
                        $valEsc = htmlspecialchars($value);
                        echo <<<HTML
                        <div class="mb-3">
                            <label for="$id" class="form-label">$label</label>
                            <input type="$type" id="$id" name="$id" class="form-control" value="$valEsc" $minAttr $maxAttr>
                        </div>
                        HTML;
                    }

                    inputField('nama', 'Masukkan Nama', $nama);
                    inputField('nim', 'Masukkan NIM', $nim);
                    inputField('absen', 'Nilai Kehadiran (10%)', $absen, 'number', 0, 100);
                    inputField('tugas', 'Nilai Tugas (20%)', $tugas, 'number', 0, 100);
                    inputField('uts', 'Nilai UTS (30%)', $uts, 'number', 0, 100);
                    inputField('uas', 'Nilai UAS (40%)', $uas, 'number', 0, 100);
                    ?>
                    <button type="submit" class="btn btn-primary w-100">Proses</button>

                    <?php if ($errors): ?>
                        <?php foreach ($error_messages as $msg): ?>
                            <div class="alert alert-danger mt-2 mb-0" role="alert"><?php echo $msg; ?></div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </form>
            </div>
        </div>

        <?php if ($show_result): ?>
            <?php $bgClass = ($status === 'LULUS') ? 'bg-success' : 'bg-danger'; ?>
            <div class="card mt-4">
                <div class="card-header <?php echo $bgClass; ?> text-white text-start">
                    <h5>Hasil Penilaian</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6 text-center"><strong>Nama:</strong> <?php echo htmlspecialchars($nama); ?></div>
                        <div class="col-md-6"><strong>NIM:</strong> <?php echo htmlspecialchars($nim); ?></div>
                    </div>
                    <p>Nilai Kehadiran: <strong><?php echo htmlspecialchars($absen); ?>%</strong></p>
                    <p>Nilai Tugas: <strong><?php echo htmlspecialchars($tugas); ?></strong></p>
                    <p>Nilai UTS: <strong><?php echo htmlspecialchars($uts); ?></strong></p>
                    <p>Nilai UAS: <strong><?php echo htmlspecialchars($uas); ?></strong></p>
                    <p>Nilai Akhir: <strong><?php echo number_format($nilai_akhir, 2); ?></strong></p>
                    <p>Grade: <strong><?php echo $grade; ?></strong></p>
                    <p>Status: <strong><?php echo $status; ?></strong></p>
                </div>
                <div class="card-footer <?php echo $bgClass; ?>">
                    <button class="btn btn-selesai w-100" onclick="window.location.href=window.location.href;">Selesai</button>
                </div>
            </div>
        <?php endif; ?>
    </div>

</body>

</html>
