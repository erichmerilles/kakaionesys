<?php
// backend/inventory_logistics/manage_suppliers.php
require_once __DIR__ . '/../../frontend/includes/auth_check.php';

requirePermission('can_manage_inventory');
verifyCSRF();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = htmlspecialchars($_POST['supplier_name']);
    $person = htmlspecialchars($_POST['contact_person']);
    $phone = htmlspecialchars($_POST['contact_number']);
    $address = htmlspecialchars($_POST['address']);

    global $pdo;

    try {
        $stmt = $pdo->prepare("INSERT INTO suppliers (supplier_name, contact_person, contact_number, address) VALUES (?, ?, ?, ?)");
        $stmt->execute([$name, $person, $phone, $address]);

        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}
