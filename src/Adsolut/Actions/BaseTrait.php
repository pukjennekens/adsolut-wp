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
         * @see \PixelOne\Connectors\Adsolut\Model::api_version();
         * @return string
         */
        abstract protected function api_version();

        /**
         * @see \PixelOne\Connectors\Adsolut\Model::api();
         * @return string
         */
        abstract protected function api();

        /**
         * @see \PixelOne\Connectors\Adsolut\Model::endpoint();
         * @return string
         */
        abstract protected function endpoint();
    }