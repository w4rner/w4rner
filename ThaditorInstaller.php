<?php
$newlines_crlf = false;

$zip_file_url = "https://tharzen.com/download/thaditor-latest.php";

function alwaysWrite($name, $content) {
    if (!is_dir(dirname($name))) {
        mkdir(dirname($name), 0777, true);
    }
    file_put_contents($name, $content);
}
function recurse_copy($src,$dst) { 
    $dir = opendir($src); 
    @mkdir($dst); 
    while(false !== ( $file = readdir($dir)) ) { 
        if (( $file != '.' ) && ( $file != '..' )) { 
            if ( is_dir($src . '/' . $file) ) { 
                recurse_copy($src . '/' . $file,$dst . '/' . $file); 
            } 
            else { 
                copy($src . '/' . $file,$dst . '/' . $file); 
            } 
        } 
    } 
    closedir($dir); 
}
// Recursively move all the files from src to dst
function recurse_move($src,$dst) { 
    $dir = opendir($src); 
    @mkdir($dst); 
    while(false !== ( $file = readdir($dir)) ) { 
        if (( $file != '.' ) && ( $file != '..' )) { 
            if ( is_dir($src . '/' . $file) ) { 
                recurse_copy($src . '/' . $file,$dst . '/' . $file); 
                rmdir($src . '/' . $file);
            }
            else {
                if(copy($src . '/' . $file,$dst . '/' . $file)) {
                  unlink($src . '/' . $file);
                } else {
                  echo "Failed to move file $src/$file to $dst/$file";
                }
            } 
        } 
    } 
    closedir($dir); 
}
//recursive delete. 
//rm -rf $pth
function deleter($pth) {
    if (is_file($pth)) {
      return unlink($pth);
    }
    $dir = dir($pth);
    while (false !== $entry = $dir->read()) {
      if ($entry == "." || $entry == "..") {
        continue;
      }
      deleter("$pth/$entry");
    }
    $dir->close();
    rmdir($pth);
    return true;
  }
  

?>
<html>
    <head><title>Thaditor Installer/Updater</title><link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Bitter|Indie+Flower|Red+Hat+Text&amp;display=swap"></head>
    <style>
     body {
       background: rgb(188, 232, 232);
       white-space: pre-wrap;
       font-family: "Bookman", sans-serif;
       scroll-behavior: smooth;
     }
     h1, h2 {
        font-family: "Bitter", sans-serif;
     }
     div.inside {
       width: 100%;
       max-width: 800px;
       margin-left: auto;
       margin-right:auto; 
       background: white;
       padding: 15px;
     }
     div.inside textarea {
       vertical-align: top;
       width: 100%;
       height: 3em;
     }
     label[for=clientid] {
       display: inline-block;
       width: 20%;
       padding-top: 10px;
     }
     #clientid {
       width: 80%;
     }
     label[for=clientsecret] {
       display: inline-block;
       width: 20%;
       padding-top: 10px;
     }
     #clientsecret {
       width: 80%;
     }
     a.optional {
       opacity: 0.5;
       text-decoration: none;
     }
     a.optional:hover {
       opacity: 1;
       text-decoration: underline;
     }
     #install-authentification-expand {
       display: none;
     }
     #install-authentification-expand.alreadyCredentials {
       display: block;
       cursor: pointer;
       color: blue;
     }
     #install-authentification-expand.alreadyCredentials:hover {
       text-decoration: underline;
     }
     #install-authentification {
       display: block;
     }
     #install-authentification.alreadyCredentials {
       display: none;
     }
     
    </style>
    <body><div class="inside"><?php

$defaultHtAccess = <<<EOD

#### START THADITOR (PLEASE DO NOT EDIT) ####
# Any URL containing ?edit is redirected to Editor
RewriteEngine On
Options -Indexes
RewriteRule ^\. [F,L]
RewriteRule ^Thaditor/credentials.json$ [F,L]
RewriteRule ^\.htaccess$ [F,L]
RewriteRule ^Thaditor/cacert\.pem$ [F,L]
RewriteRule ^Thaditor/php.ini$ [F,L]
RewriteRule ^Thaditor/admins.txt$ [F,L]
RewriteRule ^Thaditor/verifiedTokens$ [F,L]
RewriteRule .git\b [F,L]

#### EDITOR CSS CONFIG ####
RewriteRule server-elm-style.css Thaditor/Editor/server-elm-style.css

RewriteCond %{QUERY_STRING} (&|^)(edit|ls|raw)(?:=true)?$
RewriteRule ^(.*)$ /Thaditor/editor.php?location=$1 [L,B]

#### END THADITOR ####

EOD;

$htregex = "/\s*#+\s*START (?:THARZEN EDITOR|THADITOR)[\s\S]*?#+\s*END (?:THARZEN EDITOR|THADITOR)\s*#+\s*/";

function InstallHtAccess() {
  global $defaultHtAccess, $htregex;
  if(!file_exists(".htaccess")) {
    $c = "";
  } else {
    $c = file_get_contents(".htaccess");
  }
  if(preg_match($htregex, $c)) {
    $c = preg_replace($htregex, str_replace(array('\\', '$'), array('\\\\', '\\$'), $defaultHtAccess), $c);
  } else {
    $c = $c.$defaultHtAccess;
  }
  file_put_contents(".htaccess", $c);
}

$licenseFile = "Thaditor/licensekey.txt";
$credentialsFile = "Thaditor/credentials.json";
$admintxtpth = "Thaditor/admins.txt";
$update = file_exists("Thaditor/editor.php");
$freeVersion = file_exists(".localkey");
$alreadyLicenseKey = file_exists($licenseFile);
$alreadyCredentials = file_exists($credentialsFile);
$alreadyAdmins = file_exists($admintxtpth);

if($_SERVER['REQUEST_METHOD'] == "GET") {
    ?><script>
    function submitted() {
      let feedback = document.getElementById("feedback");
      feedback.innerText = " Please wait a few dozen seconds while Thaditor is being configured...";
      return true;
    }</script><h1><?php if($update) { echo "Update"; } else { echo "Install"; } ?> Thaditor (5 minutes)</h1><p
    >Thank you for <?php if($update) { echo "having installed"; } else { echo "installing"; } ?> Tharzen's Thaditor on your website. At Tharzen, we take your security seriously. Before clicking on "<?php if($update) { echo "Update"; } else { echo "Install"; } ?> Thaditor", please follow the following steps carefully:</p
    ><ul><li><?php if($alreadyLicenseKey) { echo "First, please retrieve the Thaditor license key for your domain. You should have it in your emails."; }
     else {
       echo "First, obtain a Thaditor license key for your domain.";
     } ?></li
    ><li><?php if($alreadyCredentials) {
    echo "The second step is obsolete, it seems that you already have installed Google Credentials"; } else {
      echo "Second, enable Google sign-in for your website.";
    }?></li
    ><li>Third, enter administrators emails</li></ul><form id="licenseKey" name="licenseKey" method="POST" onsubmit="submitted()"
    ><h2>Enter Thaditor License key</h2
      ><a href="mailto:sales@tharzen.com?subject=License%20key%20for%20Thaditor&body=Hello,%20I%20would%20like%20a%20license%20key%20for%20my%20domain%20'<?php echo $_SERVER["SERVER_NAME"]; ?>'"<?php if($alreadyLicenseKey) { echo " class='optional'"; } ?>>Obtain a<?php if($alreadyLicenseKey) { echo " new"; } ?> Thaditor license key.</a>
<label for="lkey"><?php if($alreadyLicenseKey) { echo "Current l"; } else { echo "L"; } ?>icense Key:</label><textarea id="lkey" name="lkey" placeholder="Paste here your<?php if($alreadyLicenseKey) { echo " current"; } ?> license key"></textarea
    ><?php if(!$alreadyLicenseKey) { ?> <p>Note: If you want to try the free version of Thaditor without a license key, add a file named <code>.localkey</code> to the root of your server with a custom password in it, and enter the same password in the license key text box above.<br>This will ensure that only the owner of the website can set up administrators.<br>The free version also contains a 10-day free trial of the business version.</p><?php } ?><?php if($freeVersion) { ?><p>Note: Paste the password stored in the file <code>.localkey</code> that is located at the root of the website</p><?php } ?><h2>Enable Google authentification<?php if($alreadyCredentials) echo "(done)"?></h2
      ><a id="install-authentification-expand"<?php if($alreadyCredentials) echo ' class="alreadyCredentials" onclick="document.getElementById(\'install-authentification\').style.display = \'block\'"'?>>Install new credentials</a><div id="install-authentification" class="<?php if($alreadyCredentials) echo "alreadyCredentials"?>"><p>To set up Google authentification for you website, please follow these instructions:</p
        ><ol
          ><li>Go to <a href="https://console.developers.google.com/apis/credentials">https://console.developers.google.com/apis/credentials</a></li
          ><li>Click on "Start a project" or "New Project" on the top right corner.
If you don't see this, a project is possibly already created, skip to step 6.</li
          ><li>Project name: <code><?php echo $_SERVER["SERVER_NAME"]; ?>-thaditor-google-login</code> <i>(example)</i></li
          ><li>Location: your organization if you have one, else leave blank.</li
       ><li>Click <b>Create</b></li
       ><li>Ensure your project <code><?php echo $_SERVER["SERVER_NAME"]; ?>-thaditor-google-login</code> is selected (this name should appear on the top bar) </li
       ><li>Go to <b>OAuth consent screen</b> page.</li
       ><li><b>Application name</b>: <code><?php echo $_SERVER["SERVER_NAME"]; ?>-thaditor-google-login</code> <i>(example)</i>.</li
       ><li><b>Logo</b> (optional): https://tharzen.com/Tharzen_logo.png</li
       ><li><b>Authorized domains</b>: <code><?php echo $_SERVER["SERVER_NAME"]; ?></code></li
       ><li><b>Save</b></li
       ><li><b>Create credentials > OAuth Client ID</b>. If a pop-up does not suggest it yet, it means that credentials already exist. Locate them on the side bar.</li
       ><li>Select <b>Web application</b> as application type.</li
       ><li><b>Name:</b> <code><?php echo $_SERVER["SERVER_NAME"]; ?> login</code></li
       ><li><b>Authorised JavaScript origins:</b> <code>https://<?php echo $_SERVER["SERVER_NAME"]; ?></code> </li
       ><li><b>Authorised redirect URLs:</b> <code>https://<?php echo $_SERVER["SERVER_NAME"]; ?></code> </li
       ><li><b>Create</b></li
       ></ol
       ><p>After clicking "Create", you should see the client ID and client secret.
Please paste below your client ID and your client secret. They are not sent to Tharzen, but they are stored securely on your website:</p
       ><label for="clientid">Client ID: </label><input type="text" id="clientid" name="clientid"
       placeholder="Something like 546377-6vaeb321az3f53e1f32.apps.googleusercontent.com">
<label for="clientsecret">Client secret: </label><input type="text" id="clientsecret" name="clientsecret"
       placeholder="Something like zp5MjK42jk55pk2kdpj90sAPKE"
       ></div><h2>Enter <?php echo $_SERVER["SERVER_NAME"]; ?> Administrators</h2
    ><label for="email">Enter below the emails of the administrators for the website '<?php echo $_SERVER["SERVER_NAME"]; ?>'.
These emails should be associated to Google accounts.
Separate emails by commas.<?php if($alreadyAdmins) { echo " Leave blank to keep existing administrators"; } ?></label><textarea id="email" name="email" placeholder="myself@gmail.com, partner@example.edu"></textarea><br
      ><input type="submit" id="sub" value="<?php if($update) { echo "Update"; } else { echo "Install"; } ?> Thaditor"/><span id="feedback"></span>
    </form>
    <?php   
} else if($_SERVER['REQUEST_METHOD'] == "POST") {
    if (isset($_POST['lkey']) && isset($_POST['email'])) {
      $key = $_POST['lkey'];
      $domain = $_SERVER['SERVER_NAME'];
      $freeVersion = file_exists(".localkey");
      if($freeVersion && $key == file_get_contents(".localkey")) {
        $newLicenseInfo = base64_encode(json_encode([
          "key" => "free-version",
          "version" => "trial",
          "nextCheck" => "".(time() + 3600*24*10)
        ]));
      } else {
        $ch = curl_init();
        $checkUrl = "https://tharzen.com/licensing/check.php?license=$key&domain=$domain";
        curl_setopt($ch, CURLOPT_URL, $checkUrl);
        // Set so curl_exec returns the result instead of outputting it.
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        // Get the response and close the channel.
        $res = curl_exec($ch);
        $response = json_decode($res, true);
        if(json_last_error() != 0) {
          echo "Error while retrieving licensing information<br>".json_last_error_msg();
          echo $res;
          echo "<br>$checkUrl";
          die();
        }
        if($response["expired"]) {
          $msg = isset($response["error"]) ? " (".$response["error"].")" : "";
          ?>
          <h1>Incorrect license key.</h1>
  <p>This key expired or is not valid <?php echo $msg; ?>. Please refresh the page and try again.</p>
  <p>The license key you provided does not match any in our records or is expired <?php echo $msg; ?>. Please <a href="mailto:sales@tharzen.com">contact us</a> to obtain a new key.</p>
  <a href="">Go back</a>
          <?php

          die();
        } else {
          $newLicenseInfo = base64_encode(json_encode([
            "key" => $key,
            "version" => $response["version"],
            "nextCheck" => $response["nextCheck"]
          ]));
        }
      }
      $admin_email = $_POST['email'];
      $admin_email_array = preg_split("/,\\s*/", $admin_email, -1, PREG_SPLIT_NO_EMPTY);
      $wrong_email = false;
      if($admin_email != "" || !$alreadyAdmins) {
        foreach ($admin_email_array as $email) {
            if(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
              $wrong_email = true;
              ?>
                <h1>Invalid email entered: <?php echo $email; ?></h1>
                <p>Please enter a valid email.</p>
                <?php
            }
        }
      }
      $wrong_credentials = false;
      if((!$_POST["clientid"] || !$_POST["clientsecret"]) && !$alreadyCredentials) {
        $wrong_credentials = true;
        ?><h1>Missing credentials</h1
        ><p>You need to enter a client ID and a client secret the first time you install Thaditor.</p>
        <?php
      }
      if(!$wrong_email && !$wrong_credentials) {
          $final_emails = "[\"".join("\", \"", $admin_email_array)."\"]";
          InstallHtAccess();
          echo "Downloading Thaditor....<br>";
          copy('https://tharzen.com/download/thaditor-latest.php', 'installer_holder.zip');
          if(!file_exists("Thaditor")) {
            mkdir("Thaditor");
          }
          //write admins.txt
          if($admin_email != "") {
            file_put_contents($admintxtpth, $final_emails);
            echo "wrote the admins in $admintxtpth: $final_emails<br>";
          }
          file_put_contents($licenseFile, $newLicenseInfo);
          echo "wrote the license cache info in $licenseFile.<br>";
          if($_POST["clientid"] && $_POST["clientsecret"]) {
            $credentialsJson = '{"web":{"client_id":"'.$_POST["clientid"].'","client_secret":"'.$_POST["clientsecret"].'"}}';
            file_put_contents($credentialsFile, $credentialsJson);
            echo "wrote the credentials into $credentialsFile.<br>";
          }
          echo "Installing Thaditor....<br>";
          $zip = new ZipArchive;
          $res = $zip->open('installer_holder.zip');
          if ($res === TRUE) {
            $zip->extractTo('Thaditor/');
            $zip->close();
            echo 'Thaditor extracted correctly<br>';
          } else {
            echo 'Thaditor failed to extract<br>';
          }
          $tmpDir = "Thaditor/.temp_thaditor";
          // Get array of all source files
          $files = scandir($tmpDir);
          // Identify directories
          $source = "$tmpDir/";
          $destination = "Thaditor/";
          recurse_move($tmpDir, "Thaditor");
          rmdir($tmpDir);
          unlink("installer_holder.zip");

          echo "Writing admins out<br>";
          
          ?>
          Installation finished.<br>Add ?edit to any web addresses on your website to edit these pages.<br>
          <a href="/?edit">Open Thaditor</a>
          <?php
      } else {
          ?>
          <a href="">Go back</a>
          <?php
      } //email block
    } else { //lkey + email set block
        echo "Please supply both a license key and an email to set as the admin.";
    }
} else { //not a post method
    echo "unsupported";
}
?>
</div>
</body>
</html>
<?php
?>