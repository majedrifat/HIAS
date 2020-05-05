<?php
include dirname(__FILE__) . '/../../iotJumpWay/Classes/pbkdf2.php';

    class iotJumpWay
    {

        function __construct($_GeniSys)
        {
            $this->_GeniSys = $_GeniSys;
        }

        public function getLocations()
        {
            $pdoQuery = $this->_GeniSys->_secCon->prepare("
                SELECT *
                FROM mqttl
                ORDER BY id DESC
            ");
            $pdoQuery->execute();
            $response=$pdoQuery->fetchAll(PDO::FETCH_ASSOC);
            $pdoQuery->closeCursor();
            $pdoQuery = null;
            return $response;
        }

        public function getLocation($id)
        {
            $pdoQuery = $this->_GeniSys->_secCon->prepare("
                SELECT *
                FROM mqttl
                WHERE id = :id 
            ");
            $pdoQuery->execute([
                ":id" => $id 
            ]);
            $response=$pdoQuery->fetch(PDO::FETCH_ASSOC);
            $pdoQuery->closeCursor();
            $pdoQuery = null;
            return $response;
        }

        public function update()
        {
            if(!filter_input(INPUT_POST, "id", FILTER_SANITIZE_NUMBER_INT)):
                return [
                    "Response"=> "Failed", 
                    "Message" => "ID is required"
                ];
            endif;
            if(!filter_input(INPUT_POST, "name", FILTER_SANITIZE_STRING)):
                return [
                    "Response"=> "Failed", 
                    "Message" => "Name is required"
                ];
            endif;
            if(!filter_input(INPUT_POST, "ip", FILTER_SANITIZE_STRING)):
                return [
                    "Response"=> "Failed", 
                    "Message" => "Location IP is required"
                ];
            endif;
            if(!filter_input(INPUT_POST, "mac", FILTER_SANITIZE_STRING)):
                return [
                    "Response"=> "Failed", 
                    "Message" => "Location MAC is required"
                ];
            endif;

            $pdoQuery = $this->_GeniSys->_secCon->prepare("
                UPDATE mqttl
                SET name = :name,
                    ip = :ip, 
                    mac = :mac
                WHERE id = :id 
            ");
            $pdoQuery->execute([
                ":name" => filter_input(INPUT_POST, "name", FILTER_SANITIZE_STRING),
                ":ip" => $this->_GeniSys->_helpers->oEncrypt(filter_input(INPUT_POST, "ip", FILTER_SANITIZE_STRING)),
                ":mac" => $this->_GeniSys->_helpers->oEncrypt(filter_input(INPUT_POST, "mac", FILTER_SANITIZE_STRING)),
                ":id" => filter_input(INPUT_POST, "id", FILTER_SANITIZE_NUMBER_INT)
            ]);
            $pdoQuery->closeCursor();
            $pdoQuery = null;
            return [
                "Response"=> "OK", 
                "Message" => "Location updated!"
            ];
        }

        public function getZones()
        {
            $pdoQuery = $this->_GeniSys->_secCon->prepare("
                SELECT *
                FROM mqttlz
                ORDER BY id DESC
            ");
            $pdoQuery->execute();
            $response=$pdoQuery->fetchAll(PDO::FETCH_ASSOC);
            $pdoQuery->closeCursor();
            $pdoQuery = null;
            return $response;
        }

        public function getZone($id)
        {
            $pdoQuery = $this->_GeniSys->_secCon->prepare("
                SELECT *
                FROM mqttlz
                WHERE id = :id 
                ORDER BY id DESC
            ");
            $pdoQuery->execute([
                ":id" => $id
            ]);
            $response=$pdoQuery->fetch(PDO::FETCH_ASSOC);
            $pdoQuery->closeCursor();
            $pdoQuery = null;
            return $response;
        }

        public function createZone()
        {
            if(!filter_input(INPUT_POST, "name", FILTER_SANITIZE_STRING)):
                return [
                    "Response"=> "Failed", 
                    "Message" => "Name is required"
                ];
            endif;
            
            if(!filter_input(INPUT_POST, "lid", FILTER_SANITIZE_NUMBER_INT)):
                return [
                    "Response"=> "Failed", 
                    "Message" => "Location ID is required"
                ];
            endif;
            
            $query = $this->_GeniSys->_secCon->prepare("
                INSERT INTO  mqttlz  (
                    `lid`,
                    `zn`,
                    `time`
                )  VALUES (
                    :lid,
                    :zn,
                    :time
                )
            ");
            $query->execute([
                ':lid' => filter_input(INPUT_POST, "lid", FILTER_SANITIZE_NUMBER_INT),
                ':zn' => filter_input(INPUT_POST, "name", FILTER_SANITIZE_STRING),
                ':time' => time()
            ]);
            $zid = $this->_GeniSys->_secCon->lastInsertId();
    
            $query = $this->_GeniSys->_secCon->prepare("
                UPDATE mqttl
                SET zones = zones + 1
                WHERE id = :id
            ");
            $query->execute(array(
                ':id'=>filter_input(INPUT_POST, "lid", FILTER_SANITIZE_NUMBER_INT)
            ));

            return [
                "Response"=> "OK", 
                "Message" => "Zone created!", 
                "LID" => filter_input(INPUT_POST, "lid", FILTER_SANITIZE_NUMBER_INT), 
                "ZID" => $zid
            ];
        }

        public function updateZone()
        {
            if(!filter_input(INPUT_POST, "id", FILTER_SANITIZE_NUMBER_INT)):
                return [
                    "Response"=> "Failed", 
                    "Message" => "ID is required"
                ];
            endif;
            
            if(!filter_input(INPUT_POST, "lid", FILTER_SANITIZE_NUMBER_INT)):
                return [
                    "Response"=> "Failed", 
                    "Message" => "Location ID is required"
                ];
            endif;

            if(!filter_input(INPUT_POST, "name", FILTER_SANITIZE_STRING)):
                return [
                    "Response"=> "Failed", 
                    "Message" => "Name is required"
                ];
            endif;

            $pdoQuery = $this->_GeniSys->_secCon->prepare("
                UPDATE mqttlz
                SET zn = :zn,
                    lid = :lid
                WHERE id = :id 
            ");
            $pdoQuery->execute([
                ":zn" => filter_input(INPUT_POST, "name", FILTER_SANITIZE_STRING),
                ":lid" => filter_input(INPUT_POST, "lid", FILTER_SANITIZE_STRING),
                ":id" => filter_input(INPUT_POST, "id", FILTER_SANITIZE_NUMBER_INT)
            ]);
            $pdoQuery->closeCursor();
            $pdoQuery = null;
            return [
                "Response"=> "OK", 
                "Message" => "Zone updated!"
            ];
        }

        public function getDevices()
        {
            $pdoQuery = $this->_GeniSys->_secCon->prepare("
                SELECT *
                FROM mqttld
                ORDER BY id DESC
            ");
            $pdoQuery->execute();
            $response=$pdoQuery->fetchAll(PDO::FETCH_ASSOC);
            $pdoQuery->closeCursor();
            $pdoQuery = null;
            return $response;
        }

        public function getDevice($id)
        {
            $pdoQuery = $this->_GeniSys->_secCon->prepare("
                SELECT *
                FROM mqttld
                WHERE id = :id 
                ORDER BY id DESC
            ");
            $pdoQuery->execute([
                ":id" => $id
            ]);
            $response=$pdoQuery->fetch(PDO::FETCH_ASSOC);
            $pdoQuery->closeCursor();
            $pdoQuery = null;
            return $response;
        }

        public function createDevice()
        {
            if(!filter_input(INPUT_POST, "name", FILTER_SANITIZE_STRING)):
                return [
                    "Response"=> "Failed", 
                    "Message" => "Name is required"
                ];
            endif;
            
            if(!filter_input(INPUT_POST, "lid", FILTER_SANITIZE_NUMBER_INT)):
                return [
                    "Response"=> "Failed", 
                    "Message" => "Location ID is required"
                ];
            endif;
            
            if(!filter_input(INPUT_POST, "zid", FILTER_SANITIZE_NUMBER_INT)):
                return [
                    "Response"=> "Failed", 
                    "Message" => "Zone ID is required"
                ];
            endif;

            if(!filter_input(INPUT_POST, "ip", FILTER_SANITIZE_STRING)):
                return [
                    "Response"=> "Failed", 
                    "Message" => "Location IP is required"
                ];
            endif;
            if(!filter_input(INPUT_POST, "mac", FILTER_SANITIZE_STRING)):
                return [
                    "Response"=> "Failed", 
                    "Message" => "Location MAC is required"
                ];
            endif;
            if(!filter_input(INPUT_POST, "lt", FILTER_SANITIZE_STRING)):
                return [
                    "Response"=> "Failed", 
                    "Message" => "Location latitude is required"
                ];
            endif;
            if(!filter_input(INPUT_POST, "lg", FILTER_SANITIZE_STRING)):
                return [
                    "Response"=> "Failed", 
                    "Message" => "Location longitude is required"
                ];
            endif;

            $mqttUser = $this->_GeniSys->_helpers->generateKey(12);
            $mqttPass = $this->_GeniSys->_helpers->password();
            $mqttHash = create_hash($mqttPass);
    
            $apiKey = $this->_GeniSys->_helpers->generateKey(30);
            $apiSecretKey = $this->_GeniSys->_helpers->generateKey(35);
            
            $query = $this->_GeniSys->_secCon->prepare("
                INSERT INTO  mqttld  (
                    `lid`,
                    `zid`,
                    `name`,
                    `mqttu`,
                    `mqttp`,
                    `apub`,
                    `aprv`,
                    `ip`,
                    `mac`,
                    `lt`,
                    `lg`,
                    `time`
                )  VALUES (
                    :lid,
                    :zid,
                    :name,
                    :mqttu,
                    :mqttp,
                    :apub,
                    :aprv,
                    :ip,
                    :mac,
                    :lt,
                    :lg,
                    :time
                )
            ");
            $query->execute([
                ':lid' => filter_input(INPUT_POST, "lid", FILTER_SANITIZE_NUMBER_INT),
                ':zid' => filter_input(INPUT_POST, "zid", FILTER_SANITIZE_NUMBER_INT),
                ':name' => filter_input(INPUT_POST, "name", FILTER_SANITIZE_STRING),
                ':mqttu' =>$this->_GeniSys->_helpers->oEncrypt($mqttUser),
                ':mqttp' =>$this->_GeniSys->_helpers->oEncrypt($mqttPass),
                ':apub' => $this->_GeniSys->_helpers->oEncrypt($apiKey),
                ':aprv' => $this->_GeniSys->_helpers->oEncrypt($apiSecretKey),
                ':ip' => $this->_GeniSys->_helpers->oEncrypt(filter_input(INPUT_POST, "ip", FILTER_SANITIZE_STRING)),
                ':mac' => $this->_GeniSys->_helpers->oEncrypt(filter_input(INPUT_POST, "mac", FILTER_SANITIZE_STRING)),
                ':lt' => $this->_GeniSys->_helpers->oEncrypt(filter_input(INPUT_POST, "lt", FILTER_SANITIZE_STRING)),
                ':lg' => $this->_GeniSys->_helpers->oEncrypt(filter_input(INPUT_POST, "lg", FILTER_SANITIZE_STRING)),
                ':time' => time()
            ]);
            $did = $this->_GeniSys->_secCon->lastInsertId();
    
            $query = $this->_GeniSys->_secCon->prepare("
                INSERT INTO  mqttu  (
                    `lid`,
                    `zid`,
                    `did`,
                    `uname`,
                    `pw`
                )  VALUES (
                    :lid,
                    :zid,
                    :did,
                    :uname,
                    :pw
                )
            ");
            $query->execute([
                ':lid' => filter_input(INPUT_POST, "lid", FILTER_SANITIZE_NUMBER_INT),
                ':zid' => filter_input(INPUT_POST, "zid", FILTER_SANITIZE_NUMBER_INT),
                ':did' => $did,
                ':uname' => $mqttUser,
                ':pw' => $mqttHash
            ]);

            $query = $this->_GeniSys->_secCon->prepare("
                INSERT INTO  mqttua  (
                    `lid`,
                    `zid`,
                    `did`,
                    `username`,
                    `topic`,
                    `rw`
                )  VALUES (
                    :lid,
                    :zid,
                    :did,
                    :username,
                    :topic,
                    :rw
                )
            ");
            $query->execute(array(
                ':lid' => filter_input(INPUT_POST, "lid", FILTER_SANITIZE_NUMBER_INT),
                ':zid' => filter_input(INPUT_POST, "zid", FILTER_SANITIZE_NUMBER_INT),
                ':did' => $did,
                ':username' => $mqttUser,
                ':topic' => filter_input(INPUT_POST, "lid", FILTER_SANITIZE_NUMBER_INT)."/Devices/#",
                ':rw' => 4
            ));
    
            $query = $this->_GeniSys->_secCon->prepare("
                INSERT INTO  mqttua  (
                    `lid`,
                    `zid`,
                    `did`,
                    `username`,
                    `topic`,
                    `rw`
                )  VALUES (
                    :lid,
                    :zid,
                    :did,
                    :username,
                    :topic,
                    :rw
                )
            ");
            $query->execute(array(
                ':lid' => filter_input(INPUT_POST, "lid", FILTER_SANITIZE_NUMBER_INT),
                ':zid' => filter_input(INPUT_POST, "zid", FILTER_SANITIZE_NUMBER_INT),
                ':did' => $did,
                ':username' => $mqttUser,
                ':topic' => filter_input(INPUT_POST, "lid", FILTER_SANITIZE_NUMBER_INT)."/Applications/#",
                ':rw' => 2
            ));
    
            $query = $this->_GeniSys->_secCon->prepare("
                UPDATE mqttl
                SET devices = devices + 1
                WHERE id = :id
            ");
            $query->execute(array(
                ':id'=>filter_input(INPUT_POST, "lid", FILTER_SANITIZE_NUMBER_INT)
            ));

            return [
                "Response"=> "OK", 
                "Message" => "Application created!", 
                "LID" => filter_input(INPUT_POST, "lid", FILTER_SANITIZE_NUMBER_INT), 
                "ZID" => filter_input(INPUT_POST, "zid", FILTER_SANITIZE_NUMBER_INT), 
                "DID" => $did
            ];
        }

        public function updateDevice()
        {
            if(!filter_input(INPUT_POST, "id", FILTER_SANITIZE_NUMBER_INT)):
                return [
                    "Response"=> "Failed", 
                    "Message" => "ID is required"
                ];
            endif;
            
            if(!filter_input(INPUT_POST, "lid", FILTER_SANITIZE_NUMBER_INT)):
                return [
                    "Response"=> "Failed", 
                    "Message" => "Location ID is required"
                ];
            endif;
            
            if(!filter_input(INPUT_POST, "zid", FILTER_SANITIZE_NUMBER_INT)):
                return [
                    "Response"=> "Failed", 
                    "Message" => "Zone ID is required"
                ];
            endif;
            
            if(!filter_input(INPUT_POST, "ip", FILTER_SANITIZE_NUMBER_INT)):
                return [
                    "Response"=> "Failed", 
                    "Message" => "IP is required"
                ];
            endif;
            
            if(!filter_input(INPUT_POST, "mac", FILTER_SANITIZE_NUMBER_INT)):
                return [
                    "Response"=> "Failed", 
                    "Message" => "MAC is required"
                ];
            endif;
            
            if(!filter_input(INPUT_POST, "lt", FILTER_SANITIZE_NUMBER_INT)):
                return [
                    "Response"=> "Failed", 
                    "Message" => "Latitude is required"
                ];
            endif;
            
            if(!filter_input(INPUT_POST, "lg", FILTER_SANITIZE_NUMBER_INT)):
                return [
                    "Response"=> "Failed", 
                    "Message" => "Longitude is required"
                ];
            endif;

            if(!filter_input(INPUT_POST, "name", FILTER_SANITIZE_STRING)):
                return [
                    "Response"=> "Failed", 
                    "Message" => "Name is required"
                ];
            endif;

            $pdoQuery = $this->_GeniSys->_secCon->prepare("
                UPDATE mqttld
                SET name = :name,
                    lid = :lid,
                    zid = :zid,
                    ip = :ip,
                    mac = :mac,
                    lt = :lt,
                    lg = :lg
                WHERE id = :id 
            ");
            $pdoQuery->execute([
                ":name" => filter_input(INPUT_POST, "name", FILTER_SANITIZE_STRING),
                ":lid" => filter_input(INPUT_POST, "lid", FILTER_SANITIZE_STRING),
                ":zid" => filter_input(INPUT_POST, "zid", FILTER_SANITIZE_STRING),
                ":ip" => $this->_GeniSys->_helpers->oEncrypt(filter_input(INPUT_POST, "ip", FILTER_SANITIZE_STRING)),
                ":mac" => $this->_GeniSys->_helpers->oEncrypt(filter_input(INPUT_POST, "mac", FILTER_SANITIZE_STRING)),
                ":lt" => $this->_GeniSys->_helpers->oEncrypt(filter_input(INPUT_POST, "lt", FILTER_SANITIZE_STRING)),
                ":lg" => $this->_GeniSys->_helpers->oEncrypt(filter_input(INPUT_POST, "lg", FILTER_SANITIZE_STRING)),
                ":id" => filter_input(INPUT_POST, "id", FILTER_SANITIZE_NUMBER_INT)
            ]);
            $pdoQuery->closeCursor();
            $pdoQuery = null;
            return [
                "Response"=> "OK", 
                "Message" => "Device updated!"
            ];
        }

        public function getMDevices()
        {
            $pdoQuery = $this->_GeniSys->_secCon->prepare("
                SELECT *
                FROM mqttu
                ORDER BY id DESC
            ");
            $pdoQuery->execute();
            $response=$pdoQuery->fetchAll(PDO::FETCH_ASSOC);
            $pdoQuery->closeCursor();
            $pdoQuery = null;
            return $response;
        }

        public function getSensors($params = [])
        {
            $limit = "";

            if($params["limit"]):
                $limit = "LIMIT " . $params["limit"];
            endif;
            $pdoQuery = $this->_GeniSys->_secCon->prepare("
                SELECT *
                FROM sensors 
                ORDER BY id DESC
                $limit 
            ");
            $pdoQuery->execute();
            $response=$pdoQuery->fetchAll(PDO::FETCH_ASSOC);
            $pdoQuery->closeCursor();
            $pdoQuery = null;
            return $response;
        }

        public function getSensor($id)
        {
            $pdoQuery = $this->_GeniSys->_secCon->prepare("
                SELECT *
                FROM sensors 
                WHERE id = :id 
            ");
            $pdoQuery->execute([
                ":id" => $id
            ]);
            $response=$pdoQuery->fetch(PDO::FETCH_ASSOC);
            $pdoQuery->closeCursor();
            $pdoQuery = null;
            return $response;
        }

        public function createSensor()
        {
            
            if(!filter_input(INPUT_POST, "type", FILTER_SANITIZE_STRING)):
                return [
                    "Response"=> "Failed", 
                    "Message" => "Type is required"
                ];
            endif;

            if(!filter_input(INPUT_POST, "name", FILTER_SANITIZE_STRING)):
                return [
                    "Response"=> "Failed", 
                    "Message" => "Name is required"
                ];
            endif;
            
            $query = $this->_GeniSys->_secCon->prepare("
                INSERT INTO  sensors  (
                    `name`,
                    `type`
                )  VALUES (
                    :name,
                    :type
                )
            ");
            $query->execute([
                ':name' => filter_input(INPUT_POST, "name", FILTER_SANITIZE_STRING),
                ':type' => filter_input(INPUT_POST, "type", FILTER_SANITIZE_STRING)
            ]);
            $sensor = $this->_GeniSys->_secCon->lastInsertId();

            return [
                "Response"=> "OK", 
                "Message" => "Sensor created!", 
                "SID" => $sensor
            ];
        }

        public function updateSensor()
        {
            if(!filter_input(INPUT_POST, "id", FILTER_SANITIZE_NUMBER_INT)):
                return [
                    "Response"=> "Failed", 
                    "Message" => "ID is required"
                ];
            endif;
            
            if(!filter_input(INPUT_POST, "type", FILTER_SANITIZE_STRING)):
                return [
                    "Response"=> "Failed", 
                    "Message" => "Type is required"
                ];
            endif;

            if(!filter_input(INPUT_POST, "name", FILTER_SANITIZE_STRING)):
                return [
                    "Response"=> "Failed", 
                    "Message" => "Name is required"
                ];
            endif;

            $pdoQuery = $this->_GeniSys->_secCon->prepare("
                UPDATE sensors
                SET name = :name,
                    type = :type
                WHERE id = :id 
            ");
            $pdoQuery->execute([
                ":name" => filter_input(INPUT_POST, "name", FILTER_SANITIZE_STRING),
                ":type" => filter_input(INPUT_POST, "type", FILTER_SANITIZE_STRING),
                ":id" => filter_input(INPUT_POST, "id", FILTER_SANITIZE_NUMBER_INT)
            ]);
            $pdoQuery->closeCursor();
            $pdoQuery = null;
            return [
                "Response"=> "OK", 
                "Message" => "Sensor updated!"
            ];
        }

        public function getApplications()
        {
            $pdoQuery = $this->_GeniSys->_secCon->prepare("
                SELECT *
                FROM mqtta
                ORDER BY id DESC
            ");
            $pdoQuery->execute();
            $response=$pdoQuery->fetchAll(PDO::FETCH_ASSOC);
            $pdoQuery->closeCursor();
            $pdoQuery = null;
            return $response;
        }

        public function getApplication($id)
        {
            $pdoQuery = $this->_GeniSys->_secCon->prepare("
                SELECT *
                FROM mqtta
                WHERE id = :id 
            ");
            $pdoQuery->execute([
                ":id" => $id
            ]);
            $response=$pdoQuery->fetch(PDO::FETCH_ASSOC);
            $pdoQuery->closeCursor();
            $pdoQuery = null;
            return $response;
        }

        public function createApplication()
        {
            if(!filter_input(INPUT_POST, "name", FILTER_SANITIZE_STRING)):
                return [
                    "Response"=> "Failed", 
                    "Message" => "Name is required"
                ];
            endif;
            
            if(!filter_input(INPUT_POST, "lid", FILTER_SANITIZE_NUMBER_INT)):
                return [
                    "Response"=> "Failed", 
                    "Message" => "Location ID is required"
                ];
            endif;

            if(!filter_input(INPUT_POST, "ip", FILTER_SANITIZE_STRING)):
                return [
                    "Response"=> "Failed", 
                    "Message" => "Location IP is required"
                ];
            endif;
            if(!filter_input(INPUT_POST, "mac", FILTER_SANITIZE_STRING)):
                return [
                    "Response"=> "Failed", 
                    "Message" => "Location MAC is required"
                ];
            endif;
            if(!filter_input(INPUT_POST, "lt", FILTER_SANITIZE_STRING)):
                return [
                    "Response"=> "Failed", 
                    "Message" => "Location latitude is required"
                ];
            endif;
            if(!filter_input(INPUT_POST, "lg", FILTER_SANITIZE_STRING)):
                return [
                    "Response"=> "Failed", 
                    "Message" => "Location longitude is required"
                ];
            endif;

            $mqttUser = $this->_GeniSys->_helpers->generateKey(12);
            $mqttPass = $this->_GeniSys->_helpers->password();
            $mqttHash = create_hash($mqttPass);
    
            $apiKey = $this->_GeniSys->_helpers->generateKey(30);
            $apiSecretKey = $this->_GeniSys->_helpers->generateKey(35);
            
            $query = $this->_GeniSys->_secCon->prepare("
                INSERT INTO  mqtta  (
                    `lid`,
                    `name`,
                    `mqttu`,
                    `mqttp`,
                    `apub`,
                    `aprv`,
                    `ip`,
                    `mac`,
                    `lt`,
                    `lg`,
                    `time`
                )  VALUES (
                    :lid,
                    :name,
                    :mqttu,
                    :mqttp,
                    :apub,
                    :aprv,
                    :ip,
                    :mac,
                    :lt,
                    :lg,
                    :time
                )
            ");
            $query->execute([
                ':lid' => filter_input(INPUT_POST, "lid", FILTER_SANITIZE_NUMBER_INT),
                ':name' => filter_input(INPUT_POST, "name", FILTER_SANITIZE_STRING),
                ':mqttu' =>$this->_GeniSys->_helpers->oEncrypt($mqttUser),
                ':mqttp' =>$this->_GeniSys->_helpers->oEncrypt($mqttPass),
                ':apub' => $this->_GeniSys->_helpers->oEncrypt($apiKey),
                ':aprv' => $this->_GeniSys->_helpers->oEncrypt($apiSecretKey),
                ':ip' => $this->_GeniSys->_helpers->oEncrypt(filter_input(INPUT_POST, "ip", FILTER_SANITIZE_STRING)),
                ':mac' => $this->_GeniSys->_helpers->oEncrypt(filter_input(INPUT_POST, "mac", FILTER_SANITIZE_STRING)),
                ':lt' => $this->_GeniSys->_helpers->oEncrypt(filter_input(INPUT_POST, "lt", FILTER_SANITIZE_STRING)),
                ':lg' => $this->_GeniSys->_helpers->oEncrypt(filter_input(INPUT_POST, "lg", FILTER_SANITIZE_STRING)),
                ':time' => time()
            ]);
            $aid = $this->_GeniSys->_secCon->lastInsertId();
    
            $query = $this->_GeniSys->_secCon->prepare("
                INSERT INTO  mqttu  (
                    `lid`,
                    `aid`,
                    `uname`,
                    `pw`
                )  VALUES (
                    :lid,
                    :aid,
                    :uname,
                    :pw
                )
            ");
            $query->execute([
                ':lid' => filter_input(INPUT_POST, "lid", FILTER_SANITIZE_NUMBER_INT),
                ':aid' => $aid,
                ':uname' => $mqttUser,
                ':pw' => $mqttHash
            ]);

            $query = $this->_GeniSys->_secCon->prepare("
                INSERT INTO  mqttua  (
                    `lid`,
                    `aid`,
                    `username`,
                    `topic`,
                    `rw`
                )  VALUES (
                    :lid,
                    :aid,
                    :username,
                    :topic,
                    :rw
                )
            ");
            $query->execute(array(
                ':lid' => filter_input(INPUT_POST, "lid", FILTER_SANITIZE_NUMBER_INT),
                ':aid' => $aid,
                ':username' => $mqttUser,
                ':topic' => filter_input(INPUT_POST, "lid", FILTER_SANITIZE_NUMBER_INT)."/Devices/#",
                ':rw' => 4
            ));
    
            $query = $this->_GeniSys->_secCon->prepare("
                INSERT INTO  mqttua  (
                    `lid`,
                    `aid`,
                    `username`,
                    `topic`,
                    `rw`
                )  VALUES (
                    :lid,
                    :aid,
                    :username,
                    :topic,
                    :rw
                )
            ");
            $query->execute(array(
                ':lid' => filter_input(INPUT_POST, "lid", FILTER_SANITIZE_NUMBER_INT),
                ':aid' => $aid,
                ':username' => $mqttUser,
                ':topic' => filter_input(INPUT_POST, "lid", FILTER_SANITIZE_NUMBER_INT)."/Applications/#",
                ':rw' => 2
            ));
    
            $query = $this->_GeniSys->_secCon->prepare("
                UPDATE mqttl
                SET apps = apps + 1
                WHERE id = :id
            ");
            $query->execute(array(
                ':id'=>filter_input(INPUT_POST, "lid", FILTER_SANITIZE_NUMBER_INT)
            ));

            return [
                "Response"=> "OK", 
                "Message" => "Application created!", 
                "LID" => filter_input(INPUT_POST, "lid", FILTER_SANITIZE_NUMBER_INT), 
                "AID" => $aid
            ];
        }

        public function updateApplication()
        {
            if(!filter_input(INPUT_POST, "id", FILTER_SANITIZE_NUMBER_INT)):
                return [
                    "Response"=> "Failed", 
                    "Message" => "ID is required"
                ];
            endif;
            if(!filter_input(INPUT_POST, "name", FILTER_SANITIZE_STRING)):
                return [
                    "Response"=> "Failed", 
                    "Message" => "Name is required"
                ];
            endif;
            if(!filter_input(INPUT_POST, "ip", FILTER_SANITIZE_STRING)):
                return [
                    "Response"=> "Failed", 
                    "Message" => "Location IP is required"
                ];
            endif;
            if(!filter_input(INPUT_POST, "mac", FILTER_SANITIZE_STRING)):
                return [
                    "Response"=> "Failed", 
                    "Message" => "Location MAC is required"
                ];
            endif;
            if(!filter_input(INPUT_POST, "lt", FILTER_SANITIZE_STRING)):
                return [
                    "Response"=> "Failed", 
                    "Message" => "Location latitude is required"
                ];
            endif;
            if(!filter_input(INPUT_POST, "lg", FILTER_SANITIZE_STRING)):
                return [
                    "Response"=> "Failed", 
                    "Message" => "Location longitude is required"
                ];
            endif;

            $pdoQuery = $this->_GeniSys->_secCon->prepare("
                UPDATE mqtta
                SET name = :name,
                    lg = :lg, 
                    lt = :lt, 
                    ip = :ip, 
                    mac = :mac
                WHERE id = :id 
            ");
            $pdoQuery->execute([
                ":name" => filter_input(INPUT_POST, "name", FILTER_SANITIZE_STRING),
                ":lg" => $this->_GeniSys->_helpers->oEncrypt(filter_input(INPUT_POST, "lg", FILTER_SANITIZE_STRING)),
                ":lt" => $this->_GeniSys->_helpers->oEncrypt(filter_input(INPUT_POST, "lt", FILTER_SANITIZE_STRING)),
                ":ip" => $this->_GeniSys->_helpers->oEncrypt(filter_input(INPUT_POST, "ip", FILTER_SANITIZE_STRING)),
                ":mac" => $this->_GeniSys->_helpers->oEncrypt(filter_input(INPUT_POST, "mac", FILTER_SANITIZE_STRING)),
                ":id" => filter_input(INPUT_POST, "id", FILTER_SANITIZE_NUMBER_INT)
            ]);
            $pdoQuery->closeCursor();
            $pdoQuery = null;
            return [
                "Response"=> "OK", 
                "Message" => "Application updated!"
            ];
        }

        public function resetAppMqtt()
        {
            $pdoQuery = $this->_GeniSys->_secCon->prepare("
                SELECT uid,
                    uname
                FROM mqttu
                WHERE aid = :aid
            ");
            $pdoQuery->execute([
                ":aid" => filter_input(INPUT_POST, "id", FILTER_SANITIZE_NUMBER_INT)
            ]);
            $mqtt=$pdoQuery->fetch(PDO::FETCH_ASSOC);
            $pdoQuery->closeCursor();
            $pdoQuery = null;

            $mqttPass = $this->_GeniSys->_helpers->password();
            $mqttHash = create_hash($mqttPass);
    
            $query = $this->_GeniSys->_secCon->prepare("
                UPDATE mqtta
                SET mqttp = :mqttp 
                WHERE id = :id
            ");
            $query->execute(array(
                ':mqttp' => $this->_GeniSys->_helpers->oEncrypt($mqttPass),
                ':id' => filter_input(INPUT_POST, "id", FILTER_SANITIZE_NUMBER_INT)
            ));
    
            $query = $this->_GeniSys->_secCon->prepare("
                UPDATE mqttu
                SET pw = :pw 
                WHERE aid = :aid
            ");
            $query->execute(array(
                ':pw' => $mqttHash,
                ':aid' => filter_input(INPUT_POST, "id", FILTER_SANITIZE_NUMBER_INT)
            ));

            return [
                "Response"=> "OK", 
                "Message" => "MQTT password reset!", 
                "P" => $mqttPass
            ];

        }


    }
    
    $iotJumpWay = new iotJumpWay($_GeniSys);

    if(filter_input(INPUT_POST, "update_location", FILTER_SANITIZE_NUMBER_INT)):
        die(json_encode($iotJumpWay->update()));
    endif;

    if(filter_input(INPUT_POST, "create_zone", FILTER_SANITIZE_NUMBER_INT)):
        die(json_encode($iotJumpWay->createZone()));
    endif;

    if(filter_input(INPUT_POST, "update_zone", FILTER_SANITIZE_NUMBER_INT)):
        die(json_encode($iotJumpWay->updateZone()));
    endif;

    if(filter_input(INPUT_POST, "create_device", FILTER_SANITIZE_NUMBER_INT)):
        die(json_encode($iotJumpWay->createDevice()));
    endif;

    if(filter_input(INPUT_POST, "update_device", FILTER_SANITIZE_NUMBER_INT)):
        die(json_encode($iotJumpWay->updateDevice()));
    endif;

    if(filter_input(INPUT_POST, "create_sensor", FILTER_SANITIZE_NUMBER_INT)):
        die(json_encode($iotJumpWay->createSensor()));
    endif;

    if(filter_input(INPUT_POST, "update_sensor", FILTER_SANITIZE_NUMBER_INT)):
        die(json_encode($iotJumpWay->updateSensor()));
    endif;

    if(filter_input(INPUT_POST, "create_application", FILTER_SANITIZE_NUMBER_INT)):
        die(json_encode($iotJumpWay->createApplication()));
    endif;

    if(filter_input(INPUT_POST, "update_application", FILTER_SANITIZE_NUMBER_INT)):
        die(json_encode($iotJumpWay->updateApplication()));
    endif;

    if(filter_input(INPUT_POST, "update_application", FILTER_SANITIZE_NUMBER_INT)):
        die(json_encode($iotJumpWay->updateApplication()));
    endif;

    if(filter_input(INPUT_POST, "reset_mqtt_app", FILTER_SANITIZE_NUMBER_INT)):
        die(json_encode($iotJumpWay->resetAppMqtt()));
    endif;
    