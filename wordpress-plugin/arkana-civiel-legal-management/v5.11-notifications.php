<?php
/**
 * V5.11 Notifications foundation.
 */
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'ACLM_V511_Notifications' ) ) {
final class ACLM_V511_Notifications {
    const POST_TYPE = 'ac_notification';
    const META_USER = '_aclm_notification_user_id';
    const META_TYPE = '_aclm_notification_type';
    const META_REF  = '_aclm_notification_ref_id';
    const META_READ = '_aclm_notification_read';

    public static function init() {
        add_shortcode( 'arkana_notifications', array( __CLASS__, 'shortcode' ) );
        add_action( 'admin_bar_menu', array( __CLASS__, 'admin_bar' ), 100 );
    }

    public static function create( $user_id, $title, $message, $type = 'general', $ref_id = 0 ) {
        $user_id = absint( $user_id );
        $title   = sanitize_text_field( $title );
        $message = sanitize_textarea_field( $message );
        $allowed = array( 'general', 'request', 'task', 'deadline', 'matter', 'billing', 'document' );
        if ( ! $user_id || '' === $title || '' === $message ) return 0;
        if ( ! in_array( $type, $allowed, true ) ) $type = 'general';
        $id = wp_insert_post( array(
            'post_type'   => self::POST_TYPE,
            'post_status' => 'publish',
            'post_title'  => $title,
            'post_content' => $message,
        ), true );
        if ( is_wp_error( $id ) ) return 0;
        update_post_meta( $id, self::META_USER, $user_id );
        update_post_meta( $id, self::META_TYPE, $type );
        update_post_meta( $id, self::META_REF, absint( $ref_id ) );
        update_post_meta( $id, self::META_READ, 0 );
        return (int) $id;
    }

    public static function mark_read( $notification_id ) {
        $notification_id = absint( $notification_id );
        if ( ! $notification_id || ! is_user_logged_in() ) return false;
        $owner = (int) get_post_meta( $notification_id, self::META_USER, true );
        if ( $owner !== get_current_user_id() ) return false;
        update_post_meta( $notification_id, self::META_READ, 1 );
        return true;
    }

    public static function unread_count( $user_id = 0 ) {
        $user_id = $user_id ? absint( $user_id ) : get_current_user_id();
        if ( ! $user_id ) return 0;
        $q = new WP_Query( array(
            'post_type' => self::POST_TYPE,
            'post_status' => 'publish',
            'posts_per_page' => 1,
            'fields' => 'ids',
            'meta_query' => array(
                array( 'key' => self::META_USER, 'value' => (string) $user_id, 'compare' => '=' ),
                array( 'key' => self::META_READ, 'value' => '0', 'compare' => '=' ),
            ),
        ) );
        return (int) $q->found_posts;
    }

    public static function shortcode() {
        if ( ! is_user_logged_in() ) return '<p>Silakan login untuk melihat notifikasi.</p>';
        $q = new WP_Query( array(
            'post_type' => self::POST_TYPE,
            'post_status' => 'publish',
            'posts_per_page' => 30,
            'meta_query' => array( array( 'key' => self::META_USER, 'value' => (string) get_current_user_id(), 'compare' => '=' ) ),
            'orderby' => 'date', 'order' => 'DESC',
        ) );
        ob_start(); ?>
        <section class="aclm-notifications" style="max-width:900px;margin:40px auto;padding:0 20px;">
            <h1>Notifications</h1>
            <p>Unread: <strong><?php echo esc_html( self::unread_count() ); ?></strong></p>
            <div><?php if ( ! $q->have_posts() ) : ?><p>Belum ada notifikasi.</p><?php else : while ( $q->have_posts() ) : $q->the_post(); $id = get_the_ID(); $read = (bool) get_post_meta( $id, self::META_READ, true ); ?>
                <article style="padding:16px;border:1px solid #dfe5ea;border-radius:10px;margin:10px 0;<?php echo $read ? '' : 'font-weight:600;'; ?>">
                    <div><?php echo esc_html( get_the_title() ); ?></div>
                    <p><?php echo esc_html( get_the_content() ); ?></p>
                    <small><?php echo esc_html( get_post_meta( $id, self::META_TYPE, true ) ?: 'general' ); ?> · <?php echo esc_html( get_the_date() ); ?></small>
                </article>
            <?php endwhile; wp_reset_postdata(); endif; ?></div>
        </section>
        <?php return ob_get_clean();
    }

    public static function admin_bar( $wp_admin_bar ) {
        if ( ! is_user_logged_in() ) return;
        $count = self::unread_count();
        if ( $count < 1 ) return;
        $wp_admin_bar->add_node( array( 'id' => 'aclm-notifications', 'title' => 'Notifications (' . $count . ')' ) );
    }
}
ACLM_V511_Notifications::init();
}
