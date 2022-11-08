<?php

namespace CustomPaymentGateway\Form;

final class Textarea extends FormElement {

	protected function element(string $id, string $value, array $args) {
		$args['classes'][] = 'large-text';
		?>
		<textarea
			type="textarea"
			rows="3"
			cols="20"
			name="<?php echo $id ?>"
			id="<?php echo $id ?>"
			<?php if (!empty($args['placeholder'])) echo 'placeholder="' . $args['placeholder'] . '"' ?>
			class="<?php echo implode(' ', $args['classes']) ?>"
		><?php if (!empty($value)) echo $value ?></textarea>
		<?php
	}
}