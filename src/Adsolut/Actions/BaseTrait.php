<?php
    namespace PixelOne\Connectors\Adsolut\Actions;

    trait BaseTrait
    {
        /**
         * @see \PixelOne\Connectors\Adsolut\Model::connection();
         * @return \PixelOne\Connectors\Adsolut\Connection
         */
        abstract protected function connection();

        /**
         * @see \PixelOne\Connectors\Adsolut\Model::source();
         * @return string
         */
        abstract protected function source();

        /**
         * @see \PixelOne\Connectors\Adsolut\Model::version();
         * @return string
         */
        abstract protected function version();

        /**
         * @see \PixelOne\Connectors\Adsolut\Model::endpoint();
         * @return string
         */
        abstract protected function endpoint();

        /**
         * @see \PixelOne\Connectors\Adsolut\Model::without_administration_id();
         * @return bool
         */
        abstract protected function without_administration_id();

        /**
         * @see \PixelOne\Connectors\Adsolut\Model::collection_from_result();
         * @param array $result
         * @return array
         */
        abstract protected function collection_from_result( $result );
    }