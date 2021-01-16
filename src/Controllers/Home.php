<?php

namespace Example\Controllers;

use Example\Vue\Vue;
use Example\Modele\Envelope;

class Home
{

    private $eg;  # Reference (and URL) for this example

    private $view;
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct($page)
    {
      $this->eg = ucfirst($page);
    }

    public function showHome(){
        $this->getView()->generer([]);
    }

    public function showDashboard(){
        $model = new Envelope();
        $this->getView()->generer(['envelopes' => $model->getAll()]);
    }

    public function showAuthentication($data){
        $this->getView()->generer($data);
    }

    public function showAnEnvelope($id)
    {
      $model = new Envelope();
      $envelope = $model->getOne($id);
      $controller = '\Example\Controllers\EnvelopeInfo';
      new $controller("envelope&id=". $_GET['id'], $envelope['envelope_id']);
    }

    public function downloadEnvelopeDocument($id, $envelope_id)
    {
      $controller = '\Example\Controllers\EnvelopeGetDoc';
      new $controller("envelope&id=". $id, $envelope_id);
    }

    public function getView()
    {
      return new Vue($this->eg);
    }

}
