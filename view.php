<?php require_once('includes/header.php'); 

        if (isset($_GET["secret"]) && preg_match('/^[0-9A-F]{32}$/i', $_GET["secret"])) {
            $secretToken = htmlspecialchars($_GET["secret"]);
            if (secretExist($secretToken)){			
                echo '
				<form id="mainForm" action="view.php" method="POST">
				<div id="container-fluid">
					<div class="row justify-content-md-center">
						<div class="col-md-6 col-sm-12">
							<div class="form-group">
								<label><strong>Your Secure Message is ready to be viewed, remember the Secure Message is only viewable once and destroyed.<br>
								Please ensure that you read it before navigating away from this page.</strong></label>
								<input type="hidden" name="token" value="' . $secretToken . '" readonly="readonly" />
							</div>
						</div/>
					</div>
                ';
                if (isSecretProtected($secretToken)) {
                    echo '
                    <div class="row justify-content-md-center">
						<div class="col-md-6 col-sm-12">
							<div class="form-group">
								<label>The Secure Message is password protected, please provide the password.</label>
								<input class="form-control" required placeholder="Password" type="password" name="password" value=""/>
							</div>
						</div>
                    </div>
                    ';
                }
                echo '
					<div class="row justify-content-md-center">
						<div class="col-md-3 col-sm-12">
							<div class="form-group">
								<button class="form-control btn btn-success btn-lg" type="submit" name="showSecret" value="showSecret" onclick="alert(\'\nSECURE MESSAGE WILL BE SHOWN.\n\nPlease ensure that you read it before navigating away from this page.\n\');">View Secure Message</button>
							</div>
						</div>
					</div>
                </form>';
            } else {
                echo '
				<form action="index.php" method="POST">
					<div class="row justify-content-md-center">
						<div class="col-md-3 col-sm-6">
							<div class="form-group">
								<label><strong>Secure Message Expired</strong></label>
							</div>
						</div>
					</div>
					<div class="row justify-content-md-center">
						<div class="col-md-3 col-sm-12">
							<div class="form-group">
								<button type="submit" class="form-control btn btn-primary btn-lg" name="submit" value="submit">Create New Secure Message</button>
							</div>
						</div>
					</div>
                  </form>
                  </div>
                ';
            }
        } elseif (isset($_POST["showSecret"])) {
            $secretToken = htmlspecialchars($_POST["token"]);
            if (secretExist($secretToken)) {
                if (isSecretProtected($secretToken)) {
                    $password = htmlspecialchars($_POST["password"]);
					sendNotify($secretToken);
                    $secretData = showSecret($secretToken, $password, true);
                    echo '
						<div id="container-fluid">
						<form action="index.php" method="POST">
							<div class="row justify-content-md-center">
								<div class="col-md-10 col-sm-12">
									<label><strong>Secure Message</strong></label>
									<textarea class="form-control auto-size" readonly value="'.$secretData.'" tabindex="1">'.$secretData.'</textarea>
								</div>
							</div>
							<div class="row justify-content-md-center">
								<div class="col-4">
									&nbsp;
								</div>
							</div>
							<div class="row justify-content-md-center">
								<div class="col-md-3 col-sm-12">
									<div class="form-group">
										<button type="submit" class="form-control btn btn-primary btn-lg" name="submit" value="submit">Create New Secure Message</button>
									</div>
								</div>
							</div>
                      </form>
					  <script>$(\'textarea.auto-size\').textareaAutoSize();</script>
                    ';
                } else {
					sendNotify($secretToken);
                    $secretData = showSecret($secretToken, "", false);
                   echo '
                      <div id="container-fluid">
						<form action="index.php" method="POST">
							<div class="row justify-content-md-center">
								<div class="col-md-10 col-sm-12">
									<label><strong>Secure Message</strong></label>
									<textarea class="form-control auto-size" readonly value="'.$secretData.'" tabindex="1">'.$secretData.'</textarea>
								</div>
							</div>
							<div class="row justify-content-md-center">
								<div class="col-4">
									&nbsp;
								</div>
							</div>
							<div class="row justify-content-md-center">
								<div class="col-md-3 col-sm-12">
									<div class="form-group">
										<button type="submit" class="form-control btn btn-primary btn-lg" name="submit" value="submit">Create New Secure Message</button>
									</div>
								</div>
							</div>
                      </form>
					  <script>$(\'textarea.auto-size\').textareaAutoSize();</script>
                    ';
                }
            } else {
                echo '
				<div id="container-fluid">
					<form action="index.php" method="POST">
					<div class="row justify-content-md-center">
						<div class="col-md-3 col-sm-6">
							<div class="form-group">
								<label><strong>Invalid Secure URL</strong></label>
							</div>
						</div>
					</div>
					<div class="row justify-content-md-center">
						<div class="col-md-3 col-sm-12">
							<div class="form-group">
								<button type="submit" class="form-control btn btn-primary btn-lg" name="submit" value="submit">Create New Secure Message</button>
							</div>
						</div>
					</div>
                  </form>
                  </div>
                ';
            }
        } else {
            header('Location:' . $configArray["appUrl"]);
            die();
        }
        ?>
    </body>
</html>
