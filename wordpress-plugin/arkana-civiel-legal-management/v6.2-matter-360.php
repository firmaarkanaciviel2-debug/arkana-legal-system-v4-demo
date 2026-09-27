<?php
/** V6.2 Matter 360 foundation. */
defined( 'ABSPATH' ) || exit;
if ( ! class_exists( 'ACLM_V62_Matter_360' ) ) {
final class ACLM_V62_Matter_360 {
    public static function init() { add_shortcode( 'arkana_matter_360', array( __CLASS__, 'shortcode' ) ); }
    private static function count_for_matter( $type, $matter_id, $meta_key = '_aclm_matter_id' ) {
        if ( ! $matter_id || ! post_type_exists( $type ) ) return 0;
        $q = new WP_Query(array('post_type'=>$type,'post_status'=>'publish','posts_per_page'=>1,'fields'=>'ids','meta_query'=>array(array('key'=>$meta_key,'value'=>(string)$matter_id,'compare'=>'='))));
        return (int)$q->found_posts;
    }
    public static function profile( $matter_id ) {
        $matter_id=absint($matter_id);
        if(!$matter_id || 'ac_matter'!==get_post_type($matter_id)) return array();
        return array(
            'id'=>$matter_id,
            'name'=>get_the_title($matter_id),
            'client_id'=>(int)get_post_meta($matter_id,'_aclm_client_id',true),
            'assigned_lawyer'=>(int)get_post_meta($matter_id,'_aclm_assigned_lawyer',true),
            'tasks'=>self::count_for_matter('ac_task',$matter_id),
            'deadlines'=>self::count_for_matter('ac_deadline',$matter_id),
            'documents'=>self::count_for_matter('ac_document',$matter_id),
            'legal_requests'=>self::count_for_matter('ac_legal_request',$matter_id),
        );
    }
    private static function can_access($matter_id){
        if(class_exists('ACLM_V512_Audit_Security')) return ACLM_V512_Audit_Security::can_access_matter($matter_id);
        return current_user_can('manage_options');
    }
    public static function shortcode($atts){
        if(!is_user_logged_in()) return '<p>Silakan login untuk mengakses Matter 360.</p>';
        $atts=shortcode_atts(array('matter_id'=>0),$atts,'arkana_matter_360');
        $matter_id=absint($atts['matter_id']);
        if(!$matter_id) return '<p>Matter belum ditentukan.</p>';
        if(!self::can_access($matter_id)) return '<p>Akses Matter 360 ditolak.</p>';
        $p=self::profile($matter_id); if(!$p) return '<p>Matter tidak ditemukan.</p>';
        $client=$p['client_id'] ? get_the_title($p['client_id']) : '—';
        $lawyer=$p['assigned_lawyer'] ? get_userdata($p['assigned_lawyer']) : false;
        ob_start(); ?>
        <section class="aclm-v62-matter360" style="max-width:1100px;margin:40px auto;padding:0 20px;">
        <h1>Matter 360 — <?php echo esc_html($p['name']); ?></h1>
        <p><strong>Client:</strong> <?php echo esc_html($client); ?> &nbsp; <strong>Assigned Lawyer:</strong> <?php echo esc_html($lawyer ? $lawyer->display_name : '—'); ?></p>
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:14px;">
        <?php foreach(array('tasks'=>'Tasks','deadlines'=>'Deadlines','documents'=>'Documents','legal_requests'=>'Legal Requests') as $k=>$label): ?>
        <div style="border:1px solid #dfe5ea;border-radius:10px;padding:18px;background:#fff;"><small><?php echo esc_html($label); ?></small><div style="font-size:28px;font-weight:700;"><?php echo esc_html($p[$k]); ?></div></div>
        <?php endforeach; ?></div></section><?php return ob_get_clean();
    }
}
ACLM_V62_Matter_360::init();
}
