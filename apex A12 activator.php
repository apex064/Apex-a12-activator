<?php
// Check if activation data is received
$activation = array_key_exists('activation-info-base64', $_POST) 
              ? base64_decode($_POST['activation-info-base64']) 
              : (array_key_exists('activation-info', $_POST) ? $_POST['activation-info'] : '');

if (!isset($activation) || empty($activation)) {
    exit('Make sure the device is connected');
}

// Parse activation info to extract basic device details
$encodedRequest = new DOMDocument;
$encodedRequest->loadXML($activation);
$activationDecoded = base64_decode($encodedRequest->getElementsByTagName('data')->item(0)->nodeValue);

$decodedRequest = new DOMDocument;
$decodedRequest->loadXML($activationDecoded);
$nodes = $decodedRequest->getElementsByTagName('dict')->item(0)->getElementsByTagName('*');

// Extract Serial Number, UDID, and IMEI
$serialNumber = $uniqueDeviceID = $imei = '';
for ($i = 0; $i < $nodes->length - 1; $i = $i + 2) {
    switch ($nodes->item($i)->nodeValue) {
        case "SerialNumber": 
            $serialNumber = $nodes->item($i + 1)->nodeValue; 
            break;
        case "UniqueDeviceID": 
            $uniqueDeviceID = $nodes->item($i + 1)->nodeValue; 
            break;
        case "InternationalMobileEquipmentIdentity": 
            $imei = $nodes->item($i + 1)->nodeValue; 
            break;
    }
}

// Basic validations
$snLength = strlen($serialNumber);
$udidLength = strlen($uniqueDeviceID);
if ($snLength < 11 || $snLength > 12 || $udidLength != 40) {
    exit("Invalid device details");
}

// Load your custom factory ticket (replace with your own ticket content)
$yourTicketBase64 = 'YOUR_BASE64_ENCODED_TICKET_HERE';

// Construct the XML response to send to the device
$response = '$response ='<!DOCTYPE html><html><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8" /><meta name="keywords" content="iTunes Store" /><meta name="description" content="iTunes Store" /><title>iPhone Activation</title><link href="http://static.ips.apple.com/ipa_itunes/stylesheets/shared/common-min.css" charset="utf-8" rel="stylesheet" /><link href="http://static.ips.apple.com/deviceservices/stylesheets/styles.css" charset="utf-8" rel="stylesheet" /><link href="http://static.ips.apple.com/ipa_itunes/stylesheets/pages/IPAJingleEndPointErrorPage-min.css" charset="utf-8" rel="stylesheet" /><link href="resources/auth_styles.css" charset="utf-8" rel="stylesheet" /><script id="protocol" type="text/x-apple-plist">
<plist version="1.0">
	<dict>
		<key>'.($deviceClass == "iPhone" ? 'iphone' : 'device').'-activation</key>
		<dict>
			<key>activation-record</key>
			<dict>
				<key>FairPlayKeyData</key>
				<data>'.$fairPlayKeyData.'</data>
				<key>AccountTokenCertificate</key>
				<data>'.$accountTokenCertificateBase64.'</data>
				<key>DeviceCertificate</key>
				<data>'.$deviceCertificate.'</data>
				<key>AccountTokenSignature</key>
				<data>'.$accountTokenSignature2.'</data>
				<key>AccountToken</key>
				<data>'.$accountTokenBase642.'</data>
			</dict>
			<key>unbrick</key>
			<true/>
			<key>show-settings</key>
			<true/>
		</dict>
	</dict>
</plist>
</script><script>var protocolElement = document.getElementById("protocol");var protocolContent = protocolElement.innerText;iTunes.addProtocol(protocolContent);</script></head>
</html>;

// Send the response back to the device
echo $response
exit();
?>

