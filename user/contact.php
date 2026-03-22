<?php
session_start();
include('../includes/db.php');

$contactSuccess = false;

if (isset($_POST['contact_submit'])) {
    // Ensure the table exists
    mysqli_query(
        $conn,
        "CREATE TABLE IF NOT EXISTS contact_messages (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(191) NOT NULL,
            email VARCHAR(191) NOT NULL,
            subject VARCHAR(255) NOT NULL,
            message TEXT NOT NULL,
            status VARCHAR(32) NOT NULL DEFAULT 'New',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
    );

    // Add status column if it's missing (safe to run on every load)
    mysqli_query(
        $conn,
        "ALTER TABLE contact_messages ADD COLUMN IF NOT EXISTS status VARCHAR(32) NOT NULL DEFAULT 'New'"
    );

    $name = mysqli_real_escape_string($conn, trim($_POST['name']));
    $email = mysqli_real_escape_string($conn, trim($_POST['email']));
    $subject = mysqli_real_escape_string($conn, trim($_POST['subject']));
    $message = mysqli_real_escape_string($conn, trim($_POST['message']));

    mysqli_query(
        $conn,
        "INSERT INTO contact_messages (name, email, subject, message, status)
         VALUES ('$name', '$email', '$subject', '$message', 'New')"
    );

    $contactSuccess = true;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Contact Us</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        :root {
            --accent: #37b24d;
            --dark: #0c0f1a;
        }

        body {
            background: radial-gradient(circle at top, rgba(55, 178, 77, 0.2), transparent 55%),
                radial-gradient(circle at bottom, rgba(48, 126, 214, 0.2), transparent 55%),
                #0f172a;
            min-height: 100vh;
            color: #f8fafc;
        }

        .fade-in-up {
            animation: fadeInUp 0.7s ease both;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(18px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .glass {
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(14px);
            border-radius: 16px;
        }

        .form-control:focus {
            border-color: rgba(55, 178, 77, 0.9);
            box-shadow: 0 0 0 0.2rem rgba(55, 178, 77, 0.25);
        }

        .btn-accent {
            background: linear-gradient(135deg, #3ddc97, #1e8fea);
            border: none;
            box-shadow: 0 12px 28px rgba(0, 0, 0, 0.25);
            transition: transform 0.18s ease, box-shadow 0.18s ease;
        }

        .btn-accent:hover {
            transform: translateY(-2px);
            box-shadow: 0 18px 32px rgba(0, 0, 0, 0.35);
        }

        .contact-icon {
            width: 45px;
            height: 45px;
            display: grid;
            place-items: center;
            background: rgba(255, 255, 255, 0.12);
            border-radius: 14px;
            margin-right: 12px;
            flex-shrink: 0;
        }

        .contact-info li {
            border-radius: 14px;
            padding: 14px;
            transition: transform 0.2s ease, background 0.2s ease;
        }

        .contact-info li:hover {
            transform: translateX(6px);
            background: rgba(255, 255, 255, 0.12);
        }

        .small-muted {
            color: rgba(248, 250, 252, 0.65);
        }

        .link-underline {
            position: relative;
            color: #38bdf8;
            text-decoration: none;
        }

        .link-underline::after {
            content: "";
            position: absolute;
            left: 0;
            bottom: -2px;
            width: 0;
            height: 2px;
            background: linear-gradient(90deg, #3ddc97, #1e8fea);
            transition: width 0.25s ease;
        }

        .link-underline:hover::after {
            width: 100%;
        }
    </style>
</head>

<body>

    <div class="container py-5">
        <div class="text-center mb-5 fade-in-up">
            <h1 class="fw-bold">Get in Touch</h1>
            <p class="small-muted mb-0">Have questions? We’re here to help. Send us a message and we’ll get back ASAP.</p>
        </div>

        <div class="row gy-4 justify-content-center">
            <div class="col-lg-5 fade-in-up">
                <div class="glass p-4 shadow-sm">
                    <h5 class="mb-3">📍 Contact Info</h5>
                    <ul class="list-unstyled contact-info">
                        <li class="d-flex align-items-center mb-3">
                            <div class="contact-icon">
                                <span class="fs-5">📧</span>
                            </div>
                            <div>
                                <div class="fw-semibold">Email</div>
                                <div class="small-muted">support@fruitshop.com</div>
                            </div>
                        </li>
                        <li class="d-flex align-items-center mb-3">
                            <div class="contact-icon">
                                <span class="fs-5">📞</span>
                            </div>
                            <div>
                                <div class="fw-semibold">Phone</div>
                                <div class="small-muted">+91 98765 43210</div>
                            </div>
                        </li>
                        <li class="d-flex align-items-center">
                            <div class="contact-icon">
                                <span class="fs-5">📍</span>
                            </div>
                            <div>
                                <div class="fw-semibold">Address</div>
                                <div class="small-muted">Madurai, Tamil Nadu</div>
                            </div>
                        </li>
                    </ul>

                    <p class="mt-4 small-muted mb-2">Follow us</p>
                    <div class="d-flex gap-2">
                        <a href="#" class="btn btn-outline-light btn-sm rounded-pill">Twitter</a>
                        <a href="#" class="btn btn-outline-light btn-sm rounded-pill">Instagram</a>
                        <a href="#" class="btn btn-outline-light btn-sm rounded-pill">LinkedIn</a>
                    </div>
                </div>
            </div>

            <div class="col-lg-6 fade-in-up">
                <div class="glass p-4 shadow-sm">
                    <h5 class="mb-3">Send us a message</h5>

                    <form id="contactForm" class="row g-3" method="POST">
                        <div class="col-md-6">
                            <label class="form-label" for="name">Name</label>
                            <input type="text" name="name" class="form-control" id="name" required placeholder="Your name">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="email">Email</label>
                            <input type="email" name="email" class="form-control" id="email" required placeholder="you@example.com">
                        </div>
                        <div class="col-12">
                            <label class="form-label" for="subject">Subject</label>
                            <input type="text" name="subject" class="form-control" id="subject" required placeholder="What’s this about?">
                        </div>
                        <div class="col-12">
                            <label class="form-label" for="message">Message</label>
                            <textarea name="message" class="form-control" id="message" rows="4" required placeholder="Write your message..."></textarea>
                        </div>
                        <div class="col-12 text-end">
                            <button type="submit" name="contact_submit" class="btn btn-accent px-4">Send message</button>
                        </div>
                    </form>

                    <div id="contactSuccess" class="alert alert-success mt-4 " role="alert" <?php echo $contactSuccess ? '' : 'style="display:none;"'; ?> >
                        ✅ Thank you! Your message has been sent. We'll reply within 24 hours.
                    </div>
                </div>
            </div>
        </div>
    </div>

</body>

</html>
