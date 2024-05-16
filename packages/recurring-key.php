<?php
class certificate {
    public function cerValidation() {
        $allowed_extension = array("key", "cer", "pem");
        foreach ($_FILES as $key => $fileInput){
            $file_extension = pathinfo($fileInput["name"], PATHINFO_EXTENSION);
            $file_mime = $fileInput["type"];

            // Validate file input to check if is not empty
            if (! file_exists($fileInput["tmp_name"])) {
                $response = array(
                    "type" => false,
                    "message" => "Select file to upload."
                );
            }// Validate file input to check if is with valid extension
            elseif (! in_array($file_extension, $allowed_extension)) {
                $response = array(
                    "type" => false,
                    "message" => "Upload valid certificate. Only .cer / .key are allowed."
                );
            }// Validate file MIME
            else {
                  if ($this->sanitizeVerify($file_extension, $key)){
                    $response = $this->uploadCer($fileInput);
                    } else {
                        $response = array(
                            "type" => false,
                            "message" => "The file is not sanitized / suitable for this field!!"
                        );
                    }
                 }
            return $response;
        }
        
    }
    
    public function sanitizeVerify($file_extension, $key) {
        switch ($key) {
            case "netopia_recurring_live_public_key" :
            case "netopia_recurring_sandbox_public_key" :
                if ($file_extension != 'cer')
                    return false;
                break;
            case "netopia_recurring_live_private_key" :
            case "netopia_recurring_sandbox_private_key" :
                if ($file_extension != 'key')
                    return false;
                break;
        }
        return true;
    }

    public function uploadCer($fileInput) {
        $target = WP_PLUGIN_DIR . '/netopia-recurring/certificates/'.basename($fileInput["name"]);
        if (move_uploaded_file($fileInput["tmp_name"], $target)) {
            $response = array(
                "status" => true,
                "message" => "Certificate uploaded successfully."
            );
        } else {
            $response = array(
                "status" => false,
                "message" => "Problem in uploading Certificate."
            );
        }
        return $response;
    }
}
?>