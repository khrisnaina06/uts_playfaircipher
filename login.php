<?php
// Fungsi untuk mengenkripsi password menggunakan Playfair Cipher
function createPlayfairMatrix($key)
{
    $key = str_replace('j', 'i', $key);
    $key = strtoupper($key);
    $key = str_split($key);

    $alphabet = "ABCDEFGHIKLMNOPQRSTUVWXYZ";
    $matrix = array();

    foreach ($key as $k) {
        if (!in_array($k, $matrix) && strpos($alphabet, $k) !== false) {
            $matrix[] = $k;
            $alphabet = str_replace($k, '', $alphabet);
        }
    }

    foreach (str_split($alphabet) as $letter) {
        $matrix[] = $letter;
    }

    return array_chunk($matrix, 5);
}

function findLetterPosition($matrix, $letter)
{
    foreach ($matrix as $rowIndex => $row) {
        if (in_array($letter, $row)) {
            return array($rowIndex, array_search($letter, $row));
        }
    }

    return null;
}

function playfairEncrypt($plaintext, $key)
{
    $plaintext = str_replace('j', 'i', $plaintext);
    $plaintext = strtoupper($plaintext);

    // Make plaintext even length by adding 'x' at the end if needed
    if (strlen($plaintext) % 2 != 0) {
        $plaintext .= 'x';
    }

    $matrix = createPlayfairMatrix($key);
    $ciphertext = '';

    for ($i = 0; $i < strlen($plaintext); $i += 2) {
        $char1 = $plaintext[$i];
        $char2 = $plaintext[$i + 1];

        list($row1, $col1) = findLetterPosition($matrix, $char1);
        list($row2, $col2) = findLetterPosition($matrix, $char2);

        if ($row1 == $row2) {
            $ciphertext .= $matrix[$row1][($col1 - 1 + 5) % 5];
            $ciphertext .= $matrix[$row2][($col2 - 1 + 5) % 5];
        } elseif ($col1 == $col2) {
            $ciphertext .= $matrix[($row1 - 1 + 5) % 5][$col1];
            $ciphertext .= $matrix[($row2 - 1 + 5) % 5][$col2];
        } else {
            $ciphertext .= $matrix[$row1][$col2];
            $ciphertext .= $matrix[$row2][$col1];
        }
    }

    return $ciphertext;
}

function playfairDecrypt($ciphertext, $key)
{
    $matrix = createPlayfairMatrix($key);
    $plaintext = '';

    for ($i = 0; $i < strlen($ciphertext); $i += 2) {
        $char1 = $ciphertext[$i];
        $char2 = $ciphertext[$i + 1];

        list($row1, $col1) = findLetterPosition($matrix, $char1);
        list($row2, $col2) = findLetterPosition($matrix, $char2);

        if ($row1 == $row2) {
            $plaintext .= $matrix[$row1][($col1 + 1) % 5];
            $plaintext .= $matrix[$row2][($col2 + 1) % 5];
        } elseif ($col1 == $col2) {
            $plaintext .= $matrix[($row1 + 1) % 5][$col1];
            $plaintext .= $matrix[($row2 + 1) % 5][$col2];
        } else {
            $plaintext .= $matrix[$row1][$col2];
            $plaintext .= $matrix[$row2][$col1];
        }
    }

    return $plaintext;
}


// Mengambil data dari form

    $username = $_POST['username'];
    $password = $_POST['password'];

    // Enkripsi password menggunakan Playfair Cipher
    $key = "tennis"; // Gantilah dengan kunci rahasia yang kuat
    $encryptedPassword = playfairEncrypt($password, $key);

    // Simpan data ke database MySQL
    $servername = "localhost";
    $db_username = "root";
    $db_password = "";
    $db_name = "playfair"; // Gantilah dengan nama database Anda

    // Buat koneksi ke database
    $conn = new mysqli($servername, $db_username, $db_password, $db_name);

    // Periksa koneksi
    if ($conn->connect_error) {
        die("Koneksi ke database gagal: " . $conn->connect_error);
    }

    // Query untuk mengambil data user dari database
    $sql = "SELECT * FROM users WHERE username='$username' AND password='$encryptedPassword'";
    $result = $conn->query($sql);
    
    if ($result->num_rows > 0) {
        echo '<div style="background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; padding: 10px; margin: 10px 0;">';
        echo 'Login berhasil!';
        echo '</div>';
    } else {
        echo "Username atau password salah.";
    }

    // Tutup koneksi database
    $conn->close();

?>
