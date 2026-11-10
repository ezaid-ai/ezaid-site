<?php
// EZaid website contact form handler.
// Receives the form as JSON (or a regular POST) and emails it to the address below.

$TO   = 'info@ezaid.ai';
$FROM = 'no-reply@ezaid.ai';   // must be an address on the domain this site is hosted on

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

function reply($ok, $error = null, $status = 200) {
    http_response_code($status);
    echo json_encode($error === null ? ['ok' => $ok] : ['ok' => $ok, 'error' => $error]);
    exit;
}

function cut($text, $max) {
    return function_exists('mb_substr') ? mb_substr($text, 0, $max, 'UTF-8') : substr($text, 0, $max);
}

// One line of text: trimmed, no line breaks (prevents email header injection), limited length.
function line($value, $max) {
    $value = is_string($value) ? $value : '';
    $value = preg_replace('/[\r\n\t\0]+/', ' ', $value);
    return cut(trim($value), $max);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    reply(false, 'method_not_allowed', 405);
}

$data = json_decode(file_get_contents('php://input'), true);
if (!is_array($data)) {
    $data = $_POST;
}

// Hidden field that real visitors never fill in. Bots do: pretend success and send nothing.
if (!empty($data['website'])) {
    reply(true);
}

$name    = line($data['name']  ?? '', 100);
$email   = line($data['email'] ?? '', 150);
$phone   = line($data['phone'] ?? '', 40);
$topic   = line($data['topic'] ?? '', 60);
$message = cut(trim(is_string($data['message'] ?? null) ? $data['message'] : ''), 5000);

if (strlen($name) < 2)                              reply(false, 'name', 422);
if (!filter_var($email, FILTER_VALIDATE_EMAIL))     reply(false, 'email', 422);
if (strlen($message) < 10)                          reply(false, 'message', 422);
if ($topic === '')                                  $topic = 'Other';

$subject = '[EZaid website] ' . $topic . ' - ' . $name;
$body = "New message from the contact form on the EZaid website\n\n"
      . "Name:   $name\n"
      . "Email:  $email\n"
      . "Phone:  " . ($phone !== '' ? $phone : '-') . "\n"
      . "Topic:  $topic\n"
      . "Sent:   " . gmdate('Y-m-d H:i') . " UTC\n"
      . "IP:     " . ($_SERVER['REMOTE_ADDR'] ?? '-') . "\n\n"
      . "Message:\n" . str_replace("\r\n", "\n", $message) . "\n";

$headers = implode("\r\n", [
    'From: EZaid website <' . $FROM . '>',
    'Reply-To: ' . $email,
    'MIME-Version: 1.0',
    'Content-Type: text/plain; charset=UTF-8',
    'Content-Transfer-Encoding: 8bit',
]);

$sent = @mail($TO, '=?UTF-8?B?' . base64_encode($subject) . '?=', $body, $headers);

if (!$sent) {
    reply(false, 'send_failed', 500);
}
reply(true);
