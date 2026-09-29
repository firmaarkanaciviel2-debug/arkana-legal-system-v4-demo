<?php
/**
 * V7.0 Arkana Civiel AI Agent Core foundation.
 *
 * This layer deliberately does NOT call an external AI provider yet. It defines
 * the authorization-aware agent contract and read-only tool registry that a
 * provider adapter can use in a later V7.x release.
 */
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'ACLM_V70_AI_Agent_Core' ) ) {
final class ACLM_V70_AI_Agent_Core {
    const CPT = 'ac_ai_agent_run';

    public static function init() {
        add_action( 'init', array( __CLASS__, 'register' ) );
        add_shortcode( 'arkana_ai_agent', array( __CLASS__, 'shortcode' ) );
    }

    public static function register() {
        register_post_type( self::CPT, array(
            'labels' => array( 'name' => 'AI Agent Runs', 'singular_name' => 'AI Agent Run' ),
            'public' => false,
            'show_ui' => true,
            'show_in_menu' => true,
            'supports' => array( 'title' ),
            'capability_type' => 'post',
            'map_meta_cap' => true,
        ) );
    }

    /** Read-only tools exposed to the agent. Actions are intentionally absent in V7.0. */
    public static function tools() {
        return apply_filters( 'aclm_v70_ai_tools', array(
            'matter_summary' => array( 'description' => 'Read a Matter summary after authorization.', 'mode' => 'read' ),
            'matter_deadlines' => array( 'description' => 'Read Matter deadlines after authorization.', 'mode' => 'read' ),
            'matter_tasks' => array( 'description' => 'Read Matter tasks after authorization.', 'mode' => 'read' ),
            'retainer_summary' => array( 'description' => 'Read an authorized retainer summary.', 'mode' => 'read' ),
            'litigation_timeline' => array( 'description' => 'Read an authorized litigation timeline.', 'mode' => 'read' ),
            'communication_summary' => array( 'description' => 'Read authorized Matter communications.', 'mode' => 'read' ),
            'management_metrics' => array( 'description' => 'Read management metrics for privileged users.', 'mode' => 'read' ),
        ) );
    }

    public static function can_use() {
        if ( ! is_user_logged_in() ) return false;
        $u = wp_get_current_user();
        return current_user_can( 'manage_options' ) || (bool) array_intersect( array( 'ac_managing_partner', 'ac_partner', 'ac_lawyer' ), (array) $u->roles );
    }

    public static function can_access_matter( $matter_id ) {
        $matter_id = absint( $matter_id );
        if ( ! $matter_id ) return false;
        if ( class_exists( 'ACLM_V512_Audit_Security' ) ) return (bool) ACLM_V512_Audit_Security::can_access_matter( $matter_id );
        return current_user_can( 'manage_options' );
    }

    /** Create an auditable, provider-neutral agent run record. */
    public static function create_run( $prompt, $context = array() ) {
        if ( ! self::can_use() ) return 0;
        $prompt = sanitize_textarea_field( $prompt );
        if ( ! $prompt ) return 0;
        $matter_id = isset( $context['matter_id'] ) ? absint( $context['matter_id'] ) : 0;
        if ( $matter_id && ! self::can_access_matter( $matter_id ) ) return 0;
        $id = wp_insert_post( array(
            'post_type' => self::CPT,
            'post_status' => 'publish',
            'post_title' => 'AI Run — ' . current_time( 'mysql' ),
        ), true );
        if ( is_wp_error( $id ) ) return 0;
        update_post_meta( $id, '_aclm_ai_prompt', $prompt );
        update_post_meta( $id, '_aclm_ai_context', wp_json_encode( $context ) );
        update_post_meta( $id, '_aclm_ai_status', 'pending' );
        update_post_meta( $id, '_aclm_ai_tools', wp_json_encode( array_keys( self::tools() ) ) );
        if ( class_exists( 'ACLM_V512_Audit_Security' ) ) ACLM_V512_Audit_Security::log( 'create', 'ai_agent_run', $id, array( 'matter_id' => $matter_id ) );
        return (int) $id;
    }

    public static function shortcode() {
        if ( ! self::can_use() ) return '<p>Akses AI Agent ditolak.</p>';
        ob_start(); ?>
        <section class="aclm-v70-ai-agent" style="max-width:1100px;margin:40px auto;padding:0 20px;">
            <h1>Arkana Civiel AI Agent</h1>
            <p>AI Agent Core aktif. V7.0 menggunakan tool registry read-only; koneksi provider AI dan action tools akan ditambahkan pada tahap berikutnya.</p>
            <h2>Available Tools</h2>
            <ul>
            <?php foreach ( self::tools() as $key => $tool ) : ?>
                <li><strong><?php echo esc_html( $key ); ?></strong> — <?php echo esc_html( $tool['description'] ); ?></li>
            <?php endforeach; ?>
            </ul>
        </section>
        <?php return ob_get_clean();
    }
}
ACLM_V70_AI_Agent_Core::init();
}
