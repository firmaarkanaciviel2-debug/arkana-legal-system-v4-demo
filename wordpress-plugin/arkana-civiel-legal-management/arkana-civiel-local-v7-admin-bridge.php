<?php
/**
 * Plugin Name: Arkana Civiel — Local V7 Admin Bridge
 * Description: Local QA bridge that grants the WordPress Administrator access to the Arkana Civiel Legal Management menu and V5/V7 objects.
 * Version: 7.18.2-local-fix
 * Author: Arkana Civiel Law Firm
 * License: GPL-2.0-or-later
 */

defined( 'ABSPATH' ) || exit;

function aclm_local_v7_admin_bridge_grant_caps() {
	$admin = get_role( 'administrator' );
	if ( ! $admin ) {
		return;
	}

	$admin->add_cap( 'manage_arkana_legal' );

	$types = array(
		'ac_client',
		'ac_matter',
		'ac_request',
		'ac_task',
		'ac_deadline',
		'ac_document',
		'ac_retainer',
	);

	foreach ( $types as $type ) {
		$cap_types = array( $type, $type . 's' );
		foreach ( $cap_types as $cap_type ) {
			foreach ( array( 'edit', 'read', 'delete', 'publish' ) as $action ) {
				$admin->add_cap( $action . '_' . $cap_type );
			}
		}

		// Additional capabilities WordPress may check for custom post-type UI/list screens.
		foreach ( array( 'edit_others', 'delete_others', 'delete_private', 'edit_private', 'read_private' ) as $action ) {
			$admin->add_cap( $action . '_' . $type . 's' );
		}
	}
}

add_action( 'init', 'aclm_local_v7_admin_bridge_grant_caps', 1 );
