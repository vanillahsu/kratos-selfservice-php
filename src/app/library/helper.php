<?php declare(strict_types=1);

use Ory\Kratos\Client\Model\UiContainer;
use Ory\Kratos\Client\Model\UiNodeAttributes;
use Ory\Kratos\Client\Model\UiNodeInputAttributes;
use Phalcon\Html\Escaper;
use Phalcon\Html\Helper\Button;
use Phalcon\Html\Helper\Close;
use Phalcon\Html\Helper\Form;
use Phalcon\Html\Helper\Img;
use Phalcon\Html\Helper\Input\Checkbox;
use Phalcon\Html\Helper\Input\DateTime;
use Phalcon\Html\Helper\Input\DateTimeLocal;
use Phalcon\Html\Helper\Input\Email;
use Phalcon\Html\Helper\Input\Hidden;
use Phalcon\Html\Helper\Input\Numeric;
use Phalcon\Html\Helper\Input\Password;
use Phalcon\Html\Helper\Input\Tel;
use Phalcon\Html\Helper\Input\Text;
use Phalcon\Html\Helper\Input\Url;
use Phalcon\Html\Helper\Label;
use Phalcon\Html\Helper\Script;

function getUrlForFlow(string $base, string $flow, array $params)
{
    $url = $base . "/self-service/$flow/browser";

    $query = join('&', $params);
    if (strlen($query) > 0) {
        $url .= "?$query";
    }

    return $url;
}

function convertToForm(UiContainer $ui)
{
    $escaper = new Escaper();
    $form = new Form($escaper);
    $action = $ui->getAction();
    $method = $ui->getMethod();

    $output = $form(
        array(
            "action" => $action,
            "method" => $method,
            'enctype' => ''
        )
    );
    $messages = $ui->getMessages();

    if (isset($messages)) {
        foreach ($messages as $messages) {
            $label = new Label($escaper);
            $output .= $label($messages->getText());
        }
    }
    $output .= '<br>';

    $nodes = $ui->getNodes();
    foreach ($nodes as $node) {
        $attributes = $node->getAttributes();
        $meta = $node->getMeta();
        $messages = $node->getMessages();
        if ($node->getType() === UiNodeAttributes::NODE_TYPE_INPUT) {
            if ($attributes->getType() == UiNodeInputAttributes::TYPE_SUBMIT) {
                $button = new Button($escaper);
                $options = array(
                    'name' => $attributes->getName(),
                    'value' => $attributes->getValue(),
                    'type' => 'submit',
                    'required' => $attributes->getRequired(),
                    'onclick' => $attributes->getOnclick(),
                    'onload' => $attributes->getOnload()
                );

                $output .= $button($meta->getLabel()->getText(), $options);
                $output .= '<br>';
            } else {
                if ($meta->getLabel()) {
                    $label = new Label($escaper);
                    $output .= $label($meta->getLabel()->getText());
                    $output .= '<br>';
                }

                switch($attributes->getType()) {
                case 'text':
                    $input = new Text($escaper);
                    break;
                case 'password':
                    $input = new Password($escaper);
                    break;
                case 'number':
                    $input = new Numeric($escaper);
                    break;
                case 'checkbox':
                    $input = new Checkbox($escaper);
                    break;
                case 'hidden':
                    $input = new Hidden($escaper);
                    break;
                case 'email':
                    $input = new Email($escaper);
                    break;
                case 'tel':
                    $input = new Tel($escaper);
                    break;
                case 'datetime-local':
                    $input = new DateTimeLocal($escaper);
                    break;
                case 'datetime':
                    $input = new DateTime($escaper);
                    break;
                case 'url':
                    $input = new Url($escaper);
                    break;
                default:
                    $input = new Text($escaper);
                }

                $options = array(
                    'type' => $attributes->getType(),
                    'required' => $attributes->getRequired(),
                    'autocomplete' => $attributes->getAutocomplete(),
                    'onclick' => $attributes->getOnclick(),
                    'onload' => $attributes->getOnload()
                );

                if ($attributes->getDisabled() != '') {
                    array_push($options, 'disabled', true);
                }

                $output .= $input(
                    $attributes->getName(),
                    $attributes->getValue(),
                    $options
                );
                $output .= '<br>';

                if (count($messages) > 0) {
                    foreach ($messages as $message) {
                        $label = new Label($escaper);
                        $output .= $label($message->getText());
                    }
                    $output .= '<br>';
                }
            }
        }
    }

    $close = new Close($escaper);
    $output .= $close('form');

    return $output;
}

// vim: set et sw=4 sts=4 ts=4:
