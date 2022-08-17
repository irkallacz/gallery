<?php


namespace App\Forms;


use Nette\Application\UI\Form;
use Nette\Utils\DateTime;
use Tracy\Debugger;

class AlbumFormFactory
{
	public function createForm(): Form
	{
		$form = new Form;

		$form->addText('title', 'Název', 40, 50)
			->setRequired('Vyplňte %label');

		$form->addComponent(component: (new DateInput('Datum'))
			->setRequired('Vyplňte datum začátku akce')
			->setDefaultValue(new DateTime()), name: 'date'
		);

		$form->addTextArea('summary', 'Popis', 50)
			->setHtmlAttribute('placeholder','Popis alba')
			->setNullable();

		$form->addTextArea('description', 'Pro členy', 50)
			->setHtmlAttribute('placeholder', 'Text viditelný jen pro přihlášené')
			->setNullable();

		return $form;
	}

}