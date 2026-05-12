Subject: Login Link
From: <?= $From ?>
To: <?= $To ?>
Cc:
Bcc:
Format: HTML

<p>Dear Sir/Madam,</p>

<p>Please click the following link to login to your account. The link will expire in <?= $LifeTime / 60 ?> minutes.<br>
<a href="<?= $LoginLink ?>">Login</a>
</p>

<p>
Best Regards,<br>
Support
</p>
