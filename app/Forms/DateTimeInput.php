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

final class DateTimeInput extends BaseControl
{
	const DATE_FORMAT = 'Y-m-d';
	const TIME_FORMAT = 'H:i';

	private ?string $date;
	private ?string $time;

	public function __construct($label = null)
	{
		parent::__construct($label);
		$this->addRule(__CLASS__ . '::validateDate', 'Datum nebo čas má špatný formát');
	}

	public function setValue($value)
	{
		if ($value) {
			$date = DateTime::from($value);
			$this->date = $date->format(self::DATE_FORMAT);
			$this->time = $date->format(self::TIME_FORMAT);
		} else {
			$this->date = $this->time = null;
		}

		return $this;
	}

	public function getValue(): ?DateTime
	{
		return self::validateDate($this)
			? DateTime::createFromFormat(self::DATE_FORMAT.' '.self::TIME_FORMAT,$this->date.' '.$this->time)
			: null;
	}

	public function loadHttpData(): void
	{
		$this->date = $this->getHttpData(Form::DATA_LINE, '[date]');
		$this->time = $this->getHttpData(Form::DATA_LINE, '[time]');
	}

	public function getControl(): Html
	{
		$name = $this->getHtmlName();

		return Html::el('span')
			->id($this->getHtmlId())
			->class('datetime')
			->addHtml(Html::el('input')->name($name . '[date]')
				->id($this->getHtmlId() . '-date')
				->pattern('[1-2]{1}\d{3}-[0-1]{1}\d{1}-[0-3]{1}\d{1}')
				->type('date')
				->size('10')
				->value($this->date)
				->class('date')
			)
			->addText(' ')
			->addHtml(Html::el('input')->name($name . '[time]')
				->id($this->getHtmlId() . '-time')
				->pattern('[0-2]{1}\d{1}:[0-5]{1}\d{1}')
				->type('time')
				->size('5')
				->value($this->time)
				->class('time')
			);
	}

	public static function validateDate(Control $control): bool
	{
		$value = $control->date . ' ' . $control->time;
		$datetime = [];
		$find = preg_match ('~([1-2]{1}\d{3})-([0-1]{1}\d{1})-([0-3]{1}\d{1}) ([0-2]{1}\d{1}):([0-5]{1}\d{1})~', $value, $datetime);

		if ($find) {
			if (checkdate(intval($datetime[2]), intval($datetime[3]), intval($datetime[1]))) {
				return (bool) DateTime::createFromFormat(self::DATE_FORMAT . ' ' . self::TIME_FORMAT, $value);
			} else return false;
		} else return false;
	}
}