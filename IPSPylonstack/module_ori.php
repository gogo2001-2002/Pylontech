<?

class IPSPylonstack extends IPSModule
{
    // helper properties
	private $position = 0;
    
    public function Create()
    {
		//Never delete this line!
        parent::Create();
		
		//These lines are parsed on Symcon Startup or Instance creation
        	//You cannot use variables here. Just static values.
		$this->RegisterPropertyInteger("timer", 5);
                $this->RegisterPropertyBoolean("Ladezustand",FALSE);
		$this->RegisterTimer('Update', 0, ' PSTACK_Update($_IPS[\'TARGET\'], 0);');
		$this->RegisterProfile("Charge_Ah","EnergyStorage",""," Ah",0,0,0,1,2);
		$this->RegisterProfile("Charge_KWh","EnergykwStorage",""," kWh",0,0,0,1,2);
		$this->RegisterProfile("SYNO_Percent","EnergykwStorage",""," %",0,0,0,1,2);

	}

	public function ApplyChanges()
	{
		// wait until IPS is started, dataflow does not work until stated
		$this->RegisterMessage(0, IPS_KERNELSTARTED);
		
		//Never delete this line!
		parent::ApplyChanges();
		// check kernel ready, if not wait
		if (IPS_GetKernelRunlevel() <> KR_READY)
			return;
				
		$this->ValidateConfiguration();
	}
	
	private function ValidateConfiguration()
	{
		$this->SetTimerInterval('Update', $this->ReadPropertyInteger('timer')* 60 * 1000);
	}

        public function Update()
        {
		$UpdateList = IPS_GetInstanceListByModuleID("{AE6E37A8-7241-25CF-005E-9153F322250C}");
		// $this->SendDebug("Recieve:", print_r($UpdateList), 0);
		$UpdateCount = count($UpdateList);
		$m_count = 0;
		$KapaSakt = 0;
		$KapaSNom = 0;
		$KapaSSoc = 0;
		$KapaSKW = 0;
		$val_strom = 0;
		$val_spannung = 0;
		$ladung_kw = 0;
		$soc_stack = 0;

		foreach ($UpdateList as $UpdateListID) {
    			$parent = ips_getparent($UpdateListID);
			$this->SendDebug("Info:", "Update Instance: ".$UpdateListID, 0);
			if ($parent == $this->InstanceID){
        			PTECH_Update($UpdateListID); // Update modul if it's child
				sleep(2);

				$KapanNomID = @IPS_GetObjectIDByIdent ("KapaNom",$UpdateListID);
			        $this->SendDebug("Info:","Berechnen Stack Kapazitaet Nominal", 0);
                                if ($KapanNomID !== FALSE){
					$KapanSNomID = @IPS_GetObjectIDByIdent ("KapaNom",$this->InstanceID);
					if ($KapanSNomID !== FALSE){
						$KapaSNom = $KapaSNom + GetValueFloat($KapanNomID);
					}
					else{
						$this->RegisterVariableFloat("KapaSNom", "Kapazität Stack gesamt","Charge_Ah");
                                      		$KapaSNom = $KapaSNom + GetValueFloat($KapanNomID);
					}
                                } 
	
   
				$KapaaktID = @IPS_GetObjectIDByIdent ("Kapaaktuell",$UpdateListID);
	                                if ($KapaaktID !== FALSE){
					$KapanSaktID = @IPS_GetObjectIDByIdent ("KapaSakt",$this->InstanceID);
					if ($KapanSaktID !== FALSE){
						$KapaSakt = $KapaSakt + GetValueFloat($KapaaktID);
					}
					else{
						$this->RegisterVariableFloat("KapaSakt", "Kapazität Stack aktuell","Charge_Ah");
                                      		$KapaSakt = $KapaSakt + GetValueFloat($KapaaktID);
					}
                                } 
	
          			
			
				$StromID = @IPS_GetObjectIDByIdent ("Strom",$UpdateListID);
                                if ($StromID !== FALSE){
                                        $StackStromID = @IPS_GetObjectIDByIdent ("Strom_SUM",$this->InstanceID);
					if ($StackStromID !== FALSE){
						$val_strom = $val_strom + GetValueFloat($StromID);
					}
					else{
						$this->RegisterVariableFloat("Strom_SUM", "Summe des Stroms","~Ampere");
                                        	$val_strom = $val_strom + GetValueFloat($StromID);
					}
                                }
                                else {
                                        $this->SendDebug("Info:","Spannungswert nicht verfügbar", 0);
                                }		       			
	
			
				$SpannungID = @IPS_GetObjectIDByIdent ("SpannungGes",$UpdateListID);
                                if ($SpannungID !== FALSE){
                                        $StackSpannungID = @IPS_GetObjectIDByIdent ("SpannungGes",$this->InstanceID);
					if ($StackSpannungID !== FALSE){
						$val_spannung = $val_spannung + GetValueFloat($SpannungID);
					}
					else{
						$this->RegisterVariableFloat("SpannungAVG", "Spannung Durchschnitt","~Volt");
                                        	$val_spannung = $val_spannung + GetValueFloat($SpannungID);
					}
                                }
                                else {
                                        $this->SendDebug("Info:","Spannungswert nicht verfügbar", 0);
                                }

        			$m_count++;
    			}
    		}
		if ($m_count == 0 ){
        		$this->SendDebug("Fehler:","Keine Module gefunden", 0);
    		}
    		else {
			if ($m_count > 1){
				$this->SendDebug("Info:",$m_count." Module gefunden", 0);
			}
			else{
				$this->SendDebug("Info:","1 Modul gefunden", 0);
			}
			
			if ($KapaSakt > 0){
				 	$this->SetValue("KapaSakt", $KapaSakt);
			}
			if ($StromID !== FALSE){
                        	$this->SetValue("Strom_SUM", $val_strom);
			}

			if ($SpannungID !== FALSE){
                        	$this->SetValue("SpannungAVG", $val_spannung/$m_count);
			}
			if ($KapanNomID !== FALSE){
                        	$this->SetValue("KapaSNom", $KapaSNom);
			}

			if ($KapaSNom > 0){
				if ($KapaSakt > 0){
					 $soc_stack = $KapaSakt * 100 / $KapaSNom;	
					 $soc_stackID = @IPS_GetObjectIDByIdent ("SOC_Stack",$this->InstanceID);
					 if ($soc_stackID !== FALSE){
						$this->SetValue("Socstack", $soc_stack);}
					  else 			    {
						$this->RegisterVariableFloat("Socstack", "State of Charge aller Akkus","SYNO_Percent");
                                        	$this->SetValue("Socstack", $soc_stack);}

		    						    }
						}
	


			if ($SpannungID !==  FALSE){
				if ($KapaSakt > 0){
					 $ladung_kw = $KapaSakt * ($val_spannung/$m_count) / 1000	;
					 $Ladung_KWID = @IPS_GetObjectIDByIdent ("LadungKWh",$this->InstanceID);
					 if ($Ladung_KWID !== FALSE){
						$this->SetValue("LadungKWh", $ladung_kw);}
					  else 			    {
						$this->RegisterVariableFloat("LadungKWh", "Ladung aller Akkus","Charge_KWh");
                                        	$this->SetValue("LadungKWh", $ladung_kw);}

		    						    }
						}
        }}


	
	/**
	 * Wird ausgeführt wenn der Kernel hochgefahren wurde.
	 * @access protected
	 */
	protected function KernelReady()
	{
		$this->ApplyChanges();
	}
	
	/**
	 * gets current IP-Symcon version
	 * @return float|int
	 */
	protected function GetIPSVersion()
	{
		$ipsversion = floatval(IPS_GetKernelVersion());
		if ($ipsversion < 4.1) // 4.0
		{
			$ipsversion = 0;
		} elseif ($ipsversion >= 4.1 && $ipsversion < 4.2) // 4.1
		{
			$ipsversion = 1;
		} elseif ($ipsversion >= 4.2 && $ipsversion < 4.3) // 4.2
		{
			$ipsversion = 2;
		} elseif ($ipsversion >= 4.3 && $ipsversion < 4.4) // 4.3
		{
			$ipsversion = 3;
		} elseif ($ipsversion >= 4.4 && $ipsversion < 5) // 4.4
		{
			$ipsversion = 4;
		} else   // 5
		{
			$ipsversion = 5;
		}

		return $ipsversion;
	}

	//Profile
	protected function RegisterProfile($Name, $Icon, $Prefix, $Suffix, $MinValue, $MaxValue, $StepSize, $Digits, $Vartype)
	{

		if (!IPS_VariableProfileExists($Name)) {
			IPS_CreateVariableProfile($Name, $Vartype); // 0 boolean, 1 int, 2 float, 3 string,
		} else {
			$profile = IPS_GetVariableProfile($Name);
			if ($profile['ProfileType'] != $Vartype)
				$this->SendDebug("BMW:", "Variable profile type does not match for profile " . $Name, 0);
		}

		IPS_SetVariableProfileIcon($Name, $Icon);
		IPS_SetVariableProfileText($Name, $Prefix, $Suffix);
		IPS_SetVariableProfileDigits($Name, $Digits); //  Nachkommastellen
		IPS_SetVariableProfileValues($Name, $MinValue, $MaxValue, $StepSize); // string $ProfilName, float $Minimalwert, float $Maximalwert, float $Schrittweite
	}

	protected function RegisterProfileAssociation($Name, $Icon, $Prefix, $Suffix, $MinValue, $MaxValue, $Stepsize, $Digits, $Vartype, $Associations)
	{
		if (sizeof($Associations) === 0) {
			$MinValue = 0;
			$MaxValue = 0;
		}

		$this->RegisterProfile($Name, $Icon, $Prefix, $Suffix, $MinValue, $MaxValue, $Stepsize, $Digits, $Vartype);

		//boolean IPS_SetVariableProfileAssociation ( string $ProfilName, float $Wert, string $Name, string $Icon, integer $Farbe )
		foreach ($Associations as $Association) {
			IPS_SetVariableProfileAssociation($Name, $Association[0], $Association[1], $Association[2], $Association[3]);
		}

	}

	/***********************************************************
	 * Configuration Form
	 ***********************************************************/

	/**
	 * build configuration form
	 * @return string
	 */
	public function GetConfigurationForm()
	{
		// return current form
		return json_encode([
			'elements' => $this->FormHead(),
			'actions' => $this->FormActions(),
			'status' => $this->FormStatus()
		]);
	}

	/**
	 * return form configurations on configuration step
	 * @return array
	 */
	protected function FormHead()
	{
		$form = [
			[
				'name' => 'timer',
				'type' => 'NumberSpinner',
				'caption' => 'Aktualisierungsintervall',
				'digits' => '0',
				'suffix' => 'min',
				'minimum' => 1,
				'maximum' => 1440
			],
			[
                                'name' => 'Ladezustand',
                                'type' => 'CheckBox',
                                'caption' => 'Ladezustand in Prozent'
                        ],
		];
		return $form;
	}

	/**
	 * return form actions
	 * @return array
	 */
	protected function FormActions()
	{
		$form = [
			[
				'type' => 'Button',
				'label' => 'Aktualisieren',
				'onClick' => 'PSTACK_Update($id);'
			]
		];

		return $form;
	}

	/**
	 * return from status
	 * @return array
	 */
	protected function FormStatus()
	{
		$form = [
			[
				'code' => 101,
				'icon' => 'inactive',
				'caption' => 'Creating instance.'
			],
			[
				'code' => 102,
				'icon' => 'active',
				'caption' => 'Device created.'
			],
			[
				'code' => 104,
				'icon' => 'inactive',
				'caption' => 'interface closed.'
			],
			[
				'code' => 201,
				'icon' => 'error',
				'caption' => 'special errorcode'
			]
		];

		return $form;
	}

	/**
	 * return incremented position
	 * @return int
	 */
	private function _getPosition()
	{
		$this->position++;
		return $this->position;
	}
	
	/***********************************************************
	 * Migrations
	 ***********************************************************/

	/**
	 * Polyfill for IP-Symcon 4.4 and older
	 * @param string $Ident
	 * @param mixed $Value
	 */
	//Add this Polyfill for IP-Symcon 4.4 and older
	protected function SetValue($Ident, $Value)
	{

		if (IPS_GetKernelVersion() >= 5) {
			parent::SetValue($Ident, $Value);
		} else {
			SetValue($this->GetIDForIdent($Ident), $Value);
		}
	}	
}
