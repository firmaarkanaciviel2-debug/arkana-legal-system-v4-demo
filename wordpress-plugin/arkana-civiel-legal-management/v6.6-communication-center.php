<?php
/** V6.6 Communication Center foundation. */
defined( 'ABSPATH' ) || exit;
if ( ! class_exists( 'ACLM_V66_Communication_Center' ) ) {
final class ACLM_V66_Communication_Center {
    const CPT = 'ac_communication';
    public static function init() { add_action('init',array(__CLASS__,'register')); add_shortcode('arkana_communication_360',array(__CLASS__,'shortcode')); }
    public static function register() {
        register_post_type(self::CPT,array('labels'=>array('name'=>'Matter Communications','singular_name'=>'Matter Communication'),'public'=>false,'show_ui'=>true,'show_in_menu'=>true,'supports'=>array('title','editor','author'),'capability_type'=>'post','map_meta_cap'=>true));
    }
    public static function send($matter_id,$body,$visibility='client',$recipient_user_id=0) {
        $matter_id=absint($matter_id); $body=wp_kses_post($body); $visibility=sanitize_key($visibility); $recipient_user_id=absint($recipient_user_id);
        if(!$matter_id || 'ac_matter'!==get_post_type($matter_id) || !$body || !in_array($visibility,array('internal','client'),true)) return 0;
        if(class_exists('ACLM_V512_Audit_Security') && !ACLM_V512_Audit_Security::can_access_matter($matter_id)) return 0;
        $id=wp_insert_post(array('post_type'=>self::CPT,'post_status'=>'publish','post_title'=>'Communication — '.get_the_title($matter_id),'post_content'=>$body,'post_author'=>get_current_user_id()),true);
        if(is_wp_error($id)) return 0;
        update_post_meta($id,'_aclm_matter_id',$matter_id); update_post_meta($id,'_aclm_visibility',$visibility); if($recipient_user_id) update_post_meta($id,'_aclm_recipient_user_id',$recipient_user_id);
        if(class_exists('ACLM_V512_Audit_Security')) ACLM_V512_Audit_Security::log('create','communication',$id,array('matter_id'=>$matter_id,'visibility'=>$visibility,'recipient_user_id'=>$recipient_user_id));
        return (int)$id;
    }
    public static function messages($matter_id) {
        $matter_id=absint($matter_id); if(!$matter_id) return array();
        if(class_exists('ACLM_V512_Audit_Security') && !ACLM_V512_Audit_Security::can_access_matter($matter_id)) return array();
        $all=get_posts(array('post_type'=>self::CPT,'post_status'=>'publish','numberposts'=>100,'meta_key'=>'_aclm_matter_id','meta_value'=>(string)$matter_id,'orderby'=>'date','order'=>'ASC'));
        $u=wp_get_current_user(); $is_privileged=current_user_can('manage_options') || in_array('ac_managing_partner',(array)$u->roles,true) || in_array('ac_partner',(array)$u->roles,true);
        return array_values(array_filter($all,function($m)use($u,$is_privileged){$v=get_post_meta($m->ID,'_aclm_visibility',true); $r=(int)get_post_meta($m->ID,'_aclm_recipient_user_id',true); return $is_privileged || 'client'!==$v || !$r || $r===$u->ID;}));
    }
    public static function shortcode($atts){
        if(!is_user_logged_in()) return '<p>Silakan login untuk mengakses Communication Center.</p>';
        $atts=shortcode_atts(array('matter_id'=>0),$atts,'arkana_communication_360'); $matter_id=absint($atts['matter_id']);
        if(!$matter_id) return '<p>Matter belum ditentukan.</p>';
        if(class_exists('ACLM_V512_Audit_Security') && !ACLM_V512_Audit_Security::can_access_matter($matter_id)) return '<p>Akses komunikasi ditolak.</p>';
        $messages=self::messages($matter_id); ob_start(); ?><section class="aclm-v66-communication" style="max-width:1100px;margin:40px auto;padding:0 20px;"><h1>Communication Center — <?php echo esc_html(get_the_title($matter_id)); ?></h1><div style="display:grid;gap:12px;"><?php if(!$messages): ?><p>Belum ada komunikasi.</p><?php endif; ?><?php foreach($messages as $m): ?><article style="border:1px solid #dfe5ea;border-radius:10px;padding:16px;background:#fff;"><strong><?php echo esc_html(get_the_author_meta('display_name',$m->post_author)); ?></strong><small style="margin-left:10px;"><?php echo esc_html(get_the_date('', $m)); ?></small><div><?php echo wp_kses_post(wpautop($m->post_content)); ?></div></article><?php endforeach; ?></div></section><?php return ob_get_clean();
    }
}
ACLM_V66_Communication_Center::init();
}
