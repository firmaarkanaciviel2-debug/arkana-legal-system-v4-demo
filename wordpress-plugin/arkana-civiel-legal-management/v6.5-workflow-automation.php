<?php
/** V6.5 Legal Workflow Automation foundation. */
defined( 'ABSPATH' ) || exit;
if ( ! class_exists( 'ACLM_V65_Workflow_Automation' ) ) {
final class ACLM_V65_Workflow_Automation {
    const CPT = 'ac_workflow_instance';
    public static function init() { add_action('init',array(__CLASS__,'register')); add_shortcode('arkana_workflow_360',array(__CLASS__,'shortcode')); }
    public static function register() {
        register_post_type(self::CPT,array('labels'=>array('name'=>'Workflow Instances','singular_name'=>'Workflow Instance'),'public'=>false,'show_ui'=>true,'show_in_menu'=>true,'supports'=>array('title'),'capability_type'=>'post','map_meta_cap'=>true));
    }
    public static function states(){ return array('intake','triage','assigned','in_progress','review','partner_approval','client_update','closed'); }
    public static function transitions(){ return array(
        'intake'=>array('triage'),'triage'=>array('assigned'),'assigned'=>array('in_progress'),'in_progress'=>array('review'),'review'=>array('partner_approval'),'partner_approval'=>array('client_update'),'client_update'=>array('closed'),'closed'=>array()
    ); }
    public static function create($matter_id,$request_id=0,$state='intake'){
        $matter_id=absint($matter_id); $request_id=absint($request_id); $state=sanitize_key($state);
        if(!$matter_id || 'ac_matter'!==get_post_type($matter_id) || !in_array($state,self::states(),true)) return 0;
        if(class_exists('ACLM_V512_Audit_Security') && !ACLM_V512_Audit_Security::can_access_matter($matter_id)) return 0;
        $id=wp_insert_post(array('post_type'=>self::CPT,'post_status'=>'publish','post_title'=>'Workflow — '.get_the_title($matter_id)),true);
        if(is_wp_error($id)) return 0;
        update_post_meta($id,'_aclm_matter_id',$matter_id); update_post_meta($id,'_aclm_request_id',$request_id); update_post_meta($id,'_aclm_workflow_state',$state);
        self::audit('create',$id,array('matter_id'=>$matter_id,'state'=>$state)); return (int)$id;
    }
    public static function transition($workflow_id,$next_state){
        $workflow_id=absint($workflow_id); $next_state=sanitize_key($next_state); $current=sanitize_key(get_post_meta($workflow_id,'_aclm_workflow_state',true)); $matter_id=(int)get_post_meta($workflow_id,'_aclm_matter_id',true);
        if(!$workflow_id || self::CPT!==get_post_type($workflow_id) || !$matter_id || !in_array($next_state,self::states(),true)) return false;
        if(!self::can_manage($matter_id)) return false;
        $allowed=self::transitions(); if(!in_array($next_state,$allowed[$current]??array(),true)) return false;
        update_post_meta($workflow_id,'_aclm_workflow_state',$next_state); update_post_meta($workflow_id,'_aclm_workflow_updated',current_time('mysql'));
        self::audit('transition',$workflow_id,array('from'=>$current,'to'=>$next_state,'matter_id'=>$matter_id)); return true;
    }
    private static function can_manage($matter_id){ if(class_exists('ACLM_V512_Audit_Security')) return ACLM_V512_Audit_Security::can_access_matter($matter_id); return current_user_can('manage_options'); }
    private static function audit($action,$id,$data){ if(class_exists('ACLM_V512_Audit_Security')) ACLM_V512_Audit_Security::log($action,'workflow_instance',$id,$data); }
    public static function shortcode($atts){
        if(!is_user_logged_in()) return '<p>Silakan login untuk mengakses Workflow.</p>';
        $atts=shortcode_atts(array('workflow_id'=>0),$atts,'arkana_workflow_360'); $id=absint($atts['workflow_id']);
        if(!$id || self::CPT!==get_post_type($id)) return '<p>Workflow belum ditentukan.</p>';
        $matter=(int)get_post_meta($id,'_aclm_matter_id',true); if(!self::can_manage($matter)) return '<p>Akses workflow ditolak.</p>';
        $state=sanitize_key(get_post_meta($id,'_aclm_workflow_state',true)); $states=self::states(); $current_index=array_search($state,$states,true);
        ob_start(); ?><section class="aclm-v65-workflow" style="max-width:1100px;margin:40px auto;padding:0 20px;"><h1>Workflow — <?php echo esc_html(get_the_title($matter)); ?></h1><p><strong>State:</strong> <?php echo esc_html($state); ?></p><ol><?php foreach($states as $i=>$s): ?><li<?php echo $i <= $current_index ? ' style="font-weight:700"' : ''; ?>><?php echo esc_html(ucwords(str_replace('_',' ',$s))); ?></li><?php endforeach; ?></ol></section><?php return ob_get_clean();
    }
}
ACLM_V65_Workflow_Automation::init();
}
