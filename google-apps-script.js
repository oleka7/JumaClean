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
    // Validate request
    if (!e || !e.postData || !e.postData.contents) {
      throw new Error('Invalid request format');
    }

    // Parse incoming data with error handling
    let data;
    try {
      data = JSON.parse(e.postData.contents);
    } catch (parseError) {
      throw new Error('Failed to parse request data: ' + parseError.message);
    }
    
    // Get active spreadsheet with error handling
    const spreadsheet = SpreadsheetApp.getActiveSpreadsheet();
    if (!spreadsheet) {
      throw new Error('Failed to access spreadsheet');
    }
    const sheet = spreadsheet.getActiveSheet();
    
    // Lock the sheet for writing to prevent concurrent modification issues
    const lock = LockService.getScriptLock();
    try {
      if (!lock.tryLock(30000)) {
        throw new Error('Could not obtain lock');
      }
      
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
        
        // Freeze header row
        sheet.setFrozenRows(1);
      }
      
      // Prepare and validate row data
      const rowData = [
        data.timestamp || new Date().toISOString(),
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
      
    } finally {
      // Always release the lock
      lock.releaseLock();
    }
    
    // Return success response
    return ContentService.createTextOutput(
      JSON.stringify({
        result: 'success',
        row: sheet.getLastRow(),
        timestamp: new Date().toISOString()
      })
    ).setMimeType(ContentService.MimeType.JSON);
    
  } catch (error) {
    // Log error for debugging
    console.error('Error in doPost:', error);
    
    // Return error response
    return ContentService.createTextOutput(
      JSON.stringify({
        result: 'error',
        message: error.message,
        timestamp: new Date().toISOString()
      })
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
        timestamp: new Date().toISOString(),
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
  console.log('Test result:', result.getContent());
}