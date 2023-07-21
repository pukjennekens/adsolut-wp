<?php
    namespace PixelOne\Plugins\Adsolut;

use PixelOne\Plugins\Adsolut\Exceptions\APIException;

    defined( 'ABSPATH' ) || die;

    class API
    {
        /**
         * @var \PixelOne\Connectors\Adsolut\Connection $connection The Adsolut connection
         */
        private static $connection;

        /**
         * Initialize the API class.
         * @param \PixelOne\Connectors\Adsolut\Connection $connection The connection
         * @throws PixelOne\Plugins\Adsolut\APIException If the connection is not configured
         * @return void
         */
        public static function init( $connection )
        {
            if( ! $connection || ! $connection instanceof \PixelOne\Connectors\Adsolut\Connection )
                throw new APIException( __( 'The Adsolut connection is not configured', 'adsolut' ) );

            self::$connection = $connection;
        }
    }