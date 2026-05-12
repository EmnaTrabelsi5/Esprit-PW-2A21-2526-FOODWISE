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
    'front_password_reset' => '?route=module2.front.password_reset',
    'front_verify_reset_code' => '?route=module2.front.verify_reset_code',
    'front_logout'         => '?route=module2.front.logout',
    'front_profil_edit'    => '?route=module2.front.profil.edit',
    'front_allergies'      => '?route=module2.front.allergies_regimes',
    'front_users_list'     => '?route=module2.front.users_list',
    'front_chat'           => '?route=module2.front.chat',
    'front_get_unread_count' => '?route=module2.front.get_unread_count',
    'front_public_view'    => '?route=module2.front.public_view',
    'api_toggle_visibility' => '?route=module2.api.toggle_visibility',
    // Back-office (admin)
    'back_login'           => '?route=module2.back.login',
    'back_logout'          => '?route=module2.back.logout',
    'back_dashboard_profils' => '?route=module2.back.dashboard.profils',
    'back_profil_form' => '?route=module2.back.profil.form',
    'back_suspend_user' => '?route=module2.back.suspend.user',
    'back_lift_suspension' => '?route=module2.back.lift.suspension',
    'back_ban_user' => '?route=module2.back.ban.user',
    'back_unban_user' => '?route=module2.back.unban.user',
];

