<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/config.php';

function smtpRead($socket): string
{
    $data = '';
    while (($line = fgets($socket, 512)) !== false) {
        $data .= $line;
        if (strlen($line) < 4 || $line[3] === ' ') {
            break;
        }
    }
    return $data;
}

function smtpSend($socket, string $command): void
{
    fwrite($socket, $command . "\r\n");
}

function smtpOk(string $response, array $codes): bool
{
    $code = (int) substr($response, 0, 3);
    return in_array($code, $codes, true);
}

function sendAppEmail(string $to, string $subject, string $htmlBody, string $textBody = ''): bool
{
    if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
        return false;
    }

    $socket = @stream_socket_client(
        'ssl://' . APP_SMTP_HOST . ':' . APP_SMTP_PORT,
        $errno,
        $errstr,
        15
    );

    if (!$socket) {
        return false;
    }

    stream_set_timeout($socket, 15);

    $response = smtpRead($socket);
    if (!smtpOk($response, [220])) {
        fclose($socket);
        return false;
    }

    smtpSend($socket, 'EHLO localhost');
    if (!smtpOk(smtpRead($socket), [250])) {
        fclose($socket);
        return false;
    }

    smtpSend($socket, 'AUTH LOGIN');
    if (!smtpOk(smtpRead($socket), [334])) {
        fclose($socket);
        return false;
    }

    smtpSend($socket, base64_encode(APP_MAIL_FROM));
    if (!smtpOk(smtpRead($socket), [334])) {
        fclose($socket);
        return false;
    }

    smtpSend($socket, base64_encode(APP_MAIL_APP_PASSWORD));
    if (!smtpOk(smtpRead($socket), [235])) {
        fclose($socket);
        return false;
    }

    smtpSend($socket, 'MAIL FROM:<' . APP_MAIL_FROM . '>');
    if (!smtpOk(smtpRead($socket), [250])) {
        fclose($socket);
        return false;
    }

    smtpSend($socket, 'RCPT TO:<' . $to . '>');
    if (!smtpOk(smtpRead($socket), [250, 251])) {
        fclose($socket);
        return false;
    }

    smtpSend($socket, 'DATA');
    if (!smtpOk(smtpRead($socket), [354])) {
        fclose($socket);
        return false;
    }

    $boundary = 'bnd_' . bin2hex(random_bytes(8));
    $plain = $textBody !== '' ? $textBody : strip_tags($htmlBody);

    $headers = [
        'From: ' . APP_NAME . ' <' . APP_MAIL_FROM . '>',
        'To: <' . $to . '>',
        'Subject: ' . $subject,
        'MIME-Version: 1.0',
        'Content-Type: multipart/alternative; boundary="' . $boundary . '"',
    ];

    $message = implode("\r\n", $headers) . "\r\n\r\n";
    $message .= '--' . $boundary . "\r\n";
    $message .= "Content-Type: text/plain; charset=UTF-8\r\n\r\n";
    $message .= $plain . "\r\n\r\n";
    $message .= '--' . $boundary . "\r\n";
    $message .= "Content-Type: text/html; charset=UTF-8\r\n\r\n";
    $message .= $htmlBody . "\r\n\r\n";
    $message .= '--' . $boundary . "--\r\n.";

    fwrite($socket, $message . "\r\n");
    if (!smtpOk(smtpRead($socket), [250])) {
        fclose($socket);
        return false;
    }

    smtpSend($socket, 'QUIT');
    smtpRead($socket);
    fclose($socket);

    return true;
}
