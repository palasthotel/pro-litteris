<?php


namespace Palasthotel\ProLitteris\Components\Attachment;



use Palasthotel\ProLitteris\Components\Model\Option;
use Palasthotel\ProLitteris\Components\Service\ProviderInterface;

/**
 * Class SelectMetaField
 * @version 0.1.1
 */
class SelectMetaField extends MetaField {

	/**
	 * @var Option[]
	 */
	private $options = [];

	/**
	 * @param Option[]|ProviderInterface $options
	 *
	 * @return $this
	 */
	public function options( $options ): self {
		$this->options = $options;

		return $this;
	}

	protected function field( array $field, \WP_Post $post ): array {
		$field = parent::field( $field, $post );

		$field["input"] = "html";
		$name = $this->getFormName($post->ID);
		$value = $this->getValue($post->ID);

		$options = $this->options instanceof ProviderInterface ? $this->options->get() : $this->options;

		ob_start();
		echo "<select name='$name' id='attachments-{$post->ID}-{$this->id}' style='max-width: 100%'>";
		foreach ($options as $option){
		    $selected = ($value === $option->value) ? "selected='selected'" : "";
            echo "<option value='$option->value' $selected>$option->label</option>";
        }
		echo "</select>";
		$field["html"] = ob_get_contents();
		ob_end_clean();

		return $field;
	}


}
