<?php
/** V6.3 Litigation Management foundation. */
defined( 'ABSPATH' ) || exit;
if ( ! class_exists( 'ACLM_V63_Litigation_Management' ) ) {
final class ACLM_V63_Litigation_Management {
    const CPT = 'ac_litigation_event';
    public static function init() { add_action('init',array(__CLASS__,'register')); add_shortcode('arkana_litigation_360',array(__CLASS__,'shortcode')); }
    public static function register() {
        register_post_type(self::CPT,array('labels'=>array('name'=>'Litigation Events','singular_name'=>'Litigation Event'),'public'=>false,'show_ui'=>true,'show_in_menu'=>true,'supports'=>array('title','editor'),'capability_type'=>'post','map_meta_cap'=>true));
    }
    public static function add_event($matter_id,$type,$title,$date='',$meta=array()) {
        $matter_id=absint($matter_id); $type=sanitize_key($type); $title=sanitize_text_field($title);
        if(!$matter_id || 'ac_matter'!==get_post_type($matter_id) || !$type || !$title) return 0;
        if(class_exists('ACLM_V512_Audit_Security') && !ACLM_V512_Audit_Security::can_access_matter($matter_id)) return 0;
        $id=wp_insert_post(array('post_type'=>self::CPT,'post_status'=>'publish','post_title'=>$title,'post_content'=>wp_kses_post($meta['notes']??'')),true);
        if(is_wp_error($id)) return 0;
        update_post_meta($id,'_aclm_matter_id',$matter_id); update_post_meta($id,'_aclm_litigation_event_type',$type);
        if($date) update_post_meta($id,'_aclm_event_date',sanitize_text_field($date));
        if(class_exists('ACLM_V512_Audit_Security')) ACLM_V512_Audit_Security::log('create','litigation_event',$id,array('matter_id'=>$matter_id,'type'=>$type));
        return (int)$id;
    }
    public static function events($matter_id) {
        $matter_id=absint($matter_id); if(!$matter_id) return array();
        if(class_exists('ACLM_V512_Audit_Security') && !ACLM_V512_Audit_Security::can_access_matter($matter_id)) return array();
        return get_posts(array('post_type'=>self::CPT,'post_status'=>'publish','numberposts'=>100,'meta_key'=>'_aclm_matter_id','meta_value'=>(string)$matter_id,'orderby'=>'meta_value','order'=>'ASC'));
    }
    public static function shortcode($atts) {
        if(!is_user_logged_in()) return '<p>Silakan login untuk mengakses Litigation Management.</p>';
        $atts=shortcode_atts(array('matter_id'=>0),$atts,'arkana_litigation_360'); $matter_id=absint($atts['matter_id']);
        if(!$matter_id) return '<p>Matter belum ditentukan.</p>';
        if(class_exists('ACLM_V512_Audit_Security') && !ACLM_V512_Audit_Security::can_access_matter($matter_id)) return '<p>Akses perkara ditolak.</p>';
        $events=self::events($matter_id); ob_start(); ?>
        <section class="aclm-v63-litigation" style="max-width:1100px;margin:40px auto;padding:0 20px;">
        <h1>Litigation 360 — <?php echo esc_html(get_the_title($matter_id)); ?></h1>
        <div style="display:grid;gap:12px;">
        <?php if(!$events): ?><p>Belum ada litigation event.</p><?php endif; ?>
        <?php foreach($events as $event): ?><article style="border:1px solid #dfe5ea;border-radius:10px;padding:16px;background:#fff;"><strong><?php echo esc_html($event->post_title); ?></strong><div><?php echo esc_html(get_post_meta($event->ID,'_aclm_event_date',true)); ?> · <?php echo esc_html(ucwords(str_replace('_',' ',get_post_meta($event->ID,'_aclm_litigation_event_type',true)))); ?></div><p><?php echo esc_html(wp_strip_all_tags($event->post_content)); ?></p></article><?php endforeach; ?>
        </div></section><?php return ob_get_clean();
    }
}
ACLM_V63_Litigation_Management::init();
}
