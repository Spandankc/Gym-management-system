<?php

function fitness_contact_submit(array $input, callable $mailFactory): array
{
    $data = [];
    foreach (['fullname', 'contact', 'email', 'message'] as $field) {
        $data[$field] = is_string($input[$field] ?? null) ? trim($input[$field]) : '';
    }
    $errors = [];
    foreach (['fullname' => 100, 'contact' => 30, 'email' => 254, 'message' => 5000] as $field => $limit) {
        if ($data[$field] === '') {
            $errors[] = ucfirst($field === 'fullname' ? 'full name' : $field) . ' is required.';
        } elseif (strlen($data[$field]) > $limit) {
            $errors[] = ucfirst($field) . ' is too long.';
        }
    }
    if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    }
    if ($errors) {
        return ['success' => false, 'message' => implode(' ', $errors), 'data' => $data];
    }
    try {
        $mail = $mailFactory();
        // Gmail sends from its authenticated account; replies go to the visitor.
        $mail->setFrom($mail->Username, 'Fitness Hub');
        $mail->addReplyTo($data['email'], $data['fullname']);
        $mail->isHTML(true);
        $mail->Subject = 'Fitness Hub Contact Form Submission';
        $safe = array_map(static function ($value) {
            return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
        }, $data);
        $mail->Body = '<h2>Contact Form Submission</h2>'
            . '<p><strong>Fullname:</strong> ' . $safe['fullname'] . '</p>'
            . '<p><strong>Contact:</strong> ' . $safe['contact'] . '</p>'
            . '<p><strong>Email:</strong> ' . $safe['email'] . '</p>'
            . '<p><strong>Message:</strong><br>' . nl2br($safe['message']) . '</p>';
        $mail->AltBody = "Fullname: {$data['fullname']}\nContact: {$data['contact']}\nEmail: {$data['email']}\n\n{$data['message']}";
        if (!$mail->send()) {
            throw new RuntimeException('Mail delivery failed.');
        }
        return ['success' => true, 'message' => 'Message sent successfully. Thank you for contacting Fitness Hub.', 'data' => []];
    } catch (Throwable $error) {
        error_log('Fitness Hub contact form: ' . $error->getMessage());
        return ['success' => false, 'message' => 'Your message could not be sent. Please try again later or contact us by phone.', 'data' => $data];
    }
}
