<?php
declare(strict_types=1);

class EncryptionException extends Exception {}

function encrypt(string $plaintext, string $key): string {
    $cipher = 'aes-256-gcm';
    $ivLen  = openssl_cipher_iv_length($cipher);
    $iv     = random_bytes($ivLen);

    $tag    = '';
    $ciphertext = openssl_encrypt(
        $plaintext,
        $cipher,
        hash('sha256', $key, true), // clé dérivée en 256 bits
        OPENSSL_RAW_DATA,
        $iv,
        $tag
    );

    if ($ciphertext === false) {
        throw new EncryptionException("Encryption failed");
    }

    // Stocke IV + tag + données chiffrées (format binaire)
    return base64_encode($iv . $tag . $ciphertext);
}

function decrypt(string $cipherData, string $key): string {
    $cipher = 'aes-256-gcm';
    $raw    = base64_decode($cipherData, true);

    if ($raw === false) {
        throw new EncryptionException("Invalid base64 data");
    }

    $ivLen = openssl_cipher_iv_length($cipher);
    $iv    = substr($raw, 0, $ivLen);
    $tag   = substr($raw, $ivLen, 16); // tag GCM = 16 octets
    $ciphertext = substr($raw, $ivLen + 16);

    $plaintext = openssl_decrypt(
        $ciphertext,
        $cipher,
        hash('sha256', $key, true),
        OPENSSL_RAW_DATA,
        $iv,
        $tag
    );

    if ($plaintext === false) {
        throw new EncryptionException("Decryption failed");
    }

    return $plaintext;
}

// =======================
// Exemple d'utilisation
// =======================
$key = "ik ben een aardbei";

$data = '{"user":"sooty","ex":1723620672,"level":"admin","bio":"Test secure"}';

try {
    $encrypted = encrypt($data, $key);
    echo "Encrypted: $encrypted\n";

    $decrypted = decrypt($encrypted, $key);
    echo "Decrypted: $decrypted\n";
} catch (EncryptionException $e) {
    echo "Error: " . $e->getMessage();
}
