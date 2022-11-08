<?php

namespace CustomPaymentGateway\Form;

final class Password extends FormElement {

	protected function element(string $id, string $value, array $args) {
		$args['classes'][] = 'regular-text';
		?>
		<input
			type="password"
			name="<?php echo $id ?>"
			id="<?php echo $id ?>"
			<?php if (!empty($value)) echo 'value="' . $value . '"' ?>
			<?php if (!empty($args['placeholder'])) echo 'placeholder="' . $args['placeholder'] . '"' ?>
			class="<?php echo implode(' ', $args['classes']) ?>"
		>
		<?php
	}
}