<?php

namespace Example\Controllers;

use Example\Vue\Vue;

class Error
{

    private $eg;  # Reference (and URL) for this example

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct($page)
    {
      $this->eg = ucfirst($page);
    }

    public function showNotFoundPage(){
        $shower = new Vue($this->eg);
        $shower->generer([]);
    }

    public function showDocusignError($data)
    {
      $shower = new Vue($this->eg);
      $shower->generer($data);
    }

}
