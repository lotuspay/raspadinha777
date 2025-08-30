<?php
require_once __DIR__ . '/includes/db.php';

// Accept JSON
header('Content-Type: application/json');

$raw = file_get_contents('php://input');
$payload = json_decode($raw, true);
if (!is_array($payload)) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Invalid JSON']);
    exit;
}

// Lotuspay payloads may vary. Normalize some common fields
$event = strtolower($payload['event'] ?? ($payload['type'] ?? ''));
$data = $payload['data'] ?? $payload; // if wrapped
$status = strtolower($data['status'] ?? ($payload['status'] ?? ''));
$metadata = $data['metadata'] ?? [];
$externalId = $data['external_id'] ?? ($metadata['external_id'] ?? null);
$amount = floatval($data['amount'] ?? ($payload['amount'] ?? 0));

// Heuristics to detect operation: cashIn (deposit) vs cashOut (withdraw)
$isCashOut = isset($metadata['withdraw_id']) || isset($data['recipient']);
$isCashIn = !$isCashOut; // default to deposit when unsure

try {
    if ($isCashIn) {
        // Handle deposit notifications
        if (!$externalId) {
            throw new Exception('Missing external_id for deposit');
        }

        // Find deposit by external_id
        $stmt = $conn->prepare("SELECT id, user_id, amount, status FROM deposits WHERE external_id = ? LIMIT 1");
        $stmt->bind_param('s', $externalId);
        $stmt->execute();
        $dep = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$dep) {
            throw new Exception('Deposit not found for external_id: ' . $externalId);
        }

        // Consider success statuses from provider
        $successStatuses = ['approved','paid','confirmed','completed','success','concluido'];
        $failedStatuses = ['failed','canceled','cancelled','error'];

        if (in_array($status, $successStatuses, true)) {
            if ($dep['status'] !== 'concluido') {
                // Mark as completed and credit user balance
                $conn->begin_transaction();
                try {
                    $upd1 = $conn->prepare("UPDATE deposits SET status = 'concluido', updated_at = NOW() WHERE id = ?");
                    $upd1->bind_param('i', $dep['id']);
                    $upd1->execute();
                    $upd1->close();

                    $upd2 = $conn->prepare("UPDATE users SET balance = balance + ? WHERE id = ?");
                    $amt = floatval($dep['amount']);
                    $upd2->bind_param('di', $amt, $dep['user_id']);
                    $upd2->execute();
                    $upd2->close();

                    $conn->commit();
                } catch (Throwable $txe) {
                    $conn->rollback();
                    throw $txe;
                }
            }
        } elseif (in_array($status, $failedStatuses, true)) {
            if ($dep['status'] !== 'cancelado' && $dep['status'] !== 'concluido') {
                $upd = $conn->prepare("UPDATE deposits SET status = 'cancelado', updated_at = NOW() WHERE id = ?");
                $upd->bind_param('i', $dep['id']);
                $upd->execute();
                $upd->close();
            }
        }

        echo json_encode(['ok' => true]);
        exit;
    }

    // Handle withdrawals (cashOut)
    $withdrawId = intval($metadata['withdraw_id'] ?? 0);
    if ($withdrawId <= 0) {
        throw new Exception('Missing withdraw_id in metadata');
    }

    // Load current withdraw record
    $stmt = $conn->prepare("SELECT user_id, valor, status FROM saques_pix WHERE id = ? LIMIT 1");
    $stmt->bind_param('i', $withdrawId);
    $stmt->execute();
    $saque = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$saque) {
        throw new Exception('Withdraw not found: ' . $withdrawId);
    }

    $successStatuses = ['approved','paid','confirmed','completed','success','concluido'];
    $failedStatuses = ['failed','canceled','cancelled','error'];

    if (in_array($status, $successStatuses, true)) {
        if ($saque['status'] !== 'concluido') {
            $upd = $conn->prepare("UPDATE saques_pix SET status = 'concluido', data_processamento = NOW(), observacoes = CONCAT(COALESCE(observacoes,''),'\nWebhook: cashOut concluído (', ?) ,') WHERE id = ?");
            $st = strtoupper($status);
            $upd->bind_param('si', $st, $withdrawId);
            $upd->execute();
            $upd->close();
        }
    } elseif (in_array($status, $failedStatuses, true)) {
        if ($saque['status'] !== 'cancelado' && $saque['status'] !== 'concluido') {
            // Refund user balance and mark as canceled, atomically
            $conn->begin_transaction();
            try {
                $upd1 = $conn->prepare("UPDATE saques_pix SET status = 'cancelado', data_processamento = NOW(), observacoes = CONCAT(COALESCE(observacoes,''),'\nWebhook: cashOut falhou (', ?) ,') WHERE id = ?");
                $st = strtoupper($status);
                $upd1->bind_param('si', $st, $withdrawId);
                $upd1->execute();
                $upd1->close();

                $upd2 = $conn->prepare("UPDATE users SET balance = balance + ? WHERE id = ?");
                $amt = floatval($saque['valor']);
                $upd2->bind_param('di', $amt, $saque['user_id']);
                $upd2->execute();
                $upd2->close();

                $conn->commit();
            } catch (Throwable $txe) {
                $conn->rollback();
                throw $txe;
            }
        }
    } else {
        // Intermediate status: mark processing
        if ($saque['status'] === 'pendente') {
            $upd = $conn->prepare("UPDATE saques_pix SET status = 'processando', observacoes = CONCAT(COALESCE(observacoes,''),'\nWebhook: status ', ?) WHERE id = ?");
            $st = strtoupper($status ?: 'UNKNOWN');
            $upd->bind_param('si', $st, $withdrawId);
            $upd->execute();
            $upd->close();
        }
    }

    echo json_encode(['ok' => true]);
} catch (Throwable $e) {
    error_log('webhook_lotuspay error: ' . $e->getMessage());
    http_response_code(200); // Avoid provider retries storm; we handle idempotently
    echo json_encode(['ok' => true]);
}
