<?php

error_reporting(E_ALL | E_STRICT);
ini_set('display_errors', '1');

class Deploy
{
	public static $current_symlink = 'current';
	public static $snapshots_dir = 'snapshots';
	private $_old_checkout_dir;
	private $_checkout_dir;
	
	private function _exec($command)
	{
		passthru($command, $error);
		
		if ($error) {
			echo "This command seems to have failed:\n{$command}\n";
			exit;
		}
	}
	
	public function git($url, $branch = 'master')
	{
		if (is_link(self::$current_symlink)) {
			$this->_old_checkout_dir = readlink(self::$current_symlink);
		} else {
			$this->_old_checkout_dir = null;
		}
		
		$dir = self::$snapshots_dir . '/' . base_convert(time(), 10, 36) . '_' . $branch . '/';
		$this->_exec("git clone --recurse-submodules --branch {$branch} {$url} {$dir}");
		$this->_checkout_dir = $dir;
	}
	
	public function mysql($migrations_dir, $credentials)
	{
		$old = $this->_old_checkout_dir ? glob($this->_old_checkout_dir . $migrations_dir . '*.sql') : array();
		$old = array_map('basename', $old);
		$new = glob($this->_checkout_dir . $migrations_dir . '*.sql');
		$new = array_map('basename', $new);
		
		// Migrate all new files
		foreach (array_diff($new, $old) as $file) {
			$this->_exec("mysql --user={$credentials['user']} --password={$credentials['pass']} {$credentials['dbname']} < {$this->_checkout_dir}{$migrations_dir}{$file}");
		}
	}

	public function finalize()
	{
		is_link(self::$current_symlink) && unlink(self::$current_symlink);
		symlink($this->_checkout_dir, self::$current_symlink);
	}
}

