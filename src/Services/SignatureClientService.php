<?php


namespace Example\Services;

use DocuSign\eSign\Api\AccountsApi;
use DocuSign\eSign\Api\EnvelopesApi;
use DocuSign\eSign\Api\GroupsApi;
use DocuSign\eSign\Api\TemplatesApi;
use DocuSign\eSign\Client\ApiClient;
use DocuSign\eSign\Client\ApiException;
use DocuSign\eSign\Configuration;

class SignatureClientService
{
    /**
     * DocuSign API Client
     */
    public $apiClient;

    /**
     * Router Service
     */
    public $routerService;

    /**
     * Create a new controller instance.
     *
     * @param $args
     * @return void
     */
    public function __construct($args)
    {
        # 2. Construct your API headers
        # Exceptions will be caught by the calling function
        $config = new Configuration();
        $config->setHost($args['base_path']);
        $config->addDefaultHeader('Authorization', 'Bearer ' . $args['ds_access_token']);
        $this->apiClient = new ApiClient($config);
        $this->routerService = new RouterService();
    }

    /**
     * Getter for the EnvelopesApi
     */
    public function getEnvelopeApi(): EnvelopesApi
    {
        return new EnvelopesApi($this->apiClient);
    }

    /**
     * Getter for the AccountsApi
     */
    public function getAccountsApi(): AccountsApi
    {
        return new AccountsApi($this->apiClient);
    }



    /**
     * Redirect user to the error page
     *
     * @param  ApiException $e
     * @return void
     */
    public function showErrorTemplate(ApiException $e): void
    {
        $body = $e->getResponseBody();
        $controller = '\Example\Controllers\\' . $this->routerService->getController('error');
        $c = new $controller('docusignError');
        $c->showDocusignError([
                'error_code' => $body->errorCode ?? unserialize($body)->errorCode,
                'error_message' => $body->message ?? unserialize($body)->message]
              );
        exit();
    }



    /**
     * Redirect user to the auth page
     *
     * @param $eg
     * @return void
     */
    public function needToReAuth($eg): void
    {
        // $this->routerService->flash('Sorry, you need to re-authenticate.');
        # We could store the parameters of the requested operation
        # so it could be restarted automatically.
        # But since it should be rare to have a token issue here,
        # we'll make the user re-enter the form data after
        # authentication.
        $_SESSION['eg'] = $GLOBALS['app_url'] . 'index.php?page=' . $eg;
        header('Location: ' . $GLOBALS['app_url'] . 'index.php?page=must_authenticate');
        exit;
    }



}
