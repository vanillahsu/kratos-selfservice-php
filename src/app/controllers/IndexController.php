<?php
declare(strict_types=1);

require_once BASE_PATH . '/../vendor/autoload.php';
require_once APP_PATH . '/library/helper.php';

use GuzzleHttp\Client;
use Ory\Kratos\Client\Api\FrontendApi;
use Ory\Kratos\Client\Configuration;
use Ory\Kratos\Client\Model\UiNodeAttributes;
use Ory\Kratos\Client\Model\UiNodeInputAttributes;

class IndexController extends ControllerBase
{
    public function errorAction()
    {
        $id = $this->request->get('id');
        if ($id) {
            $config = Configuration::getDefaultConfiguration()->setHost(
                $this->config->kratos->api_host
            );
            $client = new Client;
            $frontend_api = new FrontendApi($client, $config);
            $result = $frontend_api->getFlowError($id);
            $this->view->error = $result->getError()["reason"];
        }
    }

    public function indexAction()
    {

    }

    public function loginAction()
    {
        $config = Configuration::getDefaultConfiguration()->setHost(
            $this->config->kratos->api_host
        );
        $client = new Client;
        $frontend_api = new FrontendApi($client, $config);
        try {
        } catch (Exception $e) {
            print_r($e);
        }
    }

    public function registrationAction()
    {
        $flow = $this->request->get('flow');
        $return_to = $this->request->get('return_to');
        $after_verification_return_to = $this->request->get(
            'after_verification_return_to'
        );
        $login_challenge = $this->request->get('login_challenge');
        $organization = $this->request->get('organization');

        $params = array();
        if ($return_to) {
            $tmp_str = "return_to=$return_to";
            array_push($params, $tmp_str);
        }

        if ($after_verification_return_to) {
            $tmp_str = "after_verification_return_to=$after_verification_return_to";
            array_push($params, $tmp_str);
        }
        
        if ($organization) {
            $tmp_str = "organization=$organization";
            array_push($params, $tmp_str);
        }

        if ($login_challenge) {
            $tmp_str = "login_challenge=$login_challenge";
            array_push($params, $tmp_str);
        }

        $redirect_url = getUrlForFlow(
            $this->config->kratos->api_host,
            'registration',
            $params
        );
        $this->logger->debug($redirect_url);

        if (!$flow) {
            return $this->response->redirect($redirect_url, true, 303);
        }

        $config = Configuration::getDefaultConfiguration()->setHost(
            $this->config->kratos->browser_host
        );
        $client = new Client;
        $frontend_api = new FrontendApi($client, $config);
        try {
            $cookie = $this->request->getHeader('Cookie');

            $result = $frontend_api->getRegistrationFlow($flow, $cookie);
            $this->view->ui = $result->getUi();
        } catch (Exception $e) {
            print_r($e);
            $this->logger->debug($e);
            #    return $this->response->redirect($redirect_url, true, 303);
        }
    }
}

# vim: set et sw=4 sts=4 ts=4:
