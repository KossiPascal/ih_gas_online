<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */

    protected $policies = [
        'achat' => 'App\Policies\AchatPolicy',
        'assurance' => 'App\Policies\AssurancePolicy',
        'categorie' => 'App\Policies\CategoriePolicy',
        'commande' => 'App\Policies\CommandePolicy',
        'confection_kit' => 'App\Policies\ConfectionKitPolicy',
        'entree' => 'App\Policies\EntreePolicy',
        'fournisseur' => 'App\Policies\FournisseurPolicy',
        'magasin' => 'App\Policies\MagasinPolicy',
        'produit' => 'App\Policies\ProduitPolicy',
        'reception_commande' => 'App\Policies\ReceptionCommandePolicy',
        'sortie' => 'App\Policies\SortiePolicy',
        'stock' => 'App\Policies\StockPolicy',
        'transfert' => 'App\Policies\TransfertPolicy',
        'profil' => 'App\Policies\ProfilePolicy',
        'utilisateur' => 'App\Policies\UserPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        Gate::define('manage-user',function($user){
            $profils = $user->profils()->get()->pluck('nom')->toArray();
            return $user->hasAnyprofil($profils);
        });

        Gate::define('manage-action', function ($user, $groupe,$action) {
            return $user->allows($groupe,$action);
        });

        Gate::define('voir-user', function ($user) {
            return $user->isUser();
        });

        Gate::define('voir-dps', function ($user) {
            return $user->isDPS();
        });

        Gate::define('voir-si', function ($user) {
            return $user->isSI();
        });
    }
}
