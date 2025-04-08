<?php
namespace App\MailSender;

use Nette;
use Nette\Mail\Mailer;
use Latte\Engine;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
class PurchaseMailSender
{

	public function __construct(
        private Mailer $mailer,
		private Nette\Application\LinkGenerator $linkGenerator,
		private Nette\Bridges\ApplicationLatte\TemplateFactory $templateFactory,
        
	) {
        
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
		$html = $template->renderToString('/path/to/ticket_purchase.latte', $params);

		$mail = new Nette\Mail\Message;
		$mail->setHtmlBody($html);

        return $mail;
	}
    public function createPurchaseEmail($email, string $firstname, string $lastname, $variableCode, float $totalPrice): Nette\Mail\Message 
    {
        $latte = new Engine;
        
        $accountNumber = 'CZ5101000000517080060207'; 

        $qrData = "SPD*1.0*ACC:$accountNumber*AM:" . number_format($totalPrice, 2, '.', '') . "*CC:CZK*X-VS:$variableCode";
        $qrCode = new QrCode($qrData);
        $writer = new PngWriter();
        $qrImage = $writer->write($qrCode);

        $qrFilePath = __DIR__ . "/../../www/QRcodes/{$variableCode}.png";

        file_put_contents($qrFilePath, $qrImage->getString());
        $mail = new Nette\Mail\Message;

        $cid = $mail->addEmbeddedFile(
            "/{$variableCode}.png",
            file_get_contents($qrFilePath),
            )->getHeader('Content-ID');

        $cid = str_replace(['<', '>'],'', $cid);
        
        $params = [
            'firstname' => $firstname,
            'lastname' => $lastname,
            'bankAccount' => '51-7080060207/0100',
            'variableCode' => $variableCode,
            'totalPrice' => $totalPrice,
            'cid' => $cid,
        ];
        $html = $latte->renderToString(__DIR__ . '/ticket_purchase.latte', $params);

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