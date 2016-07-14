<?php

namespace Flugg\Responder\Traits;

use Flugg\Responder\Contracts\Responder;
use Illuminate\Http\JsonResponse;

/**
 * Use this trait in your base test case to give you some helper methods for
 * integration testing the API responses generated by the package.
 *
 * @package Laravel Responder
 * @author  Alexander Tømmerås <flugged@gmail.com>
 * @license The MIT License
 */
trait MakesApiRequests
{
    /**
     * Assert that the response is a valid success response.
     *
     * @param  mixed $data
     * @param  int   $status
     * @return $this
     */
    protected function seeSuccess( $data = null, $status = 200 )
    {
        $response = $this->seeSuccessResponse( $data, $status );
        $this->seeSuccessData( $response->getData( true )[ 'data' ] );

        return $this;
    }

    /**
     * Assert that the response is a valid success response.
     *
     * @param  mixed $data
     * @param  int   $status
     * @return $this
     */
    protected function seeSuccessEquals( $data = null, $status = 200 )
    {
        $response = $this->seeSuccessResponse( $data, $status );
        $this->seeJsonEquals( $response->getData( true ) );

        return $this;
    }

    /**
     * Assert that the response is a valid success response.
     *
     * @param  mixed $data
     * @param  int   $status
     * @return JsonResponse
     */
    protected function seeSuccessResponse( $data = null, $status = 200 ):JsonResponse
    {
        $response = app( Responder::class )->success( $data, $status );

        $this->seeStatusCode( $response->getStatusCode() )->seeJson( [
            'success' => true,
            'status' => $response->getStatusCode()
        ] )->seeJsonStructure( [ 'data' ] );

        return $response;
    }

    /**
     * Assert that the response data contains given values.
     *
     * @param  mixed $data
     * @return $this
     */
    protected function seeSuccessData( $data = null )
    {
        collect( $data )->each( function ( $value, $key ) {
            if ( is_array( $value ) ) {
                $this->seeSuccessData( $value );
            } else {
                $this->seeJson( [ $key => $value ] );
            }
        } );

        return $this;
    }

    /**
     * Decodes JSON response and returns the data.
     *
     * @return array
     */
    protected function getSuccessData()
    {
        return $this->decodeResponseJson()[ 'data' ];
    }

    /**
     * Assert that the response is a valid error response.
     *
     * @param  string   $error
     * @param  int|null $status
     * @return $this
     */
    protected function seeError( string $error, int $status = null )
    {
        if ( ! is_null( $status ) ) {
            $this->seeStatusCode( $status );
        }

        if ( config( 'responder.status_code' ) ) {
            $this->seeJson( 'status' => $status );
        }

        return $this->seeJson( [
            'success' => false
        ] )->seeJsonSubset( [
            'error' => [
                'code' => $error
            ]
        ] );
    }

    /**
     * Asserts that the status code of the response matches the given code.
     *
     * @param  int $status
     * @return $this
     */
    abstract protected function seeStatusCode( $status );

    /**
     * Assert that the response contains JSON.
     *
     * @param  array|null $data
     * @param  bool       $negate
     * @return $this
     */
    abstract public function seeJson( array $data = null, $negate = false );

    /**
     * Assert that the JSON response has a given structure.
     *
     * @param  array|null $structure
     * @param  array|null $responseData
     * @return $this
     */
    abstract public function seeJsonStructure( array $structure = null, $responseData = null );

    /**
     * Assert that the response is a superset of the given JSON.
     *
     * @param  array $data
     * @return $this
     */
    abstract protected function seeJsonSubset( array $data );

    /**
     * Assert that the response contains an exact JSON array.
     *
     * @param  array $data
     * @return $this
     */
    abstract public function seeJsonEquals( array $data );

    /**
     * Validate and return the decoded response JSON.
     *
     * @return array
     */
    abstract protected function decodeResponseJson();
}