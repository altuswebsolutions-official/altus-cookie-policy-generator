<?php
/**
 * Plugin Name: Altus Cookie Policy Generator
 * Description: Free UK-style cookie policy generator. Drafts a cookie policy from a short questionnaire — copy or download on screen. This open-source version contains NO lead capture, NO email sending, and NO data storage. Draft template only, NOT legal advice.
 * Version: 1.0.0
 * Author: Altus Web Solutions
 * Text Domain: altus-cookie-policy-generator
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Requires at least: 5.8
 * Requires PHP: 7.4
 */

if (!defined('ABSPATH')) exit;

define('ACPG_VERSION', '1.0.0');
define('ACPG_PATH', plugin_dir_path(__FILE__));
define('ACPG_URL', plugin_dir_url(__FILE__));


/* Cookie categories — must match assets/cookie-builder.js CATEGORIES. */
function acpg_categories() {
    return array(
        array('key' => 'necessary', 'label' => 'Strictly necessary cookies',
              'desc' => 'Keep the site working — security, load balancing, remembering cookie choices.'),
        array('key' => 'functional', 'label' => 'Functional / preference cookies',
              'desc' => 'Remember choices like language or region for the next visit.'),
        array('key' => 'analytics', 'label' => 'Analytics / performance cookies',
              'desc' => 'Measure visits and traffic (e.g. Google Analytics) to improve the site.'),
        array('key' => 'marketing', 'label' => 'Marketing / advertising cookies',
              'desc' => 'Measure ad campaigns and show relevant ads on other sites.'),
        array('key' => 'embedded', 'label' => 'Embedded third-party content',
              'desc' => 'YouTube videos, maps or social feeds embedded in pages.'),
    );
}

/* ------------------------------------------------------------------ */
/* Frontend assets (only loaded when the shortcode is present)        */
/* ------------------------------------------------------------------ */
add_action('wp_enqueue_scripts', 'acpg_enqueue');
function acpg_enqueue() {
    if (is_admin()) return;
    global $post;
    if (!($post instanceof WP_Post) || !has_shortcode($post->post_content, 'altus_cookie_policy_generator')) return;

    wp_enqueue_style('acpg-gen', ACPG_URL . 'assets/generator.css', array(), ACPG_VERSION);
    wp_enqueue_script('acpg-builder', ACPG_URL . 'assets/cookie-builder.js', array(), ACPG_VERSION, true);
    wp_enqueue_script('acpg-gen', ACPG_URL . 'assets/generator.js', array('acpg-builder'), ACPG_VERSION, true);
}

/* ------------------------------------------------------------------ */
/* Shortcode: renders the generator UI                                */
/* ------------------------------------------------------------------ */
add_shortcode('altus_cookie_policy_generator', 'acpg_shortcode');
function acpg_shortcode() {
    ob_start();
    ?>
    <div id="acpg-root" class="acpg-root">
        <div class="acpg-progress"><div id="acpg-progress-bar"></div></div>
        <div class="acpg-dots"><span class="acpg-dot active"></span><span class="acpg-dot"></span><span class="acpg-dot"></span></div>

        <!-- Step 1: about the business -->
        <div class="acpg-card" id="acpg-s1">
            <div class="acpg-badge">Free tool</div>
            <h2 class="acpg-title">About your business</h2>
            <p class="acpg-sub">First, the basics — these head up your policy so visitors know whose cookies they're reading about.</p>
            <div class="acpg-field">
                <label class="acpg-label" for="acpg-name">Business name</label>
                <input type="text" id="acpg-name" class="acpg-input" placeholder="e.g. Springfield Plumbing Ltd" autocomplete="organization">
                <p class="acpg-error" id="acpg-e-name" role="alert" hidden></p>
            </div>
            <div class="acpg-field">
                <label class="acpg-label" for="acpg-url">Website address</label>
                <input type="text" id="acpg-url" class="acpg-input" placeholder="e.g. example.co.uk" autocomplete="url" spellcheck="false">
                <p class="acpg-error" id="acpg-e-url" role="alert" hidden></p>
            </div>
            <div class="acpg-field">
                <label class="acpg-label" for="acpg-email">Contact email <span class="acpg-hint" style="display:inline;font-weight:400">— shown in the policy for cookie questions</span></label>
                <input type="email" id="acpg-email" class="acpg-input" placeholder="e.g. hello@example.co.uk" autocomplete="email">
                <p class="acpg-error" id="acpg-e-email" role="alert" hidden></p>
            </div>
            <div class="acpg-btn-row">
                <button type="button" class="acpg-btn" id="acpg-to-s2">Continue →</button>
            </div>
        </div>

        <!-- Step 2: cookies used + consent -->
        <div class="acpg-card" id="acpg-s2" hidden>
            <div class="acpg-badge">Free tool</div>
            <h2 class="acpg-title">What cookies does your site use?</h2>
            <p class="acpg-sub">Tick everything that applies. The policy will only describe what you tick — nothing invented.</p>
            <div class="acpg-check-grid">
                <?php foreach (acpg_categories() as $i => $cat) : ?>
                <label class="acpg-check-card" for="acpg-cat-<?php echo esc_attr($cat['key']); ?>">
                    <input type="checkbox" id="acpg-cat-<?php echo esc_attr($cat['key']); ?>"<?php checked($cat['key'] === 'necessary'); ?>>
                    <span><strong><?php echo esc_html($cat['label']); ?></strong><span><?php echo esc_html($cat['desc']); ?></span></span>
                </label>
                <?php endforeach; ?>
            </div>
            <p class="acpg-error" id="acpg-e-cats" role="alert" hidden></p>

            <div class="acpg-field" style="margin-top:18px">
                <label class="acpg-label" for="acpg-other">Any other named services that set cookies? <span class="acpg-hint" style="display:inline;font-weight:400">(optional)</span></label>
                <input type="text" id="acpg-other" class="acpg-input" placeholder="e.g. our booking system, live chat widget">
                <p class="acpg-hint">Named honestly in the third-party section.</p>
            </div>

            <div class="acpg-field">
                <span class="acpg-label">Do you show visitors a cookie consent banner or use a consent tool?</span>
                <div class="acpg-radio-row">
                    <label class="acpg-radio-card"><input type="radio" name="acpg-consent" value="yes"> Yes</label>
                    <label class="acpg-radio-card"><input type="radio" name="acpg-consent" value="no"> No</label>
                </div>
                <p class="acpg-error" id="acpg-e-consent" role="alert" hidden></p>
            </div>
            <div class="acpg-field">
                <label class="acpg-label" for="acpg-consent-name">Consent tool name <span class="acpg-hint" style="display:inline;font-weight:400">(optional — e.g. CookieYes, Complianz, our own banner)</span></label>
                <input type="text" id="acpg-consent-name" class="acpg-input" placeholder="e.g. CookieYes">
            </div>

            <div class="acpg-btn-row">
                <button type="button" class="acpg-btn acpg-btn-secondary" id="acpg-back-s1">← Back</button>
                <button type="button" class="acpg-btn" id="acpg-generate">Generate my cookie policy →</button>
            </div>
        </div>

        <!-- Step 3: result -->
        <div class="acpg-card" id="acpg-s3" hidden>
            <div class="acpg-badge">Your cookie policy is ready</div>
            <h2 class="acpg-title">Review it, then use it</h2>
            <p class="acpg-sub">Read it through and check every section matches reality. Then copy it onto a dedicated cookie policy page linked from your footer.</p>

            <div class="acpg-preview" id="acpg-preview" aria-live="polite"></div>

            <div class="acpg-actions">
                <button type="button" class="acpg-btn" id="acpg-copy">Copy policy text</button>
                <button type="button" class="acpg-btn acpg-btn-secondary" id="acpg-dl-txt">Download .txt</button>
                <button type="button" class="acpg-btn acpg-btn-secondary" id="acpg-dl-html">Download .html</button>
                <button type="button" class="acpg-btn acpg-btn-secondary" id="acpg-start-over">← Start over</button>
            </div>

            <p class="acpg-fineprint">Your answers are processed entirely in your browser — nothing is sent to or stored on any server. This generator produces a draft template, not legal advice.</p>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

