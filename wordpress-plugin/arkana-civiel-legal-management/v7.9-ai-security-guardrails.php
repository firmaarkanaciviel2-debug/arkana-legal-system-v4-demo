<?php
/** V7.9 AI Security & Guardrails foundation. */
defined('ABSPATH') || exit;
if(!class_exists('ACLM_V79_AI_Security_Guardrails')){
final class ACLM_V79_AI_Security_Guardrails {
 public static function init(){add_shortcode('arkana_ai_security_status',array(__CLASS__,'shortcode'));}
 public static function policy(){return array('read_only_default'=>true,'external_provider_disabled_by_default'=>true,'client_isolation'=>true,'matter_object_authorization'=>true,'human_approval_required_for_writes'=>true,'audit_required'=>true,'prompt_injection_review'=>true,'rate_limit_required'=>true,'legal_advice_disclaimer'=>true,'source_attribution_required'=>true);}
 public static function authorize($scope,$object_id=0){
  if(!class_exists('ACLM_V70_AI_Agent_Core')||!ACLM_V70_AI_Agent_Core::can_use())return false;
  $object_id=absint($object_id); if($scope==='matter'&&$object_id&&!ACLM_V70_AI_Agent_Core::can_access_matter($object_id))return false;
  return apply_filters('aclm_v79_authorize',true,$scope,$object_id);
 }
 public static function sanitize_prompt($prompt){$prompt=wp_strip_all_tags((string)$prompt);return mb_substr($prompt,0,8000);}
 public static function audit_event($event,$context=array()){if(class_exists('ACLM_V69_Advanced_Security'))return ACLM_V69_Advanced_Security::audit($event,'ai_guardrail',0,$context);return false;}
 public static function shortcode(){if(!current_user_can('manage_options')&&!self::authorized_management())return '<p>Akses AI Security Status ditolak.</p>';$p=self::policy();ob_start();?><section class="aclm-v79-security" style="max-width:1100px;margin:40px auto;padding:0 20px"><h1>AI Security &amp; Guardrails</h1><p>V7.9 defines the default security policy. Production enforcement remains subject to final integration testing.</p><ul><?php foreach($p as $k=>$v):?><li><strong><?php echo esc_html($k);?></strong>: <?php echo $v?'enabled':'disabled';?></li><?php endforeach;?></ul></section><?php return ob_get_clean();}
 private static function authorized_management(){ $u=wp_get_current_user();return (bool)array_intersect(array('ac_managing_partner','ac_partner'),(array)$u->roles); }
}
ACLM_V79_AI_Security_Guardrails::init();
}
