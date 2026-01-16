<?php

namespace Palasthotel\ProLitteris;

/**
 * @property  Plugin plugin
 */
class User extends _Component {
	
	public function onCreate() {
		parent::onCreate();

		add_action( 'show_user_profile', array($this, 'profile_fields'), 100 );
		add_action( 'edit_user_profile', array($this, 'profile_fields'), 100 );

		add_action( 'personal_options_update', array($this, 'save') );
		add_action( 'edit_user_profile_update', array($this, 'save') );
	}

	public function getProLitterisId($userId){
		return get_the_author_meta( Plugin::USER_META_PRO_LITTERIS_ID, $userId );
	}

	public function getProLitterisName($userId){
		return get_the_author_meta( Plugin::USER_META_PRO_LITTERIS_NAME, $userId );
	}

	public function getProLitterisSurname($userId){
		return get_the_author_meta( Plugin::USER_META_PRO_LITTERIS_SURNAME, $userId );
	}

	public function profile_fields($user){

		if(!user_can($user, "edit_posts")) return;
		$keyId = Plugin::USER_META_PRO_LITTERIS_ID;
		$keyName = Plugin::USER_META_PRO_LITTERIS_NAME;
		$keySurname = Plugin::USER_META_PRO_LITTERIS_SURNAME;
		?>
		<h3>ProLitteris</h3>
		<table class="form-table">
			<tr>
				<th>
					<label for="<?php echo $keyId; ?>">Member ID</label></th>
				<td>
					<input
						type="text"
						name="<?php echo $keyId; ?>"
						id="<?php echo $keyId; ?>"
						value="<?php echo esc_attr( $this->getProLitterisId($user->ID)); ?>"
						class="regular-text"
					/>
				</td>
			</tr>
			<tr>
				<th>
					<label for="<?php echo $keyName; ?>">Vorname</label></th>
				<td>
					<input
						type="text"
						name="<?php echo $keyName; ?>"
						id="<?php echo $keyName; ?>"
						value="<?php echo esc_attr( $this->getProLitterisName($user->ID)); ?>"
						class="regular-text"
					/>
					<p class="description">Der Vorname muss mit dem bei ProLitteris hinterlegten Vorname übereinstimmen.</p>
				</td>
			</tr>
			<tr>
				<th>
					<label for="<?php echo $keySurname; ?>">Nachname</label></th>
				<td>
					<input
						type="text"
						name="<?php echo $keySurname; ?>"
						id="<?php echo $keySurname; ?>"
						value="<?php echo esc_attr( $this->getProLitterisSurname($user->ID)); ?>"
						class="regular-text"
					/>
					<p class="description">Der Nachname muss mit dem bei ProLitteris hinterlegten Nachname übereinstimmen.</p>
				</td>
			</tr>
		</table>
		<?php
	}

	public function save($user_id){

		if ( !current_user_can( 'edit_user', $user_id ) )
			return;

		if(isset($_POST[Plugin::USER_META_PRO_LITTERIS_ID]) && !empty($_POST[Plugin::USER_META_PRO_LITTERIS_ID])){
			update_user_meta( $user_id, Plugin::USER_META_PRO_LITTERIS_ID, intval($_POST[Plugin::USER_META_PRO_LITTERIS_ID]) );
		} else {
			delete_user_meta($user_id, Plugin::USER_META_PRO_LITTERIS_ID );
		}

		if(isset($_POST[Plugin::USER_META_PRO_LITTERIS_NAME]) && !empty($_POST[Plugin::USER_META_PRO_LITTERIS_NAME])){
			update_user_meta( $user_id, Plugin::USER_META_PRO_LITTERIS_NAME, sanitize_text_field($_POST[Plugin::USER_META_PRO_LITTERIS_NAME]) );
		} else {
			delete_user_meta( $user_id, Plugin::USER_META_PRO_LITTERIS_NAME);
		}

		if(isset($_POST[Plugin::USER_META_PRO_LITTERIS_SURNAME]) && !empty($_POST[Plugin::USER_META_PRO_LITTERIS_SURNAME])){
			update_user_meta( $user_id, Plugin::USER_META_PRO_LITTERIS_SURNAME, sanitize_text_field($_POST[Plugin::USER_META_PRO_LITTERIS_SURNAME]) );
		} else {
			delete_user_meta( $user_id, Plugin::USER_META_PRO_LITTERIS_SURNAME);
		}

	}


}
