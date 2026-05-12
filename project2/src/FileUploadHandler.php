<?php

namespace PHPMaker2026\Project1;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class for file upload
 */
class FileUploadHandler
{
    public static array $options = [];

    public function __construct(
        protected Language $language,
    ) {
    }

    /**
     * Get value from request (query or request)
     *
     * @param Request $request
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    protected function get(Request $request, string $key, mixed $default = null): mixed
    {
        if ($request->query->has($key)) {
            return $request->query->all()[$key];
        }
        if ($request->request->has($key)) {
            return $request->request->all()[$key];
        }
        return $default;
    }

    /**
     * Perform file upload
     *
     * @param Request $request
     * @return Response
     */
    public function __invoke(Request $request): Response
    {
        // Set up upload parameters
        $uploadId = $this->get($request, "id", "");
        $uploadTable = $this->get($request, "table", "");
        $acceptFileTypes = $this->get($request, "acceptFileTypes", "");
        $arExt = explode(",", $acceptFileTypes);
        $allowedExt = Config("UPLOAD_ALLOWED_FILE_EXT");
        if ($allowedExt != "") {
            $arAllowedExt = explode(",", $allowedExt);
            $acceptFileTypes = implode(",", array_intersect($arExt, $arAllowedExt)) ?: $allowedExt; // Make sure $acceptFileTypes is a subset of $allowedExt
        } elseif ($acceptFileTypes == "") {
            $acceptFileTypes = "[\s\S]+"; // Allow all file types
        }
        $fileTypes = '/\\.(' . str_replace(",", "|", $acceptFileTypes) . ')$/i';
        $maxFileSize = $this->get($request, "maxFileSize");
        if ($maxFileSize != null) {
            $maxFileSize = (int)$maxFileSize;
        }
        $maxNumberOfFiles = $this->get($request, "maxNumberOfFiles");
        if ($maxNumberOfFiles != null) {
            $maxNumberOfFiles = (int)$maxNumberOfFiles;
            if ($maxNumberOfFiles < 1) {
                $maxNumberOfFiles = null;
            }
        }
        $params = ["rnd" => Random()];
        if ($uploadId != "") {
            $params["id"] = $uploadId;
        }
        if ($uploadTable != "") {
            $params["table"] = $uploadTable;
        }
        $url = UrlFor("api.jupload", [], $params);
        $uploadRoot = UploadTempPathRoot();
        $uploadDir = PrefixPath($uploadRoot);
        $uploadUrl = GetFilePublicUrl($uploadRoot);
        $inlineFileTypes = array_merge(explode(",", Config("IMAGE_ALLOWED_FILE_EXT")), (Config("EMBED_PDF") || !Config("DOWNLOAD_PDF_FILE")) ? ["pdf"] : []);
        $options = array_replace_recursive([
            "param_name" => $uploadId,
            "delete_type" => "POST", // POST or DELETE, set this option to POST for server not supporting DELETE requests
            "user_dirs" => true,
            "download_via_php" => 1,
            "script_url" => $url,
            "upload_dir" => $uploadDir,
            "upload_url" => $uploadUrl,
            "max_file_size" => $maxFileSize,
            "max_number_of_files" => $maxNumberOfFiles,
            "accept_file_types" => $fileTypes,
            "inline_file_types" => '/\.(' . implode("|", $inlineFileTypes) . ')$/i',
            "image_library" => 0, // Set to 0 to use the GD library to scale and orient images
            "image_versions" => [
                "" => [
                    "auto_orient" => true // Automatically rotate images based on EXIF meta data
                ],
                Config("UPLOAD_THUMBNAIL_FOLDER") => [
                    "max_width" => Config("UPLOAD_THUMBNAIL_WIDTH"),
                    "max_height" => Config("UPLOAD_THUMBNAIL_HEIGHT"),
                    "jpeg_quality" => 100,
                    "png_quality" => 9
                ]
            ]
        ], self::$options);
        $error_messages = [
            1 => $this->language->phrase("UploadError1"),
            2 => $this->language->phrase("UploadError2"),
            3 => $this->language->phrase("UploadError3"),
            4 => $this->language->phrase("UploadError4"),
            6 => $this->language->phrase("UploadError6"),
            7 => $this->language->phrase("UploadError7"),
            8 => $this->language->phrase("UploadError8"),
            "post_max_size" => $this->language->phrase("UploadErrorPostMaxSize"),
            "max_file_size" => $this->language->phrase("UploadErrorMaxFileSize"),
            "min_file_size" => $this->language->phrase("UploadErrorMinFileSize"),
            "accept_file_types" => $this->language->phrase("UploadErrorAcceptFileTypes"),
            "max_number_of_files" => $this->language->phrase("UploadErrorMaxNumberOfFiles"),
            "max_width" => $this->language->phrase("UploadErrorMaxWidth"),
            "min_width" => $this->language->phrase("UploadErrorMinWidth"),
            "max_height" => $this->language->phrase("UploadErrorMaxHeight"),
            "min_height" => $this->language->phrase("UploadErrorMinHeight")
        ];
        if (ob_get_length()) {
            ob_end_clean();
        }
        $upload_handler = new CustomUploadHandler($uploadId, $uploadTable,/* *** $sessionId,*/ $options, $error_messages);
        return $upload_handler->getResponse();
    }
}
