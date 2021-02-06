<?php

trait LGTV_HelperVariables
{

    /**
     * RegisterProfileBoolean (creating a boolean variable profile with given parameters)
     *
     * @param $Name
     * @param $Icon
     * @param $Prefix
     * @param $Suffix
     * @return bool
     */
    protected function RegisterProfileBoolean($Name, $Icon, $Prefix, $Suffix)
    {
        if (IPS_VariableProfileExists($Name) === false) {
            IPS_CreateVariableProfile($Name, 0);
        } else {
            $ProfileInfo = IPS_GetVariableProfile($Name);
            if ($ProfileInfo['ProfileType'] !== 0) {
                $this->SendDebug(__FUNCTION__, 'Type of variable does not match the variable profile "' . $Name . '"', 0);
                return false;
            }
        }

        IPS_SetVariableProfileIcon($Name, $Icon);
        IPS_SetVariableProfileText($Name, $Prefix, $Suffix);

        return true;
    }


    /**
     * RegisterProfileBooleanEx (creating a boolean variable profile with given parameters and extra associations)
     *
     * @param $Name
     * @param $Icon
     * @param $Prefix
     * @param $Suffix
     * @param $Associations
     * @return bool
     */
    protected function RegisterProfileBooleanEx($Name, $Icon, $Prefix, $Suffix, $Associations)
    {
        $this->RegisterProfileBoolean($Name, $Icon, $Prefix, $Suffix);

        foreach ($Associations as $Association) {
            IPS_SetVariableProfileAssociation($Name, $Association[0], $Association[1], $Association[2], $Association[3]);
        }

        return true;
    }


    /**
     * SetValue (set variable to new value, no matter whether the new value is the same or different)
     *
     * @param string $Ident
     * @param $Value
     * @return bool
     */
    protected function SetValue($Ident, $Value)
    {
        $VarID = @$this->GetIDForIdent($Ident);

        if (IPS_GetKernelVersion() >= 5) {
            if ($VarID > 0) {
                switch (IPS_GetVariable($VarID)['VariableType']) {
                    case 0:
                        parent::SetValue($Ident, (bool)$Value);
                        break;

                    case 1:
                        parent::SetValue($Ident, (int)$Value);
                        break;

                    case 2:
                        parent::SetValue($Ident, (float)$Value);
                        break;

                    case 3:
                        parent::SetValue($Ident, (string)$Value);
                        break;
                }
                return true;
            }
        } else {
            if ($VarID > 0) {
                switch (IPS_GetVariable($VarID)['VariableType']) {
                    case 0:
                        SetValue($VarID, (bool)$Value);
                        break;

                    case 1:
                        SetValue($VarID, (int)$Value);
                        break;

                    case 2:
                        SetValue($VarID, (float)$Value);
                        break;

                    case 3:
                        SetValue($VarID, (string)$Value);
                        break;
                }
                return true;
            }
        }

        return false;
    }


    /**
     * SetValue_IfDifferent (set variable to new value, if the value is different)
     *
     * @param string $Ident
     * @param $Value
     * @return bool
     */
    protected function SetValue_IfDifferent($Ident, $Value)
    {
        $VarID = @$this->GetIDForIdent($Ident);

        if (IPS_GetKernelVersion() >= 5) {
            if ($VarID > 0) {
                if (parent::GetValue($Ident) != $Value) {
                    switch (IPS_GetVariable($VarID)['VariableType']) {
                        case 0:
                            parent::SetValue($Ident, (bool)$Value);
                            break;

                        case 1:
                            parent::SetValue($Ident, (int)$Value);
                            break;

                        case 2:
                            parent::SetValue($Ident, (float)$Value);
                            break;

                        case 3:
                            parent::SetValue($Ident, (string)$Value);
                            break;
                    }
                    return true;
                }
            }
        } else {
            if ($VarID > 0) {
                if (GetValue($VarID) != $Value) {
                    switch (IPS_GetVariable($VarID)['VariableType']) {
                        case 0:
                            SetValue($VarID, (bool)$Value);
                            break;

                        case 1:
                            SetValue($VarID, (int)$Value);
                            break;

                        case 2:
                            SetValue($VarID, (float)$Value);
                            break;

                        case 3:
                            SetValue($VarID, (string)$Value);
                            break;
                    }
                    return true;
                }
            }
        }

        return false;
    }


    /**
     * Variable_Register (register and create variable with some parameters)
     *
     * @param $VarIdent
     * @param $VarName
     * @param $VarProfile
     * @param $VarIcon
     * @param $VarType
     * @param $EnableAction
     * @param $PositionX
     */
    protected function Variable_Register($VarIdent, $VarName, $VarProfile, $VarIcon, $VarType, $EnableAction, $PositionX = false, $Debug = false)
    {
      if ($PositionX === false) {
          $Position = 0;
      } else {
          $Position = $PositionX;
      }

      switch ($VarType) {
          case 0:
            $this->RegisterVariableBoolean($VarIdent, $VarName, $VarProfile, $Position);
            $Debug_VarType = "bool";
            break;

          case 1:
            $this->RegisterVariableInteger($VarIdent, $VarName, $VarProfile, $Position);
            $Debug_VarType = "integer";
            break;

          case 2:
            $this->RegisterVariableFloat($VarIdent, $VarName, $VarProfile, $Position);
            $Debug_VarType = "float";
            break;

          case 3:
            $this->RegisterVariableString($VarIdent, $VarName, $VarProfile, $Position);
            $Debug_VarType = "string";
            break;
      }

      if ($VarIcon !== '') {
          IPS_SetIcon($this->GetIDForIdent($VarIdent), $VarIcon);
      }

      if ($EnableAction === true) {
          $this->EnableAction($VarIdent);
      }

      if ($Debug === true) {
        $Debug_Msg = "Create Variable with Type=".$Debug_VarType." (".$this->GetIDForIdent($VarIdent)."), EnableAction="."$EnableAction".",Icon=\""."$VarIcon"."\",Position="."$Position".".";
        $this->SendDebug("Variable_Register", $Debug_Msg, 0);
      }
    }

    protected function Variable_UnRegister($VarIdent, $Debug = false) 
    {
      if(@$this->GetIDForIdent($VarIdent)==true) {
        $this->UnregisterVariable($VarIdent);
      
        if ($Debug === true) {
          $Debug_Msg = "Delete Variable (".$VarIdent.")";
          $this->SendDebug("Variable_Register", $Debug_Msg, 0);
        }
      }
    }


}
