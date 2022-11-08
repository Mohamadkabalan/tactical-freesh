<?php

namespace CustomPaymentGateway\Form;

final class Select extends FormElement {

	protected function element(string $id, string $value, array $args) {
		?>
		<select
			name="<?php echo $id ?>"
			id="<?php echo $id ?>"
			<?php if (!empty($args['classes'])) echo 'class="' . implode(' ', $args['classes']) . '"' ?>
		>
			<?php
			foreach ($args['options'] as $key => $option) {
				?>
				<option value="<?php echo $key ?>" <?php selected($value, $key) ?>>
					<?php echo $option ?>
				</option>
				<?php
			}
			?>
		</select>
		<?php
	}
}