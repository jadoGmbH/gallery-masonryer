<?php
/**
 * Plugin Name: Gallery Masonryer (Optimized)
 * Description: Erweitert Gutenberg Gallery Blocks mit Orientierungs-basierten Grid-Layouts - Performance optimiert.
 * Version: 1.3.3
 * Author: jado GmbH
 * Text Domain: gallery-masonryer
 * Domain Path: /languages
 * License: This plugin uses Swiper.js (MIT License) – Copyright © Vladimir Kharlampidi
 */

if (!defined('ABSPATH')) exit;

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
                <div id="lightbox-preview"
                     style="width: 200px; height: 100px; border: 2px solid #ddd; border-radius: 4px; position: relative; overflow: hidden; background: linear-gradient(45deg, #f0f0f0 25%, transparent 25%), linear-gradient(-45deg, #f0f0f0 25%, transparent 25%), linear-gradient(45deg, transparent 75%, #f0f0f0 75%), linear-gradient(-45deg, transparent 75%, #f0f0f0 75%); background-size: 20px 20px; background-position: 0 0, 0 10px, 10px -10px, -10px 0px;">
                    <div id="preview-overlay"
                         style="position: absolute; inset: 0; display: flex; align-items: center; justify-content: center; color: white; font-weight: bold; font-size: 14px;">
                        <?php _e('Lightbox Hintergrund', 'gallery-masonryer'); ?>
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
            jQuery(document).ready(function ($) {
                $('.color-picker').wpColorPicker({
                    change: function (event, ui) {
                        updatePreview();
                    },
                    clear: function () {
                        updatePreview();
                    }
                });
                $('#lightbox_background_opacity').on('input', function () {
                    $('#opacity-value').text($(this).val() + '%');
                    updatePreview();
                });

                function updatePreview() {
                    var color = $('#lightbox_background_color').val() || '#000000';
                    var opacity = $('#lightbox_background_opacity').val() / 100;
                    var r = parseInt(color.substr(1, 2), 16);
                    var g = parseInt(color.substr(3, 2), 16);
                    var b = parseInt(color.substr(5, 2), 16);
                    var rgba = 'rgba(' + r + ',' + g + ',' + b + ',' + opacity + ')';
                    $('#preview-overlay').css('background-color', rgba);
                    var brightness = (r * 299 + g * 587 + b * 114) / 1000;
                    var textColor = brightness > 128 ? '#333333' : '#ffffff';
                    $('#preview-overlay').css('color', textColor);
                }

                updatePreview();
            });
        </script>
        <?php
    }


    private function get_script()
    {
        return <<<JS
(function(){
    let currentIndex = 0;
    let isInitialized = false;
    
    const initGalleryMasonryer = () => {
        if (isInitialized) return;
        
        const galleries = document.querySelectorAll('.wp-block-gallery:not(.masonryer-processed)');
        if (galleries.length === 0) return;

        // Batch-Verarbeitung für bessere Performance
        galleries.forEach((gallery) => {
            gallery.classList.add('masonryer-processed');
            const columns = extractColumnsFromGallery(gallery);
            gallery.style.setProperty('--gallery-columns', columns);
            gallery.classList.add('masonryer-active');
            
            // Intersection Observer für Lazy Loading der Orientierungsberechnung
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        processGalleryImages(entry.target);
                        observer.unobserve(entry.target);
                    }
                });
            }, { rootMargin: '50px' });
            
            observer.observe(gallery);
        });

        if (typeof GalleryMasonryerOptions !== 'undefined' && GalleryMasonryerOptions.enableLightbox) {

        }
        
        isInitialized = true;
    };

    const processGalleryImages = (gallery) => {
        const images = gallery.querySelectorAll('.wp-block-image img, figure.wp-block-image img');
        
        // Verwende requestAnimationFrame für bessere Performance
        const processImages = () => {
            images.forEach((img) => {
                if (img.complete && img.naturalHeight !== 0) {
                    setImageOrientation(img);
                } else {
                    img.addEventListener('load', () => setImageOrientation(img), { once: true });
                }
            });
        };
        
        requestAnimationFrame(processImages);
    };

    const extractColumnsFromGallery = (gallery) => {
        const classList = Array.from(gallery.classList);
        const columnClass = classList.find(cls => cls.startsWith('columns-'));
        if (columnClass) {
            const num = parseInt(columnClass.replace('columns-', ''));
            if (num && num > 0) return num;
        }
        
        const dataColumns = gallery.getAttribute('data-columns');
        if (dataColumns) {
            const num = parseInt(dataColumns);
            if (num && num > 0) return num;
        }
        return 3;
    };

    const setImageOrientation = (img) => {
        const parent = img.closest('.wp-block-image') || img.closest('figure.wp-block-image');
        if (!parent || parent.classList.contains('masonryer-processed')) return;

        const { naturalWidth: w, naturalHeight: h } = img;
        if (w === 0 || h === 0) return;

        const ratio = w / h;
        
        // Batch DOM-Updates
        const classesToRemove = ['masonryer-landscape', 'masonryer-portrait', 'masonryer-square'];
        parent.classList.remove(...classesToRemove);
        
        if (ratio > 1.2) {
            parent.classList.add('masonryer-landscape');
        } else if (ratio < 0.8) {
            parent.classList.add('masonryer-portrait');
        } else {
            parent.classList.add('masonryer-square');
        }
        
        parent.classList.add('masonryer-item', 'masonryer-processed');
        
        if (typeof GalleryMasonryerOptions !== 'undefined' && GalleryMasonryerOptions.enableLightbox) {
            parent.classList.add('lightbox-enabled');
            img.style.cursor = 'pointer';
            img.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
            }, { passive: false });
        }
    };
    
    

    const getFullSizeFromSrcset = (img) => {
        const srcset = img.getAttribute('srcset');
        if (!srcset) return img.src;
        
        const sources = srcset.split(',').map(source => {
            const parts = source.trim().split(' ');
            const url = parts[0];
            const width = parseInt(parts[1]) || 0;
            return { url, width };
        });
        
        sources.sort((a, b) => b.width - a.width);
        return sources[0] ? sources[0].url : img.src;
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initGalleryMasonryer);
    } else {
        initGalleryMasonryer();
    }

    let resizeTimeout;
    window.addEventListener('resize', () => {
        clearTimeout(resizeTimeout);
        resizeTimeout = setTimeout(() => {
            isInitialized = false;
            initGalleryMasonryer();
        }, 250);
    }, { passive: true });

    const observer = new MutationObserver((mutations) => {
        let shouldReinit = false;
        mutations.forEach((mutation) => {
            if (mutation.type === 'childList' && mutation.addedNodes.length > 0) {
                mutation.addedNodes.forEach(node => {
                    if (node.nodeType === 1 && (node.classList?.contains('wp-block-gallery') || node.querySelector?.('.wp-block-gallery'))) {
                        shouldReinit = true;
                    }
                });
            }
        });
        
        if (shouldReinit) {
            requestAnimationFrame(() => {
                isInitialized = false;
                initGalleryMasonryer();
            });
        }
    });
    
    observer.observe(document.body, {
        childList: true,
        subtree: true
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
            .wp-block-gallery.masonryer-active.has-nested-images,
            .wp-block-gallery.masonryer-active.is-layout-flex,
            .wp-block-gallery.masonryer-active {
                margin: 0 !important;
                display: grid !important;
                grid-template-columns: repeat(var(--gallery-columns, 3), 1fr) !important;
                gap: <?php echo $gap; ?>px !important;
                grid-auto-flow: <?php echo $random_placement; ?> !important;
                position: relative;
                grid-auto-rows: auto !important;
                flex-direction: unset !important;
                flex-wrap: unset !important;
                align-items: unset !important;
                justify-content: unset !important;
                transform: translateZ(0);
                will-change: auto;
                contain: layout style;
            }

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
                transform: translateZ(0);
                backface-visibility: hidden;
                will-change: transform;
            }

            .wp-block-gallery.masonryer-active .wp-block-image.masonryer-landscape,
            .wp-block-gallery.masonryer-active figure.wp-block-image.masonryer-landscape {
                grid-column: span 2 !important;
            }

            .wp-block-gallery.masonryer-active .wp-block-image.masonryer-square,
            .wp-block-gallery.masonryer-active figure.wp-block-image.masonryer-square {
                box-sizing: border-box !important;
            }

            .wp-block-gallery.masonryer-active .wp-block-image.masonryer-item,
            .wp-block-gallery.masonryer-active figure.wp-block-image.masonryer-item {
                border-radius: <?php echo $radius; ?>;
            }

            .swiper-slide img{
                border-radius: <?php echo $radius; ?>;
            }

            .wp-block-gallery.masonryer-active .wp-block-image.masonryer-item img,
            .wp-block-gallery.masonryer-active figure.wp-block-image.masonryer-item img {
                width: 100% !important;
                height: 100% !important;
                object-fit: cover !important;
                display: block !important;
                max-width: none !important;
                flex: none !important;
                transform: translateZ(0);
                backface-visibility: hidden;
                image-rendering: optimizeQuality;
                contain: layout style paint;
            }

            .wp-block-gallery.masonryer-active {
                grid-auto-rows: calc((100% - (var(--gallery-columns, 3) - 1) * <?php echo $gap; ?>px) / var(--gallery-columns));
            }

            /* Lightbox-enabled Bilder */
            .masonryer-item.lightbox-enabled img {
                cursor: pointer;
                transition: transform 0.15s ease-out !important;
            }

            .masonryer-item.lightbox-enabled img:hover {
                transform: translateZ(0) scale(1.02) !important;
            }

            /* Performance-optimierte Responsive Breakpoints */
            @media (max-width: 1024px) {
                .wp-block-gallery.masonryer-active {
                    grid-template-columns: repeat(calc(var(--gallery-columns, 3) - 1), 1fr) !important;
                }
            }


            .wp-block-gallery.masonryer-active .wp-block-image.masonryer-square {
                grid-row: span 1 !important;
            }

            .wp-block-gallery.masonryer-active .wp-block-image.masonryer-landscape {
                grid-column: span 2 !important;
                grid-row: span 1 !important;
            }

            .wp-block-gallery.masonryer-active .wp-block-image.masonryer-portrait {
                grid-column: span 1 !important;
                grid-row: span 2 !important;
            }


            /* Tablet */
            @media (max-width: 768px) {
                .wp-block-gallery.masonryer-active {
                    grid-template-columns: repeat(2, 1fr) !important;
                    grid-auto-rows: auto !important;
                }

                .wp-block-gallery.masonryer-active .wp-block-image.masonryer-square {
                    grid-column: span 1 !important;
                    grid-row: span 1 !important;
                }

                .wp-block-gallery.masonryer-active .wp-block-image.masonryer-landscape {
                    grid-column: span 2 !important;
                    grid-row: span 1 !important;
                }

                .wp-block-gallery.masonryer-active .wp-block-image.masonryer-portrait {
                    grid-column: span 1 !important;
                }
            }


            /* Handy */
            @media (max-width: 480px) {
                .wp-block-gallery.masonryer-active {
                    grid-template-columns: 1fr !important;
                    gap: <?php echo $gap; ?>px !important;
                    grid-auto-rows: auto !important;
                    grid-auto-flow: dense !important;
                }

                .wp-block-gallery.masonryer-active .wp-block-image.masonryer-square {
                    width: 100% !important;
                    grid-column: span 1 !important;
                    grid-row: auto !important;
                }

                .wp-block-gallery.masonryer-active .wp-block-image.masonryer-landscape {
                    grid-column: span 1 !important;
                    grid-row: auto !important;
                }

                .wp-block-gallery.masonryer-active .wp-block-image.masonryer-portrait {
                    grid-column: span 1 !important;
                    grid-row: auto !important;
                }
            }

            /* Mobile View: Explizite Überschreibung aller Desktop-Regeln */
            @media (max-width: 480px) {
                .wp-block-gallery.masonryer-active {
                    grid-template-columns: 1fr !important;
                    gap: <?php echo $gap; ?>px !important;
                    grid-auto-rows: auto !important;
                    grid-auto-flow: dense !important;
                }

                .wp-block-gallery.masonryer-active .wp-block-image.masonryer-square {
                    grid-column: span 1 !important;
                    grid-row: auto !important;
                }

                .wp-block-gallery.masonryer-active .wp-block-image.masonryer-landscape {
                    grid-column: span 1 !important;
                    grid-row: auto !important;
                }

                .wp-block-gallery.masonryer-active .wp-block-image.masonryer-portrait {
                    grid-column: span 1 !important;
                    grid-row: auto !important;
                }
            }

            @media (max-width: 320px) {
                .wp-block-gallery.masonryer-active {
                    grid-template-columns: 1fr !important;
                }

                .wp-block-gallery.masonryer-active .wp-block-image.masonryer-landscape,
                .wp-block-gallery.masonryer-active figure.wp-block-image.masonryer-landscape,
                .wp-block-gallery.masonryer-active .wp-block-image.masonryer-portrait,
                .wp-block-gallery.masonryer-active figure.wp-block-image.masonryer-portrait,
                .wp-block-gallery.masonryer-active .wp-block-image.masonryer-square,
                .wp-block-gallery.masonryer-active figure.wp-block-image.masonryer-square {
                    grid-column: span 1 !important;
                    grid-row: span 1 !important;
                }
            }

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
                transform: translateZ(0);
                will-change: auto;
            }

            .wp-block-gallery.has-nested-images figure.wp-block-image:has(figcaption):before {
                -webkit-backdrop-filter: blur(0) !important;
                backdrop-filter: blur(0) !important;
                border-bottom-left-radius: <?php echo $radius; ?>;
                border-bottom-right-radius: <?php echo $radius; ?>;
            }
        </style>
        <?php
    }

    public function enqueue_assets()
    {
        wp_register_script(
                'gallery-masonryer',
                '',
                [],
                '1.3.3',
                true
        );
        wp_enqueue_script('gallery-masonryer');
        wp_register_style(
                'gallery-masonryer',
                '',
                [],
                '1.3.3'
        );
        wp_enqueue_style('gallery-masonryer');

        // Plugin-Optionen ins JS übergeben (KORRIGIERT - nur ein Array!)
        $js_options = [
                'enableLightbox' => get_option('enable_lightbox', false),
                'lightboxBackgroundColor' => get_option('lightbox_background_color', '#000000'),
                'lightboxBackgroundOpacity' => get_option('lightbox_background_opacity', 90),
        ];

        wp_add_inline_script(
                'gallery-masonryer',
                'const GalleryMasonryerOptions = ' . wp_json_encode($js_options) . ';',
                'before'
        );
        wp_add_inline_script('gallery-masonryer', $this->get_script());

        if (!empty($js_options['enableLightbox'])) {
            wp_enqueue_style(
                    'swiper',
                    plugin_dir_url(__FILE__) . 'assets/swiper-bundle.min.css',
                    [],
                    '11.0.0'
            );
            wp_enqueue_script(
                    'swiper',
                    plugin_dir_url(__FILE__) . 'assets/swiper-bundle.min.js',
                    [],
                    '11.0.0',
                    true
            );


// Ersetze den gesamten $init_lightbox Teil mit diesem Code:

            $init_lightbox = '
document.addEventListener(\'DOMContentLoaded\', function() {
    if(typeof Swiper !== \'undefined\') {
        let swiperInstance = null;
        let swiperContainer = null;
        let keydownHandler = null;
        let clickHandler = null;
        
        function closeLightbox() {
            console.log(\'Closing lightbox...\');
            
            if (keydownHandler) {
                document.removeEventListener(\'keydown\', keydownHandler);
                keydownHandler = null;
            }
            
            if (clickHandler && swiperContainer) {
                swiperContainer.removeEventListener(\'click\', clickHandler);
                clickHandler = null;
            }
            
            if (swiperInstance) {
                try {
                    swiperInstance.destroy(true, true);
                } catch(e) {
                    console.log(\'Error destroying swiper:\', e);
                }
                swiperInstance = null;
            }
            
            if (swiperContainer) {
                try {
                    if (swiperContainer.parentNode) {
                        swiperContainer.parentNode.removeChild(swiperContainer);
                    }
                } catch(e) {
                    console.log(\'Error removing container:\', e);
                }
                swiperContainer = null;
            }
            
            document.body.style.overflow = \'\';
            document.body.classList.remove(\'lightbox-open\');
        }
        
        function getLightboxBackground() {
            if (typeof GalleryMasonryerOptions !== \'undefined\') {
                const color = GalleryMasonryerOptions.lightboxBackgroundColor || \'#000000\';
                const opacity = (GalleryMasonryerOptions.lightboxBackgroundOpacity || 90) / 100;
                
                const r = parseInt(color.substr(1,2), 16);
                const g = parseInt(color.substr(3,2), 16);
                const b = parseInt(color.substr(5,2), 16);
                
                return `rgba(${r}, ${g}, ${b}, ${opacity})`;
            }
            return \'rgba(0, 0, 0, 0.95)\';
        }
        
        function createLightbox(gallery, startIndex = 0) {
            if (swiperContainer) {
                closeLightbox();
                setTimeout(() => createLightbox(gallery, startIndex), 100);
                return;
            }
            
            swiperContainer = document.createElement(\'div\');
            swiperContainer.className = \'swiper lightbox-overlay\';
            swiperContainer.style.cssText = `
                position: fixed !important;
                top: 0 !important;
                left: 0 !important;
                width: 100vw !important;
                height: 100vh !important;
                background: ${getLightboxBackground()} !important;
                z-index: 999999 !important;
                display: flex !important;
                align-items: center !important;
                justify-content: center !important;
                cursor: pointer !important;
            `;
            
            const swiperWrapper = document.createElement(\'div\');
            swiperWrapper.className = \'swiper-wrapper\';
            
            const prevButton = document.createElement(\'div\');
            prevButton.className = \'swiper-button-prev\';
            prevButton.style.cssText = \'color: white !important; z-index: 1000000 !important;\';
            
            const nextButton = document.createElement(\'div\');
            nextButton.className = \'swiper-button-next\';
            nextButton.style.cssText = \'color: white !important; z-index: 1000000 !important;\';
            
            const pagination = document.createElement(\'div\');
            pagination.className = \'swiper-pagination\';
            pagination.style.cssText = \'color: white !important; z-index: 1000000 !important;\';
            
            const closeButton = document.createElement(\'div\');
            closeButton.innerHTML = \'×\';
            closeButton.style.cssText = `
                position: absolute !important;
                top: 20px !important;
                right: 30px !important;
                color: white !important;
                font-size: 50px !important;
                cursor: pointer !important;
                z-index: 1000000 !important;
                line-height: 1 !important;
                user-select: none !important;
                font-weight: bold !important;
            `;
            
            const images = gallery.querySelectorAll(\'.wp-block-image img, figure.wp-block-image img\');
            console.log(\'Found images:\', images.length);
            
            images.forEach((img, index) => {
                const slide = document.createElement(\'div\');
                slide.className = \'swiper-slide\';
                slide.style.cssText = `
                    display: flex !important;
                    align-items: center !important;
                    justify-content: center !important;
                    width: 100% !important;
                    height: 100% !important;
                    cursor: default !important;
                `;
                
                const clonedImg = img.cloneNode(true);
                clonedImg.style.cssText = `
                    max-width: calc(100vw - 40px) !important;
                    max-height: calc(100vh - 40px) !important;
                    width: auto !important;
                    height: auto !important;
                    object-fit: contain !important;
                    cursor: default !important;
                    pointer-events: none !important;
                `;
                
                slide.appendChild(clonedImg);
                swiperWrapper.appendChild(slide);
            });
            
            swiperContainer.appendChild(swiperWrapper);
            swiperContainer.appendChild(prevButton);
            swiperContainer.appendChild(nextButton);
            swiperContainer.appendChild(pagination);
            swiperContainer.appendChild(closeButton);
            
            document.body.appendChild(swiperContainer);
            document.body.classList.add(\'lightbox-open\');
            
            try {
                swiperInstance = new Swiper(swiperContainer, {
                    loop: images.length > 1,
                    initialSlide: startIndex,
                    navigation: {
                        nextEl: \'.swiper-button-next\',
                        prevEl: \'.swiper-button-prev\'
                    },
                    pagination: {
                        el: \'.swiper-pagination\',
                        clickable: true,
                        type: \'fraction\'
                    },
                    keyboard: {
                        enabled: true
                    },
                    on: {
                        init: function() {
                            console.log(\'Swiper initialized\');
                        }
                    }
                });
                console.log(\'Swiper created successfully\');
            } catch(e) {
                console.error(\'Error creating swiper:\', e);
            }
            
            closeButton.addEventListener(\'click\', function(e) {
                console.log(\'Close button clicked\');
                e.stopPropagation();
                e.preventDefault();
                closeLightbox();
            });
            
            clickHandler = function(e) {
                if (e.target === swiperContainer) {
                    console.log(\'Clicked outside, closing\');
                    closeLightbox();
                }
            };
            swiperContainer.addEventListener(\'click\', clickHandler);
            
            keydownHandler = function(e) {
                if (e.key === \'Escape\') {
                    console.log(\'ESC pressed, closing\');
                    closeLightbox();
                }
            };
            document.addEventListener(\'keydown\', keydownHandler);
            
            document.body.style.overflow = \'hidden\';
        }
        
        function attachLightboxListeners() {
            const galleries = document.querySelectorAll(\'.wp-block-gallery.masonryer-active\');
            console.log(\'Found galleries:\', galleries.length);
            
            galleries.forEach(gallery => {
                const images = gallery.querySelectorAll(\'.wp-block-image img, figure.wp-block-image img\');
                console.log(\'Gallery images:\', images.length);
                
                images.forEach((img, index) => {
                    img.removeEventListener(\'click\', img.lightboxHandler);
                    
                    img.lightboxHandler = function(e) {
                        console.log(\'Image clicked, index:\', index);
                        e.preventDefault();
                        e.stopPropagation();
                        createLightbox(gallery, index);
                    };
                    
                    img.addEventListener(\'click\', img.lightboxHandler);
                });
            });
        }
        
        attachLightboxListeners();
        
        const observer = new MutationObserver(function(mutations) {
            let shouldReattach = false;
            mutations.forEach(mutation => {
                if (mutation.addedNodes) {
                    mutation.addedNodes.forEach(node => {
                        if (node.nodeType === 1 && 
                            (node.classList?.contains(\'masonryer-active\') || 
                             node.querySelector?.(\'.masonryer-active\'))) {
                            shouldReattach = true;
                        }
                    });
                }
            });
            
            if (shouldReattach) {
                setTimeout(attachLightboxListeners, 500);
            }
        });
        
        observer.observe(document.body, {
            childList: true,
            subtree: true
        });
        
        const style = document.createElement(\'style\');
        style.textContent = \'body.lightbox-open {overflow: hidden !important;}.lightbox-overlay * {box-sizing: border-box !important;}\';
        document.head.appendChild(style);
    }
});
';
            wp_add_inline_script('swiper', $init_lightbox);
        }
    }
}

new GalleryMasonryer();
