<?php
/**
 * Arkana Civiel Legal Management — V7.13
 * Matter Intelligence & Risk Dashboard foundation.
 *
 * Presentation/data-aggregation layer only. It intentionally does not mutate
 * matters, deadlines, SLAs or client records.
 */
defined('ABSPATH') || exit;

if (!class_exists('ACLM_V713_Matter_Risk_Dashboard')) {
final class ACLM_V713_Matter_Risk_Dashboard {
    public static function init() {
        add_shortcode('arkana_matter_risk_dashboard', array(__CLASS__, 'shortcode'));
        add_filter('aclm_v712_dashboard_snapshot', array(__CLASS__, 'augment_snapshot'));
    }

    public static function risk_from_item($item) {
        $status = strtolower((string)($item['status'] ?? ''));
        $due = isset($item['due_date']) ? strtotime((string)$item['due_date']) : false;
        $now = current_time('timestamp');
        if (strpos($status, 'critical') !== false || ($due && $due < $now)) return 'critical';
        if (strpos($status, 'risk') !== false || ($due && $due <= $now + (3 * DAY_IN_SECONDS))) return 'warning';
        return 'normal';
    }

    public static function normalize_items($items) {
        $out = array();
        foreach ((array)$items as $item) {
            if (!is_array($item)) continue;
            $item['risk'] = self::risk_from_item($item);
            $out[] = $item;
        }
        return $out;
    }

    public static function augment_snapshot($snapshot) {
        if (!is_array($snapshot)) $snapshot = array();
        $matters = self::normalize_items($snapshot['matters'] ?? array());
        $requests = self::normalize_items($snapshot['requests'] ?? array());
        $all = array_merge($matters, $requests);
        $counts = array('critical'=>0,'warning'=>0,'normal'=>0);
        foreach ($all as $item) { $counts[$item['risk']]++; }
        $snapshot['matter_intelligence'] = array(
            'risk_counts' => $counts,
            'attention_required' => $counts['critical'] + $counts['warning'],
            'generated_at' => current_time('mysql'),
        );
        $snapshot['matters'] = $matters;
        $snapshot['requests'] = $requests;
        return $snapshot;
    }

    public static function shortcode() {
        if (!is_user_logged_in()) return '<p>Silakan masuk untuk melihat Matter Risk Dashboard.</p>';
        $snapshot = apply_filters('aclm_v712_dashboard_snapshot', array('matters'=>array(),'requests'=>array()));
        $intel = $snapshot['matter_intelligence'] ?? array('risk_counts'=>array('critical'=>0,'warning'=>0,'normal'=>0),'attention_required'=>0);
        $c = $intel['risk_counts'];
        ob_start(); ?>
        <section class="aclm-v713-risk-dashboard" style="max-width:1180px;margin:32px auto;padding:24px">
            <header style="margin-bottom:24px">
                <div style="font-size:12px;letter-spacing:.12em;text-transform:uppercase;opacity:.65">ARKANA CIVIEL · MATTER INTELLIGENCE</div>
                <h1 style="margin:6px 0">Matter Risk Center</h1>
                <p style="margin:0;opacity:.7">Prioritise matters that require Managing Partner attention.</p>
            </header>
            <div style="display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:14px;margin-bottom:24px">
                <div><strong><?php echo esc_html($intel['attention_required']); ?></strong><br><small>Attention Required</small></div>
                <div><strong><?php echo esc_html($c['critical']); ?></strong><br><small>Critical</small></div>
                <div><strong><?php echo esc_html($c['warning']); ?></strong><br><small>Warning</small></div>
                <div><strong><?php echo esc_html($c['normal']); ?></strong><br><small>Normal</small></div>
            </div>
            <div>
                <h2>Priority Queue</h2>
                <?php if (empty($snapshot['matters'])): ?>
                    <p>No live matter records are currently exposed to this dashboard. Connect the V7.12 snapshot provider to populate the queue.</p>
                <?php else: foreach ($snapshot['matters'] as $matter): if ($matter['risk'] === 'normal') continue; ?>
                    <article style="padding:16px 0;border-bottom:1px solid rgba(0,0,0,.1)">
                        <strong><?php echo esc_html($matter['title'] ?? $matter['name'] ?? 'Matter'); ?></strong>
                        <span style="margin-left:12px;text-transform:uppercase;font-size:11px"><?php echo esc_html($matter['risk']); ?></span>
                        <?php if (!empty($matter['due_date'])): ?><div style="font-size:13px;opacity:.7">Due: <?php echo esc_html($matter['due_date']); ?></div><?php endif; ?>
                    </article>
                <?php endforeach; endif; ?>
            </div>
        </section>
        <?php return ob_get_clean();
    }
}
ACLM_V713_Matter_Risk_Dashboard::init();
}
