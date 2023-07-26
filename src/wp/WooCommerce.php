<?php
    namespace PixelOne\Plugins\Adsolut;

    use PixelOne\Plugins\Adsolut\Exceptions\PluginException;

    defined( 'ABSPATH' ) || die;

    /**
     * The WooCommerce class for actions related to WooCommerce
     */
    class WooCommerce
    {
        /**
         * @var \PixelOne\Connectors\Adsolut\Connection $connection The Adsolut connection
         */
        private static $connection;

        /**
         * Initialize the admin class,
         * @param \PixelOne\Connectors\Adsolut\Connection $connection The connection
         * @return void
         */
        public static function init( $connection )
        {
            if( ! $connection || ! $connection instanceof \PixelOne\Connectors\Adsolut\Connection )
                throw new PluginException( __( 'The Adsolut connection is not configured', 'adsolut' ) );

            self::$connection = $connection;
        }
    }