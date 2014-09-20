<?php
	
	if(! defined('OP_CHANGE_TRAD_PLATF'))
	{
		define('OP_CHANGE_TRAD_PLATF', 1) ;
		
		class FormulaireDonneesBaseTradPlatf extends PvFormulaireDonneesHtml
		{
			public $LibelleCommandeAnnuler = "Fermer" ;
		}
		class TableauDonneesBaseTradPlatf extends PvTableauDonneesAdminDirecte
		{
			public $ToujoursAfficher = 1 ;
			public $TitreBoutonSoumettreFormulaireFiltres = "Valider" ;
		}
		
		class ScriptListBaseOpChange extends PvScriptWebSimple
		{
			public $Tableau ;
			public $BarreMenu ;
			public $TypeOpChange = 1 ;
			public $Privileges = array('post_op_change') ;
			public $NecessiteMembreConnecte = 1 ;
			protected function CreeBarreMenu()
			{
				$barreMenu = new PvTablMenuHoriz() ;
				$barreMenu->NomClasseCSSCellSelect = "ui-widget ui-widget-header ui-state-focus" ;
				return $barreMenu ;
			}
			public function TypeOpChangeOppose()
			{
				return ($this->TypeOpChange == 1) ? 2 : 1 ;
			}
			protected function CreeTableau()
			{
				return new TableauDonneesBaseTradPlatf() ;
			}
			protected function DetermineBarreMenu()
			{
				$this->BarreMenu = $this->CreeBarreMenu() ;
				$this->BarreMenu->AdopteScript("barreMenu", $this) ;
				$this->BarreMenu->ChargeConfig() ;
				$smConsult = $this->BarreMenu->MenuRacine->InscritSousMenuScript(($this->TypeOpChange == 1) ? "listeAchatsDevise" : "listeVentesDevise") ;
				$smConsult->CheminMiniature = "images/miniatures/consulte_opchange.png" ;
				$smConsult->Titre = "Consultation" ;
				$smEdition = $this->BarreMenu->MenuRacine->InscritSousMenuScript(($this->TypeOpChange == 1) ? "editAchatsDevise" : "editVentesDevise") ;
				$smEdition->CheminMiniature = "images/miniatures/edit_opchange.png" ;
				$smEdition->Titre = "Publication" ;
				$smReserv = $this->BarreMenu->MenuRacine->InscritSousMenuScript(($this->TypeOpChange == 1) ? "reservAchatsDevise" : "reservVentesDevise") ;
				$smReserv->CheminMiniature = "images/miniatures/reserv_opchange.png" ;
				$smReserv->Titre = "R&eacute;servation" ;
				$smSoumiss = $this->BarreMenu->MenuRacine->InscritSousMenuScript(($this->TypeOpChange == 1) ? "soumissAchatDevise" : "soumissVenteDevise") ;
				$smSoumiss->CheminMiniature = "images/miniatures/soumiss_opchange.png" ;
				$smSoumiss->Titre = "Negociations" ;
			}
			protected function DetermineTableau()
			{
				$this->Tableau = $this->CreeTableau() ;
				$this->Tableau->AdopteScript("tableau", $this) ;
				$this->Tableau->ChargeConfig() ;
			}
			public function DetermineEnvironnement()
			{
				$this->DetermineBarreMenu() ;
				$this->DetermineTableau() ;
			}
			public function RenduSpecifique()
			{
				$ctn = '' ;
				$ctn .= '<div class="Titre">'.$this->Titre.'</div>'.PHP_EOL ;
				if($this->ZoneParent->InclureJQueryUi)
				{
					$ctn .= '<script language="javascript">
	jQuery(function() {
		jQuery(".Titre")
			.addClass("ui-widget ui-widget-header ui-state-active") ;
	}) ;
</script>' ;
				}
				$ctn .= $this->BarreMenu->RenduDispositif() ;
				$ctn .= $this->Tableau->RenduDispositif() ;
				// print_r($this->Tableau->FournisseurDonnees->BaseDonnees) ;
				return $ctn ;
			}
		}
		
		class TablConsultOpChangeTradPlatf extends TableauDonneesBaseTradPlatf
		{
			public $DefColPeutModif ;
			public $DefColPeutRep ;
			public $DefColId ;
			public $DefColEmetteur ;
			public $DefColBanque ;
			public $DefColMontant ;
			public $DefColLibDevise ;
			public $DefColDatePublic ;
			public $DefColDateValeur ;
			public $DefColDateOp ;
			public $DefColTaux ;
			public $DefColActions ;
			public $FmtModif ;
			public $FmtPostuls ;
			public $FltDateDebut ;
			public $FltDateFin ;
			public $FltTypeChange ;
			public $FltAcquis ;
			public $FltNumOp ;
			public $FltEntiteOp ;
			public $FltAuteurTransact ;
			public $FltPourAutres ;
			public $FltColValide ;
			public $CmdAjout ;
			public $RestrOps = 1 ;
			public $MsgConsultInterdit = '<div class="ui-state-error">Vous ne pouvez pas voir les demandes en cours. Veuillez poster <b>${nomOffre}</b> avant.</div>' ;
			protected function PeutVoirOps()
			{
				if($this->ZoneParent->PossedePrivilege('admin_members'))
				{
					return 1 ;
				}
				$bd = & $this->ApplicationParent->BDPrincipale ;
				$sql = 'select * from ('.TXT_SQL_SELECT_ACHAT_DEVISE_SOUMIS.') t1 where numop='.$bd->ParamPrefix.'numOp and type_change='.$bd->ParamPrefix.'typeChange' ;
				$row = $bd->FetchSqlRow($sql, array('numOp' => $this->ZoneParent->IdMembreConnecte(), "typeChange" => $this->ScriptParent->TypeOpChangeOppose())) ;
				return (count($row) > 0) ;
			}
			public function ChargeConfig()
			{
				$this->ChargeConfigBase() ;
				parent::ChargeConfig() ;
				$this->ChargeConfigSuppl() ;
			}
			protected function ChargeDefCols()
			{
				$bd = $this->ApplicationParent->BDPrincipale ;
				$this->DefColPeutModif = $this->InsereDefColInvisible("peut_modif") ;
				$this->DefColPeutRep = $this->InsereDefColInvisible("peut_repondre") ;
				$this->DefColId = new PvDefinitionColonneDonnees() ;
				$this->DefColId->Visible = 0 ;
				$this->DefColId->NomDonnees = "num_op_change" ;
				$this->DefinitionsColonnes[] = & $this->DefColId ;
				$this->DefColEmetteur = new PvDefinitionColonneDonnees() ;
				$this->DefColEmetteur->Libelle = "Auteur" ;
				$this->DefColEmetteur->NomDonnees = "loginop" ;
				$this->DefColEmetteur->Largeur = "8%" ;
				$this->DefColEmetteur->AlignElement = "center" ;
				$this->DefinitionsColonnes[] = & $this->DefColEmetteur ;
				$this->DefColBanque = new PvDefinitionColonneDonnees() ;
				$this->DefColBanque->Libelle = "Banque" ;
				$this->DefColBanque->NomDonnees = "nom_entite" ;
				$this->DefColBanque->Largeur = "15%" ;
				$this->DefColBanque->AlignElement = "center" ;
				$this->DefinitionsColonnes[] = & $this->DefColBanque ;
				$this->DefColDatePublic = new PvDefinitionColonneDonnees() ;
				$this->DefColDatePublic->Libelle = "Publi&eacute; le" ;
				$this->DefColDatePublic->NomDonnees = "date_change" ;
				$this->DefColDatePublic->AliasDonnees = $bd->SqlDateToStrFr("date_change", 1) ;
				$this->DefColDatePublic->Largeur = "12%" ;
				$this->DefColDatePublic->AlignElement = "center" ;
				$this->DefinitionsColonnes[] = & $this->DefColDatePublic ;
				$this->DefColDateOp = new PvDefinitionColonneDonnees() ;
				$this->DefColDateOp->Libelle = "Date Op." ;
				$this->DefColDateOp->NomDonnees = "date_operation" ;
				$this->DefColDateOp->AliasDonnees = $bd->SqlDateToStrFr("date_operation") ;
				$this->DefColDateOp->Largeur = "12%" ;
				$this->DefColDateOp->AlignElement = "center" ;
				$this->DefinitionsColonnes[] = & $this->DefColDateOp ;
				$this->DefColDateValeur = new PvDefinitionColonneDonnees() ;
				$this->DefColDateValeur->Libelle = "Date valeur" ;
				$this->DefColDateValeur->NomDonnees = "date_valeur" ;
				$this->DefColDateValeur->AliasDonnees = $bd->SqlDateToStrFr("date_valeur") ;
				$this->DefColDateValeur->Largeur = "12%" ;
				$this->DefColDateValeur->AlignElement = "center" ;
				$this->DefinitionsColonnes[] = & $this->DefColDateValeur ;
				$this->DefColMontant = new PvDefinitionColonneDonnees() ;
				$this->DefColMontant->Libelle = "Montant" ;
				$this->DefColMontant->NomDonnees = "montant_change" ;
				$this->DefColMontant->AliasDonnees = $bd->SqlToInt("montant_change") ;
				$this->DefColMontant->Largeur = "6%" ;
				$this->DefColMontant->AlignElement = "right" ;
				$this->DefinitionsColonnes[] = & $this->DefColMontant ;
				$this->DefColLibDevise = new PvDefinitionColonneDonnees() ;
				$this->DefColLibDevise->Libelle = "Devise" ;
				$this->DefColLibDevise->NomDonnees = "devise_change" ;
				$this->DefColLibDevise->Largeur = "12%" ;
				$this->DefColLibDevise->AlignElement = "center" ;
				$this->DefinitionsColonnes[] = & $this->DefColLibDevise ;
				$this->DefColTaux = new PvDefinitionColonneDonnees() ;
				$this->DefColTaux->Libelle = "Taux" ;
				$this->DefColTaux->NomDonnees = "taux_transact" ;
				$this->DefColTaux->AliasDonnees = $bd->SqlToInt("taux_transact") ;
				$this->DefColTaux->Largeur = "12%" ;
				$this->DefColTaux->Visible = "0" ;
				$this->DefColTaux->AlignElement = "center" ;
				$this->DefinitionsColonnes[] = & $this->DefColTaux ;
				$this->DefColActions = new PvDefinitionColonneDonnees() ;
				$this->DefColActions->Libelle = "Actions" ;
				$this->DefColActions->TriPossible = 0 ;
				$this->DefColActions->Largeur = "*" ;
				$this->DefColActions->AlignElement = "center" ;
				$this->DefColActions->DeclareFormatteurLiens() ;
				$this->FmtModif = new PvConfigFormatteurColonneOuvreFenetre() ;
				$this->FmtModif->NomDonneesValid = "peut_modif" ;
				$this->FmtModif->FormatLibelle = "Modifier" ;
				$this->FmtModif->OptionsOnglet["Modal"] = 1 ;
				$this->FmtModif->OptionsOnglet["BoutonFermer"] = 0 ;
				$this->FmtModif->OptionsOnglet["Largeur"] = 600 ;
				$this->FmtModif->OptionsOnglet["Hauteur"] = 535 ;
				$this->FmtModif->FormatTitreOnglet = ($this->ScriptParent->TypeOpChange == 1) ? 'Modifier achat de devise' : 'Modifier vente de devise' ;
				$this->FmtModif->FormatCheminIcone = 'images/icones/modif.png' ;
				$this->FmtModif->FormatURL = '?'.urlencode($this->ZoneParent->NomParamScriptAppele).'='.(($this->ScriptParent->TypeOpChange == 1) ? 'modifAchatDevise' : 'modifVenteDevise').'&idEnCours=${num_op_change}' ;
				$this->DefColActions->Formatteur->Liens[] = & $this->FmtModif ;
				$this->FmtPostuls = new PvConfigFormatteurColonneOuvreFenetre() ;
				$this->FmtPostuls->NomDonneesValid = "peut_modif" ;
				$this->FmtPostuls->FormatLibelle = "R&eacute;servations" ;
				$this->FmtPostuls->OptionsOnglet["Modal"] = 1 ;
				$this->FmtPostuls->OptionsOnglet["BoutonFermer"] = 0 ;
				$this->FmtPostuls->OptionsOnglet["Hauteur"] = 600 ;
				$this->FmtPostuls->OptionsOnglet["Largeur"] = 750 ;
				$this->FmtPostuls->FormatTitreOnglet = ($this->ScriptParent->TypeOpChange == 1) ? 'R&eacute;servations achat de devise' : 'R&eacute;servations vente de devise' ;
				$this->FmtPostuls->FormatCheminIcone = 'images/icones/postulations.png' ;
				$this->FmtPostuls->FormatURL = '?'.urlencode($this->ZoneParent->NomParamScriptAppele).'='.(($this->ScriptParent->TypeOpChange == 1) ? 'postulsAchatDevise' : 'postulsVenteDevise').'&idEnCours=${num_op_change}' ;
				$this->DefColActions->Formatteur->Liens[] = & $this->FmtPostuls ;
				$this->FmtRepondre = new PvConfigFormatteurColonneOuvreFenetre() ;
				$this->FmtRepondre->NomDonneesValid = "peut_repondre" ;
				$this->FmtRepondre->FormatLibelle = "R&eacute;server" ;
				$this->FmtRepondre->OptionsOnglet["Modal"] = 1 ;
				$this->FmtRepondre->OptionsOnglet["BoutonFermer"] = 0 ;
				$this->FmtRepondre->OptionsOnglet["Hauteur"] = 300 ;
				$this->FmtRepondre->FormatTitreOnglet = ($this->ScriptParent->TypeOpChange == 1) ? 'Repondre a l\'achat de devise' : 'Repondre a la vente de devise' ;
				$this->FmtRepondre->FormatCheminIcone = 'images/icones/repondre.png' ;
				$this->FmtRepondre->FormatURL = '?'.urlencode($this->ZoneParent->NomParamScriptAppele).'='.(($this->ScriptParent->TypeOpChange == 1) ? 'negocAchatDevise' : 'negocVenteDevise').'&idEnCours=${num_op_change}' ;
				$this->DefColActions->Formatteur->Liens[] = & $this->FmtRepondre ;
				$this->DefinitionsColonnes[] = & $this->DefColActions ;
			}
			protected function ChargeConfigBase()
			{
				$this->ChargeDefCols() ;
			}
			protected function ChargeFiltresSelectionStatiq()
			{
				$this->FltTypeChange = $this->CreeFiltreFixe('typeChange', $this->ScriptParent->TypeOpChange) ;
				$this->FltTypeChange->ExpressionDonnees = 'type_change = <self>' ;
				$this->FiltresSelection[] = & $this->FltTypeChange ;
				$this->FltAuteurTransact = $this->CreeFiltreFixe('auteurTransact', 0) ;
				$this->FltAuteurTransact->ExpressionDonnees = 'peut_repondre <> <self>' ;
				$this->FiltresSelection[] = & $this->FltAuteurTransact ;
				if(! $this->ZoneParent->PossedePrivilege("admin_members"))
				{
					$this->FltAcquis = $this->ScriptParent->CreeFiltreFixe(	
						'numRep',
						$this->ZoneParent->Membership->MemberLogged->Id
					) ;
					$this->FltAcquis->ExpressionDonnees = 'top_active = 1 and numrep=<self>' ;
					$this->FiltresSelection[] = & $this->FltAcquis ;
					$this->FltPourAutres = $this->ScriptParent->CreeFiltreFixe(	
						'pourAutres',
						$this->ZoneParent->Membership->MemberLogged->Id
					) ;
					$this->FltPourAutres->ExpressionDonnees = 'numop <> <self>' ;
					$this->FiltresSelection[] = & $this->FltPourAutres ;
					$this->FltEntiteOp = $this->ScriptParent->CreeFiltreFixe('entiteOp', $this->ZoneParent->Membership->MemberLogged->RawData["ID_ENTITE_MEMBRE"]) ;
					$this->FltEntiteOp->ExpressionDonnees = 'id_entite_dest = <self>' ;
					$this->FiltresSelection[] = & $this->FltEntiteOp ;
					if($this->RestrOps)
					{
						$this->FltColValide = $this->CreeFiltreFixe('valideOpChange', '1') ;
						$this->FltColValide->ExpressionDonnees = 'bool_valide = <self>' ;
						$this->FiltresSelection[] = & $this->FltColValide ;
					}
				}
				else
				{
					$this->FltAcquis = $this->ScriptParent->CreeFiltreFixe(	
						'idOperateur',
						$this->ZoneParent->Membership->MemberLogged->Id
					) ;
					$this->FltAcquis->ExpressionDonnees = '(numrep = <self> and numop <> <self>)' ;
					$this->FiltresSelection[] = & $this->FltAcquis ;
				}
			}
			protected function ObtientRequeteSelection(& $bd)
			{
				return "(select t1.*, case when t1.commiss_ou_taux = 0 then mtt_commiss when type_taux = 0 then taux_change else ecran_taux end taux_transact, ".$bd->SqlConcat(array('t2.code_devise', "' / '", 't3.code_devise'))." devise_change, t7.shortname nom_court_entite, t7.name nom_entite, t2.code_devise lib_devise1, t3.code_devise lib_devise2, t4.login loginop, t4.nomop nomop, t4.prenomop prenomop, t5.id_entite_source, t5.id_entite_dest, t5.top_active, t6.numop numrep, t6.login loginrep, case when t4.numop = t6.numop then 1 else 0 end peut_modif, case when t4.numop <> t6.numop then 1 else 0 end peut_repondre,
case when t1.num_op_change_dem = 0 then 'demande' else 'reponse' end type_message
from op_change t1
left join devise t2
on t1.id_devise1 = t2.id_devise
left join devise t3
on t1.id_devise2 = t3.id_devise
left join operateur t4
on t1.numop = t4.numop
left join oper_b_change t5
on t5.id_entite_source=t4.id_entite
left join entite t7
on t5.id_entite_source=t7.id_entite
left join operateur t6
on t5.id_entite_dest=t6.id_entite
where t5.id_entite_dest is not null and t7.id_entite is not null and t6.login is not null and t4.active_op = 1)" ;
			}
			protected function ChargeConfigSuppl()
			{
				if(! $this->RestrOps)
				{
					$this->CmdAjout = new PvCommandeOuvreFenetreAdminDirecte() ;
					$this->CmdAjout->Libelle = "Ajouter" ;
					$this->CmdAjout->NomScript = ($this->ScriptParent->TypeOpChange == 1) ? "ajoutAchatDevise" : "ajoutVenteDevise" ;
					$this->CmdAjout->OptionsOnglet = array("Largeur" => "670", "Hauteur" => "545", "Modal" => 1, "BoutonFermer" => 0, "LibelleFermer" => "Fermer") ;
					$this->InscritCommande("cmdAjoutDevise", $this->CmdAjout) ;
				}
				$bd = $this->ApplicationParent->BDPrincipale ;
				$this->FournisseurDonnees = new PvFournisseurDonneesSql() ;
				$this->FournisseurDonnees->BaseDonnees = $bd ;
				$this->FournisseurDonnees->RequeteSelection = $this->ObtientRequeteSelection($bd) ;
				$this->FltDateDebut = $this->ScriptParent->CreeFiltreHttpGet("dateDebut") ;
				$this->FltDateDebut->Libelle = "Date Debut" ;
				$this->FltDateDebut->ValeurParDefaut = date("Y-m-d", date("U") - 30 * 86400) ;
				$this->FltDateDebut->DeclareComposant("PvCalendarDateInput") ;
				$this->FltDateDebut->ExpressionDonnees = $bd->SqlDatePart('date_change').' >= '.$bd->SqlDatePart($bd->SqlStrToDateTime('<self>')) ;
				$this->FiltresSelection[] = & $this->FltDateDebut ;
				$this->FltDateFin = $this->ScriptParent->CreeFiltreHttpGet("dateFin") ;
				$this->FltDateFin->Libelle = "Date fin" ;
				$this->FltDateFin->DeclareComposant("PvCalendarDateInput") ;
				$this->FltDateFin->ExpressionDonnees = $bd->SqlDatePart('date_change').' <= '.$bd->SqlDatePart($bd->SqlStrToDateTime('<self>')) ;
				$this->FiltresSelection[] = & $this->FltDateFin ;
				$this->ChargeFiltresSelectionStatiq() ;
			}
			protected function RenduDispositifBrut()
			{
				$ctn = '' ;
				if($this->RestrOps && ! $this->PeutVoirOps())
				{
					$args = array('nomOffre' => ($this->ScriptParent->TypeOpChange == 1) ? 'une vente de devise' : 'un achat de devise') ;
					$ctn .= _parse_pattern($this->MsgConsultInterdit, $args) ;
				}
				else
				{
					$ctn .= parent::RenduDispositifBrut() ;
					// print_r($this->FournisseurDonnees->BaseDonnees) ;
				}
				return $ctn ;
			}
		}
		class TablEditOpChangeTradPlatf extends TablConsultOpChangeTradPlatf
		{
			public $RestrOps = 0 ;
			public $CacherNegocs = 1 ;
			public $FltCacherNegocs ;
			public function ChargeConfig()
			{
				parent::ChargeConfig() ;
				$this->DefColEmetteur->Visible = 0 ;
				$this->DefColBanque->Visible = 0 ;
				if($this->EstPasNul($this->FltPourAutres))
				{
					$this->FltPourAutres->NePasIntegrerParametre = 1 ;
				}
				$this->FltAcquis->ExpressionDonnees = 'numop = <self>' ;
				$this->FltAcquis->ValeurParDefaut = $this->ZoneParent->Membership->MemberLogged->Id ;
				$this->FltAuteurTransact->ValeurParDefaut = 1 ;
				$this->FmtPostuls->Visible = 0 ;
				$this->FltCacherNegocs = $this->InsereFltSelectFixe("cacherNegoc", 0) ;
				$this->FltCacherNegocs->ExpressionDonnees = ($this->CacherNegocs) ? "num_op_change_dem = 0" : "num_op_change_dem <> 0" ;
			}
		}
		class TablReservOpChangeTradPlatf extends TablEditOpChangeTradPlatf
		{
			protected function ObtientRequeteSelection(& $bd)
			{
				return "(select t1.*, t8.total_dem, case when t1.commiss_ou_taux = 0 then mtt_commiss when type_taux = 0 then taux_change else ecran_taux end taux_transact, ".$bd->SqlConcat(array('t2.code_devise', "' / '", 't3.code_devise'))." devise_change, t7.shortname nom_court_entite, t7.name nom_entite, t2.code_devise lib_devise1, t3.code_devise lib_devise2, t4.login loginop, t4.nomop nomop, t4.prenomop prenomop, t5.id_entite_source, t5.id_entite_dest, t5.top_active, t6.numop numrep, t6.login loginrep, case when t4.numop = t6.numop then 1 else 0 end peut_modif, case when t4.numop <> t6.numop then 1 else 0 end peut_repondre,
case when t1.num_op_change_dem = 0 then 'demande' else 'reponse' end type_message
from op_change t1
left join devise t2
on t1.id_devise1 = t2.id_devise
left join devise t3
on t1.id_devise2 = t3.id_devise
left join operateur t4
on t1.numop = t4.numop
left join oper_b_change t5
on t5.id_entite_source=t4.id_entite
left join entite t7
on t5.id_entite_source=t7.id_entite
left join operateur t6
on t5.id_entite_dest=t6.id_entite
left join (select num_op_change_dem, count(0) total_dem from op_change where num_op_change_dem is not null and num_op_change_dem <> 0 group by num_op_change_dem) t8
on t8.num_op_change_dem = t1.num_op_change
where t5.id_entite_dest is not null and t7.id_entite is not null and t6.login is not null and t4.active_op = 1 and t8.total_dem > 0)" ;
			}
			public function ChargeConfig()
			{
				parent::ChargeConfig() ;
				$this->CmdAjout->Visible = 0 ;
				$this->FmtModif->Visible = 0 ;
				$this->FmtPostuls->Visible = 1 ;
			}
		}
		
		class TablSoumissOpChangeTradPlatf extends TableauDonneesBaseTradPlatf
		{
			public $FltNumOpSoumis ;
			public $FltTypeChange ;
			public $FltDatePubl ;
			public $FltMontant ;
			public $DefColTri ;
			public $DefColId ;
			public $DefColLoginDem ;
			public $DefColBanqueDem ;
			public $DefColDatePubl ;
			public $DefColTypeChange ;
			public $DefColMontantDem ;
			public $DefColMontantSoumis ;
			public $DefColTauxSoumis ;
			public $DefColConfirm ;
			public $DefColActions ;
			public $LienModif ;
			public function ChargeConfig()
			{
				parent::ChargeConfig() ;
				$this->ChargeDefCols() ;
				$this->ChargeDefColActions() ;
				$this->ChargeFlts() ;
				$this->ChargeFournDonnees() ;
			}
			protected function ChargeDefCols()
			{
				$bd = & $this->ApplicationParent->BDPrincipale ;
				$this->DefColTri = $this->InsereDefColCachee("date_change") ;
				$this->DefColId = $this->InsereDefColCachee("num_op_change") ;
				$this->DefColDatePubl = $this->InsereDefCol("date_change", 'Date publication', $bd->SqlDateToStrFr('date_change')) ;
				$this->DefColLoginDem = $this->InsereDefCol("login_dem", 'Demandeur') ;
				$this->DefColBanqueDem = $this->InsereDefCol("nom_entite_dem", 'Banque') ;
				$this->DefColMontantDem = $this->InsereDefCol("montant_change", 'Montant') ;
				$this->DefColMontantSoumis = $this->InsereDefCol("montant_soumis", 'Montant possible') ;
				$this->DefColTauxSoumis = $this->InsereDefCol("taux_soumis", 'Taux possible') ;
				// $this->DefColTauxDem = $this->InsereDefCol("taux_dem", 'Taux', 'case when commiss_ou_taux = 0 then mtt_commiss when type_taux = 0 then taux_change else ecran_taux end') ;
				$this->DefColConfirm = $this->InsereDefColBool("bool_confirme", 'Confirme') ;
			}
			protected function ChargeDefColActions()
			{
				$this->DefColActions = $this->InsereDefColActions('Actions') ;
				$this->LienModif = $this->InsereLienOuvreFenetreAction(
					$this->DefColActions,'?appelleScript=modifOpChangeSoumis&idEnCours=${num_op_change}',
					'N&eacute;gocier', 'modif_op_change_soumis_${num_op_change}',
					'Negocier operation de change', 
					array('Modal' => 1, 'BoutonFermer' => 0, 'Largeur' => 450, 'Hauteur' => 240)
				) ;
			}
			protected function ChargeFlts()
			{
				$this->FltNumOpSoumis = $this->InsereFltSelectFixe('numop', $this->ZoneParent->IdMembreConnecte(), 'numop = <self>') ;
				$this->FltEstSoumis = $this->InsereFltSelectFixe('num_op_change_dem', 0, 'num_op_change_dem <> <self>') ;
				$this->FltTypeChange = $this->InsereFltSelectFixe('type_change_dem', $this->ScriptParent->TypeOpChangeOppose(), 'type_change = <self>') ;
			}
			protected function ChargeFournDonnees()
			{
				$bd = & $this->ApplicationParent->BDPrincipale ;
				$this->FournisseurDonnees = new PvFournisseurDonneesSql() ;
				$this->FournisseurDonnees->BaseDonnees = & $bd ;
				$this->FournisseurDonnees->RequeteSelection = '(select t1.*, t3.login login_dem, t4.name nom_entite_dem from op_change t1 inner join op_change t2 on t1.num_op_change_dem = t2.num_op_change left join operateur t3 on t2.numop = t3.numop left join entite t4 on t3.id_entite = t4.id_entite)' ;
			}
		}
		
		class FormOpChangeBaseTradPlatf extends FormulaireDonneesBaseTradPlatf
		{
			public $InclureElementEnCours = 0 ;
			public $InclureTotalElements = 0 ;
			public $MaxFiltresEditionParLigne = 1 ;
			public $FltMontant ;
			public $FltDateValeur ;
			public $FltDateOper ;
			public $FltDevise1 ;
			public $FltDevise2 ;
			public $FltCommissOuTaux ;
			public $FltTypeTaux ;
			public $FltEcranTaux ;
			public $FltMttTaux ;
			public $FltCommentaire ;
			public $FltNumOp ;
			public $FltNumOpChange ;
			public $FltNumOpChangeRep ;
			public $FltTypeChange ;
			public $FltLibDevise ;
			public $FltTauxTransact ;
			public $FltMttSoumis ;
			public $FltTauxSoumis ;
			public $CritrEcheanceInvalide ;
			public $TypeOpChange = 1 ;
			public $PourReponse = 0 ;
			public $PourNegoc = 0 ;
			public $PourAjust = 0 ;
			public $NomClasseCommandeExecuter = "PvCommandeAjoutElement" ;
			public $NomClasseCommandeAnnuler = "PvCmdFermeFenetreActiveAdminDirecte" ;
			public $MsgReponseInterdit = '<div class="ui-state-error">Vous avez d&eacute;j&agrave; r&eacute;pondu &agrave; cette offre.</div>' ;
			protected function ReponsePossible()
			{
				$bd = & $this->ApplicationParent->BDPrincipale ;
				$sql = 'select * from op_change where num_op_change_dem='.$bd->ParamPrefix.'numOpChangeDem and numop='.$bd->ParamPrefix.'login' ;
				$row = $bd->FetchSqlRow(
					$sql,
					array(
						'numOpChangeDem' => $this->FltNumOpChange->Lie(),
						'login' => $this->ZoneParent->IdMembreConnecte()
					)
				) ;
				return (is_array($row) && count($row) == 0) ? 1 : 0 ;
			}
			public function TypeOpChangeRep()
			{
				return ($this->TypeOpChange == 1) ? 2 : 1 ;
			}
			protected function ChargeFiltresSelection()
			{
				parent::ChargeFiltresSelection() ;
				$this->FltNumOpChange = $this->ScriptParent->CreeFiltreHttpGet("idEnCours") ;
				$this->FltNumOpChange->ExpressionDonnees = 'num_op_change = <self>' ;
				$this->FiltresLigneSelection[] = & $this->FltNumOpChange ;
			}
			protected function ChargeFiltresEdition()
			{
				parent::ChargeFiltresEdition() ;
				// Lib Devise
				$this->FltLibDevise = $this->ScriptParent->CreeFiltreHttpPost("lib_devise") ;
				$this->FltLibDevise->NomParametreDonnees = 'devise_change' ;
				$this->FltLibDevise->Libelle = "Devise" ;
				$this->FiltresEdition[] = & $this->FltLibDevise ;
				// Taux Transaction
				$this->FltTauxTransact = $this->ScriptParent->CreeFiltreHttpPost("taux_transact") ;
				$this->FltTauxTransact->NomParametreDonnees = 'taux_transact' ;
				$this->FltTauxTransact->Libelle = "Taux / Commission" ;
				$this->FiltresEdition[] = & $this->FltTauxTransact ;
				// Devise 1
				$this->FltDevise1 = $this->ScriptParent->CreeFiltreHttpPost("devise1") ;
				$this->FltDevise1->DefinitColLiee("id_devise1") ;
				$this->FltDevise1->Libelle = "Devise" ;
				$this->FltDevise1->DeclareComposant("PvZoneBoiteSelectHtml") ;
				$comp = & $this->FltDevise1->Composant ;
				$comp->NomColonneLibelle = "code_devise" ;
				$comp->NomColonneValeur = "id_devise" ;
				$comp->FournisseurDonnees = new PvFournisseurDonneesSql() ;
				$comp->FournisseurDonnees->RequeteSelection = "devise" ;
				$comp->FournisseurDonnees->BaseDonnees = & $this->ApplicationParent->BDPrincipale ;
				$this->FiltresEdition[] = & $this->FltDevise1 ;
				// Devise 2
				$this->FltDevise2 = $this->ScriptParent->CreeFiltreHttpPost("devise2") ;
				$this->FltDevise2->DefinitColLiee("id_devise2") ;
				$this->FltDevise2->Libelle = "Devise" ;
				$this->FltDevise2->DeclareComposant("PvZoneBoiteSelectHtml") ;
				$comp = & $this->FltDevise2->Composant ;
				$comp->NomColonneLibelle = "code_devise" ;
				$comp->NomColonneValeur = "id_devise" ;
				$comp->FournisseurDonnees = new PvFournisseurDonneesSql() ;
				$comp->FournisseurDonnees->RequeteSelection = "devise" ;
				$comp->FournisseurDonnees->BaseDonnees = & $this->ApplicationParent->BDPrincipale ;
				$this->FiltresEdition[] = & $this->FltDevise2 ;
				// Commission ou taux
				$this->FltCommissOuTaux = $this->ScriptParent->CreeFiltreHttpPost("commiss_ou_taux") ;
				$this->FltCommissOuTaux->DefinitColLiee("commiss_ou_taux") ;
				$this->FiltresEdition[] = & $this->FltCommissOuTaux ;
				// Montant commission
				$this->FltMttComiss = $this->ScriptParent->CreeFiltreHttpPost("mtt_commiss") ;
				$this->FltMttComiss->DefinitColLiee("mtt_commiss") ;
				$this->ZoneParent->RemplisseurConfig->AppliqueCompMttComiss($this->FltMttComiss) ;
				$this->FiltresEdition[] = & $this->FltMttComiss ;
				// Type taux ou commission ?
				$this->FltTypeTaux = $this->ScriptParent->CreeFiltreHttpPost("type_taux") ;
				$this->FltTypeTaux->DefinitColLiee("type_taux") ;
				$this->FiltresEdition[] = & $this->FltTypeTaux ;
				// Date commission
				$this->FltDateComiss = $this->ScriptParent->CreeFiltreHttpPost("date_commiss") ;
				$this->FltDateComiss->DefinitColLiee("date_commiss") ;
				$this->FltDateComiss->DeclareComposant("PvCalendarDateInput") ;
				$this->FiltresEdition[] = & $this->FltDateComiss ;
				// Montant
				$this->FltMontant = $this->ScriptParent->CreeFiltreHttpPost("montant") ;
				$this->FltMontant->DefinitColLiee("montant_change") ;
				$this->FltMontant->Libelle = "Montant" ;
				$this->FiltresEdition[] = & $this->FltMontant ;
				// Taux / Commission
				$this->FltMttTaux = $this->ScriptParent->CreeFiltreHttpPost("taux_change") ;
				$this->FltMttTaux->DefinitColLiee("taux_change") ;
				$this->FltMttTaux->Libelle = "Taux / Commission" ;
				$this->FltMttTaux->ValeurParDefaut = 0 ;
				$this->ZoneParent->RemplisseurConfig->AppliqueCompValeurTaux($this->FltMttTaux) ;
				$this->FiltresEdition[] = & $this->FltMttTaux ;
				// Ecran Taux
				$this->FltEcranTaux = $this->ScriptParent->CreeFiltreHttpPost("ecran_taux") ;
				$this->FltEcranTaux->DefinitColLiee("ecran_taux") ;
				$this->ZoneParent->RemplisseurConfig->AppliqueCompEcranTaux($this->FltEcranTaux) ;
				$this->FiltresEdition[] = & $this->FltEcranTaux ;
				// Date operation
				$this->FltDateOper = $this->ScriptParent->CreeFiltreHttpPost("date_operation") ;
				$this->FltDateOper->DefinitColLiee("date_operation") ;
				$this->FltDateOper->Libelle = "Date operation" ;
				$this->FltDateOper->DeclareComposant("PvCalendarDateInput") ;
				$this->FiltresEdition[] = & $this->FltDateOper ;
				// Date Valeur
				$this->FltDateValeur = $this->ScriptParent->CreeFiltreHttpPost("date_valeur") ;
				$this->FltDateValeur->DefinitColLiee("date_valeur") ;
				$this->FltDateValeur->Libelle = "Date valeur" ;
				$this->FltDateValeur->DeclareComposant("PvCalendarDateInput") ;
				$this->FiltresEdition[] = & $this->FltDateValeur ;
				// Type de change
				$this->FltTypeChange = $this->ScriptParent->CreeFiltreFixe("typeChange", $this->TypeOpChange) ;
				$this->FltTypeChange->DefinitColLiee("type_change") ;
				$this->FiltresEdition[] = & $this->FltTypeChange ;
				// Operateur responsable
				$this->FltNumOp = $this->ScriptParent->CreeFiltreMembreConnecte('numop', 'MEMBER_ID') ;
				$this->FltNumOp->DefinitColLiee("numop") ;
				$this->FiltresEdition[] = & $this->FltNumOp ;
				// Montant soumis
				$this->FltMttSoumis = $this->InsereFltEditHttpPost('montant_soumis', 'montant_soumis') ;
				$this->FltMttSoumis->NePasIntegrerParametre = 1 ;
				// Taux soumis
				$this->FltTauxSoumis = $this->InsereFltEditHttpPost('taux_soumis', 'taux_soumis') ;
				$this->FltTauxSoumis->NePasIntegrerParametre = 1 ;
				// Commentaire
				$this->FltCommentaire = $this->ScriptParent->CreeFiltreHttpPost("commentaire") ;
				$comp0 = $this->FltCommentaire->DeclareComposant("PvZoneMultiligneHtml") ;
				$comp0->TotalLignes = 11 ;
				$comp0->TotalColonnes = 92 ;
				$this->FltCommentaire->DefinitColLiee("commentaire") ;
				$this->FiltresEdition[] = & $this->FltCommentaire ;
				if($this->PourReponse || $this->PourNegoc)
				{
					$this->Editable = 0 ;
					$this->FltNumOpChangeRep = $this->ScriptParent->CreeFiltreRef("numOpChangeDem", $this->FltNumOpChange) ;
					$this->FltNumOpChangeRep->DefinitColLiee("num_op_change_dem") ;
					$this->FiltresEdition[] = & $this->FltNumOpChangeRep ;
					$this->FltDevise1->NomColonneLiee = 'id_devise2' ;
					$this->FltDevise2->NomColonneLiee = 'id_devise1' ;
				}
			}
			public function ChargeConfig()
			{
				if($this->PourReponse || $this->PourNegoc)
				{
					$this->NomClasseCommandeExecuter = 'CmdEnvoiRepOpChangeTradPlatf' ;
				}
				parent::ChargeConfig() ;
				$this->FournisseurDonnees = new PvFournisseurDonneesSql() ;
				$this->FournisseurDonnees->RequeteSelection = '(select t1.*, t2.code_devise lib_devise1, t3.code_devise lib_devise2, '.$this->ApplicationParent->BDPrincipale->SqlConcat(array('t2.code_devise', "' / '", 't3.code_devise')).' devise_change, case when t1.commiss_ou_taux = 0 then mtt_commiss when type_taux = 0 then taux_change else ecran_taux end taux_transact from op_change t1 left join devise t2 on t1.id_devise1 = t2.id_devise left join devise t3 on t1.id_devise2 = t3.id_devise)' ;
				if($this->PourNegoc == 1 && $this->PourAjust == 1)
				{
					$this->FournisseurDonnees->RequeteSelection = '(select t1.*, t4.num_op_change num_op_change_soumis, t4.montant_soumis montant_change_soumis, t4.taux_soumis taux_change_soumis, t2.code_devise lib_devise1, t3.code_devise lib_devise2, '.$this->ApplicationParent->BDPrincipale->SqlConcat(array('t2.code_devise', "' / '", 't3.code_devise')).' devise_change, case when t1.commiss_ou_taux = 0 then t1.mtt_commiss when t1.type_taux = 0 then t1.taux_change else t1.ecran_taux end taux_transact from op_change t4 inner join op_change t1 on t4.num_op_change_dem=t1.num_op_change left join devise t2 on t1.id_devise1 = t2.id_devise left join devise t3 on t1.id_devise2 = t3.id_devise)' ;
					// echo $this->FournisseurDonnees->RequeteSelection ;
				}
				// print 				$this->FournisseurDonnees->RequeteSelection ;
				$this->FournisseurDonnees->TableEdition = "op_change" ;
				$this->FournisseurDonnees->BaseDonnees = $this->ApplicationParent->BDPrincipale ;
				if(! $this->PourReponse && ! $this->PourNegoc)
				{
					$this->CritrEcheanceInvalide = $this->CommandeExecuter->InsereNouvCritere(new CritereEcheanceInvalideOpChange()) ;
				}
				if($this->PourNegoc && $this->PourAjust)
				{
					$this->FltNumOpChange->ExpressionDonnees = 'num_op_change_soumis = <self>' ;
				}
			}
			public function CalculeElementsRendu()
			{
				parent::CalculeElementsRendu() ;
				// print_r($this->ApplicationParent->BDPrincipale) ;
				if(! count($this->ElementEnCours))
				{
					return ;
				}
				if($this->PourNegoc == 1)
				{
					$typeTaux = $this->ZoneParent->RemplisseurConfig->ObtientTypeTaux($this->ElementEnCours) ;
					$valTaux = $this->ZoneParent->RemplisseurConfig->ObtientValeurTaux($this->ElementEnCours) ;
					if($this->PourAjust == 0)
					{
						$this->ZoneParent->RemplisseurConfig->AppliqueCompComissOuTaux($this->FltTauxSoumis, $this->ElementEnCours) ;
						$this->FltMttSoumis->ValeurParDefaut = $this->ElementEnCours["montant_change"] ;
						$this->FltTauxSoumis->ValeurParDefaut = $valTaux ;
						// print_r($this->ElementEnCours) ;
						$this->FltMttSoumis->NePasIntegrerParametre = 0 ;
						$this->FltTauxSoumis->NePasIntegrerParametre = 0 ;
					}
					else
					{
						$this->FltMontant->NePasIntegrerParametre = 0 ;
						$this->FltMttSoumis->DefinitColLiee("montant_change_soumis") ;
						$this->FltTauxSoumis->DefinitColLiee("taux_change_soumis") ;
					}
				}
			}
			protected function RenduDispositifBrut()
			{
				$ctn = '' ;
				if(($this->PourReponse || ($this->PourNegoc && ! $this->PourAjust)) && ! $this->ReponsePossible())
				{
					$ctn .= $this->MsgReponseInterdit ;
				}
				else
				{
					$ctn .= parent::RenduDispositifBrut() ;
				}
				return $ctn ;
			}
			protected function InitDessinateurFiltresEdition()
			{
				$this->DessinateurFiltresEdition = new DessinFiltresFormOpChange() ;
			}
		}
		
		class CmdEnvoiRepOpChangeTradPlatf extends PvCommandeEditionElementBase
		{
			public function ExecuteInstructions()
			{
				$this->StatutExecution = 0 ;
				if($this->EstNul($this->FormulaireDonneesParent->FournisseurDonnees))
				{
					$this->RenseigneErreur("La base de donn�e du formulaire n'est renseign�.") ;
					return ;
				}
				$bd = & $this->FormulaireDonneesParent->FournisseurDonnees->BaseDonnees ;
				$numOpChange = $this->FormulaireDonneesParent->FltNumOpChange->Lie() ;
				$ok = $bd->RunSql('insert into op_change (type_change, montant_change, date_change, taux_change, id_devise1, id_devise2, numop, date_valeur, date_operation, bool_valide, bool_confirme, bool_expire, num_op_change_dem, commiss_ou_taux, mtt_commiss, date_commiss, type_taux, ecran_taux)
SELECT case when t1.type_change=1 then 2 else 1 end, t1.montant_change, t1.date_change, t1.taux_change, t1.id_devise2, t1.id_devise1, '.$bd->ParamPrefix.'numOperateur, t1.date_valeur, '.$bd->SqlNow().', t1.bool_valide, t1.bool_confirme, t1.bool_expire, t1.num_op_change, t1.commiss_ou_taux, t1.mtt_commiss, t1.date_commiss, t1.type_taux, t1.ecran_taux FROM op_change t1
WHERE num_op_change = '.$bd->ParamPrefix.'numOpChange', array('numOperateur' => $this->ZoneParent->Membership->MemberLogged->Id, 'numOpChange' => $numOpChange)) ;
				if($ok)
				{
					$this->StatutExecution = 1 ;
					$this->MessageExecution = $this->MessageSuccesExecution ;
				}
				else
				{
					$this->StatutExecution = 0 ;
					$this->MessageExecution = 'Erreur SQL : '.$bd->ConnectionException ;
				}
				return ;
			}
		}
		
		class CmdAjustOpChangeTradPlatf extends PvCommandeEditionElementBase
		{
			public $Mode = 2 ;
			public $MessageSuccesExecution = "Votre demande a &eacute;t&eacute; enregistr&eacute;e" ;
			public function ExecuteInstructions()
			{
				parent::ExecuteInstructions() ;
				// print_r($this->FormulaireDonneesParent->FournisseurDonnees->BaseDonnees) ;
				if($this->StatutExecution == 1)
				{
					$this->FormulaireDonneesParent->CacherFormulaireFiltres = 1 ;
				}
			}
		}
		class CmdNegocOpChangeTradPlatf extends CmdAjustOpChangeTradPlatf
		{
			public $Mode = 1 ;
		}
		
		class DessinFiltresFormOpChange extends PvDessinateurRenduHtmlFiltresDonnees
		{
			public function Execute(& $script, & $composant, $parametres)
			{
				$composant->LieTousLesFiltres() ;
				$ctn = '' ;
				if($composant->Editable == 1)
				{
					$ctn .= $this->ExecuteForm($script, $composant, $parametres) ;
				}
				elseif($composant->PourNegoc == 1)
				{
					$ctn .= $this->ExecuteNegoc($script, $composant, $parametres) ;
				}
				else
				{
					$ctn .= $this->ExecuteSommaire($script, $composant, $parametres) ;
				}
				return $ctn ;
			}
			protected function ExecuteSommaire(& $script, & $composant, $parametres)
			{
				$ctn = '' ;
				$ctn .= '<table width="100%" cellspacing="0" cellpadding="2">'.PHP_EOL ;
				$ctn .= '<tr>'.PHP_EOL ;
				$ctn .= '<td width="40%">'.(($composant->FltTypeChange ->Lie() == 1) ? 'Achat devise' : 'Vente devise').'</td>'.PHP_EOL ;
				$ctn .= '<td width="*">'.htmlentities($composant->FltLibDevise->Etiquette()).'</td>'.PHP_EOL ;
				$ctn .= '</tr>'.PHP_EOL ;
				$ctn .= '<tr>'.PHP_EOL ;
				$ctn .= '<td>Date operation</td>'.PHP_EOL ;
				$ctn .= '<td>'.htmlentities($composant->FltDateOper->Etiquette()).'</td>'.PHP_EOL ;
				$ctn .= '</tr>'.PHP_EOL ;
				$ctn .= '<tr>'.PHP_EOL ;
				$ctn .= '<td>Montant Demand&eacute;</td>'.PHP_EOL ;
				$ctn .= '<td>'.$composant->FltMontant->Etiquette().'</td>'.PHP_EOL ;
				$ctn .= '</tr>'.PHP_EOL ;
				$ctn .= '<tr>'.PHP_EOL ;
				$ctn .= '<td>Taux/Commission demand&eacute;</td>'.PHP_EOL ;
				$ctn .= '<td>'.$composant->FltTauxTransact->Etiquette().'</td>'.PHP_EOL ;
				$ctn .= '</tr>'.PHP_EOL ;
				$ctn .= '</table>'.PHP_EOL ;
				return $ctn ;
			}
			protected function ExecuteNegoc(& $script, & $composant, $parametres)
			{
				// $auteurDem = ($this->FltIdEnCours->Lie() == $composant->)
				$renduMttTransact = ($composant->PourAjust == 0) ? $composant->FltMontant->Etiquette() : $composant->FltMontant->Rendu() ;
				$renduTauxTransact = ($composant->PourAjust == 0) ? $composant->FltTauxTransact->Etiquette() : $composant->FltTauxTransact->Rendu() ;
				$renduMttSoumis = ($composant->PourAjust == 0) ? $composant->FltMttSoumis->Rendu() : $composant->FltMttSoumis->Etiquette() ;
				$renduTauxSoumis = ($composant->PourAjust == 0) ? $composant->FltTauxSoumis->Rendu() : $composant->FltTauxSoumis->Etiquette() ;
				$ctn = '' ;
				$ctn .= '<table width="100%" cellspacing="0" cellpadding="2">'.PHP_EOL ;
				$ctn .= '<tr>'.PHP_EOL ;
				$ctn .= '<td width="60%">'.(($composant->FltTypeChange ->Lie() == 1) ? 'Achat devise' : 'Vente devise').'</td>'.PHP_EOL ;
				$ctn .= '<td width="*">'.htmlentities($composant->FltLibDevise->Etiquette()).'</td>'.PHP_EOL ;
				$ctn .= '</tr>'.PHP_EOL ;
				$ctn .= '<tr>'.PHP_EOL ;
				$ctn .= '<td>Date operation</td>'.PHP_EOL ;
				$ctn .= '<td>'.htmlentities($composant->FltDateOper->Etiquette()).'</td>'.PHP_EOL ;
				$ctn .= '</tr>'.PHP_EOL ;
				$ctn .= '<tr>'.PHP_EOL ;
				$ctn .= '<td>Montant Demand&eacute;</td>'.PHP_EOL ;
				$ctn .= '<td>'.$renduMttTransact.'</td>'.PHP_EOL ;
				$ctn .= '</tr>'.PHP_EOL ;
				$ctn .= '<tr>'.PHP_EOL ;
				$ctn .= '<td>Taux/Commission demand&eacute;</td>'.PHP_EOL ;
				$ctn .= '<td>'.$renduTauxTransact.'</td>'.PHP_EOL ;
				$ctn .= '</tr>'.PHP_EOL ;
				$ctn .= '<tr>'.PHP_EOL ;
				$ctn .= '<td>Montant possible</td>'.PHP_EOL ;
				$ctn .= '<td>'.$renduMttSoumis.'</td>'.PHP_EOL ;
				$ctn .= '</tr>'.PHP_EOL ;
				$ctn .= '<tr>'.PHP_EOL ;
				$ctn .= '<td>Taux/Commission possible</td>'.PHP_EOL ;
				$ctn .= '<td>'.$renduTauxSoumis.'</td>'.PHP_EOL ;
				$ctn .= '</tr>'.PHP_EOL ;
				$ctn .= '</table>'.PHP_EOL ;
				return $ctn ;
			}
			protected function ExecuteForm(& $script, & $composant, $parametres)
			{
				$ctn = '' ;
				$ctn .= '<table width="100%" cellspacing=0 cellpadding="2">'.PHP_EOL ;
				$ctn .= '<tr><th colspan="2" align="left">Transaction</th></tr>'.PHP_EOL ;
				$ctn .= '<tr>'.PHP_EOL ;
				$ctn .= '<td width="25%">Devise :</td><td width="*"><table cellspacing="0" cellpadding="0"><tr><td>'.$composant->FltDevise1->Rendu().'</td><td>&nbsp;&nbsp;Contre&nbsp;&nbsp;</td><td>'.$composant->FltDevise2->Rendu().'</td></tr></table></td>'.PHP_EOL ;
				$ctn .= '</tr>'.PHP_EOL ;
				$ctn .= '<tr>'.PHP_EOL ;
				$ctn .= '<td>Montant</td>'.PHP_EOL ;
				$ctn .= '<td>'.$composant->FltMontant->Rendu().'</td>'.PHP_EOL ;
				$ctn .= '</tr>'.PHP_EOL ;
				$ctn .= '<tr>'.PHP_EOL ;
				$ctn .= '<td>Date operation</td>'.PHP_EOL ;
				$ctn .= '<td>'.$composant->FltDateOper->Rendu().'</td>'.PHP_EOL ;
				$ctn .= '</tr>'.PHP_EOL ;
				$ctn .= '<tr>'.PHP_EOL ;
				$ctn .= '<td>Date valeur</td>'.PHP_EOL ;
				$ctn .= '<td>'.$composant->FltDateValeur->Rendu().'</td>'.PHP_EOL ;
				$ctn .= '</tr>
</table>
</div>'.PHP_EOL ;
				$ctn .= '<div id="infosSupplTransact">
	<ul>
		<li><a href="#ongletCibleTransact"><span>Transaction</span></a></li>
		<li><a href="#ongletCommentTransact"><span>Commentaire</span></a></li>
	</ul>
	<div id="ongletCibleTransact">
		<table width="100%" cellspacing="0">
			<tr>
			<td><input type="radio" name="cible_transact" value="2" checked id="cible_transact_params" /></td>
			<td><label for="cible_transact_params">Envoyer aux banques parametr&eacute;es</label></td>
			</tr>
			<tr>
			<td><input type="radio" name="cible_transact" value="1" id="cible_transact_tous" /></td>
			<td><label for="cible_transact_tous">Envoyer &agrave; toutes les banques</label></td>
			</tr>
		</table>
	</div>
	<div id="ongletCommentTransact">'.$composant->FltCommentaire->Rendu().'</div>
</div>'.PHP_EOL ;
				$ctn .= '</div>'.PHP_EOL ;
				$ctn .= '</td></tr>'.PHP_EOL ;
				$ctn .= '</table>'.PHP_EOL ;
				$ctn .= '<script type="text/javascript">
	var evtCommissOuTaux'.$composant->IDInstanceCalc.' = [
		function () {},
		function () {
			var val = jQuery("#type_taux :checked").val() ;
			jQuery(".grpValTaux span").hide()
			.each(function (index, elem) {
				if(val == index)
					jQuery(this).show() ;
			}) ;
		}
	] ;
	function affichCommissOuTaux'.$composant->IDInstanceCalc.'()
	{
		var obj = jQuery(".commissOuTaux");
		var val = obj.find("input[name=\'commiss_ou_taux\']:checked").val();
		obj.find(".frm").hide() ;
		obj.find(".frm").each(function(index, elem){
			if(index == val)
			{
				jQuery(this).show() ;
				evtCommissOuTaux'.$composant->IDInstanceCalc.'[index]() ;
			}
		}) ;
	}
	jQuery(function (){
		affichCommissOuTaux'.$composant->IDInstanceCalc.'();
		jQuery("#infosSupplTransact").tabs() ;
	}) ;
</script>' ;
				return $ctn ;
			}
		}
		
		class FormAjoutAchatDeviseTradPlatf extends FormOpChangeBaseTradPlatf
		{
			public $TypeOpChange = 1 ;
		}
		class FormReponseAchatDeviseTradPlatf extends FormAjoutAchatDeviseTradPlatf
		{
			public $InclureElementEnCours = 1 ;
			public $InclureTotalElements = 1 ;
			public $PourReponse = 1 ;
		}
		/*
		class FormNegocAchatDeviseTradPlatf extends FormAjoutAchatDeviseTradPlatf
		{
			public $InclureElementEnCours = 1 ;
			public $InclureTotalElements = 1 ;
			public $PourNegoc = 1 ;
		}
		*/
		class FormAjustAchatDeviseTradPlatf extends FormulaireDonneesBaseTradPlatf
		{
			public $InclureElementEnCours = 1 ;
			public $InclureTotalElements = 1 ;
			public $NomClasseCommandeAnnuler = "PvCmdFermeFenetreActiveAdminDirecte" ;
			public $NomClasseCommandeExecuter = "CmdAjustOpChangeTradPlatf" ;
			public $FltIdEnCours ;
			public $FltLimitOpChange ;
			public $FltTypeChange ;
			public $FltDevise ;
			public $FltMontantDem ;
			public $FltTauxDem ;
			public $FltMontantSoumis ;
			public $FltTauxSoumis ;
			public $ModeAccesMembre = 0 ;
			public $MaxFiltresEditionParLigne = 1 ;
			public $LigneOpChangeDem = array() ;
			public $MsgReponseInterdit = '<div class="ui-state-error">Vous avez d&eacute;j&agrave; r&eacute;pondu &agrave; cette offre.</div>' ;
			protected function ReponsePossible()
			{
				$bd = & $this->ApplicationParent->BDPrincipale ;
				$sql = 'select * from op_change where num_op_change_dem='.$bd->ParamPrefix.'numOpChangeDem and numop='.$bd->ParamPrefix.'login' ;
				$row = $bd->FetchSqlRow(
					$sql,
					array(
						'numOpChangeDem' => $this->FltIdEnCours->Lie(),
						'login' => $this->ZoneParent->IdMembreConnecte()
					)
				) ;
				return (is_array($row) && count($row) == 0) ? 1 : 0 ;
			}
			public function EstAccessible()
			{
				$ok = parent::EstAccessible() ;
				if(! $ok)
					return $ok ;
				$idMembre = $this->ZoneParent->IdMembreConnecte() ;
				$bd = & $this->ApplicationParent->BDPrincipale ;
				$ligne = array() ;
				if($this->InclureElementEnCours)
				{
					$idEnCours = $this->FltIdEnCours->Lie() ;
					$ligne = $bd->FetchSqlRow('select t1.* from op_change t1
	inner join op_change t2 on t1.num_op_change_dem = t2.num_op_change
	where t1.num_op_change='.$bd->ParamPrefix.'idEnCours and (t1.numop = '.$bd->ParamPrefix.'idMembre or t2.numop = '.$bd->ParamPrefix.'idMembre)', array('idEnCours' => $idEnCours, 'idMembre' => $idMembre)) ;
				}
				else
				{
					$ligne = $this->LigneOpChangeDem ;
				}
				$ok = (is_array($ligne) && count($ligne) > 0) ;
				if($ok)
				{
					// print_r($ligne) ;
					$this->ModeAccesMembre = ($ligne["numop"] == $idMembre) ? 1 : 0 ;
					$this->ZoneParent->RemplisseurConfig->AppliqueCompComissOuTaux($this->FltTauxDem, $ligne, $this->InclureElementEnCours, 1) ;
					$this->ZoneParent->RemplisseurConfig->AppliqueCompComissOuTaux($this->FltTauxSoumis, $ligne, 0) ;
					if($this->InclureElementEnCours)
					{
						if($this->ModeAccesMembre == 1)
						{
							$this->FltTauxDem->EstEtiquette = 1 ;
							$this->FltTauxDem->NePasLierColonne = 1 ;
							$this->FltMontantDem->EstEtiquette = 1 ;
							$this->FltMontantDem->NePasLierColonne = 1 ;
						}
						else
						{
							$this->FltTauxSoumis->EstEtiquette = 1 ;
							$this->FltTauxSoumis->NePasLierColonne = 1 ;
							$this->FltMontantSoumis->EstEtiquette = 1 ;
							$this->FltMontantSoumis->NePasLierColonne = 1 ;
						}
					}
					else
					{
						$this->FltTauxDem->EstEtiquette = 1 ;
						$this->FltMontantDem->EstEtiquette = 1 ;
					}
				}
				return $ok ;
			}
			public function ChargeConfig()
			{
				parent::ChargeConfig() ;
				$this->ChargeFournDonneesSpec() ;
			}
			protected function ChargeFournDonneesSpec()
			{
				$fourn = new PvFournisseurDonneesSql() ;
				$fourn->BaseDonnees = & $this->ApplicationParent->BDPrincipale ;
				$fourn->RequeteSelection = '(select t1.*, t2.lib_devise lib_devise1, t3.lib_devise code_devise2, '.$fourn->BaseDonnees->SqlConcat(array('t2.code_devise', "' / '", 't3.code_devise')).' lib_devise from op_change t1 left join devise t2 on t1.id_devise1 = t2.id_devise left join devise t3 on t1.id_devise2 = t3.id_devise)' ;
				$fourn->TableEdition = 'op_change' ;
				$this->FournisseurDonnees = & $fourn ;
			}
			protected function ChargeFiltresSelection()
			{
				$this->FltIdEnCours = $this->InsereFltLgSelectHttpGet("idEnCours", 'num_op_change = <self>') ;
				$this->FltLimitOpChange = $this->InsereFltLgSelectFixe("limitOpChange", 0, 'num_op_change_dem <> <self>') ;
			}
			protected function ChargeOpChangeDem()
			{
				$bd = & $this->ApplicationParent->BDPrincipale ;
				if(! $this->InclureElementEnCours)
				{
					$idEnCours = $this->FltIdEnCours->Lie() ;
					$ligne = $bd->FetchSqlRow('select t1.*, '.$bd->SqlDateToStr('t1.date_operation').' date_operation_str, '.$bd->SqlDateToStr('t1.date_valeur').' date_valeur_str, t2.lib_devise lib_devise1, t3.lib_devise code_devise2, '.$bd->SqlConcat(array('t2.code_devise', "' / '", 't3.code_devise')).' lib_devise from op_change t1 left join devise t2 on t1.id_devise1 = t2.id_devise left join devise t3 on t1.id_devise2 = t3.id_devise where t1.num_op_change='.$bd->ParamPrefix.'idEnCours', array('idEnCours' => $idEnCours)) ;
					if(count($ligne) > 0)
					{
						$flts = array() ;
						$this->LigneOpChangeDem = $ligne ;
						foreach($ligne as $nom => $val)
						{
							if(in_array($nom, array("num_op_change", "montant_soumis", "taux_soumis", "montant_change", "taux_change", "mtt_commiss", "ecran_taux", "date_valeur_str", "date_operation_str")))
							{
								continue ;
							}
							$valLiee = $val ;
							if($nom == "type_change")
								$valLiee = ($valLiee == 1) ? 2 : 1 ;
							elseif($nom == "num_op_change_dem")
								$valLiee = $ligne["num_op_change"] ;
							elseif($nom == "numop")
								$valLiee = $this->ZoneParent->IdMembreConnecte() ;
							elseif($nom == "date_valeur")
								$valLiee = $ligne["date_valeur_str"] ;
							elseif($nom == "date_operation")
								$valLiee = $ligne["date_operation_str"] ;
							$flts[$nom] = $this->CreeFiltreFixe($nom, $valLiee) ;
							$nomColLiee = $nom ;
							if($nom == "id_devise1")
							{
								$nomColLiee = "id_devise2" ;
							}
							elseif($nom == "id_devise2")
							{
								$nomColLiee = "id_devise1" ;
							}
							$flts[$nom]->DefinitColLiee($nomColLiee) ;
							if($nom == "date_valeur" || $nom == "date_operation")
							{
								$flts[$nom]->ExpressionColonneLiee = $bd->SqlStrToDate('<self>') ;
							}
							$this->FiltresEdition[] = & $flts[$nom] ;
						}
						$this->FltDevise->ValeurParDefaut = $ligne["lib_devise"] ;
						$this->FltMontantDem->ValeurParDefaut = $ligne["montant_change"] ;
						$this->FltTauxDem->ValeurParDefaut = $this->ZoneParent->RemplisseurConfig->ObtientValeurTaux($ligne) ;
						$this->FltMontantSoumis->ValeurParDefaut = $this->FltMontantDem->ValeurParDefaut ;
						$this->FltTauxSoumis->ValeurParDefaut = $this->FltTauxDem->ValeurParDefaut ;
						$this->ReinitParametres() ;
					}
				}
			}
			protected function ChargeFiltresEdition()
			{
				$this->FltDevise = $this->InsereFltEditHttpPost("lib_devise", "lib_devise") ;
				$this->FltDevise->Libelle = "Devise" ;
				$this->FltDevise->EstEtiquette = 1 ;
				$this->FltMontantDem = $this->InsereFltEditHttpPost("montant_dem", "montant_change") ;
				$this->FltMontantDem->Libelle = "Montant demand&eacute;" ;
				$this->FltTauxDem = $this->InsereFltEditHttpPost("taux_dem", "taux_change") ;
				$this->FltTauxDem->Libelle = "Taux demand&eacute;" ;
				$this->FltMontantSoumis = $this->InsereFltEditHttpPost("montant_soumis", "montant_soumis") ;
				$this->FltMontantSoumis->Libelle = "Montant possible" ;
				$this->FltTauxSoumis = $this->InsereFltEditHttpPost("taux_soumis", "taux_soumis") ;
				$this->FltTauxSoumis->Libelle = "Taux possible" ;
				$this->ChargeOpChangeDem() ;
			}
			public function CalculeElementsRendu()
			{
				parent::CalculeElementsRendu() ;
			}
			public function RenduDispositif()
			{
				$ok = 1 ;
				if($this->InclureElementEnCours == 0)
					$ok = $this->ReponsePossible() ;
				$ctn = '' ;
				if(! $ok)
				{
					$ctn .= $this->MsgReponseInterdit ;
				}
				else
				{
					$ctn .= parent::RenduDispositif() ;
				}
				return $ctn ;
			}
		}
		class FormNegocAchatDeviseTradPlatf extends FormAjustAchatDeviseTradPlatf
		{
			public $InclureElementEnCours = 0 ;
			public $InclureTotalElements = 0 ;
			public $NomClasseCommandeExecuter = "CmdNegocOpChangeTradPlatf" ;
		}
		class FormModifAchatDeviseTradPlatf extends FormAjoutAchatDeviseTradPlatf
		{
			public $InclureElementEnCours = 1 ;
			public $InclureTotalElements = 1 ;
			public $NomClasseCommandeExecuter = "PvCommandeModifElement" ;
		}
		class FormSupprAchatDeviseTradPlatf extends FormModifAchatDeviseTradPlatf
		{
			public $Editable = 0 ;
			public $NomClasseCommandeExecuter = "PvCommandeSupprElement" ;
		}
		
		class FormAjoutVenteDeviseTradPlatf extends FormOpChangeBaseTradPlatf
		{
			public $TypeOpChange = 2 ;
		}
		class FormReponseVenteDeviseTradPlatf extends FormAjoutVenteDeviseTradPlatf
		{
			public $InclureElementEnCours = 1 ;
			public $InclureTotalElements = 1 ;
			public $PourReponse = 1 ;
		}
		class FormNegocVenteDeviseTradPlatf extends FormAjoutVenteDeviseTradPlatf
		{
			public $InclureElementEnCours = 1 ;
			public $InclureTotalElements = 1 ;
			public $PourNegoc = 1 ;
		}
		class FormAjustVenteDeviseTradPlatf extends FormAjustAchatDeviseTradPlatf
		{
			public $PourAjust = 1 ;
		}
		class FormModifVenteDeviseTradPlatf extends FormAjoutVenteDeviseTradPlatf
		{
			public $InclureElementEnCours = 1 ;
			public $InclureTotalElements = 1 ;
			public $NomClasseCommandeExecuter = "PvCommandeModifElement" ;
		}
		class FormSupprVenteDeviseTradPlatf extends FormModifVenteDeviseTradPlatf
		{
			public $Editable = 0 ;
			public $NomClasseCommandeExecuter = "PvCommandeSupprElement" ;
		}

		class TablAchatsDeviseBaseTradPlatf extends TablConsultOpChangeTradPlatf
		{
		}
		
		class ScriptSoumissAchatDeviseTradPlatf extends ScriptListBaseOpChange
		{
			public $TablPrinc ;
			public $TypeOpChange = 1 ;
			protected function DetermineTablPrinc()
			{
				$this->TablPrinc = new TablSoumissOpChangeTradPlatf() ;
				$this->TablPrinc->AdopteScript("tablPrinc", $this) ;
				$this->TablPrinc->ChargeConfig() ;
			}
			public function DetermineEnvironnement()
			{
				parent::DetermineEnvironnement() ;
				$this->DetermineTablPrinc() ;
			}
			public function RenduSpecifique()
			{
				$ctn = '' ;
				$ctn .= $this->BarreMenu->RenduDispositif() ;
				$ctn .= $this->TablPrinc->RenduDispositif() ;
				return $ctn ;
			}
		}
		class ScriptModifOpChangeSoumisTradPlatf extends PvScriptWebSimple
		{
			public $FormPrinc ;
			protected function DetermineFormPrinc()
			{
				$this->FormPrinc = new FormAjustAchatDeviseTradPlatf() ;
				$this->FormPrinc->AdopteScript("formPrinc", $this) ;
				$this->FormPrinc->ChargeConfig() ;
			}
			public function DetermineEnvironnement()
			{
				$this->DetermineFormPrinc() ;
			}
			public function RenduSpecifique()
			{
				$ctn = '' ;
				$ctn .= $this->FormPrinc->RenduDispositif() ;
				return $ctn ;
			}
		}
		
		class ScriptListeAchatsDeviseTradPlatf extends ScriptListBaseOpChange
		{
			public $TypeOpChange = 1 ;
			public $Titre = "Liste achats de devise" ;
			public $TitreDocument = "Liste achats de devise" ;
			protected function CreeTableau()
			{
				return new TablAchatsDeviseBaseTradPlatf() ;
			}
		}
		class ScriptEditAchatsDeviseTradPlatf extends ScriptListeAchatsDeviseTradPlatf
		{
			public $Titre = "Publication achats de devise" ;
			public $TitreDocument = "Publication achats de devise" ;
			protected function CreeTableau()
			{
				return new TablEditOpChangeTradPlatf() ;
			}
		}
		class ScriptReservAchatsDeviseTradPlatf extends ScriptListeAchatsDeviseTradPlatf
		{
			public $Titre = "R&eacute;servations achats de devise" ;
			public $TitreDocument = "R&eacute;servations achats de devise" ;
			protected function CreeTableau()
			{
				return new TablReservOpChangeTradPlatf() ;
			}
		}
		class ScriptAjoutAchatDeviseTradPlatf extends PvScriptWebSimple
		{
			public $TitreDocument = "Nouvel achat de devise" ;
			public $Titre = "Nouvel achat de devise" ;
			public $FormOpChange ;
			protected function CreeFormOpChange()
			{
				return new FormAjoutAchatDeviseTradPlatf() ;
			}
			public function DetermineEnvironnement()
			{
				parent::DetermineEnvironnement() ;
				$this->FormOpChange = $this->CreeFormOpChange() ;
				$this->FormOpChange->AdopteScript("formOpChange", $this) ;
				$this->FormOpChange->ChargeConfig() ;
			}
			public function RenduSpecifique()
			{
				$ctn = '' ;
				$ctn .= $this->FormOpChange->RenduDispositif() ;
				// $ctn .= print_r($this->FormOpChange->FournisseurDonnees->BaseDonnees, true) ;
				return $ctn ;
			}
		}
		class ScriptModifAchatDeviseTradPlatf extends ScriptAjoutAchatDeviseTradPlatf
		{
			public $TitreDocument = "Modif. achat de devise" ;
			public $Titre = "Modif. achat de devise" ;
			protected function CreeFormOpChange()
			{
				return new FormModifAchatDeviseTradPlatf() ;
			}
		}
		class ScriptPostulsAchatDeviseTradPlatf extends ScriptModifAchatDeviseTradPlatf
		{
			public $TitreDocument = "R&eacute;servations achat de devise" ;
			public $Titre = "R&eacute;servations achat de devise" ;
			public $FltIdEnCours ;
			protected function EstConfirme()
			{
				$id = $this->FltIdEnCours->Lie() ;
				$bd = & $this->ApplicationParent->BDPrincipale ;
				$lgnConf = $bd->FetchSqlRow('select * from op_change where num_op_change_dem='.$bd->ParamPrefix.'id and bool_confirme=1', array('id' => $id)) ;
				if(! is_array($lgnConf))
				{
					die('Erreur SQL : '.$bd->ConnectionException) ;
				}
				$ok = 0 ;
				if(count($lgnConf) > 0)
				{
					$ok = 1 ;
				}
				return $ok ;
			}
			protected function DetermineTablPostuls()
			{
				$this->TablPostuls = new TableauDonneesBaseTradPlatf() ;
				$this->TablPostuls->AdopteScript("tablPostuls", $this) ;
				$this->TablPostuls->ChargeConfig() ;
				$this->FltIdEnCours = $this->TablPostuls->InsereFltSelectHttpGet('idEnCours', 'num_op_change_dem = <self>') ;
				$this->FltIdEnCours->LectureSeule = 1 ;
				$this->FltIdEnCours->Obligatoire = 1 ;
				$this->TablPostuls->DeclareFournDonneesSql($this->ApplicationParent->BDPrincipale, '('.TXT_SQL_POSTUL_OP_CHANGE_TRAD_PLATF.')') ;
				$this->TablPostuls->InsereDefColCachee('idEnCours', 'num_op_change') ;
				$this->TablPostuls->InsereDefColCachee('peut_ajuster', 'peut_ajuster') ;
				$this->TablPostuls->InsereDefCol('loginop', 'Login') ;
				$this->TablPostuls->InsereDefCol('nom_entite', 'Etablissement') ;
				$this->TablPostuls->InsereDefCol('date_operation', 'Date rep.', $this->ApplicationParent->BDPrincipale->SqlDateToStrFr('date_operation')) ;
				$colConfirm = $this->TablPostuls->InsereDefColBool('bool_confirme', 'Confirm&eacute;', '') ;
				$colConfirm->AlignElement = "center" ;
				$colActions = $this->TablPostuls->InsereDefColActions("Actions") ;
				$colActions->Largeur = "*" ;
				// $lienAjuster = $this->TablPostuls->InsereLienAction($colActions, $this->ZoneParent->ScriptAjustVenteDevise->ObtientUrl().'&idEnCours=${idEnCours}', 'Ajuster') ;
				$lienAjuster = $this->TablPostuls->InsereLienOuvreFenetreAction($colActions, $this->ZoneParent->ScriptAjustVenteDevise->ObtientUrl().'&idEnCours=${idEnCours}', 'N&eacute;gocier', 'ajuster_${idEnCours}', 'N&eacute;gocier', array('Modal' => 1, 'Largeur' => '450', 'Hauteur' => 300, 'BoutonFermer' => 0)) ;
				$lienAjuster->NomDonneesValid = "peut_ajuster" ;
				$lienConfirm = $this->TablPostuls->InsereLienAction($colActions, $this->ZoneParent->ScriptValPostulVenteDevise->ObtientUrl().'&id=${idEnCours}', 'Confimer') ;
				$lienConfirm->Visible = ! $this->EstConfirme() ;
				$this->TablPostuls->ToujoursAfficher = 1 ;
				$this->TablPostuls->CacherFormulaireFiltres = 1 ;
			}
			public function DetermineEnvironnement()
			{
				parent::DetermineEnvironnement() ;
				$this->FormOpChange->Editable = 0 ;
				$this->FormOpChange->CacherBlocCommandes = 1 ;
				$this->DetermineTablPostuls() ;
			}
			public function RenduSpecifique()
			{
				$ctn = parent::RenduSpecifique() ;
				$ctn .= $this->TablPostuls->RenduDispositif() ;
				// $ctn .= print_r($this->TablPostuls->FournisseurDonnees->BaseDonnees, true) ;
				return $ctn ;
			}
		}
		class ScriptReponseAchatDeviseTradPlatf extends ScriptAjoutAchatDeviseTradPlatf
		{
			public $TitreDocument = "Reponse achat de devise" ;
			public $Titre = "Reponse achat de devise" ;
			protected function CreeFormOpChange()
			{
				return new FormReponseAchatDeviseTradPlatf() ;
			}
		}
		class ScriptNegocAchatDeviseTradPlatf extends ScriptAjoutAchatDeviseTradPlatf
		{
			public $TitreDocument = "N&eacute;gociation achat de devise" ;
			public $Titre = "N&eacute;gociation achat de devise" ;
			protected function CreeFormOpChange()
			{
				return new FormNegocAchatDeviseTradPlatf() ;
				// return new FormAjustAchatDeviseTradPlatf() ;
			}
		}
		class ScriptAjustAchatDeviseTradPlatf extends ScriptAjoutAchatDeviseTradPlatf
		{
			public $TitreDocument = "Ajustement achat de devise" ;
			public $Titre = "Ajustement achat de devise" ;
			protected function CreeFormOpChange()
			{
				return new FormAjustAchatDeviseTradPlatf() ;
			}
		}
		class ScriptSupprAchatDeviseTradPlatf extends ScriptAjoutAchatDeviseTradPlatf
		{
			public $TitreDocument = "Suppr. achat de devise" ;
			public $Titre = "Suppr. achat de devise" ;
			protected function CreeFormOpChange()
			{
				return new FormSupprAchatDeviseTradPlatf() ;
			}
		}
		
		class ScriptSoumissVenteDeviseTradPlatf extends ScriptSoumissAchatDeviseTradPlatf
		{
			public $TypeOpChange = 2 ;
		}
		class ScriptListeVentesDeviseTradPlatf extends ScriptListBaseOpChange
		{
			public $TypeOpChange = 2 ;
			public $Titre = "Liste ventes de devise" ;
			public $TitreDocument = "Liste ventes de devise" ;
			protected function CreeTableau()
			{
				return new TablConsultOpChangeTradPlatf() ;
			}
		}
		class ScriptEditVentesDeviseTradPlatf extends ScriptListeVentesDeviseTradPlatf
		{
			public $Titre = "Publications ventes de devise" ;
			public $TitreDocument = "Publications ventes de devise" ;
			protected function CreeTableau()
			{
				return new TablEditOpChangeTradPlatf() ;
			}
		}
		class ScriptReservVentesDeviseTradPlatf extends ScriptListeVentesDeviseTradPlatf
		{
			public $Titre = "R&eacute;servations ventes de devise" ;
			public $TitreDocument = "R&eacute;servations ventes de devise" ;
			protected function CreeTableau()
			{
				return new TablReservOpChangeTradPlatf() ;
			}
		}
		class ScriptAjoutVenteDeviseTradPlatf extends PvScriptWebSimple
		{
			public $TitreDocument = "Nouvel vente de devise" ;
			public $Titre = "Nouvel vente de devise" ;
			public $FormOpChange ;
			protected function CreeFormOpChange()
			{
				return new FormAjoutVenteDeviseTradPlatf() ;
			}
			public function DetermineEnvironnement()
			{
				parent::DetermineEnvironnement() ;
				$this->FormOpChange = $this->CreeFormOpChange() ;
				$this->FormOpChange->AdopteScript("formOpChange", $this) ;
				$this->FormOpChange->ChargeConfig() ;
			}
			public function RenduSpecifique()
			{
				$ctn = '' ;
				$ctn .= $this->FormOpChange->RenduDispositif() ;
				// $ctn .= print_r($this->FormOpChange->FournisseurDonnees->BaseDonnees, true) ;
				return $ctn ;
			}
		}
		class ScriptModifVenteDeviseTradPlatf extends ScriptAjoutVenteDeviseTradPlatf
		{
			public $TitreDocument = "Modif. vente de devise" ;
			public $Titre = "Modif. vente de devise" ;
			protected function CreeFormOpChange()
			{
				return new FormModifVenteDeviseTradPlatf() ;
			}
		}
		class ScriptPostulsVenteDeviseTradPlatf extends ScriptPostulsAchatDeviseTradPlatf
		{
			public $TitreDocument = "R&eacute;servations vente de devise" ;
			public $TypeOpChange = 2 ;
			public $Titre = "R&eacute;servations vente de devise" ;
		}
		class ScriptReponseVenteDeviseTradPlatf extends ScriptAjoutVenteDeviseTradPlatf
		{
			public $TitreDocument = "Reponse vente de devise" ;
			public $Titre = "Reponse vente de devise" ;
			protected function CreeFormOpChange()
			{
				return new FormReponseVenteDeviseTradPlatf() ;
			}
		}
		class ScriptModifVenteDeviseSoumisTradPlatf extends ScriptAjoutVenteDeviseTradPlatf
		{
			public $TypeOpChange = 1 ;
		}
		class ScriptNegocVenteDeviseTradPlatf extends ScriptAjoutVenteDeviseTradPlatf
		{
			public $TitreDocument = "Negociation vente de devise" ;
			public $Titre = "Negociation vente de devise" ;
			protected function CreeFormOpChange()
			{
				return new FormNegocVenteDeviseTradPlatf() ;
			}
		}
		class ScriptAjustVenteDeviseTradPlatf extends ScriptAjoutVenteDeviseTradPlatf
		{
			public $TitreDocument = "Ajustement vente de devise" ;
			public $Titre = "Ajustement vente de devise" ;
			protected function CreeFormOpChange()
			{
				return new FormAjustVenteDeviseTradPlatf() ;
			}
		}
		class ScriptSupprVenteDeviseTradPlatf extends ScriptAjoutVenteDeviseTradPlatf
		{
			public $TitreDocument = "Suppr. vente de devise" ;
			public $Titre = "Suppr. vente de devise" ;
			protected function CreeFormOpChange()
			{
				return new FormSupprVenteDeviseTradPlatf() ;
			}
		}
		class ScriptValPostulVenteDeviseTradPlatf extends PvScriptWebSimple
		{
			public $MsgSucces = 'La postulation a ete accept&eacute;e' ;
			public $MsgErreur = 'Impossible de confirmer la transaction actuelle.' ;
			public $MsgNonAutorise = 'Vous ne pouvez pas acceder a cette transaction' ;
			public $MsgExec = '' ;
			public $LgnOpChangeSelect = array() ;
			public function DetermineEnvironnement()
			{
				$bd = $this->ApplicationParent->BDPrincipale ;
				$id = (isset($_GET["id"])) ? $_GET["id"] : 0 ;
				$this->LgnOpChangeSelect = $bd->FetchSqlRow('select t1.*, t2.numop numop_dem, t2.type_change type_change_dem,
t2.id_devise1 id_devise1_dem, t2.id_devise2 id_devise2_dem,
t2.montant_change montant_change_dem, t2.taux_change taux_change_dem,
t2.date_operation date_operation_dem, t2.date_valeur date_valeur_dem
from op_change t1 inner join op_change t2
on t1.num_op_change_dem=t2.num_op_change
where t1.num_op_change='.$bd->ParamPrefix.'id and t2.numop='.$bd->ParamPrefix.'numOp', array(
					'id' => $id,
					'numOp' => $this->ZoneParent->Membership->MemberLogged->Id
				)) ;
				if(count($this->LgnOpChangeSelect) > 0)
				{
					$succes = $bd->RunSql('update op_change set bool_confirme=1 where num_op_change='.$bd->ParamPrefix.'id', array('id' => $id)) ;
					if($succes)
					{
						$this->MsgExec = $this->MsgSucces ;
					}
					else
					{
						$this->MsgExec = $this->MsgErreur ;
					}
				}
				else
				{
					$this->MsgExec = $this->MsgNonAutorise ;
				}
			}
			public function RenduSpecifique()
			{
				$ctn = '<p>'.$this->MsgExec.'</p>' ;
				if(count($this->LgnOpChangeSelect) > 0)
				{
					$typeChange = $this->LgnOpChangeSelect["type_change_dem"] ;
					$nomScript = ($typeChange == 1) ? "postulsAchatDevise" : "postulsVenteDevise" ;
					$ctn .= '<p><a href="?'.urlencode($this->ZoneParent->NomParamScriptAppele).'='.urlencode($nomScript).'&idEnCours='.urlencode($this->LgnOpChangeSelect["num_op_change_dem"]).'">Retour a la transaction</a></p>' ;
				}
				return $ctn ;
			}
		}
		
		class CritereEcheanceInvalideOpChange extends PvCritereBase
		{
			public $FormatMessageErreur = 'La date d\'&eacute;ch&eacute;ance ne doit pas &ecirc;tre inferieure &agrave; la date de valeur' ;
			public function EstRespecte()
			{
				return 1 ;
				$estSelect = $this->FormulaireDonneesParent->FltCommissOuTaux->Lie() ;
				$valDateEcheance = $this->FormulaireDonneesParent->FltDateComiss->Lie() ;
				$valDateValeur = $this->FormulaireDonneesParent->FltDateValeur->Lie() ;
				if($estSelect == 1)
					return 1 ;
				if($valDateEcheance < $valDateValeur)
				{
					$this->MessageErreur = $this->FormatMessageErreur ;
					return 0 ;
				}
				return 1 ;
			}
		}
	}
	
?>