<?php
/**
 * Plugin Name: Gallery Masonryer
 * Description: Erweitert Gutenberg Gallery Blocks mit Orientierungs-basierten Grid-Layouts.
 * Version: 1.2.0
 * Author: jado GmbH
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Direktzugriff verhindern

class GalleryMasonryer {

    public function __construct() {
        add_action('admin_menu', [$this, 'add_settings_page']);
        add_action('admin_init', [$this, 'register_settings']);

        add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);
        add_action('wp_head', [$this, 'output_custom_styles']);
    }

    public function add_settings_page() {
        add_options_page(
                'Gallery Masonryer',
                'Gallery Masonryer',
                'manage_options',
                'gallery-masonryer',
                [$this, 'settings_page_html']
        );
    }

    public function register_settings() {
        register_setting('gallery_masonryer_options', 'gallery_gap', [
                'type' => 'integer',
                'sanitize_callback' => 'absint',
                'default' => 10,
        ]);
        register_setting('gallery_masonryer_options', 'gallery_radius', [
                'type' => 'string',
                'sanitize_callback' => function($val){ return sanitize_text_field($val); },
                'default' => '0px',
        ]);
        register_setting('gallery_masonryer_options', 'random_placement', [
                'type' => 'boolean',
                'sanitize_callback' => function($val){ return (bool)$val; },
                'default' => false,
        ]);
    }

    public function settings_page_html() {
        ?>
        <div class="wrap">
            <h1>Gallery Masonryer Einstellungen</h1>
            <form method="post" action="options.php">
                <?php settings_fields('gallery_masonryer_options'); ?>
                <table class="form-table">
                    <tr>
                        <th scope="row"><label for="gallery_gap">Gap zwischen Bildern</label></th>
                        <td><input type="number" id="gallery_gap" name="gallery_gap" value="<?php echo esc_attr(get_option('gallery_gap', 10)); ?>" min="0" max="50"> px</td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="gallery_radius">Border Radius</label></th>
                        <td><input type="text" id="gallery_radius" name="gallery_radius" value="<?php echo esc_attr(get_option('gallery_radius', '0px')); ?>" placeholder="z.B. 8px oder 0.5rem"></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="random_placement">Zufällige Platzierung</label></th>
                        <td>
                            <input type="checkbox" id="random_placement" name="random_placement" value="1" <?php checked(1, get_option('random_placement', false)); ?>>
                            <label for="random_placement">CSS Grid Dense Auto-Placement verwenden</label>
                        </td>
                    </tr>
                </table>
                <?php submit_button(); ?>
            </form>
            <div style="margin-top: 20px; padding: 15px; background: #f9f9f9; border-left: 4px solid #0073aa;">
                <h3>Hinweis:</h3>
                <p><strong>Bildgrößen:</strong></p>
                <ul>
                    <li><strong>Landscape:</strong> 2x1 Verhältnis (nimmt 2 Spalten ein)</li>
                    <li><strong>Portrait:</strong> 1x2 Verhältnis (nimmt 2 Zeilen ein)</li>
                    <li><strong>Square:</strong> 1x1 Verhältnis</li>
                </ul>
                <p>Die Anzahl der Spalten wird aus den Gutenberg Gallery Block Einstellungen übernommen.</p>
            </div>
        </div>
        <?php
    }

    public function enqueue_assets() {
        wp_register_script(
                'gallery-masonryer',
                '',
                [],
                '1.2.0',
                true
        );
        wp_enqueue_script('gallery-masonryer');
        wp_add_inline_script('gallery-masonryer', $this->get_script());
    }

    private function get_script() {
        return <<<JS
    (function(){
        // Warte auf DOM ready
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initGalleryMasonryer);
        } else {
            initGalleryMasonryer();
        }

        function initGalleryMasonryer() {
            const galleries = document.querySelectorAll('.wp-block-gallery');
            
            galleries.forEach(gallery => {
                // Extrahiere Spaltenanzahl aus Gutenberg CSS-Variablen oder Klassen
                const computedStyle = window.getComputedStyle(gallery);
                let columns = extractColumnsFromGallery(gallery);
                
                // Setze CSS Custom Property für die Spaltenanzahl
                gallery.style.setProperty('--gallery-columns', columns);
                gallery.classList.add('masonryer-active');
                
                // Verarbeite alle Bilder
                const images = gallery.querySelectorAll('.wp-block-image img');
                let loadedImages = 0;
                
                images.forEach(img => {
                    if (img.complete && img.naturalHeight !== 0) {
                        setImageOrientation(img);
                        loadedImages++;
                    } else {
                        img.addEventListener('load', () => {
                            setImageOrientation(img);
                            loadedImages++;
                            if (loadedImages === images.length) {
                                gallery.classList.add('masonryer-loaded');
                            }
                        });
                    }
                });
                
                if (loadedImages === images.length) {
                    gallery.classList.add('masonryer-loaded');
                }
            });
        }

        function extractColumnsFromGallery(gallery) {
            // Versuche Spaltenanzahl aus verschiedenen Quellen zu extrahieren
            
            // 1. CSS Custom Property (neuere Gutenberg Versionen)
            const computedStyle = window.getComputedStyle(gallery);
            const cssColumns = computedStyle.getPropertyValue('--wp--style--unstable-gallery-gap');
            
            // 2. Aus Klassen (z.B. columns-3)
            const classList = Array.from(gallery.classList);
            const columnClass = classList.find(cls => cls.startsWith('columns-'));
            if (columnClass) {
                const num = parseInt(columnClass.replace('columns-', ''));
                if (num && num > 0) return num;
            }
            
            // 3. Aus data-Attributen
            const dataColumns = gallery.getAttribute('data-columns');
            if (dataColumns) {
                const num = parseInt(dataColumns);
                if (num && num > 0) return num;
            }
            
            // 4. Aus CSS grid-template-columns
            const gridColumns = computedStyle.gridTemplateColumns;
            if (gridColumns && gridColumns !== 'none') {
                const matches = gridColumns.match(/repeat\\((\\d+),/);
                if (matches) return parseInt(matches[1]);
                
                const fractionCount = (gridColumns.match(/1fr/g) || []).length;
                if (fractionCount > 0) return fractionCount;
            }
            
            // 5. Fallback: Zähle tatsächliche Kinder in der ersten Reihe
            const items = gallery.querySelectorAll('.wp-block-image');
            if (items.length > 0) {
                // Einfache Heuristik basierend auf der Gesamtanzahl
                if (items.length <= 2) return 2;
                if (items.length <= 6) return 3;
                if (items.length <= 12) return 4;
                return 5;
            }
            
            return 3; // Standard-Fallback
        }

        function setImageOrientation(img) {
            const parent = img.closest('.wp-block-image');
            if (!parent) return;
            
            const { naturalWidth: w, naturalHeight: h } = img;
            const ratio = w / h;
            
            // Entferne alte Klassen
            parent.classList.remove('masonryer-landscape', 'masonryer-portrait', 'masonryer-square');
            
            // Setze neue Orientierungsklassen
            if (ratio > 1.2) {
                parent.classList.add('masonryer-landscape');
            } else if (ratio < 0.8) {
                parent.classList.add('masonryer-portrait');
            } else {
                parent.classList.add('masonryer-square');
            }
            
            parent.classList.add('masonryer-item');
        }

        // Resize Handler
        let resizeTimer;
        window.addEventListener('resize', () => {
            clearTimeout(resizeTimer);
            resizeTimer = setTimeout(initGalleryMasonryer, 300);
        });
    })();
    JS;
    }

    public function output_custom_styles() {
        $gap = absint(get_option('gallery_gap', 10));
        $radius = esc_attr(get_option('gallery_radius', '0px'));
        $random_placement = get_option('random_placement', false) ? 'dense' : 'row';

        ?>
        <style>
            /* Override Gutenberg Gallery Styles - Mit Grid-Größen-Kompensation */
            .wp-block-gallery.masonryer-active.has-nested-images,
            .wp-block-gallery.masonryer-active.is-layout-flex,
            .wp-block-gallery.masonryer-active {
                margin: 0 !important;
                display: grid !important;
                grid-template-columns: repeat(var(--gallery-columns, 3), 1fr) !important;
                gap: <?php echo $gap; ?>px !important;
                grid-auto-flow: <?php echo $random_placement; ?> !important;
                position: relative;

                /* Grid-Zeilenhöhe basierend auf Spaltenbreite für perfekte Squares */
                grid-auto-rows: calc((100vw - <?php echo $gap * 4; ?>px) / var(--gallery-columns, 3)) !important;

                /* Überschreibe alle Flexbox-Eigenschaften */
                flex-direction: unset !important;
                flex-wrap: unset !important;
                align-items: unset !important;
                justify-content: unset !important;
            }

            /* Container-abhängige Zeilenhöhe für bessere Squares */
            @supports (container-type: inline-size) {
                .wp-block-gallery.masonryer-active {
                    container-type: inline-size;
                    grid-auto-rows: calc((100cqw - <?php echo $gap * 2; ?>px) / var(--gallery-columns, 3)) !important;
                }
            }

            /* Grid Items - Überschreibe alle Gutenberg Figure-Styles */
            .wp-block-gallery.masonryer-active .wp-block-image.masonryer-item,
            .wp-block-gallery.masonryer-active figure.wp-block-image.masonryer-item {
                margin: 0 !important;
                text-align: center;
                overflow: hidden;
                display: flex !important;
                position: relative;

                /* Überschreibe Gutenberg Flex-Eigenschaften */
                flex: none !important;
                width: auto !important;
                max-width: none !important;
                min-width: 0 !important;
            }

            /* Orientations with exact ratios - Perfekte Größen! */
            .wp-block-gallery.masonryer-active .wp-block-image.masonryer-landscape,
            .wp-block-gallery.masonryer-active figure.wp-block-image.masonryer-landscape {
                grid-column: span 2 !important;
                aspect-ratio: unset !important;
            }

            .wp-block-gallery.masonryer-active .wp-block-image.masonryer-portrait,
            .wp-block-gallery.masonryer-active figure.wp-block-image.masonryer-portrait {
                grid-row: span 2 !important;
                aspect-ratio: unset !important;
            }

            .wp-block-gallery.masonryer-active .wp-block-image.masonryer-square,
            .wp-block-gallery.masonryer-active figure.wp-block-image.masonryer-square {
                aspect-ratio: unset !important;
                /* Square nimmt genau eine Grid-Zelle ein */
            }

            /* Images - Überschreibe alle Gutenberg Styles */
            .wp-block-gallery.masonryer-active .wp-block-image.masonryer-item img,
            .wp-block-gallery.masonryer-active figure.wp-block-image.masonryer-item img {
                width: 100% !important;
                height: 100% !important;
                object-fit: cover !important;
                display: block !important;
                border-radius: <?php echo $radius; ?>;

                /* Entferne alle Gutenberg Image-Constraints */
                max-width: none !important;
                flex: none !important;
            }

            /* Links innerhalb der Items */
            .wp-block-image.masonryer-item a {
                display: block !important;
                width: 100%;
                height: 100%;
                line-height: 0;
            }

            /* Figcaptions */
            .wp-block-image.masonryer-item figcaption {
                position: absolute;
                bottom: 0;
                left: 0;
                right: 0;
                background: linear-gradient(transparent, rgba(0,0,0,0.7));
                color: white;
                padding: 20px 15px 10px;
                margin: 0 !important;
                font-size: 14px;
            }

            <?php if ($enable_hover): ?>
            /* Hover Effects */
            .wp-block-image.masonryer-item:hover {
                transform: scale(1.02);
                box-shadow: 0 8px 25px rgba(0,0,0,0.15);
                z-index: 2;
            }

            .wp-block-image.masonryer-item:hover img {
                transform: scale(1.05);
            }

            .wp-block-image.masonryer-item:hover figcaption {
                opacity: 1;
                transform: translateY(0);
            }
            <?php endif; ?>

            /* Responsive Behavior */
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

            /* Loading Animation */
            .wp-block-gallery.masonryer-active:not(.masonryer-loaded) .wp-block-image {
                opacity: 0;
                transform: scale(0.8);
            }

            .wp-block-gallery.masonryer-loaded .wp-block-image.masonryer-item {
                animation: fadeInScale 0.6s ease forwards;
            }

            .wp-block-gallery.masonryer-loaded .wp-block-image.masonryer-item:nth-child(1) { animation-delay: 0.1s; }
            .wp-block-gallery.masonryer-loaded .wp-block-image.masonryer-item:nth-child(2) { animation-delay: 0.15s; }
            .wp-block-gallery.masonryer-loaded .wp-block-image.masonryer-item:nth-child(3) { animation-delay: 0.2s; }
            .wp-block-gallery.masonryer-loaded .wp-block-image.masonryer-item:nth-child(4) { animation-delay: 0.25s; }
            .wp-block-gallery.masonryer-loaded .wp-block-image.masonryer-item:nth-child(5) { animation-delay: 0.3s; }
            .wp-block-gallery.masonryer-loaded .wp-block-image.masonryer-item:nth-child(6) { animation-delay: 0.35s; }

            @keyframes fadeInScale {
                from {
                    opacity: 0;
                    transform: scale(0.8);
                }
                to {
                    opacity: 1;
                    transform: scale(1);
                }
            }

            /* Links */
            .wp-block-image.masonryer-item a {
                display: flex;
                width: 100%;
                height: 100%;
            }
        </style>
        <?php
    }
}

new GalleryMasonryer();
