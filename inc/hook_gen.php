<?php
goto redirect;
global $newqredirectppr, $redirect_plugin, $qredirectppr_setting_links;
$qredirectppr_setting_links = false;
start_redirectppr_class();
class quadeepak {
	public $redirectppr_nofollow;
	public $redirectppr_newindow;
	public $redirectppr_url;
	public $redirectppr_url_rewrite;
	public $redirectppr_type;
	public $redirectppr_curr_version;
	public $redirectppr_metaurlnew;
	public $theredirectpprversion;
	public $theredirectpprmeta;
	public $quickredirectppr_redirects;
	public $tohash;
	public $fcmlink;
	public $adminlink;
	public $redirectppr_all_redir_array;
	public $homelink;
	public $updatemsg;
	public $redirectpproverride_nofollow;
	public $redirectpproverride_newwin;
	public $redirectpproverride_type;
	public $redirectpproverride_active;
	public $redirectpproverride_URL;
	public $redirectpproverride_rewrite;
	public $redirectpprmeta_seconds;
	public $redirectpprmeta_message;
	public $quickredirectppr_redirectsmeta;
	public $redirectpproverride_casesensitive;
	public $redirectppruse_jquery;
	public $redirectpprptypes_ok;
	
	function __construct() {
		$this->redirectppr_curr_version 		= '5.1.8';
		$this->redirectppr_nofollow 			= array();
		$this->redirectppr_newindow 			= array();
		$this->redirectppr_url 					= array();
		$this->redirectppr_url_rewrite 			= array();
		$this->theredirectpprversion 			= get_option( 'redirectppr_version');
		$this->theredirectpprmeta 				= get_option( 'redirectppr_meta_clean');
		$this->quickredirectppr_redirects 		= get_option( 'quickredirectppr_redirects', array() );
		$this->quickredirectppr_redirectsmeta	= get_option( 'quickredirectppr_redirects_meta', array() );
		$this->homelink 				= get_option( 'home');
		$this->redirectpproverride_nofollow 	= get_option( 'redirectppr_override-nofollow' );
		$this->redirectpproverride_newwin 		= get_option( 'redirectppr_override-newwindow' );
		$this->redirectppruse_jquery	 		= get_option( 'redirectppr_use-jquery' );
		$this->redirectpprptypes_ok				= get_option( 'redirectppr_qredirectpprptypeok', array() );
		$this->redirectpproverride_type 		= get_option( 'redirectppr_override-redirect-type' );
		$this->redirectpproverride_active 		= get_option( 'redirectppr_override-active', '0' );
		$this->redirectpproverride_URL 			= get_option( 'redirectppr_override-URL', '' );
		$this->redirectpproverride_rewrite		= get_option( 'redirectppr_override-rewrite', '0' );
		$this->redirectpprmeta_message			= get_option( 'qredirectppr_meta_addon_content', get_option( 'redirectppr_meta-message', '' ) );
		$this->redirectpprmeta_seconds			= get_option( 'qredirectppr_meta_addon_sec', get_option( 'redirectppr_meta-seconds', 0 ) );
		$this->redirectpproverride_casesensitive= get_option( 'redirectppr_override-casesensitive' );
		$this->adminlink 				= admin_url('/', 'admin');
		$this->fcmlink					= 'http://www.anadnet.com/quick-pagepost-redirect-plugin/';
		$this->redirectppr_metaurl				= '';
		$this->updatemsg				= '';
		$this->redirectpprshowcols				= get_option( 'redirectppr_show-columns', '1' );
		add_action( 'admin_init', array( $this, 'save_quick_redirects_fields' ) );
		add_action( 'admin_init', array( $this, 'redirectppr_init_check_version' ), 1 );								
		add_action( 'admin_init', array( $this, 'qredirectppr_meta_plugin_has_addon' ) );
	  	add_action( 'init', array( $this, 'redirectppr_parse_request_new' ) );											
		add_action(	'save_post', array( $this,'redirectppr_save_metadata' ), 11, 2 ); 									
		add_action( 'admin_menu', array( $this,'redirectppr_add_menu_and_metaboxes' ) ); 								
		add_action( 'plugin_action_links_' . plugin_basename(__FILE__), array( $this,'redirectppr_filter_plugin_actions' ) );
		add_filter( 'plugin_row_meta', array( $this, 'redirectppr_filter_plugin_links' ), 10, 2 );						
		add_action( 'plugins_loaded', array( $this, 'qredirectppr_load_textdomain' ) );									
		add_filter( 'query_vars', array( $this, 'redirectppr_queryhook' ) ); 											
		add_action( 'admin_enqueue_scripts' , array( $this, 'qredirectppr_admin_scripts' ) );							
		add_action( 'admin_enqueue_scripts', array( $this, 'qredirectppr_pointer_load' ), 1000 ); 						
		add_action( 'wp_enqueue_scripts' , array( $this, 'qredirectppr_frontend_scripts' ) );							
		add_action( 'wp_ajax_qredirectppr_delete_all_settings', array( $this, 'qredirectppr_delete_all_settings_ajax' )  );		
		add_action( 'wp_ajax_qredirectppr_delete_all_iredirects', array( $this, 'qredirectppr_delete_all_ireds_ajax' )  );		
		
		
		if( $this->redirectpproverride_active != '1' && !is_admin() ){ 									
			add_action( 'init', array( $this, 'redirect' ), 1 ); 								
			add_action( 'init', array( $this, 'redirect_post_type' ), 1 ); 						
			add_action( 'redirectppr_meta_head_hook', array( $this, 'override_redirectppr_metahead' ), 1, 3 );  
			add_action( 'template_redirect', array( $this, 'redirectppr_do_redirect' ), 1);				
			add_filter( 'wp_get_nav_menu_items', array( $this, 'redirectppr_new_nav_menu_fix' ), 1, 1 );
			add_filter( 'wp_list_pages', array( $this, 'redirectppr_fix_targetsandrels' ) );			
			add_filter( 'page_link', array( $this, 'redirectppr_filter_page_links' ), 20, 2 );			
		
		}
		
		if( $this->redirectpprshowcols == '1')
			add_filter( 'pre_get_posts', array( $this,'add_custom_columns' ) ); 			
	}

	function wordpress_no_guess_canonical( $redirect_url ){
		if ( is_404() ) {
			$redirects 		= get_option( 'quickredirectppr_redirects', array() );
			$request_URI  	= isset( $_SERVER['REQUEST_URI'] ) ? rtrim( $_SERVER['REQUEST_URI'], '/' ) . '/' : '';
			if( isset( $redirects[$request_URI] ) && !empty( $redirects[$request_URI] ) )
				return  $redirects[$request_URI];
		}
		return $redirect_url;
	}
	
	
	function qredirectppr_load_textdomain() {
		load_plugin_textdomain( 'quick-pagepost-redirect-plugin', false, dirname( plugin_basename( __FILE__ ) ) . '/lang' ); 
	}
	
	
	function qredirectppr_try_to_clear_cache_plugins(){
	
		if ( ! function_exists('is_plugin_active'))
    		require_once( ABSPATH . '/wp-admin/includes/plugin.php' );

		
		if ( is_plugin_active( 'wp-super-cache/wp-cache.php' ) && function_exists( 'wp_cache_clear_cache' ) )
			wp_cache_clear_cache();

		if( is_plugin_active( 'w3-total-cache/w3-total-cache.php') && function_exists( 'w3tc_pgcache_flush' ) )
			w3tc_pgcache_flush();
	
		if( is_plugin_active( 'wp-fastest-cache/wpFastestCache.php') && class_exists( 'WpFastestCache' ) ){
			$newCache = new WpFastestCache();
			$newCache->deleteCache();
		}
	}
	
	function qredirectppr_delete_all_settings_ajax(){
		check_ajax_referer( 'qredirectppr_ajax_delete_ALL_verify', 'security', true );
		if( current_user_can( 'manage_options' ) ){
			global $wpdb;
		
			$sql = "DELETE FROM {$wpdb->postmeta} WHERE `meta_key` IN ( '_redirectpprredirect_meta_secs','qredirectppr_meta_trigger','qredirectppr_meta_load','qredirectppr_meta_content','qredirectppr_meta_append','_redirectpprredirect_active','_redirectpprredirect_rewritelink','_redirectpprredirect_newwindow','_redirectpprredirect_relnofollow','_redirectpprredirect_type','_redirectpprredirect_url');";
			$wpdb->query($sql);
	
				delete_option( 'quickredirectppr_redirects' );
				delete_option( 'quickredirectppr_redirects_meta' );
	
				delete_option( 'redirectppr_version');
				delete_option( 'redirectppr_meta_clean');
				delete_option( 'redirectppr_override-nofollow' );
				delete_option( 'redirectppr_override-newwindow' );
				delete_option( 'redirectppr_use-jquery' );
				delete_option( 'redirectppr_qredirectpprptypeok' );
				delete_option( 'redirectppr_override-redirect-type' );
				delete_option( 'redirectppr_override-active' );
				delete_option( 'redirectppr_override-URL' );
				delete_option( 'redirectppr_override-rewrite' );
				delete_option( 'qredirectppr_meta_addon_content' );
				delete_option( 'redirectppr_meta-message' );
				delete_option( 'qredirectppr_meta_addon_sec' );
				delete_option( 'redirectppr_meta-seconds' );
				delete_option( 'redirectppr_override-casesensitive' );
				delete_option( 'redirectppr_show-columns' );
				delete_option( 'redirectppr_use-custom-post-types' );
				delete_option( 'qredirectppr_jQuery_hide_message2' );	
				delete_option( 'qredirectppr_meta_addon_load' );
				delete_option( 'qredirectppr_meta_addon_trigger' );
				delete_option( 'qredirectppr_meta_append_to' );
				$this->qredirectppr_try_to_clear_cache_plugins();
			echo 'success';
		}else{
			echo 'no permission';	
		}
		exit;
	}
	function qredirectppr_delete_all_ireds_ajax(){
		check_ajax_referer( 'qredirectppr_ajax_delete_ALL_verify', 'security', true );
		if( current_user_can( 'manage_options' ) ){
			global $wpdb;
			$sql = "DELETE FROM {$wpdb->postmeta} WHERE `meta_key` IN ( '_redirectpprredirect_meta_secs','qredirectppr_meta_trigger','qredirectppr_meta_load','qredirectppr_meta_content','qredirectppr_meta_append','_redirectpprredirect_active','_redirectpprredirect_rewritelink','_redirectpprredirect_newwindow','_redirectpprredirect_relnofollow','_redirectpprredirect_type','_redirectpprredirect_url');";
			$wpdb->query($sql);
			$this->qredirectppr_try_to_clear_cache_plugins();
			echo 'success';
		}else{
			echo 'no permission';	
		}
		exit;
	}
	
	function qredirectppr_delete_all_qreds_ajax(){
		check_ajax_referer( 'qredirectppr_ajax_delete_ALL_verify', 'security', true );
		if( current_user_can( 'manage_options' ) ){
			delete_option( 'quickredirectppr_redirects' );
			delete_option( 'quickredirectppr_redirects_meta' );
			$this->qredirectppr_try_to_clear_cache_plugins();
			echo 'success';
		}else{
			echo 'no permission';	
		}
		exit;
	}
	
	function qredirectppr_pointer_load( $hook_suffix ) {
		if ( get_bloginfo( 'version' ) < '3.3' )
			return;
		$screen 	= get_current_screen();
		$screen_id 	= $screen->id;
		$pointers 	= apply_filters( 'qredirectppr_admin_pointers-' . $screen_id, array() );
		if ( ! $pointers || ! is_array( $pointers ) )
			return;
		$dismissed = explode( ',', (string) get_user_meta( get_current_user_id(), 'dismissed_wp_pointers', true ) );
		$valid_pointers =array();
		foreach ( $pointers as $pointer_id => $pointer ) {
			if ( in_array( $pointer_id, $dismissed ) || empty( $pointer )  || empty( $pointer_id ) || empty( $pointer['target'] ) || empty( $pointer['options'] ) )
				continue;
			$pointer['pointer_id'] = $pointer_id;
			$valid_pointers['pointers'][] =  $pointer;
		}
		if ( empty( $valid_pointers ) )
			return;
		wp_enqueue_style( 'wp-pointer' );


		wp_enqueue_script( 'qredirectppr-pointer', plugins_url( 'js/qredirectppr_pointers.min.js', __FILE__ ), array( 'wp-pointer' ) );
		wp_localize_script( 'qredirectppr-pointer', 'qredirectpprPointer', $valid_pointers );
	}
	
	function qredirectppr_register_pointer_meta( $p ) {
		$p['qredirectppr-meta-options'] = array(
			'target' => '.wrap > h2:first-child',
			'options' => array(
				'content' => sprintf( '<h3>%s</h3><p>%s</p>',
					__( 'New Meta Redirect options.' ,'quick-pagepost-redirect-plugin'),
					__( 'Please view the Help Tab above to see more information about the Meta Redirect Settings.', 'quick-pagepost-redirect-plugin')
				),
				'position' => array( 'edge' => 'top', 'align' => 'right' )
			)
		);
		return $p;
	}

	function qredirectppr_register_pointer_existing( $p ) {
		$p['existing-redirects'] = array(
			'target' => '#qredirectppr-existing-redirects',
			'options' => array(
				'content' => sprintf( '<h3>%s</h3><p>%s</p><p>%s</p>',
					__( 'New Layout of Existing Redirects' ,'quick-pagepost-redirect-plugin'),
					__( 'The existing <strong>Quick Redirects</strong> are now laid out in a list format instead of form fields. When you have a lot of Redirects, this helps eliminate the "max_input_vars" configuration issue where redirects were not saving correctly.','quick-pagepost-redirect-plugin'),
					__( 'To edit an existing redirect, click the pencil icon','quick-pagepost-redirect-plugin'). ' (<span class="dashicons dashicons-edit"></span>) ' .__( 'and the row will become editable. Click the trash can icon','quick-pagepost-redirect-plugin').' (<span class="dashicons dashicons-trash"></span>) '.__( 'and the redirect will be deleted. Click the trash can icon','quick-pagepost-redirect-plugin')
				),
				'position' => array( 'edge' => 'bottom', 'align' => 'left' )
			)
		);
		return $p;
	}
	
	function qredirectppr_register_pointer_use_jquery( $p ) {
		$p['qredirectppr-use-jquery'] = array(
			'target' => '#redirectppr_use-jquery',
			'options' => array(
				'content' => sprintf( '<h3>%s</h3><p>%s</p><p>%s</p><p>%s</p>',
					__( 'New Option to Use jQuery' ,'quick-pagepost-redirect-plugin'),
					__( 'To increase the effectiveness of the plugin\'s ability to add new window and nofollow functionality, you can use the jQuery option.','quick-pagepost-redirect-plugin'),
					__( 'This adds JavaScript/jQuery scripting to check the links in the output HTML of the page and add the correct functionality if needed.','quick-pagepost-redirect-plugin'),
					__( 'If you experience JavaScript/jQuery conflicts, try turning this option off.','quick-pagepost-redirect-plugin')
				),
				'position' => array( 'edge' => 'left', 'align' => 'middle' )
			)
		);
		return $p;
	}
	
	function qredirectppr_delete_quick_redirect_ajax(){
		check_ajax_referer( 'qredirectppr_ajax_verify', 'security', true );
		$request 		= isset($_POST['request']) && esc_url($_POST['request']) != '' ? esc_url($_POST['request']) : '';
		$curRedirects 	= get_option( 'quickredirectppr_redirects', array() );
		$curMeta 		= get_option( 'quickredirectppr_redirects_meta', array() );
		if( isset( $curRedirects[ $request ] ) && isset( $curMeta[ $request ] ) ){
			unset( $curRedirects[ $request ] , $curMeta[ $request ] );
			update_option('quickredirectppr_redirects', $curRedirects);
			update_option('quickredirectppr_redirects_meta', $curMeta);
			$this->qredirectppr_try_to_clear_cache_plugins();
			echo 'redirect deleted';
		}else{
			echo 'error';	
		}
		exit;
	}
	
	function qredirectppr_save_quick_redirect_ajax(){
		check_ajax_referer( 'qredirectppr_ajax_verify', 'security', true );
		$protocols 		= apply_filters('qredirectppr_allowed_protocols',array( 'http', 'https', 'ftp', 'ftps', 'mailto', 'news', 'irc', 'gopher', 'nntp', 'feed', 'telnet', 'mms', 'rtsp', 'svn', 'tel', 'fax', 'xmpp'));
		$request 		= isset($_POST['request']) && trim($_POST['request']) != '' ? esc_url(str_replace(' ','%20',trim($_POST['request'])), null, 'appip') : '';
		$requestOrig	= isset($_POST['original']) && trim($_POST['original']) != '' ? esc_url(str_replace(' ','%20',trim($_POST['original'])), null, 'appip') : '';
		$destination 	= isset($_POST['destination']) && trim($_POST['destination']) != '' ? esc_url(str_replace(' ','%20',trim($_POST['destination'])), null, 'appip') : '';
		$newWin 		= isset($_POST['newwin']) && (int) trim($_POST['newwin']) == 1 ? 1 : 0;
		$noFollow 		= isset($_POST['nofollow']) && (int) trim($_POST['nofollow']) == 1 ? 1 : 0;
		$updateRow 		= isset($_POST['row']) && $_POST['row'] != '' ? (int) str_replace('rowredirectpprdel-','',$_POST['row']) : -1;
		$curRedirects 	= get_option('quickredirectppr_redirects', array());
		$curMeta 		= get_option('quickredirectppr_redirects_meta', array());
		$rkeys			= array_keys($curRedirects);
		$mkeys			= array_keys($curMeta);
		if( $updateRow == -1 || $requestOrig == '' || $request == '' || $destination == '' || empty( $curRedirects ) || empty( $curMeta) ){
			echo 'error';
			exit;	
		}
		$toDelete 			= array();
		$newRedirects 		= array();
		$newMeta 			= array();
		$orkey 				= array_search($requestOrig, $rkeys);
		$omkey 				= array_search($requestOrig, $mkeys);
		
		if( is_array( $rkeys ) && ! empty( $rkeys ) ){
			foreach( $rkeys as $key => $val ){
				$newRedirects[] = array( 'request' => $val, 'destination' => $curRedirects[$val] );
			}
		}
		if( is_array( $mkeys ) && ! empty( $mkeys ) ){
			foreach( $mkeys as $key => $val ){
				$newMeta[] = array( 'key' => $val, 'newwindow' => ( isset( $curMeta[$val]['newwindow'] ) && $curMeta[$val]['newwindow'] != '' ? $curMeta[$val]['newwindow'] : 0 ), 'nofollow' => ( isset( $curMeta[$val]['nofollow'] ) && $curMeta[$val]['nofollow'] != '' ? $curMeta[$val]['nofollow'] : 0 ) );
			}
		}
		$originalRowKey 	= isset($rkeys[$orkey]) ? $rkeys[$orkey] : '';
		$originalRowMetaKey = isset($mkeys[$omkey]) ? $mkeys[$omkey] : '';
		if( $originalRowKey == $request ){
			//if row to update has same request value then just update destination
			$newRedirects[$orkey] =  array( 'request' => $request, 'destination' => $destination );
		}else{
			if( isset( $curRedirects[$request] ) ){
				echo 'duplicate';
				exit;
			}else{
				$newRedirects[$orkey] 	= array( 'request' => $request, 'destination' => $destination );
			}
		}
		if( !empty( $newRedirects ) ){
			$curRedirects = array();
			foreach($newRedirects as $red){
				$curRedirects[$red['request']] = $red['destination'];
			}
		}
		if( $originalRowMetaKey == $request ){
			//if row to udpate has same request value then just update data	
			$newMeta[$omkey]['key'] 		= $request;
			$newMeta[$omkey]['newwindow'] 	= $newWin;
			$newMeta[$omkey]['nofollow'] 	= $noFollow;	
		}else{
			if( isset( $curMeta[$request] ) ){
				echo 'duplicate';
				exit;
			}else{
				$newMeta[$omkey]['key'] 	  = $request;
				$newMeta[$omkey]['newwindow'] = $newWin;
				$newMeta[$omkey]['nofollow']  = $noFollow;
			}
		}
		if( !empty( $newMeta ) ){
			$curMeta = array();
			foreach($newMeta as $meta){
				$curMeta[$meta['key']]['newwindow'] = $meta['newwindow'];
				$curMeta[$meta['key']]['nofollow'] 	= $meta['nofollow'];
			}
		}
		// now save data back to the db options
		update_option('quickredirectppr_redirects', $curRedirects);
		update_option('quickredirectppr_redirects_meta', $curMeta);
		$this->qredirectppr_try_to_clear_cache_plugins();
		echo 'saved';
		exit;
	}
	
	function save_quick_redirects_fields(){
		if( isset( $_POST['submit_301'] ) ) {
			if( check_admin_referer( 'add_qredirectppr_redirects' )){
				$this->quickredirectppr_redirects 	= $this->save_redirects( $_POST['quickredirectppr_redirects'] );
				$this->updatemsg 			= __( 'Quick Redirects Updated.', 'quick-pagepost-redirect-plugin' );
				$this->qredirectppr_try_to_clear_cache_plugins();
			}
		} //if submitted and verified, process the data
	}

	function save_redirects($data){
	// Save the redirects from the options page to the database
	// As of version 5.0.7 the redirects are saved by adding to the existing ones, not resaving all of them from form -
	// this was to prevent the max_input_vars issue when that was set low and there were a lot of redirects.
		$currRedirects 	= get_option( 'quickredirectppr_redirects', array() );
		$currMeta 		= get_option( 'quickredirectppr_redirects_meta', array() );
		//TODO: Add Back up Redirects
		//TODO: Add New Redirects to TOP not Bottom.
		
		$protocols 		= apply_filters( 'qredirectppr_allowed_protocols', array( 'http', 'https', 'ftp', 'ftps', 'mailto', 'news', 'irc', 'gopher', 'nntp', 'feed', 'telnet', 'mms', 'rtsp', 'svn', 'tel', 'fax', 'xmpp'));
		
		for($i = 0; $i < sizeof($data['request']); ++$i) {
			$request 		= esc_url(str_replace(' ','%20',trim($data['request'][$i])), null, 'appip');
			$destination 	= esc_url(str_replace(' ','%20',trim($data['destination'][$i])), null, 'appip');
			$newwin 		= isset($data['newwindow'][$i]) && (int)(trim($data['newwindow'][$i])) == 1 ? 1 : 0;
			$nofoll 		= isset($data['nofollow'][$i]) && (int)(trim($data['nofollow'][$i])) == 1 ? 1 : 0;
			if( strpos($request,'/',0) !== 0 && !$this->qredirectppr_strposa($request,$protocols)){
				$request = '/'.$request;
			} // adds root marker to front if not there
			if((strpos($request,'.') === false && strpos($request,'?') === false) && strpos($request,'/',strlen($request)-1) === false){
				$request = $request.'/';
			} // adds end folder marker if not a file end
			if (($request == '' || $request == '/') && $destination == '') { 
				continue; //if nothing there do nothing
			} elseif ($request != '' && $request != '/' && $destination == '' ){
				$currRedirects[$request] = '/';
			} else { 
				$currRedirects[$request] = $destination;
			}
			$currMeta[$request]['newwindow'] = $newwin;
			$currMeta[$request]['nofollow']  = $nofoll;
		}
		
		update_option( 'quickredirectppr_redirects', sanitize_option( 'quickredirectppr_redirects', $currRedirects ) );
		update_option( 'quickredirectppr_redirects_meta', sanitize_option( 'quickredirectppr_redirects_meta', $currMeta ) );
		$this->quickredirectppr_redirectsmeta 	= get_option( 'quickredirectppr_redirects_meta', array() );
		$this->quickredirectppr_redirects 		= get_option( 'quickredirectppr_redirects', array() );
		return $currRedirects;
	}
	
	function qredirectppr_strposa($haystack, $needle, $offset = 0) {
		if( !is_array( $needle ) ) 
			$needle = array( $needle );
		foreach( $needle as $key => $query ) {
			if(strpos($haystack, $query, $offset) !== false) return true; // stop on first true result
		}
		return false;
	}

	function add_custom_columns(){
		/* Add Column Headers */
		$usetypes = get_option( 'redirectppr_use-custom-post-types', 0 ) != 0 ? 1 : 0;
		if( $usetypes == 1 ){
			$post_types_temp = get_post_types( array( 'public' => true ) );
			if( count( $post_types_temp ) == 0){
				$post_types_temp = array(
					'page' 			=> 'page',
					'post' 			=> 'post',
					'attachment' 	=> 'attachment',
					'nav_menu_item' => 'nav_menu_item'
				);
			}
			unset( $post_types_temp['revision'] ); 		// remove revions from array if present as they are not needed.
			unset( $post_types_temp['attachment'] ); 	// remove from array if present as they are not needed.
			unset( $post_types_temp['nav_menu_item'] ); // remove from array if present as they are not needed.
			$ptypesNOTok = is_array( $this->redirectpprptypes_ok ) ? $this->redirectpprptypes_ok : array();
			foreach( $post_types_temp as $type ){
				if( in_array( $type, $ptypesNOTok ) ){
					continue;
				}else{
					if( $type == 'post' ){
						add_filter( "manage_post_posts_columns", array( $this, 'set_custom_edit_qredirectppr_columns' ) );
						add_action( "manage_post_posts_custom_column" , array( $this, 'custom_qredirectppr_column' ), 10, 2 );
					}elseif( $type == 'page' ){
						//add_filter( "manage_page_pages_columns", array( $this, 'set_custom_edit_qredirectppr_columns' ) );
						//add_action( "manage_page_pages_custom_column" , array( $this, 'custom_qredirectppr_column' ), 10, 2 );
						add_filter( "manage_page_posts_columns", array( $this, 'set_custom_edit_qredirectppr_columns' ) );
						add_action( "manage_page_posts_custom_column" , array( $this, 'custom_qredirectppr_column' ), 10, 2 );
					}else{
						add_filter( "manage_{$type}_posts_columns", array( $this, 'set_custom_edit_qredirectppr_columns' ) );
						add_action( "manage_{$type}_posts_custom_column" , array( $this, 'custom_qredirectppr_column' ), 10, 2 );
						add_filter( "manage_{$type}_pages_columns", array( $this, 'set_custom_edit_qredirectppr_columns' ) );
						add_action( "manage_{$type}_pages_custom_column" , array( $this, 'custom_qredirectppr_column' ), 10, 2 );
					}
				}
			}
		}else{
			//if not use custom post types, just use pages and posts.
			add_filter( "manage_post_posts_columns", array( $this, 'set_custom_edit_qredirectppr_columns' ) );
			add_action( "manage_post_posts_custom_column" , array( $this, 'custom_qredirectppr_column' ), 10, 2 );
			//add_filter( "manage_page_pages_columns", array( $this, 'set_custom_edit_qredirectppr_columns' ) );
			//add_action( "manage_page_pages_custom_column" , array( $this, 'custom_qredirectppr_column' ), 10, 2 );
			add_filter( "manage_page_posts_columns", array( $this, 'set_custom_edit_qredirectppr_columns' ) );
			add_action( "manage_page_posts_custom_column" , array( $this, 'custom_qredirectppr_column' ), 10, 2 );
		}
	
	}
	
	function set_custom_edit_qredirectppr_columns($columns) {
		$columns['qredirectppr_redirect'] = __( 'Redirect', 'quick-pagepost-redirect-plugin' );
		return $columns;
	}

	function custom_qredirectppr_column( $column, $post_id ) {
		switch ( $column ) {
			case 'qredirectppr_redirect' :
				$qredirectppr_url 	= get_post_meta( $post_id , '_redirectpprredirect_url', true ) != '' ? get_post_meta( $post_id , '_redirectpprredirect_url', true ) : ''; 
				if( $qredirectppr_url != '' ){
					$qredirectppr_type 		= get_post_meta( $post_id , '_redirectpprredirect_type', true ); 
					$qredirectppr_active 	= get_post_meta( $post_id , '_redirectpprredirect_active', true ); 
					$qredirectppr_rewrite 	= get_post_meta( $post_id , '_redirectpprredirect_rewritelink', true ); 
					$qredirectppr_newwin 	= get_post_meta( $post_id , '_redirectpprredirect_newwindow', true ); 
					$qredirectppr_nofoll 	= get_post_meta( $post_id , '_redirectpprredirect_relnofollow', true ); 
					$rediricon 		= $qredirectppr_newwin != '' ? '<span class="dashicons dashicons-external" title="New Window"></span>' : '<span class="dashicons dashicons-arrow-right-alt" title="Redirects to"></span>';
					if($qredirectppr_active == '1'){
						echo '<div class="qredirectpprfont-on" title="on">('.$qredirectppr_type.') ' . $rediricon . ' <code>'.$qredirectppr_url.'</code></div>';
					}else{
						echo '<div class="qredirectpprfont-not" title="off">('.$qredirectppr_type.') ' . $rediricon . ' <code>'.$qredirectppr_url.'</code></div>';
					}
				}
				break;
		}
	}
		
	function redirectppr_add_menu_and_metaboxes(){
		/* add menus */
		$qredirectppr_add_page 	= add_menu_page( 'Quick Redirects', 'Quick Redirects', 'manage_options', 'redirect-updates', array( $this, 'redirectppr_options_page' ), 'dashicons-external' );
		add_submenu_page( 'redirect-updates', 'Quick Redirects', 'Quick Redirects', 'manage_options', 'redirect-updates', array( $this,'redirectppr_options_page' ) );
		$qredirectppr_exp_page 	= add_submenu_page( 'redirect-updates', 'Import/Export', 'Import/Export', 'manage_options', 'redirect-import-export', array( $this, 'redirectppr_import_export_page' ) );
		add_submenu_page( 'redirect-updates', 'Redirect Summary', 'Redirect Summary', 'manage_options', 'redirect-summary', array( $this,'redirectppr_summary_page' ) );
		add_submenu_page( 'redirect-updates', 'Redirect Options', 'Redirect Options', 'manage_options', 'redirect-options', array( $this, 'redirectppr_settings_page' ) );
		$qredirectppr_meta_page = add_submenu_page( 'redirect-updates', 'Meta Options', 'Meta Options', 'manage_options', 'meta_addon', array( $this, 'qredirectppr_meta_addon_page' ) );
		add_submenu_page( 'redirect-updates', 'FAQs/Help', 'FAQs/Help', 'manage_options', 'redirect-faqs', array( $this, 'redirectppr_faq_page' ) );
		add_action( 'admin_init', array( $this, 'register_redirectpprsettings' ) );
		add_action( 'load-'.$qredirectppr_meta_page, array( $this, 'qredirectppr_options_help_tab' ) );
		add_action( 'load-'.$qredirectppr_add_page, array( $this, 'qredirectppr_options_help_tab' ) );
		add_action( 'load-'.$qredirectppr_exp_page, array( $this, 'qredirectppr_options_help_tab' ) );

		/* Add Metaboxes */
		$usetypes = get_option( 'redirectppr_use-custom-post-types', 0 ) != 0 ? 1 : 0;
		if( $usetypes == 1 ){
			$post_types_temp = get_post_types();
			if( count( $post_types_temp ) == 0){
				$post_types_temp = array(
					'page' 			=> 'page',
					'post' 			=> 'post',
					'attachment' 	=> 'attachment',
					'nav_menu_item' => 'nav_menu_item'
				);
			}
			unset( $post_types_temp['revision'] ); 		// remove revions from array if present as they are not needed.
			unset( $post_types_temp['attachment'] ); 	// remove from array if present as they are not needed.
			unset( $post_types_temp['nav_menu_item'] ); // remove from array if present as they are not needed.
		}else{
			//use only for Page && Post if not set to use custom post types
			$post_types_temp = array(
				'page' => 'page',
				'post' => 'post'
			);
		}
		
		$ptypesNOTok = is_array( $this->redirectpprptypes_ok ) ? $this->redirectpprptypes_ok : array();
		
		foreach( $post_types_temp as $type ){
			if( !in_array( $type, $ptypesNOTok ) ){
				$context 	= apply_filters('appip_metabox_context_filter','normal');
				$priority 	= apply_filters('appip_metabox_priority_filter','high');
				add_meta_box( 'edit-box-redirectppr', __( 'Quick Page/Post Redirect', 'quick-pagepost-redirect-plugin' ) , array( $this, 'edit_box_redirectppr_1' ), $type, $context, $priority ); 
			}
		}
	}
	
	function qredirectppr_admin_scripts($hook){
		if(in_array( $hook, array( 'post-new.php', 'edit.php', 'post.php', 'toplevel_page_redirect-updates', 'quick-redirects_page_redirect-options', 'quick-redirects_page_redirect-summary', 'quick-redirects_page_redirect-faqs', 'quick-redirects_page_redirect-import-export', 'quick-redirects_page_meta_addon' ) ) ){
			$ajax_add_nonce = wp_create_nonce( 'qredirectppr_ajax_verify' );
			$secDeleteNonce = wp_create_nonce( 'qredirectppr_ajax_delete_ALL_verify' );
			$protocols 		= apply_filters( 'qredirectppr_allowed_protocols', array( 'http', 'https', 'ftp', 'ftps', 'mailto', 'news', 'irc', 'gopher', 'nntp', 'feed', 'telnet', 'mms', 'rtsp', 'svn', 'tel', 'fax', 'xmpp'));
			wp_enqueue_style( 'qredirectppr_admin_meta_style', plugins_url( '/css/qredirectppr_admin_style.css', __FILE__ ) , null , $this->redirectppr_curr_version );
			//wp_enqueue_script( 'qredirectppr_admin_meta_script', plugins_url( '/js/qredirectppr_admin_script.js', __FILE__ ) , array('jquery'), $this->redirectppr_curr_version );
			wp_enqueue_script( 'qredirectppr_admin_meta_script', plugins_url( '/js/qredirectppr_admin_script.min.js', __FILE__ ) , array('jquery'), $this->redirectppr_curr_version );
			wp_localize_script( 'qredirectppr_admin_meta_script', 'qredirectpprData', array( 'msgAllDeleteConfirm' => __( 'Are you sure you want to PERMANENTLY Delete ALL Redirects and Settings (this cannot be undone)?', 'quick-pagepost-redirect-plugin' ),'msgQuickDeleteConfirm' => __( 'Are you sure you want to PERMANENTLY Delete ALL Quick Redirects?', 'quick-pagepost-redirect-plugin' ), 'msgIndividualDeleteConfirm' => __( 'Are you sure you want to PERMANENTLY Delets ALL Individual Redirects?', 'quick-pagepost-redirect-plugin' ), 'securityDelete' => $secDeleteNonce, 'protocols' => $protocols, 'msgDuplicate' => __( 'Redirect could not be saved as a redirect already exists with the same Request URL.', 'quick-pagepost-redirect-plugin' ) , 'msgDeleteConfirm' => __( 'Are you sure you want to delete this redirect?', 'quick-pagepost-redirect-plugin' ) , 'msgErrorSave' => __( 'Error Saving Redirect\nTry refreshing the page and trying again.', 'quick-pagepost-redirect-plugin' ) , 'msgSelect' => 'select a file', 'msgFileType' => __( 'File type not allowed,\nAllowed file type: *.txt', 'quick-pagepost-redirect-plugin' ) , 'adminURL' => admin_url('admin.php'),'ajaxurl'=> admin_url('admin-ajax.php'), 'security' => $ajax_add_nonce, 'error' => __('Please add at least one redirect before submitting form', 'quick-pagepost-redirect-plugin')));
		}
		return;
	}	
	
	function qredirectppr_frontend_scripts(){
		global $qredirectppr_setting_links;
		$qredirectppr_setting_links = true;
		$turnOff 		= get_option( 'redirectppr_override-active', '0' ) ;
		$useJQ 			= get_option( 'redirectppr_use-jquery', '0' ) ;
		if( (int) $useJQ == 0 || (int) $turnOff == 1 )
			return;
		global $wpdb;
		$rewrite		= ($this->redirectpproverride_rewrite == '0' || $this->redirectpproverride_rewrite == '') ? false : true;
		$allNewWin 		= get_option( 'redirectppr_override-newwindow', '0' ) ;
		$allNoFoll 		= get_option( 'redirectppr_override-nofollow', '0' ) ;
		$noFollNewWin 	= get_option( 'quickredirectppr_redirects_meta', array() );
		$mainQuick 		= get_option( 'quickredirectppr_redirects', array() );
		$linkData 		= array();
		if(is_array($noFollNewWin) && !empty($noFollNewWin)){
			foreach( $noFollNewWin as $key => $val ){
				if( (int) $allNewWin == 1 && (int) $allNoFoll == 1 ){
					$linkData[$key] = array( 1, 1 );
				}elseif( ( (int) $val['nofollow'] !== 0 || (int) $allNoFoll == 1 ) || ( (int) $val['newwindow'] !== 0 || (int) $allNewWin == 1 ) ){
					$newwinval 	= (int) $allNewWin == 1 ? 1 : (int) $val['newwindow'];
					$nofollval 	= (int) $allNoFoll == 1 ? 1 : (int) $val['nofollow'];
					$rewriteval = $rewrite && isset($mainQuick[$key]) && $mainQuick[$key] != '' ? $mainQuick[$key] : '';
					$linkData[$key] = array( $newwinval, $nofollval, $rewriteval );
				}
			};
		}
		$joinSQL	= ((int) $allNewWin == 1 || (int) $allNoFoll == 1 || $rewrite ) ? "" : " INNER JOIN {$wpdb->prefix}postmeta AS mt3 ON ( {$wpdb->prefix}posts.ID = mt3.post_id ) ";
		$whereSQL 	= ((int) $allNewWin == 1 || (int) $allNoFoll == 1 || $rewrite ) ? "" : " ( m1.meta_key IN ( '_redirectpprredirect_newwindow' ,'_redirectpprredirect_relnofollow', '_redirectpprredirect_rewritelink', '_redirectpprredirect_url' ) AND m1.meta_value !='0' AND  m1.meta_value !='' ) AND ";
		$finalSQL 	= "SELECT * FROM {$wpdb->prefix}postmeta as `m1` WHERE {$whereSQL} m1.post_id IN ( SELECT post_id FROM {$wpdb->prefix}postmeta as `m` WHERE 1 = 1 AND m.meta_key ='_redirectpprredirect_active' AND m.meta_value = '1');";
		$indReds 	= $wpdb->get_results($finalSQL);
		$parray = array();
		if( is_array($indReds) && !empty($indReds) ){
			foreach( $indReds as $key => $qpost ){
				$postid = $qpost->post_id;
				$postky = $qpost->meta_key;
				$postvl = $qpost->meta_value;
				$parray[ $postid ][ $postky ] = $postvl;
			}
		}
		if( is_array($parray) && !empty($parray) ){
			foreach( $parray as $key => $val ){
				$destURL 	= isset($val['_redirectpprredirect_url']) && $val['_redirectpprredirect_url'] != '' ? $val['_redirectpprredirect_url'] : ''; //get_post_meta( $qpost->ID, '_redirectpprredirect_url', true );
				$rwMeta 	= isset($val['_redirectpprredirect_rewritelink']) && (int)$val['_redirectpprredirect_rewritelink'] == 1 ? true : false; //(int) get_post_meta( $qpost->ID, '_redirectpprredirect_rewritelink', true ) == 1 ? true : false;
				$noFoll 	= (int) $allNoFoll == 1 ? 1 : ( isset($val['_redirectpprredirect_relnofollow']) && (int)$val['_redirectpprredirect_relnofollow'] == 1 ? 1 : 0);//(int) $allNoFoll == 1 ? 1 : ( (int) get_post_meta( $qpost->ID, '_redirectpprredirect_relnofollow', true ) );
				$newWin 	= (int) $allNewWin == 1 ? 1 : ( isset($val['_redirectpprredirect_newwindow']) && $val['_redirectpprredirect_newwindow'] != '' ? 1 : 0);//( get_post_meta( $qpost->ID, '_redirectpprredirect_newwindow', true ) != '' ? 1 : 0 );
				$rewriteval = ($rewrite || $rwMeta) && $destURL != '' ? $destURL : '';
				$redURL 	= get_permalink( $key );
				$linkData[$redURL] = array( $newWin, $noFoll, $rewriteval );
			}
		}
		
		$qredirectppr_setting_links = false;
		//wp_enqueue_script( 'qredirectppr_frontend_scripts', plugins_url( '/js/qredirectppr_frontend_script.js', __FILE__ ) , array('jquery'), $this->redirectppr_curr_version, true );
		wp_enqueue_script( 'qredirectppr_frontend_scripts', plugins_url( '/js/qredirectppr_frontend_script.min.js', __FILE__ ) , array('jquery'), $this->redirectppr_curr_version, true );
		wp_localize_script( 'qredirectppr_frontend_scripts', 'qredirectpprFrontData', array( 'linkData' => $linkData, 'siteURL' => site_url(), 'siteURLq' => $this->getQAddress() ) );
	}
	
	function register_redirectpprsettings() {
		register_setting( 'redirectppr-settings-group', 'redirectppr_use-custom-post-types' );
		register_setting( 'redirectppr-settings-group', 'redirectppr_override-nofollow' );
		register_setting( 'redirectppr-settings-group', 'redirectppr_override-newwindow' );
		register_setting( 'redirectppr-settings-group', 'redirectppr_override-redirect-type' );
		register_setting( 'redirectppr-settings-group', 'redirectppr_override-active' );
		register_setting( 'redirectppr-settings-group', 'redirectppr_override-URL' );
		register_setting( 'redirectppr-settings-group', 'redirectppr_override-rewrite' );
		register_setting( 'redirectppr-settings-group', 'redirectppr_use-jquery' );
		register_setting( 'redirectppr-settings-group', 'redirectppr_qredirectpprptypeok' );
		register_setting( 'redirectppr-settings-group', 'redirectppr_override-casesensitive' );
		register_setting( 'redirectppr-settings-group', 'redirectppr_show-columns' );
		//meta settings
		register_setting( 'qredirectppr-meta-settings-group', 'qredirectppr_meta_addon_sec' );
		register_setting( 'qredirectppr-meta-settings-group', 'qredirectppr_meta_addon_load' );
		register_setting( 'qredirectppr-meta-settings-group', 'qredirectppr_meta_append_to' );
		register_setting( 'qredirectppr-meta-settings-group', 'qredirectppr_meta_addon_trigger' );
		register_setting( 'qredirectppr-meta-settings-group', 'qredirectppr_meta_addon_content' );
		register_setting( 'qredirectppr-meta-settings-group', 'redirectppr_meta-seconds' );
		register_setting( 'qredirectppr-meta-settings-group', 'redirectppr_meta-message' );
	}

	function redirectppr_wp_feed_options( $cache, $url ){
		// this is only for testing cached FAQ
		if( $url == "http://www.anadnet.com/?feed=qredirectppr_faqs" )
			$cache = '1';
		return $cache;
	}
	
	function redirectppr_faq_page(){
		include_once(ABSPATH . WPINC . '/feed.php');
		echo '
		<div class="wrap">
		 	<h2>' . __( 'Quick Page/Post Redirect FAQs/Help', 'quick-pagepost-redirect-plugin' ) . '</h2>
			<div align="left"><p>' . __( 'The FAQS are now on a feed that can be updated on the fly. If you have a question and don\'t see an answer, please send an email to <a href="mailto:info@anadnet.com">info@anadnet.com</a> and ask your question. If it is relevant to the plugin, it will be added to the FAQs feed so it will show up here. Please be sure to include the plugin you are asking a question about (Quick Page/Post Redirect Plugin) and any other information like your WordPress version and examples if the plugin is not working correctly for you. THANKS!', 'quick-pagepost-redirect-plugin' ) . '</p>
			<hr noshade color="#C0C0C0" size="1" />
		';
		$rss 			= fetch_feed( 'http://www.anadnet.com/?feed=qredirectppr_faqs&ver=' . $this->redirectppr_curr_version . '&loc=' . urlencode( $this->homelink ) );
		$linkfaq 		= array();
		$linkcontent 	= array();
		if (!is_wp_error( $rss ) ) : 
		    $maxitems 	= $rss->get_item_quantity( 100 ); 
		    $rss_items 	= $rss->get_items( 0, $maxitems ); 
		endif;
			$aqr = 0;
		    if ($maxitems != 0){
			    foreach ( $rss_items as $item ) :
			    	$aqr++; 
			    	$linkfaq[]		= '<li class="faq-top-item"><a href="#faq-'.$aqr.'">'.esc_html( $item->get_title() ).'</a></li>';
				    $linkcontent[] 	= '<li class="faq-item"><a name="faq-'.$aqr.'"></a><h3 class="qa"><span class="qa">Q. </span>'.esc_html( $item->get_title() ).'</h3><div class="qa-content"><span class="qa answer">A. </span>'.$item->get_content().'</div><div class="toplink"><a href="#faq-top">top &uarr;</a></li>';
			    endforeach;
			}
		echo '<a name="faq-top"></a><h2>'.__('Table of Contents','quick-pagepost-redirect-plugin').'</h2>';
		echo '<ol class="qredirectppr-faq-links">';
		echo implode( "\n", $linkfaq );
		echo '</ol>';
		echo '<h2>' . __( 'Questions/Answers', 'quick-pagepost-redirect-plugin' ) . '</h2>';
		echo '<ul class="qredirectppr-faq-answers">';
		echo implode( "\n", $linkcontent );
		echo '</ul>';
		echo '
			</div>
		</div>';
	}
	
	function redirectppr_summary_page() {
?>
<div class="wrap">
	<h2><?php echo __( 'Quick Page Post Redirect Summary', 'quick-pagepost-redirect-plugin' );?></h2>
	<p><?php echo __( 'This is a summary of Individual &amp; Quick 301 Redirects.', 'quick-pagepost-redirect-plugin' );?></p>
	<br/>
	<?php if($this->updatemsg!=''){?>
	<div class="updated settings-error" id="setting-error-settings_updated">
		<p><strong><?php echo $this->updatemsg;?></strong></p>
	</div>
	<?php } ?>
	<?php $this->updatemsg ='';?>
	<h2 style="font-size:20px;"><?php echo __( 'Summary', 'quick-pagepost-redirect-plugin' );?></h2>
	<div align="left">
		<?php 		    		
			if($this->redirectpproverride_active =='1'){echo '<div class="redirectppr-acor" style="margin:1px 0;width: 250px;font-weight: bold;padding: 2px;">' . __( 'Acitve Override is on - All Redirects are OFF!', 'quick-pagepost-redirect-plugin' ) . '</div>';}
			if($this->redirectpproverride_nofollow =='1'){echo '<div class="redirectppr-nfor" style="margin:1px 0;width: 200px;font-weight: bold;padding: 2px;">' . __( 'No Follow Override is on!', 'quick-pagepost-redirect-plugin' ) . '</div>';}
			if($this->redirectpproverride_newwin =='1'){echo '<div class="redirectppr-nwor" style="margin:1px 0;width: 200px;font-weight: bold;padding: 2px;">' . __( 'New Window Override is on!', 'quick-pagepost-redirect-plugin' ) . '</div>';}
			if($this->redirectpproverride_rewrite =='1'){echo '<div class="redirectppr-rrlor" style="margin:1px 0;width: 200px;font-weight: bold;padding: 2px;">' . __( 'Rewrite Override is on!', 'quick-pagepost-redirect-plugin' ) . '</div>';}
			$labels 	= array(
				__( 'ID', 'quick-pagepost-redirect-plugin' ),
				__( 'post type', 'quick-pagepost-redirect-plugin' ),
				__( 'active', 'quick-pagepost-redirect-plugin' ),
				__( 'no follow', 'quick-pagepost-redirect-plugin' ),
				__( 'new window', 'quick-pagepost-redirect-plugin' ),
				__( 'type', 'quick-pagepost-redirect-plugin' ),
				__( 'rewrite link', 'quick-pagepost-redirect-plugin' ),
				__( 'original URL', 'quick-pagepost-redirect-plugin' ),
				__( 'redirect to URL', 'quick-pagepost-redirect-plugin' )
			);
			$labelsTD 	= array(
				'<span>'.$labels[0].' :</span>',
				'<span>'.$labels[1].' :</span>',
				'<span>'.$labels[2].' :</span>',
				'<span>'.$labels[3].' :</span>',
				'<span>'.$labels[4].' :</span>',
				'<span>'.$labels[5].' :</span>',
				'<span>'.$labels[6].' :</span>',
				'<span>'.$labels[7].' :</span>',
				'<span>'.$labels[8].' :</span>',
			)
			?>
		<table class="form-table qform-table" width="100%">
			<thead>
				<tr scope="col" class="headrow">
					<th align="center"><?php echo $labels[0];?></th>
					<th align="center"><?php echo $labels[1];?></th>
					<th align="center"><?php echo $labels[2];?></th>
					<th align="center"><?php echo $labels[3];?></th>
					<th align="center"><?php echo $labels[4];?></th>
					<th align="center"><?php echo $labels[5];?></th>
					<th align="center"><?php echo $labels[6];?></th>
					<th align="left"><?php echo $labels[7];?></th>
					<th align="left"><?php echo $labels[8];?></th>
				</tr>
			</thead>
			<tbody>
			<?php 
			$tempReportArray = array();
			$tempa = array();
			$tempQTReportArray = array();
			if( !empty( $this->quickredirectppr_redirects)){
				foreach( $this->quickredirectppr_redirects as $key => $redir ){
					$tempQTReportArray 	= array('url'=>$key,'destinaition'=>$redir);
					$qr_nofollow 		= isset($this->quickredirectppr_redirectsmeta[$key]['nofollow']) && $this->quickredirectppr_redirectsmeta[$key]['nofollow'] != '' ? $this->quickredirectppr_redirectsmeta[$key]['nofollow'] : '0';
					$qr_newwindow 		= isset($this->quickredirectppr_redirectsmeta[$key]['newwindow']) && $this->quickredirectppr_redirectsmeta[$key]['newwindow'] != '' ? $this->quickredirectppr_redirectsmeta[$key]['newwindow'] : '0';
					$qrtredURL 			= (int) $this->redirectpproverride_rewrite 	== 1 && $this->redirectpproverride_URL != '' ? '<span class="redirectppr-rrlor">'.$this->redirectpproverride_URL.'</span>' : $redir;
					$qrtactive 			= (int) $this->redirectpproverride_active 	== 1 ? '<span class="redirectppr-acor">0</span>' : 1;
					$qr_nofollow		= (int) $this->redirectpproverride_nofollow	== 1 ? '<span class="redirectppr-nfor">1</span>' : $qr_nofollow;
					$qr_newwindow 		= (int) $this->redirectpproverride_newwin 	== 1 ? '<span class="redirectppr-nwor">1</span>' : $qr_newwindow;
					$qrtrewrit 			= (int) $this->redirectpproverride_rewrite 	== 1 ? '<span class="redirectppr-rrlor">1</span>': 'N/A';
					$tempReportArray[] = array(
						'_redirectpprredirect_active' => $qrtactive,
						'_redirectpprredirect_rewritelink' => $qrtrewrit,
						'_redirectpprredirect_relnofollow' => $qr_nofollow,
						'_redirectpprredirect_newwindow' => $qr_newwindow,
						'_redirectpprredirect_type' => 'Quick',
						'post_type' => 'N/A',
						'id' => 'N/A',
						'origurl' => $key,
						'_redirectpprredirect_url' => $qrtredURL,
						'_redirectpprredirect_meta_secs' => $this->redirectpprmeta_seconds,
						);
				}
			}
			if(!empty($this->redirectppr_all_redir_array)){
				foreach( $this->redirectppr_all_redir_array as $key => $result ){
					$tempa['id']= $key;
					$tempa['post_type'] = get_post_type( $key );
					if(count($result)>0){
						foreach($result as $metakey => $metaval){
							$tempa[$metakey] = $metaval;
						}
					}
					$tempReportArray[] = $tempa;
					unset($tempa);
				}
			}
			if(!empty($tempReportArray)){
				$pclass = 'onrow';
				foreach($tempReportArray as $reportItem){
					$tactive = $reportItem['_redirectpprredirect_active'];
					if($this->redirectpproverride_active =='1'){$tactive = '<span class="redirectppr-acor">0</span>';}
					$trewrit = $reportItem['_redirectpprredirect_rewritelink'];
					$tnofoll = $reportItem['_redirectpprredirect_relnofollow'];
					$tnewwin = $reportItem['_redirectpprredirect_newwindow'];
					$tredSec = $reportItem['_redirectpprredirect_meta_secs'];
					$tretype = $reportItem['_redirectpprredirect_type'];
					$tredURL = $reportItem['_redirectpprredirect_url'];
					$tpotype = $reportItem['post_type'];
					$tpostid = $reportItem['id'];
					if($tnewwin == '0' || $tnewwin == ''){
						$tnewwin = '0';
					}elseif($tnewwin == 'N/A'){
						$tnewwin = 'N/A';
					}elseif($tnewwin == '_blank'){
						$tnewwin = '1';
					};
					$tnofoll	= (int) $this->redirectpproverride_nofollow == 1 ? '<span class="redirectppr-nfor">1</span>' : $tnofoll;
					$tnewwin 	= (int) $this->redirectpproverride_newwin == 1 ? '<span class="redirectppr-nwor">1</span>' : $tnewwin;
					$trewrit	= (int) $this->redirectpproverride_rewrite == 1 ? '<span class="redirectppr-rrlor">1</span>' : $trewrit;
					$tredURL 	= (int) $this->redirectpproverride_rewrite == 1 && $this->redirectpproverride_URL != '' ? '<span class="redirectppr-rrlor">' . $this->redirectpproverride_URL . '</span>' : $tredURL;
					$toriurl 	= isset($reportItem['origurl']) ? $reportItem['origurl'] : get_permalink($tpostid);
					$pclass 	= $pclass == 'offrow' ? 'onrow' : 'offrow';
					if($tredURL == 'http://www.example.com' || $tredURL == '<span class="redirectppr-rrlor">http://www.example.com</span>'){$tredURL='<strong>N/A - redirection will not occur</strong>';}
				?>
				<tr class="<?php echo $pclass;?>">
					<?php if( $tpostid != 'N/A'){ ?> 
					<td align="left"><?php echo $labelsTD[0];?><a href="<?php echo admin_url('post.php?post='.$tpostid.'&action=edit');?>" title="edit"><?php echo $tpostid;?></a></td>
					<?php }else{ ?>
					<td align="left"><?php echo $labelsTD[0];?><?php echo $tpostid;?></td>
					<?php } ?>
					<td align="center"><?php echo $labelsTD[1];?><?php echo $tpotype;?></td>
					<td align="center"><?php echo $labelsTD[2];?><?php echo $tactive;?></td>
					<td align="center"><?php echo $labelsTD[3];?><?php echo $tnofoll;?></td>
					<td align="center"><?php echo $labelsTD[4];?><?php echo $tnewwin;?></td>
					<td align="center"><?php echo $labelsTD[5];?><?php echo $tretype;?></td>
					<td align="center"><?php echo $labelsTD[6];?><?php echo $trewrit;?></td>
					<td align="left"><?php echo $labelsTD[7];?><?php echo $toriurl;?></td>
					<td align="left"><?php echo $labelsTD[8];?><?php echo $tredURL;?></td>
				</tr>
			<?php }
			}
		 ?>
		 	</tbody>
		</table>
	</div>
</div>
<?php 
	} 
	
	function redirectppr_import_export_page(){
		if(isset($_GET['update'])){
			if($_GET['update']=='4'){$this->updatemsg ='' . __( 'Quick Redirects Imported & Replaced.', 'quick-pagepost-redirect-plugin' ) . '';}
			if($_GET['update']=='5'){$this->updatemsg ='' . __( 'Quick Redirects Imported & Added to Existing Redirects.', 'quick-pagepost-redirect-plugin' ) . '';}
		}
		echo '<div class="wrap">';
		echo '	<h2>' . __( 'Import/Export Redirects', 'quick-pagepost-redirect-plugin' ) . '</h2>';
		if($this->updatemsg != '')
			echo '	<div class="updated settings-error" id="setting-error-settings_updated"><p><strong>' . $this->updatemsg . '</strong></p></div>';
		$this->updatemsg = '';
		?>
		<div class="qredirectppr-content">
			<div class="qredirectppr-sidebar">
				<div class="redirectpprdonate">
					<div>
						<form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_blank">
							<input name="cmd" value="_s-xclick" type="hidden"/>
							<input name="hosted_button_id" value="8274582" type="hidden"/>
							<input alt="PayPal - The safer, easier way to pay online!" name="submit" src="https://www.paypal.com/en_US/i/btn/btn_donateCC_LG.gif" type="image">
							<img src="https://www.paypal.com/en_US/i/scr/pixel.gif" alt="" border="0" height="1" width="1" />
						</form>
					</div>
					<?php echo __( 'If you enjoy or find any of our plugins useful, please donate a few dollars to help with future development and updates. We thank you in advance.', 'quick-pagepost-redirect-plugin' );?>
				</div>
			</div>
			<div class="qredirectppr-left">
				<table style="border-collapse: collapse" class="form-table">
				<tr valign="top">
					<td><label class="qredirectppr-label"><strong><?php echo __( 'Export Redirects', 'quick-pagepost-redirect-plugin' );?></strong></label>
					<p><?php echo __( 'You should back-up your redirect regularly in case something happens to the database.', 'quick-pagepost-redirect-plugin' );?></p>
						<p><?php echo __( 'Please use the below buttons to make a back-up as either encoded (unreadable) or pipe separated', 'quick-pagepost-redirect-plugin' );?> (<code>|</code>).</p>
						<br /><p><input class="button button-primary qredirectppr-export-quick-redirects" type="button" name="qredirectppr-export-quick-redirects" value="<?php echo __( 'EXPORT all Quick Redirects (Encoded)', 'quick-pagepost-redirect-plugin' );?>" onclick="document.location='<?php echo wp_nonce_url( admin_url('admin.php?page=redirect-options&qredirectppr-file-type=encoded').'&action=export-quick-redirects-file', 'export-redirects-qredirectppr'); ?>';" /></p>
						<p><?php echo __( 'OR', 'quick-pagepost-redirect-plugin' );?></p>
						<p><input class="button button-primary qredirectppr-export-quick-redirects" type="button" name="qredirectppr-export-quick-redirects" value="<?php echo __( 'EXPORT all Quick Redirects (PIPE Separated)', 'quick-pagepost-redirect-plugin' );?>" onclick="document.location='<?php echo wp_nonce_url( admin_url('admin.php?page=redirect-options').'&action=export-quick-redirects-file&qredirectppr-file-type=pipe', 'export-redirects-qredirectppr'); ?>';" /></p>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row" colspan="2"><hr noshade color="#EAEAEA" size="1"></th>
				</tr>
				<tr valign="top">
					<td><label class="qredirectppr-label"><strong><?php echo __( 'Import Redirects', 'quick-pagepost-redirect-plugin' );?></strong></label>
					<p><?php echo __( 'If you want to replace or restore redirects from a file, use the "Restore" option.', 'quick-pagepost-redirect-plugin' );?></p>
					<p><?php echo __( 'To add new redirects in bulk use the "Add To" option - NOTE: to Add To redirects, the file must be pipe dilimited ', 'quick-pagepost-redirect-plugin' );?> (<code>|</code>).</p>
						<br/>
						<input class="button-primary qredirectppr-import-quick-redirects" type="button" id="qredirectppr-import-quick-redirects-button" name="qredirectppr-import-quick-redirects" value="<?php echo __( 'RESTORE Saved Quick Redirects', 'quick-pagepost-redirect-plugin' );?>" />
						<?php echo __( 'OR', 'quick-pagepost-redirect-plugin' );?>
						<input class="button-primary qredirectppr_addto_qr" type="button" id="qredirectppr_addto_qr_button" name="qredirectppr_addto_qr" value="<?php echo __( 'ADD TO Quick Redirects', 'quick-pagepost-redirect-plugin' );?>" />
						<div id="qredirectppr_import_form" class="hide-if-js">
							<form action="<?php echo admin_url('admin.php?page=redirect-import-export'); ?>" method="post" enctype="multipart/form-data">
								<p style="margin:1em 0;">
									<label><?php echo __( 'Select Quick Redirects file to import:', 'quick-pagepost-redirect-plugin' );?></label>
									<input type="file" name="qredirectppr_file" onchange="qredirectppr_check_file(this);" />
								</p>
								<p class="submit">
									<?php wp_nonce_field( 'import-quick-redrects-file' ); ?>
									<input class="button button-primary" type="submit" id="import-quick-redrects-file" name="import-quick-redrects-file" value="IMPORT & REPLACE Current Quick Redirects" />
								</p>
							</form>
						</div>
						<div id="qredirectppr_addto_form" class="hide-if-js">
							<form action="<?php echo admin_url('admin.php?page=redirect-import-export'); ?>" method="post" enctype="multipart/form-data">
								<p style="margin:.5em 0 1em 1em;color:#444;"> <?php echo __( 'The import file should be a text file with one rediect per line, PIPE separated, in this format:', 'quick-pagepost-redirect-plugin' );?><br/>
									<br/>
									<code><?php echo __( 'redirect|destination|newwindow|nofollow', 'quick-pagepost-redirect-plugin' );?></code><br/>
									<br/><?php echo __( 'for Example:', 'quick-pagepost-redirect-plugin' );?>
									<br/><br/>
									<code><?php echo __( '/old-location.htm|http://some.com/new-destination/|0|1', 'quick-pagepost-redirect-plugin' );?></code><br />
									<code><?php echo __( '/dontate/|http://example.com/destination/|1|1', 'quick-pagepost-redirect-plugin' );?></code><br/>
									<br/>
									<strong><?php echo __( 'IMPORTANT:', 'quick-pagepost-redirect-plugin' );?></strong> <?php echo __( 'Make Sure any destination URLs that have a PIPE in the querystring data are URL encoded before adding them!', 'quick-pagepost-redirect-plugin' );?><br/>
									<br/>
									<label><?php echo __( 'Select Quick Redirects file to import:', 'quick-pagepost-redirect-plugin' );?></label>
									<input type="file" name="qredirectppr_file_add" onchange="qredirectppr_check_file(this);" />
								</p>
								<p class="submit">
									<?php wp_nonce_field( 'import_redirects_add_qredirectppr' ); ?>
									<input class="button button-primary" type="submit" id="import_redirects_add_qredirectppr" name="import_redirects_add_qredirectppr" value="<?php echo __( 'ADD TO Current Quick Redirects', 'quick-pagepost-redirect-plugin' );?>" />
								</p>
							</form>
						</div></td>
				</tr>
				<tr valign="top">
					<th scope="row" colspan="2"><hr noshade color="#EAEAEA" size="1"></th>
				</tr>
			</table>
			</div>
			<div class="clear-both"></div>
			</div>
		<?php
		echo '</div>';
	}

	function redirectppr_settings_page() {
		if( isset( $_GET['update'] ) && $_GET['update'] != '' ){
			if( $_GET['update'] == '3' ){ $this->updatemsg = __( 'All Quick Redirects deleted from database.', 'quick-pagepost-redirect-plugin' );}
			if( $_GET['update'] == '2' ){ $this->updatemsg = __( 'All Individual Redirects deleted from database.', 'quick-pagepost-redirect-plugin' );}
			if( $_GET['update'] == '4' ){ $this->updatemsg = __( 'Quick Redirects Imported & Replaced.', 'quick-pagepost-redirect-plugin' );}
			if( $_GET['update'] == '5' ){ $this->updatemsg = __( 'Quick Redirects Imported & Added to Existing Redirects.', 'quick-pagepost-redirect-plugin' );}
			if( $_GET['update'] == '6' ){ $this->updatemsg = __( 'All Redirects and Settings deleted from database', 'quick-pagepost-redirect-plugin' );}
			if( $_GET['update'] == '0' ){ $this->updatemsg = __( 'There was an problem with your last request. Please reload the page and try again.', 'quick-pagepost-redirect-plugin' );}
		}
	?>
<div class="wrap" style="position:relative;">
	<h2><?php echo __( 'Quick Page Post Redirect Options', 'quick-pagepost-redirect-plugin' );?></h2>
	<?php if($this->updatemsg != ''){?>
		<div class="updated" id="setting-error-settings_updated">
			<p><strong><?php echo $this->updatemsg;?></strong></p>
		</div>
	<?php } ?>
	<?php $this->updatemsg = '';//reset message;?>
	<div class="qredirectppr-content">
		<div class="qredirectppr-sidebar">
			<div class="redirectpprdonate">
				<div>
					<form action="https://www.paypal.com/cgi-bin/webscr" method="post">
						<input name="cmd" value="_s-xclick" type="hidden"/>
						<input name="hosted_button_id" value="8274582" type="hidden"/>
						<input alt="PayPal - The safer, easier way to pay online!" name="submit" src="https://www.paypal.com/en_US/i/btn/btn_donateCC_LG.gif" type="image">
						<img src="https://www.paypal.com/en_US/i/scr/pixel.gif" alt="" border="0" height="1" width="1" />
					</form>
				</div>
				<?php echo __( 'If you enjoy or find any of our plugins useful, please donate a few dollars to help with future development and updates. We thank you in advance.', 'quick-pagepost-redirect-plugin' );?>
			</div>
		</div>
		<div class="qredirectppr-left">
		<form method="post" action="options.php" class="qredirectpprform">
			<?php settings_fields( 'redirectppr-settings-group' ); ?>
			<table class="form-table">
				<tr>
					<th scope="row" colspan="2" class="qredirectppr-no-padding"><hr noshade color="#EAEAEA" size="1"></th>
				</tr>
				<tr>
					<th scope="row" colspan="2"><h2 style="display:inline-block;"><?php echo __( 'Basic Settings', 'quick-pagepost-redirect-plugin' );?></h2></th>
				</tr>
				<tr>
					<th scope="row"><label><?php echo __( 'Use with Custom Post Types?', 'quick-pagepost-redirect-plugin' );?></label></th>
					<td><input type="checkbox" name="redirectppr_use-custom-post-types" value="1"<?php if(get_option('redirectppr_use-custom-post-types')=='1'){echo ' checked="checked" ';} ?>/></td>
				</tr>
				<tr>
					<th scope="row"><label><span style="color:#FF0000;font-weight:bold;font-size:100%;margin-left:0px;"><?php echo __( 'Hide', 'quick-pagepost-redirect-plugin' );?></span> <?php echo __( 'meta box for following Post Types:', 'quick-pagepost-redirect-plugin' );?></label></th>
					<td><?php
						$ptypes = get_post_types();
						$ptypesok = $this->redirectpprptypes_ok;
						if(!is_array($ptypesok)){$ptypesok = get_option( 'redirectppr_qredirectpprptypeok' );}
						if(!is_array($ptypesok)){$ptypesok = array();}
						$ptypeHTML = '<div class="qredirectppr-posttypes">';
						foreach($ptypes as $ptype){
							if($ptype != 'nav_menu_item' && $ptype != 'attachment' && $ptype != 'revision'){
								if(in_array($ptype,$ptypesok)){
									$ptypecheck = ' checked="checked"';
								}else{
									$ptypecheck = '';
								}
								$ptypeHTML .= '<div class="qredirectppr-ptype"><input class="qredirectppr-ptypecb" type="checkbox" name="redirectppr_qredirectpprptypeok[]" value="'.$ptype.'"'.$ptypecheck.' /> <div class="redirectppr-type-name">'.$ptype.'</div></div>';
							}
						}
						$ptypeHTML .= '</div>';
					echo $ptypeHTML;
					?></td>
				</tr>
				<tr>
					<th scope="row"><label><?php echo __( 'Show Column Headers?', 'quick-pagepost-redirect-plugin' );?></label></th>
					<td><input type="checkbox" id ="redirectppr_show-columns" name="redirectppr_show-columns" value="1"<?php if(get_option('redirectppr_show-columns')=='1'){echo ' checked="checked" ';} ?>/> <span><?php echo __( 'Show Columns on list pages for set up redirects.', 'quick-pagepost-redirect-plugin' );?></span></td>
				</tr>
				<tr>

					<th scope="row"><label><?php echo __( 'Use jQuery?', 'quick-pagepost-redirect-plugin' );?></label></th>
					<td><input type="checkbox" id ="redirectppr_use-jquery" name="redirectppr_use-jquery" value="1"<?php if(get_option('redirectppr_use-jquery')=='1'){echo ' checked="checked" ';} ?>/> <span><?php echo __( 'Increases effectiveness of plugin. If you have a jQuery conflict, try turning this off.', 'quick-pagepost-redirect-plugin' );?></span><br /><span style="margin:0;"><?php echo __( 'Uses jQuery to add the "New Window" and "No Follow" attributes to links.', 'quick-pagepost-redirect-plugin' );?></span></td>
				</tr>
				<tr>
					<th scope="row" colspan="2" class="qredirectppr-no-padding"><hr noshade color="#EAEAEA" size="1"></th>
				</tr>
				<tr>
					<th scope="row" colspan="2"><h2 style="font-size:20px;display:inline-block;"><?php echo __( 'Master Override Options', 'quick-pagepost-redirect-plugin' );?></h2><span><?php echo __( '<strong>NOTE: </strong>The below settings will override all individual settings.', 'quick-pagepost-redirect-plugin' );?></span></th>
				</tr>
				<tr>
					<th scope="row"><label><?php echo __( 'Turn OFF all Redirects?', 'quick-pagepost-redirect-plugin' );?> </label></th>
					<td><input type="checkbox" name="redirectppr_override-active" value="1"<?php if(get_option('redirectppr_override-active')=='1'){echo ' checked="checked" ';} ?>/> <span><?php echo __( 'Basically the same as having no redirects set up.', 'quick-pagepost-redirect-plugin' );?></span></td>
				</tr>
				<tr>
					<th scope="row"><label><?php echo __( 'Make ALL Redirects have <code>rel="nofollow"</code>?', 'quick-pagepost-redirect-plugin' );?> </label></th>
					<td><input type="checkbox" name="redirectppr_override-nofollow" value="1"<?php if(get_option('redirectppr_override-nofollow')=='1'){echo ' checked="checked" ';} ?>/> <span><?php echo __( 'Requires "use jQuery" option to work with Quick Redirects.', 'quick-pagepost-redirect-plugin' );?></span></td>
				</tr>
				<tr>
					<th scope="row"><label><?php echo __( 'Make ALL Redirects open in a New Window?', 'quick-pagepost-redirect-plugin' );?> </label></th>
					<td><input type="checkbox" name="redirectppr_override-newwindow" value="1"<?php if(get_option('redirectppr_override-newwindow')=='1'){echo ' checked="checked" ';} ?>/>	<span><?php echo __( 'Requires "use jQuery" option to work with Quick Redirects.', 'quick-pagepost-redirect-plugin' );?></span></td>
				</tr>
				<tr>
					<th scope="row"><label><?php echo __( 'Make ALL Redirects this type:', 'quick-pagepost-redirect-plugin' );?> </label></th>
					<td><select name="redirectppr_override-redirect-type">
							<option value="0"><?php echo __( 'Use Individual Settings', 'quick-pagepost-redirect-plugin' );?></option>
							<option value="301" <?php if( get_option('redirectppr_override-redirect-type')=='301') {echo ' selected="selected" ';} ?>>301 <?php echo __( 'Permanant Redirect', 'quick-pagepost-redirect-plugin' );?></option>
							<option value="302" <?php if( get_option('redirectppr_override-redirect-type')=='302') {echo ' selected="selected" ';} ?>>302 <?php echo __( 'Temporary Redirect', 'quick-pagepost-redirect-plugin' );?></option>
							<option value="307" <?php if( get_option('redirectppr_override-redirect-type')=='307') {echo ' selected="selected" ';} ?>>307 <?php echo __( 'Temporary Redirect', 'quick-pagepost-redirect-plugin' );?></option>
							<option value="meta" <?php if(get_option('redirectppr_override-redirect-type')=='meta'){echo ' selected="selected" ';} ?>><?php echo __( 'Meta Refresh Redirect', 'quick-pagepost-redirect-plugin' );?></option>
						</select>
						<span> <?php echo __( '(This will also override Quick Redirects)', 'quick-pagepost-redirect-plugin' );?></span>
					</td>
				</tr>
				<tr>
					<th scope="row"><label><?php echo __( 'Make ALL redirects Case Sensitive?', 'quick-pagepost-redirect-plugin' );?> </label></th>
					<td><input type="checkbox" name="redirectppr_override-casesensitive" value="1"<?php if(get_option('redirectppr_override-casesensitive')=='1'){echo ' checked="checked" ';} ?>/> <span> <?php echo __( 'Makes URLs CaSe SensiTivE - i.e., /somepage/ DOES NOT EQUAL /SoMEpaGe/', 'quick-pagepost-redirect-plugin' );?></span></td>
				</tr>
				<tr>
					<th scope="row"><label><?php echo __( 'Make ALL Redirects go to this URL:', 'quick-pagepost-redirect-plugin' );?> </label></th>
					<td><input type="text" size="50" name="redirectppr_override-URL" value="<?php echo get_option('redirectppr_override-URL'); ?>"/> <span><?php echo __( 'Use full URL including <code>http://</code>.', 'quick-pagepost-redirect-plugin' );?></span></td>
				</tr>
				<tr>
					<th scope="row"><label><?php echo __( 'Rewrite ALL Redirects URLs to Show in LINK?', 'quick-pagepost-redirect-plugin' );?> </label></th>
					<td><input type="checkbox" name="redirectppr_override-rewrite" value="1"<?php if(get_option('redirectppr_override-rewrite')=='1'){echo ' checked="checked" ';} ?>/> <span><?php echo __( 'Makes link show redirect URL instead of the original URL. Will only work on Quick Redirects if the "Use jQuery" option is set.', 'quick-pagepost-redirect-plugin' );?></span></td>
				</tr>
				<tr>
					<th scope="row" colspan="2"><hr noshade color="#EAEAEA" size="1"></th>
				</tr>
				<tr>
					<th scope="row" colspan="2" class="qredirectppr-no-padding"><h2 style="display:inline-block;"><?php echo __( 'Plugin Clean Up', 'quick-pagepost-redirect-plugin' );?></h2><span><?php echo __( '<strong>NOTE: </strong>This will DELETE all redirects - so be careful with this.', 'quick-pagepost-redirect-plugin' );?></span></th>
				</tr>
				<tr>
					<th scope="row"><label><?php echo __( 'Delete Redirects?', 'quick-pagepost-redirect-plugin' );?> </label></th>
					<td>
						<input class="button-secondary qredirectppr-delete-regular" type="button" name="qredirectppr-delete-regular" value="<?php echo __( 'Delete All Individual Redirects', 'quick-pagepost-redirect-plugin' );?>" />
						<input class="button-secondary qredirectppr-delete-quick" type="button" name="qredirectppr-delete-quick" value="<?php echo __( 'Delete all Quick Redirects', 'quick-pagepost-redirect-plugin' );?>" />
						<span style="display: block;margin-top: 5px;"><?php echo __( 'Individual Redirects are redirects set up on individual pages or posts when in the editing screen. The Quick Redirects are set up on the Quick Redirects page.', 'quick-pagepost-redirect-plugin' );?></span>
					</td>
				</tr>
				<tr>
					<th scope="row"><label><?php echo __( 'Delete ALL Redirects & Settings?', 'quick-pagepost-redirect-plugin' );?> </label></th>
					<td>
						<input class="button-secondary qredirectppr-delete-everything" type="button" name="qredirectppr-delete-everything" value="<?php echo __( 'Delete ALL Redirects AND Settings', 'quick-pagepost-redirect-plugin' );?>" />
						<span style="color: #0000ff;display: block;margin-top: 5px;"><?php echo __( 'All Redirects and Settings will be removed from the database. This can NOT be undone!', 'quick-pagepost-redirect-plugin' );?></span>
					</td>
				</tr>
			</table>
			<p class="submit"><input type="submit" class="button-primary" value="<?php echo __( 'Save Changes', 'quick-pagepost-redirect-plugin' );?>" /></p>
		</form>
		</div>
		<div class="clear-both"></div>
	</div>
</div>
<?php } 

	function qredirectppr_options_help_tab(){
		//generate the options page in the wordpress admin
		$screen 	= get_current_screen();
		$screen_id 	= $screen->id;
		if($screen_id == 'toplevel_page_redirect-updates' ){
			$content 	= '
			<div style="padding:10px 0;">		
			<table border="0" cellspacing="0" cellpadding="0">
				<tr>
					<th align="left">Example Requests</th>
					<th align="left"></th>
					<th align="left">Example Destinations</th>
				</tr>
				<tr>
					<td><code>/about.htm</code></td>
					<td>&nbsp;&raquo;&nbsp;</td>
					<td><code>'.$this->homelink.'/about/</code></td>
				</tr>
				<tr>
					<td><code>/directory/landing/</code></td>
					<td>&nbsp;&raquo;&nbsp;</td>
					<td><code>/about/</code></td>
				</tr>
				<tr>
					<td><code>'. str_replace("http://", "https://",$this->homelink).'/contact-us/</code></td>
					<td>&nbsp;&raquo;&nbsp;</td>
					<td><code>'.$this->homelink.'/contact-us-new/</code></td>
				</tr>
			</table>

			</div>
			';
			$screen->add_help_tab( array( 
			   'id' => 'qredirectppr_sample_redirects',
			   'title' => __( 'Examples', 'quick-pagepost-redirect-plugin' ), 
			   'content' => $content ,
			) );
			$screen->add_help_tab( array( 
			   'id' => 'qredirectppr_add_redirects',
			   'title' => __( 'Troubleshooting', 'quick-pagepost-redirect-plugin' ), 
			   'content' => '
			   <div style="padding:10px 0;">		
				<b style="color:red;">' . __( 'IMPORTANT TROUBLESHOOTING NOTES:', 'quick-pagepost-redirect-plugin' ) . '</b>
				<ol style="margin-top:5px;">
					<li style="color:#214070;margin-left:15px;list-style-type:disc;">' . __( 'At this time the New Window (NW) and No Follow (NF) features will not work for Quick Redirects unless "Use jQuery" is enabled in the options.', 'quick-pagepost-redirect-plugin' ) . '</li>
					<li style="color:#214070;margin-left:15px;list-style-type:disc;">' . __( 'It is recommended that the <b>Request URL</b> be relative to the ROOT directory and contain the <code>/</code> at the beginning.', 'quick-pagepost-redirect-plugin' ) . '</li>
					<li style="color:#214070;margin-left:15px;list-style-type:disc;">' . __( 'If you do use the domain name in the Request URL field, make sure it matches your site\'s domain style and protocol. For example, if your site uses "www" in front of your domain name, be sure to include it. if your site uses <code>https://</code>, use it as the protocol. Our best guess is that your domain and protocol are', 'quick-pagepost-redirect-plugin' ) . ' <code>'.network_site_url('/').'</code></li>
					<!--li style="color:#214070;margin-left:15px;list-style-type:disc;">' . __( 'If you are having issues with the link not redirecting on a SSL site with mixed SSL (meaning links can be either SSL or non SSL), try adding two redirects, one with and one without the SSL protocol.', 'quick-pagepost-redirect-plugin' ) . '</li-->
					<li style="color:#214070;margin-left:15px;list-style-type:disc;">' . __( 'The <b>Destination</b> field can be any valid URL or relative path (from root), for example', 'quick-pagepost-redirect-plugin' ) . ' <code>http://www.mysite.com/destination-page/</code> OR <code>/destination-page/</code></li>
					<li style="color:#214070;margin-left:15px;list-style-type:disc;">' . __( 'In order for NW (open in a new window) or NF (rel="nofollow") options to work with Quick Redirects, you need to have:', 'quick-pagepost-redirect-plugin' ) . '
						<ol>
							<li>' . __( '"Use jQuery?" option selected in the settings page', 'quick-pagepost-redirect-plugin' ) . '</li>
							<li>' . __( 'A link that uses the request url SOMEWHERE in your site page - i.e., in a menu, content, sidebar, etc.', 'quick-pagepost-redirect-plugin' ) . ' </li>
							<li>' . __( 'The open in a new window or nofollow settings will not happen if someone just types the old link in the URL or if they come from a bookmark or link outside your site - in essence, there needs to be a link that they click on in your site so that the jQuery script can add the aredirectppropriate <code>target</code> and <code>rel</code> properties to the link to make it work.', 'quick-pagepost-redirect-plugin' ) . '</li>
						</ol>
					</li>
				</ol>
				</div>' ,
			) );
		}elseif( $screen_id == 'quick-redirects_page_redirect-import-export' ){
			$screen->add_help_tab( array( 
			   'id' => 'qredirectppr_export_redirects',
			   'title' => __( 'Export Redirects', 'quick-pagepost-redirect-plugin' ), 
			   'content' => '<div style="padding:10px 0;"><p>' . __( 'You can export redirects in two formats - Encoded or Delimited.', 'quick-pagepost-redirect-plugin' ) . '</p></div>' ,
			) );
			$screen->add_help_tab( array( 
			   'id' => 'qredirectppr_import_redirects',
			   'title' => __( 'Import Redirects', 'quick-pagepost-redirect-plugin' ), 
			   'content' => '<div style="padding:10px 0;"><p>Help content coming soon.</p></div>' ,
			) );
		}elseif( $screen_id == 'quick-redirects_page_meta_addon' ){
			$screen->add_help_tab( array( 
			   'id' => 'qredirectppr-load-page-content',
			   'title' => __( 'Load Content?', 'quick-pagepost-redirect-plugin' ), 
			   'content' => '<div style="padding:10px 0;"><p>' . __( 'Use the <strong>Load Content?</strong> option to allow the page content to load as normal or to only load a blank page or the content provided in the <strong>Page Content</strong> section. ', 'quick-pagepost-redirect-plugin' ) . '</p>
			   <p>' . __( 'If checked, all of the original content will load, so keep this in mind when setting the <strong>Redirect Seconds</strong> - if set too low, the page will not compeletely load. ', 'quick-pagepost-redirect-plugin' ) . '</p></div>' ,
			) );
			$screen->add_help_tab( array( 
			   'id' => 'qredirectppr-redirect-seconds',
			   'title' => __( 'Redirect Seconds', 'quick-pagepost-redirect-plugin' ), 
			   'content' => '<div style="padding:10px 0;"><p>' . __( 'Enter the nuber of seconds to wait before the redirect happens. Enter 0 to have an instant redirect*.', 'quick-pagepost-redirect-plugin' ) . '</p>
			   <p>' . __( '*Keep in mind that the redirect seconds will start counting only AFTER the <strong>Redirect Trigger</strong> element is loaded - so 0 may be slightly longer than instant, depending on how much content needs to load before the trigger happens.', 'quick-pagepost-redirect-plugin' ) . '</p></div>' ,
			) );
			$screen->add_help_tab( array( 
			   'id' => 'qredirectppr-redirect-trigger',
			   'title' => __( 'Redirect Trigger', 'quick-pagepost-redirect-plugin' ), 
			   'content' => '<div style="padding:10px 0;"><p>' . __( 'The class or id or tag name of the element to load before the redirect starts counting down. If nothing is used, it will default to the body tag as a trigger.', 'quick-pagepost-redirect-plugin' ) . '</p>
			   <p>' . __( 'If you use a class, the class name should have the "." in the name, i.e., <strong>.my-class-name</strong>', 'quick-pagepost-redirect-plugin' ) . '</p>
			   <p>' . __( 'If you use an id, the id should have the "#" in the name, i.e., <strong>#my-id-name</strong>.', 'quick-pagepost-redirect-plugin' ) . '</p>
			   <p>' . __( 'If you use a tag name, the name should NOT have the "&lt;" or "&gt;" characters in the name, i.e., &lt;body&gt; would just be <strong>body</strong>.', 'quick-pagepost-redirect-plugin' ) . '</p>
			   <p>' . __( 'Do not use a tag name that is common, like "a" or "div" as it will trigger on all events.', 'quick-pagepost-redirect-plugin' ) . '</p></div>' ,
			) );
			$screen->add_help_tab( array( 
			   'id' => 'qredirectppr-redirect-append',
			   'title' => __( 'Append Content To', 'quick-pagepost-redirect-plugin' ), 
			   'content' => '<div style="padding:10px 0;"><p>' . __( 'The class, id or tag name that you want the content in the <strong>Page Content</strong> to be loading into.', 'quick-pagepost-redirect-plugin' ) . '</p>
			   <p>' . __( 'If you are loading the content of the page, use an existing class or id for an existing element (i.e., .page-content) so your additional page content (if any) is loaded into that element.', 'quick-pagepost-redirect-plugin' ) . '</p>
			   <p>' . __( 'When no class, id or tag name is used, the <strong>body</strong> tag will be used.', 'quick-pagepost-redirect-plugin' ) . '</p></div>' ,
			) );
			$screen->add_help_tab( array( 
			   'id' => 'qredirectppr-redirect-content',
			   'title' => __( 'Page Content', 'quick-pagepost-redirect-plugin' ), 
			   'content' => '<div style="padding:10px 0;"><p>' . __( 'This is your page content you want to add. If you have a "tracking pixel" script or image tag you want to use, add it here.', 'quick-pagepost-redirect-plugin' ) . '</p>
			   <p>' . __( 'A good example of use, is adding a tracking script (or Facebook Conversion Pixel) to the <strong>Page Content box</strong> and unchecking the <strong>Load Content?</strong> box. Then set the <strong>Redirect Seconds</strong> to 1 or 2 so the script has a chance to load and set <strong>Append Content</strong> To to "body" and <strong>Redirect Trigger</strong> to "body".', 'quick-pagepost-redirect-plugin' ) . '</p>
			   <p>' . __( 'Additionally, you can add the redirect counter to the page by adding the code sample under the <strong>Page Content</strong> box.', 'quick-pagepost-redirect-plugin' ) . '</p></div>' ,
			) );
		}
	}

	function redirectppr_options_page(){
?>
<div class="wrap">
	<h2><?php echo __( 'Quick Redirects (301 Redirects)', 'quick-pagepost-redirect-plugin' );?></h2>
	<?php if($this->updatemsg != ''){?>
		<div class="updated settings-error" id="setting-error-settings_updated"><p><strong><?php echo $this->updatemsg;?></strong></p></div>
	<?php } ?>
	<?php $this->updatemsg ='';//reset message;?>
	<?php 
	$isJQueryOn 		= get_option('redirectppr_use-jquery');
	$isJQueryMsgHidden 	= get_option('qredirectppr_jQuery_hide_message');
	$isJQueryMsgHidden2 = get_option('qredirectppr_jQuery_hide_message2');?>
		<?php if( $isJQueryOn == '' && ( $isJQueryMsgHidden == '' || $isJQueryMsgHidden == '0' ) ){ ?>
			<div class="usejqredirectpprmessage error below-h2" id="usejqredirectpprmessage">
				<?php echo __( 'The <code>Use jQuery?</code> option is turned off in the settings.<br/>In order to use <strong>NW</strong> (open in a new window) or <strong>NF</strong> (add rel="nofollow") options for Quick Redirects, you must have it enabled.', 'quick-pagepost-redirect-plugin' );?><br/>
				<div class="hideredirectpprjqmessage" style=""><a href="javascript:void(0);" id="hideredirectpprjqmessage"><?php echo __( 'hide this message', 'quick-pagepost-redirect-plugin' );?></a></div>
			</div>
		<?php }elseif($isJQueryMsgHidden2 !='1'){ ?>
			<div class="usejqredirectpprmessage info below-h2" id="usejqredirectpprmessage2">
				<?php echo __( 'To use the <strong>NW</strong> (open in a new window) <strong>NF</strong> (nofollow) options, check the aredirectppropriate option and update when adding redirects. Then, any link in the page that has the request URL will be updated with these options (as long as you have <code>Use jQuery?</code> enabled in the plugin settings.', 'quick-pagepost-redirect-plugin' );?>
				<div class="hideredirectpprjqmessage" style=""><a href="javascript:void(0);" id="hideredirectpprjqmessage2"><?php echo __( 'hide this message', 'quick-pagepost-redirect-plugin' );?></a></div>
			</div>
		<?php }?>
	<p><?php echo __( 'Quick Redirects are useful when you have links from an old site that now come up 404 Not Found, and you need to have them redirect to a new location on the current site - as long as the old site and the current site have the same domain name. They are also helpful if you have an existing URL that you need to send some place else and you don\'t want to create a Page or Post just to use the individual Page/Post Redirect option.', 'quick-pagepost-redirect-plugin' );?></p>
	<p><?php echo __( 'To add Quick Redirects, put the URL for the redirect in the <strong>Request URL</strong> field, and the URL it should be redirected to in the <strong>Destination URL</strong> field. To delete a redirect, click the trash can at the end of that row. To edit a redirect, click the pencil edit icon.', 'quick-pagepost-redirect-plugin' );?></p>
	<p><?php echo __( 'See \'HELP\' in the upper right corner, for troubleshooting problems and example redirects.', 'quick-pagepost-redirect-plugin' );?></p>
	<form method="post" action="admin.php?page=redirect-updates" id="qredirectppr_quick_save_form">
		<?php wp_nonce_field( 'add_qredirectppr_redirects' ); ?>
		<div class="qredirectppr_quick_redirects_wrapper">
		<table border="0" cellspacing="0" cellpadding="0">
			<tr>
				<th align="left" colspan="8"><h3><?php echo __( 'Add New Redirects', 'quick-pagepost-redirect-plugin' );?></h3></th>
			</tr>
			<tr>
				<th align="left" colspan="2"><?php echo __( 'Request URL', 'quick-pagepost-redirect-plugin' );?></th>
				<th align="left">&nbsp;</th>
				<th align="left"><?php echo __( 'Destination URL', 'quick-pagepost-redirect-plugin' );?></th>
				<th align="center"><?php echo __( 'NW', 'quick-pagepost-redirect-plugin' );?>*</th>
				<th align="center"><?php echo __( 'NF', 'quick-pagepost-redirect-plugin' );?>*</th>
				<th align="left"></th>
				<th align="left"></th>
			</tr>
			<tr>
				<td class="table-qredirectppr-req" colspan="2"><input type="text" name="quickredirectppr_redirects[request][]" value="" style="max-width:100%;width:100%;" /></td>
				<td class="table-qredirectppr-arr">&nbsp;&raquo;&nbsp;</td>
				<td class="table-qredirectppr-des"><input type="text" name="quickredirectppr_redirects[destination][]" value="" style="max-width:100%;width:100%;" /></td>
				<td class="table-qredirectppr-nwn"><input class="redirectpprnewwin" type="checkbox" name="quickredirectppr_redirects[newwindow][0]" value="1" title="<?php echo __( 'open in a New Window', 'quick-pagepost-redirect-plugin' );?>" /></td>
				<td class="table-qredirectppr-nfl"><input class="redirectpprnofoll" type="checkbox" name="quickredirectppr_redirects[nofollow][0]" value="1" title="<?php echo __( 'add No Follow', 'quick-pagepost-redirect-plugin' );?>" /></td>
				<td class="table-qredirectppr-edt"></td>
				<td class="table-qredirectppr-del"></td>
			</tr>
			<tr>
				<td class="table-qredirectppr-req" colspan="2"><input type="text" name="quickredirectppr_redirects[request][]" value="" style="max-width:100%;width:100%;" /></td>
				<td class="table-qredirectppr-arr">&nbsp;&raquo;&nbsp;</td>
				<td class="table-qredirectppr-des"><input type="text" name="quickredirectppr_redirects[destination][]" value="" style="max-width:100%;width:100%;" /></td>
				<td class="table-qredirectppr-nwn"><input class="redirectpprnewwin" type="checkbox" name="quickredirectppr_redirects[newwindow][1]" value="1" title="<?php echo __( 'open in a New Window', 'quick-pagepost-redirect-plugin' );?>" /></td>
				<td class="table-qredirectppr-nfl"><input class="redirectpprnofoll" type="checkbox" name="quickredirectppr_redirects[nofollow][1]" value="1" title="<?php echo __( 'add No Follow', 'quick-pagepost-redirect-plugin' );?>" /></td>
				<td class="table-qredirectppr-edt"></td>
				<td class="table-qredirectppr-del"></td>
			</tr>
			<tr>
				<td class="table-qredirectppr-req" colspan="2"><input type="text" name="quickredirectppr_redirects[request][]" value="" style="max-width:100%;width:100%;" /></td>
				<td class="table-qredirectppr-arr">&nbsp;&raquo;&nbsp;</td>
				<td class="table-qredirectppr-des"><input type="text" name="quickredirectppr_redirects[destination][]" value="" style="max-width:100%;width:100%;" /></td>
				<td class="table-qredirectppr-nwn"><input class="redirectpprnewwin" type="checkbox" name="quickredirectppr_redirects[newwindow][2]" value="1" title="<?php echo __( 'open in a New Window', 'quick-pagepost-redirect-plugin' );?>" /></td>
				<td class="table-qredirectppr-nfl"><input class="redirectpprnofoll" type="checkbox" name="quickredirectppr_redirects[nofollow][2]" value="1" title="<?php echo __( 'add No Follow', 'quick-pagepost-redirect-plugin' );?>" /></td>
				<td class="table-qredirectppr-edt"></td>
				<td class="table-qredirectppr-del"></td>
			</tr>
			<tr>
				<td style="text-align:right;" colspan="6"><div style="font-size: 11px;"><em>*<?php echo __( 'New Window(NW) and NoFollow(NF) functionality not available unless "Use with jQuery" is set in the options.', 'quick-pagepost-redirect-plugin' );?></em></div></td>
				<td style="text-align:right;" colspan="2"></td>
			</tr>
			<tr>
				<td align="left" colspan="8"><p class="submit"><input type="submit" name="submit_301" class="button button-primary" value="<?php echo __( 'Add New Redirects', 'quick-pagepost-redirect-plugin' );?>" /></p></td>
			</tr>
			<tr>
				<td class="newdiv" colspan="8"><div></div></td>
			</tr>
			<tr>
				<th align="left" colspan="8"><h3 id="qredirectppr-existing-redirects"><?php echo __( 'Existing Redirects', 'quick-pagepost-redirect-plugin' );?></h3></th>
			</tr>
			<tr>
				<th align="left" colspan="2"><?php echo __( 'Request URL', 'quick-pagepost-redirect-plugin' );?></th>
				<th align="left">&nbsp;</th>
				<th align="left"><?php echo __( 'Destination URL', 'quick-pagepost-redirect-plugin' );?></th>
				<th align="center"><?php echo __( 'NW', 'quick-pagepost-redirect-plugin' );?></th>
				<th align="center"><?php echo __( 'NF', 'quick-pagepost-redirect-plugin' );?></th>
				<th align="center"><?php //echo __( 'Edit', 'quick-pagepost-redirect-plugin' );?></th>
				<th align="center"><?php //echo __( 'Delete', 'quick-pagepost-redirect-plugin' );?></th>
			</tr>
			<?php echo $this->expand_redirects(); ?>
			<tr id="qredirectppr-edit-row-holder" class="qredirectppr-editing">
				<td class="table-qredirectppr-req cloned" colspan="2"><input class="input-qredirectppr-req" type="text" name="request" value="" style="max-width:100%;width:100%;" /></td>
				<td class="table-qredirectppr-arr cloned">&nbsp;&raquo;&nbsp;</td>
				<td class="table-qredirectppr-des cloned"><input class="input-qredirectppr-dest" type="text" name="destination" value="" style="max-width:100%;width:100%;" /></td>
				<td class="table-qredirectppr-nwn cloned"><input class="input-qredirectppr-neww" type="checkbox" name="newwindow" value="1" title="<?php echo __( 'open in a New Window', 'quick-pagepost-redirect-plugin' );?>" /></td>
				<td class="table-qredirectppr-nfl cloned"><input class="input-qredirectppr-nofo" type="checkbox" name="nofollow" value="1" title="<?php echo __( 'add No Follow', 'quick-pagepost-redirect-plugin' );?>" /></td>
				<td class="table-qredirectppr-sav cloned"><span class="qredirectpprfont-save" data-rowid="" title="<?php echo __( 'Save', 'quick-pagepost-redirect-plugin' );?>"></span></td>
				<td class="table-qredirectppr-can cloned"><span class="qredirectpprfont-cancel" data-rowid="" title="<?php echo __( 'Cancel', 'quick-pagepost-redirect-plugin' );?>"></span></td>
			</tr>
			<tr id="qredirectppr-edit-row-saving" class="qredirectppr-saving">
				<td colspan="8" class="qredirectppr-saving-row"><div class="saving"></div></td>
			</tr>
		</table>
		</div>
	</form>
	<table id="qredirectppr-temp-table-holder"><tr><td></td></tr></table>
	</div>
<?php
	} 

	function expand_redirects(){
	//utility function to return the current list of redirects as form fields
		$output = '';
		if (!empty($this->quickredirectppr_redirects)) {
			$ww = 0;
			foreach ($this->quickredirectppr_redirects as $request => $destination) {
				$newWindow 		= isset($this->quickredirectppr_redirectsmeta[$request]['newwindow']) ? (int) $this->quickredirectppr_redirectsmeta[$request]['newwindow'] : 0;
				$noFollow  		= isset($this->quickredirectppr_redirectsmeta[$request]['nofollow']) ? (int) $this->quickredirectppr_redirectsmeta[$request]['nofollow'] : 0;
				$noChecked 		= '';
				$noCheckedAjax 	= '';
				$newChecked 	= '';
				$newCheckedAjax = '';
				if($newWindow == 1){
					$newChecked = ' checked="checked"';
					$newCheckedAjax = 'X';
				}
				if($noFollow == 1){
					$noChecked = ' checked="checked"'; 
					$noCheckedAjax = 'X';
				}
				$output .= '
				<tr id="rowredirectpprdel-'.$ww.'" class="qredirectppr-existing">
					<td class="table-qredirectppr-count"><span class="qredirectppr-count-row">'.($ww + 1).'.</span></td>
					<td class="table-qredirectppr-req"><div class="qredirectppr-request" data-qredirectppr-orig-url="'.esc_attr($request).'">'.esc_attr(urldecode($request)).'</div></td>
					<td class="table-qredirectppr-arr">&nbsp;&raquo;&nbsp;</td>
					<td class="table-qredirectppr-des"><div class="qredirectppr-destination">'.esc_attr(urldecode($destination)).'</div></td>
					<td class="table-qredirectppr-nwn"><div class="qredirectppr-newindow" >'.$newCheckedAjax.'</div></td>
					<td class="table-qredirectppr-nfl"><div class="qredirectppr-nofollow" >'.$noCheckedAjax.'</div></td>
					<td class="table-qredirectppr-edt"><span id="redirectppredit-'.$ww.'" class="edit-qredirectppr dashicons-edit" data-rowid="rowredirectpprdel-'.$ww.'" title="' . __( 'Edit', 'quick-pagepost-redirect-plugin' ) . '"></span></td>
					<td class="table-qredirectppr-del"><span id="redirectpprdel-'.$ww.'" class="delete-qredirectppr dashicons-trash" data-rowid="rowredirectpprdel-'.$ww.'" title="' . __( 'Delete', 'quick-pagepost-redirect-plugin' ) . '"></span></td>
				</tr>
				';
				$ww++;
			}
		}else{
				$output .= '
				<tr >
					<td colspan="8">' . __( 'No Quick Redirects.', 'quick-pagepost-redirect-plugin' ) . '</td>
				</tr>
				';
		}
		return $output;
	}
	
	function redirectppr_filter_links ($link = '', $post = array()) {
		global $qredirectppr_setting_links;
		if( $qredirectppr_setting_links)
			return $link;
		if(isset($post->ID)){	
			$id = $post->ID;
		}else{
			$id = $post;
		}
		$newCheck = is_array( $this->redirectppr_all_redir_array ) ? $this->redirectppr_all_redir_array : array();
		if( array_key_exists( $id, $newCheck ) ){
			$matchedID = $newCheck[$id];
			if($matchedID['_redirectpprredirect_rewritelink'] == '1' || $this->redirectpproverride_rewrite =='1'){ // if rewrite link is checked or override is set
				if($this->redirectpproverride_URL ==''){
					$newURL = $matchedID['_redirectpprredirect_url'];
				}else{
					$newURL = $this->redirectpproverride_URL;
				} // check override
				if( strpos( $newURL, $this->homelink ) >= 0 || strpos( $newURL, 'www.' ) >= 0 || strpos( $newURL, 'http://' ) >= 0 || strpos( $newURL, 'https://') >= 0 ){
					$link = esc_url( $newURL );
				}else{
					$link = esc_url( $this->homelink.'/'. $newURL );
				}
			}
		}
		return $link;
	}
	
	function redirectppr_filter_page_links ($link, $post) {
		global $qredirectppr_setting_links;
		if( $qredirectppr_setting_links)
			return $link;
		$id 		= isset( $post->ID ) ? $post->ID : $post;
		$newCheck 	= $this->redirectppr_all_redir_array;
		if( !is_array( $newCheck ) ){
			$newCheck = array();
		}
		if( array_key_exists( $id, $newCheck ) ){
			$matchedID = $newCheck[$id];
			if($matchedID['_redirectpprredirect_rewritelink'] == '1' || $this->redirectpproverride_rewrite =='1'){ // if rewrite link is checked
				if($this->redirectpproverride_URL ==''){
					$newURL = $matchedID['_redirectpprredirect_url'];
				}else{
					$newURL = $this->redirectpproverride_URL;
				} // check override
				if(strpos($newURL,$this->homelink)>=0 || strpos($newURL,'www.')>=0 || strpos($newURL,'http://')>=0 || strpos($newURL,'https://')>=0){
					$link = esc_url( $newURL );
				}else{
					$link = esc_url( $this->homelink.'/'. $newURL );
				}
			}
		}
		return $link;
	}
	
	function get_main_array(){
		global $wpdb;
		$this->redirectpprptypes_ok	= get_option( 'redirectppr_qredirectpprptypeok', array() );		
		if( is_array( $this->redirectppr_all_redir_array ) && ! empty( $this->redirectppr_all_redir_array ) )
			return $this->redirectppr_all_redir_array;
		$theArray 	= array();
		$theArrayNW = array();
		$theArrayNF = array();
		$theqsl 	= "SELECT * FROM $wpdb->postmeta a, $wpdb->posts b  WHERE a.`post_id` = b.`ID` AND b.`post_status` != 'trash' AND ( a.`meta_key` = '_redirectpprredirect_active' OR a.`meta_key` = '_redirectpprredirect_rewritelink' OR a.`meta_key` = '_redirectpprredirect_newwindow' OR a.`meta_key` = '_redirectpprredirect_relnofollow' OR a.`meta_key` = '_redirectpprredirect_type' OR a.`meta_key` = '_redirectpprredirect_url') ORDER BY a.`post_id` ASC;";
		$thetemp 	= $wpdb->get_results($theqsl);
		if( count( $thetemp ) > 0 ){
			foreach( $thetemp as $key ){
				$theArray[$key->post_id][$key->meta_key] = $key->meta_value;
			}
			foreach( $thetemp as $key ){
				// defaults
				if(!isset($theArray[$key->post_id]['_redirectpprredirect_rewritelink'])){$theArray[$key->post_id]['_redirectpprredirect_rewritelink']	= 0;}
				if(!isset($theArray[$key->post_id]['_redirectpprredirect_url'])){$theArray[$key->post_id]['_redirectpprredirect_url']					= '';}
				if(!isset($theArray[$key->post_id]['_redirectpprredirect_type'] )){$theArray[$key->post_id]['_redirectpprredirect_type']				= 302;}
				if(!isset($theArray[$key->post_id]['_redirectpprredirect_relnofollow'])){$theArray[$key->post_id]['_redirectpprredirect_relnofollow']	= 0;}
				if(!isset($theArray[$key->post_id]['_redirectpprredirect_newwindow'] ))	{$theArray[$key->post_id]['_redirectpprredirect_newwindow']		= 0;}
				if(!isset($theArray[$key->post_id]['_redirectpprredirect_meta_secs'] ))	{$theArray[$key->post_id]['_redirectpprredirect_meta_secs']		= 0;}
				if(!isset($theArray[$key->post_id]['_redirectpprredirect_active'] )){$theArray[$key->post_id]['_redirectpprredirect_active']			= 0;}
				if($theArray[$key->post_id]['_redirectpprredirect_newwindow']!= '0' || $this->redirectpproverride_newwin =='1'){
					$theArrayNW[$key->post_id] = get_permalink($key->ID);
				}
				if($theArray[$key->post_id]['_redirectpprredirect_relnofollow']!= '0' || $this->redirectpproverride_nofollow =='1'){
					$theArrayNF[$key->post_id] = get_permalink($key->ID);
				}
			}
		}
		$this->redirectppr_newwindow = $theArrayNW;
		$this->redirectppr_nofollow  = $theArrayNF;
		return $theArray;
	}
	
	function get_value($theval='none'){
		return isset($this->$theval) ? $this->$theval : 0;
	}
	
	function redirectppr_queryhook($vars) {
		$vars[] = 'qredirectppr-file-type';
		return $vars;
	}
	
	function redirectppr_parse_request_new($wp) {	
		global $wp, $wpdb;
		$this->redirectppr_all_redir_array	= $this->get_main_array();
		$this->redirectpprptypes_ok	= get_option( 'redirectppr_qredirectpprptypeok', array() );
		if( current_user_can( 'manage_options' ) ){
			if ( isset( $_GET['action'] ) && $_GET['action'] == 'export-quick-redirects-file' ) {
				$newQredirectppr_Array = array();
				check_admin_referer( 'export-redirects-qredirectppr' );
				$type = isset( $_GET['qredirectppr-file-type'] ) && sanitize_text_field( $_GET['qredirectppr-file-type'] ) == 'encoded' ? 'encoded' : 'pipe' ; // can be 'encoded' or 'pipe';
				header( 'Expires: Sat, 26 Jul 1997 05:00:00 GMT' ); 
				header( 'Last-Modified: ' . gmdate( 'D, d M Y H:i:s' ) . ' GMT' ); 
				header( 'Cache-Control: no-store, no-cache, must-revalidate' ); 
				header( 'Cache-Control: post-check=0, pre-check=0', false ); 
				header( 'Pragma: no-cache' ); 
				header( "Content-Type: application/force-download" );
				header( "Content-Type: application/octet-stream" );
				header( "Content-Type: application/download" );
				header( "Content-Disposition: attachment; filename=qredirectppr-quick-redirects-export-" . date('U') . ".txt;" );
				$newQredirectppr_Array['quickredirectppr_redirects'] 		= get_option( 'quickredirectppr_redirects', array() );
				$newQredirectppr_Array['quickredirectppr_redirects_meta'] 	= get_option( 'quickredirectppr_redirects_meta', array() );
				if( $type == 'encoded' ){
					die( 'QUICKPAGEPOSTREDIRECT' . base64_encode( serialize( $newQredirectppr_Array ) ) );
				}else{
					if( is_array( $newQredirectppr_Array ) ){
						$qredirectpprs = $newQredirectppr_Array['quickredirectppr_redirects'];
						$qredirectpprm = $newQredirectppr_Array['quickredirectppr_redirects_meta'];
						foreach($qredirectpprs as $key=>$val){
							$nw 	= ( isset( $qredirectpprm[$key]['newwindow'] ) && $qredirectpprm[$key]['newwindow'] == '1' ) ? $qredirectpprm[$key]['newwindow'] : '0' ;
							$nf 	= ( isset( $qredirectpprm[$key]['nofollow'] ) && $qredirectpprm[$key]['nofollow'] == '1' ) ? $qredirectpprm[$key]['nofollow'] : '0' ;
							$temps 	= str_replace( '|', '%7C', $key ) . '|' . str_replace( '|', '%7C', $val ) . '|' . $nw . '|' . $nf;
							if($temps!='|||'){
								$newline[] = $temps;
							}
						}
						$newfile 	= implode( "\r\n", $newline );
					}else{
						$newfile 	= $newtext;
					}
					die( $newfile );				
				}
				exit;
			} elseif( isset( $_POST['import-quick-redrects-file'] ) && isset( $_FILES['qredirectppr_file'] ) ) {
				check_admin_referer( 'import-quick-redrects-file' );
				if ( $_FILES['qredirectppr_file']['error'] > 0 ) {
					wp_die( __( 'An error occured during the file upload. Please fix your server configuration and retry.', 'quick-pagepost-redirect-plugin' ) , __( 'SERVER ERROR - Could Not Load', 'quick-pagepost-redirect-plugin' ), array( 'response' => '200', 'back_link' => '1' ) );
					exit;
				} else {
					$config_file = file_get_contents( $_FILES['qredirectppr_file']['tmp_name'] );
					if ( substr($config_file, 0, strlen('QUICKPAGEPOSTREDIRECT')) !== 'QUICKPAGEPOSTREDIRECT' ) {
						if(strpos($config_file,'|') !== false){
							$delim = '|';
						}elseif(strpos($config_file,',') !== false){
							$delim = ',';
						}elseif(strpos($config_file,"\t") !== false){
							$delim = "\t";
						}else{
							$delim = false;
						}
						if($delim != false){
							$config_file = str_replace("\r\n", "\n", $config_file);  
							$config_file = str_replace("\r", "\n", $config_file);
							$text		 = explode( "\n", $config_file );
							$newfile1 	 = array();
							if( is_array( $text ) && !empty( $text ) ){
								foreach( $text as $nl ){
									if( $nl != '' ){
										$elem 	= explode( $delim, $nl );
										if( isset( $elem[0] ) && isset( $elem[1] ) ){
											$newfile1['quickredirectppr_redirects'][esc_url($elem[0])] = esc_url($elem[1]);
											$nw 	= isset($elem[2]) && $elem[2] == '1' ? '1' : '0';
											$nf 	= isset($elem[3]) && $elem[3] == '1' ? '1' : '0';
											$newfile1['quickredirectppr_redirects_meta'][$elem[0]]['newwindow'] = $nw;
											$newfile1['quickredirectppr_redirects_meta'][$elem[0]]['nofollow'] = $nf;
										}
									}
								}
								if(is_array($newfile1) && !empty( $newfile1 )){
									if( isset( $newfile1['quickredirectppr_redirects'] ) ){
										update_option( 'quickredirectppr_redirects', $newfile1['quickredirectppr_redirects'] );
									}
									if( isset( $newfile1['quickredirectppr_redirects_meta'] ) ){
										update_option( 'quickredirectppr_redirects_meta', $newfile1['quickredirectppr_redirects_meta'] );
									}
								}
							}
							$this->qredirectppr_try_to_clear_cache_plugins();
							wp_redirect( admin_url( 'admin.php?page=redirect-import-export&update=4' ), 302 );
							exit;
						}else{
							wp_die( __( 'This does not look like a Quick Page Post Redirect file - it is possibly damaged or corrupt.', 'quick-pagepost-redirect-plugin' ) , __( 'ERROR - Not a valid File', 'quick-pagepost-redirect-plugin' ), array( 'response' => '200', 'back_link' => '1' ) );
							exit;
						}
					} else {
						$config_file = unserialize(base64_decode(substr($config_file, strlen('QUICKPAGEPOSTREDIRECT'))));
						if ( !is_array( $config_file ) ) {
							wp_die( __( 'This does not look like a Quick Page Post Redirect file - it is possibly damaged or corrupt.', 'quick-pagepost-redirect-plugin' ) , __( 'ERROR - Not a valid File', 'quick-pagepost-redirect-plugin' ), array( 'response' => '200', 'back_link' => '1' ) );
							exit;
						} else {
							$newQredirectpprRedirects 	= $config_file['quickredirectppr_redirects'];
							$newQredirectpprMeta 		= $config_file['quickredirectppr_redirects_meta'];
							update_option('quickredirectppr_redirects', $newQredirectpprRedirects);
							update_option('quickredirectppr_redirects_meta', $newQredirectpprMeta);
							$this->qredirectppr_try_to_clear_cache_plugins();
							wp_redirect(admin_url('admin.php?page=redirect-import-export&update=4'),302);
						}
					}
				}
			} elseif( isset($_POST['import_redirects_add_qredirectppr']) && isset($_FILES['qredirectppr_file_add']) ) {
				check_admin_referer( 'import_redirects_add_qredirectppr' );
				if ( $_FILES['qredirectppr_file_add']['error'] > 0 ) {
					wp_die(__( 'An error occured during the file upload. It might me that the file is too large or you do not have the premissions to write to the temporary upload directory. Please fix your server configuration and retry.', 'quick-pagepost-redirect-plugin' ) , __( 'SERVER ERROR - Could Not Load', 'quick-pagepost-redirect-plugin' ), array( 'response' => '200', 'back_link' => '1' ) );
					exit;
				} else {
					$config_file = file_get_contents( $_FILES['qredirectppr_file_add']['tmp_name'] );
					if(strpos($config_file,'|') !== false){
						$delim = '|';
					}elseif(strpos($config_file,',') !== false){
						$delim = ',';
					}elseif(strpos($config_file,"\t") !== false){
						$delim = "\t";
					}else{
						$delim = false;
					}
					if ( strpos( $config_file, $delim ) === false ) {
						wp_die( __( 'This does not look like the file is in the correct format - it is possibly damaged or corrupt.<br/>Be sure the redirects are 1 per line and the redirect and destination are seperated by a PIPE (|), COMMA (,) or a TAB.', 'quick-pagepost-redirect-plugin' ) . '<br/>Example:<br/><br/><code>redirect|destination</code>', __( 'ERROR - Not a valid File', 'quick-pagepost-redirect-plugin' ), array( 'response' => '200', 'back_link' => '1' ) );
						exit;
					} else {
						$tempArr	 = array();
						$tempMArr	 = array();
						$config_file = str_replace("\r\n", "\n", $config_file);  
						$config_file = str_replace("\r", "\n", $config_file);
						$QR_Array 	 = explode( "\n", $config_file );
						$newfile1 	 = array();
						if( !empty( $QR_Array ) && is_array( $QR_Array )):
							foreach( $QR_Array as $qrtoadd ):
								if( $qrtoadd != '' && $delim != false && strpos( $qrtoadd, $delim ) !== false ){
									$elem 	= explode( $delim, str_replace( array( "\r", "\n" ), array( '', '' ), $qrtoadd ) );	
									if( isset( $elem[0] ) && isset( $elem[1] ) ){
										$newfile1['quickredirectppr_redirects'][esc_url($elem[0])] = esc_url($elem[1]);
										$nw 	= isset($elem[2]) && $elem[2] == '1' ? '1' : '0';
										$nf 	= isset($elem[3]) && $elem[3] == '1' ? '1' : '0';
										$newfile1['quickredirectppr_redirects_meta'][$elem[0]]['newwindow'] = $nw;
										$newfile1['quickredirectppr_redirects_meta'][$elem[0]]['nofollow'] = $nf;
									}
								}
							endforeach;	
							if(is_array($newfile1) && !empty( $newfile1 )){
								if( isset( $newfile1['quickredirectppr_redirects'] ) ){
									$currQRs 	= get_option( 'quickredirectppr_redirects', array() );
									$resultQRs 	= array_replace($currQRs, $newfile1['quickredirectppr_redirects']);
									update_option( 'quickredirectppr_redirects', $resultQRs );
								}
								if( isset( $newfile1['quickredirectppr_redirects_meta'] ) ){
									$currQRM 	= get_option( 'quickredirectppr_redirects_meta', array() );
									$resultQRMs = array_replace($currQRM, $newfile1['quickredirectppr_redirects_meta']);
									update_option( 'quickredirectppr_redirects_meta', $resultQRMs );
								}
							}
							$this->qredirectppr_try_to_clear_cache_plugins();
							wp_redirect(admin_url('admin.php?page=redirect-import-export&update=5'),302);
							exit;
						else:
							wp_die( __( 'It does not look like there are any valid items to import - check the file and try again.', 'quick-pagepost-redirect-plugin' ) , __( 'ERROR - No Valid items to add.', 'quick-pagepost-redirect-plugin' ), array( 'response' => '200', 'back_link' => '1' ) );
							exit;
						endif;
					}
				}
			}		return;
		}	return;
	}
		
	function qredirectppr_redirectpprhidemessage_ajax(){
		check_ajax_referer( 'qredirectppr_ajax_verify', 'scid', true );
		$msg = isset($_POST['redirectpprhidemessage']) ? (int)$_POST['redirectpprhidemessage'] : 0;
		if($msg == 1){
			update_option('qredirectppr_jQuery_hide_message','1');
			echo '1';
		}elseif($msg == 2){
			update_option('qredirectppr_jQuery_hide_message2','1');
			echo '1';
		}else{
			echo '0';	
		}
		exit;
	}

	function redirectppr_init_check_version() {
	// checks version of plugin in DB and updates if needed.
		global $wpdb;
		//$this->redirectpprptypes_ok	= get_option( 'redirectppr_qredirectpprptypeok', array() );		
		if( is_array( $this->redirectppr_all_redir_array ) && ! empty( $this->redirectppr_all_redir_array ) )
			$this->redirectppr_all_redir_array = $this->get_main_array();

		if ( version_compare( $this->theredirectpprversion, $this->redirectppr_curr_version, '<' ) && version_compare( $this->redirectppr_curr_version, '5.1.1', '<' )  ){
			$metaMsg 	= get_option( 'redirectppr_meta-message', 'not-set' );
			$metaMsgNew = get_option( 'qredirectppr_meta_addon_content', 'not-set' );
			if( $metaMsgNew == 'not-set' && $metaMsg != 'not-set' ){
				update_option( 'qredirectppr_meta_addon_content', $metaMsg );
				$this->redirectpprmeta_message = $metaMsg; 
			}
			$metaSec 	= get_option( 'redirectppr_meta-seconds', 'not-set' );
			$metaSecNew = get_option( 'qredirectppr_meta_addon_sec', 'not-set');
			if( $metaSecNew == 'not-set' && $metaSec != 'not-set' ){
				update_option( 'qredirectppr_meta_addon_sec', $metaSec );
				$this->redirectpprmeta_seconds	= $metaSec;
			}
			if( $this->theredirectpprversion == '5.0.7' ){
				update_option( 'redirectppr_use-jquery','1'); //default to on
				update_option( 'redirectppr_show-columns','1'); //default to on
			}elseif( $this->theredirectpprversion != '5.1.0' ){
				if ( get_option( 'redirectppr_override-casesensitive' , 'not-set' ) == 'not-set' )
					update_option( 'redirectppr_override-casesensitive', '1' );
				$this->redirectppruse_jquery 	= '0';
				$this->redirectpproverride_casesensitive = '1';
			}
			update_option( 'redirectppr_version', $this->redirectppr_curr_version );
		}elseif( version_compare( $this->theredirectpprversion, $this->redirectppr_curr_version, '<' ) ){
			update_option( 'redirectppr_version', $this->redirectppr_curr_version );
		}
		
		if( $this->theredirectpprmeta != '1' && version_compare( $this->redirectppr_curr_version, '5.0.7', '<' )){
			update_option( 'redirectppr_meta_clean', '1' );
			$wpdb->query("UPDATE $wpdb->postmeta SET `meta_key` = CONCAT('_',`meta_key`) WHERE `meta_key` = 'redirectpprredirect_active' OR `meta_key` = 'redirectpprredirect_rewritelink' OR `meta_key` = 'redirectpprredirect_newwindow' OR `meta_key` = 'redirectpprredirect_relnofollow' OR `meta_key` = 'redirectpprredirect_type' OR `meta_key` = 'redirectpprredirect_url';");
		}
	}

	function redirectppr_filter_plugin_actions($links){
		$links[] 	= '<a href="'.$this->adminlink.'admin.php?page=redirect-options"><span class="dashicons dashicons-admin-settings"></span> ' . __( 'Settings', 'quick-pagepost-redirect-plugin' ) . '</a>';
		return $links;
	}
	
	function redirectppr_filter_plugin_links($links, $file){
		if ( $file == plugin_basename(__FILE__) ){
			$links[] = '<a href="'.$this->adminlink.'admin.php?page=redirect-updates"><span class="dashicons dashicons-external"></span> ' . __( 'Quick Redirects', 'quick-pagepost-redirect-plugin' ) . '</a>';
			$links[] = '<a href="'.$this->adminlink.'admin.php?page=redirect-faqs"><span class="dashicons dashicons-editor-help"></span> ' . __( 'FAQ', 'quick-pagepost-redirect-plugin' ) . '</a>';
			$links[] = '<a target="_blank" href="'.$this->fcmlink.'/donations/"><span class="dashicons dashicons-heart"></span> ' . __( 'Donate', 'quick-pagepost-redirect-plugin' ) . '</a>';
		}
		return $links;
	}
	
	function edit_box_redirectppr_1() {
	// Prints the inner fields for the custom post/page section 
		global $post;
		$redirectppr_option1='';
		$redirectppr_option2='';
		$redirectppr_option3='';
		$redirectppr_option4='';
		$redirectppr_option5='';
		// Use nonce for verification ... ONLY USE ONCE!
		wp_nonce_field( 'redirectpprredirect_noncename', 'redirectpprredirect_noncename', false, true );
		// The actual fields for data entry
		$redirectpprredirecttype = get_post_meta($post->ID, '_redirectpprredirect_type', true) !='' ? get_post_meta($post->ID, '_redirectpprredirect_type', true) : "";
		$redirectpprredirecturl =  get_post_meta($post->ID, '_redirectpprredirect_url', true)!='' ? get_post_meta($post->ID, '_redirectpprredirect_url', true) : "";
		echo '<label for="redirectpprredirect_active" style="padding:2px 0;"><input type="checkbox" name="redirectpprredirect_active" value="1" '. checked('1',get_post_meta($post->ID,'_redirectpprredirect_active',true),0).' />&nbsp;' . __( 'Make Redirect <strong>Active</strong>.', 'quick-pagepost-redirect-plugin' ) . '<span class="qredirectppr_meta_help_wrap"><span class="qredirectppr_meta_help_icon dashicons dashicons-editor-help"></span><span class="qredirectppr_meta_help">' . __( 'Check to turn on or redirect will not work.', 'quick-pagepost-redirect-plugin' ) . '</span></span></label><br />';
		echo '<label for="redirectpprredirect_newwindow" style="padding:2px 0;"><input type="checkbox" name="redirectpprredirect_newwindow" id="redirectpprredirect_newwindow" value="_blank" '. checked('_blank',get_post_meta($post->ID,'_redirectpprredirect_newwindow',true),0).'>&nbsp;' . __( 'Open in a <strong>new window.</strong>', 'quick-pagepost-redirect-plugin' ) . '<span class="qredirectppr_meta_help_wrap"><span class="qredirectppr_meta_help_icon dashicons dashicons-editor-help"></span><span class="qredirectppr_meta_help">' . __( 'To increase effectivness, select "Use jQuery" in the options.', 'quick-pagepost-redirect-plugin' ) . '</span></span></label><br />';
		echo '<label for="redirectpprredirect_relnofollow" style="padding:2px 0;"><input type="checkbox" name="redirectpprredirect_relnofollow" id="redirectpprredirect_relnofollow" value="1" '. checked('1',get_post_meta($post->ID,'_redirectpprredirect_relnofollow',true),0).'>&nbsp;' . __( 'Add <strong>rel="nofollow"</strong> to link.', 'quick-pagepost-redirect-plugin' ) . '<span class="qredirectppr_meta_help_wrap"><span class="qredirectppr_meta_help_icon dashicons dashicons-editor-help"></span><span class="qredirectppr_meta_help">' . __( 'To increase effectivness, select "Use jQuery" in the options.', 'quick-pagepost-redirect-plugin' ) . '</span></span></label><br />';
		echo '<label for="redirectpprredirect_rewritelink" style="padding:2px 0;"><input type="checkbox" name="redirectpprredirect_rewritelink" id="redirectpprredirect_rewritelink" value="1" '. checked('1',get_post_meta($post->ID,'_redirectpprredirect_rewritelink',true),0).'>&nbsp;' . __( '<strong>Show</strong> Redirect URL in link.', 'quick-pagepost-redirect-plugin' ) . ' <span class="qredirectppr_meta_help_wrap"><span class="qredirectppr_meta_help_icon dashicons dashicons-editor-help"></span><span class="qredirectppr_meta_help">' . __( 'To increase effectivness, select "Use jQuery" in the options. This will only change the URL in the link <strong>NOT</strong> the URL in the Address bar.', 'quick-pagepost-redirect-plugin' ) . '</span></span></label><br /><br />';
		//echo '<label for="redirectpprredirect_casesensitive" style="padding:2px 0;"><input type="checkbox" name="redirectpprredirect_casesensitive" id="redirectpprredirect_casesensitive" value="1" '. checked('1',get_post_meta($post->ID,'_redirectpprredirect_casesensitive',true),0).'>&nbsp;Make the Redirect Case Insensitive.</label><br /><br />';
		echo '<label for="redirectpprredirect_url"><b>' . __( 'Redirect / Destination URL:', 'quick-pagepost-redirect-plugin' ) . '</b></label><br />';
		echo '<input type="text" style="width:75%;margin-top:2px;margin-bottom:2px;" name="redirectpprredirect_url" value="'.$redirectpprredirecturl.'" /><span class="qredirectppr_meta_help_wrap"><span class="qredirectppr_meta_help_icon dashicons dashicons-editor-help"></span><span class="qredirectppr_meta_help"><br />' . __( '(i.e., <strong>http://example.com</strong> or <strong>/somepage/</strong> or <strong>p=15</strong> or <strong>155</strong>. Use <b>FULL URL</b> <i>including</i> <strong>http://</strong> for all external <i>and</i> meta redirects.)', 'quick-pagepost-redirect-plugin' ) . '</span></span><br /><br />';
		echo '<label for="redirectpprredirect_type"><b>' . __( 'Type of Redirect:', 'quick-pagepost-redirect-plugin' ) . '</b></label><br />';
		
		switch($redirectpprredirecttype):
			case "":
				$redirectppr_option1=" selected"; //default is 301 (as of 5.1.1)
				break;
			case "301":
				$redirectppr_option1=" selected";
				break;
			case "302":
				$redirectppr_option2=" selected";
				break;
			case "307":
				$redirectppr_option3=" selected";
				break;
			case "meta":
				$redirectppr_option5=" selected";
				break;
		endswitch;
		
		echo '
		<select style="margin-top:2px;margin-bottom:2px;width:40%;" name="redirectpprredirect_type" id="redirectpprredirect_type">
		<option value="301" '.$redirectppr_option1.'>301 ' . __( 'Permanent', 'quick-pagepost-redirect-plugin' ) . '</option>
		<option value="302" '.$redirectppr_option2.'>302 ' . __( 'Temporary', 'quick-pagepost-redirect-plugin' ) . '</option>
		<option value="307" '.$redirectppr_option3.'>307 ' . __( 'Temporary', 'quick-pagepost-redirect-plugin' ) . '</option>
		<option value="meta" '.$redirectppr_option5.'>' . __( 'Meta Redirect', 'quick-pagepost-redirect-plugin' ) . '</option>
		</select><span class="qredirectppr_meta_help_wrap"><span class="qredirectppr_meta_help_icon dashicons dashicons-editor-help"></span><span class="qredirectppr_meta_help">' . __( 'Default is 301 (Permanent Redirect).', 'quick-pagepost-redirect-plugin' ) . ' </span></span><br /><br />
		';
		$metasel = ' meta-not-selected';
		if( $redirectppr_option5 == ' selected' )
			$metasel = ' meta-selected';
			
		echo '<div class="qredirectppr-meta-section-wrapper'.$metasel.'">';
		echo '	<label for="redirectpprredirect_meta_secs" style="padding:2px 0;"><strong>' . __( 'Redirect Seconds (ONLY for meta redirects).', 'quick-pagepost-redirect-plugin' ) . '</strong></label><br /><input type="text" name="redirectpprredirect_meta_secs" id="redirectpprredirect_meta_secs" value="'. (get_post_meta($post->ID,'_redirectpprredirect_meta_secs',true) != '' ? get_post_meta($post->ID,'_redirectpprredirect_meta_secs',true ): '' ).'" size="3"><span class="qredirectppr_meta_help_wrap"><span class="qredirectppr_meta_help_icon dashicons dashicons-editor-help"></span><span class="qredirectppr_meta_help">' . __( 'Leave blank to use options setting. 0 = instant.', 'quick-pagepost-redirect-plugin' ) . ' </span></span><br /><br />';
		echo '</div>';
		echo __( '<strong>NOTE:</strong> For a Page or Post (or Custom Post) Redirect to work, it may need to be published first and then saved again as a Draft. If you do not already have a page/post created you can add a \'Quick\' redirect using the', 'quick-pagepost-redirect-plugin' ) . ' <a href="./admin.php?page=redirect-updates">' . __( 'Quick Redirects', 'quick-pagepost-redirect-plugin' ) . '</a> ' . __( 'method.', 'quick-pagepost-redirect-plugin' );
	}
	
	function isOne_none($val=''){ //true (1) or false =''
		if($val == '_blank'){
			return $val;
		}elseif($val == '1' || $val == 'true' || $val === true ){
			return 1;
		}
		return '';
	}
	
	function redirectppr_save_metadata($post_id, $post) {
		if($post->post_type == 'revision' || ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) )
			return;
		// verify authorization
		if( isset( $_POST['redirectpprredirect_noncename'] ) ){
			if ( !wp_verify_nonce( $_REQUEST['redirectpprredirect_noncename'], 'redirectpprredirect_noncename' ) )
				return $post_id;
		}
		// check allowed to editing
		if ( !current_user_can('edit_posts', $post_id))
			return $post_id;
			
		if(!empty($my_meta_data))
			unset($my_meta_data);
			
		$my_meta_data = array();
		if( isset( $_POST['redirectpprredirect_active'] ) || isset( $_POST['redirectpprredirect_url'] ) || isset( $_POST['redirectpprredirect_type'] ) || isset( $_POST['redirectpprredirect_newwindow'] ) || isset($_POST['redirectpprredirect_relnofollow']) || isset($_POST['redirectpprredirect_meta_secs'])):
			$protocols 		= apply_filters( 'qredirectppr_allowed_protocols', array( 'http', 'https', 'ftp', 'ftps', 'mailto', 'news', 'irc', 'gopher', 'nntp', 'feed', 'telnet', 'mms', 'rtsp', 'svn', 'tel', 'fax', 'xmpp'));
			// find & save the form data & put it into an array
			$my_meta_data['_redirectpprredirect_active'] 		= isset($_REQUEST['redirectpprredirect_active']) 		? sanitize_meta( '_redirectpprredirect_active', $this->isOne_none(intval( $_REQUEST['redirectpprredirect_active'])), 'post' ) : '';
			$my_meta_data['_redirectpprredirect_newwindow'] 	= isset($_REQUEST['redirectpprredirect_newwindow']) 	? sanitize_meta( '_redirectpprredirect_newwindow', $this->isOne_none( $_REQUEST['redirectpprredirect_newwindow']), 'post' ) 	: '';
			$my_meta_data['_redirectpprredirect_relnofollow'] 	= isset($_REQUEST['redirectpprredirect_relnofollow']) 	? sanitize_meta( '_redirectpprredirect_relnofollow', $this->isOne_none(intval( $_REQUEST['redirectpprredirect_relnofollow'])), 'post' ) 	: '';
			$my_meta_data['_redirectpprredirect_type'] 			= isset($_REQUEST['redirectpprredirect_type']) 			? sanitize_meta( '_redirectpprredirect_type', sanitize_text_field( $_REQUEST['redirectpprredirect_type'] ), 'post' )		: '';
			$my_meta_data['_redirectpprredirect_rewritelink'] 	= isset($_REQUEST['redirectpprredirect_rewritelink']) 	? sanitize_meta( '_redirectpprredirect_rewritelink', $this->isOne_none(intval( $_REQUEST['redirectpprredirect_rewritelink'])), 'post' )	: '';
			$my_meta_data['_redirectpprredirect_url']    		= isset($_REQUEST['redirectpprredirect_url']) 			? esc_url_raw( $_REQUEST['redirectpprredirect_url'], $protocols ) : ''; 
			$my_meta_data['_redirectpprredirect_meta_secs']    	= isset($_REQUEST['redirectpprredirect_meta_secs']) &&  $_REQUEST['redirectpprredirect_meta_secs'] != '' ? (int) $_REQUEST['redirectpprredirect_meta_secs'] : ''; 

			$info = $this->appip_parseURI($my_meta_data['_redirectpprredirect_url']);
			//$my_meta_data['_redirectpprredirect_url'] = esc_url_raw($info['url']);
			$my_meta_data['_redirectpprredirect_url'] = $info['url'];

			if($my_meta_data['_redirectpprredirect_url'] == 'http://' || $my_meta_data['_redirectpprredirect_url'] == 'https://' || $my_meta_data['_redirectpprredirect_url'] == ''){
				$my_meta_data['_redirectpprredirect_url'] 		= ''; //reset to nothing
				$my_meta_data['_redirectpprredirect_type'] 		= NULL; //clear Type if no URL is set.
				$my_meta_data['_redirectpprredirect_active'] 	= NULL; //turn it off if no URL is set
				$my_meta_data['_redirectpprredirect_rewritelink'] = NULL;  //turn it off if no URL is set
				$my_meta_data['_redirectpprredirect_newwindow']	= NULL; //turn it off if no URL is set
				$my_meta_data['_redirectpprredirect_relnofollow'] = NULL; //turn it off if no URL is set
			}
			
			// Add values of $my_meta_data as custom fields
			if(count($my_meta_data)>0){
				foreach ($my_meta_data as $key => $value) { 
					$value = implode(',', (array)$value);
					if($value == '' || $value == NULL || $value == ','){ 
						delete_post_meta($post->ID, $key); 
					}else{
						if(get_post_meta($post->ID, $key, true) != '') {
							update_post_meta($post->ID, $key, $value);
						} else { 
							add_post_meta($post->ID, $key, $value);
						}
					}
				}
			}
			$this->qredirectppr_try_to_clear_cache_plugins();
		endif;
	}
	
	function appip_parseURI($url){
		/*
		[scheme]
		[host]
		[user]
		[pass]
		[path]
		[query]
		[fragment]
		*/
		$strip_protocol = 0;
		$tostrip = '';
		if(substr($url,0,2) == 'p=' || substr($url,0,8) == 'page_id='){ 
			// page or post id
			$url = network_site_url().'/?'.$url;
		}elseif(is_numeric($url)){ 
			// page or post id
			$url = network_site_url().'/?'.$url;
		}elseif($url == "/" ){ 
			// root
			$url = network_site_url().'/';
		}elseif(substr($url,0,1) == '/' ){ 
			// relative to root
			$url =  network_site_url().$url;
			$strip_protocol = 1;
			$tostrip = network_site_url(); 
		}elseif(substr($url,0,7) != 'http://' && substr($url,0,8) != 'https://' ){ 
			//no protocol so add it
			//NOTE: desided not to add it automatically - too iffy.
		}
		$info = @parse_url($url);
		if($strip_protocol == 1 && $tostrip != '' ){
			$info['url'] = str_replace($tostrip, '', $url);
		}else{
			$info['url'] = $url;
		}
		return $info;
	}
	
	function redirectppr_fix_targetsandrels($pages) {
		$redirectppr_url 		= array();
		$redirectppr_newindow 	= array();
		$redirectppr_nofollow 	= array();
		
		if (empty($redirectppr_url) && empty($redirectppr_newindow) && empty($redirectppr_nofollow)){
			$thefirstredirectppr = array();
			if(!empty($this->redirectppr_all_redir_array)){
				foreach($this->redirectppr_all_redir_array as $key => $redirectpprd){
					foreach($redirectpprd as $ppkey => $redirectpprs){
						$thefirstredirectppr[$key][$ppkey] = $redirectpprs;
						$thefirstredirectppr[$key]['post_id'] = $key;

					}
				}
			}
			if(!empty($thefirstredirectppr)){
				foreach($thefirstredirectppr as $ppitems){
					if($ppitems['_redirectpprredirect_active'] == 1 && $this->redirectpproverride_newwin =='1'){ 
						// check override of NEW WINDOW
						$redirectppr_newindow[] = $ppitems['post_id'];
					}else{
						if($ppitems['_redirectpprredirect_active'] == 1 && $ppitems['_redirectpprredirect_newwindow'] === '_blank'){
							$redirectppr_newindow[] = $ppitems['post_id'];
						}
					}
					
					if($ppitems['_redirectpprredirect_active']==1 && $this->redirectpproverride_nofollow =='1'){ 
						//check override of NO FOLLOW
						$redirectppr_nofollow[] = $ppitems['post_id'];
					}else{
						if($ppitems['_redirectpprredirect_active']==1 && $ppitems['_redirectpprredirect_relnofollow'] == 1){
							$redirectppr_nofollow[] = $ppitems['post_id'];
						}
					}
					
					if($ppitems['_redirectpprredirect_active']==1 && $this->redirectpproverride_rewrite =='1'){ 
						//check override of REWRITE
						if($this->redirectpproverride_URL!=''){
							$redirectppr_url_rewrite[] = $ppitems['post_id'];
							$redirectppr_url[$ppitems['post_id']]['URL'] = $this->redirectpproverride_URL; //check override of URL
						}elseif($ppitems['_redirectpprredirect_url']!=''){
							$redirectppr_url_rewrite[] = $ppitems['post_id'];
							$redirectppr_url[$ppitems['post_id']]['URL'] = $ppitems['_redirectpprredirect_url'];
						}
					}else{
						if($ppitems['_redirectpprredirect_active']==1 && $ppitems['_redirectpprredirect_rewritelink'] == '1' && $ppitems['_redirectpprredirect_url']!=''){
							$redirectppr_url_rewrite[] = $ppitems['post_id'];
							$redirectppr_url[$ppitems['post_id']]['URL'] = $ppitems['_redirectpprredirect_url'];
						}
					}
				}
			}
			if (count($redirectppr_newindow)<0 && count($redirectppr_nofollow)<0){
				return $pages;
			}
		}
		
		//$this_url = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
		if(count($redirectppr_nofollow)>=1) {
			foreach($redirectppr_nofollow as $relid){
			$validexp="@\<li(?:.*?)".$relid."(?:.*?)\>\<a(?:.*?)rel\=\"nofollow\"(?:.*?)\>@i";
			$found = preg_match_all($validexp, $pages, $matches);
				if($found!=0){
					$pages = $pages; //do nothing 'cause it is already a rel=nofollow.
				}else{
					$pages = preg_replace('@<li(.*?)-'.$relid.'(.*?)\>\<a(.*?)\>@i', '<li\1-'.$relid.'\2><a\3 rel="nofollow">', $pages);
				}
			}
		}
		
		if(count($redirectppr_newindow)>=1) {
			foreach($redirectppr_newindow as $p){
				$validexp="@\<li(?:.*?)".$p."(?:.*?)\>\<a(?:.*?)target\=(?:.*?)\>@i";
				$found = preg_match_all($validexp, $pages, $matches);
				if($found!=0){
					$pages = $pages; //do nothing 'cause it is already a target=_blank.
				}else{
					$pages = preg_replace('@<li(.*?)-'.$p.'(.*?)\>\<a(.*?)\>@i', '<li\1-'.$p.'\2><a\3 target="_blank">', $pages);
				}
			}
		}
		return $pages;
	}
	
	function redirect_post_type(){
		return;
		//not needed at this time
	}
	
	function getAddress($home = ''){
	// utility function to get the full address of the current request - credit: http://www.phpro.org/examples/Get-Full-URL.html
		if( !isset( $_SERVER['HTTPS'] ) ){
			$_SERVER['HTTPS'] = '';
		}
		$protocol = $_SERVER['HTTPS'] !== '' && strpos( $home, 'http:' ) === false ? 'https' : 'http'; //check for https
		return $protocol.'://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']; //return the full address
	}
	
	function getQAddress($home = ''){
	// utility function to get the protocol and host of the current request
		if( !isset( $_SERVER['HTTPS'] ) )
			$_SERVER['HTTPS'] = '';
		$protocol = $_SERVER['HTTPS'] !== '' && strpos( $home, 'http:' ) === false ? 'https' : 'http'; //check for https
		return $protocol.'://'.$_SERVER['HTTP_HOST']; 
	}
	
	function redirectppr_new_nav_menu_fix($redirectppr){
		$newmenu = array();
		if(!empty($redirectppr)){
			foreach($redirectppr as $ppd){
				if(isset($this->redirectppr_all_redir_array[$ppd->object_id])){
					$theIsActives 	= $this->redirectppr_all_redir_array[$ppd->object_id]['_redirectpprredirect_active'];
					$theNewWindow 	= $this->redirectppr_all_redir_array[$ppd->object_id]['_redirectpprredirect_newwindow'];
					$theNoFollow 	= $this->redirectppr_all_redir_array[$ppd->object_id]['_redirectpprredirect_relnofollow'];
					$theRewrite 	= $this->redirectppr_all_redir_array[$ppd->object_id]['_redirectpprredirect_rewritelink'];
					$theRedURL	 	= $this->redirectppr_all_redir_array[$ppd->object_id]['_redirectpprredirect_url'];
					if($this->redirectpproverride_URL !=''){$theRedURL = $this->redirectpproverride_URL;} // check override
					if($theIsActives == '1' && $theNewWindow === '_blank'){
						$ppd->target = '_blank';
						$ppd->classes[] = 'redirectppr-new-window';
					}
					if($theIsActives == '1' && $theNoFollow == '1'){
						$ppd->xfn = 'nofollow';
						$ppd->classes[] = 'redirectppr-nofollow';
					}
					if($theIsActives == '1' && $theRewrite == '1' && $theRedURL != ''){
						$ppd->url = $theRedURL;
						$ppd->classes[] = 'redirectppr-rewrite';
					}
				}
				$newmenu[] = $ppd;
			}
		}
		return $newmenu;
	}
	
	function redirect(){
		//bypass for testing.
		if(isset($_GET['action']) && $_GET['action'] == 'no-redirect' )
			return;
		// Quick Redirects Redirect.
		// Read the list of redirects and if the current page is found in the list, send the visitor on her way
		if (!empty($this->quickredirectppr_redirects) && !is_admin()) {
			$homeURL 		= get_option( 'home' );
			$getAddress		= $this->getAddress( $homeURL ); // gets just the protocol and full URL of request. for cases when the setting for Site URL has a subfolder but a request may not.
			$getQAddress	= $this->getQAddress( $homeURL ); // gets just the protocol and domain (host) of the request.
			
			//get the query string if there is one so that it can be preserved
				// patch submitted for version 5.0.7 by Romulo De Lazzari <romulodelazzari@gmail.com> - THANKS!
				$finalQS = (filter_input(INPUT_SERVER, 'QUERY_STRING'));
				if ($finalQS === null || $finalQS === false || $finalQS === '') {
					$finalQS = '';
				} else {
					$finalQS = '?' . $finalQS;
				}
				$userrequest = str_replace( $homeURL, '', $getAddress );
				$userrequest = preg_replace('/\?.*/', '', $userrequest);
				// end patch				
			//end QS preservation

			$needle 		= $this->redirectpproverride_casesensitive ? $userrequest : strtolower($userrequest);
			$haystack 		= $this->redirectpproverride_casesensitive ? $this->quickredirectppr_redirects : array_change_key_case($this->quickredirectppr_redirects);
			$getAddrNeedle 	= $this->redirectpproverride_casesensitive ? $getAddress : strtolower($getAddress);
			$getQAddrNeedle = $this->redirectpproverride_casesensitive ? str_replace( $getQAddress, '', $getAddrNeedle ) : strtolower(str_replace( $getQAddress, '', $getAddrNeedle ));
			$finalQS 		= str_replace( '&amp;','&', $finalQS);
			$finalQS		= $this->redirectpproverride_casesensitive ? $finalQS : strtolower( $finalQS ); //added 5.1.4 to fix URL needle being converted to lower, but not Query (as it never matches unless user enters lower)
			$finalQS 		= apply_filters( 'appip_filter_testing_finalQS', $finalQS, $needle, $haystack); // added 5.1.4 to allow filtering of QS data prior to matching.
			$index 			= false;
			
			/* These are the URL matching checks to see if the request should be redirected.
			 * They trickle down to the less likely scenarios last - tries to recover a redirect if the 
			 * user just forgot things like ending slash or used wrong protocol, etc.
			 */

			if( array_key_exists( ($needle . $finalQS), $haystack ) ){ 
				//check if QS data might be part of the redirect URL and not supposed to be added back.
				$index = $needle . $finalQS;
				$finalQS = ''; //remove it
			}elseif( array_key_exists( urldecode($needle . $finalQS), $haystack ) ){ 
				//check if QS data might be part of the encoded redirect URL and not supposed to be added back.
				$index = $needle . $finalQS;
				$finalQS = ''; //remove it
			}elseif(array_key_exists( $needle, $haystack)){
				//standard straight forward check for needle (request URL)
				$index = $needle;
			}elseif(array_key_exists(urldecode($needle), $haystack)){
				//standard straight forward check for URL encoded needle (request URL)
				$index = urldecode($needle);
			}elseif(array_key_exists( $getAddrNeedle, $haystack)){
				//Checks of the needle (request URL) might be using a different protocol than site home URL
				$index = $getAddrNeedle;
			}elseif(array_key_exists( urldecode( $getAddrNeedle ), $haystack)){
				//Checks of an encoded needle (request URL) might be using a different protocol than site home URL
				$index =  urldecode( $getAddrNeedle );
			}elseif( strpos( $needle, 'https' ) !== false ){
				//Checks of the encoded needle (request URL) might be http but the redirect is set up as http
				if(array_key_exists(str_replace('https','http',$needle), $haystack))
					$index = str_replace('https','http',$needle); //unencoded version
				elseif(array_key_exists(str_replace('https','http',urldecode($needle)), $haystack))
					$index = str_replace('https','http',urldecode($needle)); //encoded version
			}elseif(strpos($needle,'/') === false) {
				//Checks of the needle (request URL) might not have beginning and ending / but the redirect is set up with them
				if( array_key_exists( '/' . $needle . '/', $haystack ) )
					$index = '/'.$needle.'/';
			}elseif( array_key_exists( urldecode($getQAddrNeedle), $haystack ) ){
				//Checks if encoded needle (request URL) doesn't contain a sub directory in the URL, but the site Root is set to include it.
				$index = urldecode( $getQAddrNeedle );
			}elseif( array_key_exists( $getQAddrNeedle, $haystack ) ){
				//Checks if needle (request URL) doesn't contain a sub directory in the URL, but the site Root is set to include it.
				$index = $getQAddrNeedle;
			}elseif( array_key_exists( $needle . '/', $haystack ) ){
				//checks if needle (request URL) just is missing the ending / but the redirect is set up with it.
				$index = $needle . '/';
			}
			$index = apply_filters('qredirectppr_filter_quickredirect_index', $index, $finalQS);
			
			if($index != false && $index != ''){
				// Finally, if we have a matched request URL, get ready to redirect.
				$val = isset($haystack[$index]) ? $haystack[$index] : false;				
				if($val) {
					// if global setting to make all redirects go to a specific URL is set, that takes priority.
					$useURL 	 = $this->redirectpproverride_URL != '' ? $this->redirectpproverride_URL : $val;
					$useURL 	.= apply_filters( 'qredirectppr_filter_quickredirect_append_QS_data', $finalQS ); //add QS back or use filter to set to blank.
					$useURL 	 = apply_filters( 'qredirectppr_filter_quickredirect_url', $useURL, $index ); // final URL filter
					
					$qredirectpprRedType = apply_filters( 'qredirectppr_filter_quickredirect_type', 301 ) ; // filter for redirect type (301 is default here).
					$qredirectpprMetaSec = apply_filters( 'qredirectppr_filter_quickredirect_secs', $this->redirectpprmeta_seconds ) ; // filter for redirect seconds if type is changed to meta).
					if( strpos( $useURL, '/' ) !== false && strpos( $useURL, '/' ) === 0 ){
						// $addback refers to adding back the site home link back to the front of the request URL that is relative to the root. 
						// by default it will, but this can be filtered to never add it back (or based on URL).
						$addback 	= (bool) apply_filters( 'qredirectppr_filter_quickredirect_add_home_link_to_destination_url', true, $useURL);
						$useURL = $addback ? $homeURL . $useURL : $useURL;
					}
					// action to allow take over.
					do_action( 'qredirectppr_redirect', $useURL, $qredirectpprRedType );
					
					if( $useURL != '' ){
						// and now the redirect (meta or type set).
						if( $qredirectpprRedType == 'meta' ){
							$this->redirectppr_metaurl = $useURL;
							$this->redirectppr_addmetatohead_theme();
						}else{
							header('RedirectType: Quick Page Post Redirect - Quick');
							wp_redirect( $useURL, $qredirectpprRedType );
							exit();
						}
					}
				}	
			}
		}
	}
	
	function redirectppr_do_redirect( $var1='var1', $var2 = 'var2'){
		//bypass for testing.
		if(isset($_GET['action']) && $_GET['action'] == 'no-redirect' )
			return;
		// Individual Redirects Redirect.
		// Read the list of redirects and if the current page is found in the list, send the visitor on her way
		
		global $post;
		if ( count( $this->redirectppr_all_redir_array ) > 0 && ( is_single() || is_singular() || is_page() ) ) {
			if( isset( $this->redirectppr_all_redir_array[$post->ID] ) ){
				$isactive = $this->redirectppr_all_redir_array[$post->ID]['_redirectpprredirect_active'];
				$redrtype = $this->redirectppr_all_redir_array[$post->ID]['_redirectpprredirect_type'];
				$redrurl  = $this->redirectppr_all_redir_array[$post->ID]['_redirectpprredirect_url'];
				$metasecs = $this->redirectppr_all_redir_array[$post->ID]['_redirectpprredirect_meta_secs'];
				if($isactive == 1 && $redrurl != '' && $redrurl != 'http://www.example.com'){
					if($redrtype === 0){$redrtype = '200';}
					if($redrtype === ''){$redrtype = '302';}
					if( strpos($redrurl, 'http://')=== 0 || strpos($redrurl, 'https://')=== 0){
						$urlsite	= $redrurl;
					}elseif(strpos($redrurl, 'www')=== 0){ //check if they have full url but did not put http://
						$urlsite 	= 'http://'.$redrurl;
					}elseif(is_numeric($redrurl)){ // page/post number
						$urlsite	= $this->homelink.'/?p='.$redrurl;
					}elseif(strpos($redrurl,'/') === 0){ // relative to root	
						$urlsite 	= $this->homelink.$redrurl;
					}else{	// we assume they are using the permalink / page name??
						$urlsite=$this->homelink.'/'.$redrurl;
					}
					// check if override is set for all redirects to go to one URL
					if($this->redirectpproverride_URL !=''){$urlsite=$this->redirectpproverride_URL;} 
					if($this->redirectpproverride_type!='0' && $this->redirectpproverride_type!=''){$redrtype = $this->redirectpproverride_type;} //override check
					if($redrtype == 'meta'){
						$this->redirectppr_metaurl = $redrurl;
						$post_meta_secs = get_post_meta( $post->ID, '_redirectpprredirect_meta_secs', true);
						$this->redirectppr_addmetatohead_theme();
						//$this->add_extra_meta_features( $redrurl, $metasecs, 'individual', $post );
					}else{
						header('RedirectType: Quick Page Post Redirect - Individual');
						do_action('qredirectppr_do_redirect',$urlsite,$this->redirectpproverride_type);
						wp_redirect($urlsite,$redrtype);
						exit();
					}
				}
			}
		}
	}

	function redirectppr_addmetatohead_theme(){
		$themsgmeta = '';
		$themsgmsg 	= '';
		$hook_name 	= 'redirectppr_meta_head_hook';
		// check URL override
	    if($this->redirectpproverride_URL !=''){
			$urlsite = $this->redirectpproverride_URL;
		} else {
			$urlsite = $this->redirectppr_metaurl;
		}
	    $this->redirectpproverride_URL = ''; //reset
	    if($this->redirectpprmeta_seconds==''){
			$this->redirectpprmeta_seconds='0';
		}
		$themsgmeta =  '<meta http-equiv="refresh" content="'.$this->redirectpprmeta_seconds.'; URL='.$urlsite.'" />'."\n";
		if($this->redirectpprmeta_message!='' && $this->redirectpprmeta_seconds!='0'){
			$themsgmsg =  '<div style="margin-top:20px;text-align:center;">'.$this->redirectpprmeta_message.'</div>'."\n";
		}
		if( has_action($hook_name)){
			do_action( $hook_name,$urlsite,$this->redirectpprmeta_seconds,$this->redirectpprmeta_message);
			return;
		}elseif( has_filter($hook_name.'_filter')){
			$themsgmeta = apply_filters($hook_name, $themsgmeta,$themsgmsg);
			echo $themsgmeta;
			return;
		}else{
			echo $themsgmeta;
			echo $themsgmsg;
			exit;
		}
	}

	function override_redirectppr_metahead( $refresh_url = '', $refresh_secs = 0, $messages = ''){
		global $post;
		global $is_IE;
		$messages 	= '';
		$outHTML   	= array();
		$psecs 		= '';
		$ptrigger	= '';
		$pload		= '';
		$pcontent	= '';
		$appMsgTo	= 'body';
		if( is_object( $post ) && !empty( $post )){
			$psecs 		= get_post_meta($post->ID, '_redirectpprredirect_meta_secs', true);	
			$ptrigger	= get_post_meta($post->ID, 'qredirectppr_meta_trigger', true) != '' ? get_post_meta($post->ID, 'qredirectppr_meta_trigger', true) : '';
			$pload		= (bool) get_post_meta($post->ID, 'qredirectppr_meta_load', true) === true ? '1' : '';
			$pcontent	= get_post_meta($post->ID, 'qredirectppr_meta_content', true) != '' ? get_post_meta($post->ID, 'qredirectppr_meta_content', true) : '';
			$appMsgTo	= get_post_meta($post->ID, 'qredirectppr_meta_append', true) != '' ? get_post_meta($post->ID, 'qredirectppr_meta_append', true) : '';
		}
		$secs			= $psecs != '' ? $psecs : get_option( 'qredirectppr_meta_addon_sec', $refresh_secs );
		$class			= $ptrigger != '' ? $ptrigger : get_option( 'qredirectppr_meta_addon_trigger', 'body' );
		$load			= $pload != '' ? true : ( get_option( 'qredirectppr_meta_addon_load', '' ) != '' ? true : false);
		$content		= $pcontent != '' ? $pcontent : get_option( 'qredirectppr_meta_addon_content', $this->redirectpprmeta_message );
		$timer 			= (int) $secs * 100;
		$appendTo		= $appMsgTo != '' ? $appMsgTo : get_option( 'qredirectppr_meta_append_to', 'body' );
		$injectMsg		= $content != '' ? '<div id="redirectppr_custom_message">'.$content.'</div>' : '';
		$bfamily 		= qredirectppr_get_browser_family();
		if( !$load ) {
			//wp_enqueue_script( 'qredirectppr-meta-redirect-no-load', plugins_url( '/js/qredirectppr_meta_redirect.js', __FILE__ ), array( 'jquery' ), $this->redirectppr_curr_version, false );
			wp_enqueue_script( 'qredirectppr-meta-redirect-no-load', plugins_url( '/js/qredirectppr_meta_redirect.min.js', __FILE__ ), array( 'jquery' ), $this->redirectppr_curr_version, false );
			wp_localize_script( 'qredirectppr-meta-redirect-no-load', 'qredirectpprMetaData', array( 'browserFamily' => $bfamily,'appendTo' => $appendTo, 'class' => $class, 'secs' => $secs, 'refreshURL' => $refresh_url , 'injectMsg' => $injectMsg ) );
			echo '<!DOCTYPE html>'."\n";
			echo '<html>'."\n";
			echo '<head>'."\n";
			global $wp_scripts;
			$allowScripts = array('jquery','qredirectppr-meta-redirect-no-load');
			$jqnew = isset( $wp_scripts->queue ) ? $wp_scripts->queue : array() ;
			if( is_array($jqnew) && !empty($jqnew)){
				foreach( $jqnew as $key => $val ){
					if( !in_array( $val, $allowScripts ) ){
						unset($wp_scripts->queue[$key]);
					}
				}
			}
			wp_print_scripts();
			echo '</head>'."\n";
			echo '<body>'."\n";
			echo '</body>'."\n";
			echo '</html>';
			exit;
		}else{
			//wp_enqueue_script( 'qredirectppr-meta-redirect-load', plugins_url( '/js/qredirectppr_meta_redirect.js', __FILE__ ), array( 'jquery' ), $this->redirectppr_curr_version, false );
			wp_enqueue_script( 'qredirectppr-meta-redirect-load', plugins_url( '/js/qredirectppr_meta_redirect.min.js', __FILE__ ), array( 'jquery' ), $this->redirectppr_curr_version, false );
			wp_localize_script( 'qredirectppr-meta-redirect-load', 'qredirectpprMetaData', array('browserFamily' => $bfamily, 'appendTo' => $appendTo, 'class' => $class, 'secs' => $secs, 'refreshURL' => $refresh_url , 'injectMsg' => $injectMsg ) );
		}
		return;
	}

	function qredirectppr_meta_addon_page(){
	?>
	<div class="wrap" style="position:relative;">
		<h2><?php echo __( 'Meta Redirect Settings', 'quick-pagepost-redirect-plugin' );?></h2>
		<?php if ( ! empty( $_GET['settings-updated'] ) ) : ?><div id="message" class="updated notice is-dismissible"><p><?php echo __( 'Settings Updated', 'quick-pagepost-redirect-plugin' );?></p></div><?php endif; ?>
		<p><?php echo __( 'This section is for updating options for redirects that use the "meta refresh" funcitonality for redirecting.', 'quick-pagepost-redirect-plugin' );?></p>
		<p><?php echo __( 'Using the setting below, you can add elements or a message to the page that is loaded before tht redirect, or just allow the page to load as normal until the redirect reaches the number of seconds you have set below.', 'quick-pagepost-redirect-plugin' );?></p>
		<form method="post" action="options.php" class="qredirectpprform">
			<?php settings_fields( 'qredirectppr-meta-settings-group' ); ?>
			<table class="form-table">
				<tr>
					<th scope="row"><label id="qredirectppr-meta-options"><?php echo __( 'Load Page Content?', 'quick-pagepost-redirect-plugin' );?></label></th>
					<td><input type="checkbox" name="qredirectppr_meta_addon_load" value="1" <?php echo ( ( get_option( 'qredirectppr_meta_addon_load', '' ) != '' ) ? ' checked="checked"' : '' ); ?> /><span><?php echo __( 'Check if you want the normal page to load before redirect happens (if redirect is 0 seconds, it may not load fully).', 'quick-pagepost-redirect-plugin' );?></span></td>
				</tr>
				<tr>
					<th scope="row"><label><?php echo __( 'Redirect Seconds', 'quick-pagepost-redirect-plugin' );?>:</label></th>
					<td><input type="text" size="5" name="qredirectppr_meta_addon_sec" value="<?php echo get_option('qredirectppr_meta_addon_sec', '0'); ?>"/><span><code>0</code> = <?php echo __( 'instant', 'quick-pagepost-redirect-plugin' );?>*. <code>10</code> <?php echo __( 'would redirect 10 seconds after the required element is loaded (i.e., body or an element with a specific class). *Intsant will still have a \'slight\' delay, as some content needs to load before the redirect occurs. Settings on individual pages will override this setting.', 'quick-pagepost-redirect-plugin' );?></span></td>
				</tr>
				<tr>
					<th scope="row"><label><?php echo __( 'Redirect Trigger', 'quick-pagepost-redirect-plugin' );?>:</label></th>
					<td><input type="text" size="25" id="qredirectppr_meta_addon_trigger" name="qredirectppr_meta_addon_trigger" value="<?php echo get_option('qredirectppr_meta_addon_trigger', 'body'); ?>"/><span><?php printf( __( 'The %1$s, %2$s or tag name of the element you want to load before triggering redirect. Use a %3$s in the class name or %4$s for the ID. <strong><em>For example:</em></strong> if you want it to redirect when the body tag loads, you would type %5$s above. To redirect after an element with a class or ID, use %6$s or %7$s.', 'quick-pagepost-redirect-plugin' ), '<code>class</code>', '<code>ID</code>', '<code>.</code>', '<code>#</code>', '<code>body</code>', '<code>.some-class</code>', '<code>#some-id</code>' );?></span></td>
				</tr>
				<tr>
					<th scope="row"><label><?php echo __( 'Append Content To', 'quick-pagepost-redirect-plugin' );?>:</label></th>
					<td><input type="text" size="25" id="qredirectppr_meta_append_to" name="qredirectppr_meta_append_to" value="<?php echo get_option('qredirectppr_meta_append_to', 'body'); ?>"/><span><?php printf( __( 'The %1$s, %2$s or tag name of the element you want the content to load into when the page loads.', 'quick-pagepost-redirect-plugin' ), '<code>class</code>', '<code>ID</code>' );?></span></td>
				</tr>
				<tr>
					<th scope="row"><label><?php echo __( 'Page Content', 'quick-pagepost-redirect-plugin' );?>:</label></th>
					<td><span><?php printf( __( 'Be sure to include a tag with your class or ID or tag name (entered above) so the redirect triggers - if you do not, the redirect will not happen. If you check the box to "Load Page Content", this data will be inserted into the page right after the %1$s tag. Otherwise, it will be the only content shown.', 'quick-pagepost-redirect-plugin' ), '&lt;body&gt;');?><br /><strong><br /><?php echo __( 'Add your content below', 'quick-pagepost-redirect-plugin' );?></strong>.</span>
						<textarea id="qredirectppr_meta_addon_content" name="qredirectppr_meta_addon_content"><?php echo get_option('qredirectppr_meta_addon_content', ''); ?></textarea>
						<br /><span><?php echo __( 'To use a counter, add the following:', 'quick-pagepost-redirect-plugin' );?>
						<pre>&lt;div id="qredirectppr_meta_counter" data-meta-counter-text="This page will redirect in %1$ seconds."&gt;&lt;/div&gt;</pre>
						<?php echo __( 'The "%1$" will be replaced with the actual seconds.', 'quick-pagepost-redirect-plugin' );?>
						</span>
					</td>
				</tr>
			</table>
			<p class="submit"><input type="submit" class="button-primary" value="<?php echo __( 'Save Changes', 'quick-pagepost-redirect-plugin' );?>" /></p>
		</form>
	</div>	
	<?php
	}
	
	function qredirectppr_meta_plugin_has_addon() {
		if ( ( defined('DOING_AJAX') && DOING_AJAX ) || ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) )
			return;
		if ( is_admin() && is_plugin_active( 'qredirectppr-meta-redirect-add-on/qredirectppr-meta-redirect-add-on.php' ) ) {
			add_action( 'admin_notices', array( $this, 'qredirectppr_meta_addon_admin_notice' ) );
			deactivate_plugins( 'qredirectppr-meta-redirect-add-on/qredirectppr-meta-redirect-add-on.php' ); 
		}
	}

	function qredirectppr_meta_addon_admin_notice() {
		echo '
		<div class="update-nag">
			' . __( 'You have the Addon Plugin', 'quick-pagepost-redirect-plugin' ) . ' <strong>"Qredirectppr - Meta Redirect Add On"</strong> ' . __( 'activated. This plugin\'s functionality is now built into the parent', 'quick-pagepost-redirect-plugin' ) . ' <strong>"Quick Page/Post Redirect Plugin"</strong> ' . __( 'so you no longer need to have the addon plugin installed.', 'quick-pagepost-redirect-plugin' ) . '
			<br /><br />' . __( 'The plugin will be deactivated now to prevent conflicts. You may delete it if you desire.', 'quick-pagepost-redirect-plugin' ) . '
		</div>';
	}
}
//=======================================
// END Main Redirect Class.
//=======================================
function start_redirectppr_class(){
	global $newqredirectppr, $redirect_plugin;
	$redirect_plugin = $newqredirectppr = new quadeepak(); // call our class
}

/**
* qredirectppr_create_individual_redirect - helper function to create Individual Redirect programatically.
* @param array $atts default settings for array.
*		post_id int|string the post id
*		active int 1 or 0
*		url	string redirect URL
*		type string 301, 302, 307 or meta
*		newwindow int 1 or 0
*		nofollow int 1 or 0
*		rewrite int 1 or 0
* @return bool true on success 
* @example:
* *****************
	$atts = array(
		'post_id' 	=> $post->ID,
		'url' 		=> 'http://example.com/',
		'active' 	=> 0, 
		'type' 		=> '301',
		'newwindow'	=> 1,
		'nofollow'	=> 0,
		'rewrite'	=> 0
	);
	qredirectppr_create_individual_redirect( $atts );
* *****************
*/
function qredirectppr_create_individual_redirect( $atts = array() ){
	if( !is_array( $atts ) )
		return false;
	$defaults = array( 
		'post_id' 	=> '0', 
		'active' 	=> 1, 
		'url'		=> '',
		'type' 		=> '301',
		'newwindow'	=> 0,
		'nofollow'	=> 0,
		'rewrite'	=> 0
	);
	extract( shortcode_atts($defaults, $atts) );
	if( $post_id == '0' || $url == '' )
		return false;
	// some validation
	$type 		= !in_array( $type, array( '301', '302', '307', 'meta' ) ) ? '301' : $type;
	$active 	= (int) $active == 1 ? 1 : 0;
	$newwindow 	= (int) $newwindow == 1 ? 1 : 0;
	$nofollow 	= (int) $nofollow == 1 ? 1 : 0;
	$rewrite 	= (int) $rewrite == 1 ? 1 : 0;
	// set required meta 
	add_post_meta( $post_id, '_redirectpprredirect_url', $url );
	add_post_meta( $post_id, '_redirectpprredirect_type', $type );
	add_post_meta( $post_id, '_redirectpprredirect_active', $active );
	//set optional meta
	if( $rewrite == 1 )
		add_post_meta( $post_id, '_redirectpprredirect_rewritelink', 1 );
	if( $newwindow == 1 )
		add_post_meta( $post_id, '_redirectpprredirect_newwindow', '_blank' );
	if( $nofollow == 1 )
		add_post_meta( $post_id, '_redirectpprredirect_relnofollow', 1 );	
	return true;
}
/**
* qredirectppr_delete_individual_redirect - helper function to delete Individual Redirect programatically.
* @param post_id int|string the post id
* @return bool true on success 
* @example:
* *****************
	qredirectppr_delete_individual_redirect( $post_id );
* *****************
*/
function qredirectppr_delete_individual_redirect( $post_id = 0){
	$post_id = (int) $post_id;
	if( $post_id == 0 )
		return false;
	$ptype = get_post_type( $post_id );
	if( $ptype != 'post' )
		$ok = current_user_can( 'edit_pages' );
	else
		$ok = current_user_can( 'edit_posts' );
		
	if( $ok ){ 
		// delete meta fields
		delete_post_meta( $post_id, '_redirectpprredirect_url' );
		delete_post_meta( $post_id, '_redirectpprredirect_type');
		delete_post_meta( $post_id, '_redirectpprredirect_active' );
		delete_post_meta( $post_id, '_redirectpprredirect_rewritelink' );
		delete_post_meta( $post_id, '_redirectpprredirect_newwindow' );
		delete_post_meta( $post_id, '_redirectpprredirect_relnofollow' );	
		return true;
	}else{
		return false;
	}
}

/**
* qredirectppr_create_quick_redirect - helper function to create Quick Redirect programatically.
* @param array $atts default settings for array.
*		request_url string redirect URL
*		destination_url	string redirect URL
*		newwindow int 1 or 0
*		nofollow int 1 or 0
* @return bool true on success 
* @example:
* *****************
	$atts = array(
		'request_url'		=> '/some-url/',
		'destination_url'	=> '/new-url/',
		'newwindow'			=> 1,
		'nofollow'			=> 0,
	);
	qredirectppr_create_quick_redirect( $atts );
* *****************
*/
function qredirectppr_create_quick_redirect( $atts = array() ){
	if( !is_array( $atts ) )
		return false;
	$defaults = array( 
		'request_url'		=> '',
		'destination_url'	=> '',
		'newwindow'			=> 0,
		'nofollow'			=> 0,
	);
	extract( shortcode_atts($defaults, $atts) );
	if( $request_url == '' || $destination_url == '' )
		return false;
	
	global $newqredirectppr, $redirect_plugin;
	$currRedirects 	= get_option( 'quickredirectppr_redirects', array() );
	$currMeta 		= get_option( 'quickredirectppr_redirects_meta', array() );
	$protocols 		= apply_filters( 'qredirectppr_allowed_protocols', array( 'http', 'https', 'ftp', 'ftps', 'mailto', 'news', 'irc', 'gopher', 'nntp', 'feed', 'telnet', 'mms', 'rtsp', 'svn', 'tel', 'fax', 'xmpp'));
	$request_url	= esc_url( str_replace( ' ', '%20', trim( $request_url ) ), null, 'appip' );
	$destination_url= esc_url( str_replace( ' ', '%20', trim( $destination_url ) ), null, 'appip' );
	$newwindow 		= (int) $newwindow == 1 ? 1 : 0;
	$nofollow 		= (int) $nofollow == 1 ? 1 : 0;
	if( strpos( $request_url, '/', 0 ) !== 0 && !$redirect_plugin->qredirectppr_strposa( $request_url, $protocols ) )
		$request_url = '/' . $request_url; // adds root marker to front if not there
	if( ( strpos( $request_url, '.' ) === false && strpos( $request_url, '?' ) === false ) && strpos( $request_url, '/', strlen( $request_url ) -1 ) === false )
		$request_url = $request_url . '/'; // adds end folder marker if not a file end
	if( ( $request_url == '' || $request_url == '/' ) && $destination_url == '')
		return false; //if nothing there do nothing
	elseif ( $request_url != '' && $request_url != '/' && $destination_url == '' )
		$currRedirects[$request_url] = '/';
	else
		$currRedirects[$request_url] = $destination_url;

	$currMeta[$request_url]['newwindow'] = $newwin;
	$currMeta[$request_url]['nofollow']  = $nofoll;
	update_option( 'quickredirectppr_redirects', sanitize_option( 'quickredirectppr_redirects', $currRedirects ) );
	update_option( 'quickredirectppr_redirects_meta', sanitize_option( 'quickredirectppr_redirects_meta', $currMeta ) );
	$redirect_plugin->quickredirectppr_redirectsmeta	= get_option( 'quickredirectppr_redirects_meta', array() );
	$redirect_plugin->quickredirectppr_redirects 		= get_option( 'quickredirectppr_redirects', array() );
	return true;
}
/**
* qredirectppr_delete_quick_redirect - helper function to delete Quick Redirect programatically.
* @param request_url string redirect URL
* @return bool true on success 
* @example:
* *****************
	qredirectppr_delete_quick_redirect( '/some-url/' );
* *****************
*/
function qredirectppr_delete_quick_redirect( $request_url = '' ){
	if( $request_url == '' )
		return false;
	global $newqredirectppr, $redirect_plugin;
	$currRedirects 	= get_option( 'quickredirectppr_redirects', array() );
	$currMeta 		= get_option( 'quickredirectppr_redirects_meta', array() );
	$protocols 		= apply_filters( 'qredirectppr_allowed_protocols', array( 'http', 'https', 'ftp', 'ftps', 'mailto', 'news', 'irc', 'gopher', 'nntp', 'feed', 'telnet', 'mms', 'rtsp', 'svn', 'tel', 'fax', 'xmpp'));
	$request_url	= esc_url( str_replace( ' ', '%20', trim( $request_url ) ), null, 'appip' );
	if( !isset( $currRedirects[$request_url] ) )
		return false;
	if( !isset( $currMeta[$request_url] ) ) 
		return false;
	unset( $currRedirects[$request_url], $currMeta[$request_url] );
	update_option( 'quickredirectppr_redirects', sanitize_option( 'quickredirectppr_redirects', $currRedirects ) );
	update_option( 'quickredirectppr_redirects_meta', sanitize_option( 'quickredirectppr_redirects_meta', $currMeta ) );
	$redirect_plugin->quickredirectppr_redirectsmeta	= get_option( 'quickredirectppr_redirects_meta', array() );
	$redirect_plugin->quickredirectppr_redirects 		= get_option( 'quickredirectppr_redirects', array() );
	return true;
}

/**
* qredirectppr_get_browser_family - helper function that uses HTTP_USER_AGENT to determine browser family (for meta redirect).
* @param type string either 'name' or 'class'
* @return string returns browser family name or class (using sanitize_title_with_dashes function).
*		returns 'unknown' if browser family is not known.
* @since: 5.1.3
* @example:
* *****************
	$browserFamilyName = qredirectppr_get_browser_family( 'name' );
* *****************
*/
function qredirectppr_get_browser_family( $type = 'class' ){ //name or class
	global $is_iphone,$is_chrome,$is_safari,$is_NS4,$is_opera,$is_macIE,$is_winIE,$is_gecko,$is_lynx,$is_IE,$is_edge;
	if( $is_IE ){
		if( $is_macIE )
			$name = 'Mac Internet Explorer';
		if( $is_winIE )
			$name = 'Windows Internet Explorer';
		$name = 'Internet Explorer';
	}else if( $is_iphone || $is_safari ){
		if( $is_safari )
			$name = 'Safari';
		$name = 'iPhone Safari';
	}else if( $is_edge ){
		$name = 'Microsoft Edge';
	}else if( $is_chrome ){
		$name = 'Google Chrome';
		if ( isset($_SERVER['HTTP_USER_AGENT']) ) {
			if ( strpos($_SERVER['HTTP_USER_AGENT'], 'Edge') !== false )
				$name = 'Microsoft Edge';
		}
	}else if( $is_NS4 ){
		$name = 'Netscape 4';
	}else if( $is_opera ){
		$name = 'Opera';
	}else if( $is_gecko ){
		$name = 'FireFox';
	}else if( $is_lynx ){
		$name = 'Lynx';
	}else{
		$name = 'Unknown';	
	}
	if($type == 'name')
		return $name;
	return sanitize_title_with_dashes( 'browser-'.$name );
}
redirect:
?>