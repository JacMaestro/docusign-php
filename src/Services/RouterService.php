<?php


namespace Example\Services;

use Example\Services\CodeGrantService;

class RouterService
{
    /**
     * The list of controllers for each example
     */
    public $authService;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->authService = new CodeGrantService();
    }

    /**
     * The list of controllers for each example
     */
    private const CONTROLLER = [
        'home' => 'Home',
        'error' => 'Error',
        'sigin_with_email' => 'SigningViaEmail',
        'envelope_info' => 'EnvelopeInfo',
    ];


    /**
     * Page router
     */
    public function router(): void
    {

        $page = $_GET['page'] ?? 'home';

        if ($page == 'home') {

          error_reporting(E_ALL & ~E_NOTICE);
          $controller = '\Example\Controllers\\' . $this->getController($page);
          $c = new $controller($page);
          $c->showHome();
          exit();

        }
        elseif ($page == 'dashboard') {
          error_reporting(E_ALL & ~E_NOTICE);
          $controller = '\Example\Controllers\\' . $this->getController('home');
          $c = new $controller($page);
          $c->showDashboard();
          exit();
        }
        else if ($page == 'send_envelope') {
          $method = $_SERVER['REQUEST_METHOD'];
          if ($method == 'GET') {
            $this->authService->flash('Sorry, this route is not available for get request ! Only Post is enabled.');
            header('Location: ' . $GLOBALS['app_url']);
            exit;
          };
          if ($method == 'POST') {
            error_reporting(E_ALL & ~E_NOTICE);
            $controller = '\Example\Controllers\\' . $this->getController('sigin_with_email');
            new $controller('home');
            exit();
          };
        }
        elseif ($page == 'must_authenticate') {
          $controller = '\Example\Controllers\\' . $this->getController('home');
          $c = new $controller('authentication');
          $c->showAuthentication(['back_url' => isset($_SESSION['eg']) ? $_SESSION['eg'] : $GLOBALS['app_url']]);
          exit();

        } elseif ($page == 'ds_login') {
            $this->ds_login(); // See below in oauth section
            exit();
      }
        else if ($page == 'ds_callback') {
            $this->ds_callback(); // See below in oauth section
            exit();
        }
        else if ($page == 'envelope') {
          error_reporting(E_ALL & ~E_NOTICE);
          $controller = '\Example\Controllers\\' . $this->getController('home');
          $c = new $controller($page);
          $c->showAnEnvelope($_GET['id']);
        }
        else if ($page == 'download') {
          $controller = '\Example\Controllers\\' . $this->getController('home');
          $c = new $controller($page);
          $c->downloadEnvelopeDocument($_GET['id'], $_GET['envelope_id']);
        }
        else {
          $controller = '\Example\Controllers\\' . $this->getController('error');
          $c = new $controller('notfound');
          $c->showNotFoundPage();
          exit();
        }

    }

    /**
     * @param int $buffer_min buffer time needed in minutes
     * @return boolean $ok true iff the user has an access token that will be good for another buffer min
     */
    function ds_token_ok($buffer_min = 10): bool
    {
        $ok = isset($_SESSION['ds_access_token']) && isset($_SESSION['ds_expiration']);
        $ok = $ok && (($_SESSION['ds_expiration'] - ($buffer_min * 60)) > time());
        return $ok;
    }

    /**
     * Called via a redirect from DocuSign authentication service
     */
    function ds_callback(): void
    {
        # Save the redirect eg if present
        $redirectUrl = false;
        if (isset($_SESSION['eg'])) {
            $redirectUrl = $_SESSION['eg'];
        }
        # reset the session
        $this->ds_logout_internal();
        $this->authService->authCallback($redirectUrl);
    }

    /**
     * DocuSign login handler
     */
    function ds_login(): void
    {
        $this->authService->login();
    }

    /**
     * Checker for the CSRF token
     */
    function check_csrf(): void
    {
        $this->authService->checkToken();
    }

    /**
     * Set flash for the current user session
     * @param $msg string
     */
    public function flash(string $msg): void
    {
        $this->authService->flash($msg);
    }

    /**
     * DocuSign logout handler
     */
    function ds_logout(): void
    {
        $this->ds_logout_internal();
        $this->flash('You have logged out from DocuSign.');
        header('Location: ' . $GLOBALS['app_url']);
        exit;
    }

    /**
     * Unset all items from the session
     */
    function ds_logout_internal(): void
    {
        if (isset($_SESSION['ds_access_token'])) {
            unset($_SESSION['ds_access_token']);
        }
        if (isset($_SESSION['ds_refresh_token'])) {
            unset($_SESSION['ds_refresh_token']);
        }
        if (isset($_SESSION['ds_user_email'])) {
            unset($_SESSION['ds_user_email']);
        }
        if (isset($_SESSION['ds_user_name'])) {
            unset($_SESSION['ds_user_name']);
        }
        if (isset($_SESSION['ds_expiration'])) {
            unset($_SESSION['ds_expiration']);
        }
        if (isset($_SESSION['ds_account_id'])) {
            unset($_SESSION['ds_account_id']);
        }
        if (isset($_SESSION['ds_account_name'])) {
            unset($_SESSION['ds_account_name']);
        }
        if (isset($_SESSION['ds_base_path'])) {
            unset($_SESSION['ds_base_path']);
        }
        if (isset($_SESSION['envelope_id'])) {
            unset($_SESSION['envelope_id']);
        }
        if (isset($_SESSION['eg'])) {
            unset($_SESSION['eg']);
        }
        if (isset($_SESSION['envelope_documents'])) {
            unset($_SESSION['envelope_documents']);
        }
        if (isset($_SESSION['template_id'])) {
            unset($_SESSION['template_id']);
        }
    }

    /**
     * Get Controller for the template example
     *
     * @param $eg
     * @return mixed
     */
    public function getController($eg)
    {
        return self::CONTROLLER[$eg];
    }
}
