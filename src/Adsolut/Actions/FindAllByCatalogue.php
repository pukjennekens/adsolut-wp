<?php
    namespace PixelOne\Connectors\Adsolut\Actions;

    trait FindAllByCatalogue
    {
        use BaseTrait;

        /**
         * @param int[] $catalogue_ids
         * @param array $params
         * @param array $headers
         * @throws \PixelOne\Connectors\Adsolut\AdsolutException
         * @return mixed
         */
        public function get_by_catalogue_ids( $catalogue_ids, $params = array(), $headers = array() )
        {
            $params = array_merge( $params, array( 'CatalogueCodes' => implode( ',', $catalogue_ids ) ) );

            $result = $this->connection()->get( $this->source(), $this->version(), $this->endpoint() . '/byCatalogue', $this->without_administration_id(), false, $params, $headers );
            return $this->collection_from_result( $result );
        }

        /**
         * @param int[] $catalogue_ids
         * @param array $params
         * @param array $headers
         * @throws \PixelOne\Connectors\Adsolut\AdsolutException
         * @return mixed
         */
        public function get_all_by_catalogue_ids( $catalogue_ids, $params = array(), $headers = array() )
        {
            $params = array_merge( $params, array( 'CatalogueCodes' => implode( ',', $catalogue_ids ) ) );

            $result = $this->connection()->get( $this->source(), $this->version(), $this->endpoint() . '/byCatalogue', $this->without_administration_id(), true, $params, $headers );
            return $this->collection_from_result( $result );
        }
    }