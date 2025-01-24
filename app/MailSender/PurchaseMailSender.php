<?php
namespace App\MailSender;

use Nette;
use Nette\Mail\Mailer;
use Latte\Engine;
class PurchaseMailSender
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
		$html = $template->renderToString('/path/to/ticket_purchase.latte', $params);

		$mail = new Nette\Mail\Message;
		$mail->setHtmlBody($html);
		// ...
		return $mail;
	}
    public function createPurchaseEmail( $email, string $firstname, string $lastname, $variableCode): Nette\Mail\Message
    {
        $latte = new Engine;
        $params = [
            'firstname' => $firstname,
            'lastname' => $lastname,
            'bankAccount' => '51-7080060207/0100',
            'variableCode' => $variableCode,
        ];
        $html = $latte->renderToString(__DIR__ . '/ticket_purchase.latte', $params);

        $mail = new Nette\Mail\Message;
        $mail->setFrom('festzone@email.cz')
            ->addTo($email)
            ->addBcc('festzone@email.cz')
            ->setSubject('Pokyny k platbě')
            ->setHtmlBody($html);
        return $mail;
    }


    public function sendPurchaseEmail(Nette\Mail\Message $mail): void
    {
        $this->mailer->send($mail);
    }
}