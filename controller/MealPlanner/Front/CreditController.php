<?php

declare(strict_types=1);

namespace Controller\Front;

use Controller\Controller;
use Controller\Url;
use Model\CreditWallet;
use Model\Objectif;

final class CreditController extends Controller
{
    public function index(): void
    {
        $walletModel = new CreditWallet();
        $objectifModel = new Objectif();
        
        $objectifs = $objectifModel->all();
        $selectedId = isset($_GET['id_obj']) ? (int)$_GET['id_obj'] : null;
        
        // On cherche l'objectif sélectionné, sinon on prend le premier
        $mainObj = null;
        if ($selectedId !== null) {
            foreach ($objectifs as $obj) {
                if ((int)$obj['id_obj'] === $selectedId) {
                    $mainObj = $obj;
                    break;
                }
            }
        }
        
        if (!$mainObj && !empty($objectifs)) {
            $mainObj = $objectifs[0];
            $selectedId = (int)$mainObj['id_obj'];
        }

        $budget = $walletModel->getDailyBudget($selectedId);
        $spent = $walletModel->getSpentToday($selectedId);
        $discount = $walletModel->getQualityDiscount($selectedId);
        $history = $walletModel->getSavingsHistory($selectedId);
        
        $totalSaved = array_sum(array_column($history, 'saved'));
        $grade = $walletModel->getUserGrade($totalSaved);
        
        // Analyse de faillite (Banqueroute Énergétique)
        $hour = (int) date('H');
        $spentRatio = $budget > 0 ? ($spent / $budget) : 0;
        $riskOfBankruptcy = ($hour < 16 && $spentRatio > 0.75);

        $this->view('front/wallet', [
            'budget' => $budget,
            'spent' => $spent,
            'discount' => $discount,
            'remaining' => max(0, $budget - $spent + $discount),
            'history' => $history,
            'totalSaved' => $totalSaved,
            'grade' => $grade,
            'risk' => $riskOfBankruptcy,
            'objectifs' => $objectifs,
            'selectedId' => $selectedId,
            'url' => Url::class,
            'csrf' => $this->csrfToken(),
        ]);
    }
}

