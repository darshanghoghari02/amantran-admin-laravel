<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * DbService — Port of the Node.js dbService.
 *
 * Uses the same schema: every table has two columns:
 *   - `id`   VARCHAR(255)  PRIMARY KEY
 *   - `data` LONGTEXT      (JSON-encoded object)
 *
 * This allows 1:1 compatibility with the existing MySQL database.
 */
class DbService
{
    /**
     * Get all records from a table.
     * Returns an array of decoded objects (arrays with id merged in).
     */
    public function getAll(string $table): array
    {
        $rows = DB::table($table)->get();
        return $rows->map(fn($row) => $this->decode($row))->toArray();
    }

    /**
     * Get a single record by ID.
     */
    public function getOne(string $table, string $id): ?array
    {
        $row = DB::table($table)->where('id', $id)->first();
        if (!$row) return null;
        return $this->decode($row);
    }

    /**
     * Get records matching a specific field value (searches inside JSON data).
     */
    public function getByField(string $table, string $field, mixed $value): array
    {
        // MySQL JSON_EXTRACT for efficient querying
        $rows = DB::table($table)
            ->whereRaw("JSON_EXTRACT(data, ?) = ?", ['$.' . $field, json_encode($value)])
            ->get();

        return $rows->map(fn($row) => $this->decode($row))->toArray();
    }

    /**
     * Get records where a JSON array field contains a value.
     */
    public function getWhereArrayContains(string $table, string $field, mixed $value): array
    {
        $rows = DB::table($table)
            ->whereRaw("JSON_CONTAINS(JSON_EXTRACT(data, ?), ?)", [
                '$.' . $field,
                json_encode($value)
            ])
            ->get();

        return $rows->map(fn($row) => $this->decode($row))->toArray();
    }

    /**
     * Add a new record.
     * Generates a unique ID if not provided.
     */
    public function add(string $table, array $data): array
    {
        $id = $data['id'] ?? $this->generateId($table);
        unset($data['id']);

        $now = now()->toISOString();
        if (!isset($data['createdAt'])) {
            $data['createdAt'] = $now;
        }
        if (!isset($data['updatedAt'])) {
            $data['updatedAt'] = $now;
        }

        $record = array_merge(['id' => $id], $data);

        DB::table($table)->insert([
            'id'   => $id,
            'data' => json_encode($data, JSON_UNESCAPED_UNICODE),
        ]);

        return $record;
    }

    /**
     * Update an existing record by ID (partial update — merges with existing data).
     */
    public function update(string $table, string $id, array $updates): array
    {
        $row = DB::table($table)->where('id', $id)->first();
        if (!$row) {
            throw new \Exception("Record not found in table '{$table}' with id '{$id}'");
        }

        $existing = json_decode($row->data, true) ?? [];
        $updates['updatedAt'] = now()->toISOString();
        $merged = array_merge($existing, $updates);

        DB::table($table)->where('id', $id)->update([
            'data' => json_encode($merged, JSON_UNESCAPED_UNICODE),
        ]);

        return array_merge(['id' => $id], $merged);
    }

    /**
     * Delete a record by ID.
     */
    public function delete(string $table, string $id): bool
    {
        return DB::table($table)->where('id', $id)->delete() > 0;
    }

    /**
     * Check if a table is empty.
     */
    public function isEmpty(string $table): bool
    {
        return DB::table($table)->count() === 0;
    }

    /**
     * Count records in a table.
     */
    public function count(string $table): int
    {
        return DB::table($table)->count();
    }

    /**
     * Decode a DB row into a PHP array with id merged in.
     */
    private function decode(object $row): array
    {
        $data = json_decode($row->data, true) ?? [];
        return array_merge(['id' => $row->id], $data);
    }

    /**
     * Generate a unique ID for a table record.
     * Matches the Node.js uuid pattern.
     */
    private function generateId(string $table): string
    {
        return (string) Str::uuid();
    }
}
