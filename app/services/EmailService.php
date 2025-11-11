<?php

namespace App\Services;

class EmailService
{
    private $config;

    public function __construct()
    {
        $this->config = require __DIR__ . '/../config/email.php';
    }

    public function send(string $to, string $subject, string $htmlBody, string $textBody = '')
    {
        error_log('[EmailService] Attempting to send email'
            . ' to=' . $to
            . ' subject="' . $subject . '"'
            . ' driver=' . ($this->config['driver'] ?? 'mail'));
        // Try PHPMailer if available
        if (class_exists('\\PHPMailer\\PHPMailer\\PHPMailer') && $this->config['driver'] === 'smtp') {
            $result = $this->sendWithPHPMailer($to, $subject, $htmlBody, $textBody);
            error_log('[EmailService] SMTP send result=' . ($result ? 'success' : 'failed') . ' to=' . $to);
            return $result;
        }
        // Fallback to mail()
        $headers  = "MIME-Version: 1.0\r\n";
        $headers .= "Content-type: text/plain; charset=UTF-8\r\n";
        $headers .= "From: " . ($this->config['from_email'] ?? 'no-reply@studyaids') . "\r\n";
        $fallbackResult = @mail($to, $subject, $textBody ?: strip_tags($htmlBody), $headers);
        error_log('[EmailService] mail() send result=' . ($fallbackResult ? 'success' : 'failed') . ' to=' . $to);
        return $fallbackResult;
    }

    private function sendWithPHPMailer(string $to, string $subject, string $htmlBody, string $textBody = '')
    {
        $cfg = $this->config;
        $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
        try {
            error_log('[EmailService] Using PHPMailer SMTP host=' . $cfg['host'] . ' port=' . $cfg['port']);
            $mail->isSMTP();
            $mail->Host = $cfg['host'];
            $mail->SMTPAuth = true;
            $mail->Username = $cfg['username'];
            $mail->Password = $cfg['password'];
            $mail->SMTPSecure = $cfg['encryption'] === 'ssl' ? \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS : \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = $cfg['port'];

            $mail->setFrom($cfg['from_email'], $cfg['from_name'] ?? 'StudyAids');
            $mail->addAddress($to);

            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = $htmlBody;
            $mail->AltBody = $textBody ?: strip_tags($htmlBody);

            $mail->send();
            return true;
        } catch (\Throwable $e) {
            error_log('Email send error: ' . $e->getMessage());
            return false;
        }
    }
}


