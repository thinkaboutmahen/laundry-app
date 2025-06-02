<?php
require_once '../config/database.php';
require_once '../config/email.php';
require_once '../config/session.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if it's a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

// Get JSON data from request body
$json = file_get_contents('php://input');
$data = json_decode($json, true);

if (!isset($data['transaksi_id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Transaction ID is required']);
    exit();
}

try {
    // Get transaction details
    $stmt = $pdo->prepare("
        SELECT 
            t.*, 
            p.name_pelanggan, p.email_pelanggan,
            l.jenis_laundry
        FROM transaksi t 
        JOIN pelanggan p ON t.id_pelanggan = p.id_pelanggan 
        JOIN paket_laundry l ON t.id_laundry = l.id_laundry 
        WHERE t.id_transaksi = ?
    ");
    
    $stmt->execute([$data['transaksi_id']]);
    $transaction = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$transaction) {
        throw new Exception('Transaction not found');
    }

    if (empty($transaction['email_pelanggan'])) {
        throw new Exception('Customer email not found');
    }

    // Format dates
    $tanggal_terima = date('d F Y', strtotime($transaction['tanggal_terima']));
    $tanggal_selesai = $transaction['tanggal_selesai'] ? date('d F Y', strtotime($transaction['tanggal_selesai'])) : 'Belum ditentukan';
    
    // Format currency
    $total_bayar = 'Rp ' . number_format($transaction['total_bayar'], 0, ',', '.');

    // Create email content
    $subject = "Update Status Laundry Anda - Order #LD-" . str_pad($transaction['id_transaksi'], 4, '0', STR_PAD_LEFT);

    $emailBody = "
    <!DOCTYPE html>
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { text-align: center; margin-bottom: 30px; }
            .header img { max-width: 150px; }
            .content { background: #f9f9f9; padding: 20px; border-radius: 5px; }
            .status { 
                background: #e3f2fd; 
                padding: 10px; 
                border-radius: 5px; 
                margin: 15px 0; 
                text-align: center;
                font-weight: bold;
            }
            .details { margin: 20px 0; }
            .details p { margin: 5px 0; }
            .footer { 
                text-align: center; 
                margin-top: 30px; 
                font-size: 12px; 
                color: #666; 
            }
            .button {
                display: inline-block;
                padding: 10px 20px;
                background-color: #4CAF50;
                color: white;
                text-decoration: none;
                border-radius: 5px;
                margin-top: 20px;
            }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <img src='cid:logo' alt='Laundry Logo'>
                <h2>Update Status Laundry</h2>
            </div>
            
            <div class='content'>
                <p>Halo {$transaction['name_pelanggan']},</p>
                
                <p>Kami ingin memberitahu Anda tentang status terbaru dari layanan laundry Anda:</p>
                
                <div class='status'>
                    Status Laundry: {$transaction['status_laundry']}<br>
                    Status Pembayaran: {$transaction['status_pembayaran']}
                </div>
                
                <div class='details'>
                    <p><strong>No. Order:</strong> LD-" . str_pad($transaction['id_transaksi'], 4, '0', STR_PAD_LEFT) . "</p>
                    <p><strong>Tanggal Terima:</strong> {$tanggal_terima}</p>
                    <p><strong>Tanggal Selesai:</strong> {$tanggal_selesai}</p>
                    <p><strong>Jenis Layanan:</strong> {$transaction['jenis_laundry']}</p>
                    <p><strong>Total Pembayaran:</strong> {$total_bayar}</p>
                </div>
                
                <p>Jika Anda memiliki pertanyaan atau membutuhkan bantuan, silakan hubungi kami.</p>
                
                <p>Terima kasih telah mempercayakan layanan laundry Anda kepada kami.</p>
                
                <p>Salam,<br>Tim Laundry</p>
            </div>
            
            <div class='footer'>
                <p>Email ini dikirim secara otomatis. Mohon tidak membalas email ini.</p>
                <p>&copy; " . date('Y') . " Laundry. All rights reserved.</p>
            </div>
        </div>
    </body>
    </html>";

    // Set email headers
    $headers = array(
        'MIME-Version: 1.0',
        'Content-type: text/html; charset=UTF-8',
        'From: Laundry <noreply@laundry.com>',
        'Reply-To: support@laundry.com',
        'X-Mailer: PHP/' . phpversion()
    );

    // Attach logo
    $mail->addEmbeddedImage('../assets/images/logoinvoice.png', 'logo');

    // Set email parameters
    $mail->setFrom('noreply@laundry.com', 'Laundry');
    $mail->addAddress($transaction['email_pelanggan'], $transaction['name_pelanggan']);
    $mail->Subject = $subject;
    $mail->Body = $emailBody;
    $mail->AltBody = strip_tags($emailBody);

    // Send email
    if ($mail->send()) {
        echo json_encode(['success' => true, 'message' => 'Notification sent successfully']);
    } else {
        throw new Exception('Failed to send email: ' . $mail->ErrorInfo);
    }

} catch (Exception $e) {
    error_log("Error in send_notification.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
} 