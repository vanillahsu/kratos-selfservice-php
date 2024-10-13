<?php
declare(strict_types=1);

require_once BASE_PATH . '/../vendor/autoload.php';

use Ory\Kratos\Client\Model\UiContainer;
use Ory\Kratos\Client\Model\UiNodeAttributes;
use Ory\Kratos\Client\Model\UiNodeInputAttributes;
use Phalcon\Html\Escaper;
use Phalcon\Html\Helper\Close;
use Phalcon\Html\Helper\Form;
use Phalcon\Html\Helper\Img;
use Phalcon\Html\Helper\Input\Button;
use Phalcon\Html\Helper\Input\Checkbox;
use Phalcon\Html\Helper\Input\Hidden;
use Phalcon\Html\Helper\Input\Submit;
use Phalcon\Html\Helper\Input\Text;
use Phalcon\Html\Helper\Script;

function convert_to_form(UiContainer $ui) {
	$escaper = new Escaper();
	$form = new Form($escaper);
	$action = $ui->getAction();
	$method = $ui->getMethod();

	$output = $form(array("action" => $action, "method" => $method));

	$nodes = $ui->getNodes();
	foreach($nodes as $node) {
		$attrs = $node->getAttributes();
		print_r($attrs);
		switch($node->getType()) {
		case UiNodeAttributes::NODE_TYPE_INPUT:
			$type = $attrs->getType();
			switch($type) {
			case UiNodeInputAttributes::TYPE_TEXT:
				$text = new Text($escaper);
				$name = $attrs->getName();
				$value = $attrs->getValue();
				$disabled = $attrs->getDisabled();
				$attr = [];
				if ($disabled) {
					$attr = array_merge($attr, array("disabled" => true));
				}

				$output .= $text($name, $value, $attr);
				$attr = [];

				break;
			case UiNodeInputAttributes::TYPE_CHECKBOX:
				$checkbox = new Checkbox($escaper);
				$name = $attrs->getName();
				print_r($attrs);
				echo "checkbox\n";
				break;
			case UiNodeInputAttributes::TYPE_HIDDEN:
				$hidden = new Hidden($escaper);
				$name = $attrs->getName();
				$value = $attrs->getValue() ?? true;
				$output .= $hidden($name, $value) . PHP_EOL;
				break;
			case UiNodeInputAttributes::TYPE_SUBMIT:
				$submit = new Submit($escaper);
				$name = $attrs->getName();
				$value = $attrs->getValue() ?? "";
				$disabled = $attrs->getDisabled() ?? false;
				$attr = [];
				if ($disabled) {
					$attr = array_merge($attr, array("disabled" => true));
				}

				$output .= $submit($name, $value, $attr);
				$attr = [];

				break;
			case UiNodeInputAttributes::TYPE_BUTTON:
				$button = new Button($escaper);
				$name = $attrs->getName();
				$value = $attrs->getValue();
				$disabled = $attrs->getDisabled() ?? false;
				$attr = [];
				if ($disabled) {
					$attr = array_merge($attr, array("disabled" => true));
				}

				$output .= $button($name, $value, $attr);
				$attr = [];

				break;
			default:
				$text = new Text($escaper);
				$name = $attrs->getName();
				$value = $attrs->getValue() ?? "";
				$required = $attrs->getRequired() ?? false;
				$disabled = $attrs->getDisabled() ?? false;
				$attr = [];
				if ($disabled) {
					$attr = array_merge($attr, array("disabled" => true));
				}
				if ($required) {
					$attr = array_merge($attr, array("required" => true));
				}

				$output .= $text($name, $value, $attr);
				$attr = [];
			}
			break;
		case UiNodeAttributes::NODE_TYPE_SCRIPT:
			$src = $attrs->getSrc();
			$async = $attrs->getAsync();
			$cross_origin = $attrs->getCrossorigin();
			$integrity = $attrs->getIntegrity();
			$referrer_policy = $attrs->getReferrerpolicy();

			$script = new Script($escaper);
			$script->add($src, array("async" => $async, "crossOrigin" => $cross_origin, "integrity" => $integrity, "referrerPolicy" => $referrer_policy));
			$output .= $script;
			break;
		case UiNodeAttributes::NODE_TYPE_TEXT:
		default:
			echo "default\n";
		}
	}

	$close = new Close($escaper);
	$output .= $close("form");
	return $output;
}
?>
