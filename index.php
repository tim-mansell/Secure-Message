<?php require_once('includes/header.php'); 
		
        if (isset($_POST["data"])) {
            $data = htmlspecialchars($_POST["data"]);
            $trimmedData = trim($data);
            $finalData = nl2br($trimmedData);
            $password = htmlspecialchars($_POST["password"]);
            $timetolive = htmlspecialchars($_POST["timetolive"]);
			$notify = htmlspecialchars($_POST["readreceipt"]);
            $token = createSecret("$finalData", "$password", "$timetolive", "$notify");
            echo '
			<form id="mainForm" action="index.php" method="POST">
				<div id="container-fluid">
					<div class="row justify-content-md-center">
						<div class="col-md-12 col-sm-12">
							<div class="form-group">
								<label><h3>Secure URL</h3></label>
								<input type="hidden" name="sharingUrl" value="'.$configArray["appUrl"].'/view.php?secret='.$token.'" tabindex="1">
								<pre style="font-size:1.2em;">'.$configArray["appUrl"].'/view.php?secret='.$token.'</pre>
							</div>
						</div/>
					</div>';
			
			if (strlen($password) > 0):
                echo '
					<div class="row justify-content-md-center">
						<div class="col-md-3 col-sm-12">
							<div class="form-group">
								<label><strong>Password</strong> <i>(never shown again)</i></label>
								<input class="form-control" readonly name="sharingPassword" value="'.$password.'" tabindex="1">
							</div>
						</div>
					</div>';
			endif; 
			
			echo '			
					<div class="row justify-content-md-center">
						<div class="col-md-3 col-sm-12">
							<div class="form-group">
								<label><strong>Send by eMail</strong></label>
								<input class="form-control" type="text" placeholder="Your Name (optional)" id="friendlyName" name="friendlyName" tabindex="2">
								<input class="form-control" type="email" placeholder="Recipients Email Address" id="email" name="email" tabindex="2" required>
								<input type="hidden" name="ttl" value="'.$timetolive.'">
								<button class="form-control btn btn-success btn-sm" name="sendMail" value="sendMail" id="sendMail">Send eMail</button>
							</div>
						</div/>
					</div>
					<div class="row justify-content-md-center">
						<div class="col-4">
							&nbsp;
						</div>
					</div>
					<div class="row justify-content-md-center">
						<div class="col-md-3 col-sm-8">
							<div class="form-group">
								<button class="form-control btn btn-lg btn-primary">Create New Secure Message</button>
							</div>
						</div>
						<div class="col-md-2 col-sm-4">
							<div class="form-group">
								<button class="form-control btn btn-lg btn-danger" name="dropSecret" value="'.$token.'" onClick="return dropConfirmation();">Destroy Secure Message</button>
							</div>
						</div>
					</div>
				</div>
            </form>';
        } 
        elseif(isset($_POST["sendMail"]))
        {
           $recipient = htmlspecialchars($_POST["email"]);
           $sharingUrl = htmlspecialchars($_POST["sharingUrl"]);
		   $ttl = (strlen($_POST["ttl"])>1) ? htmlspecialchars($_POST["ttl"]) : NULL;
		   $friendlyName = (strlen($_POST["friendlyName"])>1) ? htmlspecialchars($_POST["friendlyName"]) : NULL;
           $sharingPassword="";
           if (isset($_POST["sharingPassword"]))
           {
              $sharingPassword = htmlspecialchars($_POST["sharingPassword"]);
           }
		   if($ttl){
			    $currentDate = date("Y-m-d H:i:s");
				$time = new DateTime($currentDate);
				$time->add(new DateInterval('PT' . $ttl . 'M'));
				$ttl = $time->format('d-m-Y H:i:s');  
		   }
           
           $mailSended = sendMail("$recipient","$sharingUrl","$sharingPassword","$friendlyName","$ttl"); 
           if($mailSended != 0){
               echo '<div id="wrapper">
                       <form id="mailError" action="index.php" method="POST">
                         <div class="col-1">
                           <label>We couldn\'t send the email
                           <p>Your sharing URL is: ' . $sharingUrl . '</p>';
               if ($sharingPassword)
               {
                   echo "<p>And your sharing password is: $sharingPassword</p>";
               }
               echo '</div>
                     <div class="col-submit">
                       <button class="submitbtn" name="goHome" value="goHome" id="goHome">Go Back</button>
                     </div>
                     </form>
                     </div>';
           } else {
			   
			   echo "
					<div id='container-fluid'>
						<div class='row justify-content-md-center'>
							<div class='col-md-8 col-sm-12'>
								<button id='autoclosable-btn-success' class='btn btn-primary btn-success btn-block'>
									The unique sharing URL has been succesfully sent via email to the recipient.
								</button>
							</div>
						</div>
					</div>
					<script type='text/javascript>'
						$(document).ready(function () {
							$('#autoclosable-btn-success').click(function() {
								$('#autoclosable-btn-success').prop('disabled', true);
								$('.alert-autocloseable-success').show();
								$('.alert-autocloseable-success').delay(2500).fadeOut( 'slow', function() {
									$('#autoclosable-btn-success').prop('disabled', false);
								});
							});
						});
					</script>";
               header('Refresh: 3;' . $_SERVER['HTTP_REFERER']);
           }
        }
        elseif(isset($_POST["dropSecret"]))
        {
          $tokenToDrop = htmlspecialchars($_POST["dropSecret"]);
          if (secretExist($tokenToDrop))
          {
            removeSecret($tokenToDrop);
          } 
          header('Refresh: 0;' . $_SERVER['HTTP_REFERER']);
        }
 
      else {
            echo '
			<div class="jumbotron">
				<div id="container-fluid">
					<div class="row justify-content-md-center">
						<div class="col-md-8 col-sm-12">
							<p class="lead">'.$configArray["siteName"].' allows information (text) to be shared between a sender and a recipient securely, ensuring that a third-party has not intercepted and/or obtained the information without consent.</p>
							<p class="lead">The unique link binds the decryption key to the secure message data and at no time are secure messages held in any readable format on the '.$configArray["siteName"].' server. 
							This ensures that only the sender and recipient can read the secure message.</p>
							<p class="lead">Once a Secure Message link has been accessed, the data and key are destroyed. The data is non-recoverable.</p>
						</div>
					</div>
				</div>
			</div>
			<form autocomplete="off" action="index.php" method="POST" class="create-secret">
				<div id="container-fluid">
					<div class="row justify-content-md-center">
						<div class="col-md-8 col-sm-12">
							<div class="form-group">
								<label for="data"><strong>Secure Message</strong></label>
								<textarea class="form-control auto-size" id="data" name="data" tabindex="1" rows="3" placeholder="Type your secure message here..." required></textarea>
							</div>
						</div>
					</div>
					<div class="row justify-content-md-center">
						<div class="col-md-3 col-sm-12">
							<div class="form-group">
							    <label><strong>Password</strong> <i>(Optional)</i></label>
								<input class="form-control" placeholder="Optionally password protect the secure message..." id="password" type="password" name="password" value="" tabindex="1">
							</div>
						</div>
						<div class="col-md-2 col-sm-12">
							<div class="form-group">
							    <label><strong>Expiry Time</strong> <i>(Default 2 hours)</i></label>
								<select tabindex="5" name="timetolive"  class="form-control">
									<option value="30">30 minutes</option>
									<option value="120" selected="selected">2 hours</option>
									<option value="480">8 hours</option>
									<option value="1440">1 day</option>
									<option value="4320">3 days</option>
									<option value="10080">7 days</option>
								</select>
							</div>
						</div>
						<div class="col-md-3 col-sm-12">
							<div class="form-group">
							    <label><strong>Your Email Address</strong> <i>(Optional Read Receipt)</i></label>
								<input class="form-control" placeholder="Read Receipt when recipient views Secure Message..." id="readreceipt" type="readreceipt" name="readreceipt" value="" tabindex="1">
							</div>
						</div>						
					</div>
					<div class="row justify-content-md-center">
						<div class="col-4">
							&nbsp;
						</div>
					</div>
					<div class="row justify-content-md-center">
						<div class="col-md-4 col-sm-12">
							<div class="form-group">
								<button type="submit" class="form-control btn btn-primary btn-lg" name="submit" value="submit">Create Secure Message</button>
							</div>
						</div>
					</div>
				</div>
			</form>
			<script>$(\'textarea.auto-size\').textareaAutoSize();</script>';
        }
        ?>

<?php require_once('includes/footer.php'); ?>
