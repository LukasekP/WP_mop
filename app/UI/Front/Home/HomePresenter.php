<?php
namespace App\UI\Front\Home;

use App\Model\FestivalFacade;
use App\MailSender\MailSender;
use Latte\Engine;

use Nette;
final class HomePresenter extends Nette\Application\UI\Presenter
{

    public function __construct(
		private FestivalFacade $festivalFacade,
        private MailSender $mailSender,

	) {
	}
    public function renderDefault(): void
    {
        $this->template->festivals = $this->festivalFacade->getFestivals();

    }
    public function handleSendEmail() 
	{
        $mail = $this->mailSender->createNotificationEmail("Lukáš", "Pražák");
        $this->mailSender->sendEmail($mail);
	}
}
