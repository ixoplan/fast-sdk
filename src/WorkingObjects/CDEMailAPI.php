<?php

namespace Ixolit\CDE\WorkingObjects;

use Ixolit\CDE\Exceptions\CDEFeatureNotSupportedException;
use Ixolit\CDE\Exceptions\MailSendingFailedException;
use Ixolit\CDE\Interfaces\MailAPI;

class CDEMailAPI implements MailAPI {

	/**
	 * {@inheritdoc}
	 */
	public function sendPlainText($from, $to, $subject, $plainText, $cc = [], $bcc = []) {
		if (!\function_exists('sendMail')) {
			throw new CDEFeatureNotSupportedException('sendMail');
		}
		if (!sendMail(
			$from,
			$to,
			$subject,
			$plainText,
			[
				'cc' => $cc,
				'bcc' => $bcc,
			]
		)) {
			throw new MailSendingFailedException();
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function sendMixed($from, $to, $subject, $plainText, $html, $cc = [], $bcc = []) {
		if (!\function_exists('sendMail')) {
			throw new CDEFeatureNotSupportedException('sendMail');
		}
		if (!sendMail(
			$from,
			$to,
			$subject,
			[
				'text' => $plainText,
				'html' => $html,
			],
			[
				'cc' => $cc,
				'bcc' => $bcc,
			]
		)) {
			throw new MailSendingFailedException();
		}
	}
}