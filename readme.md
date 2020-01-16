# Backup and Staging by WP Time Capsule < 1.21.16 - Authentication Bypass Proof Of Concept

An authentication bypass was recently discovered (https://www.webarxsecurity.com/vulnerability-infinitewp-client-wp-time-capsule/) on WP Time Capsule < 1.21.16 . This PoC prove how the issue works and how it can be exploited.

## The PoC
This POC offer two different options:
- Case 1 : grab the admin cookie
    Example:
    ```
    poc.py http://127.0.0.1/
    ```
- Case 2 : grab the admin cookie + upload a password protected webshell on /wp-content/plugins/shell/shell.php
  Example:
  ```
  poc.py http://127.0.0.1 shell
  ```


## The issue

The function  decode_server_request_wptc() , which is inside the wptc-cron-functions.php and is called by the Wptc_Init constructor does not perform any kind of privilege checking while decoding the requests sent on the body:


```
if( !empty($HTTP_RAW_POST_DATA_LOCAL)
  && strpos($HTTP_RAW_POST_DATA_LOCAL, 'IWP_JSON_PREFIX') !== false ){

  wptc_log('', "--------IWP_JSON_PREFIX--coming------");

  wp_cookie_constants();
  wptc_login_as_admin();
}
```

It simply checks that the body on any POST request contains the string IWP_JSON_PREFIX, if so , it
calls the methods:
```
  wp_cookie_constants();
  wptc_login_as_admin();
```
wptc_login_as_admin() gets all the admin users, extract the first  one and returns a cookie for that
username.
```

function wptc_login_as_admin(){

	wptc_log(func_get_args(), "--------" . __FUNCTION__ . "--------");

	wptc_define_admin_constants();

	if( !function_exists('is_user_logged_in') ){
		include_once( ABSPATH . 'wp-includes/pluggable.php' );
	}

	if(is_user_logged_in()){
		return ;
	}

	$admins = get_users(array('role' => 'administrator'));

	foreach ($admins as $admin) {
		$user = $admin;
		break;
	}

	if (isset($user) && isset($user->ID)) {
		wp_set_current_user($user->ID);
		// Compatibility with All In One Security
		update_user_meta($user->ID, 'last_login_time', current_time('mysql'));
	}

	if(!defined('SECURE_AUTH_COOKIE')){

		return;
	}

	$isHTTPS = (bool)is_ssl();

	if($isHTTPS){
		wp_set_auth_cookie($user->ID);
	} else{
		wp_set_auth_cookie($user->ID, false, false);
		wp_set_auth_cookie($user->ID, false, true);
	}
}
```

## Replicating the issue
For this test we used:
  - A docker-compose ready Wordpress Image https://github.com/nezhar/wordpress-docker-compose
  - wp-time-capsule 1.21.15 https://downloads.wordpress.org/plugin/wp-time-capsule.1.21.15.zip
  - Burp Suite

Steps involved:
  1. Access to a new session on the browser
  2. Go to wp-login.php
  3. Write dummy username and password
  4. Replace any part of the BODY with the following string: IWP_JSON_PREFIX
  5. Go back to the home page


![](https://github.com/SECFORCE/WPTimeCapsulePOC/blob/master/POC.gif)
