<?php
/**
 * Plugin Name: Arkana Civiel Legal Management
 * Description: WordPress-centered Legal Management System for Arkana Civiel.
 * Version: 5.1.0-alpha.1
 * Author: Arkana Civiel Law Firm
 * Requires at least: 6.4
 * Requires PHP: 8.1
 * License: GPL-2.0-or-later
 * Text Domain: arkana-civiel-legal-management
 */

defined( 'ABSPATH' ) || exit;

final class Arkana_Civiel_Legal_Management {
	const VERSION = '5.1.0-alpha.1';
	const OPTION_KEY = 'aclm_settings';

	private static $types = array(
		'ac_client'   => array( 'Klien', 'Klien' ),
		'ac_matter'   => array( 'Perkara', 'Perkara' ),
		'ac_request'  => array( 'Permintaan Hukum', 'Permintaan Hukum' ),
		'ac_task'     => array( 'Tugas', 'Tugas' ),
		'ac_deadline' => array( 'Deadline', 'Deadline' ),
		'ac_document' => array( 'Dokumen', 'Dokumen' ),
		'ac_retainer' => array( 'Retainer', 'Retainer' ),
	);

	public static function init() {
		add_action( 'init', array( __CLASS__, 'register_post_types' ) );
		add_action( 'init', array( __CLASS__, 'register_roles' ), 20 );
		add_action( 'admin_menu', array( __CLASS__, 'register_admin_menu' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'admin_assets' ) );
		add_action( 'add_meta_boxes', array( __CLASS__, 'register_relation_meta_boxes' ) );
		add_action( 'save_post', array( __CLASS__, 'save_relation_meta' ), 10, 2 );
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
		foreach ( self::$types as $type => $labels ) {
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
		$base_caps = array( 'read' => true, 'upload_files' => true );
		foreach ( $roles as $slug => $name ) {
			if ( ! get_role( $slug ) ) {
				add_role( $slug, $name, $base_caps );
			}
		}
		$internal_roles = array( 'ac_managing_partner', 'ac_partner', 'ac_lawyer', 'ac_paralegal', 'ac_finance' );
		foreach ( $internal_roles as $role_slug ) {
			$role = get_role( $role_slug );
			if ( ! $role ) { continue; }
			$role->add_cap( 'manage_arkana_legal' );
			foreach ( array_keys( self::$types ) as $type ) {
				foreach ( array( 'edit', 'read', 'delete', 'publish' ) as $action ) {
					$role->add_cap( $action . '_' . $type );
				}
			}
		}
		$mp = get_role( 'ac_managing_partner' );
		if ( $mp ) { $mp->add_cap( 'manage_options' ); }
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
		if ( 'toplevel_page_arkana-legal-management' !== $hook ) { return; }
		wp_register_style( 'aclm-admin', false, array(), self::VERSION );
		wp_enqueue_style( 'aclm-admin' );
		wp_add_inline_style( 'aclm-admin', self::dashboard_css() );
	}

	private static function dashboard_css() {
		return '.aclm-wrap{max-width:1200px}.aclm-hero{background:#071b2e;color:#fff;padding:28px 32px;border-radius:14px;margin:20px 0}.aclm-eyebrow{color:#dfaf45;font-weight:700;letter-spacing:.12em;text-transform:uppercase;font-size:11px}.aclm-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:16px;margin:18px 0}.aclm-card{background:#fff;border:1px solid #dfe5ea;border-radius:12px;padding:20px}.aclm-value{font-size:30px;font-weight:700;color:#071b2e}.aclm-label{color:#64748b;margin-top:4px}.aclm-note{background:#f7f6f2;border-left:4px solid #dfaf45;padding:14px 16px;margin-top:18px}@media(max-width:900px){.aclm-grid{grid-template-columns:repeat(2,minmax(0,1fr))}}@media(max-width:600px){.aclm-grid{grid-template-columns:1fr}}';
	}

	/**
	 * V5.1 relationship model.
	 * IDs are stored as post meta so the model stays WordPress-native and portable.
	 */
	private static function relation_schema() {
		return array(
			'ac_client' => array(
				'client_type' => 'select',
				'client_status' => 'select',
			),
			'ac_matter' => array(
				'client_id' => 'ac_client',
				'matter_status' => 'select',
				'matter_type' => 'select',
				'lead_lawyer_id' => 'user',
				'partner_id' => 'user',
			),
			'ac_request' => array(
				'client_id' => 'ac_client',
				'matter_id' => 'ac_matter',
				'request_status' => 'select',
				'priority' => 'select',
			),
			'ac_task' => array(
				'matter_id' => 'ac_matter',
				'client_id' => 'ac_client',
				'assignee_id' => 'user',
				'task_status' => 'select',
				'due_date' => 'date',
			),
			'ac_deadline' => array(
				'matter_id' => 'ac_matter',
				'deadline_type' => 'select',
				'due_date' => 'date',
				'deadline_status' => 'select',
			),
			'ac_document' => array(
				'client_id' => 'ac_client',
				'matter_id' => 'ac_matter',
				'document_type' => 'select',
				'access_level' => 'select',
			),
			'ac_retainer' => array(
				'client_id' => 'ac_client',
				'retainer_status' => 'select',
				'start_date' => 'date',
				'end_date' => 'date',
			),
		);
	}

	private static function field_labels() {
		return array(
			'client_id' => 'Klien', 'matter_id' => 'Perkara', 'lead_lawyer_id' => 'Lead Lawyer', 'partner_id' => 'Partner',
			'assignee_id' => 'Assignee', 'client_type' => 'Jenis Klien', 'client_status' => 'Status Klien', 'matter_status' => 'Status Perkara',
			'matter_type' => 'Jenis Perkara', 'request_status' => 'Status Request', 'priority' => 'Prioritas', 'task_status' => 'Status Tugas',
			'due_date' => 'Tanggal Jatuh Tempo', 'deadline_type' => 'Jenis Deadline', 'deadline_status' => 'Status Deadline',
			'document_type' => 'Jenis Dokumen', 'access_level' => 'Level Akses', 'retainer_status' => 'Status Retainer',
			'start_date' => 'Tanggal Mulai', 'end_date' => 'Tanggal Berakhir',
		);
	}

	private static function choices( $key ) {
		$choices = array(
			'client_type' => array( 'individual' => 'Perorangan', 'company' => 'Perusahaan', 'organization' => 'Organisasi' ),
			'client_status' => array( 'prospect' => 'Prospek', 'active' => 'Aktif', 'inactive' => 'Tidak Aktif' ),
			'matter_status' => array( 'intake' => 'Intake', 'active' => 'Aktif', 'on_hold' => 'On Hold', 'closed' => 'Selesai' ),
			'matter_type' => array( 'retainer' => 'Retainer', 'litigation' => 'Litigasi', 'corporate' => 'Corporate', 'transaction' => 'Transaksi', 'other' => 'Lainnya' ),
			'request_status' => array( 'new' => 'Baru', 'review' => 'Review', 'assigned' => 'Ditugaskan', 'in_progress' => 'Dikerjakan', 'done' => 'Selesai' ),
			'priority' => array( 'low' => 'Rendah', 'normal' => 'Normal', 'high' => 'Tinggi', 'critical' => 'Kritis' ),
			'task_status' => array( 'todo' => 'To Do', 'in_progress' => 'In Progress', 'blocked' => 'Blocked', 'done' => 'Done' ),
			'deadline_type' => array( 'court' => 'Persidangan', 'filing' => 'Filing', 'contract' => 'Kontrak', 'internal' => 'Internal', 'other' => 'Lainnya' ),
			'deadline_status' => array( 'upcoming' => 'Upcoming', 'at_risk' => 'At Risk', 'done' => 'Done' ),
			'document_type' => array( 'identity' => 'Identitas', 'contract' => 'Kontrak', 'court' => 'Dokumen Perkara', 'legal_opinion' => 'Legal Opinion', 'other' => 'Lainnya' ),
			'access_level' => array( 'internal' => 'Internal', 'matter_team' => 'Matter Team', 'client' => 'Client' ),
			'retainer_status' => array( 'draft' => 'Draft', 'active' => 'Aktif', 'paused' => 'Paused', 'expired' => 'Berakhir' ),
		);
		return isset( $choices[ $key ] ) ? $choices[ $key ] : array();
	}

	public static function register_relation_meta_boxes() {
		$schema = self::relation_schema();
		foreach ( array_keys( $schema ) as $post_type ) {
			add_meta_box( 'aclm_relationships', 'Arkana Civiel — Data & Relasi V5.1', array( __CLASS__, 'render_relation_box' ), $post_type, 'normal', 'high' );
		}
	}

	public static function render_relation_box( $post ) {
		wp_nonce_field( 'aclm_save_relations', 'aclm_relations_nonce' );
		$schema = self::relation_schema()[ $post->post_type ];
		$labels = self::field_labels();
		echo '<div style="display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:16px;max-width:900px;">';
		foreach ( $schema as $key => $kind ) {
			$value = get_post_meta( $post->ID, '_aclm_' . $key, true );
			echo '<p style="margin:0"><label for="aclm_' . esc_attr( $key ) . '"><strong>' . esc_html( $labels[ $key ] ) . '</strong></label><br/>';
			if ( in_array( $kind, array( 'ac_client', 'ac_matter' ), true ) ) {
				$post_type = 'ac_client' === $kind ? 'ac_client' : 'ac_matter';
				$items = get_posts( array( 'post_type' => $post_type, 'post_status' => array( 'publish', 'draft', 'private' ), 'numberposts' => 100, 'orderby' => 'title', 'order' => 'ASC' ) );
				echo '<select class="widefat" id="aclm_' . esc_attr( $key ) . '" name="aclm_' . esc_attr( $key ) . '"><option value="">— Pilih —</option>';
				foreach ( $items as $item ) {
					echo '<option value="' . esc_attr( $item->ID ) . '" ' . selected( (string) $value, (string) $item->ID, false ) . '>' . esc_html( $item->post_title ) . '</option>';
				}
				echo '</select>';
			} elseif ( 'user' === $kind ) {
				$users = get_users( array( 'role__in' => array( 'administrator', 'ac_managing_partner', 'ac_partner', 'ac_lawyer', 'ac_paralegal' ), 'orderby' => 'display_name', 'number' => 100 ) );
				echo '<select class="widefat" id="aclm_' . esc_attr( $key ) . '" name="aclm_' . esc_attr( $key ) . '"><option value="">— Pilih —</option>';
				foreach ( $users as $user ) {
					echo '<option value="' . esc_attr( $user->ID ) . '" ' . selected( (string) $value, (string) $user->ID, false ) . '>' . esc_html( $user->display_name ) . '</option>';
				}
				echo '</select>';
			} elseif ( 'select' === $kind ) {
				echo '<select class="widefat" id="aclm_' . esc_attr( $key ) . '" name="aclm_' . esc_attr( $key ) . '"><option value="">— Pilih —</option>';
				foreach ( self::choices( $key ) as $choice_value => $choice_label ) {
					echo '<option value="' . esc_attr( $choice_value ) . '" ' . selected( (string) $value, (string) $choice_value, false ) . '>' . esc_html( $choice_label ) . '</option>';
				}
				echo '</select>';
			} else {
				echo '<input class="widefat" type="' . esc_attr( 'date' === $kind ? 'date' : 'text' ) . '" id="aclm_' . esc_attr( $key ) . '" name="aclm_' . esc_attr( $key ) . '" value="' . esc_attr( $value ) . '" />';
			}
			echo '</p>';
		}
		echo '</div>';
		echo '<p style="color:#64748b;margin-bottom:0">V5.1 menyimpan relasi sebagai WordPress post meta. Ini memudahkan migrasi, backup, dan pengembangan portal klien pada tahap berikutnya.</p>';
	}

	public static function save_relation_meta( $post_id, $post ) {
		if ( ! isset( $_POST['aclm_relations_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['aclm_relations_nonce'] ) ), 'aclm_save_relations' ) ) { return; }
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) { return; }
		if ( wp_is_post_revision( $post_id ) ) { return; }
		$schema = self::relation_schema();
		if ( ! isset( $schema[ $post->post_type ] ) ) { return; }
		if ( ! current_user_can( 'edit_post', $post_id ) ) { return; }
		foreach ( $schema[ $post->post_type ] as $key => $kind ) {
			$field = 'aclm_' . $key;
			if ( ! isset( $_POST[ $field ] ) ) { continue; }
			$raw = wp_unslash( $_POST[ $field ] );
			$value = 'date' === $kind ? sanitize_text_field( $raw ) : sanitize_text_field( $raw );
			if ( in_array( $kind, array( 'ac_client', 'ac_matter', 'user' ), true ) ) { $value = absint( $value ); }
			if ( 'select' === $kind && ! array_key_exists( $value, self::choices( $key ) ) ) { $value = ''; }
			if ( '' === (string) $value ) { delete_post_meta( $post_id, '_aclm_' . $key ); }
			else { update_post_meta( $post_id, '_aclm_' . $key, $value ); }
		}
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
				<p style="margin:0;color:#d9e2ea;">V5.1 Data Model — satu pusat untuk klien, perkara, request, tugas, deadline, dokumen, dan retainer.</p>
			</div>
			<div class="aclm-grid">
				<?php self::metric_card( 'Klien', $count( 'ac_client' ) ); ?>
				<?php self::metric_card( 'Perkara', $count( 'ac_matter' ) ); ?>
				<?php self::metric_card( 'Permintaan Hukum', $count( 'ac_request' ) ); ?>
				<?php self::metric_card( 'Retainer', $count( 'ac_retainer' ) ); ?>
			</div>
			<div class="aclm-card">
				<h2>Relasi inti V5.1</h2>
				<p><strong>Client → Matter → Request → Task / Deadline → Document</strong></p>
				<p><strong>Client → Retainer</strong></p>
				<p>Setiap Matter dapat memiliki Client, Partner, Lead Lawyer; Request dan Task dapat ditautkan ke Matter; dokumen dapat dibatasi menurut level akses. Relasi ini menjadi dasar portal klien pada V5.5.</p>
			</div>
			<div class="aclm-note"><strong>Production gate:</strong> jangan masukkan data klien/perkara nyata sebelum V5.9 private document delivery, matter-level authorization, audit log, dan security QA selesai.</div>
		</div>
		<?php
	}

	private static function metric_card( $label, $value ) {
		echo '<div class="aclm-card"><div class="aclm-value">' . esc_html( $value ) . '</div><div class="aclm-label">' . esc_html( $label ) . '</div></div>';
	}

	public static function dashboard_shortcode() {
		if ( ! is_user_logged_in() ) { return '<p>Silakan login untuk mengakses Legal Management.</p>'; }
		ob_start(); self::render_dashboard(); return ob_get_clean();
	}
}

Arkana_Civiel_Legal_Management::init();
register_activation_hook( __FILE__, array( 'Arkana_Civiel_Legal_Management', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'Arkana_Civiel_Legal_Management', 'deactivate' ) );
