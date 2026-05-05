<?php

/**
 * Routes for conferences
 * 
 * This file is part of the OSIRIS package.
 * Copyright (c) 2026 Julia Koblitz, OSIRIS Solutions GmbH
 * 
 * @package     OSIRIS
 * @since       1.3.5
 * 
 * @copyright	Copyright (c) 2026 Julia Koblitz, OSIRIS Solutions GmbH
 * @author		Julia Koblitz <julia.koblitz@osiris-solutions.de>
 * @license     MIT
 */


Route::post('/crud/upload-image', function () {
    include_once BASEPATH . "/php/init.php";
    // Check if an image file is being uploaded#
    if (!isset($_POST['project_id'])) {
        echo json_encode(['error' => 'No project ID was provided.']);
        exit;
    }
    $project_id = $_POST['project_id'];
    if (isset($_FILES['image'])) {
        // Define the directory where you want to save the uploaded images
        $target_dir = "/uploads/projects/";

        $file_name = $_POST['project_id'] . "_" . basename($_FILES['image']['name']);

        // Create the target file path
        $target_file = BASEPATH . $target_dir . $file_name;

        // Get the file extension
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        // Check if the file is actually an image
        $check = getimagesize($_FILES['image']['tmp_name']);
        if ($check === false) {
            echo json_encode(['error' => 'File is not an image.']);
            exit;
        }

        // Check if file already exists
        if (file_exists($target_file)) {
            echo json_encode(['error' => 'File already exists.']);
            exit;
        }

        // Allow only certain file formats (JPEG, PNG, GIF)
        if ($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif") {
            echo json_encode(['error' => 'Sorry, only JPG, JPEG, PNG & GIF files are allowed.']);
            exit;
        }

        // Move the uploaded file to the target directory
        if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
            // Return the image URL as a JSON response
            $image_url = ROOTPATH . $target_dir . $file_name; 
            echo json_encode(['url' => $image_url]);
        } else {
            echo json_encode(['error' => 'Sorry, there was an error uploading your file.']);
        }
    } else {
        echo json_encode(['error' => 'No image file was uploaded.']);
    }
}, 'login');
