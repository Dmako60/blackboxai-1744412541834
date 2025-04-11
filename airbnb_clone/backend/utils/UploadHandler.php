<?php
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/Response.php';

class UploadHandler {
    private $uploadPath;
    private $maxFileSize;
    private $allowedTypes;
    private $uploadedFiles = [];
    private $errors = [];

    public function __construct() {
        $this->uploadPath = UPLOAD_PATH;
        $this->maxFileSize = MAX_FILE_SIZE;
        $this->allowedTypes = ALLOWED_FILE_TYPES;

        // Create upload directory if it doesn't exist
        if (!file_exists($this->uploadPath)) {
            mkdir($this->uploadPath, 0755, true);
        }
    }

    /**
     * Handle single file upload
     * 
     * @param array $file $_FILES array element
     * @param string $customPath Optional custom upload path
     * @return string|false Returns file path on success, false on failure
     */
    public function uploadSingle($file, $customPath = '') {
        if (!$this->validateFile($file)) {
            return false;
        }

        $uploadPath = $this->uploadPath;
        if ($customPath) {
            $uploadPath .= '/' . trim($customPath, '/');
            if (!file_exists($uploadPath)) {
                mkdir($uploadPath, 0755, true);
            }
        }

        $fileName = $this->generateUniqueFileName($file['name']);
        $filePath = $uploadPath . '/' . $fileName;

        if (move_uploaded_file($file['tmp_name'], $filePath)) {
            $this->uploadedFiles[] = $filePath;
            return $fileName;
        }

        $this->errors[] = "Failed to move uploaded file.";
        return false;
    }

    /**
     * Handle multiple file uploads
     * 
     * @param array $files Array of $_FILES elements
     * @param string $customPath Optional custom upload path
     * @return array Returns array of uploaded file paths
     */
    public function uploadMultiple($files, $customPath = '') {
        $uploadedFiles = [];

        foreach ($files as $file) {
            $result = $this->uploadSingle($file, $customPath);
            if ($result) {
                $uploadedFiles[] = $result;
            }
        }

        return $uploadedFiles;
    }

    /**
     * Validate uploaded file
     * 
     * @param array $file $_FILES array element
     * @return bool Returns true if file is valid
     */
    private function validateFile($file) {
        // Check for upload errors
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $this->errors[] = $this->getUploadErrorMessage($file['error']);
            return false;
        }

        // Check file size
        if ($file['size'] > $this->maxFileSize) {
            $this->errors[] = "File size exceeds maximum limit of " . ($this->maxFileSize / 1024 / 1024) . "MB.";
            return false;
        }

        // Check file type
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mimeType, $this->allowedTypes)) {
            $this->errors[] = "File type not allowed. Allowed types: " . implode(', ', $this->allowedTypes);
            return false;
        }

        return true;
    }

    /**
     * Generate unique filename
     * 
     * @param string $originalName Original filename
     * @return string Unique filename
     */
    private function generateUniqueFileName($originalName) {
        $extension = pathinfo($originalName, PATHINFO_EXTENSION);
        return uniqid() . '_' . time() . '.' . $extension;
    }

    /**
     * Get upload error message
     * 
     * @param int $errorCode PHP upload error code
     * @return string Error message
     */
    private function getUploadErrorMessage($errorCode) {
        switch ($errorCode) {
            case UPLOAD_ERR_INI_SIZE:
                return "The uploaded file exceeds the upload_max_filesize directive in php.ini.";
            case UPLOAD_ERR_FORM_SIZE:
                return "The uploaded file exceeds the MAX_FILE_SIZE directive specified in the HTML form.";
            case UPLOAD_ERR_PARTIAL:
                return "The uploaded file was only partially uploaded.";
            case UPLOAD_ERR_NO_FILE:
                return "No file was uploaded.";
            case UPLOAD_ERR_NO_TMP_DIR:
                return "Missing a temporary folder.";
            case UPLOAD_ERR_CANT_WRITE:
                return "Failed to write file to disk.";
            case UPLOAD_ERR_EXTENSION:
                return "A PHP extension stopped the file upload.";
            default:
                return "Unknown upload error.";
        }
    }

    /**
     * Delete uploaded file
     * 
     * @param string $filePath Path to the file
     * @return bool Returns true if file was deleted
     */
    public function deleteFile($filePath) {
        $fullPath = $this->uploadPath . '/' . $filePath;
        if (file_exists($fullPath)) {
            return unlink($fullPath);
        }
        return false;
    }

    /**
     * Delete multiple files
     * 
     * @param array $filePaths Array of file paths
     * @return array Returns array of deletion results
     */
    public function deleteFiles($filePaths) {
        $results = [];
        foreach ($filePaths as $path) {
            $results[$path] = $this->deleteFile($path);
        }
        return $results;
    }

    /**
     * Get upload errors
     * 
     * @return array Array of error messages
     */
    public function getErrors() {
        return $this->errors;
    }

    /**
     * Get uploaded files
     * 
     * @return array Array of uploaded file paths
     */
    public function getUploadedFiles() {
        return $this->uploadedFiles;
    }

    /**
     * Clear uploaded files
     */
    public function clearUploadedFiles() {
        $this->uploadedFiles = [];
    }

    /**
     * Clear errors
     */
    public function clearErrors() {
        $this->errors = [];
    }

    /**
     * Check if file exists
     * 
     * @param string $filePath Path to the file
     * @return bool Returns true if file exists
     */
    public function fileExists($filePath) {
        return file_exists($this->uploadPath . '/' . $filePath);
    }

    /**
     * Get file URL
     * 
     * @param string $filePath Path to the file
     * @return string File URL
     */
    public function getFileUrl($filePath) {
        return API_BASE_URL . 'uploads/' . $filePath;
    }

    /**
     * Get file size
     * 
     * @param string $filePath Path to the file
     * @return int|false Returns file size in bytes or false if file doesn't exist
     */
    public function getFileSize($filePath) {
        $fullPath = $this->uploadPath . '/' . $filePath;
        return file_exists($fullPath) ? filesize($fullPath) : false;
    }

    /**
     * Get file mime type
     * 
     * @param string $filePath Path to the file
     * @return string|false Returns mime type or false if file doesn't exist
     */
    public function getFileMimeType($filePath) {
        $fullPath = $this->uploadPath . '/' . $filePath;
        if (!file_exists($fullPath)) {
            return false;
        }
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $fullPath);
        finfo_close($finfo);
        return $mimeType;
    }
}
?>
