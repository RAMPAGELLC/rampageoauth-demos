<?php
$scopes = array("target_rampage", "rampage_id", "rampage_dname", "rampage_name", "rampage_email", "target_discord", "discord_id", "discord_email");
$scopes = implode(',', $scopes);
$return = "https://secure.example.com/login";
$url = "https://id.rampage.place/oauth?scopes=" . rawurlencode($scopes) . "&return_url=" . rawurlencode($return);

// Handle Logging out
if (!empty($_GET["logout"])) {
    echo "<b>Your account has been logged out.</b>";
    setcookie(session_name(), '', 100);
    session_unset();
    session_destroy();
    $_SESSION = array();
}

// Function for cURL Http Requests
function HttpRequest($url, $fields)
{
    $ch = curl_init($url);
    $payload = json_encode($fields);

    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

    $result = curl_exec($ch);
    return json_decode($result);
    curl_close($ch);
}

// Handle login returns
if (!empty($_GET["target_rampage_key"])) {

// Verify keys
    $rampage_data = HttpRequest("https://id.rampage.place/oauth-api/redeem", [
        "key" => htmlspecialchars($_GET["target_rampage_key"]),
        "platform" => "target_rampage"
    ]);
    
    $discord_data = HttpRequest("https://id.rampage.place/oauth-api/redeem", [
        "key" => htmlspecialchars($_GET["target_discord_key"]),
        "platform" => "target_discord"
    ]);

    $success = true;

    if (!$rampage_data->success) $success = false;
    if (!$discord_data->success) $success = false;
    if (!$success) echo "<b>Login authorization key is expired, unable to login.</b>";
    
// If successful, then log in the user.
    if ($success) {
        $_SESSION["rampage_oauth_session"] = array_merge($rampage_data, $discord_data);
        echo "<b>Account authorized!</b>";
        echo "<p>Payload:</p>" . json_encode($data);
    }
} elseif (!empty($_GET["failed"])) {
    if ($_GET["failed"] == true) {
        echo "<b>Login failed due to: Pending Verification, No Account Found, or a system error occured at id.rampage.place.</b>";
    }
}

<?php
if (empty($_SESSION["rampage_oauth_session"])) {
    echo "<script>window.location='$url'</script>"; // redirect for login
} else {
    echo $_SESSION["rampage_oauth_session"]->rampage_id; // display data
}
?>
