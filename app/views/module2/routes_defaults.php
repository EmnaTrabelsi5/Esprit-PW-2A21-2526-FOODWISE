<?php
declare(strict_types=1);

/**
 * Routes par défaut Module 2 — à surcharger depuis le contrôleur via $routesModule2.
 * Exemple contrôleur : $routesModule2['front_mon_profil'] = $router->url('module2.front.mon_profil');
 */
if (!isset($routesModule2) || !is_array($routesModule2)) {
    $routesModule2 = [];
}

$routesModule2 += [
    // Front-office (client)
    'front_mon_profil'     => '?route=module2.front.mon_profil',
    'front_inscription'    => '?route=module2.front.inscription',
    'front_connexion'      => '?route=module2.front.connexion',
    'front_profil_edit'    => '?route=module2.front.profil.edit',
    'front_allergies'      => '?route=module2.front.allergies_regimes',
    // Back-office (admin)
    'back_dashboard_profils' => '?route=module2.back.dashboard.profils',
    'back_profil_form' => '?route=module2.back.profil.form',
];
