# OCR Log Checking Guide

## Log File Location

The PHP error log is located at:
```
C:\xampp\php\logs\php_error_log
```

## Methods to Check OCR Logs

### Method 1: Using the Log Checker Script (Recommended)

Run the provided script to filter and display OCR-related logs:

```bash
php check_ocr_logs.php
```

This script will:
- Show the last 100 log entries
- Filter only OCR-related logs
- Display them in a readable format

### Method 2: PowerShell - View Recent OCR Logs

Open PowerShell and run:

```powershell
# View last 50 lines
Get-Content C:\xampp\php\logs\php_error_log -Tail 50 | Select-String -Pattern "OCR|Tesseract|Google Vision"

# View in real-time (follow logs)
Get-Content C:\xampp\php\logs\php_error_log -Wait -Tail 50 | Select-String -Pattern "OCR"
```

### Method 3: Command Prompt - Find OCR Logs

```cmd
# Find all OCR-related logs
findstr /i "OCR Tesseract Google Vision" C:\xampp\php\logs\php_error_log

# View last 100 lines with OCR
powershell "Get-Content C:\xampp\php\logs\php_error_log -Tail 100 | Select-String -Pattern 'OCR'"
```

### Method 4: Manual File Viewing

1. Navigate to: `C:\xampp\php\logs\`
2. Open `php_error_log` in a text editor
3. Search for `[OCR]` to find OCR-related entries

## Log Entry Format

All OCR logs are prefixed with `[OCR]` for easy filtering. Here are the log types you'll see:

### Success Logs
```
[OCR] Starting OCR processing for image: filename.jpg (Extension: jpg)
[OCR] SUCCESS - Image: filename.jpg | Engine: tesseract | Text length: 1234 chars | Confidence: 85.5% | Processing time: 2.3s
[OCR] COMPLETE - Successfully extracted 1234 characters from image: filename.jpg
```

### Failure Logs
```
[OCR] FAILED - Image: filename.jpg | Engine: tesseract | Error: No text detected in image
[OCR] WARNING - No text extracted from image: filename.jpg. File will still be saved.
```

### Error Logs
```
[OCR] Tesseract Error: Tesseract is not available. Please install Tesseract OCR.
[OCR] Google Vision Error: Google Vision client not initialized. Please check your credentials.
[OCR] EXCEPTION - Image: filename.jpg | Exception: Error message here
```

### Usage Logs
```
[OCR] Usage: {"timestamp":"2024-01-01 12:00:00","image_path":"filename.jpg","success":true,"engine":"tesseract","confidence":0.85,"text_length":1234,"processing_time":2.3}
```

## What to Look For

### ✅ Successful OCR Processing
- Look for `[OCR] SUCCESS` entries
- Check confidence percentage (higher is better)
- Verify text length is greater than 0

### ❌ Failed OCR Processing
- Look for `[OCR] FAILED` entries
- Check error messages to understand why it failed
- Common issues:
  - Tesseract not installed or not in PATH
  - Google Vision credentials not configured
  - Image has no text content
  - Image format not supported

### ⚠️ Warnings
- `[OCR] WARNING` entries indicate images were processed but no text was found
- Files are still saved even if OCR fails

## Real-Time Monitoring

To monitor OCR logs in real-time while uploading images:

```powershell
Get-Content C:\xampp\php\logs\php_error_log -Wait -Tail 0 | Select-String -Pattern "\[OCR\]"
```

This will show new OCR log entries as they occur.

## Troubleshooting

### No OCR logs appearing?
1. Check if error logging is enabled in `php.ini`
2. Verify the log file path: `php -i | findstr "error_log"`
3. Check file permissions on the log directory
4. Ensure images are actually being uploaded (check file extension)

### OCR not working?
1. Check for `[OCR] FAILED` or `[OCR] EXCEPTION` entries
2. Verify Tesseract is installed: `tesseract --version`
3. Check Google Vision credentials if using fallback
4. Review error messages in the logs

