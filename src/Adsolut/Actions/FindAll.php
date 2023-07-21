<?php
    namespace PixelOne\Connectors\Adsolut\Actions;

    trait FindAll
    {
        use BaseTrait;

        /**
         * @param array $params
         * @param array $body
         * @return mixed
         */
        public function getAll( $params = array(), $body = array() )
        {
            $result = $this->connection()->request( 'GET', $this->endpoint(), $this->api(), $body, $params );
            return $result;
        }
    }