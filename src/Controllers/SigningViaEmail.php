<?php
/**
 * Example 002: Remote signer, cc, envelope has three documents
 */

namespace Example\Controllers;

use DocuSign\eSign\Client\ApiException;
use DocuSign\eSign\Model\CarbonCopy;
use DocuSign\eSign\Model\Document;
use DocuSign\eSign\Model\EnvelopeDefinition;
use DocuSign\eSign\Model\Recipients;
use DocuSign\eSign\Model\Signer;
use DocuSign\eSign\Model\SignHere;
use DocuSign\eSign\Model\Tabs;
use Example\Services\SignatureClientService;
use Example\Services\RouterService;
use Example\Modele\Envelope;

class SigningViaEmail
{
    /** signatureClientService */
    private $clientService;

    /** RouterService */
    private $routerService;

    /** Specific template arguments */
    private $args;

    private $eg = "home";  # reference (and url) for this example

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct($eg)
    {
        $this->eg = $eg;
        $this->args = $this->getEnvelopeArgs();
        $this->clientService = new SignatureClientService($this->args);
        $this->routerService = new RouterService();
        $method = $_SERVER['REQUEST_METHOD'];
        if ($method == 'GET') {
            $this->getController($eg, $routerService);
        };
        if ($method == 'POST') {
            $this->routerService->check_csrf();
            $this->sendEnvelope();
        };
    }

    /**
     * Show the example's form page
     *
     * @param $eg string
     * @param $routerService RouterService
     * @return void
     */
    private function getController(
        string $eg,
        $routerService
    ): void
    {
        if ($routerService->ds_token_ok()) {

            header('Location: ' . $GLOBALS['app_url'] . 'index.php?page=dashboard');
            exit;

        } else {
            # Save the current operation so it will be resumed after authentication
            $_SESSION['eg'] = $GLOBALS['app_url'] . 'index.php?page=' . $eg;
            header('Location: ' . $GLOBALS['app_url'] . 'index.php?page=must_authenticate');
            exit;
        }
    }

    /**
     * 1. Check the token
     * 2. Call the worker method
     * 3. Redirect the user to the signing
     *
     * @return void
     * @throws ApiException for API problems and perhaps file access \Exception too.
     */
    public function sendEnvelope(): void
    {
        $minimum_buffer_min = 3;
        if ($this->routerService->ds_token_ok($minimum_buffer_min)) {
            # 2. Call the worker method
            # More data validation would be a good idea here
            # Strip anything other than characters listed
            $results = $this->worker($this->args);

            if ($results) {

                // Save info to db
                $model = new Envelope();

                $to_save = [
                  'account_id' => $this->args['account_id'],
                  'envelope_id' => $results["envelope_id"],
                  'file_name' => $this->args['envelope_args']['doc']['name'],
                  'file_link' => $this->args['envelope_args']['doc']['link'],
                  'signer_email' =>$this->args['envelope_args']['signer_email'],
                   'signer_name' => $this->args['envelope_args']['signer_name'],
                   'cc_email' => $this->args['envelope_args']['cc_email'],
                   'cc_name' => $this->args['envelope_args']['cc_name'],
                   'status' => $this->args['envelope_args']['status']
                ];

                $model->save($to_save);

                header('Location: ' . $GLOBALS['app_url'] . 'index.php?page=dashboard');
                exit;
            }
            else {
              $this->routerService->flash("Sorry, something goes wrong. Try again please.");
              header('Location: ' . $GLOBALS['app_url'] . 'index.php?page=dashboard');
              exit;
            }
        } else {
            $this->clientService->needToReAuth($this->eg);
        }
    }


    /**
     * Do the work of the example
     * 1. Create the envelope request object
     * 2. Send the envelope
     *
     * @param  $args array
     * @return array ['redirect_url']
     * @throws ApiException for API problems and perhaps file access \Exception too.
     */
    # ***DS.snippet.0.start
    public function worker($args): array
    {
        # 1. Create the envelope request object
        $envelope_definition = $this->make_envelope($args["envelope_args"]);
        $envelope_api = $this->clientService->getEnvelopeApi();

        # 2. call Envelopes::create API method
        # Exceptions will be caught by the calling function
        try {
            $results = $envelope_api->createEnvelope($args['account_id'], $envelope_definition);
        } catch (ApiException $e) {
            $this->clientService->showErrorTemplate($e);
            exit;
        }

        return ['envelope_id' => $results->getEnvelopeId()];
    }

    /**
     * Creates envelope definition
     * Document 1: An HTML document.
     * Document 2: A Word .docx document.
     * Document 3: A PDF document.
     * DocuSign will convert all of the documents to the PDF format.
     * The recipients' field tags are placed using <b>anchor</b> strings.
     *
     * Parameters for the envelope: signer_email, signer_name, signer_client_id
     *
     * @param  $args array
     * @return EnvelopeDefinition -- returns an envelope definition
     */
    private function make_envelope(array $args): EnvelopeDefinition
    {
        # document 1 (html) has sign here anchor tag **signature_1**
        # document 2 (docx) has sign here anchor tag /sn1/
        # document 3 (pdf)  has sign here anchor tag /sn1/
        #
        # The envelope has two recipients.
        # recipient 1 - signer
        # recipient 2 - cc
        # The envelope will be sent first to the signer.
        # After it is signed, a copy is sent to the cc person.
        #
        # create the envelope definition
        $envelope_definition = new EnvelopeDefinition([
           'email_subject' => 'Please sign this document set'
        ]);

        # read file from a local directory
        # The reads could raise an exception if the file is not available!

        $content_bytes = file_get_contents($args['doc']['link']);
        $doc_b64 = base64_encode($content_bytes);

        # Create the document models
        $document = new Document([  # create the DocuSign document object
            'document_base64' => $doc_b64,
            'name' => $args['doc']['name'],  # can be different from actual file name
            'file_extension' => $args['doc']['type'],  # many different document types are accepted
            'document_id' => strval(mt_rand(1,100))  # a label used to reference the doc,
        ]);


        # The order in the docs array determines the order in the envelope
        # You can use insert more document at same time
        $envelope_definition->setDocuments([$document]);


        # Create the signer recipient model
        $signer = new Signer([
            'email' => $args['signer_email'], 'name' => $args['signer_name'],
            'recipient_id' => "1", 'routing_order' => "1"]);
        # routingOrder (lower means earlier) determines the order of deliveries
        # to the recipients. Parallel routing order is supported by using the
        # same integer as the order for two or more recipients.

        # create a cc recipient to receive a copy of the documents
        $cc = new CarbonCopy([
            'email' => $args['cc_email'], 'name' => $args['cc_name'],
            'recipient_id' => "2", 'routing_order' => "2"]);

        # Create signHere fields (also known as tabs) on the documents,
        # We're using anchor (autoPlace) positioning
        #
        # The DocuSign platform searches throughout your envelope's
        # documents for matching anchor strings. So the
        # signHere tab will be used to anchor signer signature
        # But it is not required for signer to sign the document
        $sign_here = new SignHere([
            'anchor_string' => '/sn1/', 'anchor_units' =>  'pixels',
            'anchor_y_offset' => '10', 'anchor_x_offset' => '20']);

        # Add the tabs model (including the sign_here tabs) to the signer
        # The Tabs object wants arrays of the different field/tab types
        $signer->setTabs(new Tabs([
            'sign_here_tabs' => [$sign_here]]));

        # Add the recipients to the envelope object
        $recipients = new Recipients([
            'signers' => [$signer], 'carbon_copies' => [$cc]]);
        $envelope_definition->setRecipients($recipients);

        # Request that the envelope be sent by setting |status| to "sent".
        # To request that the envelope be created as a draft, set to "created"
        $envelope_definition->setStatus($args["status"]);

        return $envelope_definition;
    }
    # ***DS.snippet.0.end

    /**
     * Get specific envelope arguments
     *
     * @return array
     */
    private function getEnvelopeArgs(): array
    {
        $signer_name  = preg_replace('/([^\w \-\@\.\,])+/', '', $_POST['signer_name' ]);
        $signer_email = preg_replace('/([^\w \-\@\.\,])+/', '', $_POST['signer_email']);
        $cc_name      = preg_replace('/([^\w \-\@\.\,])+/', '', $_POST['cc_name'     ]);
        $cc_email     = preg_replace('/([^\w \-\@\.\,])+/', '', $_POST['cc_email'    ]);
        $envelope_args = [
            'signer_email' => $signer_email,
            'signer_name' => $signer_name,
            'cc_email' => $cc_email,
            'cc_name' => $cc_name,
            'status' => 'sent',
            'doc' => $this->getEnvelopeDoc()
        ];
        $args = [
            'account_id' => $_SESSION['ds_account_id'],
            'base_path' => $_SESSION['ds_base_path'],
            'ds_access_token' => $_SESSION['ds_access_token'],
            'envelope_args' => $envelope_args
        ];

        return $args;
    }

    /**
     * Get envelope documents
     *
     * @return array
     */

    private function getEnvelopeDoc(): array
    {
      $target_dir = $_SERVER['DOCUMENT_ROOT']. "/" . "uploads/";
      $target_file = $target_dir . basename($_FILES["fileToUpload"]["name"]);
      $uploadOk = 1;
      $imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));

      // Check if file already exists
      if (file_exists($target_file)) {
        return [
          'link' => $target_file,
          'name' => basename($_FILES["fileToUpload"]["name"]),
          'type' => $imageFileType,
         ];
      }

       // Check file size
      if ($_FILES["fileToUpload"]["size"] > 500000) {
        $this->routerService->flash("Sorry, your file is too large.");
        $uploadOk = 0;
       }

      // Allow certain file formats
      if($imageFileType != "pdf") {
        $this->routerService->flash("Sorry, only PDF files are allowed.");
        $uploadOk = 0;
      }

      // Check if $uploadOk is set to 0 by an error
      if ($uploadOk == 0) {
        $this->routerService->flash("Sorry, your file was not uploaded.");
        header('Location: ' . $GLOBALS['app_url'] . 'index.php?page=' . $this->eg);
        exit;
      // if everything is ok, try to upload file
      } else {

          if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file)) {

            return [
              'link' => $target_file,
              'name' => basename($_FILES["fileToUpload"]["name"]),
              'type' => $imageFileType,
             ];

          } else {

            $this->routerService->flash("Sorry, there was an error uploading your file.");
            header('Location: ' . $GLOBALS['app_url'] . 'index.php?page=' . $this->eg);
            exit;
          }
      }
    }
}
