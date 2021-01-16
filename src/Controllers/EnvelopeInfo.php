<?php
/**
 * Example 004: Get an envelope's basic information and status
 */

namespace Example\Controllers;

use DocuSign\eSign\Client\ApiException;
use DocuSign\eSign\Model\Envelope;
use Example\Services\SignatureClientService;
use Example\Services\RouterService;
use Example\Vue\Vue;
use Example\Modele\Envelope as EnvelopeModel;


class EnvelopeInfo
{
    /** signatureClientService */
    private $clientService;

    /** RouterService */
    private $routerService;

    /** Specific template arguments */
    private $args;

    private $eg;  # reference (and url) for this example

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct($eg, $envelope_id)
    {
        $this->eg = $eg;
        $this->args = $this->getTemplateArgs($envelope_id);
        $this->clientService = new SignatureClientService($this->args);
        $this->routerService = new RouterService();
        $this->showEnvelopeInfo();
    }

    /**
     * 1. Check the token and check we have an envelope_id
     * 2. Call the worker method
     *
     * @return void
     * @throws ApiException for API problems and perhaps file access \Exception too.
     */
    public function showEnvelopeInfo(): void
    {
        $minimum_buffer_min = 3;
        $envelope_id= $this->args['envelope_id'];
        $token_ok = $this->routerService->ds_token_ok($minimum_buffer_min);

        if ($token_ok && $envelope_id) {
            # 2. Call the worker method
            # More data validation would be a good idea here
            # Strip anything other than characters listed
            $results = $this->worker($this->args);

            if ($results) {
                # results is an object that implements ArrayAccess. Convert to a regular array:
                $results = json_decode((string)$results, true);

                //  Update envelope state
                $model = new EnvelopeModel();

                // A comma-separated list of current envelope statuses to included in the response. Possible values are:
                //
                // completed
                // created
                // declined
                // deleted
                // delivered
                // processing
                // sent
                // signed
                // timedout
                // voided
                // The any value is equivalent to any status.

                $model->updateState($results['status'], $envelope_id);

                //  Show view
                $view = new Vue('Envelope');
                $view->generer(['envelope' => $model->getOneByEnvelopeId($envelope_id)]);
                exit;
              }
        } elseif (! $token_ok) {
            $this->clientService->needToReAuth($this->eg);
        } elseif (! $envelope_id) {
            $this->routerService->flash('Sorry, you need to chose an envelope first.');
            header('Location: ' . $GLOBALS['app_url'] . 'index.php?page=dashboard');
            exit;
        }
    }


    /**
     * Do the work of the example
     * 1. Get the envelope's data
     *
     * @param  $args array
     * @return Envelope
     * @throws ApiException for API problems and perhaps file access \Exception too.
     */
    # ***DS.snippet.0.start
    private function worker(array $args): Envelope
    {
        # 1. call API method
        # Exceptions will be caught by the calling function
        $envelope_api = $this->clientService->getEnvelopeApi();
        try {
            $results = $envelope_api->getEnvelope($args['account_id'], $args['envelope_id']);
        } catch (ApiException $e) {
            $this->clientService->showErrorTemplate($e);
            exit;
        }

        return $results;
    }
    # ***DS.snippet.0.end

    /**
     * Get specific template arguments
     *
     * @return array
     */
    private function getTemplateArgs($envelope_id): array
    {

        $args = [
            'account_id' => $_SESSION['ds_account_id'],
            'base_path' => $_SESSION['ds_base_path'],
            'ds_access_token' => $_SESSION['ds_access_token'],
            'envelope_id' => $envelope_id
        ];

        return $args;
    }
}
