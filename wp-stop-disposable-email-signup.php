<?php

/*
Plugin Name: Stop Disposable Email Sign-ups
Plugin URI: http://sitepoint.com
Description: Stop users from registering a WordPress account with disposable emails.
Version: 1.0
Author: Agbonghama Collins
Author URI: http://w3guy.com
License: GPL2
*/

class Stop_Disposable_Email {

	/** @type string API key */
	static private $api_key = 'd619f9ad24052ad785d1edf65bbd33b4';

	public function __construct() {
		add_filter( 'registration_errors', array( $this, 'stop_disposable_email_signups' ), 10, 3 );
	}


	/**
	 * Check if an email is disposable or not.
	 *
	 * @param $email string email to check
	 *
	 * @return bool true if disposable or false otherwise.
	 */
	public static function is_email_disposable( $email ) {

		// get the domain part of the email address
		// e.g in hi@trashmail.com, "trashmail.com" is the domain part
		$domain = array_pop( explode( '@', $email ) );

		$endpoint = 'http://check.block-disposable-email.com/easyapi/json/' . self::$api_key . '/' . $domain;

		$request = wp_remote_get( $endpoint );

		$reponse_body = $body = wp_remote_retrieve_body( $request );

		$response_in_object = json_decode( $reponse_body );

		$domain_status = $response_in_object->domain_status;

		if ( $response_in_object->request_status == 'success' ) {

			if ( $domain_status == 'block' ) {
				return true;
			} elseif ( $domain_status == 'ok' ) {
				return false;
			}

		}

	}


	/**
	 * Stop disposable email from creating an account
	 *
	 * @param $errors WP_Error Registration generated error object
	 * @param $sanitized_user_login string signup username
	 * @param $user_email string signup email
	 *
	 * @return mixed
	 */
	public function stop_disposable_email_signups( $errors, $sanitized_user_login, $user_email ) {

		if ( self::is_email_disposable( $user_email ) ) {

			$errors->add( 'disposable_email', '<strong>ERROR</strong>: Email is disposable, please try another one.' );
		}

		return $errors;

	}
}


new Stop_Disposable_Email();