<?php

namespace App\Actions\EntreeProduit;

use App\Models\StockProduit;
use App\Models\User;
use Lorisleiva\Actions\Concerns\AsAction;

class StoreEntreeProduit
{
    use AsAction;

    /** @param  array<mixed>  $data */
    public function handle(User $user, array $data)
    {
        collect($data['produits'])
            ->each(function ($each) use ($user, $data) {
                $user->entreeProduits()->make($each)
                    ->fill(['stock_produit_id' => $each['id']])
                    ->fill(['centre_id' => $user->centre_id])
                    ->fill(['motif' => $data['motif']])
                    ->save();

                StockProduit::query()
                    ->whereKey($each['id'])
                    ->increment('qte', $each['qte'] * $each['unite']);
            });
    }
}
