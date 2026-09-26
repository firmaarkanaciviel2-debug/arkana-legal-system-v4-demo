<?php
/**
 * V5.5 Client Portal foundation.
 */
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'ACLM_V55_Client_Portal' ) ) {
final class ACLM_V55_Client_Portal {
    const SHORTCODE = 'arkana_client_portal';

    public static function init() {
        add_shortcode( self::SHORTCODE, array( __CLASS__, 'shortcode' ) );
        add_action( 'template_redirect', array( __CLASS__, 'guard_portal_routes' ) );
    }

    private static function is_client_role() {
        $user = wp_get_current_user();
        return $user && ( in_array( 'ac_client_admin', (array) $user->roles, true ) || in_array( 'ac_client_user', (array) $user->roles, true ) );
    }

    public static function guard_portal_routes() {
        if ( ! is_user_logged_in() && is_page() && has_shortcode( get_post_field( 'post_content', get_queried_object_id() ), self::SHORTCODE ) ) {
            auth_redirect();
        }
    }

    private static function client_id() {
        return (int) get_user_meta( get_current_user_id(), '_aclm_client_id', true );
    }

    private static function query_matters( $client_id ) {
        if ( ! $client_id ) {
            return array();
        }
        $q = new WP_Query(
            array(
                'post_type' => 'ac_matter',
                'post_status' => 'publish',
                'posts_per_page' => 20,
                'meta_query' => array(
                    array( 'key' => '_aclm_client_id', 'value' => (string) $client_id, 'compare' => '=' ),
                ),
            )
        );
        return $q->posts;
    }

    public static function shortcode() {
        if ( ! is_user_logged_in() ) {
            return '<p>Silakan login untuk mengakses Client Portal Arkana Civiel.</p>';
        }
        if ( ! self::is_client_role() ) {
            return '<p>Akses Client Portal hanya tersedia untuk akun klien.</p>';
        }

        $client_id = self::client_id();
        $matters = self::query_matters( $client_id );
        ob_start();
        ?>
        <section class="aclm-client-portal" style="max-width:1100px;margin:40px auto;padding:0 20px;">
            <div style="background:#071b2e;color:#fff;padding:30px;border-radius:16px;margin-bottom:22px;">
                <div style="color:#dfaf45;font-size:11px;font-weight:700;letter-spacing:.12em;text-transform:uppercase;">Arkana Civiel</div>
                <h1 style="color:#fff;margin:8px 0;">Client Portal</h1>
                <p style="margin:0;color:#d9e2ea;">Pantau perkara dan layanan hukum Anda dari satu tempat.</p>
            </div>
            <?php if ( ! $client_id ) : ?>
                <div style="padding:18px;border:1px solid #f0c36d;border-radius:12px;background:#fff8e8;">Akun Anda belum terhubung ke profil klien. Hubungi administrator Arkana Civiel untuk aktivasi.</div>
            <?php elseif ( empty( $matters ) ) : ?>
                <div style="padding:18px;border:1px solid #dfe5ea;border-radius:12px;background:#fff;">Belum ada perkara yang tersedia untuk akun Anda.</div>
            <?php else : ?>
                <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:16px;">
                    <?php foreach ( $matters as $matter ) : ?>
                        <article style="border:1px solid #dfe5ea;border-radius:12px;background:#fff;padding:20px;">
                            <h2 style="font-size:19px;margin-top:0;"><?php echo esc_html( get_the_title( $matter ) ); ?></h2>
                            <p style="margin-bottom:0;color:#64748b;">Status: <?php echo esc_html( get_post_meta( $matter->ID, '_aclm_status', true ) ?: '—' ); ?></p>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            <p style="margin-top:22px;color:#64748b;font-size:13px;">V5.5 foundation: portal hanya menampilkan Matter yang memiliki relasi Client ID yang sama dengan akun pengguna. Private documents dan granular Matter authorization akan di-hardening pada V5.9/V5.12.</p>
        </section>
        <?php
        return ob_get_clean();
    }
}
ACLM_V55_Client_Portal::init();
}
