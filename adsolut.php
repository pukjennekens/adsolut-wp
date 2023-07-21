<?php
    /**
     * Plugin Name: Adsolut
     * Plugin URI: https://pixelone.nl
     * Description: Adsolut koppeling voor WordPress
     * Version: 1.0.0
     * Author: Pixel One
     * Author URI: https://pixelone.nl
     * License: GPL2
     */

    use PixelOne\Plugins\Adsolut\Plugin;

    require __DIR__ . '/vendor/autoload.php';

    define( 'ADSOLUT_PLUGIN_FILE', __FILE__ );
    define( 'ADSOLUT_PLUGIN_DIR', __DIR__ );
    define( 'ADSOLUT_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
    define( 'ADSOLUT_PLUGIN_VERSION', '1.0.0' );

    add_action( 'plugins_loaded', array( Plugin::class, 'init' ) );