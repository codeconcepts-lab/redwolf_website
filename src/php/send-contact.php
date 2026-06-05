<?php

// ─── SMTP CONFIGURATION ───────────────────────────────────────────────────────
// In cPanel: Email Accounts → Create → then fill in credentials below.
define('SMTP_HOST',     'smtpout.secureserver.net'); // your cPanel mail server
define('SMTP_PORT',     465);                         // 587 = STARTTLS  |  465 = SSL
define('SMTP_USER',     'noreply@redwolfsecurity.com');
define('SMTP_PASS',     'zVYretIrZi?,W8}K');  // ← replace this

define('MAIL_FROM',     'noreply@redwolfsecurity.com');
define('MAIL_FROM_NAME','Red Wolf Security');
define('MAIL_TO',       'contact@redwolfsecurity.com');
define('SUCCESS_URL',   '/thank-you.html');
define('ERROR_URL',     '/contact.html?error=1');
// ─────────────────────────────────────────────────────────────────────────────

require_once __DIR__ . '/../../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /contact.html');
    exit;
}

function clean(string $v): string {
    return htmlspecialchars(strip_tags(trim($v)), ENT_QUOTES, 'UTF-8');
}

$firstName = clean($_POST['first_name'] ?? '');
$lastName  = clean($_POST['last_name']  ?? '');
$email     = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
$service   = clean($_POST['service_interest'] ?? 'Not specified');
$message   = nl2br(clean($_POST['message'] ?? ''));

if (!$firstName || !$lastName || !$email || !$_POST['message']) {
    header('Location: ' . ERROR_URL); exit;
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header('Location: ' . ERROR_URL); exit;
}

// ─── EMAIL BODIES ─────────────────────────────────────────────────────────────
$notifyHtml = <<<HTML
<!DOCTYPE html><html><head><meta charset="UTF-8">
<style>
  body{font-family:Arial,sans-serif;background:#f4f5f6;margin:0;padding:40px}
  .w{max-width:600px;margin:0 auto;background:#18181b;border-radius:12px;overflow:hidden}
  .h{background:#972f1e;padding:28px 32px}.h h1{margin:0;color:#fff;font-size:20px;font-weight:900;text-transform:uppercase}
  .h p{margin:4px 0 0;color:rgba(255,255,255,.7);font-size:13px}
  .b{padding:32px}.st{font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.15em;color:#ebaf0b;margin-bottom:12px}
  .lbl{font-size:11px;color:#71717a;text-transform:uppercase;margin-bottom:3px}.val{font-size:14px;color:#f4f4f5;font-weight:600;margin-bottom:10px}
  .msg{background:#09090b;border:1px solid #27272a;border-radius:8px;padding:16px;font-size:14px;color:#d4d4d8;line-height:1.6}
  .ft{padding:20px 32px;border-top:1px solid #27272a;text-align:center;font-size:12px;color:#52525b}
</style></head><body>
<div class="w"><div class="h"><h1>New Contact Message</h1><p>redwolfsecurity.com</p></div>
<div class="b">
  <div class="st">Contact Details</div>
  <div class="lbl">Name</div><div class="val">{$firstName} {$lastName}</div>
  <div class="lbl">Email</div><div class="val">{$email}</div>
  <div class="lbl">Service Interest</div><div class="val">{$service}</div>
  <div class="st" style="margin-top:20px">Message</div>
  <div class="msg">{$message}</div>
</div>
<div class="ft">Red Wolf Security &mdash; Contact Form</div></div>
</body></html>
HTML;

$confirmHtml = <<<CONFIRM
<!DOCTYPE html><html><head><meta charset="UTF-8">
<style>
  body{font-family:Arial,sans-serif;background:#f4f5f6;margin:0;padding:40px}
  .w{max-width:600px;margin:0 auto;background:#18181b;border-radius:12px;overflow:hidden}
  .h{background:#972f1e;padding:28px 32px}.h h1{margin:0;color:#fff;font-size:20px;font-weight:900;text-transform:uppercase}
  .h p{margin:4px 0 0;color:rgba(255,255,255,.7);font-size:13px}
  .b{padding:32px;color:#d4d4d8;font-size:14px;line-height:1.7}
  .b h2{color:#fff;font-size:18px;margin:0 0 12px}.hl{color:#ebaf0b;font-weight:700}
  .ft{padding:20px 32px;border-top:1px solid #27272a;text-align:center;font-size:12px;color:#52525b}
  .btn{display:inline-block;margin-top:20px;padding:12px 24px;background:#972f1e;color:#fff;text-decoration:none;border-radius:6px;font-weight:700;font-size:13px;text-transform:uppercase}
</style></head><body>
<div class="w"><div class="h"><h1>Message Received</h1><p>Red Wolf Security</p></div>
<div class="b">
  <h2>Hi {$firstName},</h2>
  <p>Thank you for contacting Red Wolf Security. We've received your message and will be in touch shortly.</p>
  <p><span class="hl">contact@redwolfsecurity.com</span> &nbsp;|&nbsp; <span class="hl">+234 818 030 3067</span></p>
  <a href="https://redwolfsecurity.com/services.html" class="btn">Explore Our Services</a>
</div>
<div class="ft">Red Wolf Security &mdash; Absolute Safety Without Compromise</div></div>
</body></html>
CONFIRM;

// ─── SEND VIA SMTP ────────────────────────────────────────────────────────────
function makeMailer(): PHPMailer {
    $m = new PHPMailer(true);
    $m->isSMTP();
    $m->Host       = SMTP_HOST;
    $m->SMTPAuth   = true;
    $m->Username   = SMTP_USER;
    $m->Password   = SMTP_PASS;
    $m->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;    // SSL on port 465
    $m->Port       = SMTP_PORT;
    $m->Timeout    = 15;                              // fail fast — prevent 524 timeout
    $m->SMTPOptions = [
        'ssl' => [
            'verify_peer'       => false,
            'verify_peer_name'  => false,
            'allow_self_signed' => true,
        ],
    ];
    $m->CharSet    = 'UTF-8';
    $m->setFrom(MAIL_FROM, MAIL_FROM_NAME);
    return $m;
}

$sent = false;
try {
    // Notification to Red Wolf
    $m = makeMailer();
    $m->addAddress(MAIL_TO);
    $m->addReplyTo($email, "{$firstName} {$lastName}");
    $m->isHTML(true);
    $m->Subject = "Contact Message — {$firstName} {$lastName}";
    $m->Body    = $notifyHtml;
    $m->send();

    // Auto-reply to submitter
    $r = makeMailer();
    $r->addAddress($email, "{$firstName} {$lastName}");
    $r->isHTML(true);
    $r->Subject = "We've received your message — Red Wolf Security";
    $r->Body    = $confirmHtml;
    $r->send();

    $sent = true;
} catch (Exception $e) {
    error_log('Contact mail error: ' . $e->getMessage());
}

header('Location: ' . ($sent ? SUCCESS_URL : ERROR_URL));
exit;
