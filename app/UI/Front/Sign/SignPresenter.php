<?php

declare(strict_types=1);

namespace App\UI\Front\Sign;

use App\Model\DuplicateNameException;
use App\Model\UserFacade;
use App\UI\Accessory\FormFactory;
use Nette;
use Nette\Application\Attributes\Persistent;
use Nette\Application\UI\Form;


/**
 * Presenter for sign-in and sign-up actions.
 */
final class SignPresenter extends Nette\Application\UI\Presenter
{
	/**
	 * Stores the previous page hash to redirect back after successful login.
	 */
	#[Persistent]
	public string $backlink = '';


	// Dependency injection of form factory and user management facade
	public function __construct(
		private UserFacade $userFacade,
		private FormFactory $formFactory,
	) {
	}


	/**
	 * Create a sign-in form with fields for username and password.
	 * On successful submission, the user is redirected to the dashboard or back to the previous page.
	 */
	protected function createComponentSignInForm(): Form
	{
		$form = $this->formFactory->create();
		$form->addText('username', 'Uživatelské jméno:')
			->setRequired('Zadejte prosím své uživatelské jméno.');

		$form->addPassword('password', 'Heslo:')
			->setRequired('Zadejte prosím své heslo.');

		$form->addSubmit('send', 'Přihlásit se');

		// Handle form submission
		$form->onSuccess[] = function (Form $form, \stdClass $data): void {
			try {
				// Attempt to login user
				$this->getUser()->login($data->username, $data->password);
				$this->restoreRequest($this->backlink);
				$this->redirect('Home:');
			} catch (Nette\Security\AuthenticationException) {
				$form->addError('Zadané uživatelské jméno nebo heslo je nesprávné.');
			}
		};

		return $form;
	}


	/**
	 * Create a sign-up form with fields for username, email, and password.
	 * On successful submission, the user is redirected to the dashboard.
	 */
	protected function createComponentSignUpForm(): Form
	{
		$form = $this->formFactory->create();
		$form->addText('username', 'Uživatelské jméno:')
    		->setRequired('Prosím, zadejte uživatelské jméno.');

		$form->addText('firstname', 'Křestní jméno:')
			->setRequired('Prosím, zadejte křestní jméno.');

		$form->addText('lastname', 'Příjmení:')
			->setRequired('Prosím, zadejte příjmení.');

		$form->addEmail('email', 'E-mail:')
			->setRequired('Prosím, zadejte e-mail.');

		$form->addPassword('password', 'Heslo:')
			->setOption('description', sprintf('alespoň %d znaků', $this->userFacade::PasswordMinLength))
			->setRequired('Prosím, vytvořte heslo.')
			->addRule($form::MinLength, null, $this->userFacade::PasswordMinLength);

		$form->addText('phone', 'Telefonní číslo:')
			->setRequired('Prosím, zadejte telefonní číslo.');

		$form->addText('birthdate_day', 'Datum narození:')
			->setRequired('Prosím, zadejte den narození.')
			->addRule($form::INTEGER, 'Den musí být číslo.')
			->addRule($form::RANGE, 'Den musí být v rozmezí 1 až 31.', [1, 31])
			->setHtmlAttribute('placeholder', 'Den');
		
		$form->addText('birthdate_month', '')
			->setRequired('Prosím, zadejte měsíc narození.')
			->addRule($form::INTEGER, 'Měsíc musí být číslo.')
			->addRule($form::RANGE, 'Měsíc musí být v rozmezí 1 až 12.', [1, 12])
			->setHtmlAttribute('placeholder', 'Měsíc');
		
		$form->addText('birthdate_year', '')
			->setRequired('Prosím, zadejte rok narození.')
			->addRule($form::INTEGER, 'Rok musí být číslo.')
			->addRule($form::RANGE, 'Rok musí být v rozmezí 1900 až ' . date('Y') . '.', [1900, date('Y')])
			->setHtmlAttribute('placeholder', 'Rok');

		$form->addText('address', 'Adresa (ulice a číslo popisné):')
			->setRequired('Prosím, zadejte adresu.');

		$form->addText('city', 'Město:')
			->setRequired('Prosím, zadejte město.');
	

		$form->addSubmit('send', 'Sign up');

		// Handle form submission
		$form->onSuccess[] = function (Form $form, \stdClass $data): void {
			try {
				// Attempt to register a new user
				$this->userFacade->add($data->username, $data->firstname, $data->lastname, $data->email, $data->password, $data->phone, $data->birthdate_day, $data->birthdate_month, $data->birthdate_year, $data->address, $data->city);
				$this->redirect('Home:');
			} catch (DuplicateNameException) {
				// Handle the case where the username is already taken
				$form['username']->addError('Uživatelské jméno je zabrané.');
			}
		};

		return $form;
	}


	/**
	 * Logs out the currently authenticated user.
	 */
	public function actionOut(): void
	{
		$this->getUser()->logout();
	}
}
