<?php
    namespace PixelOne\Plugins\Adsolut;
    defined( 'ABSPATH' ) || die;

    /**
     * Utils class for the Adsolut WordPress integration
     * This class has all the utility functions inside
     */
    class Utils
    {
        /**
         * Render templates from the templates folder
         * @param string $template The template name
         * @param array $data The data to pass to the template
         * @return void
         */
        public static function render_template( $template, $data = array() )
        {
            $template = ADSOLUT_PLUGIN_DIR . '/src/templates/' . $template . '.php';
            if ( file_exists( $template ) ) {
                extract( $data );
                include $template;
            }
        }
    }