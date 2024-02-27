<?php

namespace App\Actions\SortieProduit;

use App\Models\StockProduit;
use App\Models\User;
use Lorisleiva\Actions\Concerns\AsAction;

class StoreSortieProduit
{
    use AsAction;

    /** @param  array<mixed>  $data */
    public function handle(User $user, array $data)
    {
        collect($data['produits'])
            ->each(function ($each) use ($user, $data) {
                $user->sortieProduits()->make($each)
                    ->fill(['stock_produit_id' => $each['id']])
                    ->fill(['centre_id' => $user->centre_id])
                    ->fill(['motif' => $data['motif']])
                    ->save();

                StockProduit::query()
                    ->whereKey($each['id'])
                    ->decrement('qte', $each['qte'] * $each['unite']);
            });
    }
}
