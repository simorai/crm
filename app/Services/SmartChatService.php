<?php

namespace App\Services;

use App\Models\Tenant;
use Illuminate\Support\Facades\DB;
use OpenAI\Laravel\Facades\OpenAI;

class SmartChatService
{
    /**
     * System prompt — explains schema and strict tenant scoping to the model.
     */
    private function systemPrompt(int $tenantId): string
    {
        return <<<PROMPT
You are a CRM data assistant. You help users query their CRM data using natural language.
You MUST always scope every query to tenant_id = {$tenantId}.

Available tables and key columns:
- entities(id, tenant_id, name, vat, email, phone, status)
- people(id, tenant_id, entity_id, name, email, phone, position)
- deals(id, tenant_id, entity_id, person_id, title, value, stage, probability, expected_close_date, owner_id)
- products(id, tenant_id, name, description)
- deal_products(deal_id, product_id, quantity, price)
- activity_logs(id, tenant_id, loggable_type, loggable_id, type, description, user_id, created_at)
- calendar_events(id, tenant_id, title, start_at, end_at, location, owner_id)

Stages: lead, contact, proposal, negotiation, won, lost

When the user asks a question, respond in one of two ways:
1. If you can answer with a SQL query, respond with JSON like:
   {"type":"query","sql":"SELECT ... WHERE tenant_id = {$tenantId} ...","explanation":"brief explanation"}
   The SQL must always include WHERE tenant_id = {$tenantId} or equivalent JOIN scoping.
2. If the question is conversational or cannot be answered with SQL, respond with JSON like:
   {"type":"answer","content":"Your natural language answer here"}

IMPORTANT SECURITY RULES:
- NEVER generate SQL that modifies data (INSERT, UPDATE, DELETE, DROP, TRUNCATE, ALTER).
- NEVER remove the tenant_id = {$tenantId} filter.
- NEVER access tables outside the list above.
- Only SELECT statements are allowed.
PROMPT;
    }

    /**
     * Build a non-streaming response.
     */
    public function chat(Tenant $tenant, array $messages): array
    {
        $formattedMessages = $this->formatMessages($tenant, $messages);

        $response = OpenAI::chat()->create([
            'model'    => config('openai.model', 'gpt-4o-mini'),
            'messages' => $formattedMessages,
        ]);

        $content = $response->choices[0]->message->content ?? '';

        return $this->processResponse($tenant->id, $content);
    }

    /**
     * Generator for streaming — yields partial content chunks.
     */
    public function chatStream(Tenant $tenant, array $messages): \Generator
    {
        $formattedMessages = $this->formatMessages($tenant, $messages);

        $stream = OpenAI::chat()->createStreamed([
            'model'    => config('openai.model', 'gpt-4o-mini'),
            'messages' => $formattedMessages,
        ]);

        foreach ($stream as $response) {
            $delta = $response->choices[0]->delta->content ?? '';
            if ($delta !== '' && $delta !== null) {
                yield $delta;
            }
        }
    }

    /**
     * Execute a safe SELECT query scoped to tenant.
     */
    public function executeQuery(int $tenantId, string $sql): array
    {
        // Safety check: only allow SELECT
        $trimmed = ltrim($sql);
        if (!preg_match('/^SELECT\s/i', $trimmed)) {
            return ['error' => 'Only SELECT queries are allowed.'];
        }

        // Ensure tenant_id scoping is present
        if (!str_contains(strtolower($sql), 'tenant_id')) {
            return ['error' => 'Query must include tenant_id scoping.'];
        }

        try {
            $results = DB::select($sql);
            return ['data' => $results];
        } catch (\Throwable $e) {
            return ['error' => 'Query failed: '.$e->getMessage()];
        }
    }

    private function formatMessages(Tenant $tenant, array $messages): array
    {
        $formatted = [
            ['role' => 'system', 'content' => $this->systemPrompt($tenant->id)],
        ];

        foreach ($messages as $message) {
            $formatted[] = [
                'role'    => in_array($message['role'] ?? '', ['user', 'assistant']) ? $message['role'] : 'user',
                'content' => strip_tags((string) ($message['content'] ?? '')),
            ];
        }

        return $formatted;
    }

    public function processStreamedResponse(int $tenantId, string $content): array
    {
        return $this->processResponse($tenantId, $content);
    }

    private function processResponse(int $tenantId, string $content): array
    {
        // Strip markdown code fences if present
        $clean = preg_replace('/```(?:json)?\s*(.*?)\s*```/s', '$1', $content);
        $decoded = json_decode(trim($clean ?? $content), true);

        if (!is_array($decoded)) {
            return ['type' => 'answer', 'content' => $content];
        }

        if (($decoded['type'] ?? '') === 'query' && isset($decoded['sql'])) {
            // Double-check tenant isolation before executing
            if (!str_contains(strtolower($decoded['sql']), (string) $tenantId)) {
                $decoded['sql'] .= " AND tenant_id = $tenantId";
            }
            $queryResult = $this->executeQuery($tenantId, $decoded['sql']);
            $decoded['results'] = $queryResult;
        }

        return $decoded;
    }
}
