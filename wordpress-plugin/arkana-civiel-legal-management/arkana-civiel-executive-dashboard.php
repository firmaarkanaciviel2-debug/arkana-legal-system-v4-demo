<?php
/**
 * Plugin Name: Arkana Civiel — Managing Partner Dashboard
 * Description: Executive management dashboard for Arkana Civiel Legal Management.
 * Version: 7.11.0-alpha.1
 * Author: Arkana Civiel Law Firm
 * Requires at least: 6.4
 * Requires PHP: 8.1
 * License: GPL-2.0-or-later
 */

defined( 'ABSPATH' ) || exit;

final class Arkana_Civiel_Executive_Dashboard {
	const VERSION = '7.11.0-alpha.1';

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

		$active_matters    = self::meta_count( 'ac_matter', 'matter_status', 'active' );
		$critical_requests = self::meta_count( 'ac_request', 'priority', 'critical' );
		$at_risk_deadlines = self::meta_count( 'ac_deadline', 'deadline_status', 'at_risk' );
		$open_tasks        = self::count( 'ac_task', 'publish' ) - self::meta_count( 'ac_task', 'task_status', 'done' );
		if ( $open_tasks < 0 ) { $open_tasks = 0; }
		$clients           = self::meta_count( 'ac_client', 'client_status', 'active' );
		$retainers         = self::meta_count( 'ac_retainer', 'retainer_status', 'active' );
		$total_matters     = self::count( 'ac_matter' );
		$total_requests    = self::count( 'ac_request' );
		$total_documents   = self::count( 'ac_document' );
		?>
		<div class="wrap aclm-exec">
			<section class="aclm-topbar">
				<div class="aclm-brand-mark"><span class="aclm-mark">AC</span><div><strong>ARKANA CIVIEL</strong><small>LEGAL MANAGEMENT</small></div></div>
				<div class="aclm-topbar-actions"><span class="aclm-live"><i></i> System operational</span><span class="aclm-role">Managing Partner</span></div>
			</section>

			<section class="aclm-exec-hero">
				<div class="aclm-hero-copy">
					<div class="aclm-eyebrow">EXECUTIVE COMMAND CENTER · V7.11</div>
					<h1>Good day, Managing Partner.</h1>
					<p>Monitor matters, service delivery, risk, retainers, and team execution from one command center.</p>
					<div class="aclm-hero-actions"><a class="aclm-btn primary" href="admin.php?page=arkana-legal-management">Open Management</a><a class="aclm-btn ghost" href="admin.php?page=arkana-executive-dashboard">Refresh Overview</a></div>
				</div>
				<div class="aclm-hero-orbit" aria-hidden="true"><div class="orbit-core">AC</div><div class="orbit-ring ring-a"></div><div class="orbit-ring ring-b"></div></div>
			</section>

			<div class="aclm-kpi-grid">
				<?php self::kpi_card( 'Active Matters', $active_matters, 'Matter workspace', 'matter', $active_matters > 0 ? 'Live' : 'Clear' ); ?>
				<?php self::kpi_card( 'Open Requests', $total_requests, 'Service delivery', 'request', $critical_requests . ' critical' ); ?>
				<?php self::kpi_card( 'SLA / Deadline Risk', $at_risk_deadlines, 'Needs attention', 'risk', $at_risk_deadlines ? 'Review now' : 'On track' ); ?>
				<?php self::kpi_card( 'Open Tasks', $open_tasks, 'Team execution', 'task', 'Across matters' ); ?>
			</div>

			<div class="aclm-command-grid">
				<section class="aclm-panel matter-panel">
					<div class="aclm-panel-head"><div><span class="aclm-section-kicker">MATTER WORKSPACE</span><h2>Active Legal Matters</h2></div><a href="admin.php?page=arkana-legal-management">View all →</a></div>
					<div class="aclm-matter-list">
						<?php self::matter_preview( 'Perjanjian Vendor & Commercial Review', 'AC-MAT-2026-0001', 'Corporate & Commercial', 'IN PROGRESS', 'blue' ); ?>
						<?php self::matter_preview( 'Ketenagakerjaan & Industrial Relations', 'AC-MAT-2026-0002', 'Employment', 'WATCHLIST', 'amber' ); ?>
						<?php self::matter_preview( 'Dispute Prevention & Legal Advisory', 'AC-MAT-2026-0003', 'Dispute Resolution', 'OPEN', 'green' ); ?>
					</div>
					<div class="aclm-panel-footer"><span><?php echo esc_html( $active_matters ); ?> active matters in portfolio</span><a href="admin.php?page=arkana-legal-management">Manage matters</a></div>
				</section>

				<section class="aclm-panel copilot-panel">
					<div class="aclm-ai-glow"></div>
					<div class="aclm-panel-head"><div><span class="aclm-section-kicker ai">AI MANAGEMENT</span><h2>Arkana Copilot</h2></div><span class="aclm-ai-badge">BETA</span></div>
					<p class="aclm-copilot-intro">Your management intelligence layer. Ask for operational signals without changing any records.</p>
					<div class="aclm-ai-prompt"><span>Ask Arkana about your firm…</span><span class="aclm-send">↗</span></div>
					<div class="aclm-ai-suggestions"><span>What needs attention today?</span><span>Show deadline risks</span><span>Review workload</span></div>
					<div class="aclm-ai-insight"><span class="insight-icon">✦</span><div><strong>Management signal</strong><p><?php echo esc_html( $at_risk_deadlines ? $at_risk_deadlines . ' deadline(s) need review.' : 'No deadline risks are currently flagged.' ); ?></p></div></div>
				</section>
			</div>

			<div class="aclm-lower-grid">
				<section class="aclm-panel">
					<div class="aclm-panel-head"><div><span class="aclm-section-kicker">SERVICE DELIVERY</span><h2>Legal Requests</h2></div><a href="admin.php?page=arkana-legal-management">Open queue →</a></div>
					<div class="aclm-request-row"><div class="request-icon purple">§</div><div><strong>Review draft vendor agreement</strong><small>AC-REQ-001 · Corporate</small></div><span class="status-chip blue">HIGH</span></div>
					<div class="aclm-request-row"><div class="request-icon blue">§</div><div><strong>Legal opinion on employee warning letter</strong><small>AC-REQ-002 · Employment</small></div><span class="status-chip neutral">NORMAL</span></div>
					<div class="aclm-request-row"><div class="request-icon green">§</div><div><strong>Contract clause clarification</strong><small>AC-REQ-003 · Commercial</small></div><span class="status-chip green">COMPLETED</span></div>
				</section>

				<section class="aclm-panel risk-panel">
					<div class="aclm-panel-head"><div><span class="aclm-section-kicker">RISK CENTER</span><h2>Attention Required</h2></div></div>
					<?php self::risk_row( 'Deadline At Risk', $at_risk_deadlines ); ?>
					<?php self::risk_row( 'Critical Legal Requests', $critical_requests ); ?>
					<?php self::risk_row( 'Matters On Hold', self::meta_count( 'ac_matter', 'matter_status', 'on_hold' ) ); ?>
				</section>

				<section class="aclm-panel portfolio-panel">
					<div class="aclm-panel-head"><div><span class="aclm-section-kicker">PORTFOLIO</span><h2>Firm Snapshot</h2></div></div>
					<div class="snapshot-grid"><div><strong><?php echo esc_html( $clients ); ?></strong><span>Active clients</span></div><div><strong><?php echo esc_html( $retainers ); ?></strong><span>Active retainers</span></div><div><strong><?php echo esc_html( $total_matters ); ?></strong><span>Total matters</span></div><div><strong><?php echo esc_html( $total_documents ); ?></strong><span>Documents</span></div></div>
				</section>
			</div>

			<section class="aclm-quick-actions"><div><span class="aclm-section-kicker">QUICK ACTIONS</span><h2>Move work forward</h2></div><div class="quick-links"><a href="post-new.php?post_type=ac_matter">＋ New Matter</a><a href="post-new.php?post_type=ac_client">＋ New Client</a><a href="post-new.php?post_type=ac_task">＋ New Task</a><a href="admin.php?page=arkana-executive-dashboard">↗ Reports</a></div></section>

			<div class="aclm-security-note"><span>✓</span><div><strong>Management security boundary</strong><p>Executive view uses aggregate metrics. Client isolation, matter-level authorization, private document delivery, audit logging, and AI production controls remain enforced by the underlying modules and release gates.</p></div></div>
		</div>
		<?php
	}

	private static function kpi_card( $label, $value, $context, $icon, $foot ) {
		echo '<div class="aclm-kpi"><div class="kpi-top"><span class="kpi-icon ' . esc_attr( $icon ) . '">' . self::icon( $icon ) . '</span><span class="kpi-context">' . esc_html( $context ) . '</span></div><strong class="kpi-value">' . esc_html( $value ) . '</strong><div class="kpi-label">' . esc_html( $label ) . '</div><div class="kpi-foot">' . esc_html( $foot ) . '</div></div>';
	}

	private static function matter_preview( $title, $id, $type, $status, $tone ) {
		echo '<div class="matter-row"><div class="matter-symbol">§</div><div class="matter-info"><small>' . esc_html( $id ) . '</small><strong>' . esc_html( $title ) . '</strong><span>' . esc_html( $type ) . '</span></div><span class="matter-status ' . esc_attr( $tone ) . '">' . esc_html( $status ) . '</span></div>';
	}

	private static function risk_row( $label, $value ) {
		$tone = $value > 0 ? 'risk' : 'ok';
		$text = $value > 0 ? 'Review required' : 'On track';
		echo '<div class="risk-item ' . esc_attr( $tone ) . '"><span class="risk-dot"></span><div><strong>' . esc_html( $label ) . '</strong><small>' . esc_html( $text ) . '</small></div><b>' . esc_html( $value ) . '</b></div>';
	}

	private static function icon( $name ) {
		$icons = array(
			'matter' => '◈', 'request' => '✦', 'risk' => '△', 'task' => '✓',
		);
		return isset( $icons[ $name ] ) ? esc_html( $icons[ $name ] ) : '•';
	}

	private static function css() {
		return <<<'CSS'
.aclm-exec{max-width:1440px;margin:0 auto;padding:8px 18px 42px;font-family:Inter,-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif;color:#182230}.aclm-exec *{box-sizing:border-box}.aclm-topbar{height:58px;display:flex;align-items:center;justify-content:space-between;margin:0 0 14px}.aclm-brand-mark{display:flex;align-items:center;gap:10px}.aclm-mark{width:34px;height:34px;border-radius:10px;background:#0a2238;color:#e1b65a;display:grid;place-items:center;font-size:11px;font-weight:800;letter-spacing:.04em}.aclm-brand-mark strong{display:block;font-size:12px;letter-spacing:.16em;color:#0a2238}.aclm-brand-mark small{display:block;font-size:9px;color:#8190a0;letter-spacing:.13em;margin-top:2px}.aclm-topbar-actions{display:flex;align-items:center;gap:14px;font-size:11px}.aclm-live{color:#27724a}.aclm-live i{display:inline-block;width:7px;height:7px;background:#38a169;border-radius:50%;margin-right:5px}.aclm-role{padding:7px 10px;border:1px solid #e2e7ec;border-radius:999px;color:#516174;background:#fff}.aclm-exec-hero{position:relative;overflow:hidden;min-height:220px;border-radius:22px;padding:32px 38px;background:linear-gradient(120deg,#071b2e 0%,#0d2b47 58%,#123b59 100%);color:#fff;display:flex;align-items:center;justify-content:space-between;box-shadow:0 14px 35px rgba(7,27,46,.14)}.aclm-hero-copy{position:relative;z-index:2}.aclm-eyebrow,.aclm-section-kicker{font-size:10px;letter-spacing:.16em;font-weight:800;color:#d9ad55}.aclm-exec-hero h1{font-size:32px;line-height:1.15;margin:9px 0 8px;color:#fff;font-weight:700}.aclm-exec-hero p{max-width:650px;color:#c8d5df;font-size:13px;margin:0;line-height:1.6}.aclm-hero-actions{display:flex;gap:9px;margin-top:21px}.aclm-btn{display:inline-flex;align-items:center;padding:9px 14px;border-radius:9px;text-decoration:none;font-size:11px;font-weight:700}.aclm-btn.primary{background:#d9ad55;color:#071b2e}.aclm-btn.ghost{border:1px solid rgba(255,255,255,.25);color:#fff;background:rgba(255,255,255,.05)}.aclm-hero-orbit{position:absolute;right:65px;width:220px;height:220px;opacity:.75}.orbit-core{position:absolute;inset:75px;border-radius:50%;display:grid;place-items:center;background:rgba(217,173,85,.13);border:1px solid rgba(217,173,85,.55);color:#e7c77f;font-weight:800;font-size:20px}.orbit-ring{position:absolute;border:1px solid rgba(217,173,85,.18);border-radius:50%}.ring-a{inset:38px}.ring-b{inset:4px}.aclm-kpi-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-top:16px}.aclm-kpi,.aclm-panel{background:#fff;border:1px solid #e5e9ee;border-radius:16px;box-shadow:0 6px 20px rgba(16,31,48,.04)}.aclm-kpi{padding:18px 19px}.kpi-top{display:flex;align-items:center;justify-content:space-between}.kpi-icon{width:32px;height:32px;border-radius:10px;display:grid;place-items:center;font-weight:800;font-size:14px}.kpi-icon.matter{background:#eef4fa;color:#27618d}.kpi-icon.request{background:#f3effb;color:#7654a9}.kpi-icon.risk{background:#fff4e4;color:#a86b16}.kpi-icon.task{background:#edf8f1;color:#2f8153}.kpi-context{font-size:9px;text-transform:uppercase;letter-spacing:.12em;color:#91a0af}.kpi-value{display:block;font-size:32px;line-height:1;margin:19px 0 6px;color:#10283e}.kpi-label{font-size:13px;font-weight:700;color:#2a3949}.kpi-foot{font-size:10px;color:#8996a4;margin-top:8px}.aclm-command-grid{display:grid;grid-template-columns:1.55fr 1fr;gap:16px;margin-top:16px}.aclm-panel{padding:21px}.aclm-panel-head{display:flex;align-items:flex-start;justify-content:space-between;gap:15px;margin-bottom:13px}.aclm-panel-head h2{font-size:17px;margin:5px 0 0;color:#172a3e}.aclm-panel-head a{font-size:10px;color:#64758a;text-decoration:none;margin-top:4px}.aclm-section-kicker{color:#8493a3}.aclm-section-kicker.ai{color:#7b61ad}.matter-row{display:flex;align-items:center;gap:12px;padding:14px 0;border-bottom:1px solid #edf0f3}.matter-row:last-child{border-bottom:0}.matter-symbol{width:28px;height:28px;border-radius:8px;background:#f3f5f7;color:#8a98a6;display:grid;place-items:center}.matter-info{min-width:0;flex:1}.matter-info small,.matter-info span{display:block;font-size:9px;color:#8b98a5}.matter-info strong{display:block;font-size:12px;color:#24384b;margin:3px 0}.matter-status,.status-chip{font-size:8px;font-weight:800;letter-spacing:.08em;border-radius:999px;padding:5px 7px;white-space:nowrap}.matter-status.blue,.status-chip.blue{background:#edf4fb;color:#2e6a98}.matter-status.amber{background:#fff5e6;color:#a36d1e}.matter-status.green,.status-chip.green{background:#edf8f1;color:#28734a}.aclm-panel-footer{display:flex;justify-content:space-between;align-items:center;border-top:1px solid #edf0f3;padding-top:13px;margin-top:6px;font-size:10px;color:#8a96a3}.aclm-panel-footer a{color:#466985;text-decoration:none;font-weight:700}.copilot-panel{position:relative;overflow:hidden;background:linear-gradient(145deg,#fff 0%,#fbf8ff 100%)}.aclm-ai-glow{position:absolute;right:-50px;top:-60px;width:170px;height:170px;border-radius:50%;background:rgba(132,96,185,.08);filter:blur(2px)}.aclm-ai-badge{font-size:8px;letter-spacing:.1em;padding:5px 7px;border-radius:999px;background:#f1eafa;color:#75539f;font-weight:800}.aclm-copilot-intro{font-size:11px;line-height:1.55;color:#687687;margin:7px 0 15px}.aclm-ai-prompt{height:46px;border:1px solid #ded6e9;border-radius:11px;background:#fff;display:flex;align-items:center;justify-content:space-between;padding:0 12px;color:#9a8faa;font-size:11px}.aclm-send{width:26px;height:26px;border-radius:8px;background:#6d4b99;color:#fff;display:grid;place-items:center;font-weight:800}.aclm-ai-suggestions{display:flex;gap:6px;flex-wrap:wrap;margin:10px 0}.aclm-ai-suggestions span{font-size:8px;border:1px solid #e5dff0;background:#fff;padding:5px 7px;border-radius:999px;color:#765f8d}.aclm-ai-insight{display:flex;gap:9px;background:#f4eff9;border:1px solid #e5dcf0;border-radius:10px;padding:11px;margin-top:13px}.insight-icon{color:#7956a1;font-size:16px}.aclm-ai-insight strong{font-size:10px;color:#4b3c5b}.aclm-ai-insight p{font-size:9px;color:#756c80;margin:3px 0 0}.aclm-lower-grid{display:grid;grid-template-columns:1.35fr 1fr 1fr;gap:16px;margin-top:16px}.aclm-request-row{display:flex;align-items:center;gap:10px;padding:12px 0;border-bottom:1px solid #edf0f3}.aclm-request-row:last-child{border-bottom:0}.request-icon{width:28px;height:28px;border-radius:8px;display:grid;place-items:center;font-weight:700}.request-icon.purple{background:#f2ecfa;color:#7955a2}.request-icon.blue{background:#edf4fb;color:#3c719b}.request-icon.green{background:#edf8f1;color:#348156}.aclm-request-row>div:nth-child(2){flex:1;min-width:0}.aclm-request-row strong{display:block;font-size:10px;color:#26384b}.aclm-request-row small{display:block;color:#909ba7;font-size:8px;margin-top:3px}.status-chip.neutral{background:#f1f3f5;color:#6f7d8a}.risk-item{display:flex;align-items:center;gap:9px;padding:12px 0;border-bottom:1px solid #edf0f3}.risk-item:last-child{border-bottom:0}.risk-dot{width:8px;height:8px;border-radius:50%;background:#39a267}.risk-item.risk .risk-dot{background:#d58b2d}.risk-item div{flex:1}.risk-item strong{display:block;font-size:10px;color:#293a4b}.risk-item small{display:block;font-size:8px;color:#95a0ab;margin-top:2px}.risk-item b{font-size:16px;color:#1e354a}.risk-item.risk b{color:#b66d1d}.snapshot-grid{display:grid;grid-template-columns:1fr 1fr;gap:10px}.snapshot-grid div{padding:13px;background:#f7f9fa;border-radius:10px}.snapshot-grid strong{display:block;font-size:21px;color:#173149}.snapshot-grid span{display:block;font-size:8px;color:#8996a3;margin-top:3px}.aclm-quick-actions{margin-top:16px;padding:19px 21px;border-radius:16px;background:#f7f9fa;border:1px solid #e5e9ee;display:flex;align-items:center;justify-content:space-between;gap:20px}.aclm-quick-actions h2{margin:5px 0 0;font-size:16px;color:#173149}.quick-links{display:flex;gap:7px;flex-wrap:wrap;justify-content:flex-end}.quick-links a{padding:9px 11px;background:#fff;border:1px solid #dde4ea;border-radius:9px;text-decoration:none;color:#38536a;font-size:10px;font-weight:700}.aclm-security-note{display:flex;gap:12px;margin-top:14px;padding:13px 15px;background:#f0f8f3;border:1px solid #d8ebdf;border-radius:12px;color:#356348}.aclm-security-note>span{width:22px;height:22px;border-radius:50%;background:#d8eddf;display:grid;place-items:center;color:#28724a;font-weight:800}.aclm-security-note strong{font-size:10px}.aclm-security-note p{font-size:9px;margin:3px 0 0;color:#688172;line-height:1.5}@media(max-width:1100px){.aclm-kpi-grid{grid-template-columns:repeat(2,1fr)}.aclm-command-grid,.aclm-lower-grid{grid-template-columns:1fr}.aclm-hero-orbit{right:15px;opacity:.35}}@media(max-width:650px){.aclm-exec{padding:5px 8px 30px}.aclm-topbar-actions .aclm-live{display:none}.aclm-exec-hero{padding:25px 22px}.aclm-exec-hero h1{font-size:25px}.aclm-kpi-grid{grid-template-columns:1fr}.aclm-quick-actions{align-items:flex-start;flex-direction:column}.quick-links{justify-content:flex-start}.aclm-hero-orbit{display:none}}
CSS;
	}
}

Arkana_Civiel_Executive_Dashboard::init();
