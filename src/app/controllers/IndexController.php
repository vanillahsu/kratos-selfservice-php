<?php declare(strict_types=1);

/**
 * IndexController
 */

require_once APP_PATH . '/library/helper.php';

use GuzzleHttp\Client;
use Ory\Hydra\Client\Api\OAuth2Api;
use Ory\Hydra\Client\Configuration as HConfiguration;
use Ory\Hydra\Client\Model\AcceptOAuth2ConsentRequest;
use Ory\Hydra\Client\Model\AcceptOAuth2ConsentRequestSession;
use Ory\Kratos\Client\ApiException;
use Ory\Kratos\Client\Api\FrontendApi;
use Ory\Kratos\Client\Configuration as KConfiguration;
use Ory\Kratos\Client\Model\UiNodeAttributes;
use Ory\Kratos\Client\Model\UiNodeInputAttributes;
use Ory\Kratos\Client\Model\UiText;

class IndexController extends ControllerBase
{
    public function initialize()
    {
        $client = new Client();

        $this->frontend_api = new FrontendApi(
            $client,
            KConfiguration::getDefaultConfiguration()->setHost(
                $this->config->kratos->browser_url
            )
        );
        $this->oauth2_client = new OAuth2Api(
            $client,
            HConfiguration::getDefaultConfiguration()->setHost(
                $this->config->kratos->sdk_url
            )
        );
    }

    public function consentGetAction()
    {
        $consent_challenge = $this->request->get('consent_challenge');
        if (!$consent_challenge) {
            $this->logger->debug(
                'Expected a consent challenge to be set but received none.'
            );

            return;
        }

        try {
            $result = $this->oauth2_client->getOAuth2ConsentRequest(
                $consent_challenge
            );
            $grant_scope = $result->getRequestedScope();
            $grant_access_token_audience = $result->
                getRequestedAccessTokenAudience();
            $accept_oauth2_consent_request = new AcceptOAuth2ConsentRequest();
            $accept_oauth2_consent_request->setGrantScope($grant_scope);
            $accept_oauth2_consent_request->setGrantAccessTokenAudience(
                $grant_access_token_audience
            );
            try {
                $body = $this->oauth2_client->acceptOAuth2ConsentRequest(
                    $consent_challenge,
                    $accept_oauth2_consent_request
                );
                $this->response->redirect($body->getRedirectTo());
            } catch (Exception $e) {
                print($e);
                $this->logger->debug($e);
            }
        } catch (Exception $e) {
            print($e);
            $this->logger->debug($e);
        }
    }

    public function errorAction()
    {
        $id = $this->request->get('id');
        if ($id) {
            $result = $this->frontend_api->getFlowError($id);
            $this->view->error = $result->getError()["reason"];
        }
    }

    public function indexAction()
    {

    }

    public function loginAction()
    {
        $flow = $this->request->get('flow');
        $aal = $this->request->get('aal') ?? '';
        $refresh = $this->request->get('refresh') ?? '';
        $return_to = $this->request->get('return_to') ?? '';
        $organization = $this->request->get('organization') ?? '';
        $via = $this->request->get('via') ?? '';
        $login_challenge = $this->request->get('login_challenge');

        $params = array(
            "aal=$aal",
            "refresh=$refresh",
            "return_to=$return_to",
            "organization=$organization",
            "via=$via"
        );

        if ($login_challenge) {
            $tmp_str = "login_challenge=$login_challenge";
            array_push($params, $tmp_str);
        }

        $redirect_url = getUrlForFlow(
            $this->config->kratos->browser_url,
            'login',
            $params
        );

        if (!$flow) {
            $this->logger->debug(
                "No flow ID found in URL query initializing login flow"
            );
            return $this->response->redirect($redirect_url, true, 303);
        }

        try {
            $cookie = $this->request->getHeader('Cookie');
            $result = $this->frontend_api->getLoginFlow($flow, $cookie);
            $ui = $result->getUi();
            $messages = $ui->getMessages();
            if ($return_to === '') {
                $return_to = $result->getReturnTo();
            }

            if ($messages && count($messages) > 0) {
                foreach ($messages as $message) {
                    if ($message->getId() === 4000010) {
                        return $this->_redirectToVerificationFlow(
                            $return_to,
                            $ui
                        );
                    }
                }
            }

            $registration_params = array("return_to=$return_to");
            if ($result->getOauth2LoginRequest()) {
                $challenge = $result->getOauth2LoginRequest()->getChallenge();
                array_push(
                    $registration_params,
                    "login_challenge=$challenge"
                );
            }

            $recovery_url = '';
            $registration_url = getUrlForFlow(
                $this->config->kratos->browser_url,
                'registration',
                $registration_params
            );

            if (!$result->getRefresh()) {
                $recovery_url = getUrlForFlow(
                    $this->config->kratos->browser_url,
                    'recovery',
                    array("return_to=$return_to")
                );
            }

            $this->view->data = ConvertToForm($ui);
        } catch (Exception $e) {
            return $this->_redirectOnSoftError($e, $redirect_url);
        }
    }

    public function recoveryAction()
    {
        $flow = $this->request->get('flow');
        $return_to = $this->request->get('return_to');

        $params = array("return_to=$return_to");
        $redirect_url = getUrlForFlow(
            $this->config->kratos->browser_url,
            'recovery',
            $params
        );

        if (!isset($flow)) {
            $this->logger->debug(
                "No flow ID found in URL query initializing login flow"
            );
            return $this->response->redirect($redirect_url, true, 303);
        }

        try {
            $cookie = $this->request->getHeader('Cookie');
            $result = $this->frontend_api->getRecoveryFlow($flow, $cookie);
            $ui = $result->getUi();

            if ($return_to == '') {
                $return_to = $result->getReturnTo();
            }

            $params = array(
                "return_to=$return_to"
            );

            $login_url = getUrlForFlow(
                $this->config->kratos->browser_url,
                'login',
                $params
            );
            $this->view->data = ConvertToForm($ui);
            $this->view->login_url = $login_url;
        } catch (Exception $e) {
            return $this->_redirectOnSoftError($e, $redirect_url);
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
            $this->config->kratos->browser_url,
            'registration',
            $params
        );

        if (!$flow) {
            $this->logger->debug(
                "No flow ID found in URL query initializing login flow"
            );
            return $this->response->redirect($redirect_url, true, 303);
        }

        try {
            $cookie = $this->request->getHeader('Cookie');
            $result = $this->frontend_api->getRegistrationFlow($flow, $cookie);
            $this->view->data = ConvertToForm($result->getUi());
        } catch (Exception $e) {
            return $this->_redirectOnSoftError($e, $redirect_url);
        }
    }

    public function verificationAction()
    {
        $flow = $this->request->get('flow');
        $return_to = $this->request->get('return_to') ?? '';
        $message = $this->request->get('message');

        $params = array("return_to=$return_to");

        $redirect_url = getUrlForFlow(
            $this->config->kratos->browser_url,
            'verification',
            $params
        );

        if (!isset($flow)) {
            $this->logger->debug(
                "No flow ID found in URL query initializing login flow"
            );
            return $this->response->redirect($redirect_url, true, 303);
        }

        try {
            $cookie = $this->request->getHeader('Cookie');
            $result = $this->frontend_api->getVerificationFlow($flow, $cookie);

            if ($return_to == '') {
                $return_to = $result->getReturnTo() ?? '';
            }

            $params = array("return_to=$return_to");
            $registration_url = getUrlForFlow(
                $this->config->kratos->browser_url,
                'registration',
                $params
            );

            if (isset($message)) {
                $m = json_decode($message);
                print_r($m);
            }

            $this->view->data = ConvertToForm($result->getUi());
        } catch (Exception $e) {
            return $this->_redirectOnSoftError($e, $redirect_url);
        }
    }

    public function welcomeAction()
    {
    }

    private function _getLogoutUrl(
        string $return_to
    ) {
        try {
            $cookie = $this->request->getHeader('Cookie');
            $result = $this->frontend_api->createBrowserLogoutFlow(
                $cookie,
                $return_to
            );
            return $result->getLogoutUrl();
        } catch (Exception $e) {
            $this->logger->debug('Unable to create logout URL: ' . $e->getMessage());
        }
    }

    private function _redirectToVerificationFlow(
        string $return_to,
        UiContainer $ui
    ) {
        try {
            [
                $result,
                $_,
                $headers
            ] = $this->frontend_api->createBrowserVerificationFlowWithHttpInfo(
                $return_to
            );

            if (array_key_exists('Set-Cookie', $headers)) {
                $this->cookies->set('Set-Cookie', $headers["Set-Cookie"]);
            }

            $verification_params = array(
                "flow=$result->getId()",
                "message=" . json_encode($ui->getMessages())
            );
            $redirect_url = getUrlForFlow('/', 'verification', $verification_params);

            return $this->response->redirect($redirect_url, false, 303);
        } catch (Exception $e) {
            $params = array("return_to=$return_to");
            $redirect_url = getUrlForFlow(
                $this->config->kratos->browser_url,
                'verification',
                $params
            );

            return $this->response->redirect($redirect_url, true, 303);
        }
    }

    private function _redirectOnSoftError(ApiException $e, $redirect_url)
    {
        $error = $e->getResponseObject()->getError();
        $code = $error->getCode();

        if ($code == 404 || $code == 410 || $code == 403) {
            if ($error->getId() == 'session_aal2_required') {
                // XXX need to handle with authenticatorAssuranceLevelError
            }
            return $this->response->redirect($redirect_url, true, 303);
        }
    }

    private function _extractSession($grant_scope)
    {
        $session = new AcceptOAuth2ConsentRequestSession();

        $identity = $this->session->get('identity');
        if (!$identity) {
            return $session;
        }

        if (in_array('email', $grant_scope)) {
            $addresses = $identity->getVerifiableAddresses();
            if (count($addresses) > 0) {
                $address = $addresses[0];
                if ($address->getVia() === 'email') {
                }
            }
        }
    }
}
// vim: set et sw=4 sts=4 ts=4:
