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

            // Price hook
            add_filter( 'woocommerce_product_get_price', [ __CLASS__, 'get_product_price' ], 10, 2 );
        }

        /**
         * Get the product price
         * @param float $price The price
         * @param \WC_Product $product The product
         * @return float
         */
        public static function get_product_price( $price, $product )
        {
            $price = get_adsolut_product_price_by_product_id( $product->get_id(), 1 );

            return $price;
        }
    }