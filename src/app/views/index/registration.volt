<form action="<?php echo $ui->getAction(); ?>" method="<?php echo $ui->getMethod(); ?>" >
<?php
$messages = $ui->getMessages();
if (isset($messages)) {
    foreach ($messages as $message) {
        $this->tag->label($message->getText());
    }
}

foreach ($ui->getNodes() as $node) {
    $attributes = $node->getAttributes();
    $meta = $node->getMeta();
    $messages = $node->getMessages();
    if ($node->getType() === "input") {
        if ($attributes->getType() === "submit") {
            echo $this->tag->button(
		$meta->getLabel()->getText(),
		array(
		    'name' => $attributes->getName(),
		    'value' => $attributes->getValue(),
		    'type' => 'submit',
		    'autocomplete' => $attributes->getAutocomplete(),
		    'required' => $attributes->getRequired(),
		    'onclick' => $attributes->getOnclick(),
		    'onload' => $attributes->getOnload()
		)
	    );
        } else {
            if ($meta->getLabel()) {
                echo $this->tag->label($meta->getLabel()->getText());
            }

            switch($attributes->getType()) {
            case 'text':
                $inputType = 'inputText';
                break;
            case 'password':
                $inputType = 'inputPassword';
                break;
            case 'number':
                $inputType = 'inputNumber';
                break;
            case 'checkbox':
                $inputType = 'inputCheckbox';
                break;
            case 'hidden':
                $inputType = 'inputHidden';
                break;
            case 'email':
                $inputType = 'inputEmail';
                break;
            case 'tel':
                $inputType = 'inputTel';
                break;
            case 'button':
                $inputType = 'inputButton';
                break;
            case 'datetime-local':
                $inputType = 'inputDateTimeLocal';
                break;
            case 'datetime':
                $inputType = 'inputDateTime';
                break;
            case 'url':
                $inputType = 'inputUrl';
                break;
            default:
                $inputType = 'inputText';
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

            echo $this->tag->$inputType($attributes->getName(), $attributes->getValue(), $options);

            if (count($messages) > 0) {
                foreach ($messages as $message) {
                    echo $this->tag->label($message->getText());
                }
            }
        }
    }
}
?>
