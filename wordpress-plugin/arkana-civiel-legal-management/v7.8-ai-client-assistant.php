<?php
/** V7.8 AI Client Assistant foundation. Client-scoped, read-only, approval-oriented. */
defined('ABSPATH') || exit;
if(!class_exists('ACLM_V78_AI_Client_Assistant')){
final class ACLM_V78_AI_Client_Assistant {
 public static function init(){add_shortcode('arkana_ai_client_assistant',array(__CLASS__,'shortcode'));}
 private static function allowed($client_id){
  if(!class_exists('ACLM_V70_AI_Agent_Core')||!ACLM_V70_AI_Agent_Core::can_use())return false;
  $client_id=absint($client_id); if(!$client_id)return false;
  $u=wp_get_current_user();
  if(current_user_can('manage_options')||array_intersect(array('ac_managing_partner','ac_partner','ac_lawyer'),(array)$u->roles))return true;
  return (int)get_user_meta($u->ID,'_aclm_client_id',true)===$client_id;
 }
 public static function get_context($client_id){
  $client_id=absint($client_id);if(!$client_id||!self::allowed($client_id))return array();
  $context=array('client_id'=>$client_id,'name'=>get_the_title($client_id),'matters'=>array(),'retainers'=>array(),'communications'=>array());
  $context['matters']=self::related('ac_matter','_aclm_client_id',$client_id);
  if(post_type_exists('ac_retainer_service'))$context['retainers']=self::related_any('ac_retainer_service',array('_aclm_client_id','_aclm_client'),$client_id);
  return apply_filters('aclm_v78_client_context',$context,$client_id);
 }
 private static function related($type,$key,$id){if(!post_type_exists($type))return array();$posts=get_posts(array('post_type'=>$type,'post_status'=>'publish','numberposts'=>50,'meta_key'=>$key,'meta_value'=>(string)$id,'orderby'=>'date','order'=>'DESC'));return array_map(function($p){return array('id'=>(int)$p->ID,'title'=>get_the_title($p->ID),'status'=>sanitize_text_field(get_post_meta($p->ID,'_aclm_status',true)),'date'=>get_the_date('c',$p));},$posts);}
 private static function related_any($type,$keys,$id){$out=array();foreach($keys as $k){foreach(self::related($type,$k,$id) as $v)$out[$v['id']]=$v;}return array_values($out);}
 public static function create_run($client_id,$prompt){$c=self::get_context($client_id);if(!$c||!class_exists('ACLM_V70_AI_Agent_Core'))return 0;$c['assistant']='client';return ACLM_V70_AI_Agent_Core::create_run($prompt,array('client_id'=>absint($client_id),'assistant'=>'client','context'=>$c));}
 public static function shortcode($atts){$atts=shortcode_atts(array('client_id'=>0),$atts,'arkana_ai_client_assistant');$id=absint($atts['client_id']);if(!$id||!self::allowed($id))return '<p>Akses AI Client Assistant ditolak.</p>';$c=self::get_context($id);ob_start();?><section class="aclm-v78-client-assistant" style="max-width:1100px;margin:40px auto;padding:0 20px"><h1>Arkana AI Client Assistant</h1><p>Client-scoped, read-only assistant foundation. Legal advice, binding commitments, and record changes are not performed automatically.</p><ul><li>Matters: <?php echo esc_html(count($c['matters']));?></li><li>Retainers: <?php echo esc_html(count($c['retainers']));?></li></ul></section><?php return ob_get_clean();}
}
ACLM_V78_AI_Client_Assistant::init();
}
