<?php

namespace CustomPaymentGateway\Form;

abstract class FormElement {
	/**
	 * @var string
	 */
	protected $id;

	/**
	 * @var string
	 */
	protected $value;

	/**
	 * @var array
	 */
	protected $args;

	/**
	 * @var string
	 */
	protected $description;

	/**
	 * FormElement constructor.
	 *
	 * @param string $id            The form element id
	 * @param string $value         The form element value
	 * @param string $description   The form element description
	 * @param array  $args          (optional) [
	 *                              'placeholder' => string,
	 *                              'options' => array,
	 *                              'classes' => string[]
	 *                              ]
	 */
	public function __construct(string $id, string $value, string $description = '', array $args = []) {
		$this->id = esc_attr($id);
		$this->value = esc_attr($value);
		$this->description = esc_html($description);
		$this->args = $args;
		if (isset($this->args['placeholder'])) {
			$this->args['placeholder'] = esc_attr($this->args['placeholder']);
		}
		if (isset($this->args['options'])) {
			foreach ($this->args['options'] as $key => $option) {
				$this->args['options'][esc_attr($key)] = esc_html($option);
				if (esc_attr($key) !== $key) {
					unset($this->args['options'][$key]);
				}
			}
			$this->args['options'] = array_map(function ($item) {
				return esc_html($item);
			}, $this->args['options']);
		}
		if (isset($this->args['classes'])) {
			$this->args['classes'] = array_map(function ($item) {
				return esc_html($item);
			}, $this->args['classes']);
		}
	}

	public function render() {
		$this->element($this->id, $this->value, $this->args);
		if (!empty($this->description)) echo '<p class="description">' . $this->description . '</p>';
	}

	abstract protected function element(string $id, string $value, array $args);
}