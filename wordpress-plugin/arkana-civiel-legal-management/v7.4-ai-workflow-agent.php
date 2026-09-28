<?php
/** V7.4 AI Workflow Agent foundation. Read-only analysis + approval-gated action proposals. */
defined('ABSPATH') || exit;
if(!class_exists('ACLM_V74_AI_Workflow_Agent')){
final class ACLM_V74_AI_Workflow_Agent {
 public static function init(){add_shortcode('arkana_ai_workflow_agent',array(__CLASS__,'shortcode'));}
 private static function allowed(){return class_exists('ACLM_V70_AI_Agent_Core') && ACLM_V70_AI_Agent_Core::can_use();}
 public static function get_context($matter_id=0){
  if(!self::allowed())return array(); $matter_id=absint($matter_id);
  if($matter_id && !ACLM_V70_AI_Agent_Core::can_access_matter($matter_id))return array();
  $context=array('matter_id'=>$matter_id,'tasks'=>array(),'deadlines'=>array(),'workflows'=>array());
  if($matter_id){$context['tasks']=self::related('ac_task',$matter_id);$context['deadlines']=self::related('ac_deadline',$matter_id);$context['workflows']=self::related('ac_workflow_instance',$matter_id);}
  return apply_filters('aclm_v74_workflow_context',$context,$matter_id);
 }
 private static function related($type,$matter_id){if(!post_type_exists($type))return array();$posts=get_posts(array('post_type'=>$type,'post_status'=>'publish','numberposts'=>50,'meta_key'=>'_aclm_matter_id','meta_value'=>(string)$matter_id,'orderby'=>'date','order'=>'DESC'));return array_map(function($p){return array('id'=>(int)$p->ID,'title'=>get_the_title($p->ID),'date'=>get_the_date('c',$p),'status'=>sanitize_text_field(get_post_meta($p->ID,'_aclm_status',true)));},$posts);}
 public static function propose_action($matter_id,$action,$payload=array()){
  if(!self::allowed() || ($matter_id && !ACLM_V70_AI_Agent_Core::can_access_matter($matter_id)))return 0;
  $allowed=array('create_task','flag_deadline','request_review','prepare_communication'); if(!in_array($action,$allowed,true))return 0;
  $id=wp_insert_post(array('post_type'=>'ac_ai_agent_run','post_status'=>'publish','post_title'=>'AI Action Proposal — '.current_time('mysql')),true); if(is_wp_error($id))return 0;
  update_post_meta($id,'_aclm_ai_status','awaiting_approval');update_post_meta($id,'_aclm_ai_action',$action);update_post_meta($id,'_aclm_ai_matter_id',absint($matter_id));update_post_meta($id,'_aclm_ai_payload',wp_json_encode($payload));
  if(class_exists('ACLM_V69_Advanced_Security'))ACLM_V69_Advanced_Security::audit('ai_action_proposed','ac_ai_agent_run',$id,array('action'=>$action,'matter_id'=>absint($matter_id)));
  return (int)$id;
 }
 public static function shortcode($atts){$atts=shortcode_atts(array('matter_id'=>0),$atts,'arkana_ai_workflow_agent');if(!self::allowed())return '<p>Akses AI Workflow Agent ditolak.</p>';$c=self::get_context(absint($atts['matter_id']));ob_start();?><section class="aclm-v74-workflow-agent" style="max-width:1100px;margin:40px auto;padding:0 20px"><h1>AI Workflow Agent</h1><p>Workflow context is read-only. Any action is represented as a proposal and requires explicit human approval.</p><ul><li>Tasks: <?php echo esc_html(count($c['tasks']));?></li><li>Deadlines: <?php echo esc_html(count($c['deadlines']));?></li><li>Workflows: <?php echo esc_html(count($c['workflows']));?></li></ul></section><?php return ob_get_clean();}
}
ACLM_V74_AI_Workflow_Agent::init();
}
