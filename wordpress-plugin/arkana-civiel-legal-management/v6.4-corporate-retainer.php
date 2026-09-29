<?php
/** V6.4 Corporate Retainer Management foundation. */
defined( 'ABSPATH' ) || exit;
if ( ! class_exists( 'ACLM_V64_Corporate_Retainer' ) ) {
final class ACLM_V64_Corporate_Retainer {
    const CPT = 'ac_retainer_service';
    public static function init() { add_action('init',array(__CLASS__,'register')); add_shortcode('arkana_retainer_360',array(__CLASS__,'shortcode')); }
    public static function register() {
        register_post_type(self::CPT,array('labels'=>array('name'=>'Corporate Retainer Services','singular_name'=>'Retainer Service'),'public'=>false,'show_ui'=>true,'show_in_menu'=>true,'supports'=>array('title','editor'),'capability_type'=>'post','map_meta_cap'=>true));
    }
    public static function profile($retainer_id){
        $retainer_id=absint($retainer_id); if(!$retainer_id || self::CPT!==get_post_type($retainer_id)) return array();
        return array(
            'id'=>$retainer_id,
            'name'=>get_the_title($retainer_id),
            'client_id'=>(int)get_post_meta($retainer_id,'_aclm_client_id',true),
            'status'=>sanitize_text_field(get_post_meta($retainer_id,'_aclm_retainer_status',true)),
            'start_date'=>sanitize_text_field(get_post_meta($retainer_id,'_aclm_start_date',true)),
            'end_date'=>sanitize_text_field(get_post_meta($retainer_id,'_aclm_end_date',true)),
            'monthly_fee'=>sanitize_text_field(get_post_meta($retainer_id,'_aclm_monthly_fee',true)),
            'sla'=>sanitize_text_field(get_post_meta($retainer_id,'_aclm_sla',true)),
        );
    }
    private static function can_access($retainer_id){
        $client_id=(int)get_post_meta($retainer_id,'_aclm_client_id',true); $u=wp_get_current_user();
        if(current_user_can('manage_options')) return true;
        if(in_array('ac_managing_partner',(array)$u->roles,true) || in_array('ac_partner',(array)$u->roles,true)) return true;
        return $client_id && (int)get_user_meta($u->ID,'_aclm_client_id',true)===$client_id;
    }
    public static function shortcode($atts){
        if(!is_user_logged_in()) return '<p>Silakan login untuk mengakses Retainer 360.</p>';
        $atts=shortcode_atts(array('retainer_id'=>0),$atts,'arkana_retainer_360'); $id=absint($atts['retainer_id']);
        if(!$id || !self::can_access($id)) return '<p>Akses Retainer 360 ditolak.</p>';
        $p=self::profile($id); if(!$p) return '<p>Retainer tidak ditemukan.</p>';
        $client=$p['client_id'] ? get_the_title($p['client_id']) : '—';
        ob_start(); ?>
        <section class="aclm-v64-retainer360" style="max-width:1100px;margin:40px auto;padding:0 20px;">
        <h1>Retainer 360 — <?php echo esc_html($p['name']); ?></h1>
        <p><strong>Client:</strong> <?php echo esc_html($client); ?> &nbsp; <strong>Status:</strong> <?php echo esc_html($p['status'] ?: '—'); ?></p>
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:14px;">
        <?php foreach(array('start_date'=>'Start','end_date'=>'End','monthly_fee'=>'Monthly Fee','sla'=>'SLA') as $k=>$label): ?>
        <div style="border:1px solid #dfe5ea;border-radius:10px;padding:18px;background:#fff;"><small><?php echo esc_html($label); ?></small><div style="font-size:20px;font-weight:700;"><?php echo esc_html($p[$k] ?: '—'); ?></div></div>
        <?php endforeach; ?></div></section><?php return ob_get_clean();
    }
}
ACLM_V64_Corporate_Retainer::init();
}
