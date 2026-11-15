<?php

/*
 * Shared product validation used by both Add Product and Edit Product (L12).
 * Returns an error message, or "" when everything is valid.
 */

function lt_validate_product_fields(
    mysqli $conn,
    string $name,
    string $description,
    string $price,
    string $category_id
): string {

    if (
        $name === "" ||
        $description === "" ||
        $price === "" ||
        $category_id === ""
    ) {
        return "Please complete all required fields.";
    }

    if (mb_strlen($name) > 100) {
        return "Product name must be 100 characters or less.";
    }

    if (mb_strlen($description) > 2000) {
        return "Description must be 2000 characters or less.";
    }

    if (
        !is_numeric($price) ||
        (float) $price <= 0 ||
        (float) $price > 9999999.99
    ) {
        return "Please enter a valid product price.";
    }

    if (filter_var($category_id, FILTER_VALIDATE_INT) === false) {
        return "Please select a valid category.";
    }

    $category = db_select(
        $conn,
        "SELECT category_id FROM categories WHERE category_id = ?",
        "i",
        [(int) $category_id]
    );

    if ($category->num_rows !== 1) {
        return "Please select a valid category.";
    }

    return "";
}
