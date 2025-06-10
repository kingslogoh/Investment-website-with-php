<?php
include 'database.php';

// Get site name from settings
$siteStmt = $pdo->query("SELECT site_name FROM settings LIMIT 1");
$siteRow = $siteStmt->fetch(PDO::FETCH_ASSOC);
$site_name = $siteRow['site_name'] ?? 'Your Site';

// Fetch footer settings
$stmt = $pdo->query("SELECT * FROM footer_settings LIMIT 1");
$footer = $stmt->fetch(PDO::FETCH_ASSOC);

$newsletter_text = $footer['newsletter_text'] ?? 'Subscribe to our newsletter for updates.';

// Decode JSON safely
$explore_links = json_decode($footer['explore_links'], true) ?: [];
$contact_info = json_decode($footer['contact_info'], true) ?: [];
$popular_posts = json_decode($footer['popular_posts'], true) ?: [];


// Ensure we have valid arrays
if (!is_array($explore_links)) $explore_links = [];
if (!is_array($contact_info)) $contact_info = [];
if (!is_array($popular_posts)) $popular_posts = [];

$social_links = json_decode($footer['social_links'], true);
if (!is_array($social_links)) $social_links = [];
?>

<!-- Footer Start -->
<center>
<div class="container-fluid footer py-5">
    <div class="container py-5">
        <div class="row g-5">
           <!-- <div class="col-md-6 col-lg-6 col-xl-3">
                <h4 class="text-black mb-4">Newsletter</h4>
                <p class="mb-3"><?= htmlspecialchars($newsletter_text); ?></p>
                <input class="form-control rounded-pill w-100 py-3" type="text" placeholder="Enter your email">
                <button class="btn btn-primary rounded-pill mt-2">Subcribe</button>
            </div>-->
            
            <div class="col-md-6 col-lg-6 col-xl-3">
                <h4 class="text-primary mb-4">Explore</h4>
                <?php foreach ($explore_links as $link): ?>
                    <a href="<?= htmlspecialchars($link['url']); ?>"><i class="fas fa-angle-right me-2"></i> <?= htmlspecialchars($link['text']); ?></a><br/>
                <?php endforeach; ?>
            </div>

            <div class="col-md-6 col-lg-6 col-xl-3">
                <h4 class="text-primary mb-4">Contact Info</h4>
                <?php foreach ($contact_info as $info): ?>
                    <a href="<?= htmlspecialchars($info['url']); ?>"><i class="<?= htmlspecialchars($info['icon']); ?> me-2"></i> <?= htmlspecialchars($info['text']); ?></a><br/>
                <?php endforeach; ?>
            </div>

            <!-- Social Links -->
            <div class="col-md-6 col-lg-6 col-xl-3">
                <h4 class="text-primary mb-4">Follow Us</h4>
                 
                    <?php foreach ($social_links as $item): ?>
                  
                            <a href="<?= htmlspecialchars($item['url']) ?>" target="_blank" class="text-white">
                                <i class="<?= htmlspecialchars($item['icon']) ?>"></i>
                            </a>
                    
                    <?php endforeach; ?>
        </div>
            <div class="col-md-6 col-lg-6 col-xl-3">
                <h4 class="text-primary mb-4">Popular Posts</h4>
                <?php foreach ($popular_posts as $post): ?>
                    <p class="text-primary"><?= htmlspecialchars($post['category']); ?></p>
                    <a href="<?= htmlspecialchars($post['url']); ?>" class="text-body"><?= htmlspecialchars($post['title']); ?></a><br/>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div></center>

<!-- Copyright -->
<div class="bg-dark text-white text-center py-3">
    &copy; <?= date('Y') ?> <?= htmlspecialchars($site_name) ?>. All Rights Reserved.
</div>

<!-- Footer End -->
   <!-- JavaScript Libraries -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="lib/wow/wow.min.js"></script>
    <script src="lib/easing/easing.min.js"></script>
    <script src="lib/waypoints/waypoints.min.js"></script>
    <script src="lib/counterup/counterup.min.js"></script>
    <script src="lib/owlcarousel/owl.carousel.min.js"></script>
    <script src="lib/lightbox/js/lightbox.min.js"></script>
    

    <!-- Template Javascript -->
    <script src="js/main.js"></script>
    </body>

</html>