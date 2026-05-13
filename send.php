<?php
$to       = 'contact@healthtechcommunity.eu';
$redirect = 'https://healthtechcommunity.eu';

$form = $_POST['_form'] ?? '';

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
    $secrets = @include __DIR__ . '/secrets.php';
    if (!is_array($secrets) || empty($secrets['brevo_api_key']) || empty($secrets['brevo_list_id']) || empty($secrets['brevo_doi_template']) || empty($secrets['brevo_doi_redirect'])) {
        header("Location: $redirect?status=error");
        exit;
    }
    $payload = [
        'email'           => $email,
        'includeListIds'  => [(int) $secrets['brevo_list_id']],
        'templateId'      => (int) $secrets['brevo_doi_template'],
        'redirectionUrl'  => $secrets['brevo_doi_redirect'],
    ];
    $ch = curl_init('https://api.brevo.com/v3/contacts/doubleOptinConfirmation');
    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => json_encode($payload),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER     => [
            'accept: application/json',
            'content-type: application/json',
            'api-key: ' . $secrets['brevo_api_key'],
        ],
        CURLOPT_TIMEOUT        => 10,
    ]);
    curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    $ok = ($code >= 200 && $code < 300);
    header('Location: ' . $redirect . '?status=' . ($ok ? 'pending' : 'error'));
    exit;
}

if ($form === 'contact') {
    $name     = htmlspecialchars($_POST['name'] ?? '');
    $email    = filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL);
    $company  = htmlspecialchars($_POST['company'] ?? '');
    $interest = htmlspecialchars($_POST['interest'] ?? '');
    $message  = htmlspecialchars($_POST['message'] ?? '');

    if (!$email || !$name) {
        header('Location: contact.html?status=error');
        exit;
    }

    $subject  = "New Contact: $interest - $name";
    $body     = "Name: $name\nEmail: $email\nCompany: $company\nInterested in: $interest\nMessage:\n$message\n";
    $headers  = "From: noreply@healthtechcommunity.eu\r\n";
    $headers .= "Reply-To: $email\r\n";
    $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

    $sent = mail($to, $subject, $body, $headers);
    header('Location: contact.html?status=' . ($sent ? 'success' : 'error'));
    exit;
}

header("Location: $redirect");
exit;
