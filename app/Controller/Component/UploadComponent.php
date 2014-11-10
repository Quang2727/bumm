<?php

App::uses('Component', 'Controller');
App::import('Vendor', 'UploadHandler', array('file' => 'UploadHandler/UploadHandler.php'));

class MyUploadHandler extends UploadHandler{
    protected function get_file_name($file_path, $name, $size, $type, $error,
            $index, $content_range) {

        if(!empty($this->options['filename'])){
            $name = $this->options['filename'];

            if (function_exists('exif_imagetype')) {
                switch(@exif_imagetype($file_path)){
                    case IMAGETYPE_JPEG:
                        $name = $name . '.jpg';
                        break;
                    case IMAGETYPE_PNG:
                        $name = $name . '.png';
                        break;
                    case IMAGETYPE_GIF:
                        $name = $name . '.gif';
                        break;
                }
            }

            return $name;
        }

        return $this->get_unique_filename(
            $file_path,
            $this->trim_file_name($file_path, $name, $size, $type, $error,
                $index, $content_range),
            $size,
            $type,
            $error,
            $index,
            $content_range
        );
    }

    protected function get_user_path() {
        if ($this->options['user_dirs']) {
            return $this->get_user_id().'/';
        }
        if (isset($this->options['custom_dir'])) {
            return $this->options['custom_dir'] ? $this->options['custom_dir'].'/' : '';
        }
        return date('Y/m/');
    }
}

class UploadComponent extends Component {
    protected $error_messages;

    public function initialize(Controller $controller) {
        $this->error_messages = array(
            1 => __('The uploaded file exceeds the upload_max_filesize directive in php.ini'),
            2 => __('The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form'),
            3 => __('The uploaded file was only partially uploaded'),
            4 => __('No file was uploaded'),
            6 => __('Missing a temporary folder'),
            7 => __('Failed to write file to disk'),
            8 => __('A PHP extension stopped the file upload'),
            'post_max_size' => __('The uploaded file exceeds the post_max_size directive in php.ini'),
            'max_file_size' => __('File is too big'),
            'min_file_size' => __('File is too small'),
            'accept_file_types' => __('Filetype not allowed'),
            'max_number_of_files' => __('Maximum number of files exceeded'),
            'max_width' => __('Image exceeds maximum width'),
            'min_width' => __('Image requires a minimum width'),
            'max_height' => __('Image exceeds maximum height'),
            'min_height' => __('Image requires a minimum height'),
            'abort' => __('File upload aborted'),
            'image_resize' => __('Failed to resize image'),
        );
    }

    public function uploadFile($options = null, $error_messages = null) {
        if (!$error_messages) {
            $error_messages = $this->error_messages;
        }

        return new MyUploadHandler($options, true, $error_messages);
    }

    public function deleteFile($options = null) {
        $file = new MyUploadHandler($options, false);
        return $file->delete();
    }
}