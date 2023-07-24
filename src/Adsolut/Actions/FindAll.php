<?php
    namespace PixelOne\Connectors\Adsolut\Actions;

    trait FindAll
    {
        use BaseTrait;

        /**
         * @param array $params
         * @param array $headers
         * @throws \PixelOne\Connectors\Adsolut\AdsolutException
         * @return mixed
         */
        public function get( $params = array(), $headers = array() )
        {
            $result = $this->connection()->get( $this->source(), $this->version(), $this->endpoint(), $this->without_administration_id(), false, $params, $headers );
            return $this->collection_from_result( $result );
        }

        /**
         * @param array $params
         * @param array $headers
         * @throws \PixelOne\Connectors\Adsolut\AdsolutException
         * @return mixed
         */
        public function getAll( $params = array(), $headers = array() )
        {
            $result = $this->connection()->get( $this->source(), $this->version(), $this->endpoint(), $this->without_administration_id(), true, $params, $headers );
            return $this->collection_from_result( $result );
        }
    }