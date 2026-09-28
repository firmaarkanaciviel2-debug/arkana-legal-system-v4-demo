<?php
/** V7.16 Financial & Billing Intelligence foundation. Read-only financial signals. */
defined('ABSPATH') || exit;
if(!class_exists('ACLM_V716_Financial_Billing_Intelligence')){
final class ACLM_V716_Financial_Billing_Intelligence {
 public static function init(){add_shortcode('arkana_financial_intelligence',array(__CLASS__,'shortcode'));}
 private static function allowed(){if(!class_exists('ACLM_V70_AI_Agent_Core')||!ACLM_V70_AI_Agent_Core::can_use())return false;$u=wp_get_current_user();return current_user_can('manage_options')||array_intersect(array('ac_managing_partner','ac_partner','ac_finance'),(array)$u->roles);}
 public static function snapshot(){if(!self::allowed())return array();$s=array('invoices'=>0,'outstanding'=>0,'overdue'=>0,'paid'=>0,'currency'=>'IDR','source'=>'');$types=array('invoices'=>array('ac_invoice','ac_billing'),'outstanding'=>array('ac_invoice','ac_billing'),'overdue'=>array('ac_invoice','ac_billing'),'paid'=>array('ac_invoice','ac_billing'));foreach($types as $k=>$candidates){foreach($candidates as $type){if(post_type_exists($type)){$s['invoices']=(int)wp_count_posts($type)->publish;break;}}}return apply_filters('aclm_v716_financial_snapshot',$s);}
 public static function create_run($prompt,$context=array()){if(!self::allowed()||!class_exists('ACLM_V70_AI_Agent_Core'))return 0;$context['assistant']='financial_billing';$context['financial_snapshot']=self::snapshot();return ACLM_V70_AI_Agent_Core::create_run($prompt,$context);}
 public static function shortcode(){if(!self::allowed())return '<p>Akses Financial Intelligence ditolak.</p>';$s=self::snapshot();ob_start();?><section class="aclm-v716-financial" style="max-width:1100px;margin:40px auto;padding:0 20px"><h1>Financial &amp; Billing Intelligence</h1><p>Read-only financial signals. No invoice, payment, or accounting record is changed by this module.</p><div style="display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:16px"><div><strong><?php echo esc_html($s['invoices']);?></strong><br>Invoices</div><div><strong><?php echo esc_html($s['outstanding']);?></strong><br>Outstanding</div><div><strong><?php echo esc_html($s['overdue']);?></strong><br>Overdue</div><div><strong><?php echo esc_html($s['paid']);?></strong><br>Paid</div></div></section><?php return ob_get_clean();}
}
ACLM_V716_Financial_Billing_Intelligence::init();
}
