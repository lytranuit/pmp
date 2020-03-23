<?php

defined('BASEPATH') OR exit('No direct script access allowed');

/*
  | -------------------------------------------------------------------------
  | URI ROUTING
  | -------------------------------------------------------------------------
  | This file lets you re-map URI requests to specific controller functions.
  |
  | Typically there is a one-to-one relationship between a URL string
  | and its corresponding controller class/method. The segments in a
  | URL normally follow this pattern:
  |
  |	example.com/class/method/id/
  |
  | In some instances, however, you may want to remap this relationship
  | so that a different class/function is called than the one
  | corresponding to the URL.
  |
  | Please see the user guide for complete details:
  |
  |	https://codeigniter.com/user_guide/general/routing.html
  |
  | -------------------------------------------------------------------------
  | RESERVED ROUTES
  | -------------------------------------------------------------------------
  |
  | There are three reserved routes:
  |
  |	$route['default_controller'] = 'welcome';
  |
  | This route indicates which controller class should be loaded if the
  | URI contains no data. In the above example, the "welcome" class
  | would be loaded.
  |
  |	$route['404_override'] = 'errors/page_missing';
  |
  | This route will tell the Router which controller/method to use if those
  | provided in the URL cannot be matched to a valid route.
  |
  |	$route['translate_uri_dashes'] = FALSE;
  |
  | This is not exactly a route, but allows you to automatically route
  | controller and method names that contain dashes. '-' isn't a valid
  | class or method name character, so it requires translation.
  | When you set this option to TRUE, it will replace ALL dashes in the
  | controller and method URI segments.
  |
  | Examples:	my-controller/index	-> my_controller/index
  |		my-controller/my-method	-> my_controller/my_method
 */
$route['default_controller'] = 'index';


//$route['^phong-cho-thue.html$'] = 'user/phong';
//$route['^he-thong-nha.html$'] = 'user/nha';
//$route['^tin-tuc.html$'] = 'user/tintuc';
//$route['^ki-gui-nha-dat.html$'] = 'user/kigui';
//$route['^phong-cho-thue/(.*)-p(:num).html$'] = 'user/infophong/$2';
//$route['^he-thong-nha/(.*)-n(:num).html$'] = 'user/infonha/$2';
//$route['^tin-tuc/(.*)-t(:num).html$'] = 'user/infotintuc/$2';

$route['404_override'] = 'index/page_404';
$route['translate_uri_dashes'] = FALSE;

require_once( BASEPATH . '/database/DB.php');
$db = &DB();
$query = $db->get('tbl_page');
$result = $query->result_array();
foreach ($result as $row) {
    $route[$row['seo_url']] = $row['link'] . "/" . $row['param'];
}
//echo "<pre>";
//print_r($route);
//die();