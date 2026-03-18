<?php
/**
 * ============================================
 * CONTACT PAGE
 * ============================================
 */

require_once 'config/config.php';

// Page metadata
$page_title = 'Contact Us';
$meta_description = 'Get in touch with the Vintage Craft team. We are here to help with your orders, questions, and artisan inquiries.';

$success_msg = '';
$error_msg = '';

// Handle form submission (simplified)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_contact'])) {
    // In a real app, you'd send an email here
    $success_msg = "Thank you for your message! Our team will get back to you within 24-48 hours.";
}

// Include header
include 'includes/header.php';
?>

<!-- ============================================
     HEADER SECTION
     ============================================ -->
<section style="background: var(--bg-tertiary); padding: 4rem 0;">
    <div class="container text-center">
        <h1 style="font-size: 3rem; margin-bottom: 1rem;">Get in Touch</h1>
        <p style="color: var(--text-secondary); max-width: 600px; margin: 0 auto; font-size: 1.125rem;">
            Have a question about a product, your order, or just want to say hello? Our team is always here for a chat.
        </p>
    </div>
</section>

<!-- ============================================
     CONTACT CONTENT
     ============================================ -->
<section class="section">
    <div class="container">
        <div class="row">
            <!-- Contact Info -->
            <div class="col-4">
                <div style="background: var(--bg-tertiary); padding: 3rem; border-radius: 20px; height: 100%;">
                    <h2 style="margin-bottom: 2rem;">Contact Details</h2>
                    
                    <div style="display: flex; gap: 1.5rem; margin-bottom: 2rem;">
                        <div style="width: 50px; height: 50px; background: white; border-radius: 12px; display: flex; align-items: center; justify-content: center; color: var(--primary-color); flex-shrink: 0; box-shadow: var(--shadow-sm);">
                            <i class="fas fa-map-marker-alt"></i>
                        </div>
                        <div>
                            <h4 style="margin-bottom: 0.5rem;">Our Studio</h4>
                            <p style="color: var(--text-secondary); margin: 0; line-height: 1.6;">
                                Karachi, Pakistan
                            </p>
                        </div>
                    </div>

                    <div style="display: flex; gap: 1.5rem; margin-bottom: 3rem;">
                        <div style="width: 50px; height: 50px; background: white; border-radius: 12px; display: flex; align-items: center; justify-content: center; color: var(--primary-color); flex-shrink: 0; box-shadow: var(--shadow-sm);">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <div>
                            <h4 style="margin-bottom: 0.5rem;">Email Support</h4>
                            <p style="color: var(--text-secondary); margin: 0;"><?php echo SITE_EMAIL; ?></p>
                            <p style="color: var(--text-muted); font-size: 0.875rem; margin: 0;">Replies in 24 hours</p>
                        </div>
                    </div>

                    <h4 style="margin-bottom: 1.5rem;">Follow Us</h4>
                    <div style="display: flex; gap: 1rem;">
                        <a href="mailto:<?php echo SITE_EMAIL; ?>" style="width: 40px; height: 40px; border-radius: 50%; background: white; display: flex; align-items: center; justify-content: center; color: var(--text-primary); transition: all 0.3s;" title="Email Us">
                            <i class="fas fa-envelope"></i>
                        </a>
                        <a href="<?php echo FACEBOOK_URL; ?>" target="_blank" rel="noopener" style="width: 40px; height: 40px; border-radius: 50%; background: white; display: flex; align-items: center; justify-content: center; color: var(--text-primary); transition: all 0.3s;" title="Facebook">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                        <a href="<?php echo INSTAGRAM_URL; ?>" target="_blank" rel="noopener" style="width: 40px; height: 40px; border-radius: 50%; background: white; display: flex; align-items: center; justify-content: center; color: var(--text-primary); transition: all 0.3s;" title="Instagram">
                            <i class="fab fa-instagram"></i>
                        </a>
                        <a href="<?php echo TIKTOK_URL; ?>" target="_blank" rel="noopener" style="width: 40px; height: 40px; border-radius: 50%; background: white; display: flex; align-items: center; justify-content: center; color: var(--text-primary); transition: all 0.3s;" title="TikTok">
                            <i class="fab fa-tiktok"></i>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Contact Form -->
            <div class="col-8">
                <div style="padding-left: 4rem;">
                    <?php if ($success_msg): ?>
                        <div class="alert alert-success" style="margin-bottom: 2rem;">
                            <i class="fas fa-check-circle"></i> <?php echo $success_msg; ?>
                        </div>
                    <?php endif; ?>

                    <h2 style="margin-bottom: 3rem;">Send a Message</h2>
                    
                    <form method="POST" action="">
                        <div class="row">
                            <div class="col-6">
                                <div class="form-group">
                                    <label class="form-label">Full Name</label>
                                    <input type="text" name="name" class="form-control" placeholder="e.g. Jane Doe" required>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-group">
                                    <label class="form-label">Email Address</label>
                                    <input type="email" name="email" class="form-control" placeholder="e.g. jane@example.com" required>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Subject</label>
                            <select name="subject" class="form-control" required>
                                <option value="">Select a reason</option>
                                <option value="Order Status">Order Status</option>
                                <option value="Product Inquiry">Product Inquiry</option>
                                <option value="Returns & Refunds">Returns & Refunds</option>
                                <option value="Artisan Application">Artisan Application</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Your Message</label>
                            <textarea name="message" class="form-control" rows="6" placeholder="How can we help you today?" required></textarea>
                        </div>

                        <button type="submit" name="submit_contact" class="btn btn-primary btn-lg" style="padding: 1rem 3rem;">
                            Send Message <i class="fas fa-paper-plane" style="margin-left: 0.5rem;"></i>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ============================================
     MAP PLACEHOLDER
     ============================================ -->
<section style="height: 450px; background: #eee; position: relative; overflow: hidden;">
    <!-- Replace with real Google Map iframe if needed -->
    <div style="position: absolute; inset: 0; background: url('https://images.unsplash.com/photo-1524661135-423995f22d0b?q=80&w=2074&auto=format&fit=crop'); background-size: cover; background-position: center; filter: grayscale(1) opacity(0.5);"></div>
    <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); text-align: center;">
        <div style="width: 60px; height: 60px; background: var(--primary-color); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; margin: 0 auto 1rem; box-shadow: 0 0 20px rgba(139, 111, 71, 0.5); border: 4px solid white;">
            <i class="fas fa-map-pin" style="font-size: 1.5rem;"></i>
        </div>
        <div style="background: white; padding: 1rem 2rem; border-radius: 8px; box-shadow: var(--shadow-md);">
            <h4 style="margin: 0; font-family: var(--font-primary);">Our Design Studio</h4>
            <p style="margin: 0; font-size: 0.875rem; color: var(--text-muted);">Karachi, Pakistan</p>
        </div>
    </div>
</section>

<?php
// Include footer
include 'includes/footer.php';
?>
