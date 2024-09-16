<?php
// Check if a file is uploaded
if (isset($_FILES['mobileconfig'])) {
    // Define the directory to store uploaded files
    $target_dir = "uploads/";
    $target_file = $target_dir . basename($_FILES["mobileconfig"]["name"]);
    $uploadOk = 1;

    // Move the uploaded file to the server
    if (move_uploaded_file($_FILES["mobileconfig"]["tmp_name"], $target_file)) {
        echo "The file " . htmlspecialchars(basename($_FILES["mobileconfig"]["name"])) . " has been uploaded.<br>";
        
        // Extract content from the uploaded file
        $file_content = file_get_contents($target_file);
        $extracted_data = extract_mobileconfig_info($file_content);

        // Save the extracted information to a .txt file
        $output_file = "extracted_info.txt";
        file_put_contents($output_file, $extracted_data);

        // Provide a download link for the extracted information
        echo "Download the extracted information: <a href='$output_file'>Download extracted_info.txt</a>";

    } else {
        echo "Sorry, there was an error uploading your file.";
    }
}

// Function to extract email configuration from the .mobileconfig content
function extract_mobileconfig_info($content) {
    // Extract the relevant XML section
    $xml_start = strpos($content, '<?xml');
    if ($xml_start === false) {
        return "Invalid file format.";
    }

    $xml_content = substr($content, $xml_start);

    // Load the XML content into a SimpleXMLElement
    try {
        $xml = new SimpleXMLElement($xml_content);
    } catch (Exception $e) {
        return "Error parsing XML.";
    }

    // Find the email configuration information
    $email_info = [];
    foreach ($xml->dict->array->dict as $dict) {
        foreach ($dict->key as $key) {
            $keyName = (string) $key;
            $value = (string) $key->nextSibling;
            switch ($keyName) {
                case 'EmailAccountDescription':
                    $email_info['Email Account Description'] = $value;
                    break;
                case 'EmailAccountName':
                    $email_info['Email Account Name'] = $value;
                    break;
                case 'EmailAccountType':
                    $email_info['Email Account Type'] = $value;
                    break;
                case 'IncomingMailServerHostName':
                    $email_info['Incoming Mail Server Hostname'] = $value;
                    break;
                case 'IncomingMailServerPortNumber':
                    $email_info['Incoming Mail Server Port'] = $value;
                    break;
                case 'IncomingMailServerUsername':
                    $email_info['Incoming Mail Server Username'] = $value;
                    break;
                case 'OutgoingMailServerHostName':
                    $email_info['Outgoing Mail Server Hostname'] = $value;
                    break;
                case 'OutgoingMailServerPortNumber':
                    $email_info['Outgoing Mail Server Port'] = $value;
                    break;
                case 'OutgoingMailServerUsername':
                    $email_info['Outgoing Mail Server Username'] = $value;
                    break;
            }
        }
    }

    // Format extracted information
    $extracted = "Extracted Email Configuration:\n";
    foreach ($email_info as $key => $value) {
        $extracted .= "$key: $value\n";
    }

    return $extracted;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload .mobileconfig File</title>
</head>
<body>

<h2>Upload .mobileconfig File</h2>
<form action="" method="post" enctype="multipart/form-data">
    Select .mobileconfig file to upload:
    <input type="file" name="mobileconfig" id="mobileconfig">
    <input type="submit" value="Upload and Extract" name="submit">
</form>

</body>
</html>
