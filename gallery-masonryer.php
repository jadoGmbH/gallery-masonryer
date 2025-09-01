<?php
/**
 * Plugin Name: Gallery Masonryer
 * Description: Erweitert Gutenberg Gallery Blocks mit Orientierungs-basierten Grid-Layouts.
 * Version: 1.3.1
 * Author: jado GmbH
 * Text Domain: gallery-masonryer
 * Domain Path: /languages
 */

if (!defined('ABSPATH')) exit; // Direktzugriff verhindern

class GalleryMasonryer
{
    public function __construct()
    {
        load_plugin_textdomain('gallery-masonryer', false, dirname(plugin_basename(__FILE__)) . '/languages');
        add_action('admin_menu', [$this, 'add_settings_page']);
        add_action('admin_init', [$this, 'register_settings']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);
        add_action('wp_head', [$this, 'output_custom_styles']);
    }

    public function add_settings_page()
    {
        add_options_page(
                __('Gallery Masonryer', 'gallery-masonryer'),
                __('Gallery Masonryer', 'gallery-masonryer'),
                'manage_options',
                'gallery-masonryer',
                [$this, 'settings_page_html']
        );
    }

    public function register_settings()
    {
        register_setting('gallery_masonryer_options', 'gallery_gap', [
                'type' => 'integer',
                'sanitize_callback' => 'absint',
                'default' => 10,
        ]);
        register_setting('gallery_masonryer_options', 'gallery_radius', [
                'type' => 'string',
                'sanitize_callback' => function ($val) {
                    return sanitize_text_field($val);
                },
                'default' => '0px',
        ]);
        register_setting('gallery_masonryer_options', 'random_placement', [
                'type' => 'boolean',
                'sanitize_callback' => function ($val) {
                    return (bool)$val;
                },
                'default' => false,
        ]);
        register_setting('gallery_masonryer_options', 'enable_lightbox', [
                'type' => 'boolean',
                'sanitize_callback' => function ($val) {
                    return (bool)$val;
                },
                'default' => false,
        ]);
        register_setting('gallery_masonryer_options', 'lightbox_background_color', [
                'type' => 'string',
                'sanitize_callback' => 'sanitize_hex_color',
                'default' => '#000000',
        ]);
        register_setting('gallery_masonryer_options', 'lightbox_background_opacity', [
                'type' => 'integer',
                'sanitize_callback' => function ($val) {
                    return max(10, min(100, absint($val)));
                },
                'default' => 90,
        ]);
    }

    public function settings_page_html()
    {
        // WordPress Color Picker einbinden
        wp_enqueue_style('wp-color-picker');
        wp_enqueue_script('wp-color-picker');

        ?>
        <div class="wrap">
            <h1><?php _e('Gallery Masonryer Einstellungen', 'gallery-masonryer'); ?></h1>
            <form method="post" action="options.php">
                <?php settings_fields('gallery_masonryer_options'); ?>
                <table class="form-table">
                    <tr>
                        <th scope="row"><label
                                    for="gallery_gap"><?php _e('Gap zwischen Bildern', 'gallery-masonryer'); ?></label>
                        </th>
                        <td><input type="number" id="gallery_gap" name="gallery_gap"
                                   value="<?php echo esc_attr(get_option('gallery_gap', 10)); ?>" min="0" max="150"> px
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label
                                    for="gallery_radius"><?php _e('Border Radius', 'gallery-masonryer'); ?></label></th>
                        <td><input type="text" id="gallery_radius" name="gallery_radius"
                                   value="<?php echo esc_attr(get_option('gallery_radius', '0px')); ?>"
                                   placeholder="<?php _e('z.B. 8px oder 0.5rem', 'gallery-masonryer'); ?>"></td>
                    </tr>
                    <tr>
                        <th scope="row"><label
                                    for="random_placement"><?php _e('Zufällige Platzierung', 'gallery-masonryer'); ?></label>
                        </th>
                        <td>
                            <input type="checkbox" id="random_placement" name="random_placement"
                                   value="1" <?php checked(1, get_option('random_placement', false)); ?>>
                            <label for="random_placement"><?php _e('CSS Grid Dense Auto-Placement verwenden', 'gallery-masonryer'); ?></label>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label
                                    for="enable_lightbox"><?php _e('Bilder klickbar machen (Einfache Lightbox)', 'gallery-masonryer'); ?></label>
                        </th>
                        <td>
                            <input type="checkbox" id="enable_lightbox" name="enable_lightbox"
                                   value="1" <?php checked(1, get_option('enable_lightbox', false)); ?>>
                            <label for="enable_lightbox"><?php _e('Aktiviere eine einfache, funktionsfähige Lightbox', 'gallery-masonryer'); ?></label>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label
                                    for="lightbox_background_color"><?php _e('Lightbox Hintergrundfarbe', 'gallery-masonryer'); ?></label>
                        </th>
                        <td>
                            <input type="text" id="lightbox_background_color" name="lightbox_background_color"
                                   value="<?php echo esc_attr(get_option('lightbox_background_color', '#000000')); ?>"
                                   class="color-picker" data-default-color="#000000">
                            <p class="description">
                                <?php _e('Wählen Sie die Grundfarbe für den Lightbox-Hintergrund.', 'gallery-masonryer'); ?>
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label
                                    for="lightbox_background_opacity"><?php _e('Lightbox Hintergrund Transparenz', 'gallery-masonryer'); ?></label>
                        </th>
                        <td>
                            <input type="range" id="lightbox_background_opacity" name="lightbox_background_opacity"
                                   value="<?php echo esc_attr(get_option('lightbox_background_opacity', 90)); ?>"
                                   min="10" max="100" step="5">
                            <span id="opacity-value"><?php echo esc_attr(get_option('lightbox_background_opacity', 90)); ?>%</span>
                            <p class="description">
                                <?php _e('Bestimmen Sie die Transparenz des Hintergrunds (10% = sehr transparent, 100% = komplett deckend).', 'gallery-masonryer'); ?>
                            </p>
                        </td>
                    </tr>
                </table>
                <?php submit_button(); ?>
            </form>

            <!-- Live-Vorschau -->
            <div style="margin-top: 30px; padding: 20px; background: #f9f9f9; border-left: 4px solid #0073aa;">
                <h3><?php _e('Live-Vorschau:', 'gallery-masonryer'); ?></h3>
                <div id="lightbox-preview" style="width: 200px; height: 100px; border: 2px solid #ddd; border-radius: 4px; position: relative; overflow: hidden; background: linear-gradient(45deg, #f0f0f0 25%, transparent 25%), linear-gradient(-45deg, #f0f0f0 25%, transparent 25%), linear-gradient(45deg, transparent 75%, #f0f0f0 75%), linear-gradient(-45deg, transparent 75%, #f0f0f0 75%); background-size: 20px 20px; background-position: 0 0, 0 10px, 10px -10px, -10px 0px;">
                    <div id="preview-overlay" style="position: absolute; inset: 0; display: flex; align-items: center; justify-content: center; color: white; font-weight: bold; font-size: 14px;">
                        <?php _e('Lightbox Hintergrund', 'gallery-masonryer'); ?>
                    </div>
                </div>
            </div>
        </div>

        <div style="margin-top: 20px; padding: 15px; background: #f9f9f9; border-left: 4px solid #0073aa;">
            <h3><?php _e('Hinweis:', 'gallery-masonryer'); ?></h3>
            <p><strong><?php _e('Bildgrößen:', 'gallery-masonryer'); ?></strong></p>
            <ul>
                <li>
                    <strong><?php _e('Landscape:', 'gallery-masonryer'); ?></strong> <?php _e('2x1 Verhältnis (nimmt 2 Spalten ein)', 'gallery-masonryer'); ?>
                </li>
                <li>
                    <strong><?php _e('Portrait:', 'gallery-masonryer'); ?></strong> <?php _e('1x2 Verhältnis (nimmt 2 Zeilen ein)', 'gallery-masonryer'); ?>
                </li>
                <li>
                    <strong><?php _e('Square:', 'gallery-masonryer'); ?></strong> <?php _e('1x1 Verhältnis', 'gallery-masonryer'); ?>
                </li>
            </ul>
            <p><?php _e('Die Anzahl der Spalten wird aus den Gutenberg Gallery Block Einstellungen übernommen.', 'gallery-masonryer'); ?></p>
        </div>
        </div>

        <script type="text/javascript">
            jQuery(document).ready(function($) {
                // WordPress Color Picker initialisieren
                $('.color-picker').wpColorPicker({
                    change: function(event, ui) {
                        updatePreview();
                    },
                    clear: function() {
                        updatePreview();
                    }
                });

                // Opacity Slider Handler
                $('#lightbox_background_opacity').on('input', function() {
                    $('#opacity-value').text($(this).val() + '%');
                    updatePreview();
                });

                // Live-Vorschau aktualisieren
                function updatePreview() {
                    var color = $('#lightbox_background_color').val() || '#000000';
                    var opacity = $('#lightbox_background_opacity').val() / 100;

                    // Hex zu RGB konvertieren
                    var r = parseInt(color.substr(1,2), 16);
                    var g = parseInt(color.substr(3,2), 16);
                    var b = parseInt(color.substr(5,2), 16);

                    var rgba = 'rgba(' + r + ',' + g + ',' + b + ',' + opacity + ')';
                    $('#preview-overlay').css('background-color', rgba);

                    // Textfarbe anpassen für bessere Lesbarkeit
                    var brightness = (r * 299 + g * 587 + b * 114) / 1000;
                    var textColor = brightness > 128 ? '#333333' : '#ffffff';
                    $('#preview-overlay').css('color', textColor);
                }

                // Initiale Vorschau
                updatePreview();
            });
        </script>
        <?php
    }

    public function enqueue_assets()
    {
        // Script registrieren und einbinden
        wp_register_script(
                'gallery-masonryer',
                '', // Inline Script
                [], // Keine Abhängigkeiten
                '1.3.1',
                true
        );
        wp_enqueue_script('gallery-masonryer');

        // CSS registrieren für Lightbox
        wp_register_style(
                'gallery-masonryer-lightbox',
                '', // Inline CSS
                [],
                '1.3.1'
        );
        wp_enqueue_style('gallery-masonryer-lightbox');

        // JavaScript Optionen bereitstellen
        $js_options = [
                'enableLightbox' => get_option('enable_lightbox', false),
        ];
        wp_add_inline_script('gallery-masonryer', 'const GalleryMasonryerOptions = ' . wp_json_encode($js_options) . ';', 'before');

        // Hauptscript hinzufügen
        wp_add_inline_script('gallery-masonryer', $this->get_script());

        // Lightbox CSS einbinden wenn aktiviert
        if (get_option('enable_lightbox', false)) {
            wp_add_inline_style('gallery-masonryer-lightbox', $this->get_lightbox_styles());
        }
    }

    private function get_lightbox_background_style()
    {
        $color = get_option('lightbox_background_color', '#000000');
        $opacity = get_option('lightbox_background_opacity', 90) / 100;

        // Hex zu RGB konvertieren
        $color = str_replace('#', '', $color);
        $r = hexdec(substr($color, 0, 2));
        $g = hexdec(substr($color, 2, 2));
        $b = hexdec(substr($color, 4, 2));

        return "rgba({$r}, {$g}, {$b}, {$opacity})";
    }

    private function get_lightbox_styles()
    {
        $background_color = $this->get_lightbox_background_style();

        // Textfarbe basierend auf Hintergrundfarbe berechnen
        $color = get_option('lightbox_background_color', '#000000');
        $color = str_replace('#', '', $color);
        $r = hexdec(substr($color, 0, 2));
        $g = hexdec(substr($color, 2, 2));
        $b = hexdec(substr($color, 4, 2));
        $brightness = ($r * 299 + $g * 587 + $b * 114) / 1000;
        $text_color = $brightness > 128 ? '#333333' : '#ffffff';

        return <<<CSS
/* Simple Lightbox Styles */
.simple-lightbox {
    display: none !important;
    position: fixed !important;
    z-index: 999999 !important;
    left: 0 !important;
    top: 0 !important;
    width: 100% !important;
    height: 100% !important;
    background-color: {$background_color} !important;
    animation: fadeIn 0.3s !important;
    box-sizing: border-box !important;
}

.simple-lightbox.active {
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
}

.simple-lightbox img {
    max-width: 90% !important;
    max-height: 90% !important;
    object-fit: contain !important;
    border-radius: 8px !important;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.5) !important;
}

.simple-lightbox .close {
    position: absolute !important;
    top: 20px !important;
    right: 30px !important;
    color: {$text_color} !important;
    cursor: pointer !important;
    user-select: none !important;
    background: none !important;
    border: none !important;
    padding: 10px !important;
    line-height: 1 !important;
    text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.5) !important;
}

.simple-lightbox .close svg{
width: 2rem;
height: 2rem;
}

.simple-lightbox .close:hover {
    opacity: 0.7 !important;
}

.simple-lightbox .nav {
    position: absolute !important;
    top: 50% !important;
    transform: translateY(-50%) !important;
    color: {$text_color} !important;
    font-weight: bold !important;
    cursor: pointer !important;
    user-select: none !important;
    background: rgba(0, 0, 0, 0.3) !important;
    border: none !important;
    padding: 8px 8px !important;
    border-radius: 4px !important;
}

.simple-lightbox .nav svg{
width: 2rem;
height: 2rem;
}

.simple-lightbox .nav:hover {
    background: rgba(0, 0, 0, 0.6) !important;
}

.simple-lightbox .prev {
    left: 20px !important;
}

.simple-lightbox .next {
    right: 20px !important;
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

.masonryer-item.lightbox-enabled {
    overflow: hidden;
}

.masonryer-item.lightbox-enabled img {
    transition: transform 0.2s ease !important;
}

.masonryer-item.lightbox-enabled img:hover {
    transform: scale(1.02) !important;
}
CSS;
    }

    private function get_script()
    {
        return <<<JS
(function(){
    console.log('Gallery Masonryer Script gestartet');
    
    let lightboxImages = [];
    let currentIndex = 0;
    let lightboxElement = null;
    
    // Warten bis DOM bereit ist
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initGalleryMasonryer);
    } else {
        initGalleryMasonryer();
    }

    function initGalleryMasonryer() {
        console.log('Gallery Masonryer initialisiert');
        const galleries = document.querySelectorAll('.wp-block-gallery');
        console.log('Gefundene Galerien:', galleries.length);
        
        if (galleries.length === 0) {
            console.log('Keine Galerien gefunden');
            return;
        }

        galleries.forEach((gallery, index) => {
            console.log('Verarbeite Galerie', index + 1);
            const columns = extractColumnsFromGallery(gallery);
            gallery.style.setProperty('--gallery-columns', columns);
            gallery.classList.add('masonryer-active');

            const images = gallery.querySelectorAll('.wp-block-image img, figure.wp-block-image img');
            console.log('Gefundene Bilder in Galerie ' + (index + 1) + ':', images.length);

            images.forEach((img, imgIndex) => {
                console.log('Verarbeite Bild', imgIndex + 1, ':', img.src);
                
                if (img.complete && img.naturalHeight !== 0) {
                    setImageOrientation(img);
                } else {
                    img.addEventListener('load', () => {
                        setImageOrientation(img);
                    });
                    img.addEventListener('error', () => {
                        console.log('Fehler beim Laden des Bildes:', img.src);
                    });
                }
            });
        });
        
        // Lightbox initialisieren wenn aktiviert
        console.log('Lightbox aktiviert:', GalleryMasonryerOptions.enableLightbox);
        if (GalleryMasonryerOptions.enableLightbox) {
            initSimpleLightbox();
        }
    }

    function extractColumnsFromGallery(gallery) {
        // Erste Priorität: CSS-Klasse
        const classList = Array.from(gallery.classList);
        const columnClass = classList.find(cls => cls.startsWith('columns-'));
        if (columnClass) {
            const num = parseInt(columnClass.replace('columns-', ''));
            if (num && num > 0) {
                console.log('Spalten aus CSS-Klasse:', num);
                return num;
            }
        }
        
        // Zweite Priorität: data-columns Attribut
        const dataColumns = gallery.getAttribute('data-columns');
        if (dataColumns) {
            const num = parseInt(dataColumns);
            if (num && num > 0) {
                console.log('Spalten aus data-Attribut:', num);
                return num;
            }
        }
        
        // Standard: 3 Spalten
        console.log('Verwende Standard-Spalten: 3');
        return 3;
    }

    function setImageOrientation(img) {
        const parent = img.closest('.wp-block-image') || img.closest('figure.wp-block-image');
        if (!parent) {
            console.log('Kein parent Element gefunden für Bild:', img.src);
            return;
        }

        const { naturalWidth: w, naturalHeight: h } = img;
        if (w === 0 || h === 0) {
            console.log('Bild noch nicht geladen oder ungültige Dimensionen:', img.src);
            return;
        }

        const ratio = w / h;
        console.log('Bild-Verhältnis für', img.src, ':', ratio);

        // Orientierung bestimmen und Klassen setzen
        parent.classList.remove('masonryer-landscape', 'masonryer-portrait', 'masonryer-square');
        
        if (ratio > 1.2) {
            parent.classList.add('masonryer-landscape');
            console.log('Bild als Landscape klassifiziert');
        } else if (ratio < 0.8) {
            parent.classList.add('masonryer-portrait');
            console.log('Bild als Portrait klassifiziert');
        } else {
            parent.classList.add('masonryer-square');
            console.log('Bild als Square klassifiziert');
        }

        parent.classList.add('masonryer-item');

        // Lightbox-Funktionalität hinzufügen
        if (typeof GalleryMasonryerOptions !== 'undefined' && GalleryMasonryerOptions.enableLightbox) {
            console.log('Füge Lightbox-Funktionalität zu Bild hinzu');
            parent.classList.add('lightbox-enabled');
            
            // Click Event hinzufügen
            img.style.cursor = 'pointer';
            img.addEventListener('click', function(e) {
                console.log('Bild geklickt:', img.src);
                e.preventDefault();
                e.stopPropagation();
                openLightbox(img);
            });
            
            console.log('Click-Event für Lightbox hinzugefügt');
        }
    }

    function initSimpleLightbox() {
        console.log('Initialisiere Simple Lightbox');
        
        // Prüfe ob bereits vorhanden
        if (document.querySelector('.simple-lightbox')) {
            console.log('Lightbox bereits vorhanden');
            return;
        }
        
        // Lightbox HTML erstellen
        lightboxElement = document.createElement('div');
        lightboxElement.className = 'simple-lightbox';
        lightboxElement.innerHTML = `
            <button class="close"><svg width="100%" height="100%" viewBox="0 0 12 12" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" xml:space="preserve" style="fill-rule:evenodd;clip-rule:evenodd;stroke-linejoin:round;stroke-miterlimit:2;">
    <g transform="matrix(1,0,0,1,0.708,0.707)">
        <g id="if_misc-_close__1276877_3_">
            <path d="M4.293,5L-0.354,0.354C-0.549,0.158 -0.549,-0.158 -0.354,-0.354C-0.158,-0.549 0.158,-0.549 0.354,-0.354L5,4.293L9.646,-0.354C9.842,-0.549 10.158,-0.549 10.354,-0.354C10.549,-0.158 10.549,0.158 10.354,0.354L5.707,5L10.354,9.646C10.549,9.842 10.549,10.158 10.354,10.354C10.158,10.549 9.842,10.549 9.646,10.354L5,5.707L0.354,10.354C0.158,10.549 -0.158,10.549 -0.354,10.354C-0.549,10.158 -0.549,9.842 -0.354,9.646L4.293,5Z" style="fill:white;"/>
        </g>
    </g>
</svg></button>
            <button class="nav prev"><svg width="100%" height="100%" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" xml:space="preserve" style="fill-rule:evenodd;clip-rule:evenodd;stroke-linejoin:round;stroke-miterlimit:2;">
    <g transform="matrix(1,0,0,1,4,0)">
        <path d="M10.477,0.477C10.741,0.741 10.741,1.168 10.477,1.432L1.909,10L10.477,18.568C10.741,18.832 10.741,19.259 10.477,19.523C10.214,19.786 9.786,19.786 9.523,19.523L0.707,10.707C0.317,10.317 0.317,9.683 0.707,9.293L9.523,0.477C9.786,0.214 10.214,0.214 10.477,0.477Z" style="fill:white;fill-rule:nonzero;"/>
    </g>
</svg></button>
            <button class="nav next"><svg width="100%" height="100%" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" xml:space="preserve" style="fill-rule:evenodd;clip-rule:evenodd;stroke-linejoin:round;stroke-miterlimit:2;">
    <g transform="matrix(1,0,0,1,4,0)">
        <path d="M1.377,19.523C1.114,19.259 1.114,18.832 1.377,18.568L9.945,10L1.377,1.432C1.114,1.168 1.114,0.741 1.377,0.477C1.641,0.214 2.068,0.214 2.332,0.477L11.147,9.293C11.538,9.683 11.538,10.317 11.147,10.707L2.332,19.523C2.068,19.786 1.641,19.786 1.377,19.523Z" style="fill:white;fill-rule:nonzero;"/>
    </g>
</svg></button>
            <img src="" alt="">
        `;
        
        document.body.appendChild(lightboxElement);
        console.log('Lightbox HTML hinzugefügt');
        
        // Event Listeners
        lightboxElement.querySelector('.close').addEventListener('click', closeLightbox);
        lightboxElement.querySelector('.prev').addEventListener('click', prevImage);
        lightboxElement.querySelector('.next').addEventListener('click', nextImage);
        
        // Klick außerhalb des Bildes schließt Lightbox
        lightboxElement.addEventListener('click', (e) => {
            if (e.target === lightboxElement) {
                closeLightbox();
            }
        });
        
        // Tastatur-Navigation
        document.addEventListener('keydown', (e) => {
            if (lightboxElement && lightboxElement.classList.contains('active')) {
                switch(e.key) {
                    case 'Escape':
                        closeLightbox();
                        break;
                    case 'ArrowLeft':
                        prevImage();
                        break;
                    case 'ArrowRight':
                        nextImage();
                        break;
                }
            }
        });
        
        console.log('Simple Lightbox erfolgreich initialisiert');
    }

    function openLightbox(img) {
        console.log('Öffne Lightbox für:', img.src);
        
        if (!lightboxElement) {
            console.error('Lightbox Element nicht gefunden!');
            return;
        }
        
        // Alle Bilder der aktuellen Galerie sammeln
        const gallery = img.closest('.wp-block-gallery');
        if (!gallery) {
            console.error('Galerie für Bild nicht gefunden');
            return;
        }
        
        lightboxImages = Array.from(gallery.querySelectorAll('.masonryer-item img'));
        currentIndex = lightboxImages.indexOf(img);
        
        console.log('Lightbox Images:', lightboxImages.length, 'Current Index:', currentIndex);
        
        // Lightbox anzeigen
        showImageInLightbox(currentIndex);
        lightboxElement.classList.add('active');
        document.body.style.overflow = 'hidden';
        
        console.log('Lightbox geöffnet für Bild', currentIndex + 1, 'von', lightboxImages.length);
    }

    function showImageInLightbox(index) {
        console.log('Zeige Bild in Lightbox:', index);
        
        const img = lightboxImages[index];
        const lightboxImg = lightboxElement.querySelector('img');
        
        // Größte Version aus srcset extrahieren
        const fullSize = getFullSizeFromSrcset(img);
        console.log('Verwende Vollbild-URL:', fullSize);
        
        lightboxImg.src = fullSize;
        lightboxImg.alt = img.alt || '';
        
        // Navigation-Buttons ein/ausblenden
        const prevBtn = lightboxElement.querySelector('.prev');
        const nextBtn = lightboxElement.querySelector('.next');
        
        prevBtn.style.display = index > 0 ? 'block' : 'none';
        nextBtn.style.display = index < lightboxImages.length - 1 ? 'block' : 'none';
    }

    function closeLightbox() {
        console.log('Schließe Lightbox');
        if (lightboxElement) {
            lightboxElement.classList.remove('active');
            document.body.style.overflow = '';
        }
    }

    function prevImage() {
        if (currentIndex > 0) {
            currentIndex--;
            showImageInLightbox(currentIndex);
            console.log('Vorheriges Bild:', currentIndex);
        }
    }

    function nextImage() {
        if (currentIndex < lightboxImages.length - 1) {
            currentIndex++;
            showImageInLightbox(currentIndex);
            console.log('Nächstes Bild:', currentIndex);
        }
    }

    // Hilfsfunktion: Größte Bildversion aus srcset extrahieren
    function getFullSizeFromSrcset(img) {
        const srcset = img.getAttribute('srcset');
        if (!srcset) {
            console.log('Kein srcset gefunden, verwende src:', img.src);
            return img.src;
        }

        // Srcset parsen und größte Version finden
        const sources = srcset.split(',').map(source => {
            const parts = source.trim().split(' ');
            const url = parts[0];
            const width = parseInt(parts[1]) || 0;
            return { url, width };
        });

        // Nach Breite sortieren und größte nehmen
        sources.sort((a, b) => b.width - a.width);
        
        const fullSize = sources[0] ? sources[0].url : img.src;
        console.log('Extrahierte Vollbild-URL aus srcset:', fullSize);
        return fullSize;
    }

    // Responsive Neuinitialisierung
    let resizeTimer;
    window.addEventListener('resize', () => {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(() => {
            console.log('Fenster-Resize: Gallery neu initialisiert');
            initGalleryMasonryer();
        }, 300);
    });
})();
JS;
    }

    public function output_custom_styles()
    {
        $gap = absint(get_option('gallery_gap', 10));
        $radius = esc_attr(get_option('gallery_radius', '0px'));
        $random_placement = get_option('random_placement', false) ? 'dense' : 'row';

        ?>
        <style>
            /* Basis Gallery Layout */
            .wp-block-gallery.masonryer-active.has-nested-images,
            .wp-block-gallery.masonryer-active.is-layout-flex,
            .wp-block-gallery.masonryer-active {
                margin: 0 !important;
                display: grid !important;
                grid-template-columns: repeat(var(--gallery-columns, 3), 1fr) !important;
                gap: <?php echo $gap; ?>px !important;
                grid-auto-flow: <?php echo $random_placement; ?> !important;
                position: relative;
                grid-auto-rows: calc((100vw - <?php echo $gap * 4; ?>px) / var(--gallery-columns, 3)) !important;
                flex-direction: unset !important;
                flex-wrap: unset !important;
                align-items: unset !important;
                justify-content: unset !important;
            }

            /* Container Query Support für moderne Browser */
            @supports (container-type: inline-size) {
                .wp-block-gallery.masonryer-active {
                    container-type: inline-size;
                    grid-auto-rows: calc((100cqw - <?php echo $gap * 2; ?>px) / var(--gallery-columns, 3)) !important;
                }
            }

            /* Basis Bild-Item Styling */
            .wp-block-gallery.masonryer-active .wp-block-image.masonryer-item,
            .wp-block-gallery.masonryer-active figure.wp-block-image.masonryer-item {
                margin: 0 !important;
                text-align: center;
                overflow: hidden;
                display: flex !important;
                position: relative;
                flex: none !important;
                width: auto !important;
                max-width: none !important;
                min-width: 0 !important;
            }

            /* Landscape Bilder (breiter als hoch) */
            .wp-block-gallery.masonryer-active .wp-block-image.masonryer-landscape,
            .wp-block-gallery.masonryer-active figure.wp-block-image.masonryer-landscape {
                grid-column: span 2 !important;
                aspect-ratio: unset !important;
            }

            /* Portrait Bilder (höher als breit) */
            .wp-block-gallery.masonryer-active .wp-block-image.masonryer-portrait,
            .wp-block-gallery.masonryer-active figure.wp-block-image.masonryer-portrait {
                grid-row: span 2 !important;
                aspect-ratio: unset !important;
            }

            /* Quadratische Bilder */
            .wp-block-gallery.masonryer-active .wp-block-image.masonryer-square,
            .wp-block-gallery.masonryer-active figure.wp-block-image.masonryer-square {
                aspect-ratio: unset !important;
            }

            /* Bild-Styling */
            .wp-block-gallery.masonryer-active .wp-block-image.masonryer-item img,
            .wp-block-gallery.masonryer-active figure.wp-block-image.masonryer-item img, .masonryer-item.lightbox-enabled {
                width: 100% !important;
                height: 100% !important;
                object-fit: cover !important;
                display: block !important;
                border-radius: <?php echo $radius; ?>;
                max-width: none !important;
                flex: none !important;
            }

            /* Bildunterschriften */
            .wp-block-image.masonryer-item figcaption {
                position: absolute;
                bottom: 0;
                left: 0;
                right: 0;
                background: linear-gradient(transparent, rgba(0, 0, 0, 0.7));
                color: white;
                padding: 20px 15px 10px;
                margin: 0 !important;
                font-size: 14px;
                border-bottom-left-radius: <?php echo $radius; ?>;
                border-bottom-right-radius: <?php echo $radius; ?>;
            }

            /* Backdrop Filter für Bildunterschriften */
            .wp-block-gallery.has-nested-images figure.wp-block-image:has(figcaption):before {
                -webkit-backdrop-filter: blur(0) !important;
                backdrop-filter: blur(0) !important;
                border-bottom-left-radius: <?php echo $radius; ?>;
                border-bottom-right-radius: <?php echo $radius; ?>;
            }

            /* Responsive Breakpoints */
            @media (max-width: 1024px) {
                .wp-block-gallery.masonryer-active {
                    grid-template-columns: repeat(calc(var(--gallery-columns, 3) - 1), 1fr) !important;
                }
            }

            @media (max-width: 768px) {
                .wp-block-gallery.masonryer-active {
                    grid-template-columns: repeat(2, 1fr) !important;
                }

                .wp-block-image.masonryer-landscape {
                    grid-column: span 2;
                }

                .wp-block-image.masonryer-portrait {
                    grid-row: span 1;
                    aspect-ratio: 1 / 1;
                }
            }

            @media (max-width: 480px) {
                .wp-block-gallery.masonryer-active {
                    grid-template-columns: 1fr !important;
                    gap: <?php echo $gap * 1.5; ?>px !important;
                }

                .wp-block-image.masonryer-landscape,
                .wp-block-image.masonryer-portrait {
                    grid-column: span 1;
                    grid-row: span 1;
                    aspect-ratio: 16 / 9;
                }
            }
        </style>
        <?php
    }
}

new GalleryMasonryer();
