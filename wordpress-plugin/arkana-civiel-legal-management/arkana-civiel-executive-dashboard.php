<?php
/**
 * Plugin Name: Arkana Civiel — Managing Partner Dashboard
 * Description: V5.3 executive dashboard companion module for Arkana Civiel Legal Management.
 * Version: 5.3.0-alpha.1
 * Author: Arkana Civiel Law Firm
 * Requires at least: 6.4
 * Requires PHP: 8.1
 * License: GPL-2.0-or-later
 */

defined( 'ABSPATH' ) || exit;

final class Arkana_Civiel_Executive_Dashboard {
	const VERSION = '5.3.0-alpha.1';

	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'menu' ), 30 );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'assets' ) );
	}

	private static function allowed() {
		return current_user_can( 'manage_arkana_legal' ) || current_user_can( 'manage_options' );
	}

	public static function menu() {
		if ( ! self::allowed() ) { return; }
		add_submenu_page(
			'arkana-legal-management',
			'Managing Partner Dashboard',
			'Executive Dashboard',
			'manage_arkana_legal',
			'arkana-executive-dashboard',
			array( __CLASS__, 'render' )
		);
	}

	public static function assets( $hook ) {
		if ( 'legal-management_page_arkana-executive-dashboard' !== $hook ) { return; }
		wp_register_style( 'aclm-executive', false, array(), self::VERSION );
		wp_enqueue_style( 'aclm-executive' );
		wp_add_inline_style( 'aclm-executive', self::css() );
	}

	private static function count( $type, $status = 'publish' ) {
		$counts = wp_count_posts( $type );
		return isset( $counts->{$status} ) ? (int) $counts->{$status} : 0;
	}

	private static function meta_count( $type, $meta_key, $meta_value ) {
		$query = new WP_Query( array(
			'post_type' => $type,
			'post_status' => array( 'publish', 'private' ),
			'posts_per_page' => 1,
			'fields' => 'ids',
			'no_found_rows' => false,
			'meta_query' => array(
				array( 'key' => '_aclm_' . $meta_key, 'value' => $meta_value ),
			),
		) );
		return (int) $query->found_posts;
	}

	public static function render() {
		if ( ! self::allowed() ) {
			wp_die( esc_html__( 'Anda tidak memiliki akses ke Executive Dashboard.', 'arkana-civiel-legal-management' ) );
		}
		$active_matters = self::meta_count( 'ac_matter', 'matter_status', 'active' );
		$critical_requests = self::meta_count( 'ac_request', 'priority', 'critical' );
		$at_risk_deadlines = self::meta_count( 'ac_deadline', 'deadline_status', 'at_risk' );
		$open_tasks = self::count( 'ac_task', 'publish' ) - self::meta_count( 'ac_task', 'task_status', 'done' );
		if ( $open_tasks < 0 ) { $open_tasks = 0; }
		?>
		<div class="wrap aclm-exec">
			<section class="aclm-exec-hero">
				<div>
					<div class="aclm-eyebrow">ARKANA CIVIEL · V5.3</div>
					<h1>Managing Partner Dashboard</h1>
					<p>Executive overview untuk memonitor kesehatan operasional firma, perkara aktif, risiko deadline, dan permintaan hukum.</p>
				</div>
				<div class="aclm-exec-badge">EXECUTIVE VIEW</div>
			</section>

			<div class="aclm-exec-grid">
				<?php self::card( 'Klien Aktif', self::meta_count( 'ac_client', 'client_status', 'active' ), 'Portfolio' ); ?>
				<?php self::card( 'Perkara Aktif', $active_matters, 'Matters' ); ?>
				<?php self::card( 'Retainer Aktif', self::meta_count( 'ac_retainer', 'retainer_status', 'active' ), 'Retainer' ); ?>
				<?php self::card( 'Open Tasks', $open_tasks, 'Execution' ); ?>
			</div>

			<div class="aclm-exec-columns">
				<div class="aclm-exec-panel">
					<h2>Risk & Attention</h2>
					<?php self::risk_row( 'Deadline At Risk', $at_risk_deadlines, 'ac_deadline', 'deadline_status', 'at_risk' ); ?>
					<?php self::risk_row( 'Critical Legal Requests', $critical_requests, 'ac_request', 'priority', 'critical' ); ?>
					<?php self::risk_row( 'Matters On Hold', self::meta_count( 'ac_matter', 'matter_status', 'on_hold' ), 'ac_matter', 'matter_status', 'on_hold' ); ?>
				</div>
				<div class="aclm-exec-panel">
					<h2>Firm Snapshot</h2>
					<div class="aclm-snapshot"><span>Total Clients</span><strong><?php echo esc_html( self::count( 'ac_client' ) ); ?></strong></div>
					<div class="aclm-snapshot"><span>Total Matters</span><strong><?php echo esc_html( self::count( 'ac_matter' ) ); ?></strong></div>
					<div class="aclm-snapshot"><span>Legal Requests</span><strong><?php echo esc_html( self::count( 'ac_request' ) ); ?></strong></div>
					<div class="aclm-snapshot"><span>Documents</span><strong><?php echo esc_html( self::count( 'ac_document' ) ); ?></strong></div>
				</div>
			</div>

			<div class="aclm-exec-panel aclm-exec-note">
				<strong>V5.3 Security Boundary</strong>
				<p>Dashboard ini hanya menampilkan aggregate metrics. Matter-level authorization, client isolation, private document delivery, audit log, dan production-grade reporting tetap harus diselesaikan sebelum data sensitif digunakan.</p>
			</div>
		</div>
		<?php
	}

	private static function card( $label, $value, $context ) {
		echo '<div class="aclm-exec-card"><span>' . esc_html( $context ) . '</span><strong>' . esc_html( $value ) . '</strong><p>' . esc_html( $label ) . '</p></div>';
	}

	private static function risk_row( $label, $value, $type, $key, $expected ) {
		$class = $value > 0 ? 'risk' : 'ok';
		echo '<div class="aclm-risk-row ' . esc_attr( $class ) . '"><span>' . esc_html( $label ) . '</span><strong>' . esc_html( $value ) . '</strong></div>';
	}

	private static function css() {
		return '.aclm-exec{max-width:1250px}.aclm-exec-hero{margin:20px 0;display:flex;justify-content:space-between;align-items:center;gap:20px;background:#071b2e;color:#fff;border-radius:16px;padding:30px 34px}.aclm-exec-hero h1{color:#fff;margin:8px 0}.aclm-exec-hero p{color:#d7e1e9;margin:0;max-width:720px}.aclm-eyebrow{font-size:11px;letter-spacing:.14em;color:#dfaf45;font-weight:700}.aclm-exec-badge{border:1px solid #dfaf45;color:#dfaf45;border-radius:999px;padding:8px 12px;font-size:11px;font-weight:700}.aclm-exec-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:16px}.aclm-exec-card,.aclm-exec-panel{background:#fff;border:1px solid #dfe5ea;border-radius:14px;padding:22px}.aclm-exec-card span{font-size:11px;color:#64748b;text-transform:uppercase;letter-spacing:.1em}.aclm-exec-card strong{display:block;font-size:34px;color:#071b2e;margin-top:8px}.aclm-exec-card p{margin:5px 0 0;color:#475569}.aclm-exec-columns{display:grid;grid-template-columns:1.3fr 1fr;gap:16px;margin-top:16px}.aclm-exec-panel h2{margin-top:0;color:#071b2e}.aclm-risk-row{display:flex;justify-content:space-between;padding:14px 0;border-bottom:1px solid #eef1f4}.aclm-risk-row:last-child{border-bottom:0}.aclm-risk-row.risk strong{color:#b45309}.aclm-risk-row.ok strong{color:#15803d}.aclm-snapshot{display:flex;justify-content:space-between;padding:13px 0;border-bottom:1px solid #eef1f4}.aclm-snapshot strong{color:#071b2e}.aclm-exec-note{margin-top:16px;background:#f7f6f2;border-left:4px solid #dfaf45}.aclm-exec-note p{margin-bottom:0}@media(max-width:900px){.aclm-exec-grid{grid-template-columns:repeat(2,1fr)}.aclm-exec-columns{grid-template-columns:1fr}}@media(max-width:600px){.aclm-exec-grid{grid-template-columns:1fr}.aclm-exec-hero{align-items:flex-start;flex-direction:column}}';
	}
}

Arkana_Civiel_Executive_Dashboard::init();
