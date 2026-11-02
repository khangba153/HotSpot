<?php

declare(strict_types=1);

namespace HotSpot\Core;

use PHPMailer\PHPMailer\PHPMailer;

final class Mailer
{
    // Gửi liên kết đặt lại mật khẩu đến email của tài khoản.
    public function sendPasswordResetLink(
        string $recipientEmail,
        string $resetUrl
    ): void {
        $configuration = require dirname(__DIR__) . '/config/app.php';
        $mailConfiguration = $configuration['mail'];

        $mailer = new PHPMailer(true);

        $mailer->isSMTP();
        $mailer->Host = $mailConfiguration['host'];
        $mailer->Port = $mailConfiguration['port'];
        $mailer->SMTPAuth = true;
        $mailer->Username = $mailConfiguration['username'];
        $mailer->Password = $mailConfiguration['password'];
        $mailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mailer->Timeout = 15;
        $mailer->CharSet = 'UTF-8';

        $mailer->setFrom(
            $mailConfiguration['username'],
            $mailConfiguration['from_name']
        );

        $mailer->addAddress($recipientEmail);

        $mailer->isHTML(false);
        $mailer->Subject = 'Đặt lại mật khẩu Hot Spot';

        $mailer->Body =
            "Bạn vừa yêu cầu đặt lại mật khẩu Hot Spot.\n\n"
            . "Mở liên kết sau để đặt mật khẩu mới:\n"
            . $resetUrl
            . "\n\nLiên kết có hiệu lực trong 15 phút "
            . "và chỉ sử dụng được một lần.\n\n"
            . "Nếu bạn không gửi yêu cầu này, "
            . "hãy bỏ qua email. Mật khẩu hiện tại vẫn được giữ nguyên.";

        $mailer->send();
    }
}