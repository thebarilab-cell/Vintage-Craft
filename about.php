<?php
/**
 * ============================================
 * ABOUT US PAGE
 * ============================================
 */

require_once 'config/config.php';

// Page metadata
$page_title = 'Our Story';
$meta_description = 'Learn more about Vintage Craft, our mission, and the artisans behind our handcrafted treasures.';

// Include header
include 'includes/header.php';
?>

<!-- ============================================
     HERO SECTION
     ============================================ -->
<section style="background: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.5)), url('https://images.unsplash.com/photo-1459749411177-042180ce673c?q=80&w=2070&auto=format&fit=crop'); background-size: cover; background-position: center; padding: 120px 0; color: white; text-align: center;">
    <div class="container">
        <h1 style="font-size: 4rem; margin-bottom: 1.5rem; color: white;">Crafting Memories</h1>
        <p style="font-size: 1.25rem; max-width: 700px; margin: 0 auto; color: var(--beige);">
            Discover the passion, tradition, and craftsmanship that goes into every piece at <?php echo SITE_NAME; ?>.
        </p>
    </div>
</section>

<!-- ============================================
     OUR STORY SECTION
     ============================================ -->
<section class="section">
    <div class="container">
        <div class="row align-center">
            <div class="col-6">
                <div style="position: relative;">
                    <img src="https://images.unsplash.com/photo-1452860606245-08befc0ff44b?q=80&w=2070&auto=format&fit=crop" 
                         alt="Artisan at work" 
                         style="border-radius: 20px; box-shadow: var(--shadow-lg); width: 100%;">
                </div>
            </div>
            <div class="col-6">
                <span style="color: var(--primary-color); font-weight: 600; text-transform: uppercase; letter-spacing: 2px; font-size: 0.875rem; display: block; margin-bottom: 1rem;">Since 2010</span>
                <h2 style="font-size: 3rem; margin-bottom: 2rem;">The Heart of Handcrafted Art</h2>
                <p style="color: var(--text-secondary); margin-bottom: 1.5rem; font-size: 1.125rem; line-height: 1.8;">
                    Welcome to <?php echo SITE_NAME; ?>, where every object tells a story of time-honored techniques and modern passion. Our journey began in a small workshop with a simple vision: to bring authentic, handmade treasures into modern homes.
                </p>
                <p style="color: var(--text-secondary); margin-bottom: 2rem; font-size: 1.125rem; line-height: 1.8;">
                    We collaborate directly with skilled artisans who pour their soul into every stitch, cut, and glaze. By choosing us, you aren't just buying a product; you're supporting a legacy of craft and a community of creators.
                </p>
                <div style="display: flex; gap: 2rem;">
                    <div>
                        <h4 style="font-size: 1.25rem; margin-bottom: 0.5rem;">Authenticity</h4>
                        <p style="color: var(--text-muted); font-size: 0.875rem;">100% genuine handcrafted materials.</p>
                    </div>
                    <div style="width: 2px; background: var(--border-color);"></div>
                    <div>
                        <h4 style="font-size: 1.25rem; margin-bottom: 0.5rem;">Sustainability</h4>
                        <p style="color: var(--text-muted); font-size: 0.875rem;">Eco-conscious production & packing.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ============================================
     OUR MISSION
     ============================================ -->
<section class="section" style="background: var(--bg-tertiary);">
    <div class="container text-center">
        <h2 style="margin-bottom: 4rem;">What Guides Us</h2>
        <div class="row">
            <div class="col-4">
                <div style="background: white; padding: 3rem 2rem; border-radius: 20px; height: 100%; box-shadow: var(--shadow-sm); transition: transform 0.3s;" onmouseover="this.style.transform='translateY(-10px)'" onmouseout="this.style.transform='translateY(0)'">
                    <i class="fas fa-heart" style="font-size: 3rem; color: var(--accent-color); margin-bottom: 1.5rem;"></i>
                    <h3 style="margin-bottom: 1rem;">Made with Love</h3>
                    <p style="color: var(--text-secondary);">We believe objects have energy. That's why every item in our store is made with intention and care by hands that love what they do.</p>
                </div>
            </div>
            <div class="col-4">
                <div style="background: white; padding: 3rem 2rem; border-radius: 20px; height: 100%; box-shadow: var(--shadow-sm); transition: transform 0.3s;" onmouseover="this.style.transform='translateY(-10px)'" onmouseout="this.style.transform='translateY(0)'">
                    <i class="fas fa-leaf" style="font-size: 3rem; color: var(--success); margin-bottom: 1.5rem;"></i>
                    <h3 style="margin-bottom: 1rem;">Eco-Conscious</h3>
                    <p style="color: var(--text-secondary);">From sourcing recycled clay to using biodegradable packaging, we strive to leave the smallest footprint on our beautiful planet.</p>
                </div>
            </div>
            <div class="col-4">
                <div style="background: white; padding: 3rem 2rem; border-radius: 20px; height: 100%; box-shadow: var(--shadow-sm); transition: transform 0.3s;" onmouseover="this.style.transform='translateY(-10px)'" onmouseout="this.style.transform='translateY(0)'">
                    <i class="fas fa-handshake" style="font-size: 3rem; color: var(--primary-color); margin-bottom: 1.5rem;"></i>
                    <h3 style="margin-bottom: 1rem;">Fair Trade</h3>
                    <p style="color: var(--text-secondary);">Empowering artisans through fair wages and ethical partnerships. We ensure sustainable livelihoods for all our craft partners.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ============================================
     TESTIMONIAL SECTION
     ============================================ -->
<section class="section" style="overflow: hidden;">
    <div class="container">
        <div style="max-width: 800px; margin: 0 auto; text-align: center;">
            <i class="fas fa-quote-left" style="font-size: 4rem; color: var(--border-color); margin-bottom: 2rem;"></i>
            <h2 style="font-family: var(--font-heading); font-style: italic; font-weight: 400; line-height: 1.6; margin-bottom: 2rem;">
                "I bought a set of handmade mugs and they've completely changed my morning ritual. You can feel the texture of the potter's hands on the clay. It's simply beautiful."
            </h2>
            <div style="display: flex; align-items: center; justify-content: center; gap: 1rem;">
                <img src="https://images.unsplash.com/photo-1438761681033-6461ffad8d80?q=80&w=2070&auto=format&fit=crop" style="width: 60px; height: 60px; border-radius: 50%; object-fit: cover;">
                <div style="text-align: left;">
                    <p style="margin: 0; font-weight: 600;">Sarah Johnson</p>
                    <p style="margin: 0; font-size: 0.875rem; color: var(--text-muted);">Verified Customer</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ============================================
     JOIN US SECTION
     ============================================ -->
<section class="section" style="background: var(--primary-color); color: white;">
    <div class="container text-center">
        <h2 style="color: white; font-size: 3rem; margin-bottom: 1.5rem;">Ready to Find Your Treasure?</h2>
        <p style="color: var(--beige); font-size: 1.25rem; margin-bottom: 3rem; max-width: 600px; margin-left: auto; margin-right: auto;">
            Explore our curated collections and bring home a piece of art that lasts a lifetime.
        </p>
        <a href="<?php echo BASE_URL; ?>shop.php" class="btn btn-lg" style="background: white; color: var(--primary-color); padding: 1rem 3rem;">
            Shop Collections
        </a>
    </div>
</section>

<?php
// Include footer
include 'includes/footer.php';
?>
