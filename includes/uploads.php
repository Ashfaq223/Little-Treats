<?php

/*
 * Safe image upload helper (S7).
 *
 * - checks the real MIME type of the file (not just its extension)
 * - only allows JPG, PNG and WEBP
 * - saves under a random name, never the name the visitor sent
 * - re-encodes the picture with GD when GD is installed, which removes
 *   anything hidden inside the file
 *
 * Returns the saved file name, or null (with $error filled in).
 */

function lt_save_uploaded_image(
    array $file,
    string $destination_folder,
    ?string &$error = null
): ?string {

    $error = null;

    $upload_error = $file["error"] ?? UPLOAD_ERR_NO_FILE;

    if ($upload_error === UPLOAD_ERR_NO_FILE) {
        $error = "Please select an image.";
        return null;
    }

    if ($upload_error !== UPLOAD_ERR_OK) {
        $error = "There was a problem uploading the image.";
        return null;
    }

    if (!is_uploaded_file($file["tmp_name"])) {
        $error = "There was a problem uploading the image.";
        return null;
    }

    if ($file["size"] > MAX_UPLOAD_BYTES) {
        $error = "Image size must be less than 5 MB.";
        return null;
    }

    // Real content type
    $finfo = new finfo(FILEINFO_MIME_TYPE);

    $mime = $finfo->file($file["tmp_name"]);

    $allowed = [
        "image/jpeg" => "jpg",
        "image/png"  => "png",
        "image/webp" => "webp"
    ];

    if (!isset($allowed[$mime])) {
        $error = "Please upload a JPG, JPEG, PNG or WEBP image.";
        return null;
    }

    if (@getimagesize($file["tmp_name"]) === false) {
        $error = "The selected file is not a valid image.";
        return null;
    }

    if (!is_dir($destination_folder)) {
        mkdir($destination_folder, 0755, true);
    }

    $extension = $allowed[$mime];

    $new_name = bin2hex(random_bytes(16)) . "." . $extension;

    $target = rtrim($destination_folder, "/\\") . DIRECTORY_SEPARATOR . $new_name;

    // Re-encode with GD when available
    $saved = false;

    $create = [
        "image/jpeg" => "imagecreatefromjpeg",
        "image/png"  => "imagecreatefrompng",
        "image/webp" => "imagecreatefromwebp"
    ][$mime];

    if (function_exists($create)) {

        $image = @$create($file["tmp_name"]);

        if ($image !== false) {

            if ($mime === "image/png") {
                imagesavealpha($image, true);
                $saved = imagepng($image, $target);
            } elseif ($mime === "image/webp") {
                $saved = imagewebp($image, $target, 90);
            } else {
                $saved = imagejpeg($image, $target, 90);
            }

            imagedestroy($image);

        }

    }

    if (!$saved) {
        $saved = move_uploaded_file($file["tmp_name"], $target);
    }

    if (!$saved) {
        $error = "Unable to upload the image.";
        return null;
    }

    return $new_name;
}
