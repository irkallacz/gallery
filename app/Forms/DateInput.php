<?php
/**
 * Created by PhpStorm.
 * User: Jakub
 * Date: 9.1.2017
 * Time: 17:56
 */

namespace App\Forms;

use Nette\Forms\Control;
use Nette\Forms\Form;
use Nette\Utils\DateTime;
use	Nette\Utils\Html;
use Nette\Forms\Controls\BaseControl;

final class DateInput extends BaseControl
{
	const DATE_FORMAT = 'Y-m-d';

	private ?string $date;

	public function __construct($label = null)
	{
		parent::__construct($label);
		$this->addRule(__CLASS__ . '::validateDate', 'Datum má špatný formát');
	}

	public function setValue($value)
	{
		if ($value) {
			$date = DateTime::from($value);
			$this->date = $date->format(self::DATE_FORMAT);
		} else {
			$this->date = null;
		}

		return $this;
	}

	public function getValue(): ?DateTime
	{
		if (self::validateDate($this)) {
			$date = DateTime::createFromFormat(self::DATE_FORMAT, $this->date);
			$date->setTime(0,0,0);
			return $date;
		} else {
			return null;
		}
	}

	public function loadHttpData(): void
	{
		$this->date = $this->getHttpData(Form::DATA_LINE, '[date]');
	}

	public function getControl(): Html
	{
		$name = $this->getHtmlName();

		return Html::el('input')->name($name . '[date]')
				->id($this->getHtmlId())
				->pattern('[1-2]{1}\d{3}-[0-1]{1}\d{1}-[0-3]{1}\d{1}')
				->type('date')
				->size('10')
				->value($this->date)
				->class('date');
	}

	public static function validateDate(Control $control): bool
	{
		$value = $control->date;
		$datetime = [];
		$find = preg_match ('~([1-2]{1}\d{3})-([0-1]{1}\d{1})-([0-3]{1}\d{1})~', $value, $datetime);

		if ($find) {
			if (checkdate(intval($datetime[2]), intval($datetime[3]), intval($datetime[1]))) {
				return (bool) DateTime::createFromFormat(self::DATE_FORMAT, $value);
			} else return false;
		} else return false;
	}
}