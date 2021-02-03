<?php
    // Klassendefinition
    class LgTvWebOs extends IPSModule {
 
      
      private $host, $port, $ws_key, $path, $lg_key, $sock, $connected=false, $handshaked=false; 

      /*
      public function __construct($host, $port=3000, $lgKey="NOKEY", $path="/") { 
        #parent::__construct($host, $port=3000, $lgKey="NOKEY", $path="/");

        $this->host = $host; 
        $this->port = $port; 
        $this->lg_key = $lgKey; 
        $this->path = $path; 
        $this->ws_key = $key = base64_encode($this->generateRandomString(16, false, true)); 
        if ($this->lg_key=="NOKEY") unset($this->lg_key);     
      } 
      */
      
      
      // Überschreibt die interne IPS_Create($id) Funktion
      public function Create() {
          // Diese Zeile nicht löschen.
          parent::Create();

          $this->RegisterPropertyString ("IPAddress", "1.1.1.1");
          $this->RegisterPropertyString ("LgClientKey","");

          // Propertys
          $this->RegisterPropertyBoolean('mute', false);
          $this->RegisterPropertyBoolean('turnOff', false);
          $this->RegisterPropertyBoolean('volumeUp', false);
          $this->RegisterPropertyBoolean('volumeDown', false);
          $this->RegisterPropertyBoolean('setVolume', false);
          $this->RegisterPropertyBoolean('startApp', false);
          $this->RegisterPropertyBoolean('play_pause', false);

          
          
      }

      // Überschreibt die intere IPS_ApplyChanges($id) Funktion
      public function ApplyChanges() {
          // Diese Zeile nicht löschen
          parent::ApplyChanges();

          // Variable mit Mute anlegen
          if($this->ReadPropertyBoolean("mute")==true) {
            $this->RegisterVariableBoolean("mute","mute","~Switch");
            $this->EnableAction("mute");
            $this->SendDebug("CreateVariable", "mute", 0);
          } else { 
            $this->UnregisterVariable("mute");
            $this->SendDebug("DeleteVariable", "mute", 0);
          }

          // Variable mit turnOff anlegen
          if($this->ReadPropertyBoolean("turnOff")==true) { 
            $this->RegisterVariableBoolean("turnOff","turnOff","~Switch");
            $this->EnableAction("turnOff");
            $this->SendDebug("CreateVariable", "turnOff", 0);
          } else {  
            $this->UnregisterVariable("turnOff");
            $this->SendDebug("DeleteVariable", "turnOff", 0);
          }

          // Variable mit volumeUp anlegen
          if($this->ReadPropertyBoolean("volumeUp")==true) {
            $this->RegisterVariableInteger("volumeUp","volumeUp");
            $this->EnableAction("volumeUp");
            $this->SendDebug("CreateVariable", "volumeUp", 0);
          } else {  
            $this->UnregisterVariable("volumeUp");
            $this->SendDebug("DeleteVariable", "volumeUp", 0);            
          }

          // Variable mit volumeDown anlegen
          if($this->ReadPropertyBoolean("volumeDown")==true) {
            $this->RegisterVariableInteger("volumeDown","volumeDown");
            $this->EnableAction("volumeDown");
            $this->SendDebug("CreateVariable", "volumeDown", 0);
          } else {  
            $this->UnregisterVariable("volumeDown");
            $this->SendDebug("DeleteVariable", "volumeDown", 0);
          } 
            
          // Variable mit setVolume anlegen
          if($this->ReadPropertyBoolean("setVolume")==true) {
            $this->RegisterVariableString("setVolume","setVolume");
            $this->SendDebug("CreateVariable", "setVolume", 0);
          } else {  
            $this->UnregisterVariable("setVolume");
            $this->SendDebug("DeleteVariable", "setVolume", 0);
          } 
            
          // Variable mit startApp anlegen
          if($this->ReadPropertyBoolean("startApp")==true) {
            $this->RegisterVariableString("startApp","startApp");
            $this->SendDebug("CreateVariable", "startApp", 0);
          } else {  
            $this->UnregisterVariable("startApp");
            $this->SendDebug("DeleteVariable", "startApp", 0);
          } 
            
          // Variable mit play pause anlegen
          if($this->ReadPropertyBoolean("play_pause")==true) {
            $this->RegisterVariableInteger("playpause","playPause");
            $this->EnableAction("playpause");
            $this->SendDebug("CreateVariable", "playpause", 0);
          } else {  
            $this->UnregisterVariable("playpause");
            $this->SendDebug("DeleteVariable", "playpause", 0);
          }            
  
      }



      private function connect() { 
        $this->host = $this->ReadPropertyString("IPAddress");
        $this->port = 3000;  
        $this->lg_key = $lgKey="NOKEY"; 
        $this->path = $path="/"; 
        $this->ws_key = $key = base64_encode($this->generateRandomString(16, false, true)); 
        if ($this->lg_key=="NOKEY") unset($this->lg_key); 


        $ws_handshake_cmd = "GET " . $this->path . " HTTP/1.1\r\n"; 
        $ws_handshake_cmd.= "Upgrade: websocket\r\n"; 
        $ws_handshake_cmd.= "Connection: Upgrade\r\n"; 
        $ws_handshake_cmd.= "Sec-WebSocket-Version: 13\r\n";             
        $ws_handshake_cmd.= "Sec-WebSocket-Key: " . $this->ws_key . "\r\n"; 
        $ws_handshake_cmd.= "Host: ".$this->host.":".$this->port."\r\n\r\n"; 
        $this->sock = fsockopen($this->host, $this->port, $errno, $errstr, 2); 
        socket_set_timeout($this->sock, 0, 10000); 
        #echo     "Sending WS handshake\n$ws_handshake_cmd\n"; 
        IPS_LogMessage(IPS_GetName($_IPS['SELF'])." (". $_IPS['SELF'].")","Sending WS handshake\n$ws_handshake_cmd\n");
        $response = $this->send($ws_handshake_cmd); 
        if ($response) 
        { 
            #echo "WS Handshake Response:\n$response\n";
            IPS_LogMessage(IPS_GetName($_IPS['SELF'])." (". $_IPS['SELF'].")","WS Handshake Response:\n$response\n");
        }  
        else  
        #echo "ERROR during WS handshake!\n"; 
        IPS_LogMessage(IPS_GetName($_IPS['SELF'])." (". $_IPS['SELF'].")","ERROR during WS handshake!\n");
        preg_match('#Sec-WebSocket-Accept:\s(.*)$#mU', $response, $matches); 
        if ($matches)  
            { 
            $keyAccept = trim($matches[1]); 
            $expectedResonse = base64_encode(pack('H*', sha1($this->ws_key . '258EAFA5-E914-47DA-95CA-C5AB0DC85B11'))); 
            $this->connected = ($keyAccept === $expectedResonse) ? true : false; 
            }  
        else $this->connected=false; 
        if ($this->connected) 
            #echo "Sucessfull WS connection to $this->host:$this->port\n\n"; 
            IPS_LogMessage(IPS_GetName($_IPS['SELF'])." (". $_IPS['SELF'].")","Sucessfull WS connection to $this->host:$this->port\n\n");
        return $this->connected;   
      } 
       
      private function lg_handshake() 
      { 
        if (!$this->connected) $this->connect(); 
        if ($this->connected) 
        { 
            $this->lg_key = $this->ReadPropertyString("LgClientKey");
            $handshake =    '{"type":"register","id":"register_0","payload":{"forcePairing":false,"pairingType":"PROMPT","client-key":"HANDSHAKEKEYGOESHERE","manifest":{"manifestVersion":1,"appVersion":"1.1","signed":{"created":"20140509","appId":"com.lge.test","vendorId":"com.lge","localizedAppNames":{"":"LG Remote App","ko-KR":"ë¦¬ëª¨ì»¨ ì•±","zxx-XX":"Ð›Ð“ RÑ�Ð¼otÑ� AÐŸÐŸ"},"localizedVendorNames":{"":"LG Electronics"},"permissions":["TEST_SECURE","CONTROL_INPUT_TEXT","CONTROL_MOUSE_AND_KEYBOARD","READ_INSTALLED_APPS","READ_LGE_SDX","READ_NOTIFICATIONS","SEARCH","WRITE_SETTINGS","WRITE_NOTIFICATION_ALERT","CONTROL_POWER","READ_CURRENT_CHANNEL","READ_RUNNING_APPS","READ_UPDATE_INFO","UPDATE_FROM_REMOTE_APP","READ_LGE_TV_INPUT_EVENTS","READ_TV_CURRENT_TIME"],"serial":"2f930e2d2cfe083771f68e4fe7bb07"},"permissions":["LAUNCH","LAUNCH_WEBAPP","APP_TO_APP","CLOSE","TEST_OPEN","TEST_PROTECTED","CONTROL_AUDIO","CONTROL_DISPLAY","CONTROL_INPUT_JOYSTICK","CONTROL_INPUT_MEDIA_RECORDING","CONTROL_INPUT_MEDIA_PLAYBACK","CONTROL_INPUT_TV","CONTROL_POWER","READ_APP_STATUS","READ_CURRENT_CHANNEL","READ_INPUT_DEVICE_LIST","READ_NETWORK_STATE","READ_RUNNING_APPS","READ_TV_CHANNEL_LIST","WRITE_NOTIFICATION_TOAST","READ_POWER_STATE","READ_COUNTRY_INFO"],"signatures":[{"signatureVersion":1,"signature":"eyJhbGdvcml0aG0iOiJSU0EtU0hBMjU2Iiwia2V5SWQiOiJ0ZXN0LXNpZ25pbmctY2VydCIsInNpZ25hdHVyZVZlcnNpb24iOjF9.hrVRgjCwXVvE2OOSpDZ58hR+59aFNwYDyjQgKk3auukd7pcegmE2CzPCa0bJ0ZsRAcKkCTJrWo5iDzNhMBWRyaMOv5zWSrthlf7G128qvIlpMT0YNY+n/FaOHE73uLrS/g7swl3/qH/BGFG2Hu4RlL48eb3lLKqTt2xKHdCs6Cd4RMfJPYnzgvI4BNrFUKsjkcu+WD4OO2A27Pq1n50cMchmcaXadJhGrOqH5YmHdOCj5NSHzJYrsW0HPlpuAx/ECMeIZYDh6RMqaFM2DXzdKX9NmmyqzJ3o/0lkk/N97gfVRLW5hA29yeAwaCViZNCP8iC9aO0q9fQojoa7NQnAtw=="}]}}}'; 
            if (isset($this->lg_key))  
              $handshake = str_replace('HANDSHAKEKEYGOESHERE',$this->lg_key,$handshake); 
            else  $handshake =    '{"type":"register","id":"register_0","payload":{"forcePairing":false,"pairingType":"PROMPT","manifest":{"manifestVersion":1,"appVersion":"1.1","signed":{"created":"20140509","appId":"com.lge.test","vendorId":"com.lge","localizedAppNames":{"":"LG Remote App","ko-KR":"ë¦¬ëª¨ì»¨ ì•±","zxx-XX":"Ð›Ð“ RÑ�Ð¼otÑ� AÐŸÐŸ"},"localizedVendorNames":{"":"LG Electronics"},"permissions":["TEST_SECURE","CONTROL_INPUT_TEXT","CONTROL_MOUSE_AND_KEYBOARD","READ_INSTALLED_APPS","READ_LGE_SDX","READ_NOTIFICATIONS","SEARCH","WRITE_SETTINGS","WRITE_NOTIFICATION_ALERT","CONTROL_POWER","READ_CURRENT_CHANNEL","READ_RUNNING_APPS","READ_UPDATE_INFO","UPDATE_FROM_REMOTE_APP","READ_LGE_TV_INPUT_EVENTS","READ_TV_CURRENT_TIME"],"serial":"2f930e2d2cfe083771f68e4fe7bb07"},"permissions":["LAUNCH","LAUNCH_WEBAPP","APP_TO_APP","CLOSE","TEST_OPEN","TEST_PROTECTED","CONTROL_AUDIO","CONTROL_DISPLAY","CONTROL_INPUT_JOYSTICK","CONTROL_INPUT_MEDIA_RECORDING","CONTROL_INPUT_MEDIA_PLAYBACK","CONTROL_INPUT_TV","CONTROL_POWER","READ_APP_STATUS","READ_CURRENT_CHANNEL","READ_INPUT_DEVICE_LIST","READ_NETWORK_STATE","READ_RUNNING_APPS","READ_TV_CHANNEL_LIST","WRITE_NOTIFICATION_TOAST","READ_POWER_STATE","READ_COUNTRY_INFO"],"signatures":[{"signatureVersion":1,"signature":"eyJhbGdvcml0aG0iOiJSU0EtU0hBMjU2Iiwia2V5SWQiOiJ0ZXN0LXNpZ25pbmctY2VydCIsInNpZ25hdHVyZVZlcnNpb24iOjF9.hrVRgjCwXVvE2OOSpDZ58hR+59aFNwYDyjQgKk3auukd7pcegmE2CzPCa0bJ0ZsRAcKkCTJrWo5iDzNhMBWRyaMOv5zWSrthlf7G128qvIlpMT0YNY+n/FaOHE73uLrS/g7swl3/qH/BGFG2Hu4RlL48eb3lLKqTt2xKHdCs6Cd4RMfJPYnzgvI4BNrFUKsjkcu+WD4OO2A27Pq1n50cMchmcaXadJhGrOqH5YmHdOCj5NSHzJYrsW0HPlpuAx/ECMeIZYDh6RMqaFM2DXzdKX9NmmyqzJ3o/0lkk/N97gfVRLW5hA29yeAwaCViZNCP8iC9aO0q9fQojoa7NQnAtw=="}]}}}'; 
            #echo "Sending LG handshake\n$handshake\n"; 
            IPS_LogMessage(IPS_GetName($_IPS['SELF'])." (". $_IPS['SELF'].")","Sending LG handshake\n$handshake\n");
            $response = $this->send($this->hybi10Encode($handshake)); 
            if ($response) 
            { 
              #echo "\nLG Handshake Response\n".$this->json_string($response)."\n"; 
              IPS_LogMessage(IPS_GetName($_IPS['SELF'])." (". $_IPS['SELF'].")","\nLG Handshake Response\n".$this->json_string($response)."\n");
              $result = $this->json_array($response); 
              if ($result && array_key_exists('id',$result) &&  $result['id']=='result_0' && array_key_exists('client-key',$result['payload'])) 
              { 
                if ($this->lg_key == $result['payload']['client-key']) 
                #echo "LG Client-Key successfully approved\n";  
                IPS_LogMessage(IPS_GetName($_IPS['SELF'])." (". $_IPS['SELF'].")","LG Client-Key successfully approved\n");
              }  
              else if ($result && array_key_exists('id',$result) &&  $result['id']=='register_0' && array_key_exists('pairingType',$result['payload']) && array_key_exists('returnValue',$result['payload'])) 
              { 
                if ($result['payload']['pairingType'] == "PROMPT" && $result['payload']['returnValue'] == "true")  
                { 
                  $starttime = microtime(1); 
                  $lg_key_received = false; 
                  $error_received = false; 
                  do 
                  { 
                    $response = @fread($this->sock, 8192); 
                    $result = $this->json_array($response); 
                    if ($result && array_key_exists('id',$result) &&  $result['id']=='register_0' && is_array($result['payload']) && array_key_exists('client-key',$result['payload'])) 
                    { 
                      $lg_key_received = true; 
                      $this->lg_key = $result['payload']['client-key']; 
                      echo "LG Client-Key successfully received: $this->lg_key\n";  
                      IPS_LogMessage(IPS_GetName($_IPS['SELF'])." (". $_IPS['SELF'].")","LG Client-Key successfully received: $this->lg_key\n");
                      return $this->lg_key;
                    }  
                    else if ($result && array_key_exists('id',$result) &&  $result['id']=='register_0' && array_key_exists('error',$result)) 
                    { 
                      $error_received = true; 
                      #echo "ERROR: ".$result['error']."\n"; 
                      IPS_LogMessage(IPS_GetName($_IPS['SELF'])." (". $_IPS['SELF'].")","ERROR: ".$result['error']."\n");
                    } 
                    usleep(200000); 
                    $time = microtime(1); 
                  }  
                  while ($time-$starttime<60 && !$lg_key_received && !$error_received); 
                } 
              } 
          }  
          else 
          #echo "ERROR during LG handshake:\n"; 
          IPS_LogMessage(IPS_GetName($_IPS['SELF'])." (". $_IPS['SELF'].")","ERROR during LG handshake:\n");
        }  
        else return FALSE;  
      }
      
      private function disconnect() {  
        $this->connected=false; 
        @fclose($this->sock); 
        #echo "Connection closed to $this->host\n";
        IPS_LogMessage(IPS_GetName($_IPS['SELF'])." (". $_IPS['SELF'].")","Connection closed to $this->host\n");
      } 
  
      private function send(string $msg) { 
        @fwrite($this->sock, $msg); 
        usleep(250000); 
        $response = @fread($this->sock, 8192); 
        return $response; 
      } 
       
      private function send_command(string $cmd) { 
        if (!$this->connected) $this->connect(); 
        if ($this->connected) 
        { 
          #echo "Sending command      : $cmd\n";
          IPS_LogMessage(IPS_GetName($_IPS['SELF'])." (". $_IPS['SELF'].")","Sending command      : $cmd\n");
          $response = $this->send($this->hybi10Encode($cmd)); 
          if ($response) 
            #echo "Command response     : ".$this->json_string($response)."\n"; 
            IPS_LogMessage(IPS_GetName($_IPS['SELF'])." (". $_IPS['SELF'].")","Command response     : ".$this->json_string($response)."\n");
          else  
            #echo "Error sending command: $cmd\n"; 
            IPS_LogMessage(IPS_GetName($_IPS['SELF'])." (". $_IPS['SELF'].")","Error sending command: $cmd\n");
          return $response;             
        }  
      } 
  
      public function ConnectForHandshake() {
        $this->connect();
        $key = $this->lg_handshake();
        $this->disconnect();
        return $key;
      }


      //Ab hier können Funktionen hinzugefügt werden 
      public function turnOff() //Einschalten geht nur über WOL! 
      { 
        $this->lg_handshake();
        $command = '{"id":"turnOff","type":"request","uri":"ssap://system/turnOff"}'; 
        $this->send_command($command); 
      } 
  
      public function volumeUp() 
      { 
        $this->lg_handshake();
        $command = '{"id":"volumeUp","type":"request","uri":"ssap://audio/volumeUp"}'; 
        $this->send_command($command); 
      } 
  
      public function volumeDown() 
      { 
        $this->lg_handshake();
        $command = '{"id":"volumeDown","type":"request","uri":"ssap://audio/volumeDown"}'; 
        $this->send_command($command); 
      } 
      
      public function mute(string $mute)
      {
        $this->lg_handshake();
        $command = '{"id":"mute","type":"request","uri":"ssap://audio/setMute","payload":{"mute":"'.$mute.'"}}';
        $this->send_command($command);
      }
    
      public function message(string $msg)
      {
        $this->lg_handshake();
        $command = '{"id":"message","type":"request","uri":"ssap://system.notifications/createToast","payload":{"message":"'.$msg.'"}}';
        $this->send_command($command);
      }
    
      public function startApp(string $app)
      {
        $this->lg_handshake();
        $command = '{"id":"app","type":"request","uri":"ssap://system.launcher/launch","payload":{"id":"'.$app.'"}}';
        $this->send_command($command);
      }
    
      public function play() 
      { 
        $this->lg_handshake();
        $command = '{"id":"play","type":"request","uri":"ssap://media.controls/play"}'; 
        $this->send_command($command); 
      } 
    
      public function pause() 
      { 
        $this->lg_handshake();
        $command = '{"id":"pause","type":"request","uri":"ssap://media.controls/pause"}'; 
        $this->send_command($command); 
      }     

      public function setVolume(int $volume) 
      { 
        $this->lg_handshake();
        $command = '{"id":"volume","type":"request","uri":"ssap://audio/setVolume","payload":{"volume":"'.$volume.'"}}'; 
        $this->send_command($command); 
      } 


      public function RequestAction($Ident, $Value) {
        switch($Ident) {
          case "mute":
            SetValue($this->GetIDForIdent($Ident), $Value);
            break;
          case "turnOff":
            SetValue($this->GetIDForIdent($Ident), $Value);
            break;
          case "volumeUp":
            SetValue($this->GetIDForIdent($Ident), $Value);
            $this->volumeUp();
            break;
          case "volumeDown":
            SetValue($this->GetIDForIdent($Ident), $Value);
            $this->volumeDown();
            break;
          case "setVolume":
            SetValue($this->GetIDForIdent($Ident), $Value);
            break;
          case "startApp":
            SetValue($this->GetIDForIdent($Ident), $Value);
            break;
          case "mute":
            SetValue($this->GetIDForIdent($Ident), $Value);
            break;
          case "play_pause":
            SetValue($this->GetIDForIdent($Ident), $Value);
            break;
          default:
            throw new Exception("Invalid Ident");
        }
      }

      private function hybi10Encode(string $payload, string $type = 'text', bool $masked = true) { 
        $frameHead = array(); 
        $frame = ''; 
        $payloadLength = strlen($payload); 

        switch ($type) { 
            case 'text': 
                $frameHead[0] = 129; 
                break; 

            case 'close': 
                $frameHead[0] = 136; 
                break; 

            case 'ping': 
                $frameHead[0] = 137; 
                break; 

            case 'pong': 
                $frameHead[0] = 138; 
                break; 
        } 

        if ($payloadLength > 65535) 
        { 
            $payloadLengthBin = str_split(sprintf('%064b', $payloadLength), 8); 
            $frameHead[1] = ($masked === true) ? 255 : 127; 
            for ($i = 0; $i < 8; $i++)  
            { 
                $frameHead[$i + 2] = bindec($payloadLengthBin[$i]); 
            } 
            if ($frameHead[2] > 127)  
            { 
                $this->close(1004); 
                return false; 
            } 
        }  
        elseif ($payloadLength > 125)  
        { 
            $payloadLengthBin = str_split(sprintf('%016b', $payloadLength), 8); 
            $frameHead[1] = ($masked === true) ? 254 : 126; 
            $frameHead[2] = bindec($payloadLengthBin[0]); 
            $frameHead[3] = bindec($payloadLengthBin[1]); 
        }  
        else     
        { 
            $frameHead[1] = ($masked === true) ? $payloadLength + 128 : $payloadLength; 
        } 
        foreach (array_keys($frameHead) as $i)  
        { 
            $frameHead[$i] = chr($frameHead[$i]); 
        } 
        if ($masked === true)  
        { 
            $mask = array(); 
            for ($i = 0; $i < 4; $i++) 
            { 
                $mask[$i] = chr(rand(0, 255)); 
            } 
            $frameHead = array_merge($frameHead, $mask); 
        } 
        $frame = implode('', $frameHead); 
        for ($i = 0; $i < $payloadLength; $i++)  
        { 
            $frame .= ($masked === true) ? $payload[$i] ^ $mask[$i % 4] : $payload[$i]; 
        } 
        return $frame; 
      } 

      private function generateRandomString(int $length = 10, bool $addSpaces = true, bool $addNumbers = true) 
      {   
        $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ!"Â§$%&/()=[]{}'; 
        $useChars = array(); 
        for($i = 0; $i < $length; $i++) 
        { 
            $useChars[] = $characters[mt_rand(0, strlen($characters)-1)]; 
        } 
        if($addSpaces === true) 
        { 
            array_push($useChars, ' ', ' ', ' ', ' ', ' ', ' '); 
        } 
        if($addNumbers === true) 
        { 
            array_push($useChars, rand(0,9), rand(0,9), rand(0,9)); 
        } 
        shuffle($useChars); 
        $randomString = trim(implode('', $useChars)); 
        $randomString = substr($randomString, 0, $length); 
        return $randomString; 
      } 

      private function json_array(string $str) 
      { 
        $result = json_decode($this->json_string($str),true); 
        return $result; 
      } 
       
      private function json_string(string $str) 
      { 
        $from = strpos($str,"{"); 
        $to = strripos($str,"}"); 
        $len = $to-$from+1; 
        $result = substr($str,$from,$len); 
        return $result; 
      } 
    }