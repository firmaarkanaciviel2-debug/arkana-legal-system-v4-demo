<?php
/**
 * V6.0 Legal Operations Core foundation.
 * Centralizes cross-module Matter/Client operational summaries without replacing
 * the existing V5 domain modules.
 */
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'ACLM_V60_Legal_Operations_Core' ) ) {
final class ACLM_V60_Legal_Operations_Core {
    public static function init() {
        add_shortcode( 'arkana_legal_operations', array( __CLASS__, 'shortcode' ) );
    }

    private static function count_posts( $type, $meta = array() ) {
        $args = array( 'post_type' => $type, 'post_status' => 'publish', 'posts_per_page' => 1, 'fields' => 'ids' );
        if ( $meta ) $args['meta_query'] = $meta;
        $q = new WP_Query( $args );
        return (int) $q->found_posts;
    }

    public static function summary( $user_id = 0 ) {
        $user_id = $user_id ? absint( $user_id ) : get_current_user_id();
        $summary = array(
            'clients' => self::count_posts( 'ac_client' ),
            'matters' => self::count_posts( 'ac_matter' ),
            'legal_requests' => self::count_posts( 'ac_legal_request' ),
            'tasks' => self::count_posts( 'ac_task' ),
            'deadlines' => self::count_posts( 'ac_deadline' ),
            'retainers' => self::count_posts( 'ac_retainer' ),
            'invoices' => self::count_posts( 'ac_invoice' ),
            'notifications' => self::count_posts( 'ac_notification', array( array( 'key' => '_aclm_notification_user_id', 'value' => (string) $user_id ) ) ),
        );
        return apply_filters( 'aclm_v60_operations_summary', $summary, $user_id );
    }

    public static function shortcode() {
        if ( ! is_user_logged_in() ) return '<p>Silakan login untuk mengakses Legal Operations.</p>';
        $s = self::summary();
        ob_start(); ?>
        <section class="aclm-v60-operations" style="max-width:1100px;margin:40px auto;padding:0 20px;">
            <h1>Arkana Civiel — Legal Operations</h1>
            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:14px;">
            <?php foreach ( $s as $key => $value ) : ?>
                <div style="border:1px solid #dfe5ea;border-radius:10px;padding:18px;background:#fff;">
                    <small><?php echo esc_html( ucwords( str_replace( '_', ' ', $key ) ) ); ?></small>
                    <div style="font-size:28px;font-weight:700;"><?php echo esc_html( $value ); ?></div>
                </div>
            <?php endforeach; ?>
            </div>
        </section>
        <?php return ob_get_clean();
    }
}
ACLM_V60_Legal_Operations_Core::init();
}
