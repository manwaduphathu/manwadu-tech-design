<?php
require_once 'mailer.php';

if (sendResetEmail('youremail@example.com', 'https://example.com/reset')) {
    echo "Email sent!";
} else {
    echo "Failed to send email.";
}
