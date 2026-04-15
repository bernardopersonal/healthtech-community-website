<?php
$to = "contact@healthtechcommunity.eu";
$redirect = "https://healthtechcommunity.eu";

$form = isset($_POST['_form']) ? $_POST['_form'] : '';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: $redirect");
    exit;
}

if ($form === 'newsletter') {
    $email = filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL);
    if (!$email) {
        header("Location: $redirect?status=error");
        exit;
    }
    $subject = "New Newsletter Subscriber";
    $body = "New newsletter subscription:\n\nEmail: $email\n";

} elseif ($form === 'contact') {
    $name = htmlspecialchars($_POST['name'] ?? '');
    $email = filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL);
    $company = htmlspecialchars($_POST['company'] ?? '');
    $interest = htmlspecialchars($_POST['interest'] ?? '');
    $message = htmlspecialchars($_POST['message'] ?? '');

    if (!$email || !$name) {
        header("Location: contact.html?status=error");
        exit;
    }

    $subject = "New Contact: $interest - $name";
    $body  = "Name: $name\n";
    $body .= "Email: $email\n";
    $body .= "Company: $company\n";
    $body .= "Interested in: $interest\n";
    $body .= "Message:\n$message\n";

} else {
    header("Location: $redirect");
    exit;
}

$headers = "From: noreply@healthtechcommunity.eu\r\n";
$headers .= "Reply-To: $email\r\n";
$headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

$sent = mail($to, $subject, $body, $headers);

if ($form === 'contact') {
    header("Location: contact.html?status=" . ($sent ? "success" : "error"));
} else {
    header("Location: $redirect?status=" . ($sent ? "subscribed" : "error"));
}
exit;
