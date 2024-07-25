<?php

declare(strict_types=1);

namespace App\Controller;

/**
 * Articles Controller
 *
 * @property \App\Model\Table\ArticlesTable $Articles
 */
class ArticlesController extends AppController
{
    protected array $paginate = [
        // Other keys here.
        'maxLimit' => 10
    ];

    /**
     * Index method
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function index()
    {
        $query = $this->Articles->find();
        $articles = $this->paginate($query);

        $this->set(compact('articles'));

        if ($this->request->getHeaderLine('HX-Request') === "true") {
            $this->viewBuilder()->disableAutoLayout();
        }
    }

    public function search()
    {
        $this->viewBuilder()->setLayout('old_default');
        $query = $this->Articles->find();
        $q = $this->request->getQuery('q');
        if (!empty($q)) {
            $query = $query->where([
                'title LIKE' => '%' . $q . '%',
            ]);
        }
        $articles = $this->paginate($query);

        $this->set(compact('articles'));
        if ($this->request->is('htmx')) {
            $this->viewBuilder()->disableAutoLayout();
            $this->viewBuilder()->setTemplatePath('element');

            $elements = $this->request->getHeader('Cake-Element');
            $element = count($elements) > 0 ? $elements[0] : 'search';
            $this->viewBuilder()->setTemplate('htmx/articles/' . $element);
        }
    }

    public function htmxIndex()
    {
        $query = $this->Articles->find();
        $articles = $this->paginate($query);

        $this->set(compact('articles'));

        if ($this->getRequest()->is('htmx')) {
            $this->viewBuilder()->disableAutoLayout();
            $this->Htmx->setBlock($this->Htmx->getTarget());
        }
    }

    public function htmxIndexWithElement()
    {
        $query = $this->Articles->find();
        $articles = $this->paginate($query);

        $this->set(compact('articles'));

        if ($this->getRequest()->is('htmx')) {
            $this->viewBuilder()->disableAutoLayout();
            $this->viewBuilder()->setTemplatePath('element');
            $this->viewBuilder()->setTemplate('htmx/articles/index');
        }
    }
}
