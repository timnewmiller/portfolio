<?php

require_once 'config.php';
require 'vendor/autoload.php';

use Ramsey\Uuid\Uuid;

error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_SERVER["REQUEST_METHOD"] != "POST" || empty($_POST)) {
    header("Location: index.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        // Get list name
        if (isset($_POST['listSelect']) && !empty($_POST['listSelect'])) {
            $selected_list_id = $_POST['listSelect'];
            $stmt = $pdo->prepare("SELECT list_name FROM lists WHERE list_id = ?");
            $stmt->execute([$selected_list_id]);
            $list_name = $stmt->fetchColumn();
        } else {
            $list_name = $_POST['listName'];
            // Insert new list into database
            $list_url = Uuid::uuid4()->toString();
            $stmt = $pdo->prepare("INSERT INTO lists (list_name, list_url) VALUES (?, ?)");
            $stmt->execute([$list_name, $list_url]);
            $selected_list_id = $pdo->lastInsertId();
        }

        // Track added/updated items
        $addedItems = [];
        $updatedItems = [];

        // In save-data.php
if (!empty($_POST['itemName'])) {
    foreach ($_POST['itemName'] as $index => $name) {
        if (!empty($name)) {
            $quantity = isset($_POST['itemQuantity'][$index]) && $_POST['itemQuantity'][$index] !== '' ? $_POST['itemQuantity'][$index] : 1;
            $item_id = isset($_POST['itemId'][$index]) ? $_POST['itemId'][$index] : null;

            try {
                if ($item_id) {
                    if($quantity == 0) {
                        // delete existing item
                        $stmt = $pdo->prepare("DELETE FROM items WHERE item_id = ? AND list_id =?");
                        $stmt->execute([$item_id, $selected_list_id]);
                        $deletedItems[] = ['name' => $name];
                    } else {
                        // update existing item
                        $stmt = $pdo->prepare("UPDATE items SET item_name = ?, quantity = ? WHERE item_id = ? AND list_id = ?");
                        $stmt->execute([$name, $quantity, $item_id, $selected_list_id]);
                        $updatedItems[] = ['name' => $name, 'quantity' => $quantity, 'item_id' => $item_id];
                    }
                } else {
                    if($quantity == 0) {
                        continue;
                    }
                    
                    $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM items WHERE item_name = ? AND list_id = ?");
                    $checkStmt->execute([$name, $selected_list_id]);
                    
                    if ($checkStmt->fetchColumn() == 0) {
                        $stmt = $pdo->prepare("INSERT INTO items (item_name, quantity, list_id) VALUES (?, ?, ?)");
                        $stmt->execute([$name, $quantity, $selected_list_id]);
                        $addedItems[] = ['name' => $name, 'quantity' => $quantity];
                    }
                }
            } catch (PDOException $e) {
                die("Item save failed: " . $e->getMessage());
            }
        }
    }
}

        // Prepare output message
        $output = "<h2 class='mb-4'>You have updated $list_name</h2>";
        
        if (!empty($deletedItems)) {
            $output .= "<h4 class='mt-3'>Items deleted:</h4>";
            $output .= "<ul class='list-group'>";
            foreach ($deletedItems as $item) {
                $output .= "<li class='list-group-item'>{$item['name']}</li>";
            }
            $output .= "</ul>";
        }
        
        if (!empty($addedItems)) {
            $output .= "<h4 class='mt-3'>New Items Added:</h4>";
            $output .= "<ul class='list-group'>";
            foreach ($addedItems as $item) {
                $output .= "<li class='list-group-item'>{$item['name']} (Quantity: {$item['quantity']})</li>";
            }
            $output .= "</ul>";
        }

        if (!empty($updatedItems)) {
            $output .= "<h4 class='mt-3'>Updated items:</h4>";
            $output .= "<ul class='list-group'>";
            foreach ($updatedItems as $item) {
                $output .= "<li class='list-group-item'><small class='text-black-50' style='display:inline-block;min-width:20px;'>{$item['item_id']}</small> | {$item['name']} (qty: {$item['quantity']})</li>";
            }
            $output .= "</ul>";
        }

        // Prepare data for QR code
      require_once 'phpqrcode/qrlib.php';
      $url = 'https://timranosaur.us/inventory/?selected='.$selected_list_id;
        
        $path = 'images/';
        $file = $path.'code-'.$selected_list_id.'.png';

        $ecc = 'L'; 
        $pixel_Size = 10; 
        $frame_Size = 10; 

        QRcode::png($url, $file, $ecc, $pixel_Size, $frame_Size); 

        ?>
        
        <!doctype html>
        <html lang="en">
        <head>
            <meta charset="utf-8">
            <meta name="viewport" content="width=device-width, initial-scale=1">
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
            <title>286 Digital Storage</title>
        </head>
         <main class="container mt-5">
            <div class="row">
                <div class="col-12 col-md-9 col-lg-6 mx-auto text-center">
                <?php 
                    echo '<h1>QR Code for <br/><span class="fw-bold">' .$list_name. '</span></h1>';
                    echo '<img src="'. $file .'">';
                    echo '</div>';
                    echo '<div class="col-12 col-md-9 col-lg-6 mx-auto text-left">';
                    echo '<div class="text-left">'. $output .'</div>';
                    echo '<a href="index.php" class="btn btn-primary mt-5">Add/edit lists</a>';
                ?>
                </div>
            </div>
        </main>
        <footer class="mt-5"></footer>
            <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
        </body>
        </html>
               
        <?php

    } catch (PDOException $e) {
        die("ERROR: " . $e->getMessage());
    }
}
?>
