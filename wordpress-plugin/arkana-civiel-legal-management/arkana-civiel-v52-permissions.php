<?php
/**
 * Plugin Name: Arkana Civiel Legal Management — V5.2 Permissions
 * Description: V5.2 role/capability enforcement layer for Arkana Civiel Legal Management.
 * Version: 5.2.0-alpha.1
 * Author: Arkana Civiel Law Firm
 * Requires at least: 6.4
 * Requires PHP: 8.1
 */

defined( 'ABSPATH' ) || exit;

final class Arkana_Civiel_V52_Permissions {
	private static $types = array( 'ac_client', 'ac_matter', 'ac_request', 'ac_task', 'ac_deadline', 'ac_document', 'ac_retainer' );
	private static $internal_roles = array( 'ac_managing_partner', 'ac_partner', 'ac_lawyer', 'ac_paralegal', 'ac_finance' );

	public static function init() {
		add_action( 'init', array( __CLASS__, 'enforce_roles' ), 30 );
		add_filter( 'map_meta_cap', array( __CLASS__, 'protect_objects' ), 20, 4 );
		add_filter( 'rest_pre_dispatch', array( __CLASS__, 'protect_rest' ), 20, 3 );
	}

	public static function enforce_roles() {
		$matrix = array(
			'ac_managing_partner' => array( 'ac_client','ac_matter','ac_request','ac_task','ac_deadline','ac_document','ac_retainer' ),
			'ac_partner' => array( 'ac_client','ac_matter','ac_request','ac_task','ac_deadline','ac_document','ac_retainer' ),
			'ac_lawyer' => array( 'ac_client','ac_matter','ac_request','ac_task','ac_deadline','ac_document' ),
			'ac_paralegal' => array( 'ac_client','ac_matter','ac_request','ac_task','ac_deadline','ac_document' ),
			'ac_finance' => array( 'ac_client','ac_retainer' ),
			'ac_client_admin' => array(),
			'ac_client_user' => array(),
		);

		foreach ( $matrix as $role_slug => $allowed_types ) {
			$role = get_role( $role_slug );
			if ( ! $role ) { continue; }
			$role->remove_cap( 'manage_options' );
			foreach ( self::$types as $type ) {
				foreach ( array( 'edit','read','delete','publish' ) as $action ) {
					$cap = $action . '_' . $type;
					if ( in_array( $type, $allowed_types, true ) ) {
						$role->add_cap( $cap );
					} else {
						$role->remove_cap( $cap );
					}
				}
			}
		}

		$mp = get_role( 'ac_managing_partner' );
		if ( $mp ) { $mp->add_cap( 'manage_arkana_legal' ); }
		foreach ( array( 'ac_partner','ac_lawyer','ac_paralegal','ac_finance' ) as $role_slug ) {
			$role = get_role( $role_slug );
			if ( $role ) { $role->add_cap( 'manage_arkana_legal' ); }
		}
	}

	public static function protect_objects( $caps, $cap, $user_id, $args ) {
		if ( ! in_array( $cap, array( 'read','edit_post','delete_post' ), true ) || empty( $args[0] ) ) {
			return $caps;
		}
		$post = get_post( (int) $args[0] );
		if ( ! $post || ! in_array( $post->post_type, self::$types, true ) ) {
			return $caps;
		}

		$user = get_userdata( $user_id );
		if ( ! $user ) { return array( 'do_not_allow' ); }
		if ( in_array( 'administrator', (array) $user->roles, true ) || in_array( 'ac_managing_partner', (array) $user->roles, true ) ) {
			return $caps;
		}
		if ( in_array( 'ac_client_admin', (array) $user->roles, true ) || in_array( 'ac_client_user', (array) $user->roles, true ) ) {
			return array( 'do_not_allow' );
		}
		return $caps;
	}

	public static function protect_rest( $result, $server, $request ) {
		$route = $request->get_route();
		if ( false === strpos( $route, '/wp/v2/ac_' ) ) { return $result; }
		if ( ! is_user_logged_in() ) {
			return new WP_Error( 'aclm_rest_auth_required', 'Authentication required.', array( 'status' => 401 ) );
		}
		$user = wp_get_current_user();
		if ( in_array( 'administrator', (array) $user->roles, true ) || in_array( 'ac_managing_partner', (array) $user->roles, true ) || in_array( 'ac_partner', (array) $user->roles, true ) || in_array( 'ac_lawyer', (array) $user->roles, true ) || in_array( 'ac_paralegal', (array) $user->roles, true ) || in_array( 'ac_finance', (array) $user->roles, true ) ) {
			return $result;
		}
		return new WP_Error( 'aclm_rest_forbidden', 'You do not have access to Legal Management data.', array( 'status' => 403 ) );
	}
}

Arkana_Civiel_V52_Permissions::init();
