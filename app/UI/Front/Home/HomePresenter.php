<?php
namespace App\UI\Front\Home;

use App\Model\FestivalFacade;
use App\MailSender\MailSender;
use Latte\Engine;
use Nette\Application\UI\Form;

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
        $this->template->festivals = $this->festivalFacade->getFestivalsWithMainImage();
        $this->template->trendingFestivals = $this->festivalFacade->getFestivalsWithMainImage();

        $this->template->trendingFestivals = $this->festivalFacade->getTopTrendingFestivals();

       
    }
    public function createComponentSearchForm(): Form
{
    $form = new Form;
    $form->addText('search')
         ->setHtmlAttribute('class', 'form-control')
         ->setNullable();
    $form->addSubmit('send', 'Hledat')
         ->setHtmlAttribute('class', 'btn btn-primary');
    $form->onSuccess[] = [$this, 'searchFormSucceeded'];
    return $form;
}
public function searchFormSucceeded(Form $form, array $values): void
{
    $this->redirect('Search:results', $values['search']);
}

}
