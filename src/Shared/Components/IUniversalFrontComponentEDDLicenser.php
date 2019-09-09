<?php
namespace shellpress\v1_3_76\src\Shared\Components;

use shellpress\v1_3_76\src\Shared\RestModels\UniversalFrontResponse;
use WP_Error;
use WP_REST_Request;

/**
 * @author jakubkuranda@gmail.com
 * Date: 29.08.2019
 * Time: 15:00
 */

abstract class IUniversalFrontComponentEDDLicenser extends IUniversalFrontComponent {

	/** @var string */
	private $_apiUrl = '';

	/** @var string */
	private $_productId = '';

	/**
	 * Returns name of shortcode.
	 *
	 * @return string
	 */
	public function getShortCodeName() {

		return sanitize_key( __CLASS__ );

	}

	/**
	 * Returns array of action names to refresh this shortcode on.
	 *
	 * @return string[]
	 */
	public function getActionsToRefreshOn() {

		return array();

	}

	/**
	 * Returns array of action names to submit this shortcode on.
	 *
	 * @return string[]
	 */
	public function getActionsToSubmitOn() {

		return array();

	}

	/**
	 * Called when front end form is sent to rest API.
	 * Returns UniversalFrontResponse object.
	 *
	 * @param UniversalFrontResponse $universalFrontResponse
	 * @param WP_REST_Request        $request
	 *
	 * @return UniversalFrontResponse
	 */
	protected function processUniversalFrontResponse( $universalFrontResponse, $request ) {

		return $universalFrontResponse;

	}

	/**
	 * Returns inner component's HTML based on request.
	 * Hints:
	 * - this method is designed to be used by developers by packing it inside UniversalFrontResponse
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return string
	 */
	public function getInnerHtml( $request ) {

		$html = '';

		return $html;

	}

	/**
	 * @return string
	 */
	private function _getApiUrl() {

		return $this->_apiUrl;

	}

	/**
	 * Sets API url. This should be your EDD store website.
	 *
	 * @param string $apiUrl
	 *
	 * @return void
	 */
	public function setApiUrl( $apiUrl ) {

		$this->_apiUrl = $apiUrl;

	}

	/**
	 * @return string
	 */
	private function _getProductId() {

		return $this->_productId;

	}

	/**
	 * Sets product ID from your EDD store.
	 *
	 * @param string $productId
	 *
	 * @return void
	 */
	public function setProductId( $productId ) {

		$this->_productId = $productId;

	}

	/**
	 * Sets license to operate with.
	 *
	 * @return bool
	 */
	private function _setLicense( $license ) {

		return update_option( sanitize_key( __CLASS__ ) . '_license', $license );

	}

	/**
	 * @return string
	 */
	private function _getLicense() {

		return get_option( sanitize_key( __CLASS__ ) . '_license', '' );

	}

	/**
	 * Sets license data.
	 *
	 * @param array
	 *
	 * @return bool
	 */
	private function _setCachedData( $data ) {

		return update_option( sanitize_key( __CLASS__ ) . '_data', (array) $data );

	}

	/**
	 * Returns cached license data.
	 *
	 * @return array
	 */
	private function _getCachedData() {

		return (array) get_option( sanitize_key( __CLASS__ ) . '_data', array() );

	}

	/**
	 * @return WP_Error|bool
	 */
	public function remoteActivateLicense(){

		//  ----------------------------------------
		//  Check requirements
		//  ----------------------------------------

		if( ! $this->_getApiUrl() ) return new WP_Error( '', 'API url is not defined.' );
		if( ! $this->_getProductId() ) return new WP_Error( '', 'Product ID is not defined.' );
		if( ! $this->_getLicense() ) return new WP_Error( '', 'License is not defined.' );

		//  ----------------------------------------
		//  Make request
		//  ----------------------------------------

		//  Prepare URL with arguments.
		$fullUrl = add_query_arg( array(
			'edd_action'        =>  'activate_license',
			'item_id'           =>  $this->_getProductId(),
			'license'           =>  $this->_getLicense(),
			'url'               =>  get_home_url()
		), $this->_apiUrl );

		if( is_wp_error( $response = wp_remote_get( $fullUrl ) ) ){

			return $response;

		} else {

			$responseBody = json_decode( wp_remote_retrieve_body( $response ) );

			if( $responseBody ){

				$this->_setCachedData( $responseBody );

				switch( $this::s()->get( $responseBody, 'license' ) ){

					case 'valid':
						return true;
						break;

					case 'invalid':
						return false;
						break;

					default:
						return false;
						break;

				}

			} else {

				return new WP_Error( '', 'Remote data has wrong format.' );

			}

		}

	}

	//  ================================================================================
	//
	//  ================================================================================

}