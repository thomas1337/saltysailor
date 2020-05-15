<?php
if (!defined('EQDKP_INC')){
	header('HTTP/1.0 404 Not Found');exit;
}

/*+----------------------------------------------------------------------------
  | generic init hook
  +--------------------------------------------------------------------------*/
if (!class_exists('salty_sailor_hook')) {
	class salty_sailor_hook extends gen_class {

		/**
		* hook_init
		* blablabla
		*
		* @return array
		*/
		public function salty_sailor(){
			// NOP
		}

	}
}
