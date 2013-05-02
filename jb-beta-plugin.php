<?php
if(!class_exists("JB_BETA_LIST_TABLE")) {
	require_once(ABSPATH . 'wp-admin/includes/template.php' );
	require_once(dirname(__FILE__) . '/jb-beta-list-table.php');
	require_once(dirname(__FILE__) . '/jb-basecamp.php');
}

class JB_Beta_Plugin {
	
	var $build = 1;
	
	var $db;
	
	var $jb_beta_table;
	
	var $listTable;
	
	var $basecamp;
	
	function __construct() {
		global $wpdb;
		$this->db =& $wpdb;
		$this->jb_beta_table = "wp_jb_beta";
		
		$this->listTable = new JB_Beta_List_Table();
		$this->basecamp = new JB_Basecamp($this->jb_beta_table);
		
		// Installation functions
		register_activation_hook(__FILE__, array(&$this, 'install'));
		
		add_action('init', array(&$this, 'init_plugin'));
		add_action('admin_menu', array(&$this, 'add_adminmenu'));
		
		add_action('wp_footer', array(&$this, 'frontend_betaform'));
		
		// establish ajax actions
		$this->establishAJAX();
		
		// check for installation
		if(get_option('jb_beta_installed', 0) < $this->build) {
			// create the database table
			$this->install();
		}
		
	}
	
	function init_plugin() {
		if(get_option('jb_beta_installed', 1) < $this->build) {
			$this->install();
		}
		
		$this->frontend_betaheader();
	}
	
	function install() {
	
		if($this->db->get_var( "SHOW TABLES LIKE '" . $this->jb_beta_table . "' ") != $this->jb_beta_table) {
			$sql = "CREATE TABLE IF NOT EXISTS `{$this->jb_beta_table}` (
					  `id` int(11) NOT NULL AUTO_INCREMENT,
					  `todo` text NOT NULL,
					  `basecamp_id` varchar(255) NOT NULL,
					  `basecamp_uri` varchar(255) NOT NULL,
					  `screenshot_uri` varchar(255) NOT NULL,
					  `done` tinyint(4) NOT NULL,
					  `created_on` datetime NOT NULL,
					  PRIMARY KEY (`id`)
					) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1";

			$this->db->query($sql);
		
		}
		
		update_option('jb_beta_installed', $this->build);

	}
	
	function establishAJAX() {
		
		add_action('wp_ajax_submitTodo', array(&$this, 'submitTodo'));
		add_action('wp_ajax_nopriv_submitTodo', array(&$this, 'submitTodo'));
		
	}
	
	function submitTodo() {
		global $wpdb;
		
		$name = (isset($_POST['name']) && $_POST['name'] != "Your Name") ? $_POST['name'] : '';
		$todoText = $_POST['todo'];
		$url = $_POST['currentPage'];
		$filename = '';
		
		if(sizeof($_POST['screenshot']) > 0) {
			
			$data = substr($_POST['screenshot'], strpos($_POST['screenshot'], ",") + 1);
			$image = base64_decode($data);
			$filename = date("Ymdhis").'-screenshot.png';
			$filePath = dirname(__FILE__).'/'.$filename;
			file_put_contents($filePath, $image);
		
		} else {
		
			echo "There was an error uploading the screenshot.";
			die();
			
		}
		
		if(strlen($todoText) > 0 && $name != '') {
			echo $this->basecamp->createTodo(array(
				'todoText' => $todoText,
				'name' => $name,
				'screenshot' => $filePath,
				'screenshot_filename' => $filename,
				'url' => $url
			));
			
			if(strlen($filePath) > 0) unlink($filePath);
			
		} else {
			echo "You left a couple fields blank... Try again.";
			
			if(strlen($filePath) > 0) unlink($filePath);	
		}
		
		die();
		
	}
	
	function frontend_betaform() {
		if(!is_admin()) {
		?>
		
		<div id="mr-beta">
			<a class="mr-beta_header" href="#">Mr. Beta <span>+</span></a>
			
			<div class="mr-beta_body">
				<form name="mr-beta_form">
					<div class="mr-beta_notice">Success!</div>
					<h3 class="mr-beta_h3">Describe the issue:</h3>
					<textarea name="mr-beta_todo" class="mr-beta_todo"/></textarea>
					<input type="text" value="Your Name" name="mr-beta_name" class="mr-beta_name"/>
					<input type="submit" id="mr-beta_submit" name="mr-beta_submit" value="Submit"/>
				</form>
			</div>
		</div>
		
		<?php
		}
		
		
	}
	
	function frontend_betaheader() {
		if(!is_admin()) {
			wp_enqueue_script('jquery');
			wp_enqueue_script('html2canvas', plugins_url('/html2canvas/build/html2canvas.js',__FILE__));
			wp_enqueue_script('mr-beta', plugins_url('/mr-beta.js',__FILE__));
			wp_enqueue_style('mr-beta', plugins_url('/mr-beta.css',__FILE__));
		}
	}
	
	function add_adminmenu() {
		
		add_menu_page(__('All Todos','jbbetatext'), __('Mr. Beta','jbbetatext'), 'manage_options',  'jbbeta_admin', array(&$this, 'handle_admin_page'));
		add_submenu_page('jbbeta_admin', __('Settings','jbbetatext'), __('Settings','jbbetatext'), 'manage_options', "jbbeta_settings", array(&$this,'handle_setting_page'));
		
	}
	
	function show_table() {
	?>
		<div class="wrap">
		
		<div id="icon-edit-comments" class="icon32"></div>
			
		<h2>Website Issues</h2>
	<?php 
		$this->listTable->prepare_items(); 
	?>
		<form method="POST">
			<input type="hidden" name="page" value="ttest_list_table">
	<?php
		$this->listTable->display();
	?>
		</form>
		</div>
	<?php
	}
	
	function handle_admin_page() {
	
		$this->show_table();
		
	}
	
	function handle_setting_page() {
		?>
		<div class="wrap">
			
			<div id="icon-options-general" class="icon32"></div>
			
			<h2>Settings</h2>
		
			<form method="POST">
				
				<ul>
					<li>
						<?php 
						$saved = false;
						
						if(isset($_POST['basecamp_project_id'])) {
							
							update_option('jb_beta_basecamp_project_id', sanitize_text_field($_POST['basecamp_project_id']));
							$saved = true;
							
						}
						
						if(isset($_POST['basecamp_list_id'])) {
							
							update_option('jb_beta_basecamp_list_id', sanitize_text_field($_POST['basecamp_list_id']));
							$saved = true;
							
						}
						
						if($saved) {
						?>
						<div id="message" class="updated"><p><strong>Settings Saved</strong>.</p></div>
						<?php
						}
						
						$basecamp_project_id = get_option('jb_beta_basecamp_project_id');
						$basecamp_list_id = get_option('jb_beta_basecamp_list_id'); 
						
						?>
					
						<h3>Basecamp Project ID</h3>
						<input id="basecamp_project_id" maxlength="100" size="50" name="basecamp_project_id" value="<?php echo $basecamp_project_id; ?>"/>
					
						<h3>Basecamp List ID</h3>
						<input id="basecamp_project_id" maxlength="100" size="50" name="basecamp_list_id" value="<?php echo $basecamp_list_id; ?>"/>
					</li>
				</ul>
				
				<p>
				<input class="button-primary" type="submit" name="Save" value="<?php _e('Save Options'); ?>" id="submitbutton"/>
				</p>
			</form>
		
		</div>
		<?php
	}
	
}
?>