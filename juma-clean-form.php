<?php
/**
 * Plugin Name: Juma Clean Form
 * Plugin URI: https://yourwebsite.com
 * Description: Stegvis bestillingsskjema for Juma Clean søppelbøttevask med dynamisk prising og reCAPTCHA.
 * Version: 1.0.0
 * Author: Your Name
 * License: GPL v2 or later
 * Text Domain: juma-clean-form
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('JUMA_CLEAN_PLUGIN_URL', plugin_dir_url(__FILE__));
define('JUMA_CLEAN_PLUGIN_PATH', plugin_dir_path(__FILE__));

class JumaCleanForm {
    
    public function __construct() {
        add_action('init', array($this, 'init'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_action('wp_ajax_juma_form_submit', array($this, 'handle_form_submission'));
        add_action('wp_ajax_nopriv_juma_form_submit', array($this, 'handle_form_submission'));
        add_action('wp_ajax_juma_get_prices', array($this, 'get_prices_ajax'));
        add_action('wp_ajax_nopriv_juma_get_prices', array($this, 'get_prices_ajax'));
        add_shortcode('juma_clean_form', array($this, 'render_form'));
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'admin_init'));
        add_filter('manage_juma_orders_posts_columns', array($this, 'set_custom_columns'));
        add_action('manage_juma_orders_posts_custom_column', array($this, 'custom_column_content'), 10, 2);
        add_action('add_meta_boxes', array($this, 'add_order_meta_boxes'));
    }
    
    public function init() {
        // Create custom post type for orders
        $this->create_post_type();
    }
    
    public function create_post_type() {
        register_post_type('juma_orders',
            array(
                'labels' => array(
                    'name' => 'Juma Bestillinger',
                    'singular_name' => 'Bestilling',
                    'add_new' => 'Ny bestilling',
                    'add_new_item' => 'Legg til ny bestilling',
                    'edit_item' => 'Rediger bestilling',
                    'new_item' => 'Ny bestilling',
                    'view_item' => 'Vis bestilling',
                    'search_items' => 'Søk bestillinger',
                    'not_found' => 'Ingen bestillinger funnet',
                    'not_found_in_trash' => 'Ingen bestillinger i papirkurven'
                ),
                'public' => false,
                'show_ui' => true,
                'show_in_menu' => true,
                'capability_type' => 'post',
                'supports' => array('title', 'custom-fields'),
                'menu_icon' => 'dashicons-cart'
            )
        );
    }
    
    public function enqueue_scripts() {
        // Enqueue CSS
        wp_enqueue_style('juma-clean-form', JUMA_CLEAN_PLUGIN_URL . 'assets/style.css', array(), '1.0.0');
        
        // Enqueue JavaScript
        wp_enqueue_script('juma-clean-form', JUMA_CLEAN_PLUGIN_URL . 'assets/script.js', array('jquery'), '1.0.0', true);
        
        // Localize script for AJAX
        wp_localize_script('juma-clean-form', 'juma_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('juma_form_nonce')
        ));
        
        // Enqueue reCAPTCHA
        wp_enqueue_script('google-recaptcha', 'https://www.google.com/recaptcha/api.js', array(), null, true);
    }
    
    public function render_form($atts) {
        $atts = shortcode_atts(array(
            'show_title' => 'true'
        ), $atts);
        
        ob_start();
        ?>
        <div class="juma-clean-form-container">
            <?php if ($atts['show_title'] === 'true'): ?>
            <header class="juma-form-header">
                <h1>Juma Clean</h1>
                <p>Bestill vask av søppelbøtter</p>
            </header>
            <?php endif; ?>
            
            <!-- Fremdrift -->
            <nav aria-label="Fremdrift" class="juma-progress">
                <div class="step active" id="stepHead1" aria-current="step">
                    <div class="step-circle">1</div>
                    <div class="step-label">Postnummer</div>
                    <div class="step-line" aria-hidden="true"></div>
                </div>
                <div class="step" id="stepHead2">
                    <div class="step-circle">2</div>
                    <div class="step-label">Kundetype</div>
                    <div class="step-line" aria-hidden="true"></div>
                </div>
                <div class="step" id="stepHead3">
                    <div class="step-circle">3</div>
                    <div class="step-label">Søppelbøtter</div>
                    <div class="step-line" aria-hidden="true"></div>
                </div>
                <div class="step" id="stepHead4">
                    <div class="step-circle">4</div>
                    <div class="step-label">Vaskeordning</div>
                    <div class="step-line" aria-hidden="true"></div>
                </div>
                <div class="step" id="stepHead5">
                    <div class="step-circle">5</div>
                    <div class="step-label">Kontakt</div>
                </div>
            </nav>

            <main class="juma-form-main">
                <form id="juma-order-form" novalidate>
                    <!-- Steg 1: Postnummer -->
                    <section id="step1" class="juma-form-step">
                        <h2 class="sr-only">Postnummer</h2>
                        <fieldset>
                            <legend>Postnummer</legend>
                            <div class="form-group">
                                <label for="postnummer">Skriv inn postnummer</label>
                                <input type="text" id="postnummer" maxlength="4" pattern="[0-9]{4}" placeholder="0000" required>
                                <p class="error-msg hidden" data-error-for="postnummer">Postnummer må være 4 siffer</p>
                            </div>
                        </fieldset>

                        <div class="btn-row" style="margin-top: 1.5rem;">
                            <button type="button" class="btn btn-secondary" onclick="jumaGoToStep(1)">Tilbake</button>
                            <button type="button" class="btn" onclick="jumaGoToStep(3)">Fortsett til søppelbøtter</button>
                        </div>
                    </section>

                    <!-- Steg 2: Kundetype -->
                    <section id="step2" class="juma-form-step hidden">
                        <h2 class="sr-only">Kundetype</h2>
                        <fieldset>
                            <legend>Velg kundetype</legend>
                            <div class="form-group">
                                <label for="customerType">Er du privatperson eller foretak?</label>
                                <select id="customerType" required>
                                    <option value="">-- Velg kundetype --</option>
                                    <option value="privatperson">Privatperson</option>
                                    <option value="foretak">Foretak</option>
                                </select>
                            </div>
                        </fieldset>
                    </section>

                    <!-- Steg 3: Velg søppelbøtter -->
                    <section id="step3" class="juma-form-step hidden">
                        <h2 class="sr-only">Velg søppelbøtter</h2>
                        <fieldset>
                            <legend>Hvilke søppelbøtter skal vaskes?</legend>
                            
                            <!-- Mat -->
                            <div class="bin-selection">
                                <div class="bin-checkbox">
                                    <input type="checkbox" id="bin_mat" class="bin-check">
                                    <label for="bin_mat"><span class="color-dot" style="background: var(--mat);"></span> Matavfall</label>
                                </div>
                                <div id="bin_mat_counts" class="bin-counts hidden">
                                    <div class="grid grid-2">
                                        <div class="form-group">
                                            <label for="mat_liten">Antall små søppelbøtter</label>
                                            <input type="number" id="mat_liten" min="0" max="20" value="0">
                                        </div>
                                        <div class="form-group">
                                            <label for="mat_stor">Antall store søppelbøtter</label>
                                            <input type="number" id="mat_stor" min="0" max="20" value="0">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Rest -->
                            <div class="bin-selection">
                                <div class="bin-checkbox">
                                    <input type="checkbox" id="bin_rest" class="bin-check">
                                    <label for="bin_rest"><span class="color-dot" style="background: var(--rest);"></span> Restavfall</label>
                                </div>
                                <div id="bin_rest_counts" class="bin-counts hidden">
                                    <div class="grid grid-2">
                                        <div class="form-group">
                                            <label for="rest_liten">Antall små søppelbøtter</label>
                                            <input type="number" id="rest_liten" min="0" max="20" value="0">
                                        </div>
                                        <div class="form-group">
                                            <label for="rest_stor">Antall store søppelbøtter</label>
                                            <input type="number" id="rest_stor" min="0" max="20" value="0">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Glass -->
                            <div class="bin-selection">
                                <div class="bin-checkbox">
                                    <input type="checkbox" id="bin_glass" class="bin-check">
                                    <label for="bin_glass"><span class="color-dot" style="background: var(--glass);"></span> Glass- og metall</label>
                                </div>
                                <div id="bin_glass_counts" class="bin-counts hidden">
                                    <div class="grid grid-2">
                                        <div class="form-group">
                                            <label for="glass_liten">Antall små søppelbøtter</label>
                                            <input type="number" id="glass_liten" min="0" max="20" value="0">
                                        </div>
                                        <div class="form-group">
                                            <label for="glass_stor">Antall store søppelbøtter</label>
                                            <input type="number" id="glass_stor" min="0" max="20" value="0">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Papir -->
                            <div class="bin-selection">
                                <div class="bin-checkbox">
                                    <input type="checkbox" id="bin_papir" class="bin-check">
                                    <label for="bin_papir"><span class="color-dot" style="background: var(--papir);"></span> Papp- og papir</label>
                                </div>
                                <div id="bin_papir_counts" class="bin-counts hidden">
                                    <div class="grid grid-2">
                                        <div class="form-group">
                                            <label for="papir_liten">Antall små søppelbøtter</label>
                                            <input type="number" id="papir_liten" min="0" max="20" value="0">
                                        </div>
                                        <div class="form-group">
                                            <label for="papir_stor">Antall store søppelbøtter</label>
                                            <input type="number" id="papir_stor" min="0" max="20" value="0">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Plast -->
                            <div class="bin-selection">
                                <div class="bin-checkbox">
                                    <input type="checkbox" id="bin_plast" class="bin-check">
                                    <label for="bin_plast"><span class="color-dot" style="background: var(--plast);"></span> Plastemballasje</label>
                                </div>
                                <div id="bin_plast_counts" class="bin-counts hidden">
                                    <div class="grid grid-2">
                                        <div class="form-group">
                                            <label for="plast_liten">Antall små søppelbøtter</label>
                                            <input type="number" id="plast_liten" min="0" max="20" value="0">
                                        </div>
                                        <div class="form-group">
                                            <label for="plast_stor">Antall store søppelbøtter</label>
                                            <input type="number" id="plast_stor" min="0" max="20" value="0">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </fieldset>
                        <div class="btn-row" style="margin-top: 1.5rem;">
                            <button type="button" class="btn btn-secondary" onclick="jumaGoToStep(2)">Tilbake</button>
                            <button type="button" class="btn" id="continueToStep4" onclick="jumaGoToStep(4)" disabled>Fortsett til vaskeordning</button>
                        </div>
                    </section>

                    <!-- Steg 4: Vaskeordning -->
                    <section id="step4" class="juma-form-step hidden">
                        <h2 class="sr-only">Velg vaskeordning</h2>
                        <fieldset>
                            <legend>Velg vaskeordning</legend>
                            
                            <div class="form-group">
                                <label>
                                    <input type="radio" name="vaskeordning" value="enkeltvask" id="vaskeordning_enkel">
                                    Enkeltvask
                                </label>
                                <p class="muted">Enkel vask av dine søppelbøtter</p>
                            </div>

                            <div class="form-group">
                                <label>
                                    <input type="radio" name="vaskeordning" value="sesongvask" id="vaskeordning_sesong">
                                    Sesongvask
                                </label>
                                <p class="muted">Regelmessig vask gjennom hele sesongen</p>
                            </div>

                            <!-- Sesongvask pakker -->
                            <div id="sesongvask_pakker" class="hidden" style="margin-top: 1.5rem;">
                                <fieldset>
                                    <legend>Velg vaskepakke</legend>
                                    
                                    <div class="form-group">
                                        <label for="pakke_liten">
                                            <input type="radio" name="vaskepakke" value="liten" id="pakke_liten">
                                            Liten vaskepakke <strong>(<span id="pakke_liten_dager">19</span> vaskedager)</strong>
                                        </label>
                                    </div>

                                    <div class="form-group">
                                        <label for="pakke_medium">
                                            <input type="radio" name="vaskepakke" value="medium" id="pakke_medium">
                                            Medium vaskepakke <strong>(<span id="pakke_medium_dager">22</span> vaskedager)</strong>
                                        </label>
                                    </div>

                                    <div class="form-group">
                                        <label for="pakke_stor">
                                            <input type="radio" name="vaskepakke" value="stor" id="pakke_stor">
                                            Stor vaskepakke <strong>(<span id="pakke_stor_dager">26</span> vaskedager)</strong>
                                        </label>
                                    </div>

                                    <div class="info-box" style="margin-top: 1rem; padding: 1rem; background: rgba(59, 130, 246, 0.1); border-left: 4px solid var(--primary); border-radius: 0.5rem;">
                                        <p style="margin: 0; font-size: 0.95rem; line-height: 1.6;">
                                            Vi vasker søppelbøttene dine regelmessig gjennom hele sesongen fra <strong>uke 14 til uke 44</strong> 
                                            — fra mars 2026 til oktober 2026. Vaskedagene koordineres med når renovasjonsbilen tømmer bøttene dine, 
                                            slik at de alltid er rene og klare.
                                        </p>
                                    </div>
                                </fieldset>
                            </div>
                        </fieldset>

                        <!-- Prisvisning -->
                        <section id="priceBox" class="price-summary" aria-live="polite" style="margin-top: 1.5rem;">
                            <div class="price-lines" id="priceLines"></div>
                            <div class="price-total"><span>Totalpris</span><span id="totalPrice">0 kr</span></div>
                        </section>

                        <!-- Oppsummeringsboks -->
                        <section id="summaryBox" class="summary-box hidden" style="margin-top: 1.5rem; padding: 1rem; background: var(--muted); border-radius: 0.75rem;">
                            <h3 style="margin: 0 0 0.75rem; font-size: 1.1rem;">📋 Oppsummering</h3>
                            <div id="summaryContent"></div>
                        </section>
                        
                        <div class="btn-row" style="margin-top: 1.5rem;">
                            <button type="button" class="btn btn-secondary" onclick="jumaGoToStep(3)">Tilbake</button>
                            <button type="button" class="btn" onclick="jumaGoToStep(5)">Fortsett til kontaktdetaljer</button>
                        </div>
                    </section>

                    <!-- Steg 5: Kontaktdetaljer -->
                    <section id="step5" class="juma-form-step hidden">
                        <h2 class="sr-only">Kontaktdetaljer</h2>
                        <fieldset>
                            <legend>Kontakt & adresse</legend>
                            <div class="grid grid-2">
                                <div class="form-group">
                                    <label for="fornavn">Fornavn</label>
                                    <input id="fornavn" required maxlength="50" pattern="[a-zA-ZæøåÆØÅ\s-]+" title="Kun bokstaver, bindestrek og mellomrom tillatt">
                                    <p class="error-msg hidden" data-error-for="fornavn">Mangler data</p>
                                </div>
                                <div class="form-group">
                                    <label for="etternavn">Etternavn</label>
                                    <input id="etternavn" required maxlength="50" pattern="[a-zA-ZæøåÆØÅ\s-]+" title="Kun bokstaver, bindestrek og mellomrom tillatt">
                                    <p class="error-msg hidden" data-error-for="etternavn">Mangler data</p>
                                </div>
                            </div>
                            <div class="grid grid-2">
                                <div class="form-group">
                                    <label for="gatenavn">Gatenavn</label>
                                    <input id="gatenavn" required maxlength="100" pattern="[a-zA-ZæøåÆØÅ0-9\s.-]+" title="Kun bokstaver, tall, punktum og bindestrek tillatt">
                                    <p class="error-msg hidden" data-error-for="gatenavn">Mangler data</p>
                                </div>
                                <div class="form-group">
                                    <label for="husnummer">Husnummer</label>
                                    <input id="husnummer" required maxlength="10" pattern="[0-9a-zA-ZæøåÆØÅ/]+" title="Kun tall og bokstaver tillatt">
                                    <p class="error-msg hidden" data-error-for="husnummer">Mangler data</p>
                                </div>
                            </div>
                            <div class="grid grid-2">
                                <div class="form-group">
                                    <label for="oppgang">Oppgang</label>
                                    <input id="oppgang" placeholder="Valgfritt" maxlength="20" pattern="[a-zA-ZæøåÆØÅ0-9\s]+" title="Kun bokstaver og tall tillatt">
                                </div>
                                <div class="form-group">
                                    <label for="poststed">Poststed</label>
                                    <input id="poststed" required maxlength="50" pattern="[a-zA-ZæøåÆØÅ\s-]+" title="Kun bokstaver, bindestrek og mellomrom tillatt">
                                    <p class="error-msg hidden" data-error-for="poststed">Mangler data</p>
                                </div>
                            </div>
                            <div class="grid grid-2">
                                <div class="form-group">
                                    <label for="telefonnummer">Telefonnummer</label>
                                    <input id="telefonnummer" type="tel" required maxlength="15" pattern="[0-9+\s-()]{8,15}" title="8-15 siffer, pluss, bindestrek, mellomrom eller parenteser">
                                    <p class="error-msg hidden" data-error-for="telefonnummer">Mangler data</p>
                                </div>
                                <div class="form-group">
                                    <label for="epost">E-post adresse</label>
                                    <input id="epost" type="email" required maxlength="100" pattern="[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}" title="Gyldig e-postadresse påkrevd">
                                    <p class="error-msg hidden" data-error-for="epost">Mangler data</p>
                                </div>
                            </div>
                        </fieldset>

                        <fieldset>
                            <legend>Sikkerhet</legend>
                            <div class="form-group">
                                <div class="g-recaptcha" data-sitekey="<?php echo get_option('juma_recaptcha_site_key', '6LdB2_QrAAAAAFKcOvn8iEKLjHXILce8vvKF_Zuk'); ?>"></div>
                                <p class="error-msg hidden" data-error-for="recaptcha">Vennligst bekreft at du ikke er en robot.</p>
                            </div>
                        </fieldset>

                        <div class="btn-row">
                            <button type="button" class="btn btn-secondary" onclick="jumaGoToStep(4)">Tilbake</button>
                            <button type="submit" class="btn">Send bestilling</button>
                        </div>
                    </section>

                    <!-- Steg 6: Takk -->
                    <section id="step6" class="juma-form-step hidden">
                        <h2>✅ Takk for din bestilling!</h2>
                        <p>Vi har registrert bestillingen. Du mottar bekreftelse på e-post.</p>
                        <p class="muted">Du kan nå lukke denne siden.</p>
                    </section>
                </form>
            </main>
        </div>
        <?php
        return ob_get_clean();
    }
    
    public function handle_form_submission() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'juma_form_nonce')) {
            wp_die('Security check failed');
        }
        
        // Validate reCAPTCHA
        $recaptcha_secret = get_option('juma_recaptcha_secret_key', '6LdB2_QrAAAAANeBR-bzF8LvwvUHKB_7Bj065Kez');
        $recaptcha_response = $_POST['g-recaptcha-response'];
        
        if (empty($recaptcha_response)) {
            wp_send_json_error('reCAPTCHA påkrevd');
        }
        
        // Verify reCAPTCHA with Google
        $verify_url = 'https://www.google.com/recaptcha/api/siteverify';
        $response = wp_remote_post($verify_url, array(
            'body' => array(
                'secret' => $recaptcha_secret,
                'response' => $recaptcha_response,
                'remoteip' => $_SERVER['REMOTE_ADDR']
            )
        ));
        
        $response_data = json_decode(wp_remote_retrieve_body($response), true);
        
        if (!$response_data['success']) {
            wp_send_json_error('reCAPTCHA validering feilet');
        }
        
        // Sanitize input
        $form_data = array(
            'customer_type' => sanitize_text_field($_POST['customer_type']),
            'vaskepakke' => sanitize_text_field($_POST['vaskepakke']),
            'matavfall_liten' => intval($_POST['matavfall_liten']),
            'restavfall_liten' => intval($_POST['restavfall_liten']),
            'glass_liten' => intval($_POST['glass_liten']),
            'papir_liten' => intval($_POST['papir_liten']),
            'plast_liten' => intval($_POST['plast_liten']),
            'matavfall_stor' => intval($_POST['matavfall_stor']),
            'restavfall_stor' => intval($_POST['restavfall_stor']),
            'papir_stor' => intval($_POST['papir_stor']),
            'plast_stor' => intval($_POST['plast_stor']),
            'fornavn' => sanitize_text_field($_POST['fornavn']),
            'etternavn' => sanitize_text_field($_POST['etternavn']),
            'gatenavn' => sanitize_text_field($_POST['gatenavn']),
            'husnummer' => sanitize_text_field($_POST['husnummer']),
            'oppgang' => sanitize_text_field($_POST['oppgang']),
            'postnummer' => sanitize_text_field($_POST['postnummer']),
            'poststed' => sanitize_text_field($_POST['poststed']),
            'telefonnummer' => sanitize_text_field($_POST['telefonnummer']),
            'epost' => sanitize_email($_POST['epost'])
        );
        
        // Validate at least one bin is selected
        $total_bins = $form_data['matavfall_liten'] + $form_data['restavfall_liten'] + $form_data['glass_liten'] + 
                      $form_data['papir_liten'] + $form_data['plast_liten'] + $form_data['matavfall_stor'] + 
                      $form_data['restavfall_stor'] + $form_data['papir_stor'] + $form_data['plast_stor'];
        
        if ($total_bins === 0) {
            wp_send_json_error("Du må velge minst én søppelbøtte");
        }
        
        // Validate required fields
        $required_fields = array('customer_type', 'fornavn', 'etternavn', 'gatenavn', 'husnummer', 'postnummer', 'poststed', 'telefonnummer', 'epost');
        foreach ($required_fields as $field) {
            if (empty($form_data[$field])) {
                wp_send_json_error("Felt $field er påkrevd");
            }
        }
        
        // Create post
        $post_data = array(
            'post_title' => 'Bestilling fra ' . $form_data['fornavn'] . ' ' . $form_data['etternavn'],
            'post_content' => '',
            'post_status' => 'publish',
            'post_type' => 'juma_orders'
        );
        
        $post_id = wp_insert_post($post_data);
        
        if ($post_id) {
            // Save meta data
            foreach ($form_data as $key => $value) {
                update_post_meta($post_id, '_' . $key, $value);
            }
            
            // Send email to admin
            $admin_email = get_option('admin_email');
            $subject = 'Ny bestilling - Juma Clean';
            $message = "Ny bestilling mottatt:\n\n";
            $message .= "Navn: " . $form_data['fornavn'] . " " . $form_data['etternavn'] . "\n";
            $message .= "Kundetype: " . $form_data['customer_type'] . "\n";
            
            // Vis valgt pakke hvis den er valgt
            if (!empty($form_data['vaskepakke'])) {
                $pakkenavn = array(
                    'liten' => 'Liten Vaskepakke (19 vaskedager)',
                    'medium' => 'Medium Vaskepakke (22 vaskedager)',
                    'stor' => 'Stor Vaskepakke (26 vaskedager)'
                );
                $message .= "Vaskepakke: " . (isset($pakkenavn[$form_data['vaskepakke']]) ? $pakkenavn[$form_data['vaskepakke']] : $form_data['vaskepakke']) . "\n";
            }
            
            $message .= "\nSmå søppelbøtter (179 kr per vaskedag):\n";
            if ($form_data['matavfall_liten'] > 0) $message .= "- Matavfall: " . $form_data['matavfall_liten'] . " stk (7 vaskedager)\n";
            if ($form_data['restavfall_liten'] > 0) $message .= "- Restavfall: " . $form_data['restavfall_liten'] . " stk (3 vaskedager)\n";
            if ($form_data['glass_liten'] > 0) $message .= "- Glass og metall: " . $form_data['glass_liten'] . " stk (3 vaskedager)\n";
            if ($form_data['papir_liten'] > 0) $message .= "- Papp og papir: " . $form_data['papir_liten'] . " stk (3 vaskedager)\n";
            if ($form_data['plast_liten'] > 0) $message .= "- Plastemballasje: " . $form_data['plast_liten'] . " stk (3 vaskedager)\n";
            
            $message .= "\nStore søppelbøtter (279 kr per vaskedag):\n";
            if ($form_data['matavfall_stor'] > 0) $message .= "- Matavfall: " . $form_data['matavfall_stor'] . " stk (7 vaskedager)\n";
            if ($form_data['restavfall_stor'] > 0) $message .= "- Restavfall: " . $form_data['restavfall_stor'] . " stk (3 vaskedager)\n";
            if ($form_data['papir_stor'] > 0) $message .= "- Papp og papir: " . $form_data['papir_stor'] . " stk (3 vaskedager)\n";
            if ($form_data['plast_stor'] > 0) $message .= "- Plastemballasje: " . $form_data['plast_stor'] . " stk (3 vaskedager)\n";
            
            $message .= "\nAdresse: " . $form_data['gatenavn'] . " " . $form_data['husnummer'];
            if (!empty($form_data['oppgang'])) $message .= " (" . $form_data['oppgang'] . ")";
            $message .= ", " . $form_data['postnummer'] . " " . $form_data['poststed'] . "\n";
            $message .= "Telefon: " . $form_data['telefonnummer'] . "\n";
            $message .= "E-post: " . $form_data['epost'] . "\n";
            
            wp_mail($admin_email, $subject, $message);
            
            // Send confirmation to customer
            $customer_subject = 'Takk for din bestilling - Juma Clean';
            $customer_message = "Hei " . $form_data['fornavn'] . ",\n\n";
            $customer_message .= "Takk for din bestilling!\n\n";
            $customer_message .= "Vi kontakter deg snart for å avtale tidspunkt for vask av søppelbøttene.\n\n";
            $customer_message .= "Med vennlig hilsen,\nJuma Clean";
            
            wp_mail($form_data['epost'], $customer_subject, $customer_message);
            
            // Send data to Google Sheets
            $this->send_to_google_sheets($form_data);
            
            wp_send_json_success('Bestilling registrert');
        } else {
            wp_send_json_error('Database feil');
        }
    }

    public function get_prices_ajax() {
        wp_send_json_success(array(
            'enkeltvask' => array(
                'liten' => intval(get_option('juma_enkeltvask_liten', 229)),
                'stor' => intval(get_option('juma_enkeltvask_stor', 279))
            ),
            'sesongvask' => array(
                'liten' => intval(get_option('juma_sesongvask_liten', 179)),
                'stor' => intval(get_option('juma_sesongvask_stor', 229))
            ),
            'pakker' => array(
                'liten' => intval(get_option('juma_pakke_liten_dager', 19)),
                'medium' => intval(get_option('juma_pakke_medium_dager', 22)),
                'stor' => intval(get_option('juma_pakke_stor_dager', 26))
            ),
            'rabatter' => array(
                'enkeltvask' => array(
                    '2' => intval(get_option('juma_enkeltvask_discount_2', 50)),
                    '3_4' => intval(get_option('juma_enkeltvask_discount_3_4', 10)),
                    '5_9' => intval(get_option('juma_enkeltvask_discount_5_9', 20)),
                    '10_19' => intval(get_option('juma_enkeltvask_discount_10_19', 30)),
                    '20_29' => intval(get_option('juma_enkeltvask_discount_20_29', 40)),
                    '30_39' => intval(get_option('juma_enkeltvask_discount_30_39', 50))
                ),
                'sesongvask' => array(
                    '10_19' => intval(get_option('juma_sesongvask_discount_10_19', 10)),
                    '20_39' => intval(get_option('juma_sesongvask_discount_20_39', 20)),
                    '40_59' => intval(get_option('juma_sesongvask_discount_40_59', 30)),
                    '60_89' => intval(get_option('juma_sesongvask_discount_60_89', 40)),
                    '90_999' => intval(get_option('juma_sesongvask_discount_90_999', 50))
                )
            )
        ));
    }

    private function send_to_google_sheets($form_data) {
        $google_sheets_url = get_option('juma_google_sheets_url', '');
        
        if (empty($google_sheets_url)) {
            return; // Ingen Google Sheets URL konfigurert
        }
        
        // Forbered data for Google Sheets
        $data = array(
            'timestamp' => current_time('Y-m-d H:i:s'),
            'customer_type' => $form_data['customer_type'],
            'package_type' => $form_data['package_type'],
            'fornavn' => $form_data['fornavn'],
            'etternavn' => $form_data['etternavn'],
            'gatenavn' => $form_data['gatenavn'],
            'husnr' => $form_data['husnr'],
            'oppgang' => $form_data['oppgang'],
            'postnr' => $form_data['postnr'],
            'poststed' => $form_data['poststed'],
            'telefon' => $form_data['telefon'],
            'epost' => $form_data['epost']
        );
        
        // Send til Google Sheets
        $response = wp_remote_post($google_sheets_url, array(
            'method' => 'POST',
            'timeout' => 15,
            'headers' => array('Content-Type' => 'application/json'),
            'body' => json_encode($data)
        ));
        
        // Logg eventuell feil (valgfritt)
        if (is_wp_error($response)) {
            error_log('Google Sheets error: ' . $response->get_error_message());
        }
    }
    
    public function add_admin_menu() {
        add_options_page(
            'Juma Clean Settings',
            'Juma Clean',
            'manage_options',
            'juma-clean-settings',
            array($this, 'admin_page')
        );
    }
    
    public function admin_init() {
        register_setting('juma_clean_settings', 'juma_recaptcha_site_key');
        register_setting('juma_clean_settings', 'juma_recaptcha_secret_key');
        register_setting('juma_clean_settings', 'juma_google_sheets_url');

        // Register price settings with defaults
        register_setting('juma_clean_settings', 'juma_enkeltvask_liten');
        register_setting('juma_clean_settings', 'juma_enkeltvask_stor');
        register_setting('juma_clean_settings', 'juma_sesongvask_liten');
        register_setting('juma_clean_settings', 'juma_sesongvask_stor');
        register_setting('juma_clean_settings', 'juma_pakke_liten_dager');
        register_setting('juma_clean_settings', 'juma_pakke_medium_dager');
        register_setting('juma_clean_settings', 'juma_pakke_stor_dager');

        // Register discount settings
        register_setting('juma_clean_settings', 'juma_enkeltvask_discount_2');
        register_setting('juma_clean_settings', 'juma_enkeltvask_discount_3_4');
        register_setting('juma_clean_settings', 'juma_enkeltvask_discount_5_9');
        register_setting('juma_clean_settings', 'juma_enkeltvask_discount_10_19');
        register_setting('juma_clean_settings', 'juma_enkeltvask_discount_20_29');
        register_setting('juma_clean_settings', 'juma_enkeltvask_discount_30_39');

        register_setting('juma_clean_settings', 'juma_sesongvask_discount_10_19');
        register_setting('juma_clean_settings', 'juma_sesongvask_discount_20_39');
        register_setting('juma_clean_settings', 'juma_sesongvask_discount_40_59');
        register_setting('juma_clean_settings', 'juma_sesongvask_discount_60_89');
        register_setting('juma_clean_settings', 'juma_sesongvask_discount_90_999');

        // Set default values if not set
        if (!get_option('juma_enkeltvask_liten')) {
            update_option('juma_enkeltvask_liten', '229');
        }
        if (!get_option('juma_enkeltvask_stor')) {
            update_option('juma_enkeltvask_stor', '279');
        }
        if (!get_option('juma_sesongvask_liten')) {
            update_option('juma_sesongvask_liten', '179');
        }
        if (!get_option('juma_sesongvask_stor')) {
            update_option('juma_sesongvask_stor', '229');
        }
        if (!get_option('juma_pakke_liten_dager')) {
            update_option('juma_pakke_liten_dager', '19');
        }
        if (!get_option('juma_pakke_medium_dager')) {
            update_option('juma_pakke_medium_dager', '22');
        }
        if (!get_option('juma_pakke_stor_dager')) {
            update_option('juma_pakke_stor_dager', '26');
        }

        // Set default discount values
        if (!get_option('juma_enkeltvask_discount_2')) {
            update_option('juma_enkeltvask_discount_2', '50');
        }
        if (!get_option('juma_enkeltvask_discount_3_4')) {
            update_option('juma_enkeltvask_discount_3_4', '10');
        }
        if (!get_option('juma_enkeltvask_discount_5_9')) {
            update_option('juma_enkeltvask_discount_5_9', '20');
        }
        if (!get_option('juma_enkeltvask_discount_10_19')) {
            update_option('juma_enkeltvask_discount_10_19', '30');
        }
        if (!get_option('juma_enkeltvask_discount_20_29')) {
            update_option('juma_enkeltvask_discount_20_29', '40');
        }
        if (!get_option('juma_enkeltvask_discount_30_39')) {
            update_option('juma_enkeltvask_discount_30_39', '50');
        }

        if (!get_option('juma_sesongvask_discount_10_19')) {
            update_option('juma_sesongvask_discount_10_19', '10');
        }
        if (!get_option('juma_sesongvask_discount_20_39')) {
            update_option('juma_sesongvask_discount_20_39', '20');
        }
        if (!get_option('juma_sesongvask_discount_40_59')) {
            update_option('juma_sesongvask_discount_40_59', '30');
        }
        if (!get_option('juma_sesongvask_discount_60_89')) {
            update_option('juma_sesongvask_discount_60_89', '40');
        }
        if (!get_option('juma_sesongvask_discount_90_999')) {
            update_option('juma_sesongvask_discount_90_999', '50');
        }
    }
    
    public function admin_page() {
        ?>
        <div class="wrap">
            <h1>Juma Clean Settings</h1>
            <form method="post" action="options.php">
                <?php
                settings_fields('juma_clean_settings');
                do_settings_sections('juma_clean_settings');
                ?>
                
                <h2>reCAPTCHA Innstillinger</h2>
                <table class="form-table">
                    <tr>
                        <th scope="row">reCAPTCHA Site Key</th>
                        <td><input type="text" name="juma_recaptcha_site_key" value="<?php echo esc_attr(get_option('juma_recaptcha_site_key')); ?>" class="regular-text" /></td>
                    </tr>
                    <tr>
                        <th scope="row">reCAPTCHA Secret Key</th>
                        <td><input type="text" name="juma_recaptcha_secret_key" value="<?php echo esc_attr(get_option('juma_recaptcha_secret_key')); ?>" class="regular-text" /></td>
                    </tr>
                </table>
                
                <h2>Prisinnstillinger</h2>
                <table class="form-table">
                    <tr>
                        <th colspan="2"><h3>Enkeltvask priser</h3></th>
                    </tr>
                    <tr>
                        <th scope="row">Liten søppelbøtte</th>
                        <td>
                            <input type="number" name="juma_enkeltvask_liten" value="<?php echo esc_attr(get_option('juma_enkeltvask_liten', '229')); ?>" min="0" step="1" class="small-text" /> kr
                            <p class="description">Pris per liten søppelbøtte for enkeltvask</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Stor søppelbøtte</th>
                        <td>
                            <input type="number" name="juma_enkeltvask_stor" value="<?php echo esc_attr(get_option('juma_enkeltvask_stor', '279')); ?>" min="0" step="1" class="small-text" /> kr
                            <p class="description">Pris per stor søppelbøtte for enkeltvask</p>
                        </td>
                    </tr>

                    <tr>
                        <th colspan="2"><h3>Sesongvask priser (per dag)</h3></th>
                    </tr>
                    <tr>
                        <th scope="row">Liten søppelbøtte</th>
                        <td>
                            <input type="number" name="juma_sesongvask_liten" value="<?php echo esc_attr(get_option('juma_sesongvask_liten', '179')); ?>" min="0" step="1" class="small-text" /> kr per dag
                            <p class="description">Daglig pris per liten søppelbøtte for sesongvask</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Stor søppelbøtte</th>
                        <td>
                            <input type="number" name="juma_sesongvask_stor" value="<?php echo esc_attr(get_option('juma_sesongvask_stor', '229')); ?>" min="0" step="1" class="small-text" /> kr per dag
                            <p class="description">Daglig pris per stor søppelbøtte for sesongvask</p>
                        </td>
                    </tr>

                    <tr>
                        <th colspan="2"><h3>Vaskepakker (antall dager)</h3></th>
                    </tr>
                    <tr>
                        <th scope="row">Liten pakke</th>
                        <td>
                            <input type="number" name="juma_pakke_liten_dager" value="<?php echo esc_attr(get_option('juma_pakke_liten_dager', '19')); ?>" min="1" step="1" class="small-text" /> dager
                            <p class="description">Antall vaskedager i liten sesongpakke</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Medium pakke</th>
                        <td>
                            <input type="number" name="juma_pakke_medium_dager" value="<?php echo esc_attr(get_option('juma_pakke_medium_dager', '22')); ?>" min="1" step="1" class="small-text" /> dager
                            <p class="description">Antall vaskedager i medium sesongpakke</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Stor pakke</th>
                        <td>
                            <input type="number" name="juma_pakke_stor_dager" value="<?php echo esc_attr(get_option('juma_pakke_stor_dager', '26')); ?>" min="1" step="1" class="small-text" /> dager
                            <p class="description">Antall vaskedager i stor sesongpakke</p>
                        </td>
                    </tr>
                </table>

                <h3>Rabattordninger (basert på antall dunkevask)</h3>
                <table class="form-table">
                    <tr>
                        <th colspan="2"><h4>Enkeltvask rabatter</h4></th>
                    </tr>
                    <tr>
                        <th scope="row">2 vask</th>
                        <td>
                            <input type="number" name="juma_enkeltvask_discount_2" value="<?php echo esc_attr(get_option('juma_enkeltvask_discount_2', '50')); ?>" min="0" max="100" step="1" class="small-text" /> %
                            <p class="description">Rabatt for 2 enkeltvask</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">3-4 vask</th>
                        <td>
                            <input type="number" name="juma_enkeltvask_discount_3_4" value="<?php echo esc_attr(get_option('juma_enkeltvask_discount_3_4', '10')); ?>" min="0" max="100" step="1" class="small-text" /> %
                            <p class="description">Rabatt for 3-4 enkeltvask</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">5-9 vask</th>
                        <td>
                            <input type="number" name="juma_enkeltvask_discount_5_9" value="<?php echo esc_attr(get_option('juma_enkeltvask_discount_5_9', '20')); ?>" min="0" max="100" step="1" class="small-text" /> %
                            <p class="description">Rabatt for 5-9 enkeltvask</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">10-19 vask</th>
                        <td>
                            <input type="number" name="juma_enkeltvask_discount_10_19" value="<?php echo esc_attr(get_option('juma_enkeltvask_discount_10_19', '30')); ?>" min="0" max="100" step="1" class="small-text" /> %
                            <p class="description">Rabatt for 10-19 enkeltvask</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">20-29 vask</th>
                        <td>
                            <input type="number" name="juma_enkeltvask_discount_20_29" value="<?php echo esc_attr(get_option('juma_enkeltvask_discount_20_29', '40')); ?>" min="0" max="100" step="1" class="small-text" /> %
                            <p class="description">Rabatt for 20-29 enkeltvask</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">30-39 vask</th>
                        <td>
                            <input type="number" name="juma_enkeltvask_discount_30_39" value="<?php echo esc_attr(get_option('juma_enkeltvask_discount_30_39', '50')); ?>" min="0" max="100" step="1" class="small-text" /> %
                            <p class="description">Rabatt for 30-39 enkeltvask</p>
                        </td>
                    </tr>

                    <tr>
                        <th colspan="2"><h4>Sesongvask rabatter</h4></th>
                    </tr>
                    <tr>
                        <th scope="row">10-19 dunkevask</th>
                        <td>
                            <input type="number" name="juma_sesongvask_discount_10_19" value="<?php echo esc_attr(get_option('juma_sesongvask_discount_10_19', '10')); ?>" min="0" max="100" step="1" class="small-text" /> %
                            <p class="description">Rabatt for 10-19 dunkevask i sesong</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">20-39 dunkevask</th>
                        <td>
                            <input type="number" name="juma_sesongvask_discount_20_39" value="<?php echo esc_attr(get_option('juma_sesongvask_discount_20_39', '20')); ?>" min="0" max="100" step="1" class="small-text" /> %
                            <p class="description">Rabatt for 20-39 dunkevask i sesong</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">40-59 dunkevask</th>
                        <td>
                            <input type="number" name="juma_sesongvask_discount_40_59" value="<?php echo esc_attr(get_option('juma_sesongvask_discount_40_59', '30')); ?>" min="0" max="100" step="1" class="small-text" /> %
                            <p class="description">Rabatt for 40-59 dunkevask i sesong</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">60-89 dunkevask</th>
                        <td>
                            <input type="number" name="juma_sesongvask_discount_60_89" value="<?php echo esc_attr(get_option('juma_sesongvask_discount_60_89', '40')); ?>" min="0" max="100" step="1" class="small-text" /> %
                            <p class="description">Rabatt for 60-89 dunkevask i sesong</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">90-999 dunkevask</th>
                        <td>
                            <input type="number" name="juma_sesongvask_discount_90_999" value="<?php echo esc_attr(get_option('juma_sesongvask_discount_90_999', '50')); ?>" min="0" max="100" step="1" class="small-text" /> %
                            <p class="description">Rabatt for 90-999 dunkevask i sesong</p>
                        </td>
                    </tr>
                </table>

                <h2>Google Sheets Integrasjon</h2>
                <table class="form-table">
                    <tr>
                        <th scope="row">Google Sheets Web App URL</th>
                        <td>
                            <input type="url" name="juma_google_sheets_url" value="<?php echo esc_attr(get_option('juma_google_sheets_url')); ?>" class="large-text" placeholder="https://script.google.com/macros/s/..." />
                            <p class="description">
                                Lim inn URL fra din Google Apps Script Web App. <br>
                                <strong>Slik setter du det opp:</strong><br>
                                1. Åpne Google Sheet → Extensions → Apps Script<br>
                                2. Lim inn script-koden (se dokumentasjon)<br>
                                3. Deploy → New deployment → Web app<br>
                                4. Execute as: Me, Who has access: Anyone<br>
                                5. Kopier Web app URL og lim inn her
                            </p>
                        </td>
                    </tr>
                </table>
                
                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }
    
    // Custom columns for Juma Orders list
    public function set_custom_columns($columns) {
        $new_columns = array(
            'cb' => $columns['cb'],
            'title' => 'Navn',
            'customer_type' => 'Kundetype',
            'package_type' => 'Pakke',
            'address' => 'Adresse',
            'contact' => 'Kontakt',
            'date' => 'Dato'
        );
        return $new_columns;
    }
    
    public function custom_column_content($column, $post_id) {
        switch ($column) {
            case 'customer_type':
                $type = get_post_meta($post_id, '_customer_type', true);
                echo esc_html(ucfirst($type));
                break;
                
            case 'package_type':
                $package = get_post_meta($post_id, '_package_type', true);
                $package_names = array(
                    'liten' => 'Liten (19 dager)',
                    'medium' => 'Medium (22 dager)',
                    'stor' => 'Stor (26 dager)'
                );
                echo isset($package_names[$package]) ? esc_html($package_names[$package]) : esc_html($package);
                break;
                
            case 'address':
                $gatenavn = get_post_meta($post_id, '_gatenavn', true);
                $husnr = get_post_meta($post_id, '_husnr', true);
                $oppgang = get_post_meta($post_id, '_oppgang', true);
                $postnr = get_post_meta($post_id, '_postnr', true);
                $poststed = get_post_meta($post_id, '_poststed', true);
                
                echo esc_html($gatenavn . ' ' . $husnr);
                if (!empty($oppgang)) {
                    echo ' (' . esc_html($oppgang) . ')';
                }
                echo '<br>' . esc_html($postnr . ' ' . $poststed);
                break;
                
            case 'contact':
                $telefon = get_post_meta($post_id, '_telefon', true);
                $epost = get_post_meta($post_id, '_epost', true);
                
                if (!empty($telefon)) {
                    echo '📞 ' . esc_html($telefon) . '<br>';
                }
                if (!empty($epost)) {
                    echo '✉️ <a href="mailto:' . esc_attr($epost) . '">' . esc_html($epost) . '</a>';
                }
                break;
        }
    }
    
    // Add meta boxes to show order details
    public function add_order_meta_boxes() {
        add_meta_box(
            'juma_order_details',
            'Bestillingsdetaljer',
            array($this, 'render_order_details_meta_box'),
            'juma_orders',
            'normal',
            'high'
        );
    }
    
    public function render_order_details_meta_box($post) {
        $customer_type = get_post_meta($post->ID, '_customer_type', true);
        $package_type = get_post_meta($post->ID, '_package_type', true);
        $fornavn = get_post_meta($post->ID, '_fornavn', true);
        $etternavn = get_post_meta($post->ID, '_etternavn', true);
        $gatenavn = get_post_meta($post->ID, '_gatenavn', true);
        $husnr = get_post_meta($post->ID, '_husnr', true);
        $oppgang = get_post_meta($post->ID, '_oppgang', true);
        $postnr = get_post_meta($post->ID, '_postnr', true);
        $poststed = get_post_meta($post->ID, '_poststed', true);
        $telefon = get_post_meta($post->ID, '_telefon', true);
        $epost = get_post_meta($post->ID, '_epost', true);
        
        $package_names = array(
            'liten' => 'Liten vaskepakke (' . get_option('juma_pakke_liten_dager', '19') . ' vaskedager)',
            'medium' => 'Medium vaskepakke (' . get_option('juma_pakke_medium_dager', '22') . ' vaskedager)',
            'stor' => 'Stor vaskepakke (' . get_option('juma_pakke_stor_dager', '26') . ' vaskedager)'
        );
        ?>
        <style>
            .juma-order-details { font-size: 14px; }
            .juma-order-details table { width: 100%; border-collapse: collapse; }
            .juma-order-details th { text-align: left; padding: 10px; background: #f5f5f5; width: 30%; }
            .juma-order-details td { padding: 10px; border-bottom: 1px solid #e5e5e5; }
            .juma-order-details tr:last-child td { border-bottom: none; }
        </style>
        <div class="juma-order-details">
            <table>
                <tr>
                    <th>Kundetype:</th>
                    <td><strong><?php echo esc_html(ucfirst($customer_type)); ?></strong></td>
                </tr>
                <tr>
                    <th>Vaskepakke:</th>
                    <td><strong><?php echo isset($package_names[$package_type]) ? esc_html($package_names[$package_type]) : esc_html($package_type); ?></strong></td>
                </tr>
                <tr>
                    <th>Fornavn:</th>
                    <td><?php echo esc_html($fornavn); ?></td>
                </tr>
                <tr>
                    <th>Etternavn:</th>
                    <td><?php echo esc_html($etternavn); ?></td>
                </tr>
                <tr>
                    <th>Gatenavn:</th>
                    <td><?php echo esc_html($gatenavn); ?></td>
                </tr>
                <tr>
                    <th>Husnummer:</th>
                    <td><?php echo esc_html($husnr); ?></td>
                </tr>
                <?php if (!empty($oppgang)): ?>
                <tr>
                    <th>Oppgang:</th>
                    <td><?php echo esc_html($oppgang); ?></td>
                </tr>
                <?php endif; ?>
                <tr>
                    <th>Postnummer:</th>
                    <td><?php echo esc_html($postnr); ?></td>
                </tr>
                <tr>
                    <th>Poststed:</th>
                    <td><?php echo esc_html($poststed); ?></td>
                </tr>
                <tr>
                    <th>Telefon:</th>
                    <td><a href="tel:<?php echo esc_attr($telefon); ?>"><?php echo esc_html($telefon); ?></a></td>
                </tr>
                <tr>
                    <th>E-post:</th>
                    <td><a href="mailto:<?php echo esc_attr($epost); ?>"><?php echo esc_html($epost); ?></a></td>
                </tr>
            </table>
        </div>
        <?php
    }
}

// Initialize the plugin
new JumaCleanForm();
?>
