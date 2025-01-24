<?php
namespace App\MailSender;

use Nette;
use Nette\Mail\Mailer;
use Latte\Engine;
class ConfirmMailSender
{

	public function __construct(
        private Mailer $mailer,
		private Nette\Application\LinkGenerator $linkGenerator,
		private Nette\Bridges\ApplicationLatte\TemplateFactory $templateFactory,
        
	) {}

	private function createTemplate(): Nette\Application\UI\Template
	{
		$template = $this->templateFactory->createTemplate();
		$template->getLatte()->addProvider('uiControl', $this->linkGenerator);
		return $template;
	}

	public function createEmail(): Nette\Mail\Message
	{
		$template = $this->createTemplate();
		$html = $template->renderToString('/path/to/confirmation.latte', $params);

		$mail = new Nette\Mail\Message;
		$mail->setHtmlBody($html);
		// ...
		return $mail;
	}
    public function createConfirmEmail(int $id, $email, string $firstname, string $lastname, $festivalName): Nette\Mail\Message
    {
        $latte = new Engine;
        $params = [
            'id' => $id,
            'firstname' => $firstname,
            'lastname' => $lastname,
            'festivalName' => $festivalName,

            
        ];
        $html = $latte->renderToString(__DIR__ . '/confirmation.latte', $params);

        $mail = new Nette\Mail\Message;
        $mail->setFrom('festzone@email.cz')
            ->addTo($email)
            ->addBcc('festzone@email.cz')
            ->setSubject('Potvrzení platby')
            ->setHtmlBody($html);
        return $mail;
    }


    public function sendConfirmEmail(Nette\Mail\Message $mail): void
    {
        $this->mailer->send($mail);
    }
}