<?php

declare(strict_types=1);

namespace Controller\Front;

use Controller\Controller;
use Model\PlanAlimentaire;
use Model\Objectif;

/**
 * AI Digital Twin Controller
 * High-tech metabolic simulation and projection.
 */
final class DigitalTwinController extends Controller
{
    public function index(): void
    {
        $planModel = new PlanAlimentaire();
        $objectifModel = new Objectif();

        $plans = $planModel->all();
        $objectifs = $objectifModel->all();

        if (empty($objectifs)) {
            $this->view('front/digital_twin', [
                'error' => 'Aucun objectif trouvé. Créez un objectif pour initialiser votre Jumeau Numérique.',
                'title' => 'AI Digital Twin'
            ]);
            return;
        }

        // Allow selection of objective via GET, default to most recent
        $selectedId = isset($_GET['id_obj']) ? (int)$_GET['id_obj'] : null;
        $mainObj = null;

        if ($selectedId) {
            foreach ($objectifs as $obj) {
                if ((int)$obj['id_obj'] === $selectedId) {
                    $mainObj = $obj;
                    break;
                }
            }
        }

        if (!$mainObj) {
            $mainObj = $objectifs[0];
        }

        $simulation = $this->runMetabolicSimulation($mainObj, $plans);

        $this->view('front/digital_twin', [
            'simulation' => $simulation,
            'objectifs' => $objectifs,
            'selectedId' => (int)$mainObj['id_obj'],
            'title' => 'AI Digital Twin & Projection',
            'sidebarActive' => 'digital_twin'
        ]);
    }

    private function runMetabolicSimulation(array $objectif, array $plans): array
    {
        $targetCal = (int)$objectif['calories_cible'];
        $objType = strtolower($objectif['type']);
        
        // Simulation constants
        $startWeight = 75.0; // Simulated start weight in kg
        $kcalPerKg = 7700;
        $projectionDays = 90;
        
        // Maintenance Estimation
        // If the objective is "Perte de poids", maintenance is probably higher than target.
        // If "Prise de masse", maintenance is lower.
        $maintenance = $targetCal;
        if (str_contains($objType, 'perte')) {
            $maintenance = (int)($targetCal / 0.8); // Assume 20% deficit
        } elseif (str_contains($objType, 'prise')) {
            $maintenance = (int)($targetCal / 1.15); // Assume 15% surplus
        }

        // Calculate Average Actual Intake from plans
        $totalPlanCal = 0;
        $validPlansCount = 0;
        foreach ($plans as $plan) {
            if ($plan['id_obj'] == $objectif['id_obj']) {
                $totalPlanCal += (int)$plan['calories_cible'];
                $validPlansCount++;
            }
        }
        $avgIntake = $validPlansCount > 0 ? ($totalPlanCal / $validPlansCount) : $targetCal;

        // Daily Delta
        $dailyDelta = $avgIntake - $maintenance;
        
        // Projection Data
        $projection = [];
        $currentWeight = $startWeight;
        for ($i = 0; $i <= $projectionDays; $i += 10) {
            $weightAtDay = $startWeight + (($dailyDelta * $i) / $kcalPerKg);
            $projection[] = [
                'day' => $i,
                'weight' => round($weightAtDay, 2)
            ];
        }

        $finalWeight = $startWeight + (($dailyDelta * $projectionDays) / $kcalPerKg);
        $totalLossGain = $finalWeight - $startWeight;

        // Gap Closing Logic
        $status = 'success';
        $correctionMessage = "Votre stratégie actuelle est parfaitement alignée.";
        $correctionValue = 0;

        if (str_contains($objType, 'perte') && $totalLossGain >= -1) {
            $status = 'warning';
            $neededDelta = -500; // Aim for 0.5kg/week loss
            $correctionValue = $neededDelta - $dailyDelta;
            $correctionMessage = "Attention : Votre trajectoire est trop lente pour une perte de poids efficace. Ajustement requis.";
        } elseif (str_contains($objType, 'prise') && $totalLossGain <= 1) {
            $status = 'warning';
            $neededDelta = 300;
            $correctionValue = $neededDelta - $dailyDelta;
            $correctionMessage = "Note : Votre surplus actuel est trop faible pour une prise de masse optimale.";
        }

        return [
            'objectif' => $objectif,
            'maintenance' => $maintenance,
            'avg_intake' => round($avgIntake),
            'start_weight' => $startWeight,
            'final_weight' => round($finalWeight, 2),
            'total_change' => round($totalLossGain, 2),
            'projection' => $projection,
            'status' => $status,
            'correction_message' => $correctionMessage,
            'correction_value' => round($correctionValue),
            'burn_rate' => round($dailyDelta)
        ];
    }

    protected function layoutArea(): string
    {
        return 'front';
    }
}
