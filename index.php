<?php
session_start();
use PHPMailer\PHPMailer\PHPMailer;
require_once __DIR__ . '/includes/contact.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = fitness_contact_submit($_POST, static function () {
        require_once __DIR__ . '/vendor/autoload.php';
        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'spandankc41@gmail.com';
        $mail->Password = 'oihs wwzp iorv lazt';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;
        $mail->Timeout = 10;
        $mail->Timelimit = 15;
        $mail->addAddress('spandankc41@gmail.com');
        return $mail;
    });
    $_SESSION['contact_result'] = $result;
    header('Location: index.php#contact', true, 303);
    exit;
}
$contactResult = $_SESSION['contact_result'] ?? null;
unset($_SESSION['contact_result']);
$contactData = $contactResult['data'] ?? [];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="index.css">
    <script src="https://kit.fontawesome.com/426c1a4028.js" crossorigin="anonymous"></script>
    <title>Fitness Hub</title>
</head>

<body>
    <nav>
        <a href="index.php"><img src="images/logo1.png" alt="Fitness Hub"></a>
        <ul>
            <li><a href="index.php">Home</a></li>
            <li><a href="#services">Services</a></li>
            <li><a href="#contact">Contact</a></li>
            <li class="dropdown"><a href="#account-menu" class="account-toggle" role="button" aria-expanded="false" aria-controls="account-menu">Account</a>
                <ul id="account-menu">
                    <li><a href="customer/login.php">Login</a></li>
                    <li><a href="customer/signup.php">Signin</a></li>
                </ul>
            </li>
        </ul>
    </nav>

    <!-- //home -->
    <section id="home" class="">
        <div class="container slide-in-left">
            <div class="content">
                <h1>Fitness Hub</h1>
                <p>Where hard work meets heart.</p>
                <a href="customer/signup.php">Begin Your Journey</a>
            </div>
            <p class="login_p">Already a Member? <a href="customer/login.php" class="login">Log in</a></p>
        </div>
    </section>

    <!-- //services -->
    <section id="services" class="">
        <div class="services-container slide-in-left">
            <h1>Our Services</h1>
            <p class="services_p">A variety of services to help you achieve your fitness goals.</p>
            <div class="services-content">
                <div class="service-card">
                    <i class="fa-solid fa-person-walking"></i>
                    <h2>Fitness Center</h2>
                    <p>Our fitness center is equipped with the latest machines and weights to help you reach your
                        fitness goals.</p>
                </div>
                <div class="service-card">
                    <i class="fa-solid fa-heart-pulse"></i>
                    <h2>Cardio Zone</h2>
                    <p>Our cardio zone includes state-of-the-art treadmills, bikes, and elliptical machines to keep your
                        heart healthy.</p>
                </div>
                <div class="service-card">
                    <i class="fa-solid fa-bath"></i>
                    <h2>Sauna & Relaxation</h2>
                    <p>Unwind in our sauna after a workout. The perfect way to relax your muscles and clear your mind.
                    </p>
                </div>
                <div class="service-card">
                    <i class="fa-solid fa-dumbbell"></i>
                    <h2>Weight & Strength</h2>
                    <p>Build your strength with our free weights and resistance training equipment.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- //Contact -->
    <section id="contact" class="">
        <div class="contact-container slide-in-left">
            <h1>Contact Us</h1>
            <p class="services_p">Reach out to us to begin your journey</p>
            <?php if ($contactResult): ?>
                <div class="message contact-status <?= $contactResult['success'] ? 'contact-success' : 'contact-error' ?>" role="<?= $contactResult['success'] ? 'status' : 'alert' ?>">
                    <p><?= htmlspecialchars($contactResult['message'], ENT_QUOTES, 'UTF-8') ?></p>
                </div>
            <?php endif; ?>
            <div class="contact-info">
                <div class="contacts">
                    <div class="phone contact-div">
                        <i class="fa-solid fa-phone"></i>
                        <p>+977 9876543210, 44553321</p>
                    </div>
                    <div class="email contact-div">
                        <i class="fa-solid fa-envelope"></i>
                        <p>fitnesshub@gmail.com</p>
                    </div>
                    <div class="map contact-div">
                        <i class="fa-solid fa-location-dot"></i>
                        <p>Bharatpur-12, Chitwan</p>
                    </div>
                </div>
                <div class="contact-form">
                    <form action="index.php#contact" method="POST">
                        <input type="text" placeholder="Fullname" aria-label="Full name" name="fullname" maxlength="100" autocomplete="name" value="<?= htmlspecialchars($contactData['fullname'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required>
                        <input type="tel" placeholder="Contact" aria-label="Contact number" name="contact" maxlength="30" autocomplete="tel" value="<?= htmlspecialchars($contactData['contact'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required>
                        <input type="email" placeholder="Email" aria-label="Email" name="email" maxlength="254" autocomplete="email" value="<?= htmlspecialchars($contactData['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required>
                        <textarea name="message" aria-label="Message" placeholder="Enter your message..." rows="5" cols="50" maxlength="5000" required><?= htmlspecialchars($contactData['message'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
                        <button type="submit">Submit</button>
                    </form>
                </div>
            </div>
        </div>
    </section>


    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const account = document.querySelector('.account-toggle');
            const dropdown = account.closest('.dropdown');
            function setAccountOpen(open) {
                account.setAttribute('aria-expanded', String(open));
                dropdown.classList.toggle('open', open);
            }
            account.addEventListener('click', function (event) {
                event.preventDefault();
                setAccountOpen(account.getAttribute('aria-expanded') !== 'true');
            });
            account.addEventListener('keydown', function (event) {
                if (event.key === ' ') {
                    event.preventDefault();
                    account.click();
                }
            });
            document.addEventListener('click', function (event) {
                if (!dropdown.contains(event.target)) {
                    setAccountOpen(false);
                }
            });
            dropdown.addEventListener('focusout', function (event) {
                if (!dropdown.contains(event.relatedTarget)) {
                    setAccountOpen(false);
                }
            });
            document.addEventListener('keydown', function (event) {
                if (event.key === 'Escape' && account.getAttribute('aria-expanded') === 'true') {
                    setAccountOpen(false);
                    account.focus();
                }
            });
            const sliders = document.querySelectorAll('.slide-in-left');

            const appearOptions = {
                threshold: 0,
                rootMargin: "0px 0px -150px 0px"
            };

            const appearOnScroll = new IntersectionObserver(function (entries, appearOnScroll) {
                entries.forEach(entry => {
                    if (!entry.isIntersecting) {
                        return;
                    } else {
                        entry.target.classList.add('show');
                        appearOnScroll.unobserve(entry.target);
                    }
                });
            }, appearOptions);

            sliders.forEach(slider => {
                appearOnScroll.observe(slider);
            });
        });
    </script>

</body>

</html>
