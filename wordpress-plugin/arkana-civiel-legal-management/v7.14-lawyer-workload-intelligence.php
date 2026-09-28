<?php
/**
 * V7.14 — Lawyer Workload & Performance Intelligence
 * Non-mutating operational analytics layer for the Arkana Civiel dashboard.
 */
defined('ABSPATH') || exit;

if (!class_exists('ACLM_V714_Lawyer_Workload')) {
final class ACLM_V714_Lawyer_Workload {
    public static function init() {
        add_shortcode('arkana_lawyer_workload', array(__CLASS__, 'shortcode'));
    }

    private static function snapshot() {
        $snapshot = array(
            'matters'  => array(),
            'requests' => array(),
            'tasks'    => array(),
        );
        return apply_filters('aclm_v712_dashboard_snapshot', $snapshot);
    }

    private static function name_from_item($item) {
        foreach (array('lawyer','assigned_lawyer','assignee','owner','lawyer_name','assigned_to') as $key) {
            if (isset($item[$key]) && is_string($item[$key]) && trim($item[$key]) !== '') return trim($item[$key]);
            if (is_object($item) && isset($item->{$key}) && is_string($item->{$key}) && trim($item->{$key}) !== '') return trim($item->{$key});
        }
        return 'Unassigned';
    }

    private static function status_from_item($item) {
        foreach (array('status','state') as $key) {
            if (isset($item[$key])) return strtolower((string)$item[$key]);
            if (is_object($item) && isset($item->{$key})) return strtolower((string)$item->{$key});
        }
        return '';
    }

    private static function due_from_item($item) {
        foreach (array('due_date','deadline','due','sla_due') as $key) {
            $value = isset($item[$key]) ? $item[$key] : (is_object($item) && isset($item->{$key}) ? $item->{$key} : '');
            if ($value) { $ts = strtotime((string)$value); if ($ts) return $ts; }
        }
        return 0;
    }

    private static function calculate() {
        $s = self::snapshot();
        $people = array();
        foreach (array('matters','requests','tasks') as $bucket) {
            $items = isset($s[$bucket]) && is_array($s[$bucket]) ? $s[$bucket] : array();
            foreach ($items as $item) {
                $person = self::name_from_item($item);
                if (!isset($people[$person])) $people[$person] = array('matters'=>0,'requests'=>0,'tasks'=>0,'overdue'=>0,'due_soon'=>0,'open'=>0);
                $people[$person][$bucket === 'matters' ? 'matters' : ($bucket === 'requests' ? 'requests' : 'tasks')]++;
                $status = self::status_from_item($item);
                if (!in_array($status, array('completed','complete','closed','done','resolved','cancelled'), true)) $people[$person]['open']++;
                $due = self::due_from_item($item);
                if ($due) {
                    $days = floor(($due - current_time('timestamp')) / DAY_IN_SECONDS);
                    if ($days < 0) $people[$person]['overdue']++;
                    elseif ($days <= 3) $people[$person]['due_soon']++;
                }
            }
        }
        foreach ($people as $person => &$row) {
            $score = ($row['matters'] * 3) + ($row['requests'] * 2) + $row['tasks'] + ($row['overdue'] * 5) + ($row['due_soon'] * 2);
            $row['score'] = $score;
            $row['load'] = $score >= 15 ? 'High' : ($score >= 7 ? 'Medium' : 'Normal');
        }
        unset($row);
        uasort($people, function($a,$b){ return $b['score'] <=> $a['score']; });
        return $people;
    }

    public static function shortcode() {
        if (!is_user_logged_in() || !current_user_can('edit_posts')) return '<p>Akses workload intelligence ditolak.</p>';
        $people = self::calculate();
        $total_open = 0; $total_overdue = 0; $high = 0;
        foreach ($people as $row) { $total_open += $row['open']; $total_overdue += $row['overdue']; if ($row['load'] === 'High') $high++; }
        ob_start(); ?>
        <section class="aclm-v714" style="max-width:1200px;margin:40px auto;padding:24px;font-family:system-ui,-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif;background:#f7f8fa;border-radius:24px">
          <div style="display:flex;justify-content:space-between;gap:20px;align-items:flex-start;flex-wrap:wrap">
            <div><div style="font-size:12px;letter-spacing:.14em;text-transform:uppercase;color:#9a7a35;font-weight:700">ARKANA CIVIEL · V7.14</div><h2 style="margin:6px 0">Lawyer Workload Intelligence</h2><p style="margin:0;color:#667085">Operational workload signals for management review — not a legal performance verdict.</p></div>
            <div style="padding:10px 14px;background:#fff;border-radius:12px;border:1px solid #e5e7eb;font-size:13px">Non-mutating analytics</div>
          </div>
          <div style="display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:14px;margin:24px 0">
            <div style="background:#fff;border:1px solid #e5e7eb;border-radius:16px;padding:18px"><small>OPEN WORK</small><div style="font-size:30px;font-weight:700;margin-top:5px"><?php echo esc_html($total_open); ?></div></div>
            <div style="background:#fff;border:1px solid #e5e7eb;border-radius:16px;padding:18px"><small>OVERDUE</small><div style="font-size:30px;font-weight:700;margin-top:5px"><?php echo esc_html($total_overdue); ?></div></div>
            <div style="background:#fff;border:1px solid #e5e7eb;border-radius:16px;padding:18px"><small>HIGH LOAD</small><div style="font-size:30px;font-weight:700;margin-top:5px"><?php echo esc_html($high); ?></div></div>
          </div>
          <?php if (!$people): ?><div style="background:#fff;border:1px dashed #d0d5dd;border-radius:16px;padding:30px;text-align:center;color:#667085">Belum ada workload records dari V7.12. Hubungkan canonical matter/request/task repository melalui <code>aclm_v712_dashboard_snapshot</code>.</div>
          <?php else: ?><div style="overflow:auto;background:#fff;border:1px solid #e5e7eb;border-radius:16px"><table style="width:100%;border-collapse:collapse;min-width:760px"><thead><tr style="text-align:left;background:#fafafa"><th style="padding:14px">Lawyer / Assignee</th><th>Matters</th><th>Requests</th><th>Tasks</th><th>Open</th><th>Overdue</th><th>Due ≤3d</th><th>Load</th></tr></thead><tbody><?php foreach($people as $name=>$row): ?><tr style="border-top:1px solid #eef0f2"><td style="padding:14px;font-weight:600"><?php echo esc_html($name); ?></td><td><?php echo esc_html($row['matters']); ?></td><td><?php echo esc_html($row['requests']); ?></td><td><?php echo esc_html($row['tasks']); ?></td><td><?php echo esc_html($row['open']); ?></td><td><?php echo esc_html($row['overdue']); ?></td><td><?php echo esc_html($row['due_soon']); ?></td><td><strong><?php echo esc_html($row['load']); ?></strong></td></tr><?php endforeach; ?></tbody></table></div><?php endif; ?>
          <p style="font-size:12px;color:#667085;margin-top:16px">Load is an operational heuristic based on assigned work and deadline signals. It must not be used alone for HR, disciplinary, compensation, or legal decisions.</p>
        </section>
        <?php return ob_get_clean();
    }
}
ACLM_V714_Lawyer_Workload::init();
}
