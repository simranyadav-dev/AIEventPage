<?php
/**
 * FileUpload Class - Handle file uploads with validation
 * AI Conference Summit - Beginner Friendly Code
 */

class FileUpload {
    private $allowed_types = [
        'image' => ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'],
        'pdf' => ['application/pdf']
    ];
    
    private $max_sizes = [
        'image' => 5 * 1024 * 1024, // 5MB
        'pdf' => 10 * 1024 * 1024   // 10MB
    ];
    
    /**
     * Upload image file
     */
    public function uploadImage($file, $folder = 'general') {
        return $this->upload($file, 'image', $folder);
    }
    
    /**
     * Upload PDF file
     */
    public function uploadPDF($file, $folder = 'documents') {
        return $this->upload($file, 'pdf', $folder);
    }
    
    /**
     * Main upload function
     */
    private function upload($file, $type, $folder) {
        try {
            // Check if file was uploaded
            if (!isset($file['tmp_name']) || empty($file['tmp_name'])) {
                return ['success' => false, 'message' => 'No file was uploaded'];
            }
            
            // Check for upload errors
            if ($file['error'] !== UPLOAD_ERR_OK) {
                return ['success' => false, 'message' => $this->getUploadErrorMessage($file['error'])];
            }
            
            // Validate file type
            $validation = $this->validateFile($file, $type);
            if (!$validation['valid']) {
                return ['success' => false, 'message' => $validation['message']];
            }
            
            // Create upload directory
            $upload_dir = UPLOAD_PATH . $folder . '/';
            if (!file_exists($upload_dir)) {
                if (!mkdir($upload_dir, 0777, true)) {
                    return ['success' => false, 'message' => 'Failed to create upload directory'];
                }
            }
            
            // Generate unique filename
            $filename = $this->generateUniqueFilename($file['name'], $upload_dir);
            $file_path = $upload_dir . $filename;
            
            // Move uploaded file
            if (move_uploaded_file($file['tmp_name'], $file_path)) {
                // Set proper file permissions
                chmod($file_path, 0644);
                
                return [
                    'success' => true,
                    'message' => 'File uploaded successfully',
                    'filename' => $filename,
                    'file_path' => $file_path,
                    'file_size' => $file['size']
                ];
            } else {
                return ['success' => false, 'message' => 'Failed to move uploaded file'];
            }
            
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Upload error: ' . $e->getMessage()];
        }
    }
    
    /**
     * Validate uploaded file
     */
    private function validateFile($file, $type) {
        // Check file size
        if ($file['size'] > $this->max_sizes[$type]) {
            $max_size_mb = $this->max_sizes[$type] / (1024 * 1024);
            return [
                'valid' => false,
                'message' => "File size exceeds maximum allowed size of {$max_size_mb}MB"
            ];
        }
        
        // Check MIME type
        $file_info = finfo_open(FILEINFO_MIME_TYPE);
        $mime_type = finfo_file($file_info, $file['tmp_name']);
        finfo_close($file_info);
        
        if (!in_array($mime_type, $this->allowed_types[$type])) {
            $allowed = implode(', ', $this->allowed_types[$type]);
            return [
                'valid' => false,
                'message' => "Invalid file type. Allowed types: {$allowed}"
            ];
        }
        
        // Additional validation for images
        if ($type === 'image') {
            $image_info = getimagesize($file['tmp_name']);
            if ($image_info === false) {
                return [
                    'valid' => false,
                    'message' => 'Invalid image file'
                ];
            }
            
            // Check image dimensions (optional)
            $max_width = 2000;
            $max_height = 2000;
            if ($image_info[0] > $max_width || $image_info[1] > $max_height) {
                return [
                    'valid' => false,
                    'message' => "Image dimensions too large. Maximum: {$max_width}x{$max_height}px"
                ];
            }
        }
        
        return ['valid' => true, 'message' => 'File validation passed'];
    }
    
    /**
     * Generate unique filename to prevent conflicts
     */
    private function generateUniqueFilename($original_name, $upload_dir) {
        // Get file extension
        $extension = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));
        
        // Create unique filename
        $timestamp = time();
        $random = bin2hex(random_bytes(8));
        $filename = "file_{$timestamp}_{$random}.{$extension}";
        
        // Check if file exists (unlikely but possible)
        $counter = 1;
        while (file_exists($upload_dir . $filename)) {
            $filename = "file_{$timestamp}_{$random}_{$counter}.{$extension}";
            $counter++;
        }
        
        return $filename;
    }
    
    /**
     * Get upload error message
     */
    private function getUploadErrorMessage($error_code) {
        switch ($error_code) {
            case UPLOAD_ERR_INI_SIZE:
                return 'File exceeds the maximum upload size set in php.ini';
            case UPLOAD_ERR_FORM_SIZE:
                return 'File exceeds the maximum upload size specified in the HTML form';
            case UPLOAD_ERR_PARTIAL:
                return 'File was only partially uploaded';
            case UPLOAD_ERR_NO_FILE:
                return 'No file was uploaded';
            case UPLOAD_ERR_NO_TMP_DIR:
                return 'Missing temporary folder';
            case UPLOAD_ERR_CANT_WRITE:
                return 'Failed to write file to disk';
            case UPLOAD_ERR_EXTENSION:
                return 'File upload stopped by extension';
            default:
                return 'Unknown upload error';
        }
    }
    
    /**
     * Delete uploaded file
     */
    public function deleteFile($filename, $folder) {
        $file_path = UPLOAD_PATH . $folder . '/' . $filename;
        
        if (file_exists($file_path)) {
            if (unlink($file_path)) {
                return ['success' => true, 'message' => 'File deleted successfully'];
            } else {
                return ['success' => false, 'message' => 'Failed to delete file'];
            }
        } else {
            return ['success' => false, 'message' => 'File not found'];
        }
    }
    
    /**
     * Resize image (basic implementation)
     */
    public function resizeImage($source_path, $destination_path, $max_width, $max_height) {
        try {
            // Get image info
            $image_info = getimagesize($source_path);
            if ($image_info === false) {
                return ['success' => false, 'message' => 'Invalid image'];
            }
            
            $source_width = $image_info[0];
            $source_height = $image_info[1];
            $mime_type = $image_info['mime'];
            
            // Calculate new dimensions
            $ratio = min($max_width / $source_width, $max_height / $source_height);
            $new_width = intval($source_width * $ratio);
            $new_height = intval($source_height * $ratio);
            
            // Create source image resource
            switch ($mime_type) {
                case 'image/jpeg':
                    $source_image = imagecreatefromjpeg($source_path);
                    break;
                case 'image/png':
                    $source_image = imagecreatefrompng($source_path);
                    break;
                case 'image/gif':
                    $source_image = imagecreatefromgif($source_path);
                    break;
                default:
                    return ['success' => false, 'message' => 'Unsupported image type'];
            }
            
            if ($source_image === false) {
                return ['success' => false, 'message' => 'Failed to create image resource'];
            }
            
            // Create new image
            $new_image = imagecreatetruecolor($new_width, $new_height);
            
            // Preserve transparency for PNG and GIF
            if ($mime_type === 'image/png' || $mime_type === 'image/gif') {
                imagealphablending($new_image, false);
                imagesavealpha($new_image, true);
                $transparent = imagecolorallocatealpha($new_image, 255, 255, 255, 127);
                imagefill($new_image, 0, 0, $transparent);
            }
            
            // Resize image
            imagecopyresampled($new_image, $source_image, 0, 0, 0, 0, $new_width, $new_height, $source_width, $source_height);
            
            // Save resized image
            $success = false;
            switch ($mime_type) {
                case 'image/jpeg':
                    $success = imagejpeg($new_image, $destination_path, 90);
                    break;
                case 'image/png':
                    $success = imagepng($new_image, $destination_path, 9);
                    break;
                case 'image/gif':
                    $success = imagegif($new_image, $destination_path);
                    break;
            }
            
            // Clean up memory
            imagedestroy($source_image);
            imagedestroy($new_image);
            
            if ($success) {
                return [
                    'success' => true,
                    'message' => 'Image resized successfully',
                    'new_width' => $new_width,
                    'new_height' => $new_height
                ];
            } else {
                return ['success' => false, 'message' => 'Failed to save resized image'];
            }
            
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Resize error: ' . $e->getMessage()];
        }
    }
    
    /**
     * Get file information
     */
    public function getFileInfo($file_path) {
        if (!file_exists($file_path)) {
            return ['success' => false, 'message' => 'File not found'];
        }
        
        return [
            'success' => true,
            'filename' => basename($file_path),
            'size' => filesize($file_path),
            'modified' => filemtime($file_path),
            'mime_type' => mime_content_type($file_path)
        ];
    }
    
    /**
     * Validate image dimensions
     */
    public function validateImageDimensions($file_path, $min_width = 100, $min_height = 100) {
        $image_info = getimagesize($file_path);
        if ($image_info === false) {
            return ['valid' => false, 'message' => 'Invalid image file'];
        }
        
        if ($image_info[0] < $min_width || $image_info[1] < $min_height) {
            return [
                'valid' => false,
                'message' => "Image too small. Minimum dimensions: {$min_width}x{$min_height}px"
            ];
        }
        
        return [
            'valid' => true,
            'width' => $image_info[0],
            'height' => $image_info[1]
        ];
    }
}
?>