<?php

namespace App\Libraries;

use App\Models\AuditLogModel;

class AuditLogger
{
    public static function log(string $action, ?string $entityType = null, ?int $entityId = null, ?string $description = null, ?array $metadata = null): void
    {
        try {
            $session = session();
            $request = service('request');

            $userId = $session->get('user_id');
            $username = $session->get('username');
            $role = $session->get('role');

            $log = [
                'user_id' => $userId ?: null,
                'username' => $username ?: null,
                'role' => $role ?: null,
                'action' => $action,
                'entity_type' => $entityType,
                'entity_id' => $entityId,
                'description' => $description,
                'ip_address' => $request->getIPAddress(),
                'user_agent' => $request->getUserAgent() ? $request->getUserAgent()->getAgentString() : null,
                'metadata' => $metadata ? json_encode($metadata) : null,
                'created_at' => date('Y-m-d H:i:s')
            ];

            $model = new AuditLogModel();
            $model->insert($log);
        } catch (\Throwable $e) {
            // Fail silently for audit logging
        }
    }
}
