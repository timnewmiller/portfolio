<?php
require_once 'config.php';

if (isset($_GET['id'])) {
    $list_id = $_GET['id'];
    
    // Fetch items associated with the selected list
    $stmt = $pdo->prepare("SELECT item_id, item_name, quantity FROM items WHERE list_id = ?");
    $stmt->execute([$list_id]);
    
    // Return items as JSON
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($items);
}
?>
