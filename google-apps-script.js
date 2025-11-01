/**
 * Google Apps Script for Juma Clean Form WordPress Plugin
 * 
 * INSTALLASJON:
 * 1. Åpne din Google Sheet
 * 2. Gå til Extensions → Apps Script
 * 3. Kopier hele denne koden og lim inn i editoren
 * 4. Klikk Save (💾)
 * 5. Klikk Deploy → New deployment
 * 6. Velg type: Web app
 * 7. Execute as: Me
 * 8. Who has access: Anyone
 * 9. Klikk Deploy
 * 10. Kopier Web app URL
 * 11. Lim inn URL i WordPress → Settings → Juma Clean → Google Sheets Web App URL
 */

function doPost(e) {
  try {
    // Parse incoming data
    const data = JSON.parse(e.postData.contents);
    
    // Get active spreadsheet
    const sheet = SpreadsheetApp.getActiveSpreadsheet().getActiveSheet();
    
    // Check if header row exists, if not create it
    if (sheet.getLastRow() === 0) {
      const headers = [
        'Tidspunkt',
        'Kundetype',
        'Pakke',
        'Fornavn',
        'Etternavn',
        'Gatenavn',
        'Husnr',
        'Oppgang',
        'Postnr',
        'Poststed',
        'Telefon',
        'E-post'
      ];
      sheet.appendRow(headers);
      
      // Format header row
      const headerRange = sheet.getRange(1, 1, 1, headers.length);
      headerRange.setFontWeight('bold');
      headerRange.setBackground('#4285f4');
      headerRange.setFontColor('#ffffff');
    }
    
    // Prepare row data
    const rowData = [
      data.timestamp || new Date().toLocaleString('nb-NO'),
      data.customer_type || '',
      data.package_type || '',
      data.fornavn || '',
      data.etternavn || '',
      data.gatenavn || '',
      data.husnr || '',
      data.oppgang || '',
      data.postnr || '',
      data.poststed || '',
      data.telefon || '',
      data.epost || ''
    ];
    
    // Append data to sheet
    sheet.appendRow(rowData);
    
    // Auto-resize columns for better readability
    sheet.autoResizeColumns(1, rowData.length);
    
    // Return success response
    return ContentService.createTextOutput(
      JSON.stringify({ result: 'success', row: sheet.getLastRow() })
    ).setMimeType(ContentService.MimeType.JSON);
    
  } catch (error) {
    // Log error
    Logger.log('Error: ' + error.toString());
    
    // Return error response
    return ContentService.createTextOutput(
      JSON.stringify({ result: 'error', message: error.toString() })
    ).setMimeType(ContentService.MimeType.JSON);
  }
}

/**
 * Test function - Run this to test if the script works
 * Go to Apps Script editor and click Run → Run function → testDoPost
 */
function testDoPost() {
  const testData = {
    postData: {
      contents: JSON.stringify({
        timestamp: new Date().toLocaleString('nb-NO'),
        customer_type: 'Privat',
        package_type: 'liten',
        fornavn: 'Test',
        etternavn: 'Testesen',
        gatenavn: 'Testveien',
        husnr: '123',
        oppgang: 'A',
        postnr: '0123',
        poststed: 'Oslo',
        telefon: '12345678',
        epost: 'test@example.com'
      })
    }
  };
  
  const result = doPost(testData);
  Logger.log(result.getContent());
}




