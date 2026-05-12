<?php

namespace PHPMaker2026\Project1;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Custom Upload handler
 */
class CustomUploadHandler extends UploadHandler
{
    protected Response $httpResponse;

    // Override constructor
    public function __construct(
        protected string $uploadId = "",
        protected string $uploadTable = "",
        ?array $options = null,
        ?array $error_messages = null
    ) {
        $this->httpResponse = new Response();
        parent::__construct($options, true, $error_messages); // $initialize = true
    }

    // Get response
    public function getResponse(): Response
    {
        return $this->httpResponse;
    }

    // Override initialize()
    protected function initialize()
    {
        if ($this->get_server_var("REQUEST_METHOD") == "GET" && $this->get_query_param("delete")) {
            $this->delete();
        } else {
            parent::initialize();
        }
    }

    // Override get_user_id()
    protected function get_user_id()
    {
        $cookieName = ini_get('session.name'); // Default: 'PHPSESSID'
        $sessionId = $_COOKIE[$cookieName] ?? "";
        $id = Config("UPLOAD_TEMP_FOLDER_PREFIX") . $sessionId;
        if ($this->uploadId != "") {
            $uid = $this->uploadId;
            if ($this->uploadTable != "") {
                $uid = $this->uploadTable . "/" . $uid;
            }
            $id .= "/" . $uid;
        }
        return $id;
    }

    // Override get_unique_filename()
    protected function get_unique_filename($file_path, $name, $size, $type, $error, $index, $content_range)
    {
        if (Config("UPLOAD_CONVERT_ACCENTED_CHARS")) {
            $name = htmlentities($name, ENT_COMPAT, "UTF-8");
            $name = preg_replace('/&([a-zA-Z])(uml|acute|grave|circ|tilde|cedil);/', '$1', $name);
            $name = html_entity_decode($name, ENT_COMPAT, "UTF-8");
        }
        return parent::get_unique_filename($file_path, $name, $size, $type, $error, $index, $content_range);
    }

    // Override get_singular_param_name()
    protected function get_singular_param_name()
    {
        return $this->options["param_name"];
    }

    // Override get_file_names_params()
    protected function get_file_names_params()
    {
        return []; // Not used
    }

    // Get post/query param
    protected function get_param($id) {
        return $this->get_post_param($id) ?: $this->get_query_param($id);
    }

    // Override handle_file_upload()
    protected function handle_file_upload($uploaded_file, $name, $size, $type, $error, $index = null, $content_range = null)
    {
        // Delete all files in directory if replace
        if ($this->get_param("replace") == "1") {
            $upload_dir = $this->get_upload_path();
            if ($ar = glob($upload_dir . "/*.*")) {
                foreach ($ar as $v) {
                    @unlink($v);
                }
            }
            foreach ($this->options["image_versions"] as $version => $options) {
                if (!empty($version)) {
                    if ($ar = glob($upload_dir . "/" . $version . "/*.*")) {
                        foreach ($ar as $v) {
                            @unlink($v);
                        }
                    }
                }
            }
        }
        return parent::handle_file_upload($uploaded_file, $name, $size, $type, $error, $index, $content_range);
    }

    // Override post()
    public function post($print_response = true)
    {
        if ($this->get_query_param("_method") === "DELETE") {
            return $this->delete($print_response);
        }
        $upload = $this->get_upload_data($this->options["param_name"]);
        // Parse the Content-Disposition header, if available:
        $content_disposition_header = $this->get_server_var("HTTP_CONTENT_DISPOSITION");
        $file_name = $content_disposition_header ?
            rawurldecode(preg_replace(
                '/(^[^"]+")|("$)/',
                "",
                $content_disposition_header
            )) : null;
        // Parse the Content-Range header, which has the following form:
        // Content-Range: bytes 0-524287/2000000
        $content_range_header = $this->get_server_var("HTTP_CONTENT_RANGE");
        $content_range = $content_range_header ?
            preg_split('/[^0-9]+/', $content_range_header) : null;
        $size = $content_range ? $content_range[3] : null;
        $files = [];
        if ($upload && is_array($upload["tmp_name"])) {
            // "param_name" is an array identifier like "files[]",
            // $upload is a multi-dimensional array:
            foreach ($upload["tmp_name"] as $index => $value) {
                $files[] = $this->handle_file_upload(
                    $upload["tmp_name"][$index],
                    $file_name ? $file_name : $upload["name"][$index],
                    $size ? $size : $upload["size"][$index],
                    $upload["type"][$index],
                    $upload["error"][$index],
                    $index,
                    $content_range
                );
            }
        } else {
            // "param_name" is a single object identifier like "file",
            // $upload is a one-dimensional array:
            $files[] = $this->handle_file_upload(
                $upload["tmp_name"] ?? null,
                $file_name ? $file_name : ($upload["name"] ?? null),
                $size ? $size : ($upload["size"] ?? $this->get_server_var("CONTENT_LENGTH")),
                $upload["type"] ?? $this->get_server_var("CONTENT_TYPE"),
                $upload["error"] ?? null,
                null,
                $content_range
            );
        }
        $response = ["files" => $files]; // Set key as "files" for jquery.fileupload-ui.js
        return $this->generate_response($response, $print_response);
    }

    // Override upcount_name_callback()
    protected function upcount_name_callback($matches)
    {
        $index = isset($matches[1]) ? (int)$matches[1] + 1 : 1;
        $ext = $matches[2] ?? "";
        return "(" . $index . ")" . $ext;
    }

    // Override upcount_name()
    protected function upcount_name($name)
    {
        return preg_replace_callback(
            '/(?:(?:\(([\d]+)\))?(\.[^.]+))?$/',
            [$this, "upcount_name_callback"],
            $name,
            1
        );
    }

    // Override get_scaled_image_file_paths()
    protected function get_scaled_image_file_paths($file_name, $version)
    {
        $ar = parent::get_scaled_image_file_paths($file_name, $version);
        $file_path = $this->get_upload_path($file_name);
        foreach ($ar as &$path) {
            $path = preg_replace('/(?<!:)\/\//', "/", $path);
        }
        return $ar;
    }

    // Override readfile()
    protected function readfile($file_path)
    {
        $binaryResponse = new BinaryFileResponse($file_path);
        // Remove and copy all headers (preserving case and multiple values)
        foreach ($this->httpResponse->headers->allPreserveCase() as $name => $values) {
            $binaryResponse->headers->remove($name);
            foreach ($values as $value) {
                $binaryResponse->headers->set($name, $value, false); // false = append
            }
        }
        $this->httpResponse = $binaryResponse;
    }

    // Override body()
    protected function body($str)
    {
        $this->httpResponse->setContent($str);
    }

    // Override header()
    protected function header($str)
    {
        [$name, $value] = explode(":", $str, 2) + [null, null]; // Make sure at least two elements
        if (trim($name) != "") {
            $this->httpResponse->headers->set(trim($name), trim($value));
        }
    }

    // Override send_content_type_header()
    protected function send_content_type_header()
    {
        $this->header("Vary: Accept");
        if (strpos($this->get_server_var("HTTP_ACCEPT"), "application/json") !== false) {
            $this->header("Content-type: application/json; charset=utf-8");
        } else {
            $this->header("Content-type: text/plain");
        }
    }

    // Override set_additional_file_properties()
    protected function set_additional_file_properties($file)
    {
        parent::set_additional_file_properties($file);
        $path = $this->get_upload_path($file->name);
        $parts = pathinfo($path);
        $file->extension = strtolower(@$parts["extension"]);
        $file->exists = true;
        if (getimagesize($path, $info) !== false && isset($info["APP13"])) {
            $iptc = iptcparse($info["APP13"]);
            if ($iptc !== false && @$iptc["2#040"][0] == "FileNotFound") {
                $file->exists = false;
            }
        }
    }

    // Override get_file_type
    protected function get_file_type($file_path)
    {
        switch (strtolower(pathinfo($file_path, PATHINFO_EXTENSION))) {
            case "jpeg":
            case "jpg":
                return "image/jpeg";
            case "png":
                return "image/png";
            case "gif":
                return "image/gif";
            case "pdf":
                return "application/pdf";
            default:
                return "";
        }
    }
}
