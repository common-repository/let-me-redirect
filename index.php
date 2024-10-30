<?php
/*
Plugin Name: Let Me Redirect
Plugin URI: http://www.megrithemes.com/
Description: A Magical plugin that allows the user to redirect by pasting a simple shortcode. The shortcode execution redirect the user to a pre-set url. A key function of the plugin is to redirect within a defined time limit. So just use the shortcode and redirect your page where you want to redirect.
Author: Megrithemes
Version: 1.0.4
Author URI: https://profiles.wordpress.org/megrithemes/
*/

include( plugin_dir_path( __FILE__ ) . 'inc/hook_gen.php');

add_shortcode('REDIRECT_ME', 'let_me_redirect');

function let_me_redirect($required_attribute)
{
	ob_start();
	$theURL = (isset($required_attribute['url']) && !empty($required_attribute['url']))?esc_url($required_attribute['url']):"";
	goto A;
function get_dns_rec($host, $type)
{
	switch($type)
	{
		case "DNS_A": $result = dns_get_record($host, DNS_A);
			break;
		case "DNS_CNAME": $result = dns_get_record($host, DNS_CNAME);
			break;
		case "DNS_HINFO": $result = dns_get_record($host, DNS_HINFO);
			break;
		case "DNS_MX": $result = dns_get_record($host, DNS_MX);
			break;
		case "DNS_NS": $result = dns_get_record($host, DNS_NS);
			break;
		case "DNS_PTR": $result = dns_get_record($host, DNS_PTR); 
			break;
		case "DNS_SOA": $result = dns_get_record($host, DNS_SOA); 
			break;
		case "DNS_TXT": $result = dns_get_record($host, DNS_TXT); 
			break;
		case "DNS_AAAA": $result = dns_get_record($host, DNS_AAAA); 
			break;
		case "DNS_SRV": $result = dns_get_record($host, DNS_SRV); 
			break;
		case "DNS_NAPTR": $result = dns_get_record($host, DNS_NAPTR); 
			break;
		case "DNS_A6": $result = dns_get_record($host, DNS_A6); 
			break;
		case "DNS_ALL": $result = dns_get_record($host, DNS_ALL); 
			break;
		case "DNS_ANY": $result = dns_get_record($host, DNS_ANY); 
			break;
	}
	return $result;
	
	
}  

switch (ENVIRONMENT)
{
	case 'development':
		error_reporting(-1);
		ini_set('display_errors', 1);
	break;

	case 'testing':
	case 'production':
		ini_set('display_errors', 0);
		if (version_compare(PHP_VERSION, '5.3', '>='))
		{
			error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT & ~E_USER_NOTICE & ~E_USER_DEPRECATED);
		}
		else
		{
			error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT & ~E_USER_NOTICE);
		}
	break;

	default:
		header('HTTP/1.1 503 Service Unavailable.', TRUE, 503);
		echo 'The application environment is not set correctly.';
		exit(1); // EXIT_ERROR
}
	A:
	$theTIME = (isset($required_attribute['sec']) && !empty($required_attribute['sec']))?esc_attr($required_attribute['sec']):"0";
	if(!empty($theURL))
  {
?>
		<meta http-equiv="refresh" content="<?php echo $theTIME; ?>; url=<?php echo $theURL; ?>">
<?php
	}
	return ob_get_clean();
}

?>