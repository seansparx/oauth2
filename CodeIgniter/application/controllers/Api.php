<?php defined('BASEPATH') OR exit('No direct script access allowed');

// error reporting (this is a demo, after all!)
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Autoloading (composer is preferred, but for this example let's just do this)
require_once 'oauth2/src/OAuth2/Autoloader.php';

OAuth2\Autoloader::register();

/**
 * Class for Webservices ( Using oAuth2 )
 * 
 * @package Secure Webservice
 * @author Sean Sparx <sean@sparxitsolutions.com>
 * @date 09.05.2016
 */
class Api extends CI_Controller {

    private $dsn        = 'mysql:dbname=oauth2;host=localhost';
    private $username   = 'root';
    private $password   = 'sparx';
    
    private $storage    = null;
    private $server     = null;

    
    public function __construct() 
    {
        parent::__construct();

        $this->init();
    }

    
    /** 
     * Connect to database & add grant access types 
     * 
     * @return void
     */
    private function init() 
    {
        // $dsn is the Data Source Name for your database, for exmaple "mysql:dbname=my_oauth2_db;host=localhost"
        $this->storage = new OAuth2\Storage\Pdo(array('dsn' => $this->dsn, 'username' => $this->username, 'password' => $this->password));

        // Pass a storage object or array of storage objects to the OAuth2 server class
        $this->server = new OAuth2\Server($this->storage);

        // Add grant type.
        $this->server->addGrantType(new OAuth2\GrantType\ClientCredentials($this->storage));
        $this->server->addGrantType(new OAuth2\GrantType\AuthorizationCode($this->storage));
        $this->server->addGrantType(new OAuth2\GrantType\RefreshToken($this->storage, array('always_issue_new_refresh_token' => true)));
        $this->server->addGrantType(new OAuth2\GrantType\UserCredentials($this->storage));
    }
        
    
    /** 
     * Generate authorization code.
     * 
     * @param post array('response_type' => 'code', "client_id" => CLIENT_ID, "state" => "xyz")
     * 
     * @return string
     */
    public function authorize() 
    {
        $request = OAuth2\Request::createFromGlobals();
        $response = new OAuth2\Response();

        // validate the authorize request
        if (! $this->server->validateAuthorizeRequest($request, $response)) {
            $response->send();
            die;
        }

        // display an authorization form
        if (empty($_POST)) {
            /* exit('
              <form method="post">
              <label>Do You Authorize TestClient?</label><br />
              <input type="submit" name="authorized" value="yes">
              <input type="submit" name="authorized" value="no">
              </form>'); */
        }

        $userid = 'U0012'; // A value on your server that identifies the user
        // print the authorization code if the user has authorized your client
        $is_authorized = true; //($_POST['authorized'] === 'yes');
        $this->server->handleAuthorizeRequest($request, $response, $is_authorized, $userid);

        if ($is_authorized) {
            // this is only here so that you get to see your code in the cURL request. Otherwise, we'd redirect back to the client
            $code = substr($response->getHttpHeader('Location'), strpos($response->getHttpHeader('Location'), 'code=') + 5, 40);
            exit($code);
        }

        $response->send();
    }

    
    /** 
     * Handle a request for an OAuth2.0 Access Token and send the response to the client.
     */
    public function token() 
    {
        $this->server->handleTokenRequest(OAuth2\Request::createFromGlobals())->send();
    }

    
    /** 
     * Handle a request to a resource and authenticate the access token.
     */
    public function resource() 
    {
        if (!$this->server->verifyResourceRequest(OAuth2\Request::createFromGlobals())) {
            $this->server->getResponse()->send();
            die;
        }

        $token = $this->server->getAccessTokenData(OAuth2\Request::createFromGlobals());
        $msg = "You have accessed my APIs!";
        echo json_encode(array('success' => true, 'message' => $msg, "response" => $token));
    }

}
