<?

class IPSPylontech extends IPSModule
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
                $this->RegisterPropertyInteger("address", 1);
		$this->RegisterPropertyBoolean("Zellspannungen",FALSE);
		$this->RegisterPropertyBoolean("Temperaturen",FALSE);
		$this->RegisterPropertyBoolean("Kapazitaeten",FALSE);
		$this->RegisterPropertyBoolean("Gesamtspannung",FALSE);
		$this->RegisterPropertyBoolean("Strom",FALSE);
		$this->RegisterPropertyBoolean("Zyklen",FALSE);
		$this->ConnectParent("{2D76759A-6F96-3DE1-7FBF-371BDE57B9E5}"); // Splitter
		$this->RegisterTimer('Update', 0, ' PTECH_Update($_IPS[\'TARGET\'], 0);');
 		$this->RegisterProfile("Charge_Ah","EnergyStorage",""," Ah",0,0,0,1,2);
		$this->RegisterProfile("Charge_kW","EnergykwStorage",""," kW",0,0,0,1,2);
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
		$this->RegisterVariableString("version", "Version");
		$Zellspannungen = $this->ReadPropertyBoolean("Zellspannungen");
		if ($Zellspannungen == TRUE){
			$this->RegisterVariableFloat("Spannung1", "Spannung 1","~Volt");
			$this->RegisterVariableFloat("Spannung2", "Spannung 2","~Volt");
			$this->RegisterVariableFloat("Spannung3", "Spannung 3","~Volt");
			$this->RegisterVariableFloat("Spannung4", "Spannung 4","~Volt");
			$this->RegisterVariableFloat("Spannung5", "Spannung 5","~Volt");
			$this->RegisterVariableFloat("Spannung6", "Spannung 6","~Volt");
			$this->RegisterVariableFloat("Spannung7", "Spannung 7","~Volt");
			$this->RegisterVariableFloat("Spannung8", "Spannung 8","~Volt");
			$this->RegisterVariableFloat("Spannung9", "Spannung 9","~Volt");
			$this->RegisterVariableFloat("Spannung10", "Spannung 10","~Volt");
			$this->RegisterVariableFloat("Spannung11", "Spannung 11","~Volt");
			$this->RegisterVariableFloat("Spannung12", "Spannung 12","~Volt");
			$this->RegisterVariableFloat("Spannung13", "Spannung 13","~Volt");
			$this->RegisterVariableFloat("Spannung14", "Spannung 14","~Volt");
			$this->RegisterVariableFloat("Spannung15", "Spannung 15","~Volt");
		}
		$Temperaturen = $this->ReadPropertyBoolean("Temperaturen");
		if ($Temperaturen == TRUE){
			$this->RegisterVariableFloat("Temperatur1", "Temperatur 1","~Temperature");
			$this->RegisterVariableFloat("Temperatur2", "Temperatur 2","~Temperature");
			$this->RegisterVariableFloat("Temperatur3", "Temperatur 3","~Temperature");
			$this->RegisterVariableFloat("Temperatur4", "Temperatur 4","~Temperature");
			$this->RegisterVariableFloat("Temperatur5", "Temperatur 5","~Temperature");
		}
		$Kapazitaeten = $this->ReadPropertyBoolean("Kapazitaeten");
		if ($Kapazitaeten == TRUE){
			$this->RegisterVariableFloat("Kapaaktuell", "Aktuell gespeichert","Charge_Ah");
			$this->RegisterVariableFloat("KapaNom", "A Kapazität nominal","Charge_Ah");
			$this->RegisterVariableFloat("Kaparest", "Z Speicherfaehigkeit der Batterie","SYNO_Percent");
			$this->RegisterVariableFloat("KapaSoc", "A SOC aktuelle Batterie","SYNO_Percent");
		}
		$Spannung = $this->ReadPropertyBoolean("Gesamtspannung");
		if ($Spannung == TRUE){
			$this->RegisterVariableFloat("SpannungGes" , "Gesamtspannung","~Volt");
		}
		$Strom = $this->ReadPropertyBoolean("Strom");
		if ($Strom == TRUE){
			$this->RegisterVariableFloat("Strom", "Strom","~Ampere");
		}
		$Zyklen = $this->ReadPropertyBoolean("Zyklen");
		if ($Zyklen == TRUE){
			$this->RegisterVariableInteger("Zyklenzahl", "Zyklenzahl");
		}
	}

        public function Update()
        {
	$splitterID=IPS_GetInstance($this->InstanceID)['ConnectionID'];
	$splitter=IPS_GetInstance($splitterID);
	$splitterStat=$splitter['InstanceStatus'];
	$socketID=$splitter['ConnectionID'];
	$socketStat=IPS_GetInstance($socketID)['InstanceStatus'];
//	$this->SendDebug("Info:", "Splitter ID: ".$splitterID." Splitter Status:".$splitterStat." Socket ID: ".$socketID." Socket Status: ".$socketStat, 0);
	if ($socketStat >= 200){
		$this->SendDebug("Fehler:","Socket fehlerhaft",0);
		return;
	}
	if ($splitterStat >= 200){
                $this->SendDebug("Fehler:","Splitter fehlerhaft",0);
                return;
        }

	$UpdateList = IPS_GetInstanceListByModuleID("{AE6E37A8-7241-25CF-005E-9153F322250C}"); // Pylonstack Instanzen
        $myparent = ips_getparent($this->InstanceID);
	foreach ($UpdateList as $UpdateListID) {
        	if ($myparent == $UpdateListID){ // Am I a child of Pylonstack?
			$this->SetTimerInterval('Update', 0); // disable own Update-Timer
		}
	}
	$address = $this->ReadPropertyInteger("address");
//	$adrstr = $address+1;
//        $request = "\x7E\x32\x30\x30".$adrstr."\x34\x36\x34\x32\x45\x30\x30\x32\x30\x32\x46\x44\x33\x33\x0D";
	$array = array(0x7E, 0x32, 0x30, 0x30, 0x32, 0x34, 0x36, 0x34, 0x32, 0x45, 0x30, 0x30, 0x32, 0x30, 0x32, 0x46, 0x44, 0x33, 0x33, 0x0D);

	$array[4] = ord($address+1);
	$array[14] = ord($address+1);
	// Checksumme bilden und ins Array schreiben
	$summe = 0;
	for ($i = 1; $i < 15;$i++){
		$summe = $summe + $array[$i];
	}
	$summe = $summe ^ 0xFFFF; // XOR
	$summe = $summe + 1;

	$hexstring =  sprintf("%02X", $summe);

	$array[15] = ord(substr($hexstring, 0,1));
	$array[16] = ord(substr($hexstring, 1,1));
	$array[17] = ord(substr($hexstring, 2,1));
	$array[18] = ord(substr($hexstring, 3,1));


	// Request bilden
	$request = "";
	foreach ($array as $char) {
		$request = $request.chr($char);
	}


	$this->SendToSplitter($request);
        }

	
	public function MessageSink($TimeStamp, $SenderID, $Message, $Data)
	{
		switch ($Message) {
			case IPS_KERNELSTARTED: // only after IP-Symcon started
				$this->KernelReady(); // if IP-Symcon is ready
				break;
		}
	}

	/**
	 * Wird ausgeführt wenn der Kernel hochgefahren wurde.
	 * @access protected
	 */
	protected function KernelReady()
	{
		$this->ApplyChanges();
	}
	
	
	protected function SendToSplitter(string $payload)
	{						
		// send to splitter
		$result = $this->SendDataToParent(json_encode(Array("DataID" => "{4B53FBDB-28B2-0A2F-B471-DEF8A43EA262}", "Buffer" => $payload))); // Interface GUI
		return $result;
	}
	
	public function ReceiveData($JSONString)
	{
	   $this->SendDebug("Recieve:", $JSONString, 0);
	   $receive = json_decode($JSONString);
	   $Data = $receive->{'Buffer'};
	   if (strlen($Data) != 140) {$this->SendDebug("Error_2:", "Datalength != 140 Byte", 0); };
	   $errorlenstring =  sprintf("%4d", strlen($Data));
 	   $this->SendDebug("Length:", strlen($errorlenstring), 0);
	   $address = $this->ReadPropertyInteger("address");
         //$this->SendDebug("Adress",$adrstr , 0);
	   $adrstr = $address+1;
	   $adrrec = hexdec($Data[3].$Data[4]);
         $this->SendDebug("Adress",$adrstr , 0);
  	   $this->SendDebug("Adress_rec",$adrrec , 0);
	   if ($adrstr == $adrrec and strlen($Data) == 140){
		$this->SetValue("version", $Data[1].".".$Data[2]);

		$Zellzahl = hexdec($Data[17].$Data[18]);
		$Zellspannungen = $this->ReadPropertyBoolean("Zellspannungen");
		if ($Zellspannungen == TRUE){
			$this->SetValue("Spannung1", hexdec($Data[19].$Data[20].$Data[21].$Data[22])/1000);
			$this->SetValue("Spannung2", hexdec($Data[23].$Data[24].$Data[25].$Data[26])/1000);
			$this->SetValue("Spannung3", hexdec($Data[27].$Data[28].$Data[29].$Data[30])/1000);
			$this->SetValue("Spannung4", hexdec($Data[31].$Data[32].$Data[33].$Data[34])/1000);
			$this->SetValue("Spannung5", hexdec($Data[35].$Data[36].$Data[37].$Data[38])/1000);
			$this->SetValue("Spannung6", hexdec($Data[39].$Data[40].$Data[41].$Data[42])/1000);
			$this->SetValue("Spannung7", hexdec($Data[43].$Data[44].$Data[45].$Data[46])/1000);
			$this->SetValue("Spannung8", hexdec($Data[47].$Data[48].$Data[49].$Data[50])/1000);
			$this->SetValue("Spannung9", hexdec($Data[51].$Data[52].$Data[53].$Data[54])/1000);
			$this->SetValue("Spannung10", hexdec($Data[55].$Data[56].$Data[57].$Data[58])/1000);
			$this->SetValue("Spannung11", hexdec($Data[59].$Data[60].$Data[61].$Data[62])/1000);
			$this->SetValue("Spannung12", hexdec($Data[63].$Data[64].$Data[65].$Data[66])/1000);
			$this->SetValue("Spannung13", hexdec($Data[67].$Data[68].$Data[69].$Data[70])/1000);
			$this->SetValue("Spannung14", hexdec($Data[71].$Data[72].$Data[73].$Data[74])/1000);
			$this->SetValue("Spannung15", hexdec($Data[75].$Data[76].$Data[77].$Data[78])/1000);
		}
		$TemperaturAnzahl = hexdec($Data[79].$Data[80]);
		$Temperaturen = $this->ReadPropertyBoolean("Temperaturen");
		if ($Temperaturen == TRUE){
			$this->SetValue("Temperatur1", (hexdec($Data[81].$Data[82].$Data[83].$Data[84])-2731)/10);
			$this->SetValue("Temperatur2", (hexdec($Data[85].$Data[86].$Data[87].$Data[88])-2731)/10);
			$this->SetValue("Temperatur3", (hexdec($Data[89].$Data[90].$Data[91].$Data[92])-2731)/10);
			$this->SetValue("Temperatur4", (hexdec($Data[93].$Data[94].$Data[95].$Data[96])-2731)/10);
			$this->SetValue("Temperatur5", (hexdec($Data[97].$Data[98].$Data[99].$Data[100])-2731)/10);
		}
		$Kapazitaeten = $this->ReadPropertyBoolean("Kapazitaeten");
		if ($Kapazitaeten == TRUE){
			$this->SetValue("Kapaaktuell", hexdec($Data[123].$Data[124].$Data[125].$Data[126].$Data[127].$Data[128])/1000);
			$this->SetValue("KapaNom", hexdec($Data[129].$Data[130].$Data[131].$Data[132].$Data[133].$Data[134]   )/1000);
			$this->SetValue("Kaparest", hexdec($Data[109].$Data[110].$Data[111].$Data[112])/hexdec($Data[115].$Data[116].$Data[117].$Data[118])*100 );
                  $this->SetValue("KapaSoc", hexdec($Data[123].$Data[124].$Data[125].$Data[126].$Data[127].$Data[128])/hexdec($Data[129].$Data[130].$Data[131].$Data[132].$Data[133].$Data[134]   )*100 );
		}
		$Spannung = $this->ReadPropertyBoolean("Gesamtspannung");
		if ($Spannung == TRUE){
			$this->SetValue("SpannungGes",hexdec($Data[105].$Data[106].$Data[107].$Data[108])/1000);
		}
		$Strom = $this->ReadPropertyBoolean("Strom");
		if ($Strom == TRUE){
			$short = hexdec($Data[101].$Data[102].$Data[103].$Data[104]);
	   		if($short >= 32768) { $short -= 65536; }
			$this->SetValue("Strom",$short/10);
		}
		$Zyklen = $this->ReadPropertyBoolean("Zyklen");
		if ($Zyklen == TRUE){
			$this->SetValue("Zyklenzahl",hexdec($Data[119].$Data[120].$Data[121].$Data[122]));
		}
	   }
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
//				'minimum' => 1,
				'minimum' => 0,
				'maximum' => 1440
			],
			[
				'name' => 'address',
				'type' => 'Select',
				'caption' => 'Adresse',
				'options' => [
					[
						'label' => '1',
						'value' => 1
					],
					[
						'label' => '2',
						'value' => 2
					],
                                        [
                                                'label' => '3',
                                                'value' => 3
                                        ],
                                        [
                                                'label' => '4',
                                                'value' => 4
                                        ],
                                        [
                                                'label' => '5',
                                                'value' => 5
                                        ],
                                        [
                                                'label' => '6',
                                                'value' => 6
                                        ],
                                        [
                                                'label' => '7',
                                                'value' => 7
                                        ],
					[
						'label' => '8',
						'value' => 8

					]
				]
			],
			[
				'name' => 'Zellspannungen',
				'type' => 'CheckBox',
				'caption' => 'Zellspannungen'
			],
			[
				'name' => 'Temperaturen',
				'type' => 'CheckBox',
				'caption' => 'Temperaturen'
			],
			[
				'name' => 'Kapazitaeten',
				'type' => 'CheckBox',
				'caption' => 'Kapazitäten'
			],
			[	'name' => 'Strom',
				'type' => 'CheckBox',
				'caption' => 'Strom'
			],
			[	'name' => 'Gesamtspannung',
				'type' => 'CheckBox',
				'caption' => 'Gesamtspannung'
			],
			[	'name' => 'Zyklen',
				'type' => 'CheckBox',
				'caption' => 'Zyklenzahl'
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
				'label' => 'Abfrage',
				'onClick' => 'PTECH_Update($id);'
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