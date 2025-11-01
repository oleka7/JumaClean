private function send_to_google_sheets($form_data) {
    $google_sheets_url = get_option('juma_google_sheets_url', '');
    
    if (empty($google_sheets_url)) {
        error_log('Juma Clean: Google Sheets URL not configured');
        return false;
    }
    
    // Properly format data for Google Sheets with correct field mappings
    $data = array(
        'timestamp' => current_time('Y-m-d H:i:s', true), // Use UTC time
        'customer_type' => sanitize_text_field($form_data['customer_type']),
        'package_type' => isset($form_data['vaskepakke']) ? sanitize_text_field($form_data['vaskepakke']) : '',
        'fornavn' => sanitize_text_field($form_data['fornavn']),
        'etternavn' => sanitize_text_field($form_data['etternavn']),
        'gatenavn' => sanitize_text_field($form_data['gatenavn']),
        'husnr' => sanitize_text_field($form_data['husnummer']), // Fix field name
        'oppgang' => sanitize_text_field($form_data['oppgang']),
        'postnr' => sanitize_text_field($form_data['postnummer']), // Fix field name
        'poststed' => sanitize_text_field($form_data['poststed']),
        'telefon' => sanitize_text_field($form_data['telefonnummer']), // Fix field name
        'epost' => sanitize_email($form_data['epost'])
    );
    
    $response = wp_remote_post($google_sheets_url, array(
        'method' => 'POST',
        'timeout' => 30, // Increased timeout for reliability
        'headers' => array(
            'Content-Type' => 'application/json',
            'Accept' => 'application/json'
        ),
        'body' => json_encode($data),
        'data_format' => 'body'
    ));
    
    if (is_wp_error($response)) {
        error_log('Juma Clean Google Sheets Error: ' . $response->get_error_message());
        return false;
    }
    
    $response_code = wp_remote_retrieve_response_code($response);
    $response_body = wp_remote_retrieve_body($response);
    
    if ($response_code !== 200) {
        error_log('Juma Clean Google Sheets Error: Non-200 response code: ' . $response_code);
        error_log('Response body: ' . $response_body);
        return false;
    }
    
    $result = json_decode($response_body, true);
    if (!$result || !isset($result['result']) || $result['result'] !== 'success') {
        error_log('Juma Clean Google Sheets Error: Invalid response format');
        error_log('Response body: ' . $response_body);
        return false;
    }
    
    return true;
}