<?
class ftp {
	private
		$connectionId;
	public
		function __construct($ftp_server, $ftp_username, $ftp_pass)
		{
			$this->connectionId = ftp_connect($ftp_server);
			if ($this->connectionId) {
				$login_result = ftp_login($this->connectionId, $ftp_username, $ftp_pass);
				if ((!$this->connectionId) || (!$login_result)) {
							 echo "Conexiunea FTP a dat eroare ! ";
							 echo "Incercare de conectare la $ftp_server cu userul $ftp_username";
					 } else {
							 echo "Conexiunea FTP s-a realizat ($ftp_server) cu userul $ftp_username";
					 }
			}
			else
			{
				echo "Conexiunea FTP a dat eroare ! ";
				echo "Incercare de conectare la $ftp_server !";
			}
		}
	public
		function makeFolder($dir, $permission = 0777) {
			if ($this->connectionId) {
				if (@ftp_mkdir($this->connectionId, $dir)) {
					if (@ftp_chmod($this->connectionId, $permission, $dir)) {
						return true;
					}
					else
						return false;				
				}
				else
					return false;
			}
			else
				return false;			
		}
	public
		function renameFolder($olddir, $dir) {
			if ($this->connectionId) {
				if (@ftp_rename($this->connectionId, $olddir, $dir)) {
					return true;
				}
				else
					return false;
			}
			else
				return false;
		}		
	public
		function __destruct() {
			if ($this->connectionId) {
				ftp_close($this->connectionId);
			}
		}
}
