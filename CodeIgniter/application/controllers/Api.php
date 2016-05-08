<?php
defined('BASEPATH') OR exit('No direct script access allowed');

// error reporting (this is a demo, after all!)
	    ini_set('display_errors', 1);
	    error_reporting(E_ALL);

	    // Autoloading (composer is preferred, but for this example let's just do this)
	    require_once './oauth2-server/src/OAuth2/Autoloader.php';

class Api extends CI_Controller {

	public function __construct()
	{
		
	}


	public function index()
	{
		$this->authorize();
	}


	private function server()
	{
	    $dsn = 'mysql:dbname=oauth2;host=localhost';
	    $username = 'root';
	    $password = '#$5L479211420840';

	    
	    
	    OAuth2\Autoloader::register();

	    // $dsn is the Data Source Name for your database, for exmaple "mysql:dbname=my_oauth2_db;host=localhost"
	    $storage = new OAuth2\Storage\Pdo(array('dsn' => $dsn, 'username' => $username, 'password' => $password));

	    // Pass a storage object or array of storage objects to the OAuth2 server class
	    $server = new OAuth2\Server($storage);

	    // Add the "Client Credentials" grant type (it is the simplest of the grant types)
	    $server->addGrantType(new OAuth2\GrantType\ClientCredentials($storage));

	    // Add the "Authorization Code" grant type (this is where the oauth magic happens)
	    $server->addGrantType(new OAuth2\GrantType\AuthorizationCode($storage));

	    // Add the "Refresh Token" grant type (this is where the oauth magic happens)
	    $server->addGrantType(new OAuth2\GrantType\RefreshToken($storage, array('always_issue_new_refresh_token' => true ) ));

	    // Add the "User Credentials" grant type (this is where the oauth magic happens)
	    $server->addGrantType(new OAuth2\GrantType\UserCredentials($storage));
	}
	

	public function authorize()
	{
		$this->server();

		$request = OAuth2\Request::createFromGlobals();
    $response = new OAuth2\Response();

    // validate the authorize request
    if (!$server->validateAuthorizeRequest($request, $response)) {
        $response->send();
        die;
    }
    
    // display an authorization form
    if (empty($_POST)) {
        /*exit('
            <form method="post">
              <label>Do You Authorize TestClient?</label><br />
              <input type="submit" name="authorized" value="yes">
              <input type="submit" name="authorized" value="no">
            </form>');*/
    }
    
    $userid = 'U0012'; // A value on your server that identifies the user
    // print the authorization code if the user has authorized your client
    $is_authorized = true; //($_POST['authorized'] === 'yes');
    $server->handleAuthorizeRequest($request, $response, $is_authorized, $userid);
    
    if ($is_authorized) {
        // this is only here so that you get to see your code in the cURL request. Otherwise, we'd redirect back to the client
        $code = substr($response->getHttpHeader('Location'), strpos($response->getHttpHeader('Location'), 'code=') + 5, 40);
        exit($code);
    }
    
    $response->send();
	}

	public function token()
	{

	}
}
