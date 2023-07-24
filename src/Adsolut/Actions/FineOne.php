<?php
    namespace PixelOne\Connectors\Adsolut\Actions;

    trait FineOne
    {
        use BaseTrait;

        /**
         * @param string|int $id
         * @throws \PixelOne\Connectors\Adsolut\AdsolutException
         * @return mixed
         */
        public function find( $id )
        {
            $endpoint = $this->endpoint() . '/' . $id;
            $result = $this->connection()->get( $this->source(), $this->version(), $endpoint, $this->without_administration_id(), false, array(), array() );
            return $this->collection_from_result( $result );
        }
    }