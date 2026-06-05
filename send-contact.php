<?php

// ─── CONFIGURATION ────────────────────────────────────────────────────────────
define('MAIL_FROM', 'noreply@redwolfsecurity.com');
define('MAIL_FROM_NAME', 'Red Wolf Security');
define('MAIL_TO', 'contact@redwolfsecurity.com');
define('SUCCESS_URL', '/thank-you.html');
define('ERROR_URL', '/contact.html?error=1');
// ──────────────────────────────────────────────────────────────────────────────

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /contact.html');
    exit;
}

function clean(string $value): string
{
    return htmlspecialchars(strip_tags(trim($value)), ENT_QUOTES, 'UTF-8');
}

$firstName = clean($_POST['first_name'] ?? '');
$lastName = clean($_POST['last_name'] ?? '');
$email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
$service = clean($_POST['service_interest'] ?? 'Not specified');
$message = nl2br(clean($_POST['message'] ?? ''));

if (!$firstName || !$lastName || !$email || !$_POST['message']) {
    header('Location: ' . ERROR_URL);
    exit;
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header('Location: ' . ERROR_URL);
    exit;
}

$html = <<<HTML
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<style>
  body { font-family: Arial, sans-serif; background: #f4f5f6; margin: 0; padding: 40px; }
  .wrapper { max-width: 600px; margin: 0 auto; background: #18181b; border-radius: 12px; overflow: hidden; }
  .header { background: #972f1e; padding: 28px 32px; }
  .header h1 { margin: 0; color: #fff; font-size: 20px; font-weight: 900; letter-spacing: 0.05em; text-transform: uppercase; }
  .header p { margin: 4px 0 0; color: rgba(255,255,255,0.7); font-size: 13px; }
  .body { padding: 32px; }
  .section { margin-bottom: 28px; }
  .section-title { font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.15em; color: #ebaf0b; margin-bottom: 12px; }
  .row { margin-bottom: 10px; }
  .label { font-size: 11px; color: #71717a; text-transform: uppercase; letter-spacing: 0.08em; margin-bottom: 3px; }
  .value { font-size: 14px; color: #f4f4f5; font-weight: 600; }
  .message-box { background: #09090b; border: 1px solid #27272a; border-radius: 8px; padding: 16px; font-size: 14px; color: #d4d4d8; line-height: 1.6; }
  .footer { padding: 20px 32px; border-top: 1px solid #27272a; text-align: center; font-size: 12px; color: #52525b; }
</style>
</head>
<body>
<div class="wrapper">
  <div class="header">
    <h1>New Contact Message</h1>
    <p>Submitted via redwolfsecurity.com</p>
  </div>
  <div class="body">
    <div class="section">
      <div class="section-title">Contact Details</div>
      <div class="row"><div class="label">Name</div><div class="value">{$firstName} {$lastName}</div></div>
      <div class="row"><div class="label">Email</div><div class="value">{$email}</div></div>
      <div class="row"><div class="label">Service Interest</div><div class="value">{$service}</div></div>
    </div>
    <div class="section">
      <div class="section-title">Message</div>
      <div class="message-box">{$message}</div>
    </div>
  </div>
  <div class="footer">Red Wolf Security &mdash; Contact Form</div>
</div>
</body>
</html>
HTML;

$headers = "MIME-Version: 1.0\r\n";
$headers .= "Content-type: text/html; charset=UTF-8\r\n";
$headers .= "From: " . MAIL_FROM_NAME . " <" . MAIL_FROM . ">\r\n";
$headers .= "Reply-To: {$email}\r\n";

// Send notification to Red Wolf
$sent = mail(MAIL_TO, "Contact Message — {$firstName} {$lastName}", $html, $headers);

// Send confirmation auto-reply to submitter
$confirmHtml = <<<CONFIRM
<!DOCTYPE html>
<html>
<head><meta charset="UTF-8">
<style>
  body { font-family: Arial, sans-serif; background: #f4f5f6; margin: 0; padding: 40px; }
  .wrapper { max-width: 600px; margin: 0 auto; background: #18181b; border-radius: 12px; overflow: hidden; }
  .header { background: #972f1e; padding: 28px 32px; }
  .header h1 { margin: 0; color: #fff; font-size: 20px; font-weight: 900; letter-spacing: 0.05em; text-transform: uppercase; }
  .header p { margin: 4px 0 0; color: rgba(255,255,255,0.7); font-size: 13px; }
  .body { padding: 32px; color: #d4d4d8; font-size: 14px; line-height: 1.7; }
  .body h2 { color: #fff; font-size: 18px; margin: 0 0 12px; }
  .highlight { color: #ebaf0b; font-weight: 700; }
  .footer { padding: 20px 32px; border-top: 1px solid #27272a; text-align: center; font-size: 12px; color: #52525b; }
  .btn { display: inline-block; margin-top: 20px; padding: 12px 24px; background: #972f1e; color: #fff; text-decoration: none; border-radius: 6px; font-weight: 700; font-size: 13px; text-transform: uppercase; letter-spacing: 0.05em; }
</style>
</head>
<body>
<div class="wrapper">
  <div class="header">
    <h1>Message Received</h1>
    <p>Red Wolf Security</p>
  </div>
  <div class="body">
    <h2>Hi {$firstName},</h2>
    <p>Thank you for contacting Red Wolf Security. We've received your message and our team will be in touch with you shortly.</p>
    <p>In the meantime, feel free to explore our full range of security services or get in touch directly:</p>
    <p><span class="highlight">contact@redwolfsecurity.com</span> &nbsp;|&nbsp; <span class="highlight">+234 818 030 3067</span></p>
    <a href="https://redwolfsecurity.com/services.html" class="btn">Explore Our Services</a>
  </div>
  <div class="footer">Red Wolf Security &mdash; Absolute Safety Without Compromise</div>
</div>
</body>
</html>
CONFIRM;

$confirmHeaders  = "MIME-Version: 1.0\r\n";
$confirmHeaders .= "Content-type: text/html; charset=UTF-8\r\n";
$confirmHeaders .= "From: " . MAIL_FROM_NAME . " <" . MAIL_FROM . ">\r\n";

mail($email, "We've received your message — Red Wolf Security", $confirmHtml, $confirmHeaders);

if ($sent) {
    header('Location: ' . SUCCESS_URL);
} else {
    header('Location: ' . ERROR_URL);
}
exit;
