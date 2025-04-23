<?php
namespace App\UI\Front\Home;

use App\Model\FestivalFacade;
use App\MailSender\MailSender;
use Latte\Engine;
use Nette\Application\UI\Form;
use Tracy\Debugger;
use Nette\Utils\Paginator;
use Nette;
final class HomePresenter extends Nette\Application\UI\Presenter
{

    public function __construct(
		private FestivalFacade $festivalFacade,
        private MailSender $mailSender,

	) {
	}
    
    public function renderDefault(int $page = 1, string $order = 'created_at'): void
    {
        $order = $this->getParameter('order') ?? 'created_at'; // Získání hodnoty z URL
    
        // Celkový počet festivalů
        $totalFestivals = $this->festivalFacade->getFestivalCount();
    
        // Nastavení paginatoru
        $paginator = new Paginator();
        $paginator->setItemsPerPage(9);
        $paginator->setPage($page);
        $paginator->setItemCount($totalFestivals);
    
        // Načtení festivalů s omezením podle paginatoru a řazení
        $festivals = $this->festivalFacade->getFestivalsWithMainImage(
            $order, 
            $paginator->getLength(), 
            $paginator->getOffset()
        );
    
        // Předání do šablony
        $this->template->order = $order;
        $this->template->festivals = $festivals;
        $this->template->trendingFestivals = $this->festivalFacade->getTopTrendingFestivals();
        $this->template->paginator = $paginator;
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
        $keyword = $values['search'] ?? null;
    
        if ($keyword === null || $keyword === '') {
            $this->flashMessage('Zadejte slovo pro vyhledávání.', 'warning');
            $this->redirect('this');
        }
    
        $this->redirect('Search:results', ['keyword' => $keyword]);
    }

}
