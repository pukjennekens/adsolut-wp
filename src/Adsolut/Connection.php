<?php
    namespace PixelOne\Connectors\Adsolut;

    use FileSystemCache;
    use GuzzleHttp\Client;
    use GuzzleHttp\Exception\ClientException;
    use GuzzleHttp\HandlerStack;
    use GuzzleHttp\Psr7\Request;
    use PixelOne\Connectors\Adsolut\Exceptions\AdsolutException;

    class Connection
    {
        /**
         * @var string $client_id The client ID
         */
        private $client_id;

        /**
         * @var string $client_secret The client secret
         */
        private $client_secret;

        /**
         * @var array $scopes The scopes for the Oauth2 connection
         */
        private $scopes = array( 'WK.GraphAPI.User', 'offline_access', 'WK.BE.Administrations', 'WK.BE.ERP.Base', 'WK.BE.ERP.Webshop', 'WK.BE.Documents' );

        /**
         * @var bool $test_mode Whether or not the connection is in test mode
         */
        private $test_mode;

        /**
         * @var string $authorization_code The authorization code
         */
        private $authorization_code;

        /**
         * @var string $access_token The access token
         */
        private $access_token;

        /**
         * @var string $refresh_token The refresh token
         */
        private $refresh_token;

        /**
         * @var int $expires_at The timestamp at which the access token expires
         */
        private $expires_at;

        /**
         * @var string $redirect_uri The redirect URI
         */
        private $redirect_uri;

        /**
         * @var \GuzzleHttp\Client $client The Guzzle client
         */
        private $client;

        /**
         * @var string $live_auth_url The live auth URL
         */
        private $live_auth_url = 'https://login.wolterskluwer.eu/auth/core/connect/authorize';

        /**
         * @var string $test_auth_url The test auth URL
         */
        private $test_auth_url = 'https://login-stg.wolterskluwer.eu/auth/core/connect/authorize';

        /**
         * @var string $live_token_url
         */
        private $live_token_url = 'https://login.wolterskluwer.eu/auth/core/connect/token';

        /**
         * @var string $test_token_url
         */
        private $test_token_url = 'https://login-stg.wolterskluwer.eu/auth/core/connect/token';

        /**
         * @var string $live_url The live API URL
         */
        private $live_url = 'https://api.adsolut.com';

        /**
         * @var string $test_url The test API URL
         */
        private $test_url = 'https://uat-api.wktaae.be';

        /**
         * @var callable( \PixelOne\Connectors\Adsolut\Connection $connection ) $token_update_callback The callback to execute when the access token is refreshed
         */
        private $token_update_callback;

        /**
         * @var string $administration_id The Adsolut administration ID
         */
        protected $administration_id;

        /**
         * Get the client
         * @return \GuzzleHttp\Client
         */
        public function client()
        {
            if( $this->client )
                return $this->client;

            FileSystemCache::$cacheDir = __DIR__ . '/cache';

            $handler_stack = HandlerStack::create();

            $this->client = new Client( array(
                'http_errors' => true,
                'handler'     => $handler_stack,
                'expect'      => false,
            ) );

            return $this->client;
        }

        /**
         * Refresh the access token
         * @throws \PixelOne\Connectors\Adsolut\Exceptions\AdsolutException
         * @return void
         */
        private function refresh_access_token()
        {
            $body = array(
                'form_params' => array(
                    'grant_type'    => 'refresh_token',
                    'refresh_token' => $this->refresh_token,
                    'client_id'     => $this->client_id,
                    'client_secret' => $this->client_secret,
                ),
            );

            try {
                $url = $this->test_mode ? $this->test_token_url : $this->live_token_url;
                $response = $this->client()->post( $url, $body );

                if( $response->getStatusCode() == 200 ) {
                    $response_body = json_decode( $response->getBody() );

                    $this->access_token  = $response_body->access_token;
                    $this->refresh_token = $response_body->refresh_token;
                    $this->expires_at    = time() + $response_body->expires_in;

                    if( is_callable( $this->token_update_callback ) )
                        call_user_func( $this->token_update_callback, $this );
                }
            } catch( ClientException $e ) {
                $this->logout();
                throw new AdsolutException( 'Could not refresh access token', $e->getCode(), $e );
            }
        }

        /**
         * Logout the user from Adsolut, callable when the token cannot be refreshed
         * @return void
         */
        private function logout()
        {
            $this->access_token       = null;
            $this->refresh_token      = null;
            $this->expires_at         = null;
            $this->authorization_code = null;

            if( is_callable( $this->token_update_callback ) )
                call_user_func( $this->token_update_callback, $this );
        }

        /**
         * Connect to Adsolut
         * @return \GuzzleHttp\Client
         * @throws \PixelOne\Connectors\Adsolut\Exceptions\AdsolutException
         */
        public function connect()
        {
            if( empty( $this->access_token ) && ! empty( $this->authorization_code ) )
                $this->acquire_access_token();

            return $this->client();
        }

        /**
         * Acquire an access token
         * @throws \PixelOne\Connectors\Adsolut\Exceptions\AdsolutException
         * @return void
         */
        private function acquire_access_token()
        {
            $body = array(
                'form_params' => array(
                    'grant_type'    => 'authorization_code',
                    'code'          => $this->authorization_code,
                    'client_id'     => $this->client_id,
                    'client_secret' => $this->client_secret,
                    'redirect_uri'  => $this->redirect_uri,
                ),
            );

            $url = $this->test_mode ? $this->test_token_url : $this->live_token_url;

            try {
                $response = $this->client()->post( $url, $body );

                if( $response->getStatusCode() == 200 ) {
                    $response_body = json_decode( $response->getBody() );

                    $this->access_token  = $response_body->access_token;
                    $this->refresh_token = $response_body->refresh_token;
                    $this->expires_at    = time() + $response_body->expires_in;

                    if( is_callable( $this->token_update_callback ) )
                        call_user_func( $this->token_update_callback, $this );
                } else {
                    throw new AdsolutException( 'Could not acquire access token' );
                }
            } catch( ClientException ) {
                throw new AdsolutException( 'Could not acquire access token' );
            }
        }

        /**
         * Set the client ID
         * @param string $client_id The client ID
         * @return void
         */
        public function set_client_id( $client_id )
        {
            $this->client_id = $client_id;
        }

        /**
         * Get the client ID
         * @return string
         */
        public function get_client_id()
        {
            return $this->client_id;
        }

        /**
         * Set the client secret
         * @param string $client_secret The client secret
         * @return void
         */
        public function set_client_secret( $client_secret )
        {
            $this->client_secret = $client_secret;
        }

        /**
         * Get the client secret
         * @return string
         */
        public function get_client_secret()
        {
            return $this->client_secret;
        }

        /**
         * Set the redirect URI
         * @param string $redirect_uri The redirect URI
         * @return void
         */
        public function set_redirect_uri( $redirect_uri )
        {
            $this->redirect_uri = $redirect_uri;
        }

        /**
         * Get the redirect URI
         * @return string
         */
        public function get_redirect_uri()
        {
            return $this->redirect_uri;
        }

        /**
         * Set the authorization code
         * @param string $authorization_code The authorization code
         * @return void
         */
        public function set_authorization_code( $authorization_code )
        {
            $this->authorization_code = $authorization_code;
        }

        /**
         * Get the authorization code
         * @return string
         */
        public function get_authorization_code()
        {
            return $this->authorization_code;
        }

        /**
         * Set the administration ID
         * @param string $administration_id The administration ID
         * @return void
         */
        public function set_administration_id( $administration_id )
        {
            $this->administration_id = $administration_id;
        }

        /**
         * Get the administration ID
         * @return string
         */
        public function get_administration_id()
        {
            return $this->administration_id;
        }

        /**
         * Set the token update callback
         * @param callable( \PixelOne\Connectors\Adsolut\Connection $connection ) $callback The callback to execute when the access token is refreshed
         * @return void
         */
        public function set_token_update_callback( $callback )
        {
            $this->token_update_callback = $callback;
        }

        /**
         * Set access token
         * @param string $access_token The access token
         * @return void
         */
        public function set_access_token( $access_token )
        {
            $this->access_token = $access_token;
        }

        /**
         * Get access token
         * @return string
         */
        public function get_access_token()
        {
            return $this->access_token;
        }

        /**
         * Set refresh token
         * @param string $refresh_token The refresh token
         * @return void
         */
        public function set_refresh_token( $refresh_token )
        {
            $this->refresh_token = $refresh_token;
        }

        /**
         * Get refresh token
         * @return string
         */
        public function get_refresh_token()
        {
            return $this->refresh_token;
        }

        /**
         * Set expires at
         * @param int $expires_at The expires at
         * @return void
         */
        public function set_token_expires_at( $expires_at )
        {
            $this->expires_at = $expires_at;
        }

        /**
         * Get expires at
         * @return int
         */
        public function get_expires_at()
        {
            return $this->expires_at;
        }

        /**
         * Set test mode
         * @param bool $test_mode The test mode
         * @return void
         */
        public function set_test_mode( $test_mode )
        {
            $this->test_mode = $test_mode;
        }

        /**
         * Get test mode
         * @return bool
         */
        public function get_test_mode()
        {
            return $this->test_mode;
        }

        /**
         * Get the auth URL
         * @return string
         */
        public function get_auth_url()
        {
            $url =  $this->test_mode ? $this->test_auth_url : $this->live_auth_url;

            return $url . '?' . http_build_query( array(
                'response_type' => 'code',
                'client_id'     => $this->client_id,
                'redirect_uri'  => $this->redirect_uri,
                'scope'         => $this->scopes ? implode( ' ', $this->scopes ) : '',
            ) );
        }

        /**
         * Create a request to send to the Adsolute API
         * @param string $method The HTTP method
         * @param string $endpoint The endpoint
         * @param string $api The API to use, "acc" for Accounting, "dms" for Document Management (Basecone or ERP document management), "erp" for ERP
         * @param array $body The body
         * @param array $params The query parameters
         * @param array $headers The headers
         * @return \GuzzleHttp\Psr7\Request
         */
        public function create_request( $method, $endpoint, $api, $body = array(), $params = array(), $headers = array() )
        {
            $headers = array_merge( $headers, array(
                'Content-Type' => 'application/json-patch+json',
                'Cache-Control' => 'no-cache',
            ) );

            if( empty( $this->access_token ) )
                $this->acquire_access_token();

            if( $this->expires_at < time() )
                $this->refresh_access_token();

            if( ! empty( $this->access_token ) )
                $headers['Authorization'] = 'Bearer ' . $this->access_token;

            if( ! empty( $params ) )
                $endpoint .= '?' . http_build_query( $params );

            $request = new Request( $method, $this->format_url( $endpoint, $api ), $headers, json_encode( $body ) );
            return $request;
        }

        /**
         * Format the URL to include the administration ID
         * @param string $endpoint
         * @param string $api The API to use, "acc" for Accounting, "dms" for Document Management (Basecone or ERP document management), "erp" for ERP
         * @param string $api_version The API version to use, defaults to V1
         * @return string
         */
        private function format_url( $endpoint, $api = null, $api_version = 'V1' )
        {
            $url = $this->test_mode ? $this->test_url : $this->live_url;

            return $url . '/' . ( $api ? $api . '/' : '' ) . $api_version . '/' . ( $this->administration_id ? $this->administration_id . '/' : '' ) . $endpoint;
        }

        /**
         * Get a connection without an administration ID
         * @return \PixelOne\Connectors\Adsolut\Connection
         */
        public function without_administration_id()
        {
            $clone = clone $this;
            $clone->administration_id = null;

            return $clone;
        }

        /**
         * Make a request
         * @param string $method The HTTP method
         * @param string $endpoint The endpoint
         * @param string $api The API to use, "acc" for Accounting, "dms" for Document Management (Basecone or ERP document management), "erp" for ERP
         * @param array $body The body
         * @param array $params The query parameters
         * @param array $headers The headers
         * @throws \PixelOne\Connectors\Adsolut\AdsolutException
         * @return \GuzzleHttp\Psr7\Response
         */
        public function request( $method, $endpoint, $api, $body = array(), $params = array(), $headers = array() )
        {
            $request = $this->create_request( $method, $endpoint, $api );
            $response = $this->client->send( $request );
            
            return $this->format_response( $response );
        }

        /**
         * Make a cached request, will return the cached response if it exists
         * @param string $method The HTTP method
         * @param string $endpoint The endpoint
         * @param string $api The API to use, "acc" for Accounting, "dms" for Document Management (Basecone or ERP document management), "erp" for ERP
         * @param array $body The body
         * @param array $params The query parameters
         * @param array $headers The headers
         * @throws \PixelOne\Connectors\Adsolut\AdsolutException
         * @return \GuzzleHttp\Psr7\Response
         */
        public function cached_request( $method, $endpoint, $api, $body = array(), $params = array(), $headers = array() )
        {
            $cache_key_string = md5( $method . $endpoint . $api . json_encode( $body ) . json_encode( $params ) . json_encode( $headers ) );
            $cache_key        = FileSystemCache::generateCacheKey( $cache_key_string );
            $found            = FileSystemCache::retrieve( $cache_key );

            if( $found )
                return $found;

            $response = $this->request( $method, $endpoint, $api, $body, $params, $headers );
            FileSystemCache::store( $cache_key, $response, 60 * 15 );

            return $response;
        }

        /**
         * Format the response to a more readable format
         * @param \GuzzleHttp\Psr7\Response $response The response
         * @throws \PixelOne\Connectors\Adsolut\AdsolutException
         * @return array
         */
        public function format_response( $response )
        {
            try {
                \GuzzleHttp\Psr7\Message::rewindBody( $response );
                $json = json_decode( $response->getBody()->getContents(), true );

                return $json;
            } catch( \RuntimeException $e ) {
                throw new AdsolutException( 'Invalid response from Adsolut: ' . $e->getMessage() );
            }
        }
    }