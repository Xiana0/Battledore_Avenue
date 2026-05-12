<?php

namespace PHPMaker2026\Project1;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * File viewer
 */
class FileViewer
{

    public function __construct(protected Language $language)
    {
    }

    /**
     * Output file
     *
     * @return bool Whether file is outputted successfully
     */
    public function __invoke(): Response
    {
        // Get parameters
        $tbl = null;
        $tableName = "";
        if (IsPost()) {
            $fn = Post("fn", "");
            $table = Post(Config("API_OBJECT_NAME"), "");
            $field = Post(Config("API_FIELD_NAME"), "");
            $recordkey = Post("key", "");
            $resize = PostBool("resize", false);
            $width = PostInt("width", 0);
            $height = PostInt("height", 0);
            $download = PostBool("download", true); // Download by default
            $crop = PostBool("crop");
        } else { // For /api/file/{table}/{param}/{key}, $param can be {field} or {path}
            $fn = Get("fn", "");
            if (!empty(Route("param")) && empty(Route("key"))) {
                $fn = Route("param");
            }
            $table = Get(Config("API_OBJECT_NAME"), Route("table"));
            $field = Get(Config("API_FIELD_NAME"), Route("key") ? Route("param") : null);
            $recordkey = Get("key", Route("key"));
            $resize = GetBool("resize", false);
            $width = GetInt("width", 0);
            $height = GetInt("height", 0);
            $download = GetBool("download", true); // Download by default
            $crop = GetBool("crop");
        }
        $key = SessionId() . ServerVar("ENCRYPTION_KEY");
        if ($width == 0 && $height == 0 && $resize) {
            $width = Config("THUMBNAIL_DEFAULT_WIDTH");
            $height = Config("THUMBNAIL_DEFAULT_HEIGHT");
        }

        // Get table object
        $tbl = Container($table);

        // API request with table/fn
        $fn = ($tbl?->TableName ?? false)
            ? Decrypt($fn, $key) // File path is always encrypted
            : "";

        // Get image
        $res = false;
        $callback = $crop ? fn($img) => $img->cover($width, $height) : null;
        $response = new Response();
        if ($fn != "") { // Physical file
            $fn = filter_var($fn, FILTER_UNSAFE_RAW, FILTER_FLAG_STRIP_LOW);
            if (!FileExists($fn)) {
                throw new NotFoundHttpException($this->language->phrase("FileNotFound", true));
            }
            $info = pathinfo($fn);
            if ($data = ReadFile($fn)) {
                $ext = strtolower($info["extension"] ?? "");
                $isPdf = SameText($ext, "pdf");
                $ct = MimeContentType($fn);
                if ($ct) {
                    $response->headers->set("Content-type", $ct);
                }
                if (in_array($ext, explode(",", Config("IMAGE_ALLOWED_FILE_EXT")))) { // Skip "Content-Disposition" header if images
                    if ($width > 0 || $height > 0) {
                        ResizeBinary($data, $width, $height, $callback);
                    }
                } elseif (in_array($ext, explode(",", Config("DOWNLOAD_ALLOWED_FILE_EXT")))) {
                    $isAttachment = false;
                    if ($download && !((Config("EMBED_PDF") || !Config("DOWNLOAD_PDF_FILE")) && $isPdf)) { // Skip header if embed/inline PDF
                        $isAttachment = true;
                    }
                    // Add filename in Content-Disposition
                    $response->headers->set("Content-Disposition", ($isAttachment ? "attachment" : "inline") . "; filename=\"" . $info["basename"] . "\"");
                }
                return $response->setContent($data);
            }
        } elseif (is_object($tbl) && $field != "" && $recordkey != "") { // From table
            return $tbl->getFileData($field, $recordkey, $resize, $width, $height, $callback);
        }
        return $response;
    }
}
