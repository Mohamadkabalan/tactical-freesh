<?php

namespace CustomPaymentGateway\Form;

final class Checkbox extends FormElement {

	public function render() {
		$this->element($this->id, $this->value, $this->args);
	}

	protected function element(string $id, string $value, array $args) {
		?>
		<label for="<?php echo $id ?>">
			<input
				type="hidden"
				name="<?php echo $id ?>"
				id="<?php echo $id ?>_hidden"
				value="no"
				<?php checked($value, 'no') ?>
			>
			<input
				type="checkbox"
				name="<?php echo $id ?>"
				id="<?php echo $id ?>"
				value="yes"
				<?php if (!empty($args['classes'])) echo 'class="' . implode(' ', $args['classes']) . '"' ?>
				<?php checked($value, 'yes') ?>
			>
			<?php echo $this->description ?>
		</label>
		<?php
	}
}