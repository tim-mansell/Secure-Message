<?php

/*
 * Date: 10/12/2014
 */

require_once('includes\config.php');
require_once('libs\PHPMailerAutoload.php');

# Desc: Create the connection link of the database
# Input: None
# Output: Database connection link
function dbConnect() {
	GLOBAL $configArray;
    $dbConnection = new PDO('mysql:dbname=' . $configArray["dbName"] . ';host=' . $configArray["dbHost"] .';charset=utf8', $configArray["dbUser"], $configArray["dbPassword"]);
    return $dbConnection;
}

# Desc: Generate the secret's token to be shared
# Input: none
# Output: Token ready to be used
function generateToken() {
    $freeToken = false;
    $dbLink = dbConnect();
    while ($freeToken == false) {
        $bytes = openssl_random_pseudo_bytes(16);
        $token = bin2hex($bytes);
        $query = $dbLink->prepare('SELECT token from tokens WHERE token = ?');
        $array = array("$token");
        $query->execute($array);
        if ($query->rowCount() < 1) {
            $freeToken = true;
            $query = $dbLink->prepare('INSERT INTO tokens (token) VALUES (?)');
            $array = array("$token");
            $query->execute($array);
        }
    }
    return $token;
}

# Desc: Hashes plain text passwords
# Input: Plain text password
# Output: Hashed password
function passwordCipher($passwordString) {
    $hashedPassword = password_hash($passwordString, PASSWORD_BCRYPT);
    return $hashedPassword;
}

# Desc: Crypt/Decrypt input string
# Input: Operation: crypt|decrypt and String being procesed
# Output: text
function cryptFunction($operation,$string){ 
	GLOBAL $configArray;
   $cryptKey = $configArray["cryptKey"];
   $key = hash('SHA256', $cryptKey, true);
   $ivSize = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC);
   $iv = mcrypt_create_iv($ivSize, MCRYPT_RAND);
   if ($operation == "decrypt")
   {
       $encryptedText = base64_decode($string);
       $ivDec = substr($encryptedText , 0, $ivSize);
       $encryptedText = substr($encryptedText, $ivSize);
       $plainText = mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $key, $encryptedText, MCRYPT_MODE_CBC, $ivDec);
       $returnText = $plainText;
   }
   else
   {
       $encryptedText = mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $key, $string, MCRYPT_MODE_CBC, $iv);
       $encryptedText = $iv . $encryptedText;
       $returnText = base64_encode($encryptedText);
   }
   return $returnText;
}

# Desc: Compares two hashed passwords and check if both match
# Input: Two hashed passwords
# Output: true if both passwords matches false if not
function checkPassword($userPassword, $dataPassword) {
    $result = false;
    if ($userPassword === $dataPassword) {
        $result = true;
    }
    return $result;
}

# Desc: Write statistic information (secrets created / secrets viewed)
# Input: Operation (view || create), if is password protected
# Output: None
function writeStat($operation, $isPasswordProtected) {
    $dbLink = dbConnect();
    $query = $dbLink->prepare('INSERT INTO stats (passwordProtected,operation) VALUES (?,?)');
    $array = array("$isPasswordProtected","$operation");
    $query->execute($array);
}

# Desc: Create the secret in the DB
# Input: Secret data, password (if any), time to live (if nobody views the secret)
# Output: Secret's token
function createSecret($secret, $password, $timetolive, $notify=NULL) {
    $isPasswordProtected = false;
    if ($password) {
        $isPasswordProtected = true;
        $hashedPassword = passwordCipher($password);
    } else {
        $hashedPassword = NULL;
    }
    $token = generateToken();
    $secretHashed = cryptFunction("crypt",$secret);
    # Set timetolive
    $currentDate = date("Y-m-d H:i:s");
    $time = new DateTime($currentDate);
    $time->add(new DateInterval('PT' . $timetolive . 'M'));
    $finalTimeToLive = $time->format('Y-m-d H:i:s');
    # End
    $dbLink = dbConnect();
    $query = $dbLink->prepare('INSERT INTO data (token,secret,password,timetolive,status) VALUES (?,?,?,?,?)');
    $array = array("$token","$secretHashed","$hashedPassword","$finalTimeToLive","0");
    $query->execute($array);
	if(filter_var($notify, FILTER_VALIDATE_EMAIL)){
		$query = $dbLink->prepare('INSERT INTO notify (token,email) VALUES (?,?)');
		$array = array("$token","$notify");
		$query->execute($array);
	}
    writeStat("create", $isPasswordProtected);
    return $token;
}

# Desc: Shows the secret data
# Input: Secret's Token, password (if any), password protected (true,false)
# Output: None
function showSecret($token, $password, $passwordProtected) {
    $dbLink = dbConnect();
    $query = $dbLink->prepare('SELECT secret,password FROM data WHERE token = ?');
    $array = array("$token");
    $query->execute($array);
    $secretData = $query->fetch();
    $returnText="null";
    if ($passwordProtected) {
        if (password_verify($password, $secretData["password"])) {
            $plainSecret = cryptFunction("decrypt",$secretData["secret"]);
            $returnText = $plainSecret;
            removeSecret($token);
            writeStat("view", $passwordProtected);
        } else {
            $returnText = "Wrong Password. <br>You will be redirected in 2 seconds";
            header('Refresh: 1;' . $_SERVER['HTTP_REFERER']);
        }
    } else {
        $plainSecret = cryptFunction("decrypt",$secretData["secret"]);
        $returnText = $plainSecret;
        removeSecret($token);
        writeStat("view", $passwordProtected);
    }
    $returnText = preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $returnText);
    $returnText = preg_replace('/\<br(\s+)?\/?\>/i', "\n", $returnText);
    return $returnText;
}

# Desc: Check if a given secret exist in the database
# Input: Token
# Output: Secret exist (true) or not (false)
function secretExist($token) {
    $result = true;
    $dbLink = dbConnect();
    $query = $dbLink->prepare('SELECT token from data WHERE token = ? AND status = ?');
    $array = array("$token","0");
    $query->execute($array);
    if ($query->rowCount() < 1) {
        $result = false;
    }
    return $result;
}

# Desc: Check if a given secret is password protected
# Input: Token
# Output: Secret is protected by password (true) or not (false)
function isSecretProtected($token) {
    $result = false;
    $dbLink = dbConnect();
    $query = $dbLink->prepare('SELECT password FROM data WHERE token = ?');
    $array = array("$token");
    $query->execute($array);
    $queryResult = $query->fetch();
    if (strlen($queryResult["password"]) > 0) {
        $result = true;
    }
    return $result;
}

# Desc: Remove a secret from the database
# Input: Token/Data to be removed
# Output: None
function removeSecret($token) {
    $dbLink = dbConnect();
    $query = $dbLink->prepare('UPDATE data set status = ? WHERE token = ?');
    $array = array("1","$token");
    $query->execute($array);
    $query = $dbLink->prepare('DELETE FROM data WHERE token = ?');
    $array = array("$token");
    $query->execute($array);
    $query = $dbLink->prepare('DELETE from tokens WHERE token = ?');
    $array = array("$token");
    $query->execute($array);
	$query = $dbLink->prepare('DELETE from notify WHERE token = ?');
    $array = array("$token");
    $query->execute($array);
}

// DELETE EXPIRED TOKENS
function deleteExpired(){
	$dbLink = dbConnect();
	$query = $dbLink->prepare("SELECT * FROM data WHERE timetolive < NOW()");
	$query->execute();
	$expiredTokens = $query->fetchAll(PDO::FETCH_ASSOC);
		foreach($expiredTokens as $secret){
			removeSecret($secret['token']);
		}
}
deleteExpired();

function sendMail($recipient,$sharingUrl,$sharingPassword,$friendlyName=NULL,$ttl=NULL) {
	GLOBAL $configArray;
    $mail = new PHPMailer;
    $mail->isSMTP();
    $mail->Host = $configArray["smtpHost"];
    $configArray["smtpAuth"] ? $mail->SMTPAuth = $configArray["smtpAuth"] : "";
    $configArray["smtpUser"] ? $mail->Username = $configArray["smtpUser"] : ""; 
    $configArray["smtpPassword"] = $mail->Password = $configArray["smtpPassword"] : "";
    $configArray["smtpSecure"] ? $mail->SMTPSecure = $configArray["smtpSecure"]: "";
    $mail->Port = $configArray["smtpPort"];
    $mail->From = $configArray["smtpFromAddress"];
    $mail->FromName = $configArray["smtpFromName"];
    $mail->addAddress("$recipient");
    $mail->isHTML(true);
    $mail->Subject = $configArray["mailSubject"];
    $message = $configArray["mailMessage"];
	$message = (isset($friendlyName)) ? str_replace('Someone',$friendlyName,$message) : $message;
    $message .= "<p><a href=\"$sharingUrl\">$sharingUrl</a></p>";
    if (strlen($sharingPassword)>0)
    {
        $message .= $configArray["passwordMessage"];
        $message .= "$sharingPassword";
    }
	if($ttl){
		$message = $message."<p>The Secure Message is set to expire at ".$ttl." and will be destroyed.";
	}
	$message = $message.$configArray["endofMessage"];
    $mail->Body = "$message";
    if(!$mail->send()) {
        $result = 1; 
    }
    else {
        $result = 0;
    }
    return $result;
}

function sendNotify($token){
	$dbLink = dbConnect();
    $query = $dbLink->prepare('SELECT email FROM notify WHERE token = ?');
    $array = array("$token");
    $query->execute($array);
    $queryResult = $query->fetch();
    if (strlen($queryResult["email"]) > 0) {
		GLOBAL $configArray;
		$mail = new PHPMailer;
		$mail->isSMTP();
        $mail->Host = $configArray["smtpHost"];
        $configArray["smtpAuth"] ? $mail->SMTPAuth = $configArray["smtpAuth"] : "";
        $configArray["smtpUser"] ? $mail->Username = $configArray["smtpUser"] : ""; 
        $configArray["smtpPassword"] = $mail->Password = $configArray["smtpPassword"] : "";
        $configArray["smtpSecure"] ? $mail->SMTPSecure = $configArray["smtpSecure"]: "";
		$mail->Port = $configArray["smtpPort"];
		$mail->From = $configArray["smtpFromAddress"];
		$mail->FromName = $configArray["smtpFromName"];
		$mail->addAddress($queryResult["email"]);
		$mail->isHTML(true);
		$mail->Subject = $configArray["notifySubject"];
		$message = $configArray["notifyMessage"];
		$mail->Body = "$message";
		if(!$mail->send()) {
			$result = 1; 
		}
		else {
			$result = 0;
		}
		return $result;
    }
}
?>
