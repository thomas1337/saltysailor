<?php

if (!defined('EQDKP_INC')){
	header('HTTP/1.0 404 Not Found');exit;
}

class salty_sailor extends plugin_generic {
	public $vstatus		= 'Stable';
	public $version		= '1.0.0';
	public $copyright 	= 'Push';

	protected static $apiLevel = 23;

	public function __construct(){
		parent::__construct();

		$this->add_data(array (
			'name'				=> 'SaltySailor Plugin',
			'code'				=> 'salty_sailor',
			'path'				=> 'salty_sailor',
			'template_path'		=> 'plugins/salty_sailor/templates/',
			'icon'				=> 'fa-university',
			'version'			=> $this->version,
			'author'			=> $this->copyright,
			'description'		=> $this->user->lang('salty_sailor_short_desc'),
			'long_description'	=> $this->user->lang('salty_sailor_long_desc'),
			'homepage'			=> EQDKP_PROJECT_URL, // none
			'manuallink'		=> false,
			'plus_version'		=> '2.1'
		));
		
		// -- Hooks -------------------------------------------
		// $this->add_hook('salty_sailor',	'salty_sailor_hook', 'salty_sailor'); // for upcomming features - NOP now
	}

	/**
	* Define Installation
	* ** SaltySailor: Add Raidexporter for Exorsus Raid Tools
	* ** SaltySailor: Use Plus*Handlers to install and uninstall the exporter in a clean way
	*/
	public function pre_install() {
		$new_exporter_path = $this->root_path.'core/calendarexport/saltysailor_export.class.php';
		$this->pfh->CheckCreateFile($new_exporter_path);
		
		
		$fcontent = "<?php
			
		if ( !defined('EQDKP_INC') ){
			header('HTTP/1.0 404 Not Found');exit;
		}
			
		\$rpexport_plugin['saltysailor_export.class.php'] = array(
			'name'			=> 'saltysailor',
			'function'		=> 'saltysailorexport',
			'contact'		=> 'webmaster@saltysailor-classic.de',
			'version'		=> '2.0.0');
			
		if(!function_exists('saltysailorexport')){
			function saltysailorexport(\$raid_id, \$raid_groups){
				\$attendees	= registry::register('plus_datahandler')->get('calendar_raids_attendees', 'attendees', array(\$raid_id));
				\$guests		= registry::register('plus_datahandler')->get('calendar_raids_guests', 'members', array(\$raid_id));
			
				\$a_json	= array();
				foreach(\$attendees as \$id_attendees=>\$d_attendees){
					\$a_json[]	= array(
						'name'		=> registry::register('plus_datahandler')->get('member', 'name', array(\$id_attendees)),
						'status'	=> \$d_attendees['signup_status'],
						'guest'		=> false,
						'group'		=> \$d_attendees['raidgroup']
					);
				}
				foreach(\$guests as \$guestsdata){
					\$a_json[]	= array(
						'name'		=> \$guestsdata['name'],
						'status'	=> false,
						'guest'		=> true,
						'group'		=> \$guestsdata['raidgroup']
					);
				}
				\$json = json_encode(\$a_json);
				unset(\$a_json);
			
				registry::register('template')->add_js('
					genOutput()
					$(\"input[type=\'checkbox\'], #ip_seperator, #raidgroup\").change(function (){
						genOutput()
					});
			
				', \"docready\");
			
				registry::register('template')->add_js('
				function genOutput(){
					var attendee_data = '.\$json.';
					var data = [];
			
					ip_seperator	= ($(\"#ip_seperator\").val() != \"\") ? \"\\n\" : \"\\n\";
					cb_guests		= ($(\"#cb_guests\").prop(\"checked\")) ? true : false;
					cb_confirmed	= ($(\"#cb_confirmed\").prop(\"checked\")) ? true : false;
					cb_signedin		= ($(\"#cb_signedin\").prop(\"checked\")) ? true : false;
					cb_backup		= ($(\"#cb_backup\").prop(\"checked\")) ? true : false;
			
					$.each(attendee_data, function(i, item) {
						if((cb_guests && item.guest == true) || (cb_confirmed && !item.guest && item.status == 0) || (cb_signedin && item.status == 1) || (cb_backup && item.status == 3)){
							if($(\"#raidgroup\").val() == \"0\" || (item.group > 0 && item.group == $(\"#raidgroup\").val())){
								data.push(item.name);
							}
						}
					});
					\$(\"#attendeeout\").html(data.join(ip_seperator));
				}
					');
				\$text  = \"\";
			
				\$text .= \"<dt><label>\".registry::fetch('user')->lang('raidevent_export_raidgroup').\"</label></dt>
							<dd>
								\".(new hdropdown('raidgroup', array('options' => \$raid_groups, 'value' => 0, 'id' => 'raidgroup')))->output().\"
							</dd>
						</dl><dl>\";
				\$text .= \"<input type='checkbox' checked='checked' name='confirmed' id='cb_confirmed' value='true'> \".registry::fetch('user')->lang(array('raidevent_raid_status', 0));
				\$text .= \"<input type='checkbox' checked='checked' name='guests' id='cb_guests' value='true'> \".registry::fetch('user')->lang('raidevent_raid_guests');
				\$text .= \"<input type='checkbox' checked='checked' name='signedin' id='cb_signedin' value='true'> \".registry::fetch('user')->lang(array('raidevent_raid_status', 1));
				\$text .= \"<input type='checkbox' name='backup' id='cb_backup' value='true'> \".registry::fetch('user')->lang(array('raidevent_raid_status', 3));
				\$text .= \"<br/>\";
				\$text .= \"<textarea name='group\".mt_rand().\"' id='attendeeout' cols='60' rows='10' onfocus='this.select()' readonly='readonly'>\";
				\$text .= \"</textarea>\";
			
				\$text .= '<br/>'.registry::fetch('user')->lang('rp_copypaste_ig').\"</b>\";
				return \$text;
			}
		}";
		
		$this->pfh->putContent($new_exporter_path, $fcontent);
	}

	/**
	* Define uninstallation
	* ** SaltySailor: Clean install Excorsius Raid Tools exporter using Plus Handler Functions
	*/
	public function pre_uninstall(){
		$new_exporter_path = $this->root_path.'core/calendarexport/saltysailor_export.class.php';
		$this->pfh->Delete($new_exporter_path);
	}
	
}
?>
