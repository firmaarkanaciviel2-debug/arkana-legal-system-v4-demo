<?php
/**
 * Plugin Name: Arkana Civiel Legal Management
 * Description: Foundation for the Arkana Civiel Legal Management System inside WordPress.
 * Version: 5.0.0-alpha.1
 * Author: Arkana Civiel Law Firm
 * Requires at least: 6.4
 * Requires PHP: 8.1
 * License: GPL-2.0-or-later
 * Text Domain: arkana-civiel-legal-management
 */

defined( 'ABSPATH' ) || exit;

final class Arkana_Civiel_Legal_Management {
	const VERSION = '5.0.0-alpha.1';
	const OPTION_KEY = 'aclm_settings';

	public static function init() {
		add_action( 'init', array( __CLASS__, 'register_post_types' ) );
		add_action( 'init', array( __CLASS__, 'register_roles' ), 20 );
		add_action( 'admin_menu', array( __CLASS__, 'register_admin_menu' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'admin_assets' ) );
		add_shortcode( 'arkana_legal_dashboard', array( __CLASS__, 'dashboard_shortcode' ) );
	}

	public static function activate() {
		self::register_roles();
		self::register_post_types();
		flush_rewrite_rules();
	}

	public static function deactivate() {
		flush_rewrite_rules();
	}

	public static function register_post_types() {
		$types = array(
			'ac_client' => array( 'Klien', 'Klien', 'clients' ),
			'ac_matter' => array( 'Perkara', 'Perkara', 'matters' ),
			'ac_request' => array( 'Permintaan Hukum', 'Permintaan Hukum', 'legal requests' ),
			'ac_task' => array( 'Tugas', 'Tugas', 'tasks' ),
			'ac_deadline' => array( 'Deadline', 'Deadline', 'deadlines' ),
			'ac_document' => array( 'Dokumen', 'Dokumen', 'documents' ),
			'ac_retainer' => array( 'Retainer', 'Retainer', 'retainers' ),
		);

		foreach ( $types as $type => $labels ) {
			register_post_type(
				$type,
				array(
					'labels' => array(
						'name' => $labels[0],
						'singular_name' => $labels[1],
					),
					'public' => false,
					'show_ui' => true,
					'show_in_menu' => 'arkana-legal-management',
					'show_in_rest' => true,
					'supports' => array( 'title', 'editor', 'author', 'custom-fields' ),
					'capability_type' => array( $type, $type . 's' ),
					'map_meta_cap' => true,
				)
			);
		}
	}

	public static function register_roles() {
		$roles = array(
			'ac_managing_partner' => 'Managing Partner',
			'ac_partner' => 'Partner',
			'ac_lawyer' => 'Lawyer',
			'ac_paralegal' => 'Paralegal',
			'ac_finance' => 'Finance',
			'ac_client_admin' => 'Client Admin',
			'ac_client_user' => 'Client User',
		);

		$base_caps = array(
			'read' => true,
			'upload_files' => true,
		);

		foreach ( $roles as $slug => $name ) {
			if ( ! get_role( $slug ) ) {
				add_role( $slug, $name, $base_caps );
			}
		}

		$internal_roles = array( 'ac_managing_partner', 'ac_partner', 'ac_lawyer', 'ac_paralegal', 'ac_finance' );
		$object_types = array( 'ac_client', 'ac_matter', 'ac_request', 'ac_task', 'ac_deadline', 'ac_document', 'ac_retainer' );

		foreach ( $internal_roles as $role_slug ) {
			$role = get_role( $role_slug );
			if ( ! $role ) {
				continue;
			}
			$role->add_cap( 'manage_arkana_legal' );
			foreach ( $object_types as $type ) {
				$role->add_cap( 'edit_' . $type );
				$role->add_cap( 'read_' . $type );
				$role->add_cap( 'delete_' . $type );
				$role->add_cap( 'publish_' . $type );
			}
		}

		$mp = get_role( 'ac_managing_partner' );
		if ( $mp ) {
			$mp->add_cap( 'manage_options' );
		}
	}

	public static function register_admin_menu() {
		add_menu_page(
			'Arkana Civiel Legal Management',
			'Legal Management',
			'manage_arkana_legal',
			'arkana-legal-management',
			array( __CLASS__, 'render_dashboard' ),
			'dashicons-portfolio',
			25
		);
	}

	public static function admin_assets( $hook ) {
		if ( 'toplevel_page_arkana-legal-management' !== $hook ) {
			return;
		}
		wp_register_style( 'aclm-admin', false, array(), self::VERSION );
		wp_enqueue_style( 'aclm-admin' );
		wp_add_inline_style( 'aclm-admin', self::dashboard_css() );
	}

	private static function dashboard_css() {
		return '.aclm-wrap{max-width:1200px}.aclm-hero{background:#071b2e;color:#fff;padding:28px 32px;border-radius:14px;margin:20px 0}.aclm-eyebrow{color:#dfaf45;font-weight:700;letter-spacing:.12em;text-transform:uppercase;font-size:11px}.aclm-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:16px;margin:18px 0}.aclm-card{background:#fff;border:1px solid #dfe5ea;border-radius:12px;padding:20px}.aclm-value{font-size:30px;font-weight:700;color:#071b2e}.aclm-label{color:#64748b;margin-top:4px}.aclm-note{background:#f7f6f2;border-left:4px solid #dfaf45;padding:14px 16px;margin-top:18px}@media(max-width:900px){.aclm-grid{grid-template-columns:repeat(2,minmax(0,1fr))}}@media(max-width:600px){.aclm-grid{grid-template-columns:1fr}}';
	}

	public static function render_dashboard() {
		if ( ! current_user_can( 'manage_arkana_legal' ) ) {
			wp_die( esc_html__( 'Anda tidak memiliki akses ke Legal Management.', 'arkana-civiel-legal-management' ) );
		}
		$count = static function( $post_type ) {
			$result = wp_count_posts( $post_type );
			return isset( $result->publish ) ? (int) $result->publish : 0;
		};
		?>
		<div class="wrap aclm-wrap">
			<div class="aclm-hero">
				<div class="aclm-eyebrow">Arkana Civiel</div>
				<h1 style="color:#fff;margin-bottom:8px;">Legal Management</h1>
				<p style="margin:0;color:#d9e2ea;">V5 WordPress foundation — satu pusat untuk klien, perkara, permintaan hukum, tugas, deadline, dokumen, dan retainer.</p>
			</div>
			<div class="aclm-grid">
				<?php self::metric_card( 'Klien', $count( 'ac_client' ) ); ?>
				<?php self::metric_card( 'Perkara', $count( 'ac_matter' ) ); ?>
				<?php self::metric_card( 'Permintaan Hukum', $count( 'ac_request' ) ); ?>
				<?php self::metric_card( 'Retainer', $count( 'ac_retainer' ) ); ?>
			</div>
			<div class="aclm-card">
				<h2>V5 Foundation aktif</h2>
				<p>Modul dasar sudah terdaftar sebagai WordPress custom post types dan role. Tahap berikutnya akan menambahkan relasi Client → Matter → Request → Task → Document serta portal klien dan permission berbasis perkara.</p>
			</div>
			<div class="aclm-note"><strong>Keamanan:</strong> V5 tidak menyimpan credential, API key, atau dokumen perkara sensitif di source code. Private document delivery dan audit log akan dibangun sebelum production use.</div>
		</div>
		<?php
	}

	private static function metric_card( $label, $value ) {
		echo '<div class="aclm-card"><div class="aclm-value">' . esc_html( $value ) . '</div><div class="aclm-label">' . esc_html( $label ) . '</div></div>';
	}

	public static function dashboard_shortcode() {
		if ( ! is_user_logged_in() ) {
			return '<p>Silakan login untuk mengakses Legal Management.</p>';
		}
		ob_start();
		self::render_dashboard();
		return ob_get_clean();
	}
}

Arkana_Civiel_Legal_Management::init();
register_activation_hook( __FILE__, array( 'Arkana_Civiel_Legal_Management', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'Arkana_Civiel_Legal_Management', 'deactivate' ) );
