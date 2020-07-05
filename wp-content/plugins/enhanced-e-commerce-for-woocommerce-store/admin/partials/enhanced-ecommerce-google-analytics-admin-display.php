<?php
/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       tatvic.com
 * @since      1.0.0
 *
 * @package    Enhanced_Ecommerce_Google_Analytics
 * @subpackage Enhanced_Ecommerce_Google_Analytics/admin/partials
 *
 */
if (!defined('ABSPATH')) {
    exit;
}

$site_url = "admin.php?page=enhanced-ecommerce-google-analytics-admin-display&tab=";

if(isset($_GET['tab']) && $_GET['tab'] == 'general_settings'){
    $general_class_active = "active";
}
else{
    $general_class_active = "";
}
if(isset($_GET['tab']) && $_GET['tab'] == 'about_plugin'){
    $advanced_class_active = "active";
}
else{
    $advanced_class_active = "";
}
if(empty($_GET['tab'])){
    $general_class_active = "active";
}


?>
<header class='background-color:#E8E8E8;height:500px;width:auto;margin-top:100px;margin-left:20px;'>
    <img class ="banner" src='<?php echo plugins_url('../images/banner.png', __FILE__ )  ?>' style="margin-left:10px;">
</header>
<ul class="nav nav-tabs nav-pills" style="margin-left: 10px;margin-top:20px;">
    <li class="nav-item">
        <a  href="<?php echo $site_url.'general_settings'; ?>"  class="border-left aga-tab nav-link <?php echo $general_class_active; ?>">General Settings</a>
    </li>
    <li class="nav-item"><a href="<?php echo $site_url.'about_plugin'; ?>" class="border-left aga-tab nav-link <?php echo $advanced_class_active; ?>">Premium <img class="new-img-blink" src='<?php echo plugins_url('../images/new-2.gif', __FILE__ )  ?>' /></a></li>
</ul>
