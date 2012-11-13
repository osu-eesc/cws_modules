<?php
/**
 * SSO functions and related LDAP utilities
 * $Id: sso.php 11134 2007-08-07 23:34:07Z mccammos $
 *
 * @package libs
 * @subpackage sso
 * @author Michael Morgan <mike.morgan@oregonstate.edu>
 * @author Scott McCammon <scott.mccammon@oregonstate.edu>
 * @author Andy Morgan <morgan@oregonstate.edu>
 */

/**
 * This library requires the PEAR XML_RPC package
 */
include_once 'XML/RPC.php';

/*****************************************
 *         BEGIN CONFIGURATION           *
 * (see below for sample configurations) *
 *****************************************/

/* Disable auto configuration and initialization to
 * encourage repository checkouts to be manually configured
 * via external configuration. If distributing this library
 * outside of CWS, you should uncomment this block as well
 * as the block of auto init code at the end

$sso_site_config = array(
	// automatic authentication and session checking
	'auto'            => '0',

	// authentication configuration
	'sso_service'     => '',
	'sso_password'    => '',
	'logout_page'     => 'logout.php',
	'logout_redirect' => '1',

	// session configuration
	'sess_enable'     => '1',
	'sess_cookie'     => 'sso',
	'sess_path'       => '/',
	'sess_host'       => 'oregonstate.edu',
	'sess_length'     => '3600',
	'sess_secure'     => '0',

	// the remaining parameters are required to store sessions in a databsase 
	// if not specified, sessions will be store on the local file system
	'db_host'         => 'db.cws.oregonstate.edu',
	'db_username'     => '',
	'db_password'     => '',
	'db_name'         => '',
	'db_table'        => 'session',		// name of session table
	'db_sid_col'      => 'sid',			// expected type: varchar(32)
	'db_expire_col'   => 'expire',		// expected type: unsigned int
	'db_data_col'     => 'data',		// expected type: text

	// you should not need to change these
	'sso_host'        => 'secure.onid.oregonstate.edu',
	'sso_path'        => '/sso/rpc',
	'sso_cookie'      => 'sso',
	'sso_login_url'   => 'https://secure.onid.oregonstate.edu/login',
);

***** end auto configuration block comment *****/

/*****************************************
 *          END CONFIGURATION            *
 *                                       *
 * DO NOT EDIT ANYTHING BELOW THIS POINT *
 *****************************************/

/**
 *  Default configuration. Any parameters not explicitly set above
 *  will be set to the following by default:

$sso_site_config = array(
	'auto'            => '1',

	// sso auth configuration
	'sso_service'     => '',
	'sso_password'    => '',
	'sso_host'        => 'secure.onid.oregonstate.edu',
	'sso_path'        => '/sso/rpc',
	'sso_cookie'      => 'sso',
	'logout_page'     => 'logout.php',
	'logout_redirect' => '1',
	'sso_login_url'   => 'https://secure.onid.oregonstate.edu/login',

	// ssesion configuration
	'sess_enable'     => '1',
	'sess_cookie'     => 'sso',
	'sess_path'       => '/',
	'sess_host'       => 'oregonstate.edu',
	'sess_length'     => '3600',
	'sess_secure'     => '0',
	'db_host'         => 'db.cws.oregonstate.edu',
	'db_username'     => '',
	'db_password'     => '',
	'db_name'         => '',
	'db_table'        => 'session',		// name of session table
	'db_sid_col'      => 'sid',			// expected type: varchar(32)
	'db_expire_col'   => 'expire',		// expected type: unsigned int
	'db_data_col'     => 'data',		// expected type: text
);

 */

/*****************************************************************************
 * BEGIN CONFIGURATION EXAMPLES:
 *****************************************************************************
 *
 * Each configuration sample includes configuration parameters that must
 * be specified by editing this file (above), and also sample php code
 * for how to use the configuration with your site.
 *
 *****************************************************************************
 * Session Handling Only Via Database (no sso authentication):
 *****************************************************************************

$sso_site_config = array(
	// automatic session checking
	'auto'            => '1',

	// disable authentication
	'sso_service'     => '',

	// session configuration
	'sess_enable'     => '1',
	'sess_cookie'     => 'yourcookiename',
	'sess_path'       => '/',
	'sess_host'       => 'oregonstate.edu',
	'sess_length'     => '3600',
	'sess_secure'     => '0',

	// database information for storing session data
	'db_host'         => 'db.cws.oregonstate.edu',
	'db_username'     => 'username',
	'db_password'     => 'password',
	'db_name'         => 'database_name',
	'db_table'        => 'table_name',
	'db_sid_col'      => 'sid',			// expected column type: varchar(32)
	'db_expire_col'   => 'expire',		// expected column type: unsigned int
	'db_data_col'     => 'data',		// expected column type: text
);

<?php
	//
	// all files in your site
	//

	include_once('sso.php');

	// ... rest of page content
?>

 *****************************************************************************
 * Session Handling and SSO Authentication For Entire Site:
 *****************************************************************************

$sso_site_config = array(
	// automatic authentication and session checking
	'auto'            => '1',

	// authentication configuration
	'sso_service'     => 'myservice',
	'sso_password'    => 'servicepassword',

	// database information for storing session data
	'db_host'         => 'db.cws.oregonstate.edu',
	'db_username'     => 'username',
	'db_password'     => 'password',
	'db_name'         => 'database_name',
	'db_table'        => 'table_name',
	'db_sid_col'      => 'sid',			// expected column type: varchar(32)
	'db_expire_col'   => 'expire',		// expected column type: unsigned int
	'db_data_col'     => 'data',		// expected column type: text
);

<?php
	//
	// all files in your site except logout.php
	//
	include_once('sso.php');

	// ... rest of page content
?>

<?php
	//
	// logout.php
	//
	 include_once('sso.php');
	 sso_logout();
?>

 *****************************************************************************
 * Advanced Example With Session Handling and SSO Authentication:
 *  - index.php does not require authentication
 *  - private.php requires authentication
 *  - logout.php does not destroy session or redirect to sso login page
 *****************************************************************************

$sso_site_config = array(
	// automatic authentication and session checking
	'auto'            => '0',

	// authentication configuration
	'sso_service'     => 'myservice',
	'sso_password'    => 'servicepassword',
	'logout_redirect' => '0',

	// database information for storing session data
	'sess_cookie'     => 'myservice',	// anything but 'sso'
	'db_host'         => 'db.cws.oregonstate.edu',
	'db_username'     => 'username',
	'db_password'     => 'password',
	'db_name'         => 'database_name',
	'db_table'        => 'table_name',
	'db_sid_col'      => 'sid',			// expected column type: varchar(32)
	'db_expire_col'   => 'expire',		// expected column type: unsigned int
	'db_data_col'     => 'data',		// expected column type: text
);

<?php
	//
	// index.php
	//

	include_once('sso.php');
	if (sso_authenticate(false)) {	// will NOT redirect if not authenticated
		// do any stuff here that requires authentication
		sso_session_fill_userinfo();	

	}
	sso_session_check();

	// display navigation and public content for everybody here
?>

<?php
	//
	// private.php
	//
	include_once('sso.php');

	sso_authenticate();	// will redirect to login page if not authenicated
	sso_session_check();
	sso_session_fill_userinfo();

	// display navigation and content for everybody here
?>

<?php
	//
	// logout.php
	//
	include_once('sso.php');
	sso_logout(false);

	// display navigation and content of your logout page here
?>

 *****************************************************************************
 * END CONFIGURATION EXAMPLES
 *****************************************************************************/

/*****************************************************************************
 * BEGIN SSO AUTHENTICATION AND SESSION HANDLING FUNCTIONS
 *****************************************************************************/

/**
 * Get/set sso configuration parameters
 * param = null: returns array of all config parameters
 * param = string: return a specific config parameter
 * param = array: set config parameter(s)
 *
 * @param mixed $param
 * @return mixed
 */
function sso_config($param=null)
{
	static $config;

	// initialize config with default values
	if (!isset($config)) {
		$config = array(
			'auto'            => '0',

			// sso auth configuration
			'sso_service'     => '',
			'sso_password'    => '',
			'sso_host'        => 'secure.onid.oregonstate.edu',
			'sso_path'        => '/sso/rpc',
			'sso_cookie'      => 'sso',
			'logout_page'     => 'logout.php',
			'logout_redirect' => '1',
			'sso_login_url'   => 'https://secure.onid.oregonstate.edu/login',

			// ssesion configuration
			'sess_enable'     => '1',
			'sess_cookie'     => 'sso',
			'sess_path'       => '/',
			'sess_host'       => 'oregonstate.edu',
			'sess_length'     => '3600',
			'sess_secure'     => '0',
			'db_host'         => 'db.cws.oregonstate.edu',
			'db_username'     => '',
			'db_password'     => '',
			'db_name'         => '',
			'db_table'        => 'session',
			'db_sid_col'      => 'sid',			// expected type: varchar(32)
			'db_expire_col'   => 'expire',		// expected type: unsigned int
			'db_data_col'     => 'data',		// expected type: text
		);
	}

	// retrieve entire config array
	if (is_null($param)) {
		return $config;

	// set config params: overwrite only values specified
	} elseif (is_array($param)) {
		foreach ($param as $key => $val) {
			$config[$key] = $val;
		}
		return $config;

	// retrieve a single config param
	} else {
		if (isset($config[$param])) {
			return $config[$param];
		} else {
			return null;
		}
	}
}

/**
 * Retrieve a database connection, connecting if necessary or optionally set
 *
 * @param mixed $new_dbh if specified set the connection to this handle
 * @return mixed resource if db connection is valid, false if not
 */
function sso_db_connect($new_dbh = null)
{
	static $dbh;

	// explicitly set handle 
	if (!is_null($new_dbh)) {
		// close any existing connection
		if (is_resource($dbh)) {
			mysql_close($dbh);
		}
		$dbh = false;

		// set new connection
		if (is_resource($new_dbh)) {
			$dbh = $new_dbh;
		}

	// attempt to connect using db config parameters
	} elseif (!isset($dbh)) {
		$conf = sso_config();
		if ($conf['db_host'] && $conf['db_username'] && $conf['db_password']) {
			$dbh = @mysql_connect($conf['db_host'], $conf['db_username'], $conf['db_password']);
			if ($dbh) {
				mysql_select_db($conf['db_name']);
			}
		} else {
			$dbh = false;
		}
	}

	return $dbh;
}

/**
 * Private: Open session
 *
 * @return bool
 */
function sso_sess_open($save_path, $session_name)
{
	// don't really need to do anything, but might as well check the db connection
	if (sso_db_connect()) {
		return true;
	}
	return false;
}

/**
 * Private: Close session
 *
 * @return bool true
 */
function sso_sess_close()
{
	return true;
}

/**
 * Private: Read session data
 *
 * @return string
 */
function sso_sess_read($sess_id)
{
	if ($dbh = sso_db_connect()) {
		$conf = sso_config();
		$sid = mysql_real_escape_string($sess_id);
		$sql = "SELECT `$conf[db_data_col]` FROM `$conf[db_table]` WHERE `$conf[db_sid_col]` = '$sid'";
		if ($res = mysql_query($sql, $dbh)) {
			if (mysql_num_rows($res) == 1) {	
				$row = mysql_fetch_row($res);
				return $row[0];
			}
		}
	}
	return '';
}

/**
 * Private: Write session data into database
 *
 * @param string $sess_id session id
 * @param string $sess_data serialized array of session data
 * @return bool true
 */
function sso_sess_write($sess_id, $sess_data)
{
	// get session database handle
	if ($dbh = sso_db_connect()) {
		$conf = sso_config();
		$sid = mysql_real_escape_string($sess_id);
		$data = mysql_real_escape_string($sess_data);
		$sql = "REPLACE INTO `$conf[db_table]`
				SET `$conf[db_data_col]`   = '$data',
				    `$conf[db_expire_col]` = (UNIX_TIMESTAMP() + '$conf[sess_length]'),
				    `$conf[db_sid_col]` = '$sid'";
		if (mysql_query($sql, $dbh)) {
			return true;
		}
	}
	return false;
}

/**
 * Private: Destroy a session
 *
 * @param string $sess_id session identifier
 * @return bool true on success
 */
function sso_sess_destroy($sess_id)
{
	if ($sess_id) {
		$conf = sso_config();
		$sid = mysql_real_escape_string($sess_id);
		$sql = "DELETE FROM `$conf[db_table]` WHERE `$conf[db_sid_col]` = '$sid' LIMIT 1";
		if (($dbh = sso_db_connect()) && mysql_query($sql, $dbh)) {
			return true;
		}
		return false;
	}
	return true; // no session to destroy?
}

/**
 * Private: Garbage collection for expired session data
 *
 * @param int $maxlifetime
 * @return bool true
 */
function sso_sess_gc($maxlifetime)
{
	// get session database handle
	if ($dbh = sso_db_connect()) {
		// delete expired sessions
		$conf = sso_config();
		$sql = "DELETE FROM `$conf[db_table]` WHERE `$conf[db_expire_col]` < UNIX_TIMESTAMP()";
		if (mysql_query($sql, $dbh)) {
			return true;
		}
	}
	return false;
}

/**
 * Authenticate the current sso session
 *
 * @param bool $redirect redirect to sso login page (default = true)
 * @param bool $extend extend sso login expiration (default = true)
 * @param mixed $query hash of values to append to login query string or null
 * @return mixed int expire time on success, 0 on failure
 */
function sso_authenticate($redirect = true, $extend = true, $query = null)
{
	static $auth; // cache result to save on some unecessary calls
	if (isset($auth)) {
		if (!$auth && $redirect) {
			sso_login($query);
		}
		return $auth;
	}

	$conf = sso_config();

	// return if authentication is not configured
	if (empty($conf['sso_service']) || empty($conf['sso_password'])) {
		return 0;
	}

	if ($conf['sso_cookie'] && !empty($_COOKIE[$conf['sso_cookie']])) {
		$sid = $_COOKIE[$conf['sso_cookie']];
		$ssoauth = $conf['sso_service'] . ":" . $conf['sso_password'];

		$message = new XML_RPC_Message('sso.session_check',
					array(new XML_RPC_Value($ssoauth, 'string'),
						new XML_RPC_Value($sid, 'string'),
						new XML_RPC_Value($extend ? 1 : 0, 'int')));

		$server = new XML_RPC_Client($conf['sso_path'], "https://$conf[sso_host]");
		$result = $server->send($message, 0);

		if (is_object($result) && ($result->faultCode() === 0)) {
			$array = XML_RPC_decode($result->value());
			if (strcmp($array['valid'], 1) === 0) {
				$auth = $array['expire_time'];
				return $auth;
			}
		}
	}

	// if we get here, they are invalid - send them to the login page or return false
	$auth = 0;
	if ($redirect) {
		sso_login($query);
	}
	return $auth;
}

/**
 * Redirect to sso login page
 *
 * @param mixed $query hash of values to append to login query string or null
 * @return void
 */
function sso_login($query = null)
{
	$url = sso_login_url($query, false);
	header("Location: $url");
	exit;
}

/**
 * Generate an sso login url
 *
 * @param mixed $query hash of values to append to login query string or null
 * @param bool $encode_amp if true (default) query params are separated by '&amp;' 
 *                         otherwise with '&'
 * @return string
 */
function sso_login_url($query = null, $encode_amp=true)
{
	$conf = sso_config();
	$amp = $encode_amp ? '&amp;' : '&';

	// handle the passed query arguments, if any
	$qstring = '';
	if ( is_array($query) ) {
		foreach ($query as $key=>$val) {
			$qstring .= $amp . $key . '=' . urlencode($val);
		}
	}

	return "{$conf['sso_login_url']}?service={$conf['sso_service']}{$qstring}";
}

/**
 * Logout of sso. Optionally destroy local session data. Optionally redirect to sso login page.
 * NOTE: if sso and session are configured to share the same cookie, the local session data
 * will always be destroyed
 *
 * @param bool $destroy_session if true, will destroy local session data (sso session data is always destroyed)
 * @return mixed true or redirect to login
 */
function sso_logout($destroy_session = true)
{
	$conf = sso_config();

	// delete sso cookie
	if ($conf['sso_cookie'] && !empty($_COOKIE[$conf['sso_cookie']])) {
		$sid = $_COOKIE[$conf['sso_cookie']];
		$ssoauth = $conf['sso_service'] . ":" . $conf['sso_password'];

		$message = new XML_RPC_Message('sso.session_destroy',
					array(new XML_RPC_Value($ssoauth, 'string'),
						new XML_RPC_Value($sid, 'string')));

		$server = new XML_RPC_Client($conf['sso_path'], "https://$conf[sso_host]");
		$result = $server->send($message, 0);

		if (is_object($result) && ($result->faultCode() === 0)) {
			$array = XML_RPC_decode($result->value());
			if (strcmp($array['success'], 1) === 0) {
				// Couldn't find it in the SSO sessions, or some remote error
				// Either way, carry on
			}
		}
		setcookie($conf['sso_cookie'], '', time() - 3600, '/', '.oregonstate.edu');
		unset($_COOKIE[$conf['sso_cookie']]);

		// destroy sso session data
		if (isset($_SESSION['sso'])) {
			unset($_SESSION['sso']);
		}
		if (isset($_SESSION['ldap'])) {
			unset($_SESSION['ldap']);
		}
	}

	// possibly destroy local session data
	if ($destroy_session || ($conf['sso_cookie'] == $conf['sess_cookie'])) {
		sso_session_destroy();
	}

	// possibly redirect to login page
	if ($conf['logout_redirect']) {
		header("Location: {$conf['sso_login_url']}?service={$conf['sso_service']}");
		exit;
	}
}

/**
 *  Start or continue a local session 
 *
 *  @return string local session id on success
 */
function sso_session_check()
{
	static $sid;
	if (isset($sid)) {
		return $sid;
	}

	// if we get here, we haven't started sessions yet...
	$conf = sso_config();
	if ($conf['sess_enable'] && $conf['sess_cookie']) {
		// session/cookie name
		$c_name = $conf['sess_cookie'];

		// using the sso cookie for our session id
		if ($conf['sess_cookie'] === $conf['sso_cookie']) {
			$c_host = '.oregonstate.edu';
			$c_path = '/';
			$c_secure = 0;
			$c_length = 0;

		// using a custom cookie for our session id
		} else {
			$c_host = !empty($conf['sess_host']) ? $conf['sess_host'] : $_SERVER['HTTP_HOST'];
			$c_path = !empty($conf['sess_path']) ? $conf['sess_path'] : '/';
			$c_secure = !empty($conf['sess_secure']) ? $conf['sess_secure'] : 0;
			$c_length = !empty($conf['sess_length']) ? $conf['sess_length'] : 0;
		}

		// we're using cookie based sessions here...
		session_set_cookie_params($c_length, $c_path, $c_host, $c_secure);

		// register database session handling functions (if we have db connectivity)
		// without db connectivity, this will fallback to default file based session - oh well...
		if (sso_db_connect()) {
			session_set_save_handler(
				"sso_sess_open",
				"sso_sess_close",
				"sso_sess_read",
				"sso_sess_write",
				"sso_sess_destroy",
				"sso_sess_gc");
		}

		// specify session name
		session_name($c_name);

		// make sure we continue using any existing session id
		if (isset($_COOKIE[$c_name])) {
			session_id($_COOKIE[$c_name]);
		}

		// fire up the session
		session_start();

		// need to reset session if a new sso login is detected
		if ((!empty($_SESSION['sso']['sid'])) && ($_SESSION['sso']['sid'] != $_COOKIE[$conf['sso_cookie']])) {

			// session_regenerate_id() doesn't function as documented with custom session handlers
			// so we must explicitly destroy and start a new session.
			session_regenerate_id();
			$new_sid = session_id();

			$_SESSION = array();
			session_destroy();

			// need to set handlers again (see bug http://bugs.php.net/bug.php?id=32330)
			if (sso_db_connect()) {
				session_set_save_handler(
					"sso_sess_open",
					"sso_sess_close",
					"sso_sess_read",
					"sso_sess_write",
					"sso_sess_destroy",
					"sso_sess_gc");
			}

			// set new sid and start the new session
			session_id($new_sid);
			session_start();
		}

		// remember session id for future calls to this function
		$sid = session_id();
		$_COOKIE[$c_name] = $sid;

	// sessions not enabled
	} else {
		$sid = '';
	}

	return $sid;
}

/**
 *  Destroy local session data
 *
 *  @return bool true
 */
function sso_session_destroy()
{
	$conf = sso_config();
	if ($sid = sso_session_check()) {
		// unset all session vars
		$_SESSION = array();

		// delete session cookie only if not sharing the sso cookie
		// (this shared cookie gets deleted by sso_logout())
		if (isset($_COOKIE[$conf['sess_cookie']]) && ($conf['sess_cookie'] !== $conf['sso_cookie'])) {
			// session/cookie name
			$c_name = $conf['sess_cookie'];
			$c_host = !empty($conf['sess_host']) ? $conf['sess_host'] : $_SERVER['HTTP_HOST'];
			$c_path = !empty($conf['sess_path']) ? $conf['sess_path'] : '/';
			$c_secure = !empty($conf['sess_secure']) ? $conf['sess_secure'] : 0;
			setcookie($c_name, '', time() - 3600, $c_path, $c_host, $c_secure);
			unset($_COOKIE[$c_name]);
		}

		// destroy session
		session_destroy();

		// need to set handlers again (see bug http://bugs.php.net/bug.php?id=32330)
		if (sso_db_connect()) {
			session_set_save_handler(
				"sso_sess_open",
				"sso_sess_close",
				"sso_sess_read",
				"sso_sess_write",
				"sso_sess_destroy",
				"sso_sess_gc");
		}
	}
	return true;
}

/**
 *	Verify authentication then populate SSO database info and  directory profile info for given user
 *	into $_SESSION. The results look like this:
 *
 *	Array
 *	(
 *		[sso] => Array
 *			(
 *				[sid] => rPo5FGQ1e5UkNESG8nycpxZmYlo0WpDj
 *				[sid_length] => 3600
 *				[create_time] => 1092089746
 *				[expire_time] => 1092093348
 *				[ip] => 128.193.162.244
 *				[osuuid] => 12345678901
 *				[username] => morgamic
 *				[lastname] => Morgan
 *				[firstname] => Michael
 *				[fullname] => Morgan, Michael Joseph
 *			)
 *
 *		[ldap] => Array
 *			(
 *				[osudepartment] => Media Services
 *				[postaladdress] => Media Services$Oregon State University$109 Kidder Hall$Corvallis, OR 97331-4604
 *				[mail] => mike.morgan@oregonstate.edu
 *				[title] => Analyst Programmer
 *				[osuprimaryaffiliation] => E
 *				[sn] => Morgan
 *				[cn] => Morgan, Michael Joseph
 *				[givenname] => Michael
 *				[objectclass] => top
 *				[uid] => morgamic
 *				[osuuid] => 15791281312
 *				[count] => 
 *				[dn] => o
 *			)
 *
 *	)
 *
 * @return bool true on succuess
 */
function sso_session_fill_userinfo()
{
	// can only fill userinfo if authentication and session is valid
	if (($sid = sso_session_check()) && ($expire = sso_authenticate(false))) {
		$conf = sso_config();
		if (empty($_SESSION['sso']) || ($_SESSION['sso']['sid'] != $_COOKIE[$conf['sso_cookie']])) {
			$data = sso_session_userinfo();
			$_SESSION['sso'] = $data['userinfo'];
		} else {
			// Update expire_time from session_check
			$_SESSION['sso']['expire_time'] = $expire;
		}
		if (empty($_SESSION['ldap']) && is_array($data)) {
			$_SESSION['ldap'] = sso_ldap_get_record($data['userinfo']['username'], 'uid');
		}
		return true;
	} else {
		return false;
	}
}

/**
 *	Get the userinfo stored in the SSO session
 *
 *	Returned array will look like:
 *		Array
 *		(
 *		    [userinfo] => Array
 *		        (
 *					[sid] => rPo5FGQ1e5UkNESG8nycpxZmYlo0WpDj
 *					[sid_length] => 3600
 *					[create_time] => 1092089746
 *					[expire_time] => 1092093348
 *					[ip] => 128.193.162.244
 *					[osuuid] => 12345678901
 *					[username] => morgamic
 *					[lastname] => Morgan
 *					[firstname] => Michael
 *					[fullname] => Morgan, Michael Joseph
 *		        )
 *
 *		    [isonid] => 1
 *		)
 *
 *	@return array
 */
function sso_session_userinfo()
{
	static $info;
	if (!empty($info)) {
		return $info;
	}

	$conf = sso_config();
	if ($conf['sso_cookie'] && !empty($_COOKIE[$conf['sso_cookie']])) {
		$sid = $_COOKIE[$conf['sso_cookie']];
		$ssoauth = $conf['sso_service'] . ":" . $conf['sso_password'];

		$message = new XML_RPC_Message('sso.session_userinfo',
					array(new XML_RPC_Value($ssoauth, 'string'),
						new XML_RPC_Value($sid, 'string')));

		$server = new XML_RPC_Client($conf['sso_path'], "https://$conf[sso_host]");
		$result = $server->send($message, 0);

		if (is_object($result) && ($result->faultCode() === 0)) {
			$info = XML_RPC_decode($result->value());
			return $info;
		}
	}

	$info = array();
	return $info;
}

/**
 *	Get the userinfo of any username. (but only if there is an
 *  established sso session)
 *
 *	Returned array will look like:
 *		Array
 *		(
 *		    [userinfo] => Array
 *		        (
 *					[lastname] => Morgan
 *					[firstname] => Michael
 *					[username] => morgamic
 *					[fullname] => Morgan, Michael Joseph
 *					[osuuid] => 12345678901
 *		        )
 *
 *		    [isonid] => 1
 *		)
 *
 *  @param string $username username to query
 *	@return array
 */
function sso_getuserinfo_byusername($username)
{
    $info = array();

	$conf = sso_config();
	if ($conf['sso_cookie'] && !empty($_COOKIE[$conf['sso_cookie']])) {
		$sid = $_COOKIE[$conf['sso_cookie']];
		$ssoauth = $conf['sso_service'] . ":" . $conf['sso_password'];

		$message = new XML_RPC_Message('sso.getuserinfo_byusername',
					array(new XML_RPC_Value($ssoauth, 'string'),
						new XML_RPC_Value($username, 'string')));

		$server = new XML_RPC_Client($conf['sso_path'], "https://$conf[sso_host]");
		$result = $server->send($message, 0);

		if (is_object($result) && ($result->faultCode() === 0)) {
			$info = XML_RPC_decode($result->value());
			return $info;
		}
	}

	$info = array();
	return $info;
}

/**
 *	Retrieve data based on a unique field using ldap_search.
 *
 *	<ul>
 *		<li>$sf should be a unique field (username,osuuid preferred)</li>	
 *		<li>don't use something like last name or anything else that will return more than _one_ result</li>	
 *		<li>if there is no match, function will return FALSE</li>	
 *		<li>you should check the type of the result before using it as an array</li>
 *		<li>you will receive a flattened associative array with all LDAP information for that user</li> 
 *		<li>Note that fields with line breaks are delimited with $</li>
 *	</ul>
 *
 *	Use this function to check if a user exists based on a defined parameter:
 *	if (sso_ldap_get_record('morgamic','uid'))
 *	{
 *		echo 'user exists';
 *	}
 *	else
 *	{
 *		echo 'user does not exist';
 *	}
 *
 *	Return array format:
 *	<code>
 *		Array
 *		(
 *			[osuofficeaddress] => Media Services-Central Web$The Valley Library$Corvallis, OR 97331
 *			[facsimiletelephonenumber] => 1 541 737 8224
 *			[sn] => Morgan
 *			[cn] => Morgan, Michael Joseph
 *			[givenname] => Michael
 *			[osuprimaryaffiliation] => E
 *			[objectclass] => top
 *			[uid] => morgamic
 *			[osuuid] => 15791281312
 *			[mail] => mike.morgan@oregonstate.edu
 *			[title] => Analyst Programmer
 *			[telephonenumber] => 1 541 737 7281
 *			[osudepartment] => Media Services
 *			[postaladdress] => Media Services$Oregon State University$109 Kidder Hall$Corvallis, OR 97331-4604
 *			[count] => 
 *			[dn] => o
 *		)	
 *	</code>
 *
 *	@param string $search_val value of search field
 *	@param string $search_field name of field to search by; osuuid by default
 *	@param array $fields array of desired fields 
 *	@param string $ldap_server address/ip of ldap server 
 *	@param string $dn base DN
 *	@return array|false $info array containing search result (first record of result), FALSE if no results are acquired, or if error occurs
 */
function sso_ldap_get_record($search_value, $search_field = 'osuuid',
				$lds = 'directory.oregonstate.edu', $dn = 'ou=people,o=orst.edu')
{
	$filter = $search_field.'='.$search_value;
	$conn = @ldap_connect($lds);
	if ( is_resource($conn) ) {
		$bind = @ldap_bind($conn);
		$res = @ldap_search($conn,$dn,$filter);
		$record = @ldap_get_entries($conn,$res);
		ldap_close($conn);
        if ( is_array($record) && ($record['count'] > 0)) { // (clouserw) Triggered if $search_value doesn't exist
            if ( is_array($record[0]) ) {
                foreach ($record[0] as $key=>$val) {
                    if ( !is_numeric($key) ) {
                        $info[$key] = $val[0];
                    }
                }
                return $info;
            }
        }
	}	
	return false;
}

/*****************************************************************************
 * END SSO AUTHENTICATION AND SESSION HANDLING FUNCTIONS
 *****************************************************************************/


/**
 *  Automagic authentication/session handling
 */

/* Disable auto configuration and initialization to
 * encourage repository checkouts to be manually configured
 * via external configuration. If distributing this library
 * outside of CWS, you should uncomment this block as well
 * as the block of auto configuration code at the beginning

$sso_site_config = sso_config($sso_site_config);
if ($sso_site_config['auto']) {
	if ($sso_site_config['logout_page'] &&
		preg_match('/'.preg_quote($sso_site_config['logout_page'], '/').'$/', $_SERVER['PHP_SELF'])) {
		// don't redirect on invalid auth, because this is the logout page
		sso_authenticate(false);

	} else {
		// this will redirect to login page if sso session is not set or invalid
		sso_authenticate();
	}

	if ($sso_site_config['sess_enable']) {
		sso_session_check();
		sso_session_fill_userinfo();
	}
}

***** end auto initialization block comment *****/

?>
