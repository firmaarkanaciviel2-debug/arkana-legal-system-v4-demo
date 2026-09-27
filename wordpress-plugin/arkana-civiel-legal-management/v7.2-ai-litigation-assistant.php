<?php
/** V7.2 AI Litigation Assistant foundation. */
defined('ABSPATH') || exit;
if(!class_exists('ACLM_V72_AI_Litigation_Assistant')){
final class ACLM_V72_AI_Litigation_Assistant {
 public static function init(){add_shortcode('arkana_ai_litigation_assistant',array(__CLASS__,'shortcode'));}
 private static function allowed($matter_id){
  return class_exists('ACLM_V70_AI_Agent_Core') && ACLM_V70_AI_Agent_Core::can_use() && ACLM_V70_AI_Agent_Core::can_access_matter($matter_id);
 }
 public static function get_context($matter_id){
  $matter_id=absint($matter_id); if(!$matter_id || 'ac_matter'!==get_post_type($matter_id) || !self::allowed($matter_id)) return array();
  $events=self::related('ac_litigation_event',$matter_id,'_aclm_matter_id');
  $deadlines=self::related('ac_deadline',$matter_id,'_aclm_matter_id');
  $tasks=self::related('ac_task',$matter_id,'_aclm_matter_id');
  return apply_filters('aclm_v72_litigation_context',array('matter_id'=>$matter_id,'title'=>get_the_title($matter_id),'status'=>sanitize_text_field(get_post_meta($matter_id,'_aclm_status',true)),'litigation_events'=>$events,'deadlines'=>$deadlines,'tasks'=>$tasks),$matter_id);
 }
 private static function related($type,$matter_id,$key){
  if(!post_type_exists($type)) return array();
  $posts=get_posts(array('post_type'=>$type,'post_status'=>'publish','numberposts'=>50,'meta_key'=>$key,'meta_value'=>(string)$matter_id,'orderby'=>'date','order'=>'ASC'));
  return array_map(function($p){return array('id'=>(int)$p->ID,'title'=>get_the_title($p->ID),'date'=>get_the_date('c',$p),'status'=>sanitize_text_field(get_post_meta($p->ID,'_aclm_status',true)));},$posts);
 }
 public static function create_run($matter_id,$prompt){
  if(!self::allowed($matter_id) || !self::get_context($matter_id)) return 0;
  return ACLM_V70_AI_Agent_Core::create_run($prompt,array('matter_id'=>absint($matter_id),'assistant'=>'litigation','context'=>self::get_context($matter_id)));
 }
 public static function shortcode($atts){
  $atts=shortcode_atts(array('matter_id'=>0),$atts,'arkana_ai_litigation_assistant'); $id=absint($atts['matter_id']);
  if(!$id || !self::allowed($id)) return '<p>Akses AI Litigation Assistant ditolak.</p>';
  $c=self::get_context($id); ob_start(); ?><section class="aclm-v72-litigation-assistant" style="max-width:1100px;margin:40px auto;padding:0 20px"><h1>AI Litigation Assistant — <?php echo esc_html($c['title']); ?></h1><p>Read-only litigation context is available to the authorized Agent. V7.2 does not make legal decisions or change case records.</p><ul><li>Litigation events: <?php echo esc_html(count($c['litigation_events'])); ?></li><li>Deadlines: <?php echo esc_html(count($c['deadlines'])); ?></li><li>Tasks: <?php echo esc_html(count($c['tasks'])); ?></li></ul></section><?php return ob_get_clean();
 }
}
ACLM_V72_AI_Litigation_Assistant::init();
}
