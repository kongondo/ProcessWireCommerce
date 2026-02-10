<?php

namespace ProcessWire;

trait TraitPWCommerceUtilitiesMessage
{

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~ MESSAGE ~~~~~~~~~~~~~~~~~~

	/**
	 * Build Note.
	 *
	 * @param mixed $noteText
	 * @param string $noteType
	 * @param int $userID
	 * @return mixed
	 */
	public function buildNote($noteText, $noteType = 'system', $userID = 0) {
		$note = new WireData();
		$note->text = $noteText;
		$time = time();
		$note->created = $time;
		$note->modified = $time;
		// TODO: IF SYSTEM NOTE, THEN USERID SHOULD BE ZERO EVEN IF WE HAVE A LOGGED IN USER!
		// $userID = (int) $this->wire('user')->id;
		// $userID = 0;
		$note->createdUsersID = $userID;
		$note->modifiedUsersID = $userID;
		$note->type = $noteType;
		// ---------
		// add new note
		return $note;
	}

	/**
	 * Send Email.
	 *
	 * @param mixed $emailOptions
	 * @return mixed
	 */
	public function sendEmail($emailOptions) {

		// array|string $to, string $from, string $subject, string $body

		$result = [];
		$errors = [];

		// =======
		// ERROR CHECKING

		if (empty($emailOptions['to'])) {
			// email recipient missing
			$errors[] = $this->_('Email recipient not specified.');
		}

		if (empty($emailOptions['from'])) {
			// email from addess is missing
			$errors[] = $this->_('Email sender is missing.');
		}

		if (empty($emailOptions['subject'])) {
			// email subject missing
			$errors[] = $this->_('Email subject is missing.');
		}

		if (empty($emailOptions['body'])) {
			// email body missing
			$errors[] = $this->_('Email body is missing.');
		}

		// +++++++++

		if (!empty($errors)) {
			// @RETURN ERROR
			$notice = $this->_('Not able to send email due to stated errors.');
			$result = [
				'notice' => $notice,
				'notice_type' => 'error',
				'errors' => $errors
			];
			return $result;
		}

		// +++++++++++++
		// GOOD TO GO
		$mail = wireMail();

		// recipients
		if (is_array($emailOptions['to'])) {
			// multiple recipients
			foreach ($emailOptions['to'] as $emailTo) {
				$mail->to($emailTo);
			}
		} else {
			// single recipient
			$mail->to($emailOptions['to']);
		}

		// from
		$mail->from($emailOptions['from']);
		// subject
		$mail->subject($emailOptions['subject']);
		// body
		$mail->body($emailOptions['body']);

		// body HTML if present
		if (!empty($emailOptions['bodyHTML'])) {
			$mail->bodyHTML($emailOptions['bodyHTML']);
		}

		// ----
		// send it!
		$sent = $mail->send();
		if (empty($sent)) {
			// TECHNICAL ERRORS
			$notice = $this->_('Not able to send email due to technical issues.');
			$result = [
				'notice' => $notice,
				'notice_type' => 'error',
			];
		} else {
			// SUCCESS
			$notice = $this->_('Email sent successfully!.');
			$result = [
				'notice' => $notice,
				'notice_type' => 'success',
			];
		}
		// ------
		return $result;

	}


}