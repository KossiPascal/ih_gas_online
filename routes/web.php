<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CategorieController;
use App\Http\Controllers\ProduitController;
use App\Http\Controllers\MagasinController;
use App\Http\Controllers\AssuranceController;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\MutuelleController;
use App\Http\Controllers\FournisseurController;
use App\Http\Controllers\CommandeController;
use App\Http\Controllers\AchatController;
use App\Http\Controllers\TransfertController;
use App\Http\Controllers\EntreeController;
use App\Http\Controllers\SortieController;
use App\Http\Controllers\InventaireController;
use App\Http\Controllers\InventaireSIController;
use App\Http\Controllers\ReceptionController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CSController;
use App\http\Controllers\ASCController;
use App\Http\Controllers\ValidationController;
use App\Http\Controllers\OperationBancaireController;
use App\Http\Controllers\ReceptionSiController;
use App\Http\Controllers\TransfertSiController;
use App\Http\Controllers\ReceptionDpsController;
use App\Http\Controllers\TransfertDpsController;
use App\Http\Controllers\CentreController;
use App\Http\Controllers\CorrectionStockController;
use App\Http\Controllers\VenteController;
use App\Http\Controllers\ReglementController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\EtatController;
use App\Http\Controllers\LocaleController;

#use Mcamara\LaravelLocalization\Facades\LaravelLocalization;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::group([
        // 'prefix' => '{locale}/ihgas', 'where' => ['locale' => 'fr|en']
        'prefix' => ''
        //'prefix' => LaravelLocalization::setLocale().'/ihgas',
        //'prefix' => 'ihgas',
        //'middleware' => [ 'localizationRedirect', 'localeViewPath' ],
        //'middleware' => [ 'localeSessionRedirect', 'localizationRedirect', 'localeViewPath' ]
    ], function(){ 
        Route::middleware('auth')->group(function (){
            Route::get('/', [HomeController::class,'index'])->name('app.home');
        });
        Route::middleware('auth','can:manage-user')->group(function (){
           // Route::get('/', [HomeController::class,'index'])->name('app.home');
            Route::resource('cat',CategorieController::class);
            Route::get('cat.delete/{id}', [CategorieController::class,'delete']);

            Route::resource('pdt',ProduitController::class);
            Route::post('pdt.storenp', [ProduitController::class,'storenp'])->name('pdt.storenp');
            Route::get('pdt.delete/{id}', [ProduitController::class,'delete']);

            Route::resource('mag',MagasinController::class);
            Route::get('mag.delete/{id}', [MagasinController::class,'delete']);
            Route::get('mag.stock/{id}', [MagasinController::class,'stock']);
        
            Route::resource('ass',AssuranceController::class);
            Route::get('ass.delete/{id}', [AssuranceController::class,'delete']);
        
            Route::resource('centre',CentreController::class);
            Route::post('centre/updatecentre', [CentreController::class,'updatecentre'])->name('centre.updatecentre');
        
            Route::resource('four',FournisseurController::class);
            Route::get('four.delete/{id}', [FournisseurController::class,'delete']);
        
            /*Route::resource('ini',InitialisationController::class);
            Route::get('ini.select/{id}',[InitialisationController::class,'select'])->name('ini.select');
            Route::get('ini.select_edit/{id}',[InitialisationController::class,'select_edit'])->name('ini.select_edit');
            Route::get('ini.delete/{id}',[InitialisationController::class,'delete'])->name('ini.delete');
            Route::get('ini.magasins',[InitialisationController::class,'magasins'])->name('ini.magasins');
            Route::post('ini.add',[InitialisationController::class,'add'])->name('ini.add');
            Route::get('ini.rech_pdtcon/{id}',[InitialisationController::class,'rech_pdtcon'])->name('ini.rech_pdtcon');
            Route::get('ini.editer',[InitialisationController::class,'editer'])->name('ini.editer');
            Route::get('ini.histo',[InitialisationController::class,'histo'])->name('ini.histo');*/
        
            Route::resource('cmde',CommandeController::class);
            Route::get('cmde.rech_cmde/{id}',[CommandeController::class,'rech_cmde'])->name('cmde.rech_cmde');
            Route::get('cmde.rech_mont/{id}',[CommandeController::class,'rech_mont'])->name('cmde.rech_mont');
            Route::get('cmde.rech_pdtcon/{id}',[CommandeController::class,'rech_pdtcon'])->name('cmde.rech_pdtcon');
            Route::get('cmde.select/{id}',[CommandeController::class,'select'])->name('cmde.select');
            Route::get('cmde.select_edit/{id}',[CommandeController::class,'select_edit'])->name('cmde.select_edit');
            Route::get('cmde.delete/{id}',[CommandeController::class,'delete'])->name('cmde.delete');
            Route::get('cmde.fournisseurs',[CommandeController::class,'fournisseurs'])->name('cmde.fournisseurs');
            Route::get('cmde.four_edit/{id}',[CommandeController::class,'four_edit'])->name('cmde.four_edit');
            Route::post('cmde.add',[CommandeController::class,'add'])->name('cmde.add');
            Route::get('cmde.editer',[CommandeController::class,'editer'])->name('cmde.editer');
            Route::get('cmde.histo',[CommandeController::class,'histo'])->name('cmde.histo');
            Route::get('cmde.infos/{id}',[CommandeController::class,'infos'])->name('cmde.infos');
            Route::get('cmde.delete_cmde/{id}',[CommandeController::class,'delete_cmde'])->name('cmde.delete_cmde');
            Route::get('cmde.annuler_cmde/{id}',[CommandeController::class,'annuler_cmde'])->name('cmde.annuler_cmde');
            Route::get('cmde.cmde',[CommandeController::class,'commande'])->name('cmde.cmde');
            Route::get('cmde.valider/{id}',[CommandeController::class,'valider'])->name('cmde.valider');
            Route::post('cmde.observer',[CommandeController::class,'observer'])->name('cmde.observer');
        
            Route::resource('val',ValidationController::class);
            Route::get('val.cmde/{id}',[ValidationController::class,'cmde'])->name('val.cmde');
            Route::get('val.val/{id}',[ValidationController::class,'val'])->name('val.val');
            Route::post('val.update_val',[ValidationController::class,'update_val'])->name('val.update_val');
            Route::get('val.valider/{id}',[ValidationController::class,'valider'])->name('val.valider');
            Route::get('val.details/{id}',[ValidationController::class,'details'])->name('val.details');
            Route::get('val.histo',[ValidationController::class,'histo'])->name('val.histo');
            
        
            Route::resource('rec',ReceptionController::class);
            Route::get('rec.receptions',[ReceptionController::class,'receptions'])->name('rec.receptions');
            Route::get('rec.commandes',[ReceptionController::class,'commandes'])->name('rec.commandes');
            Route::get('rec.magasins',[ReceptionController::class,'magasins'])->name('rec.magasins');
            Route::get('rec.pdt_cmde/{id}',[ReceptionController::class,'pdt_cmde'])->name('rec.pdt_cmde');
            Route::get('rec.rech_pdtAdd/{id}/{cmde}',[ReceptionController::class,'rech_pdtAdd'])->name('rec.rech_pdtAdd');
            Route::get('rec.pdt_rec/{id}/{tr}',[ReceptionController::class,'pdt_rec'])->name('rec.pdt_rec');
            Route::get('rec.pdt_rec_af/{id}',[ReceptionController::class,'pdt_rec_af'])->name('rec.pdt_rec_af');
            Route::get('rec.rech_mont/{id}',[ReceptionController::class,'rech_mont'])->name('rec.rech_mont');
            Route::get('rec.select/{id}',[ReceptionController::class,'select'])->name('rec.select');
            Route::get('rec.select_edit/{id}',[ReceptionController::class,'select_edit'])->name('rec.select_edit');
            Route::get('rec.delete/{id}',[ReceptionController::class,'delete'])->name('rec.delete');
            Route::get('rec.getreception/{id}',[ReceptionController::class,'getreception'])->name('rec.getreception');
            Route::post('rec.add',[ReceptionController::class,'add'])->name('rec.add');
            Route::get('rec.editer',[ReceptionController::class,'editer'])->name('rec.editer');
            Route::get('rec.histo',[ReceptionController::class,'histo'])->name('rec.histo');
            Route::get('rec.cmde/{id}',[ReceptionController::class,'rec_cmde'])->name('rec.cmde');
        
            //RECEPTION SI
            Route::resource('recsi',ReceptionSiController::class);
            Route::get('recsi.commandes',[ReceptionSiController::class,'commandes'])->name('recsi.commandes');
            Route::get('recsi.magasins',[ReceptionSiController::class,'magasins'])->name('recsi.magasins');
            Route::get('recsi.pdt_cmde/{id}',[ReceptionSiController::class,'pdt_cmde'])->name('recsi.pdt_cmde');
            Route::get('recsi.rech_pdtAdd/{id}/{cmde}',[ReceptionSiController::class,'rech_pdtAdd'])->name('recsi.rech_pdtAdd');
            Route::get('recsi.pdt_rec/{id}/{cmde}',[ReceptionSiController::class,'pdt_rec'])->name('recsi.pdt_rec');
            Route::get('recsi.pdtrec/{id}/{cmde}',[ReceptionSiController::class,'pdtrec'])->name('recsi.pdtrec');
            Route::get('recsi.details_rec/{id}',[ReceptionSiController::class,'details_rec'])->name('recsi.details_rec');
            Route::get('recsi.select/{id}/{cmde}',[ReceptionSiController::class,'select'])->name('recsi.select');
            Route::get('recsi.select_edit/{id}/{cmde}',[ReceptionSiController::class,'select_edit'])->name('recsi.select_edit');
            Route::get('recsi.delete/{id}',[ReceptionSiController::class,'delete'])->name('recsi.delete');
            Route::post('recsi.add',[ReceptionSiController::class,'add'])->name('recsi.add');
            Route::get('recsi.editer',[ReceptionSiController::class,'editer'])->name('recsi.editer');
            Route::get('recsi.histo',[ReceptionSiController::class,'histo'])->name('recsi.histo');
            Route::get('recsi.cmde/{id}',[ReceptionSiController::class,'rec_cmde'])->name('recsi.cmde');

            //RECEPTION DPS
            Route::resource('recdps',ReceptionDpsController::class);
            Route::get('recdps.commandes',[ReceptionDpsController::class,'commandes'])->name('recdps.commandes');
            Route::get('recdps.magasins',[ReceptionDpsController::class,'magasins'])->name('recdps.magasins');
            Route::get('recdps.pdt_cmde/{id}',[ReceptionDpsController::class,'pdt_cmde'])->name('recdps.pdt_cmde');
            Route::get('recdps.rech_pdtAdd/{id}/{cmde}',[ReceptionDpsController::class,'rech_pdtAdd'])->name('recdps.rech_pdtAdd');
            Route::get('recdps.pdt_rec/{id}/{cmde}',[ReceptionDpsController::class,'pdt_rec'])->name('recdps.pdt_rec');
            Route::get('recdps.pdtrec/{id}/{cmde}',[ReceptionDpsController::class,'pdtrec'])->name('recdps.pdtrec');
            Route::get('recdps.details_rec/{id}',[ReceptionDpsController::class,'details_rec'])->name('recdps.details_rec');
            Route::get('recdps.select/{id}/{cmde}',[ReceptionDpsController::class,'select'])->name('recdps.select');
            Route::get('recdps.select_edit/{id}/{cmde}',[ReceptionDpsController::class,'select_edit'])->name('recdps.select_edit');
            Route::get('recdps.delete/{id}',[ReceptionDpsController::class,'delete'])->name('recdps.delete');
            Route::post('recdps.add',[ReceptionDpsController::class,'add'])->name('recdps.add');
            Route::get('recdps.editer',[ReceptionDpsController::class,'editer'])->name('recdps.editer');
            Route::get('recdps.histo',[ReceptionDpsController::class,'histo'])->name('recdps.histo');
            Route::get('recdps.cmde/{id}',[ReceptionDpsController::class,'rec_cmde'])->name('recdps.cmde');
        
            Route::resource('ach',AchatController::class);
            Route::get('ach.magasins',[AchatController::class,'magasins'])->name('ach.magasins');
            Route::get('ach.pdt_rec/{id}',[AchatController::class,'pdt_rec'])->name('ach.pdt_rec');
            Route::get('ach.rech_mont/{id}',[AchatController::class,'rech_mont'])->name('ach.rech_mont');
            Route::get('ach.select/{id}',[AchatController::class,'select'])->name('ach.select');
            Route::get('ach.select_edit/{id}',[AchatController::class,'select_edit'])->name('ach.select_edit');
            Route::get('ach.delete/{id}',[AchatController::class,'delete'])->name('ach.delete');
            Route::post('ach.add',[AchatController::class,'add'])->name('ach.add');
            Route::get('ach.editer',[AchatController::class,'editer'])->name('ach.editer');
            Route::get('ach.histo',[AchatController::class,'histo'])->name('ach.histo');
            Route::get('ach.cmde/{id}',[AchatController::class,'rec_cmde'])->name('ach.cmde');
        
            //TRANSFERT FS
            Route::resource('tr',TransfertController::class);
            Route::get('tr.magasins',[TransfertController::class,'magasins'])->name('tr.magasins');
            Route::get('tr.pdt_sour/{id}',[TransfertController::class,'pdt_sour'])->name('tr.pdt_sour');
            Route::get('tr.pdt_dest/{id}',[TransfertController::class,'pdt_dest'])->name('tr.pdt_dest');
            Route::get('tr.select/{id}',[TransfertController::class,'select'])->name('tr.select');
            Route::get('tr.select_edit/{id}',[TransfertController::class,'select_edit'])->name('tr.select_edit');
            Route::get('tr.histo',[TransfertController::class,'histo'])->name('tr.histo');
            Route::post('tr.add',[TransfertController::class,'add'])->name('tr.add');
            Route::get('tr.editer',[TransfertController::class,'editer'])->name('tr.editer');
            Route::get('tr.delete/{id}',[TransfertController::class,'delete'])->name('tr.delete');
            Route::get('tr.maj/{id}',[TransfertController::class,'maj'])->name('tr.maj');
        
            //TRANSFERT SI
            Route::resource('trsi',TransfertSiController::class);
            Route::get('trsi.pdt_recu/{id}',[TransfertSiController::class,'pdt_recu'])->name('trsi.pdt_recu');
            Route::get('trsi.pdt_tr/{id}',[TransfertSiController::class,'pdt_tr'])->name('trsi.pdt_tr');
            Route::get('trsi.select/{id}',[TransfertSiController::class,'select'])->name('trsi.select');
            Route::get('trsi.reception',[TransfertSiController::class,'reception'])->name('trsi.reception');
            Route::get('trsi.histo',[TransfertSiController::class,'histo'])->name('trsi.histo');
            Route::post('trsi.add',[TransfertSiController::class,'add'])->name('trsi.add');
            Route::get('trsi.editer',[TransfertSiController::class,'editer'])->name('trsi.editer');
            Route::get('trsi.details_tr/{id}',[TransfertSiController::class,'details_tr'])->name('trsi.details_tr');
            Route::get('trsi.maj/{id}',[TransfertSiController::class,'maj'])->name('trsi.maj');
            Route::get('trsi.getcommande/{id}',[TransfertSiController::class,'getcommande'])->name('trsi.getcommande');

            //TRANSFERT DPS
            Route::resource('trdps',TransfertDpsController::class);
            Route::get('trdps.pdt_recu/{id}',[TransfertDpsController::class,'pdt_recu'])->name('trdps.pdt_recu');
            Route::get('trdps.pdt_tr/{id}',[TransfertDpsController::class,'pdt_tr'])->name('trdps.pdt_tr');
            Route::get('trdps.select/{id}',[TransfertDpsController::class,'select'])->name('trdps.select');
            Route::get('trdps.reception',[TransfertDpsController::class,'reception'])->name('trdps.reception');
            Route::get('trdps.histo',[TransfertDpsController::class,'histo'])->name('trdps.histo');
            Route::post('trdps.add',[TransfertDpsController::class,'add'])->name('trdps.add');
            Route::get('trdps.editer',[TransfertDpsController::class,'editer'])->name('trdps.editer');
            Route::get('trdps.details_tr/{id}',[TransfertDpsController::class,'details_tr'])->name('trdps.details_tr');
            Route::get('trdps.maj/{id}',[TransfertDpsController::class,'maj'])->name('trdps.maj');
            Route::get('trdps.getcommande/{id}',[TransfertDpsController::class,'getcommande'])->name('trdps.getcommande');
        
            Route::resource('sor',SortieController::class);
            Route::get('sor.magasins',[SortieController::class,'magasins'])->name('sor.magasins');
            Route::get('sor.pdt_sour/{id}',[SortieController::class,'pdt_sour'])->name('sor.pdt_sour');
            Route::get('sor.pdt_con/{id}/{mag}',[SortieController::class,'pdt_con'])->name('sor.pdt_con');
            Route::get('sor.select/{id}',[SortieController::class,'select'])->name('sor.select');
            Route::get('sor.select_edit/{id}',[SortieController::class,'select_edit'])->name('sor.select_edit');
            Route::get('sor.histo',[SortieController::class,'histo'])->name('sor.histo');
            Route::post('sor.add',[SortieController::class,'add'])->name('sor.add');
            Route::get('sor.delete/{id}',[SortieController::class,'delete'])->name('sor.delete');
        
            Route::resource('ent',EntreeController::class);
            Route::get('ent.magasins',[EntreeController::class,'magasins'])->name('ent.magasins');
            Route::get('ent.pdt_sour/{id}',[EntreeController::class,'pdt_sour'])->name('ent.pdt_sour');
            Route::get('ent.pdt_con/{id}/{mag}',[EntreeController::class,'pdt_con'])->name('ent.pdt_con');
            Route::get('ent.select/{id}',[EntreeController::class,'select'])->name('ent.select');
            Route::get('ent.select_edit/{id}',[EntreeController::class,'select_edit'])->name('ent.select_edit');
            Route::get('ent.histo',[EntreeController::class,'histo'])->name('ent.histo');
            Route::post('ent.add',[EntreeController::class,'add'])->name('ent.add');
            Route::get('ent.delete/{id}',[EntreeController::class,'delete'])->name('ent.delete');
        
            /*Vente  Controller*/
            Route::resource('vente',VenteController::class);
            Route::get('vente.ventes',[VenteController::class,'ventes'])->name('vente.ventes');
            Route::get('vente.selectionner/{id}',[VenteController::class,'selectionner'])->name('vente.selectionner');
            Route::get('vente.encaisse',[VenteController::class,'encaisse'])->name('vente.encaisse');
            Route::post('vente.validerCaisse',[VenteController::class,'validerCaisse'])->name('vente.validerCaisse');
            Route::get('vente.assurances',[VenteController::class,'assurances'])->name('vente.assurances');
            Route::get('vente.rech_code/{id}',[VenteController::class,'rech_code'])->name('vente.rech_code');
            Route::get('vente.select/{id}',[VenteController::class,'select'])->name('vente.select');
            Route::get('vente.select_mut/{id}',[VenteController::class,'select_mut'])->name('vente.select_mut');
            Route::get('vente.rechPdtMut/{id}/{mut}',[VenteController::class,'rechPdtMut'])->name('vente.rechPdtMut');
            Route::get('vente.select_edit/{id}',[VenteController::class,'select_edit'])->name('selectedit');
            Route::get('vente.rech_pdtcon/{id}',[VenteController::class,'rech_pdtcon'])->name('vente.rech_pdtcon');
            Route::get('vente.rech_mont/{id}',[VenteController::class,'rech_mont'])->name('vente.rech_mont');
            Route::get('vente.rechtaux/{id}',[VenteController::class,'rechtaux'])->name('vente.rechtaux');
            Route::get('vente.delete/{id}', [VenteController::class,'delete'])->name('vente.delete');
            Route::get('vente.annuler/{id}', [VenteController::class,'annuler'])->name('vente.annuler');
            Route::get('vente.annulervente/{id}', [VenteController::class,'annulervente'])->name('vente.annulervente');
            Route::get('vente.histo',[VenteController::class,'histo'])->name('vente.histo');
            Route::get('vente.histoenc',[VenteController::class,'histoenc'])->name('vente.histoenc');
            Route::get('vente.etat',[VenteController::class,'etat'])->name('vente.etat');
            Route::get('vente.imprimer_ef/{debut}/{fin}',[VenteController::class,'imprimer_ef'])->name('vente.imprimer_ef');
            Route::get('vente.print_ef/{debut}/{fin}',[VenteController::class,'print_ef'])->name('vente.print_ef');
            Route::get('vente.print_ef_personnel/{debut}/{fin}',[VenteController::class,'print_ef_personnel'])->name('vente.print_ef');
            Route::get('vente.printef',[VenteController::class,'print_ef'])->name('vente.printef');
            Route::get('vente.credit',[VenteController::class,'credit'])->name('vente.credit');
            Route::get('vente.credit_liste',[VenteController::class,'credit_liste'])->name('vente.credit_liste');
            Route::post('vente.savepersonnel',[VenteController::class,'savepersonnel'])->name('vente.savepersonnel');
            Route::get('vente.supprimer/{id}',[VenteController::class,'supprimer'])->name('vente.supprimer');
            Route::get('vente.imprimer_ven/{id}',[VenteController::class,'imprimer_ven'])->name('vente.imprimer_ven');
            Route::get('vente.imprimervente/{id}',[VenteController::class,'imprimervente'])->name('vente.imprimervente');
            Route::get('vente.imprimerdupplicata/{id}',[VenteController::class,'imprimerdupplicata'])->name('vente.imprimerdupplicata');
            Route::post('vente.add',[VenteController::class,'add'])->name('vente.add');
            Route::get('vente.etatcaisse',[VenteController::class,'etatcaisse'])->name('vente.etatcaisse');
            Route::get('vente.etatphar',[VenteController::class,'etatphar'])->name('vente.etatphar');
            Route::get('vente.adduser/{id}',[VenteController::class,'adduser'])->name('vente.adduser');
            Route::get('vente.user_selected',[VenteController::class,'user_selected'])->name('vente.user_selected');
            Route::get('vente.deleteuser/{id}', [VenteController::class,'deleteuser'])->name('vente.deleteuser');
            Route::get('vente.printetatcaisse/{debut}/{fin}',[VenteController::class,'printetatcaisse'])->name('vente.printetatcaisse');
            Route::get('vente.printdetailscaisse/{debut}/{fin}',[VenteController::class,'printdetailscaisse'])->name('vente.printdetailscaisse');
            Route::get('vente.printetatphar/{debut}/{fin}',[VenteController::class,'printetatphar'])->name('vente.printetatphar');
            Route::get('vente.etatassurance',[VenteController::class,'etatassurance'])->name('vente.etatassurance');
            Route::get('vente.print_etatassurance/{debut}/{fin}/{mut}',[VenteController::class,'print_etatassurance'])->name('vente.print_etatassurance');
            Route::get('vente.select_mag',[VenteController::class,'select_mag'])->name('vente.select_mag');
            Route::get('vente.mag_source/{id}',[VenteController::class,'mag_source'])->name('vente.mag_source');
            
            Route::resource('patient',PatientController::class);
            
            Route::resource('op',OperationBancaireController::class);
        
            Route::resource('reg',ReglementController::class);
            Route::get('reg.ventes',[ReglementController::class,'ventes'])->name('reg.ventes');
            Route::get('reg.rech_regs/{id}',[ReglementController::class,'rech_regs'])->name('reg.rech_regs');
            Route::get('reg.details/{id}',[ReglementController::class,'details'])->name('reg.details');
            Route::post('reg.add',[ReglementController::class,'add'])->name('reg.add');
            Route::get('reg.print_histoReg/{debut}/{fin}',[ReglementController::class,'print_histoReg'])->name('reg.print_histoReg');
        
            /*Route::resource('ck',ConfectionkitController::class);
            Route::get('ck.magasins',[ConfectionkitController::class,'magasins'])->name('ck.magasins');
            Route::get('ck.kits',[ConfectionkitController::class,'kits'])->name('ck.kits');
            Route::get('ck.rechKit/{id}',[ConfectionkitController::class,'rechKit'])->name('ck.rechKit');
            Route::get('ck.pdt_sour/{id}',[ConfectionkitController::class,'pdt_sour'])->name('ck.pdt_sour');
            Route::get('ck.pdt_con/{id}/{mag}',[ConfectionkitController::class,'pdt_con'])->name('ck.pdt_con');
            Route::get('ck.select/{id}',[ConfectionkitController::class,'select'])->name('ck.select');
            Route::get('ck.select_edit/{id}',[ConfectionkitController::class,'select_edit'])->name('ck.select_edit');
            Route::get('ck.histo',[ConfectionkitController::class,'histo'])->name('ck.histo');
            Route::post('ck.add',[ConfectionkitController::class,'add'])->name('ck.add');
            Route::get('ck.delete/{id}',[ConfectionkitController::class,'delete'])->name('ck.delete');*/
        
            /*Correction du Stock*/
            Route::resource('cs',CSController::class);
            Route::get('cs.magasins',[CSController::class,'magasins'])->name('cs.magasins');
            Route::get('cs.rech_mont/{id}',[CSController::class,'rech_mont'])->name('cs.rech_mont');
            Route::get('cs.select/{id}',[CSController::class,'select'])->name('cs.select');
            Route::get('cs.select_edit/{id}',[CSController::class,'select_edit'])->name('cs.select_edit');
            Route::get('cs.pdt_mag/{id}',[CSController::class,'pdt_mag'])->name('cs.rech_pdt_mag');
            Route::get('cs.pdt_con/{id}/{mag}',[CSController::class,'pdt_con'])->name('cs.rech_pdt_con');
            Route::get('cs.rech_mont/{id}',[CSController::class,'rech_mont'])->name('cs.rech_mont');
            Route::post('cs.add',[CSController::class,'add'])->name('cs.add');
            Route::get('cs.histo',[CSController::class,'histo'])->name('cs.histo');
            Route::get('cs.delete/{id}',[CSController::class,'delete'])->name('cs.delete');
            Route::get('cs/print_cs/{id}',[CSController::class,'add'])->name('cs.print_cs');
        
            /*Inventaire controlleur*/
            Route::get('inv.etatglobal',[InventaireController::class,'etatglobal'])->name('inv.etatglobal');
            Route::get('inv.exportEG',[InventaireController::class,'exportEG'])->name('inv.exportEG');
            Route::get('inv.exportEM',[InventaireController::class,'exportEM'])->name('inv.exportEM');
            Route::get('inv.etatmagasin',[InventaireController::class,'etatmagasin'])->name('inv.etatmagasin');
            Route::get('inv.etatmagasin/{id}',[InventaireController::class,'etatmagasin'])->name('inv.etatmagasin');
            Route::get('inv.print_etatglobal',[InventaireController::class,'print_etatglobal'])->name('inv.print_etatglobal');
            Route::get('inv.print_etatmagasin/{id}',[InventaireController::class,'print_etatmagasin'])->name('inv.print_etatmagasin');
            Route::get('inv.date_per',[InventaireController::class,'date_per'])->name('inv.date_per');
            Route::get('inv.inventaire',[InventaireController::class,'inventaire'])->name('inv.inventaire');
            Route::get('inv.magasins',[InventaireController::class,'magasins'])->name('inv.magasins');
            Route::get('inv.magasin',[InventaireController::class,'magasin'])->name('inv.magasin');
            Route::get('inv.details_pdt/{pdt}',[InventaireController::class,'details_pdt'])->name('inv.details_pdt');

            Route::get('inv.invglobal',[InventaireController::class,'invglobal'])->name('inv.invglobal');
            Route::get('inv.print_invglobal/{d}/{f}',[InventaireController::class,'print_invglobal'])->name('inv.print_invglobal');
            Route::get('inv.details/{d}/{f}/{id}',[InventaireController::class,'details'])->name('inv.details');
            Route::get('inv.details_mag/{d}/{f}/{id}/{pdt}',[InventaireController::class,'details_mag'])->name('inv.details_mag');
            Route::get('inv.invmagasin',[InventaireController::class,'invmagasin'])->name('inv.invmagasin');
            Route::get('inv.print_invmagasin/{d}/{f}/{id}',[InventaireController::class,'print_invmagasin'])->name('inv.print_invmagasin');
            Route::get('inv.invproduit',[InventaireController::class,'invproduit'])->name('inv.invproduit');
            Route::get('inv.produits',[InventaireController::class,'produits'])->name('inv.produits');
            Route::get('inv.print_invproduit/{d}/{f}/{id}',[InventaireController::class,'print_invproduit'])->name('inv.print_invproduit');
        
            /*InventaireSI controlleur*/
            Route::get('invsi.details_pdt/{pdt}/{id}',[InventaireSIController::class,'details_pdt'])->name('invsi.details_pdt');
            Route::get('invsi.invglobal',[InventaireSIController::class,'invglobal'])->name('invsi.invglobal');
            Route::get('invsi.print_invglobal/{d}/{f}',[InventaireSIController::class,'print_invglobal'])->name('invsi.print_invglobal');
            Route::get('invsi.details/{d}/{f}/{id}',[InventaireSIController::class,'details'])->name('invsi.details');
            Route::get('invsi.invcentre',[InventaireSIController::class,'invcentre'])->name('invsi.invcentre');
            Route::get('invsi.print_invcentre/{d}/{f}/{id}',[InventaireSIController::class,'print_invcentre'])->name('invsi.print_invcentre');
            Route::get('invsi.centres',[InventaireSIController::class,'centres'])->name('invsi.centres');
            Route::get('invsi.invproduit',[InventaireSIController::class,'invproduit'])->name('invsi.invproduit');
            Route::get('invsi.produits',[InventaireSIController::class,'produits'])->name('invsi.produits');
            Route::get('invsi.print_invproduit/{d}/{f}/{id}',[InventaireSIController::class,'print_invproduit'])->name('invsi.print_invproduit');
        
            //Etat stock SI
            Route::resource('eg',EtatController::class);
            Route::get('eg.stockglobal', [EtatController::class,'stockGlobal'])->name('eg.stockglobal');
            Route::get('eg.details_pdt/{id}', [EtatController::class,'details_pdt'])->name('eg.details_pdt');
            Route::get('eg.print_egstock', [EtatController::class,'print_egstock'])->name('eg.print_egstock');

            Route::get('eg.etatdps', [EtatController::class,'etatdps'])->name('eg.etatdps');
            Route::get('eg.getEtatdps/{id}', [EtatController::class,'getEtatdps'])->name('eg.getEtatdps');
            Route::get('eg.details_pdtdps/{dps}/{pdt}', [EtatController::class,'details_pdtdps'])->name('eg.details_pdtdps');
            Route::get('eg.print_etatdps/{id}', [EtatController::class,'print_etatdps'])->name('eg.print_etatdps');

            Route::get('eg.etatcentre', [EtatController::class,'etatcentre'])->name('eg.etatcentre');
            Route::get('eg.print_etatcentre/{id}', [EtatController::class,'print_etatcentre'])->name('eg.print_etatcentre');
            Route::get('eg.directions', [EtatController::class,'directions'])->name('eg.directions');
            Route::get('eg.centres', [EtatController::class,'centres'])->name('eg.centres');
            Route::get('eg.getEtatcentre/{id}', [EtatController::class,'getEtatcentre'])->name('eg.getEtatcentre');
            Route::get('eg.date_per', [EtatController::class,'date_per'])->name('eg.date_per');

            Route::get('eg.etatcaissesi',[EtatController::class,'etatcaissesi'])->name('eg.etatcaissesi');
            Route::get('eg.print_efsi/{d}/{f}',[EtatController::class,'print_efsi'])->name('eg.print_efsi');

            Route::get('eg.etatcaissecentre',[EtatController::class,'etatcaissecentre'])->name('eg.etatcaissecentre');
            Route::get('eg.print_efcentre/{d}/{f}/{i}',[EtatController::class,'print_efcentre'])->name('eg.print_efcentre');

            //Etat stock DPS
            Route::get('eg.stockglobaldps', [EtatController::class,'stockGlobaldps'])->name('eg.stockglobaldps');
            Route::get('eg.print_egstockdps', [EtatController::class,'print_egstockdps'])->name('eg.print_egstockdps');
            Route::get('eg.details_pdt_dps/{id}', [EtatController::class,'details_pdt_dps'])->name('eg.details_pdt_dps');
            Route::get('eg.etatcentredps', [EtatController::class,'etatcentredps'])->name('eg.etatcentredps');
            //Route::get('eg.print_etatcentre/{id}', [EtatController::class,'print_etatcentre'])->name('eg.print_etatcentre');
            Route::get('eg.centresdps', [EtatController::class,'centresdps'])->name('eg.centresdps');
            Route::get('eg.date_perdps', [EtatController::class,'date_perdps'])->name('eg.date_perdps');

            Route::get('eg.etatcaissedps',[EtatController::class,'etatcaissedps'])->name('eg.etatcaissedps');
            Route::get('eg.print_efdps/{d}/{f}/{i}',[EtatController::class,'print_efdps'])->name('eg.print_efdps');

            //User Controller
            Route::resource('user',UserController::class);
            Route::post('user/updatemc', 'UserController@updatemc')->name('user.updatemc');
            Route::post('user.createprofil', [UserController::class,'createprofil'])->name('user.createprofil');
            Route::get('user.editprofil/{id}', [UserController::class,'editprofil'])->name('user.editprofil');
            Route::get('user.deleteprofil/{id}', [UserController::class,'deleteprofil'])->name('user.deleteprofil');
            Route::post('user.updateprofil', [UserController::class,'updateprofil'])->name('user.updateprofil');
            Route::get('user.user', [UserController::class,'user'])->name('user.user');
            Route::post('user.usersave', [UserController::class,'usersave'])->name('user.usersave');
            Route::get('user.edituser/{id}', [UserController::class,'edituser'])->name('user.edituser');
            Route::get('user.deleteuser/{id}', [UserController::class,'deleteuser'])->name('user.deleteuser');

            Route::resource('centre',CentreController::class);
        
            Route::get('user.userdps', [UserController::class,'userdps'])->name('user.userdps');
            Route::get('user.usersi', [UserController::class,'usersi'])->name('user.usersi');
        
            Route::get('user.moncompte/{id}', 'UserController@moncompte')->name('user.moncompte');
            Route::get('user.createuser', 'UserController@createuser')->name('user.createuser');
            
            Route::post('user.logout', [UserController::class, 'logout'])->name('user.logout');
            
            Route::get('user.deconnexion', 'UserController@deconnexion')->name('user.deconnexion');
            Route::put('user.moncompte', 'UserController@updatemoncompte')->name('user.updatemoncompte');
        });    
        
        Auth::routes();
        
        Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
        
        Route::post('/set-locale', [LocaleController::class, 'setLocale'])->name('set-locale');

        Route::get('/set-locale', [LocaleController::class, 'setLocale'])->name('set-locale');
    });