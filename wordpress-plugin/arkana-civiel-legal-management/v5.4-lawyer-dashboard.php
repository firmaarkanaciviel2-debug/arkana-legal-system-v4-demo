<?php
/**
 * V5.4 Lawyer Dashboard foundation.
 * Loaded by the main plugin in the next integration commit.
 */
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'ACLM_V54_Lawyer_Dashboard' ) ) {
final class ACLM_V54_Lawyer_Dashboard {
    public static function init() {
        add_action( 'admin_menu', array( __CLASS__, 'menu' ), 30 );
    }

    public static function menu() {
        if ( ! current_user_can( 'manage_arkana_legal' ) && ! current_user_can( 'edit_ac_matter' ) ) {
            return;
        }
        add_submenu_page(
            'arkana-legal-management',
            'Lawyer Dashboard',
            'Lawyer Dashboard',
            'edit_ac_matter',
            'arkana-lawyer-dashboard',
            array( __CLASS__, 'render' )
        );
    }

    private static function count( $post_type, $meta_key = '', $meta_value = '' ) {
        $args = array( 'post_type' => $post_type, 'post_status' => 'publish', 'fields' => 'ids', 'posts_per_page' => 1 );
        if ( $meta_key ) {
            $args['meta_query'] = array( array( 'key' => $meta_key, 'value' => $meta_value, 'compare' => '=' ) );
        }
        $query = new WP_Query( $args );
        return (int) $query->found_posts;
    }

    public static function render() {
        if ( ! current_user_can( 'edit_ac_matter' ) ) {
            wp_die( esc_html__( 'Anda tidak memiliki akses ke Lawyer Dashboard.', 'arkana-civiel-legal-management' ) );
        }
        $uid = get_current_user_id();
        $matters = self::count( 'ac_matter', '_aclm_lead_lawyer', (string) $uid );
        $tasks = self::count( 'ac_task', '_aclm_assignee', (string) $uid );
        $today = gmdate( 'Y-m-d' );
        $args = array(
            'post_type' => 'ac_deadline', 'post_status' => 'publish', 'fields' => 'ids', 'posts_per_page' => 1,
            'meta_query' => array( array( 'key' => '_aclm_due_date', 'value' => $today, 'compare' => '<=', 'type' => 'DATE' ) ),
        );
        $deadline_query = new WP_Query( $args );
        ?>
        <div class="wrap">
            <div style="background:#071b2e;color:#fff;padding:28px 32px;border-radius:14px;margin:20px 0;">
                <div style="color:#dfaf45;font-weight:700;letter-spacing:.12em;text-transform:uppercase;font-size:11px;">Arkana Civiel · V5.4</div>
                <h1 style="color:#fff;margin-bottom:8px;">Lawyer Dashboard</h1>
                <p style="margin:0;color:#d9e2ea;">Fokus pada perkara, tugas, dan deadline yang menjadi tanggung jawab Anda.</p>
            </div>
            <div style="display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:16px;max-width:1000px;">
                <div style="background:#fff;border:1px solid #dfe5ea;border-radius:12px;padding:20px;"><strong style="font-size:30px;color:#071b2e;"><?php echo esc_html( $matters ); ?></strong><div>Perkara saya</div></div>
                <div style="background:#fff;border:1px solid #dfe5ea;border-radius:12px;padding:20px;"><strong style="font-size:30px;color:#071b2e;"><?php echo esc_html( $tasks ); ?></strong><div>Tugas saya</div></div>
                <div style="background:#fff;border:1px solid #dfe5ea;border-radius:12px;padding:20px;"><strong style="font-size:30px;color:#071b2e;"><?php echo esc_html( $deadline_query->found_posts ); ?></strong><div>Deadline jatuh tempo/terlewat</div></div>
            </div>
            <div style="margin-top:18px;max-width:1000px;background:#f7f6f2;border-left:4px solid #dfaf45;padding:14px 16px;"><strong>Catatan V5.4:</strong> dashboard ini hanya menghitung objek yang secara eksplisit ditugaskan kepada user. Detail matter, dokumen private, dan client isolation akan mengikuti authorization layer V5.6/V5.9.</div>
        </div>
        <?php
    }
}
ACLM_V54_Lawyer_Dashboard::init();
}
