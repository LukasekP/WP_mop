<?php
namespace App\MailSender;

use Nette;
use Nette\Mail\SmtpMailer;
use Latte\Engine;
class MailSender
{
    private SmtpMailer $mailer;

	public function __construct(
		private Nette\Application\LinkGenerator $linkGenerator,
		private Nette\Bridges\ApplicationLatte\TemplateFactory $templateFactory,
        
	) {
        $this->mailer = new Nette\Mail\SmtpMailer(
            host: 'smtp.seznam.cz',
            username: 'festzone@email.cz',
            password: 'I5XXkHHz',
            port: 465,
            encryption: 'ssl',
         );
        
	}

	private function createTemplate(): Nette\Application\UI\Template
	{
		$template = $this->templateFactory->createTemplate();
		$template->getLatte()->addProvider('uiControl', $this->linkGenerator);
		return $template;
	}

	public function createEmail(): Nette\Mail\Message
	{
		$template = $this->createTemplate();
		$html = $template->renderToString('/path/to/email.latte', $params);

		$mail = new Nette\Mail\Message;
		$mail->setHtmlBody($html);
		// ...
		return $mail;
	}
    public function createNotificationEmail( $email, string $firstname, string $lastname): Nette\Mail\Message
    {
        $latte = new Engine;
        $params = [
            
            'firstname' => $firstname,
            'lastname' => $lastname,
        ];
        $html = $latte->renderToString(__DIR__ . '/email.latte', $params);

        $mail = new Nette\Mail\Message;
        $mail->setFrom('festzone@email.cz')
            ->addTo($email)
            ->addBcc('festzone@email.cz')
            ->setSubject('Potvrzení registrace')
            ->setHtmlBody($html);
        return $mail;
    }
    public function sendEmail(Nette\Mail\Message $mail): void
    {
        $this->mailer->send($mail);
    }
}