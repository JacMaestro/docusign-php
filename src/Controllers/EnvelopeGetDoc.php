<?php
/**
 * Example 007: Get an envelope's document
 */

namespace Example\Controllers;

use DocuSign\eSign\Client\ApiException;
use Example\Services\SignatureClientService;
use Example\Services\RouterService;
use DocuSign\eSign\Model\EnvelopeDocumentsResult;


class EnvelopeGetDoc
{
    /** signatureClientService */
    private $clientService;

    /** RouterService */
    private $routerService;

    /** Specific template arguments */
    private $args;

    /** Envelope id */
    private $envelope_id;

    private $eg ;  # reference (and url) for this example

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct($eg, $envelope_id)
    {
        $this->eg = $eg;
        $this->envelope_id = $envelope_id;
        $this->args = $this->getEnvelopeArgs();
        $this->clientService = new SignatureClientService($this->args);
        $this->routerService = new RouterService();
        $this->getDocument();
    }

    /**
     * 1. Check the token and check we have an envelope_id
     * 2. Call the worker method
     *
     * @return void
     * @throws ApiException for API problems and perhaps file access \Exception too.
     */
    public function getDocument(): void
    {

        $envelope_documents = $this->getEnvelopeDocuments();

        //  Choose only one because, only one document is sent
        //  Change id in  $envelope_documents['documents'][{id}]['document_id'] to
        //  select type of document to download
        //  0 and 2 for document pdf and 3 for a zip who contains
        //  the summary and the document signed
        $document_id = $envelope_documents['documents'][2]['document_id'];

        // if your envelope contains many documents
        // foreach ($args['envelope_documents']['documents'] as $item) {
        //     if ($item['document_id'] ==  $args['document_id']) {
        //         $doc_item = $item;
        //         break;
        //     }
        // }

        $this->args = $this->getDocumentArgs($envelope_documents, $document_id);

        $minimum_buffer_min = 3;
        $token_ok = $this->routerService->ds_token_ok($minimum_buffer_min);
        if ($token_ok && $this->envelope_id && $envelope_documents) {
            # 2. Call the worker method
            # More data validation would be a good idea here
            # Strip anything other than characters listed
            $results = $this->sendGetDocumentRequest($this->args);

            if ($results) {
                # See https://stackoverflow.com/a/27805443/64904
                header("Content-Type: {$results['mimetype']}");
                header("Content-Disposition: attachment; filename=\"{$results['doc_name']}\"");
                ob_clean();
                flush();
                $file_path = $results['data']->getPathname();
                readfile($file_path);
                exit();
            }
        } elseif (! $token_ok) {
            $this->clientService->needToReAuth($this->eg);
        } elseif (! $this->envelope_id || ! $envelope_documents) {
            $this->showError('Something goes wrong. Try again please.');
        }
    }


    /**
     * Do the work of the example
     * 1. Call the envelope documents list method
     *
     * @param  $args array
     * @return array
     * @throws ApiException for API problems and perhaps file access \Exception too.
     */
    # ***DS.snippet.0.start
    private function sendGetDocumentRequest(array $args): array
    {
        # 1. call API method
        # Exceptions will be caught by the calling function
        $envelope_api = $this->clientService->getEnvelopeApi();

        # An SplFileObject is returned. See http://php.net/manual/en/class.splfileobject.php
        $temp_file = $envelope_api->getDocument($args['account_id'],  $args['document_id'], $this->envelope_id);
        # find the matching document information item
        $doc_item = false;
        foreach ($args['envelope_documents']['documents'] as $item) {
            if ($item['document_id'] ==  $args['document_id']) {
                $doc_item = $item;
                break;
            }
        }

        $doc_name = $doc_item['name'];
        $has_pdf_suffix = strtoupper(substr($doc_name, -4)) == '.PDF';
        $pdf_file = $has_pdf_suffix;
        # Add ".pdf" if it's a content or summary doc and doesn't already end in .pdf
        if ($doc_item["type"] == "content" || ($doc_item["type"] == "summary" && ! $has_pdf_suffix)) {
            $doc_name .= ".pdf";
            $pdf_file = true;
        }
        # Add .zip as appropriate
        if ($doc_item["type"] == "zip") {
            $doc_name .= ".zip";
        }

        # Return the file information
        if ($pdf_file) {
            $mimetype = 'application/pdf';
        } elseif ($doc_item["type"] == 'zip') {
            $mimetype = 'application/zip';
        } else {
            $mimetype = 'application/octet-stream';
        }

        return ['mimetype' => $mimetype, 'doc_name' => $doc_name, 'data' => $temp_file];
    }
    # ***DS.snippet.0.end

    /**
     * Get specific Document arguments
     *
     * @return array
     */
    private function getDocumentArgs($envelope_documents, $document_id): array
    {

        // $document_id  = preg_replace('/([^\w \-\@\.\,])+/', '', $document_id);
        $args = [
            'account_id' => $_SESSION['ds_account_id'],
            'base_path' => $_SESSION['ds_base_path'],
            'ds_access_token' => $_SESSION['ds_access_token'],
            'envelope_id' => $envelope_documents['envelope_id'],
            'document_id' => $document_id,
            'envelope_documents' => $envelope_documents
        ];

        return $args;
    }

    /**
     * Do the work of the example
     * 1. Call the envelope documents list method
     *
     * @param  $args array
     * @return EnvelopeDocumentsResult
     * @throws ApiException for API problems and perhaps file access \Exception too.
     */
    # ***DS.snippet.0.start
    private function getDocumentOfEnvelope(array $args): EnvelopeDocumentsResult
    {
        # 1. call API method
        # Exceptions will be caught by the calling function
        $envelope_api = $this->clientService->getEnvelopeApi();
        try {
            $results = $envelope_api->listDocuments($args['account_id'], $this->envelope_id);
        } catch (ApiException $e) {
            $this->clientService->showErrorTemplate($e);
            exit;
        }

        return $results;
    }


    # ***DS.snippet.0.end

    /**
     * Get specific Envelope arguments
     *
     * @return array
     */
    private function getEnvelopeArgs(): array
    {
        $args = [
            'account_id' => $_SESSION['ds_account_id'],
            'base_path' => $_SESSION['ds_base_path'],
            'ds_access_token' => $_SESSION['ds_access_token'],
        ];

        return $args;
    }

    /**
     * 1. Check the token and check we have an envelope_id
     * 2. Call the worker method
     *
     * @return array
     * @throws ApiException for API problems and perhaps file access \Exception too.
     */
    public function getEnvelopeDocuments(): array
    {
        $minimum_buffer_min = 3;

        $token_ok = $this->routerService->ds_token_ok($minimum_buffer_min);

        if ($token_ok && $this->envelope_id) {
            # 2. Call the worker method
            $results = $this->getDocumentOfEnvelope($this->getEnvelopeArgs());

            if ($results) {
                # results is an object that implements ArrayAccess. Convert to a regular array:
                $results = json_decode((string)$results, true);


                # Save the envelope_id and its list of documents in the session so
                # they can be used in example 7 (download a document)
                $standard_doc_items = [
                    ['name' => 'Combined'   , 'type' => 'content', 'document_id' => 'combined'],
                    ['name' => 'Zip archive', 'type' => 'zip'    , 'document_id' => 'archive']];
                # The certificate of completion is named "summary".
                # We give it a better name below.
                $map_documents = function ($doc) {
                    if ($doc['documentId'] == "certificate") {
                        $new = ['document_id' => $doc['documentId'], 'name' => "Certificate of completion",
                                'type' => $doc['type']];
                    } else {
                        $new = ['document_id' => $doc['documentId'], 'name' => $doc['name'], 'type' => $doc['type']];
                    }
                    return $new;
                };
                $envelope_doc_items = array_map($map_documents, $results['envelopeDocuments']);

                $documents = array_merge($standard_doc_items, $envelope_doc_items);

                return ['documents' => $documents];
            }
        } elseif (! $token_ok) {
            $this->clientService->needToReAuth($this->eg);
        } elseif (! $this->envelope_id) {
            $this->showError('Sorry, you need to chose an envelope first.');
        }
    }

    /**
     *
     * Display error message
     * @return void
     *
     */
    public function showError($msg) : void
    {
      $this->routerService->flash($msg);
      header('Location: ' . $GLOBALS['app_url'] . 'index.php?page=dashboard');
      exit;
    }
}
