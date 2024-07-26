<?php

declare(strict_types=1);

namespace App\Controller;

/**
 * Raffle Controller
 *
 */
class RaffleController extends AppController
{
    public function index()
    {
        $this->viewBuilder()->setLayout('old_default');
    }

    public function startPicker()
    {
        $this->viewBuilder()->disableAutoLayout();
        $this->set('min', $this->request->getData('min'));
        $this->set('max', $this->request->getData('max'));
    }

    public function chooseWinner()
    {
        $this->viewBuilder()->disableAutoLayout();
        $this->set('min', $this->request->getQuery('min'));
        $this->set('max', $this->request->getQuery('max'));

        if (rand(0, 1)) {
            $winner = rand(
                (int)$this->request->getQuery('min'),
                (int)$this->request->getQuery('max')
            );
            $this->set('winner', $winner);
            $this->Htmx->stopPolling();
        }
    }
}
